<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/acceso_helper.php';

/**
 * Consultas de Soporte de cuentas (superadministrador): buscar cualquier cuenta, ver los correos
 * que el sistema le envió y asignar o completar una clave temporal.
 * Las reglas (quién puede, generar la clave, Brevo) viven en app/services/SoporteCuentaService.php
 */
class SoporteCuenta
{
    private $conexion;

    // Nombre de la cuenta según su rol: cada rol guarda los datos personales en su propia tabla
    const SQL_CUENTA = "SELECT us.id_usuario, us.email, us.estado, us.id_rol, rol.nombre AS rol,
            COALESCE(adm.nombres, rep.nombres, pro.nombres, prop.nombres, '') AS nombres,
            COALESCE(adm.apellidos, rep.apellidos, pro.apellidos, prop.apellidos, '') AS apellidos
        FROM usuario us
        INNER JOIN rol ON rol.id_rol = us.id_rol
        LEFT JOIN administrador adm       ON adm.id_usuario = us.id_usuario
        LEFT JOIN representante_legal rep ON rep.id_usuario = us.id_usuario
        LEFT JOIN profesional pro         ON pro.id_usuario = us.id_usuario
        LEFT JOIN propietario prop        ON prop.id_usuario = us.id_usuario";

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    /** Hasta 20 cuentas cuyo correo o nombre contiene el texto. */
    public function buscar(string $texto): array
    {
        $patron = '%' . addcslashes($texto, '%_\\') . '%';
        $stmt = $this->conexion->prepare(
            "SELECT * FROM (" . self::SQL_CUENTA . ") c
             WHERE c.email LIKE :p1 OR CONCAT(c.nombres, ' ', c.apellidos) LIKE :p2
             ORDER BY c.email
             LIMIT 20"
        );
        $stmt->execute([':p1' => $patron, ':p2' => $patron]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener(int $idUsuario): ?array
    {
        $stmt = $this->conexion->prepare(self::SQL_CUENTA . " WHERE us.id_usuario = ? LIMIT 1");
        $stmt->execute([$idUsuario]);
        $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cuenta) {
            return null;
        }
        $cuenta['debe_cambiar_clave'] = acceso_leer_cambio_obligatorio($idUsuario);
        return $cuenta;
    }

    /** Últimos correos que el sistema intentó enviar a esta dirección (tabla notificaciones_enviadas). */
    public function historialCorreos(string $email, int $limite = 20): array
    {
        // destinatario puede tener varias direcciones separadas por ", "
        $stmt = $this->conexion->prepare(
            "SELECT id_notificacion, fecha_envio, tipo_notificacion, asunto, estado_envio, mensaje_error
             FROM notificaciones_enviadas
             WHERE medio_notificacion = 'email'
               AND (destinatario = :email OR FIND_IN_SET(:email2, REPLACE(destinatario, ', ', ',')) > 0)
             ORDER BY id_notificacion DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':email2', $email);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Pone una clave temporal y obliga a cambiarla al entrar. Cierra todas las sesiones del usuario
     * e invalida sus enlaces de recuperación pendientes.
     */
    public function asignarClaveTemporal(int $idUsuario, string $claveHash): void
    {
        $this->conexion->beginTransaction();
        try {
            $this->conexion->prepare('UPDATE usuario SET password_hash = ?, debe_cambiar_clave = 1 WHERE id_usuario = ?')
                ->execute([$claveHash, $idUsuario]);
            acceso_rotar_credenciales($this->conexion, $idUsuario);
            $this->conexion->commit();
        } catch (Throwable $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    /** El usuario reemplaza su clave temporal. Devuelve la nueva versión de credenciales. */
    public function completarCambioObligatorio(int $idUsuario, string $claveHash): int
    {
        $this->conexion->beginTransaction();
        try {
            $this->conexion->prepare('UPDATE usuario SET password_hash = ?, debe_cambiar_clave = 0 WHERE id_usuario = ?')
                ->execute([$claveHash, $idUsuario]);
            $version = acceso_rotar_credenciales($this->conexion, $idUsuario);
            $this->conexion->commit();
            return $version;
        } catch (Throwable $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    public function hashActual(int $idUsuario): ?string
    {
        $stmt = $this->conexion->prepare('SELECT password_hash FROM usuario WHERE id_usuario = ?');
        $stmt->execute([$idUsuario]);
        $hash = $stmt->fetchColumn();
        return $hash === false ? null : $hash;
    }
}
