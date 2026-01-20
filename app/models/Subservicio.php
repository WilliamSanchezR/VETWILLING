<?php

require_once __DIR__ . '/../../config/database.php';

class Subservicio
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
    // FUNCION PARA REGISTRAR UN NUEVO SUBSERVICIO
    public function crearSubservicio($data)
    {
        // Insertamos los datos en la base de datos
        try {
            // Validamos si existe el subservicio
            $consultaEsp = "SELECT * FROM subservicios WHERE nombre = :nombre AND id_servicio = :id_servicio";

            $resultado = $this->conexion->prepare($consultaEsp);
            $resultado->bindParam(':nombre', $data['nombre']);
            $resultado->bindParam(':id_servicio', $data['id_servicio']);
            $resultado->execute();

            $subservicioExiste = $resultado->fetch(PDO::FETCH_ASSOC);

            if ($subservicioExiste) {
                // El subservicio ya existe
                echo "El subservicio ya está registrado para este servicio.";
                return false;
            } else {
                // El subservicio no existe, lo registramos
                $consulta = "INSERT INTO subservicios (nombre, descripcion, costo, id_servicio) 
                             VALUES (:nombre, :descripcion, :costo, :id_servicio)";

                $resultado = $this->conexion->prepare($consulta);
                $resultado->bindParam(':nombre', $data['nombre']);
                $resultado->bindParam(':descripcion', $data['descripcion']);
                $resultado->bindParam(':costo', $data['costo']);
                $resultado->bindParam(':id_servicio', $data['id_servicio']);        

                // Ejecutamos la consulta
                return $resultado->execute();
            }
        } catch (PDOException $e) {
            echo "Error al registrar el subservicio: " . $e->getMessage();
            return false;
        }
    }

    // FUNCION PARA OBTENER LOS SUBSERVICIOS DE UNA VETERINARIA
    public function obtenerSubserviciosPorVeterinaria($id_veterinaria)
    {
        try {
            $consulta = "SELECT sub.id_subservicio, sub.nombre, se.nombre AS servicio, sub.costo, sub.descripcion, sub.estado
                        FROM subservicios sub
                        INNER JOIN servicio se ON se.id_servicio = sub.id_servicio
                        WHERE se.id_veterinaria = :id_veterinaria AND se.estado = 'Activo'";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_veterinaria', $id_veterinaria);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener los subservicios: " . $e->getMessage();
            return [];
        }
    }

    // FUNCION CONSULTAR SERVICIO POR ID
    public function obtenerSubservicioPorId($id_subservicio)
    {
        try {
            $consulta = "SELECT * FROM subservicios WHERE id_subservicio = :id_subservicio";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_subservicio', $id_subservicio);
            $resultado->execute();

            return $resultado->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener el subservicio: " . $e->getMessage();
            return null;
        }
    }

    public function actualizarSubservicio($data)
    {
        try {
            $consulta = "UPDATE subservicios 
                         SET nombre = :nombre, id_servicio = :id_servicio, descripcion = :descripcion, costo = :costo, estado = :estado
                         WHERE id_subservicio = :id_subservicio";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':nombre', $data['nombre']);
            $resultado->bindParam(':id_servicio', $data['id_servicio']);
            $resultado->bindParam(':descripcion', $data['descripcion']);
            $resultado->bindParam(':costo', $data['costo']);
            $resultado->bindParam(':estado', $data['estado']);
            $resultado->bindParam(':id_subservicio', $data['id_subservicio']);

            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al actualizar el subservicio: " . $e->getMessage();
            return false;
        }
    }

    public function eliminarSubservicio($id_subservicio)
    {
        try {
            $consulta = "UPDATE subservicios 
                         SET estado = :estado, fecha_modificacion = NOW()
                         WHERE id_subservicio = :id_subservicio";
            $estado = 'Inactivo';
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':estado', $estado);
            $resultado->bindParam(':id_subservicio', $id_subservicio);

            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al eliminar el subservicio: " . $e->getMessage();
            return false;
        }
    }
}
