<?php

require_once __DIR__ . '/../../config/database.php';

class Servicio
{
     private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    // =========================================
    //  FUNCIONES CRUD
    // =========================================
    // FUNCION PARA REGISTRAR UN NUEVO SERVICIO
    public function crearServicio($data)
    {
        // Insertamos los datos en la base de datos
        try {
            // Validamos si existe el servicio
            $consultaEsp = "SELECT * FROM servicio WHERE nombre = :nombre AND id_veterinaria = :id_veterinaria";

            $resultado = $this->conexion->prepare($consultaEsp);
            $resultado->bindParam(':nombre', $data['nombre']);
            $resultado->bindParam(':id_veterinaria', $data['id_veterinaria']);
            $resultado->execute();

            $servicioExiste = $resultado->fetch(PDO::FETCH_ASSOC);

            if ($servicioExiste) {
                // El servicio ya existe
                echo "El servicio ya está registrado para esta veterinaria.";
               return false;
            } else {
                // El servicio no existe, lo registramos
                $consulta = "INSERT INTO servicio (nombre, descripcion, costo, id_veterinaria) 
                             VALUES (:nombre, :descripcion, :costo, :id_veterinaria)";

                $resultado = $this->conexion->prepare($consulta);
                $resultado->bindParam(':nombre', $data['nombre']);
                $resultado->bindParam(':descripcion', $data['descripcion']);
                $resultado->bindParam(':costo', $data['costo']);
                $resultado->bindParam(':id_veterinaria', $data['id_veterinaria']);
                
                // Ejecutamos la consulta
                return $resultado->execute();

            }
        } catch (PDOException $e) {
            echo "Error al registrar el servicio: " . $e->getMessage();
            return false;
        }
    }

}