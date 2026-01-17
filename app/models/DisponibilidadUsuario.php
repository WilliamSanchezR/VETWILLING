<?php

require_once __DIR__ . '/../../config/database.php';

class DisponibilidadUsuario
{
    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }


    function listaProfesionales($id_veterinaria)
    {
        try {
            $sql = "SELECT 
                    us.id_usuario, 
                    pr.id_profesional, 
                    pr.numero_documento, 
                    CONCAT_WS(' ' , pr.nombres, pr.apellidos) as nombre, 
                    rol.nombre as rol, 
                    GROUP_CONCAT(DISTINCT esp.nombre SEPARATOR ', ') as especialidad
                FROM usuario us
                INNER JOIN rol ON us.id_rol = rol.id_rol
                INNER JOIN profesional pr ON us.id_usuario = pr.id_usuario
                INNER JOIN profesional_veterinaria pv ON pr.id_profesional = pv.id_profesional
                LEFT JOIN profesional_especialidad pe ON us.id_usuario = pe.id_usuario AND pe.estado = 'Activo' AND pe.id_veterinaria = pv.id_veterinaria
                LEFT JOIN especialidad esp ON pe.id_especialidad = esp.id_especialidad 
                WHERE us.estado = 'Activo' AND pv.id_veterinaria = :id_veterinaria AND rol.id_rol != 1 AND rol.id_rol != 5
                GROUP BY pr.id_profesional, pr.nombres, us.id_usuario;";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Usuario::listar -> " . $e->getMessage());
            return [];
        }
    }

    function agregarDisponibilidadAgenda($data)
    {
        try {

            if ($this->validarDisponibilidad(
                $data['id_usuario'],
                $data['id_especialidad'],
                $data['id_veterinaria'],
                $data['dia_semana'],
                $data['hora_inicio'],
                $data['hora_fin']
            ) === false) {
                error_log("El horario se cruza con otro existente");
                return false;
            }

            $sql = "INSERT INTO disponibilidad_usuario 
                    (id_usuario, id_especialidad, id_veterinaria, dia_semana, hora_inicio, hora_fin, duracion_minutos) 
                    VALUES 
                    (:id_usuario, :id_especialidad, :id_veterinaria, :dia_semana, :hora_inicio, :hora_fin, :duracion_minutos)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':id_especialidad', $data['id_especialidad'], PDO::PARAM_STR);
            $stmt->bindParam(':id_veterinaria', $data['id_veterinaria'], PDO::PARAM_INT);
            $stmt->bindParam(':dia_semana', $data['dia_semana'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_inicio', $data['hora_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_fin', $data['hora_fin'], PDO::PARAM_STR);
            $stmt->bindParam(':duracion_minutos', $data['duracion_minutos'], PDO::PARAM_INT);

            $estadoRespuesta  = $stmt->execute();

            if ($estadoRespuesta) {
                // Si se insertó la primera franja, verificar y agregar la segunda si existe
                if (!empty($data['hora_inicio_2']) && !empty($data['hora_fin_2'])) {

                    if ($this->validarDisponibilidad(
                        $data['id_usuario'],
                        $data['id_especialidad'],
                        $data['id_veterinaria'],
                        $data['dia_semana'],
                        $data['hora_inicio_2'],
                        $data['hora_fin_2']
                    ) === false) {
                        error_log("El horario de la segunda franja se cruza con otro existente");
                        return false;
                    }

                    $sql2 = "INSERT INTO disponibilidad_usuario 
                            (id_usuario, id_especialidad, id_veterinaria, dia_semana, hora_inicio, hora_fin, duracion_minutos) 
                            VALUES 
                            (:id_usuario, :id_especialidad, :id_veterinaria, :dia_semana, :hora_inicio, :hora_fin, :duracion_minutos)";

                    $stmt2 = $this->conexion->prepare($sql2);
                    $stmt2->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
                    $stmt2->bindParam(':id_especialidad', $data['id_especialidad'], PDO::PARAM_STR);
                    $stmt2->bindParam(':id_veterinaria', $data['id_veterinaria'], PDO::PARAM_INT);
                    $stmt2->bindParam(':dia_semana', $data['dia_semana'], PDO::PARAM_STR);
                    $stmt2->bindParam(':hora_inicio', $data['hora_inicio_2'], PDO::PARAM_STR);
                    $stmt2->bindParam(':hora_fin', $data['hora_fin_2'], PDO::PARAM_STR);
                    $stmt2->bindParam(':duracion_minutos', $data['duracion_minutos'], PDO::PARAM_INT);

                    $estadoRespuesta2  = $stmt2->execute();

                    return $estadoRespuesta2;
                }
            }

            return $estadoRespuesta;
        } catch (PDOException $e) {
            error_log("Error en DisponibilidadUsuario::agregarDisponibilidadAgenda -> " . $e->getMessage());
            return false;
        }
    }

    // Función para obtener las disponibilidades de un usuario específico en una veterinaria
    function obtenerDisponibilidadesPorUsuario($id_usuario, $id_veterinaria)
    {
        try {
            $sql = "SELECT
                        d.id_disponibilidad,
                        d.dia_semana,
                        CASE d.dia_semana
                            WHEN 1 THEN 'Lunes'
                            WHEN 2 THEN 'Martes'
                            WHEN 3 THEN 'Miércoles'
                            WHEN 4 THEN 'Jueves'
                            WHEN 5 THEN 'Viernes'
                            WHEN 6 THEN 'Sábado'
                            WHEN 7 THEN 'Domingo'
                        END AS dia,
                        es.nombre as especialidad,
                        es.id_especialidad,
                        d.hora_inicio,
                        d.hora_fin,
                        d.duracion_minutos as duracion
                    FROM disponibilidad_usuario d
                    INNER JOIN especialidad es ON d.id_especialidad = es.id_especialidad 
                    WHERE d.estado = 'Activo' AND d.id_usuario = :id_usuario AND d.id_veterinaria = :id_veterinaria
                    ORDER BY d.dia_semana ASC, d.hora_inicio ASC;";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DisponibilidadUsuario::obtenerDisponibilidadesPorUsuario -> " . $e->getMessage());
            return [];
        }
    }

    // Función para validar si una nueva disponibilidad se cruza con una existente
    function validarDisponibilidad($id_usuario, $id_especialidad, $id_veterinaria, $dia_semana, $hora_inicio, $hora_fin)
    {
        try {
            $sqlValid = "
                SELECT d.id_disponibilidad
                FROM disponibilidad_usuario d
                WHERE id_usuario = :id_usuario
                AND id_especialidad = :id_especialidad
                AND id_veterinaria = :id_veterinaria
                AND dia_semana = :dia_semana
                AND estado = 'Activo'
                AND :hora_inicio < hora_fin
                AND :hora_fin > hora_inicio
                LIMIT 1;";

            $stmtValid = $this->conexion->prepare($sqlValid);
            $stmtValid->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtValid->bindParam(':id_especialidad', $id_especialidad, PDO::PARAM_STR);
            $stmtValid->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $stmtValid->bindParam(':dia_semana', $dia_semana, PDO::PARAM_STR);
            $stmtValid->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
            $stmtValid->bindParam(':hora_fin', $hora_fin, PDO::PARAM_STR);
            $stmtValid->execute();


            if ($stmtValid->fetch()) {
                error_log("El horario se cruza con otro existente");
                return false;
            } else {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Error en DisponibilidadUsuario::validarDisponibilidad -> " . $e->getMessage());
            return false;
        }
    }

    // Función para validar si una edición de disponibilidad se cruza con una existente
    function validarDisponibilidadEdit($id_disponibilidad, $id_usuario, $id_especialidad, $id_veterinaria, $dia_semana, $hora_inicio, $hora_fin)
    {
        try {
            $sqlValid = "
                SELECT d.id_disponibilidad
                FROM disponibilidad_usuario d
                WHERE id_usuario = :id_usuario
                AND id_especialidad = :id_especialidad
                AND id_veterinaria = :id_veterinaria
                AND dia_semana = :dia_semana
                AND estado = 'Activo'
                AND :hora_inicio < hora_fin
                AND :hora_fin > hora_inicio
                AND d.id_disponibilidad != :id_disponibilidad
                LIMIT 1;";

            $stmtValid = $this->conexion->prepare($sqlValid);
            $stmtValid->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtValid->bindParam(':id_especialidad', $id_especialidad, PDO::PARAM_STR);
            $stmtValid->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $stmtValid->bindParam(':dia_semana', $dia_semana, PDO::PARAM_STR);
            $stmtValid->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
            $stmtValid->bindParam(':hora_fin', $hora_fin, PDO::PARAM_STR);
            $stmtValid->bindParam(':id_disponibilidad', $id_disponibilidad, PDO::PARAM_INT);
            $stmtValid->execute();


            if ($stmtValid->fetch()) {
                error_log("El horario se cruza con otro existente");
                return false;
            } else {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Error en DisponibilidadUsuario::validarDisponibilidad -> " . $e->getMessage());
            return false;
        }
    }

    // Función para eliminar una disponibilidad de la agenda
    function eliminarAgenda($id_disponibilidad)
    {
        try {
            $sql = "UPDATE disponibilidad_usuario 
                    SET estado = 'Inactivo' 
                    WHERE id_disponibilidad = :id_disponibilidad";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_disponibilidad', $id_disponibilidad, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en DisponibilidadUsuario::eliminarAgenda -> " . $e->getMessage());
            return false;
        }
    }

    // Función para editar una disponibilidad de la agenda
    function editarDisponibilidadAgenda($data)
    {
        try {

            if ($this->validarDisponibilidadEdit(
                $data['id_disponibilidad'],
                $data['id_usuario'],
                $data['id_especialidad'],
                $data['id_veterinaria'],
                $data['dia_semana'],
                $data['hora_inicio'],
                $data['hora_fin']
            ) === false) {
                error_log("El horario se cruza con otro existente");
                print_r("El horario se cruza con otro existente");
                return false;
            }

            $sql = "UPDATE disponibilidad_usuario 
                    SET id_especialidad = :id_especialidad,
                        dia_semana = :dia_semana,
                        hora_inicio = :hora_inicio,
                        hora_fin = :hora_fin,
                        duracion_minutos = :duracion_minutos
                    WHERE id_disponibilidad = :id_disponibilidad";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_disponibilidad', $data['id_disponibilidad'], PDO::PARAM_INT);
            $stmt->bindParam(':id_especialidad', $data['id_especialidad'], PDO::PARAM_STR);
            $stmt->bindParam(':dia_semana', $data['dia_semana'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_inicio', $data['hora_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_fin', $data['hora_fin'], PDO::PARAM_STR);
            $stmt->bindParam(':duracion_minutos', $data['duracion_minutos'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en DisponibilidadUsuario::editarDisponibilidadAgenda -> " . $e->getMessage());
            return false;
        }
    }
}
