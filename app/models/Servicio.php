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
                $consulta = "INSERT INTO servicio (nombre, descripcion, id_veterinaria) 
                             VALUES (:nombre, :descripcion, :id_veterinaria)";

                $resultado = $this->conexion->prepare($consulta);
                $resultado->bindParam(':nombre', $data['nombre']);
                $resultado->bindParam(':descripcion', $data['descripcion']);
                $resultado->bindParam(':id_veterinaria', $data['id_veterinaria']);

                // Ejecutamos la consulta
                $respuesta = $resultado->execute();

                // Validamos si existen horarios para guardar
                if ($respuesta && !empty($data['horarios'])) {
                    $id_servicio = $this->conexion->lastInsertId();

                    $horarios = json_decode($data['horarios'], true);

                    foreach ($horarios as $horario) {
                        if (isset($horario['horario_1'])) {
                            $consultaHorario = "INSERT INTO horario_servicio (id_servicio, dia_semana, hora_inicio, hora_fin) 
                                             VALUES (:id_servicio, :dia_semana, :hora_inicio, :hora_fin)";

                            $resultadoHorario = $this->conexion->prepare($consultaHorario);
                            $resultadoHorario->bindParam(':id_servicio', $id_servicio);
                            $resultadoHorario->bindParam(':dia_semana', $horario['dia']);
                            $resultadoHorario->bindParam(':hora_inicio', $horario['horario_1']['hora_inicio']);
                            $resultadoHorario->bindParam(':hora_fin', $horario['horario_1']['hora_fin']);

                            $resultadoHorario->execute();
                        }

                        // validmos si hay un segundo horario
                        if (isset($horario['horario_2'])) {
                            $consultaHorario2 = "INSERT INTO horario_servicio (id_servicio, dia_semana, hora_inicio, hora_fin) 
                                                 VALUES (:id_servicio, :dia_semana, :hora_inicio, :hora_fin)";

                            $resultadoHorario2 = $this->conexion->prepare($consultaHorario2);
                            $resultadoHorario2->bindParam(':id_servicio', $id_servicio);
                            $resultadoHorario2->bindParam(':dia_semana', $horario['dia']);
                            $resultadoHorario2->bindParam(':hora_inicio', $horario['horario_2']['hora_inicio']);
                            $resultadoHorario2->bindParam(':hora_fin', $horario['horario_2']['hora_fin']);

                            $resultadoHorario2->execute();
                        }
                    }
                }
                return $respuesta;
            }
        } catch (PDOException $e) {
            echo "Error al registrar el servicio: " . $e->getMessage();
            return false;
        }
    }

    // FUNCION PARA OBTENER LOS SERVICIOS DE UNA VETERINARIA
    public function obtenerServiciosPorVeterinaria($id_veterinaria)
    {
        try {
            $consulta = "SELECT 
                            SERV.id_servicio,
                            SERV.nombre,
                            SERV.estado,
                            GROUP_CONCAT(
                                CONCAT(
                                    CASE HS.dia_semana
                                        WHEN 1 THEN 'Lunes'
                                        WHEN 2 THEN 'Martes'
                                        WHEN 3 THEN 'Miércoles'
                                        WHEN 4 THEN 'Jueves'
                                        WHEN 5 THEN 'Viernes'
                                        WHEN 6 THEN 'Sábado'
                                        WHEN 7 THEN 'Domingo'
                                    END,
                                    ' ',
                                    TIME_FORMAT(HS.hora_inicio, '%h:%i %p'),
                                    ' - ',
                                    TIME_FORMAT(HS.hora_fin, '%h:%i %p')
                                )
                                ORDER BY HS.dia_semana ASC, HS.hora_inicio ASC
                                SEPARATOR ' | '
                            ) AS horarios
                        FROM servicio SERV
                        LEFT JOIN horario_servicio HS 
                            ON SERV.id_servicio = HS.id_servicio 
                            AND HS.estado = 'Activo'
                        WHERE SERV.id_veterinaria = :id_veterinaria
                        GROUP BY SERV.id_servicio, SERV.nombre, SERV.estado;";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_veterinaria', $id_veterinaria);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener los servicios: " . $e->getMessage();
            return [];
        }
    }

    // FUNCION PARA OBTENER LOS SERVICIOS DE UNA VETERINARIA
    public function obtenerServiciosPorVeterinariaActivos($id_veterinaria)
    {
        try {
            $consulta = "SELECT * FROM servicio WHERE id_veterinaria = :id_veterinaria AND estado = 'Activo'";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_veterinaria', $id_veterinaria);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener los servicios: " . $e->getMessage();
            return [];
        }
    }

    // FUNCION PARA OBTENER LOS SERVICIOS ACTIVOS DE UNA VETERINARIA
    public function obtenerServiciosActivosPorVeterinaria($id_veterinaria)
    {
        try {
            $consulta = "SELECT se.id_servicio, se.nombre FROM servicio se 
                        WHERE se.id_veterinaria = :id_veterinaria AND se.estado = 'Activo';";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_veterinaria', $id_veterinaria);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener los servicios activos: " . $e->getMessage();
            return [];
        }
    }

    // FUNCION CONSULTAR SERVICIO POR ID
    public function obtenerServicioPorId($id_servicio)
    {
        try {
            $consulta = "SELECT * FROM servicio WHERE id_servicio = :id_servicio";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_servicio', $id_servicio);
            $resultado->execute();

            return $resultado->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener el servicio: " . $e->getMessage();
            return null;
        }
    }

    // FUNCION PARA ACTUALIZAR UN SERVICIO
    public function actualizarServicio($data)
    {
        try {
            $consulta = "UPDATE servicio 
                         SET nombre = :nombre, descripcion = :descripcion, estado = :estado
                         WHERE id_servicio = :id_servicio";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':nombre', $data['nombre']);
            $resultado->bindParam(':descripcion', $data['descripcion']);
            $resultado->bindParam(':estado', $data['estado']);
            $resultado->bindParam(':id_servicio', $data['id_servicio']);

            $respuesta = $resultado->execute();

            if ($respuesta && !empty($data['horarios'])) {

                $horarios = json_decode($data['horarios'], true);

                foreach ($horarios as $horario) {
                    if (isset($horario['horario_1'])) {
                        $consultaHorario = "INSERT INTO horario_servicio (id_servicio, dia_semana, hora_inicio, hora_fin) 
                                             VALUES (:id_servicio, :dia_semana, :hora_inicio, :hora_fin)";

                        $resultadoHorario = $this->conexion->prepare($consultaHorario);
                        $resultadoHorario->bindParam(':id_servicio', $data['id_servicio']);
                        $resultadoHorario->bindParam(':dia_semana', $horario['dia']);
                        $resultadoHorario->bindParam(':hora_inicio', $horario['horario_1']['hora_inicio']);
                        $resultadoHorario->bindParam(':hora_fin', $horario['horario_1']['hora_fin']);

                        $resultadoHorario->execute();
                    }
                    // validmos si hay un segundo horario
                    if (isset($horario['horario_2'])) {
                        $consultaHorario2 = "INSERT INTO horario_servicio (id_servicio, dia_semana, hora_inicio, hora_fin) 
                                                 VALUES (:id_servicio, :dia_semana, :hora_inicio, :hora_fin)";

                        $resultadoHorario2 = $this->conexion->prepare($consultaHorario2);
                        $resultadoHorario2->bindParam(':id_servicio', $data['id_servicio']);
                        $resultadoHorario2->bindParam(':dia_semana', $horario['dia']);
                        $resultadoHorario2->bindParam(':hora_inicio', $horario['horario_2']['hora_inicio']);
                        $resultadoHorario2->bindParam(':hora_fin', $horario['horario_2']['hora_fin']);

                        $resultadoHorario2->execute();
                    }
                }
            }

            return $respuesta;
        } catch (PDOException $e) {
            echo "Error al actualizar el servicio: " . $e->getMessage();
            return false;
        }
    }

    // FUNCION PARA ELIMINAR UN SERVICIO (CAMBIO DE ESTADO A INACTIVO)
    public function eliminarServicio($id_servicio)
    {
        try {
            $consulta = "UPDATE servicio 
                         SET estado = :estado, fecha_modificacion = NOW()
                         WHERE id_servicio = :id_servicio";
            $estado = 'Inactivo';
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':estado', $estado);
            $resultado->bindParam(':id_servicio', $id_servicio);

            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al eliminar el servicio: " . $e->getMessage();
            return false;
        }
    }

    // FUNCION PARA OBTENER LOS HORARIOS DE UN SERVICIO
    public function obtenerHorariosPorServicio($id_servicio)
    {
        try {
            $consulta = "SELECT * FROM horario_servicio WHERE id_servicio = :id_servicio AND estado = 'Activo' ORDER BY dia_semana ASC";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_servicio', $id_servicio);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener los horarios del servicio: " . $e->getMessage();
            return [];
        }
    }

    public function eliminarHorariosPorServicio($id_servicio)
    {
        try {
            $consulta = "UPDATE horario_servicio 
                         SET estado = :estado, fecha_modificacion = NOW()
                         WHERE id_horario_servicio = :id_horario_servicio";
            $estado = 'Inactivo';
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':estado', $estado);
            $resultado->bindParam(':id_horario_servicio', $id_servicio);

            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al eliminar los horarios del servicio: " . $e->getMessage();
            return false;
        }
    }

    // FUNCION PARA OBTENER LOS SERVICIOS ACTIVOS DE UNA VETERINARIA
    public function obtenerServiciosActivos($id_veterinaria)
    {
        try {
            // CONSULTA PARA TRAER EL ID DEL SERVICIO EL NOMBRE RELACIONADOS A LA VETERINARIA ACTIVA 
            $consulta = "SELECT se.id_servicio, se.nombre
                        FROM servicio se 
                        WHERE se.id_veterinaria = :id_veterinaria AND se.estado = 'Activo'
                        GROUP BY se.id_servicio;";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_veterinaria', $id_veterinaria);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener los servicios activos: " . $e->getMessage();
            return [];
        }
    }
}
