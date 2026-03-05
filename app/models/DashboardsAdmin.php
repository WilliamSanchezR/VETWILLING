
<?php

require_once __DIR__ . '/../../config/database.php';


class DashboardsAdmin
{
    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    // Funcion para obtener informacion de usuarios
    public function getTotalUsuarios()
    {
        try {
            $sql = "SELECT 
                    COUNT(*) AS total_usuarios,

                    SUM(CASE 
                        WHEN estado = 'activo' 
                        THEN 1 ELSE 0 
                    END) AS total_activos,

                    SUM(CASE 
                        WHEN estado = 'inactivo' 
                        THEN 1 ELSE 0 
                    END) AS total_inactivos,

                    SUM(CASE 
                        WHEN fecha_creacion >= NOW() - INTERVAL 7 DAY 
                        THEN 1 ELSE 0 
                    END) AS registrados_ultima_semana,

                    ROUND(
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) * 100.0 
                        / COUNT(*), 
                        0
                    ) AS porcentaje_activos,

                    ROUND(
                        SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) * 100.0 
                        / COUNT(*), 
                        0
                    ) AS porcentaje_inactivos

                FROM usuario;";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            error_log("Error en DashboardsAdmin::getTotalUsuarios -> " . $e->getMessage());
            return false;
        }
    }

    // Funcion para traer los usuarios registrados en el ultimo mes
    public function getUsuariosRegistradosUltimoMes()
    {
        try {
            $sql = "SELECT 
                    COUNT(*) AS usuarios_ultimo_mes
                FROM usuario
                WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['usuarios_ultimo_mes'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en DashboardsAdmin::getUsuariosRegistradosUltimoMes -> " . $e->getMessage());
            return false;
        }
    }

    // Funcion para obtener informacion de veterinaria
    public function getTotalVeterinarias()
    {
        try {
            $sql = "SELECT COUNT(*) AS total_veterinrias 
        FROM veterinaria v
        WHERE estado ='activo'";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_veterinrias'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en DashboardsAdmin::getTotalVeterinarias -> " . $e->getMessage());
            return false;
        }
    }

    // Funcion para traer el porcentaje de veterinarias activas vs inactivas
    public function getPorcentajeVeterinarias()
    {
        try {
            $sql = "SELECT 
        ROUND(
            SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) * 100.0 
            / COUNT(*), 
            0
        ) AS porcentaje_activas,

        ROUND(
            SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) * 100.0 
            / COUNT(*), 
            0
        ) AS porcentaje_inactivas

        FROM veterinaria;";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            error_log("Error en DashboardsAdmin::getPorcentajeVeterinarias -> " . $e->getMessage());
            return false;
        }
    }

    // Funcion para obtener informacion de profesionales
    public function getTotalProfesionales()
    {
        try {
            $sql = "SELECT 
        COUNT(*) AS total_profesionales
        FROM profesional p
        INNER JOIN usuario u 
        ON p.id_usuario = u.id_usuario
        WHERE u.estado = 'activo'";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_profesionales'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en DashboardsAdmin::getTotalProfesionales -> " . $e->getMessage());
            return false;
        }
    }

    // Funcion para traer los profesionales agregados en el ultimo mes
    public function getProfesionalesRegistradosUltimoMes()
    {
        try {
            $sql = "SELECT 
            COUNT(*) AS profesionales_ultimo_mes
        FROM profesional p
        INNER JOIN usuario u 
        ON p.id_usuario = u.id_usuario
        WHERE u.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['profesionales_ultimo_mes'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en DashboardsAdmin::getProfesionalesRegistradosUltimoMes -> " . $e->getMessage());
            return false;
        }
    }

    // Funcion para traer el porcentaje de profesionales activos vs inactivos
    public function getPorcentajeProfesionales()
    {
        try {
            $sql = "SELECT 
        ROUND(
            SUM(CASE WHEN u.estado = 'activo' THEN 1 ELSE 0 END) * 100.0 
            / COUNT(*), 
            0
        ) AS porcentaje_activas,

        ROUND(
            SUM(CASE WHEN u.estado = 'inactivo' THEN 1 ELSE 0 END) * 100.0 
            / COUNT(*), 
            0
        ) AS porcentaje_inactivas

        FROM profesional p
        INNER JOIN usuario u 
        ON p.id_usuario = u.id_usuario;";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            error_log("Error en DashboardsAdmin::getPorcentajeProfesionales -> " . $e->getMessage());
            return false;
        }
    }

    // Funcion para obtener los Usuarios que usaron el sistema en el ultimo mes
    public function getUsuariosUltimoMes()
    {
        try {
            $sql = "SELECT 
                        COUNT(CASE 
                            WHEN ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 1 MONTH) 
                            THEN 1 
                        END) AS usuarios_ultimo_mes,
                        ROUND(
                            COUNT(CASE 
                                WHEN ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 1 MONTH) 
                                THEN 1 
                            END) * 100.0 
                            / COUNT(*),
                        0) AS porcentaje_activos_ultimo_mes

                    FROM usuario;";  
                    $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            error_log("Error en DashboardsAdmin::getUsuariosUltimoMes -> " . $e->getMessage());
            return false;
        }
    }

   
}
