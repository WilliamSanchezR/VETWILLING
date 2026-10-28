<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Acceso a datos para la recuperación de contraseña.
 * Solo consultas SQL: las reglas (vencimiento, límites, envío) viven en
 * app/services/PasswordResetService.php
 */
class RecoveryPass
{
    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    public function buscarUsuarioActivoPorEmail(string $email)
    {
        $stmt = $this->conexion->prepare(
            "SELECT id_usuario, email FROM usuario WHERE email = :email AND estado = 'activo' LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    // Cuenta solicitudes recientes por usuario o por IP (límite anti-abuso)
    public function contarSolicitudesRecientes(?int $idUsuario, string $ip, int $minutos): int
    {
        $stmt = $this->conexion->prepare(
            "SELECT COUNT(*) FROM password_reset
             WHERE (id_usuario = :id OR ip_solicitud = :ip)
               AND fecha_creacion >= (NOW() - INTERVAL :minutos MINUTE)"
        );
        $stmt->bindValue(':id', $idUsuario ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':minutos', $minutos, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // Un usuario solo puede tener un enlace vigente: al pedir uno nuevo se anulan los anteriores
    public function invalidarTokensUsuario(int $idUsuario): void
    {
        $stmt = $this->conexion->prepare(
            "UPDATE password_reset SET usado_en = NOW() WHERE id_usuario = :id AND usado_en IS NULL"
        );
        $stmt->execute([':id' => $idUsuario]);
    }

    public function crearToken(int $idUsuario, string $tokenHash, int $minutosVigencia, string $ip): void
    {
        $stmt = $this->conexion->prepare(
            "INSERT INTO password_reset (id_usuario, token_hash, expira, ip_solicitud)
             VALUES (:id, :hash, NOW() + INTERVAL :minutos MINUTE, :ip)"
        );
        $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':hash', $tokenHash);
        $stmt->bindValue(':minutos', $minutosVigencia, PDO::PARAM_INT);
        $stmt->bindValue(':ip', $ip);
        $stmt->execute();
    }

    public function buscarTokenVigente(string $tokenHash)
    {
        $stmt = $this->conexion->prepare(
            "SELECT pr.id_reset, pr.id_usuario, u.email
             FROM password_reset pr
             INNER JOIN usuario u ON u.id_usuario = pr.id_usuario
             WHERE pr.token_hash = :hash
               AND pr.usado_en IS NULL
               AND pr.expira > NOW()
               AND u.estado = 'activo'
             LIMIT 1"
        );
        $stmt->execute([':hash' => $tokenHash]);
        return $stmt->fetch();
    }

    // Cambia la contraseña y consume el token en una sola transacción
    public function actualizarClaveYConsumirToken(int $idReset, int $idUsuario, string $claveHash): bool
    {
        try {
            $this->conexion->beginTransaction();

            // Marcar el token como usado solo si sigue libre (evita doble uso simultáneo)
            $consumir = $this->conexion->prepare(
                "UPDATE password_reset SET usado_en = NOW() WHERE id_reset = :id AND usado_en IS NULL"
            );
            $consumir->execute([':id' => $idReset]);
            if ($consumir->rowCount() !== 1) {
                $this->conexion->rollBack();
                return false;
            }

            $actualizar = $this->conexion->prepare(
                "UPDATE usuario SET password_hash = :clave WHERE id_usuario = :id"
            );
            $actualizar->execute([':clave' => $claveHash, ':id' => $idUsuario]);

            $this->invalidarTokensUsuario($idUsuario);

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log('RecoveryPass::actualizarClaveYConsumirToken -> ' . $e->getMessage());
            return false;
        }
    }
}
