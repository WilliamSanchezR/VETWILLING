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

    // =========================================
    //  FUNCIONES CRUD
    // =========================================

    // FUNCION PARA REGISTRAR UNA NUEVA VETERINARIA
    public function registrar($data)
    {
        // Insertamos los datos en la base de datos
        try {
            $consulta = "INSERT INTO veterinaria (nombre, direccion, ciudad, telefono, email, fecha_creacion, nit, estado, foto) 
                         VALUES (:nombre, :direccion, :ciudad, :telefono, :email, NOW(), :nit, 1, :foto)";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':foto', $data['foto']);
            $resultado->bindParam(':nit', $data['nit']);
            $resultado->bindParam(':nombre', $data['nombre']);
            $resultado->bindParam(':direccion', $data['direccion']);
            $resultado->bindParam(':ciudad', $data['ciudad']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':email', $data['email']);

            // Ejecutamos la consulta
            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al registrar la veterinaria: " . $e->getMessage();
            return false;
        }
    }

    // FUNCION PARA LISTAR LAS VETERINARIAS REGISTRADAS
    public function listar()
    {
        // Listamos las veterinarias registradas en la base de datos
        try {
            $consulta = "SELECT 
                            id_veterinaria, 
                            nit, 
                            nombre, 
                            direccion, 
                            ciudad, 
                            telefono, 
                            email, 
                            foto,
                            fecha_creacion, 
                            estado
                         FROM veterinaria";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->execute();
            // Devolvemos los resultados como un array asociativo
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Veterinaria::listar -> " . $e->getMessage());
            return [];
        }
    }

    // FUNCION PARA LISTAR LAS VETERINARIAS REGISTRADAS POR ID Y NOMBRE
    public function consultarVeterinariasRegistradas($id)
    {
        try {
            $consulta = "SELECT id_veterinaria, nit, nombre, direccion, ciudad, telefono, email, estado, foto FROM veterinaria WHERE id_veterinaria = :id";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id', $id);
            $resultado->execute();
            return $resultado->fetch();
        } catch (PDOException $e) {
            echo "Error al listar las veterinarias: " . $e->getMessage();
            return [];
        }
    }

    // FUNCION PARA ACTUALIZAR LOS DATOS DE LA VETERINARIA
    public function actualizar($data)
    {
        // Actualizamos los datos de la veterinaria en la base de datos
        try {
            $consulta = "UPDATE veterinaria 
                         SET foto = :foto,
                             nombre = :nombre, 
                             direccion = :direccion, 
                             telefono = :telefono, 
                             email = :email, 
                             nit = :nit, 
                             ciudad = :ciudad, 
                             estado = :estado
                         WHERE id_veterinaria = :id_veterinaria";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':foto', $data['foto']);
            $resultado->bindParam(':nit', $data['nit']);
            $resultado->bindParam(':nombre', $data['nombre']);
            $resultado->bindParam(':direccion', $data['direccion']);
            $resultado->bindParam(':ciudad', $data['ciudad']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':email', $data['email']);
            $resultado->bindParam(':estado', $data['estado']);
            $resultado->bindParam(':id_veterinaria', $data['id_veterinaria']);

            // Ejecutamos la consulta
            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al actualizar la veterinaria: " . $e->getMessage();
            return false;
        }
    }

    // FUNCION PARA ELIMINAR UNA VETERINARIA
    public function eliminar($id)
    {
        // Eliminamos la veterinaria de la base de datos
        try {
            $consulta = "UPDATE veterinaria SET estado = 'Eliminado' WHERE id_veterinaria = :id";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id', $id);
            // Ejecutamos la consulta
            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al eliminar la veterinaria: " . $e->getMessage();
            return false;
        }
    }
}
