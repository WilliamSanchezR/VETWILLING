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

            if (!empty($data['hora_inicio']) && !empty($data['hora_fin'])) {
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

                if (!$estadoRespuesta) {
                    error_log("Error al insertar la primera franja: " . implode(" | ", $stmt->errorInfo()));
                    return false;
                }
            }
            // Si se insertó la primera franja, verificar y agregar la segunda si existe
            if (!empty($data['hora_inicio_seccond']) && !empty($data['hora_fin_seccond'])) {

                if ($this->validarDisponibilidad(
                    $data['id_usuario'],
                    $data['id_especialidad'],
                    $data['id_veterinaria'],
                    $data['dia_semana'],
                    $data['hora_inicio_seccond'],
                    $data['hora_fin_seccond']
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
                $stmt2->bindParam(':hora_inicio', $data['hora_inicio_seccond'], PDO::PARAM_STR);
                $stmt2->bindParam(':hora_fin', $data['hora_fin_seccond'], PDO::PARAM_STR);
                $stmt2->bindParam(':duracion_minutos', $data['duracion_minutos'], PDO::PARAM_INT);

                $estadoRespuesta2  = $stmt2->execute();

                return $estadoRespuesta2;
            }

            return true;
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

    // Función para obtener especialidades de un usuario
    public function obtenerEspecialidadesUsuario($id_usuario, $id_veterinaria)
    {
        try {
            $sql = "SELECT DISTINCT
                        esp.id_especialidad,
                        esp.nombre
                    FROM especialidad esp
                    INNER JOIN profesional_especialidad pe ON esp.id_especialidad = pe.id_especialidad
                    WHERE pe.id_usuario = :id_usuario 
                    AND pe.id_veterinaria = :id_veterinaria
                    AND pe.estado = 'Activo'
                    ORDER BY esp.nombre ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DisponibilidadUsuario::obtenerEspecialidadesUsuario -> " . $e->getMessage());
            return [];
        }
    }

    // Función para obtener la veterinaria asociada a un usuario
    public function obtenerVeterinariaPorUsuario($id_usuario)
    {
        try {
            $sql = "SELECT pv.id_veterinaria
                    FROM profesional_veterinaria pv
                    INNER JOIN profesional pr ON pv.id_profesional = pr.id_profesional
                    WHERE pr.id_usuario = :id_usuario
                    AND pv.estado = 'Activo'
                    ORDER BY pv.id_prof_veterinaria DESC
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['id_veterinaria'] ?? null;
        } catch (PDOException $e) {
            error_log("Error en DisponibilidadUsuario::obtenerVeterinariaPorUsuario -> " . $e->getMessage());
            return null;
        }
    }

    /**
     * Valida si un horario de cita está dentro de la disponibilidad del veterinario
     * @param int $id_usuario ID del veterinario
     * @param int $id_veterinaria ID de la veterinaria
     * @param string $fecha_hora_inicio Fecha y hora de inicio de la cita (Y-m-d H:i:s)
     * @param string $fecha_hora_fin Fecha y hora de fin de la cita (Y-m-d H:i:s)
     * @return array ['valido' => bool, 'mensaje' => string, 'rangos_disponibles' => array]
     */
    public function validarCitaDentroDisponibilidad($id_usuario, $id_veterinaria, $fecha_hora_inicio, $fecha_hora_fin)
    {
        try {
            // Obtener el día de la semana (1=Lunes, 7=Domingo)
            $datetime = new DateTime($fecha_hora_inicio);
            $dia_semana_num = (int)$datetime->format('N'); // 1=Lunes, 7=Domingo

            // Extraer solo la hora de inicio y fin
            $hora_inicio_cita = $datetime->format('H:i:s');
            $hora_fin_cita = (new DateTime($fecha_hora_fin))->format('H:i:s');

            // Buscar disponibilidades para ese día y usuario
            $sql = "SELECT 
                        du.hora_inicio,
                        du.hora_fin,
                        esp.nombre as especialidad
                    FROM disponibilidad_usuario du
                    LEFT JOIN especialidad esp ON du.id_especialidad = esp.id_especialidad
                    WHERE du.id_usuario = :id_usuario
                    AND du.id_veterinaria = :id_veterinaria
                    AND du.dia_semana = :dia_semana
                    ORDER BY du.hora_inicio ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $stmt->bindParam(':dia_semana', $dia_semana_num, PDO::PARAM_INT);
            $stmt->execute();

            $disponibilidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si no hay disponibilidades para ese día
            if (empty($disponibilidades)) {
                $dias = ['', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
                return [
                    'valido' => false,
                    'mensaje' => 'No hay disponibilidad configurada para el día ' . $dias[$dia_semana_num],
                    'rangos_disponibles' => []
                ];
            }

            // Validar si la cita está dentro de algún rango de disponibilidad
            $dentroDeRango = false;
            foreach ($disponibilidades as $disp) {
                $hora_inicio_disp = $disp['hora_inicio'];
                $hora_fin_disp = $disp['hora_fin'];

                // Verificar si la cita está completamente dentro del rango
                if ($hora_inicio_cita >= $hora_inicio_disp && $hora_fin_cita <= $hora_fin_disp) {
                    $dentroDeRango = true;
                    break;
                }
            }

            if (!$dentroDeRango) {
                // Formatear rangos disponibles para el mensaje
                $rangos_formateados = array_map(function ($disp) {
                    $inicio = date('h:i A', strtotime($disp['hora_inicio']));
                    $fin = date('h:i A', strtotime($disp['hora_fin']));
                    $especialidad = $disp['especialidad'] ? " ({$disp['especialidad']})" : "";
                    return "{$inicio} - {$fin}{$especialidad}";
                }, $disponibilidades);

                $mensaje = "El horario seleccionado está fuera de la disponibilidad del veterinario.\n\n";
                $mensaje .= "Rangos disponibles:\n" . implode("\n", $rangos_formateados);

                return [
                    'valido' => false,
                    'mensaje' => $mensaje,
                    'rangos_disponibles' => $disponibilidades
                ];
            }

            return [
                'valido' => true,
                'mensaje' => 'La cita está dentro del horario disponible',
                'rangos_disponibles' => $disponibilidades
            ];
        } catch (Exception $e) {
            error_log("Error en DisponibilidadUsuario::validarCitaDentroDisponibilidad -> " . $e->getMessage());
            return [
                'valido' => false,
                'mensaje' => 'Error al validar disponibilidad: ' . $e->getMessage(),
                'rangos_disponibles' => []
            ];
        }
    }
}
