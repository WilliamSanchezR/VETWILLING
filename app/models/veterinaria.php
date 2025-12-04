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

    // Función para registrar una nueva veterinaria
    public function registrar($data)
    {
        try {
            $consulta = "INSERT INTO veterinaria (nombre, direccion, ciudad, telefono, email, fecha_creacion, nit, estado) 
                         VALUES (:nombre, :direccion, :ciudad, :telefono, :email, NOW(), :nit, 1)";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':nit', $data['nit']);
            $resultado->bindParam(':nombre', $data['nombre']);
            $resultado->bindParam(':direccion', $data['direccion']);
            $resultado->bindParam(':ciudad', $data['ciudad']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':email', $data['email']);

            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al registrar la veterinaria: " . $e->getMessage();
            return false;
        }
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

?>