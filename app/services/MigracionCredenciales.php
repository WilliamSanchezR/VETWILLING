<?php

require_once __DIR__ . '/../helpers/secretos_helper.php';

/**
 * Migra las credenciales de canal_envio_config entre texto plano y cifrado (y de vuelta),
 * sin perderlas. La usan scripts/cifrar-credenciales-smtp.php y las pruebas.
 *
 * Garantías por cada credencial:
 *  - Se trabaja dentro de una transacción con la fila bloqueada (SELECT ... FOR UPDATE).
 *  - Antes de escribir se comprueba que el valor nuevo se convierte de vuelta en el original.
 *  - El UPDATE solo aplica si el valor no cambió desde que se leyó.
 *  - Después de escribir se relee y se vuelve a comprobar; si algo no coincide, se revierte.
 *  - Nunca se devuelve ni se imprime el valor de la credencial.
 */
class MigracionCredenciales
{
    private const CAMPOS = ['smtp_password', 'push_api_key'];

    public function __construct(private PDO $db)
    {
    }

    /**
     * Huella corta de la clave activa (no permite recuperarla). Sirve para confirmar
     * que dos entornos usan la misma APP_ENCRYPTION_KEY sin mostrarla.
     */
    public static function huellaClave(): ?string
    {
        $clave = secreto_clave();
        return $clave === null ? null : substr(hash('sha256', 'vetwilling-huella|' . $clave), 0, 12);
    }

    /**
     * @param string $modo    'cifrar' (texto plano -> cifrado) o 'revertir' (cifrado -> texto plano)
     * @param bool   $aplicar false = solo informa qué haría
     * @return array[] Una fila por credencial: canal, campo, estado_antes, estado_despues, accion
     */
    public function ejecutar(string $modo, bool $aplicar): array
    {
        if (!in_array($modo, ['cifrar', 'revertir'], true)) {
            throw new InvalidArgumentException("Modo desconocido: $modo");
        }
        if (secreto_clave() === null) {
            throw new RuntimeException('Falta APP_ENCRYPTION_KEY válida (32 bytes en base64). No se modificó nada.');
        }

        $ids = $this->db->query("SELECT id_canal FROM canal_envio_config ORDER BY id_canal")->fetchAll(PDO::FETCH_COLUMN);
        $resultado = [];
        foreach ($ids as $idCanal) {
            foreach (self::CAMPOS as $campo) {
                $resultado[] = $this->migrarCampo((int) $idCanal, $campo, $modo, $aplicar);
            }
        }
        return $resultado;
    }

    private function migrarCampo(int $idCanal, string $campo, string $modo, bool $aplicar): array
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT codigo_canal, `$campo` AS valor FROM canal_envio_config WHERE id_canal = ? FOR UPDATE");
            $stmt->execute([$idCanal]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            $original = $fila['valor'];
            $estado = secreto_estado($original);
            $info = ['canal' => $fila['codigo_canal'], 'campo' => $campo, 'estado_antes' => $estado, 'estado_despues' => $estado];

            $pendiente = ($modo === 'cifrar' && $estado === 'texto_plano') || ($modo === 'revertir' && $estado === 'cifrada');
            if ($modo === 'revertir' && $estado === 'ilegible') {
                $this->db->rollBack();
                return $info + ['accion' => 'error: está cifrada pero no se descifra con la clave actual (no se tocó)'];
            }
            if (!$pendiente) {
                $this->db->rollBack();
                return $info + ['accion' => 'sin cambios'];
            }

            // Valor nuevo y comprobación de ida y vuelta antes de escribir
            $plano = $modo === 'cifrar' ? $original : secreto_descifrar($original);
            $nuevo = $modo === 'cifrar' ? secreto_cifrar($original) : $plano;
            if (secreto_descifrar($nuevo) !== $plano) {
                throw new RuntimeException('La comprobación previa no coincide.');
            }

            if (!$aplicar) {
                $this->db->rollBack();
                return $info + ['accion' => $modo === 'cifrar' ? 'se cifraría' : 'se revertiría a texto plano'];
            }

            $upd = $this->db->prepare("UPDATE canal_envio_config SET `$campo` = :nuevo WHERE id_canal = :id AND `$campo` = :original");
            $upd->execute([':nuevo' => $nuevo, ':id' => $idCanal, ':original' => $original]);
            if ($upd->rowCount() !== 1) {
                throw new RuntimeException('El valor cambió mientras se migraba.');
            }

            // Relectura y comprobación final dentro de la misma transacción
            $stmt->execute([$idCanal]);
            $guardado = $stmt->fetch(PDO::FETCH_ASSOC)['valor'];
            if (secreto_descifrar($guardado) !== $plano) {
                throw new RuntimeException('La comprobación posterior no coincide.');
            }

            $this->db->commit();
            return array_merge($info, [
                'estado_despues' => secreto_estado($guardado),
                'accion' => $modo === 'cifrar' ? 'cifrada' : 'revertida a texto plano',
            ]);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['canal' => $fila['codigo_canal'] ?? "id $idCanal", 'campo' => $campo,
                'estado_antes' => $estado ?? '?', 'estado_despues' => $estado ?? '?',
                'accion' => 'error: ' . $e->getMessage() . ' (no se modificó)'];
        }
    }
}
