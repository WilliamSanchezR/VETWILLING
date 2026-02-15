<?php

require_once __DIR__ . '/../../config/database.php';

class Especialidad
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
    // FUNCION PARA REGISTRAR UNA NUEVA ESPECIALIDAD
    public function registrarEspecialidad($data)
    {
        // Insertamos los datos en la base de datos
        try {
            // Validamos si existe la especialidad
            $consultaEsp = "SELECT * FROM especialidad WHERE nombre = :nombre";

            $resultado = $this->conexion->prepare($consultaEsp);
            $resultado->bindParam(':nombre', $data['nombre']);
            $resultado->execute();

            $especialidadExistente = $resultado->fetch(PDO::FETCH_ASSOC);

            if ($especialidadExistente) {
                // La especialidad ya existe
                $consultaVetEsp = "SELECT * FROM esps_por_veterinaria 
                               WHERE id_especialidad = :id_especialidad 
                               AND id_veterinaria = :id_veterinaria";
                $resultadoVetEsp = $this->conexion->prepare($consultaVetEsp);
                $resultadoVetEsp->bindParam(':id_especialidad', $especialidadExistente['id_especialidad'], PDO::PARAM_INT);
                $resultadoVetEsp->bindParam(':id_veterinaria', $data['id_veterinaria'], PDO::PARAM_INT);
                $resultadoVetEsp->execute();
                $vetEspExistente = $resultadoVetEsp->fetch(PDO::FETCH_ASSOC);

                if ($vetEspExistente) {
                    // La especialidad ya está asociada a la veterinaria
                    echo "La especialidad ya está registrada para esta veterinaria.";
                    return false;
                } else {
                    // Asociamos la especialidad existente con la veterinaria
                    return $this->RegistrarEspVeterinaria($especialidadExistente['id_especialidad'], $data['id_veterinaria']);
                }
            } else {
                // La especialidad no existe, la registramos
                $consulta = "INSERT INTO especialidad (nombre, id_servicio_esp) 
                             VALUES (:nombre, :servicio)";

                $resultado = $this->conexion->prepare($consulta);
                $resultado->bindParam(':nombre', $data['nombre']);
                $resultado->bindParam(':servicio', $data['servicio']);
                // Ejecutamos la consulta
                $resultado->execute();

                // Obtenemos el ID de la especialidad recién creada
                $id_especialidad = $this->conexion->lastInsertId();

                // Asociamos la especialidad con la veterinaria
                return $this->RegistrarEspVeterinaria($id_especialidad, $data['id_veterinaria']);
            }
        } catch (PDOException $e) {
            echo "Error al registrar la especialidad: " . $e->getMessage();
            return false;
        }
    }

    // FUNCION PARA LISTAR LAS ESPECIALIDADES REGISTRADAS
    public function listarEspecialidades($id_veterinaria)
    {
        // Listamos las especialidades registradas en la base de datos
        try {
            $consulta = "SELECT ESP.id_especialidad, ESP.nombre As nombre_esp, SERV.nombre AS nombre_ser, SERV.id_servicio, ESPV.estado
                        FROM esps_por_veterinaria ESPV
                        INNER JOIN especialidad ESP On ESPV.id_especialidad = ESP.id_especialidad
                        INNER JOIN servicio SERV On ESP.id_servicio_esp = SERV.id_servicio
                        WHERE ESPV.id_veterinaria = :id_veterinaria";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al listar las especialidades: " . $e->getMessage();
            return [];
        }
    }

    private function RegistrarEspVeterinaria($id_especialidad, $id_veterinaria)
    {
        try {
            $consultaVetEsp = "INSERT INTO esps_por_veterinaria (id_especialidad, id_veterinaria) 
                               VALUES (:id_especialidad, :id_veterinaria)";
            $resultadoVetEsp = $this->conexion->prepare($consultaVetEsp);
            $resultadoVetEsp->bindParam(':id_especialidad', $id_especialidad, PDO::PARAM_INT);
            $resultadoVetEsp->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            return $resultadoVetEsp->execute();
        } catch (PDOException $e) {
            echo "Error al asociar la especialidad con la veterinaria: " . $e->getMessage();
            return false;
        }
    }

    // FUNCION PARA ACTUALIZAR UNA ESPECIALIDAD
    public function actualizarEspecialidad($data)
    {
        try {
            $consulta = "UPDATE especialidad 
                         SET nombre = :nombre_especialidad, id_servicio_esp = :servicio 
                         WHERE id_especialidad = :id_especialidad";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':nombre_especialidad', $data['nombre']);
            $resultado->bindParam(':servicio', $data['servicio']);  
            $resultado->bindParam(':id_especialidad', $data['id_especialidad'], PDO::PARAM_INT);

            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al actualizar la especialidad: " . $e->getMessage();
            return false;
        }
    }

    // FUNCION PARA ELIMINAR UNA ESPECIALIDAD
    public function eliminarEspecialidad($id_especialidad, $id_veterinaria)
    {
        try {
            $consulta = "UPDATE esps_por_veterinaria 
                         SET estado = 'Inactivo'
                         WHERE id_especialidad = :id_especialidad 
                         AND id_veterinaria = :id_veterinaria";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_especialidad', $id_especialidad, PDO::PARAM_INT);
            $resultado->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);

            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al eliminar la especialidad: " . $e->getMessage();
            return false;
        }
    }
}
