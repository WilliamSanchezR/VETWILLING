<?php

/**
 * MODELO: AuditoriaModificacionMascotas
 * Registra cada campo modificado en una mascota (RFS 28)
 */

require_once __DIR__ . '/../../config/database.php';

class AuditoriaModificacionMascotas
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Registra un cambio de campo en la auditoría.
     * Llama una vez por cada campo que cambió.
     */
    public function registrarCambio(array $data): bool
    {
        try {
            $sql = "INSERT INTO auditoria_modificacion_mascotas
                        (id_paciente, id_usuario_modificador, campo_modificado,
                         valor_anterior, valor_nuevo, ip_origen, user_agent)
                    VALUES
                        (:id_paciente, :id_usuario_modificador, :campo_modificado,
                         :valor_anterior, :valor_nuevo, :ip_origen, :user_agent)";

            $stmt = $this->conexion->prepare($sql);

            $id_paciente            = $data['id_paciente'];
            $id_usuario_modificador = $data['id_usuario_modificador'];
            $campo_modificado       = $data['campo_modificado'];
            $valor_anterior         = $data['valor_anterior'] ?? null;
            $valor_nuevo            = $data['valor_nuevo'] ?? null;
            $ip_origen              = $data['ip_origen'] ?? null;
            $user_agent             = $data['user_agent'] ?? null;

            $stmt->bindParam(':id_paciente',            $id_paciente,            PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario_modificador', $id_usuario_modificador, PDO::PARAM_INT);
            $stmt->bindParam(':campo_modificado',       $campo_modificado,       PDO::PARAM_STR);
            $stmt->bindParam(':valor_anterior',         $valor_anterior,         PDO::PARAM_STR);
            $stmt->bindParam(':valor_nuevo',            $valor_nuevo,            PDO::PARAM_STR);
            $stmt->bindParam(':ip_origen',              $ip_origen,              PDO::PARAM_STR);
            $stmt->bindParam(':user_agent',             $user_agent,             PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('❌ AuditoriaModificacionMascotas::registrarCambio → ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Compara los valores anteriores con los nuevos y registra cada diferencia.
     */
    public function registrarCambios(int $id_paciente, int $id_usuario, array $antes, array $despues): void
    {
        $campos_auditables = [
            'nombre', 'especie', 'raza', 'edad_numero', 'edad_unidad', 'sexo',
            'peso', 'estado_salud', 'fecha_ultima_desparasitacion', 'img_mascota'
        ];

        $ip         = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        foreach ($campos_auditables as $campo) {
            $anterior = isset($antes[$campo]) ? (string)$antes[$campo] : null;
            $nuevo    = isset($despues[$campo]) ? (string)$despues[$campo] : null;

            // Solo registrar si hay cambio real
            if ($anterior !== $nuevo) {
                $this->registrarCambio([
                    'id_paciente'            => $id_paciente,
                    'id_usuario_modificador' => $id_usuario,
                    'campo_modificado'       => $campo,
                    'valor_anterior'         => $anterior,
                    'valor_nuevo'            => $nuevo,
                    'ip_origen'              => $ip,
                    'user_agent'             => $user_agent,
                ]);
            }
        }
    }
}
