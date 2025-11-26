<?php

require_once __DIR__ . '/../../config/database.php';

class Veterinaria
{

    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    // Función para listar las veterinarias registradas por id y nombre
    public function listarVeterinariasRegistradas()
    {
        try {
            $consulta = "SELECT id_veterinaria, nombre FROM veterinaria";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->execute();
            return $resultado->fetchAll();
        } catch (PDOException $e) {
            echo "Error al listar las veterinarias: " . $e->getMessage();
            return [];
        }
    }
}

//    public function listarVeterinariasRegistradas()
//     {
//         try {
//             $consulta = "SELECT id, nombre FROM veterinaria WHERE estado = 'activo'";
//             $resultado = $this->conexion->prepare($consulta);
//             $resultado->execute();
//             $veterinarias = $resultado->fetchAll(PDO::FETCH_ASSOC);
//             return $veterinarias;
//         } catch (PDOException $e) {
//             echo "Error al listar las veterinarias: " . $e->getMessage();
//             return [];
//         }
//     }
