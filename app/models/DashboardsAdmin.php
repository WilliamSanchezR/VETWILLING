
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
}
