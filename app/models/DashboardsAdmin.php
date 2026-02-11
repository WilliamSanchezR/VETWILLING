
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

// Funcion informacion de veterinaria
function getTotalVeterinarias()
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