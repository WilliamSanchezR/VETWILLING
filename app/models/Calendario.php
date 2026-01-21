<?php

require_once __DIR__ . '/../../config/database.php';

class Calendario
{
    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    // =========================================
    //  FUNCIONES DE CONSULTA PARA EL CALENDARIO
    // =========================================

    // FUNCION PARA OBTENER LISTA DE PROPIETARIOS ACTIVOS
    public function obtenerPropietarios()
    {
        try {
            $consulta = "SELECT 
                            id_propietario,
                            CONCAT(nombres, ' ', apellidos) as nombre_completo,
                            nombres,
                            apellidos,
                            tipo_documento,
                            numero_documento,
                            telefono,
                            id_veterinaria
                        FROM propietario
                        ORDER BY nombres ASC, apellidos ASC";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Calendario::obtenerPropietarios -> " . $e->getMessage());
            return [];
        }
    }

    // FUNCION PARA OBTENER MASCOTAS DE UN PROPIETARIO ESPECIFICO
    public function obtenerMascotasPorPropietario($id_propietario)
    {
        try {
            $consulta = "SELECT 
                            p.id_paciente,
                            p.nombre,
                            p.especie,
                            p.raza,
                            p.edad,
                            p.sexo,
                            p.img_mascota,
                            p.id_propietario,
                            CONCAT(p.nombre, ' (', p.especie, ' - ', p.raza, ')') as nombre_descriptivo
                        FROM paciente p
                        WHERE p.id_propietario = :id_propietario
                        ORDER BY p.nombre ASC";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_propietario', $id_propietario, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Calendario::obtenerMascotasPorPropietario -> " . $e->getMessage());
            return [];
        }
    }

    // FUNCION PARA OBTENER TODOS LOS SERVICIOS DISPONIBLES
    public function obtenerServicios()
    {
        try {
            $consulta = "SELECT 
                            id_servicio,
                            nombre,
                            descripcion
                        FROM servicio
                        ORDER BY 
                            CASE WHEN nombre = 'Otro' THEN 1 ELSE 0 END,
                            nombre ASC";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Calendario::obtenerServicios -> " . $e->getMessage());
            return [];
        }
    }

    // FUNCION PARA OBTENER TODAS LAS MASCOTAS CON SU PROPIETARIO
    public function obtenerTodasLasMascotas()
    {
        try {
            $consulta = "SELECT 
                            p.id_paciente,
                            p.nombre,
                            p.especie,
                            p.raza,
                            p.edad,
                            p.sexo,
                            p.img_mascota,
                            p.id_propietario,
                            pr.nombres as propietario_nombres,
                            pr.apellidos as propietario_apellidos,
                            CONCAT(pr.nombres, ' ', pr.apellidos) as propietario_completo,
                            CONCAT(p.nombre, ' (', pr.nombres, ' ', pr.apellidos, ')') as nombre_con_propietario
                        FROM paciente p
                        INNER JOIN propietario pr ON p.id_propietario = pr.id_propietario
                        ORDER BY p.nombre ASC";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Calendario::obtenerTodasLasMascotas -> " . $e->getMessage());
            return [];
        }
    }

    // FUNCION PARA OBTENER INFORMACION COMPLETA DE UN AGENDAMIENTO
    public function obtenerAgendamientoCompleto($id_agendamiento)
    {
        try {
            $consulta = "SELECT 
                            a.id_agendamiento,
                            a.tipo,
                            a.observaciones,
                            a.fecha_hora,
                            a.fecha_hora_fin,
                            a.estado,
                            
                            -- Datos del propietario
                            a.id_propietario,
                            pr.nombres as propietario_nombres,
                            pr.apellidos as propietario_apellidos,
                            CONCAT(pr.nombres, ' ', pr.apellidos) as propietario_completo,
                            pr.telefono as propietario_telefono,
                            pr.numero_documento as propietario_documento,
                            
                            -- Datos de la mascota
                            a.id_paciente,
                            pac.nombre as mascota_nombre,
                            pac.especie as mascota_especie,
                            pac.raza as mascota_raza,
                            pac.edad as mascota_edad,
                            pac.sexo as mascota_sexo,
                            
                            -- Datos del servicio
                            a.id_servicio,
                            s.nombre as servicio_nombre,
                            s.descripcion as servicio_descripcion,
                            s.costo as servicio_costo,
                            
                            -- Datos del veterinario
                            a.id_usuario,
                            u.nombres as veterinario_nombres,
                            u.apellidos as veterinario_apellidos,
                            CONCAT(u.nombres, ' ', u.apellidos) as veterinario_completo
                        FROM agendamiento a
                        LEFT JOIN propietario pr ON a.id_propietario = pr.id_propietario
                        LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
                        LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                        LEFT JOIN usuario u ON a.id_usuario = u.id_usuario
                        WHERE a.id_agendamiento = :id_agendamiento
                        LIMIT 1";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Calendario::obtenerAgendamientoCompleto -> " . $e->getMessage());
            return null;
        }
    }

    // FUNCION PARA LISTAR AGENDAMIENTOS CON FILTROS OPCIONALES
    public function listarAgendamientosCompletos($filtros = [])
    {
        try {
            $consulta = "SELECT 
                            a.id_agendamiento,
                            a.tipo,
                            a.observaciones,
                            a.fecha_hora,
                            a.fecha_hora_fin,
                            a.estado,
                            
                            -- Propietario
                            a.id_propietario,
                            CONCAT(pr.nombres, ' ', pr.apellidos) as propietario_nombre,
                            pr.telefono as propietario_telefono,
                            
                            -- Mascota
                            a.id_paciente,
                            pac.nombre as mascota_nombre,
                            pac.especie as mascota_especie,
                            
                            -- Servicio
                            a.id_servicio,
                            s.nombre as servicio_nombre,
                            s.costo as servicio_costo,
                            
                            -- Veterinario
                            CONCAT(u.nombres, ' ', u.apellidos) as veterinario_nombre
                        FROM agendamiento a
                        LEFT JOIN propietario pr ON a.id_propietario = pr.id_propietario
                        LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
                        LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                        LEFT JOIN usuario u ON a.id_usuario = u.id_usuario
                        WHERE 1=1";

            // Agregar filtros dinamicos
            $params = [];

            if (!empty($filtros['fecha_inicio'])) {
                $consulta .= " AND a.fecha_hora >= :fecha_inicio";
                $params[':fecha_inicio'] = $filtros['fecha_inicio'];
            }

            if (!empty($filtros['fecha_fin'])) {
                $consulta .= " AND a.fecha_hora <= :fecha_fin";
                $params[':fecha_fin'] = $filtros['fecha_fin'];
            }

            if (!empty($filtros['id_propietario'])) {
                $consulta .= " AND a.id_propietario = :id_propietario";
                $params[':id_propietario'] = $filtros['id_propietario'];
            }

            if (!empty($filtros['id_paciente'])) {
                $consulta .= " AND a.id_paciente = :id_paciente";
                $params[':id_paciente'] = $filtros['id_paciente'];
            }

            if (!empty($filtros['estado'])) {
                $consulta .= " AND a.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }

            $consulta .= " ORDER BY a.fecha_hora ASC";

            $resultado = $this->conexion->prepare($consulta);

            // Vincular parametros dinamicos
            foreach ($params as $key => $value) {
                $resultado->bindValue($key, $value);
            }

            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Calendario::listarAgendamientosCompletos -> " . $e->getMessage());
            return [];
        }
    }

    // FUNCION PARA OBTENER ESTADISTICAS DEL CALENDARIO
    public function obtenerEstadisticas()
    {
        try {
            $consulta = "SELECT 
                            COUNT(*) as total_agendamientos,
                            SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                            SUM(CASE WHEN estado = 'Confirmada' THEN 1 ELSE 0 END) as confirmadas,
                            SUM(CASE WHEN estado = 'Realizada' THEN 1 ELSE 0 END) as realizadas,
                            SUM(CASE WHEN estado = 'Cancelada' THEN 1 ELSE 0 END) as canceladas,
                            SUM(CASE WHEN DATE(fecha_hora) = CURDATE() THEN 1 ELSE 0 END) as hoy
                        FROM agendamiento";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->execute();

            return $resultado->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Calendario::obtenerEstadisticas -> " . $e->getMessage());
            return [];
        }
    }

    // =========================================
    //  FUNCIONES PARA CANCELACION DE CITAS (RFS 36)
    // =========================================

    /**
     * Valida si una cita puede ser cancelada (debe estar en estado Pendiente)
     * 
     * @param int $id_agendamiento ID de la cita a validar
     * @return array Array con keys: 
     *         - 'valido' (bool): Si la cita puede ser cancelada
     *         - 'mensaje' (string): Mensaje descriptivo
     *         - 'estado_actual' (string): Estado actual de la cita
     *         - 'cita' (array): Datos de la cita si es válida
     */
    public function validarEstadoCita($id_agendamiento)
    {
        try {
            // Obtener información de la cita
            $consulta = "SELECT 
                            id_agendamiento,
                            estado,
                            fecha_hora,
                            id_propietario,
                            id_paciente,
                            id_usuario,
                            id_servicio,
                            observaciones
                        FROM agendamiento
                        WHERE id_agendamiento = :id_agendamiento
                        LIMIT 1";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $resultado->execute();

            $cita = $resultado->fetch(PDO::FETCH_ASSOC);

            // Validar que la cita existe
            if (!$cita) {
                return [
                    'valido' => false,
                    'mensaje' => 'La cita no existe en el sistema',
                    'estado_actual' => null,
                    'cita' => null
                ];
            }

            // Validar que la cita está en estado Pendiente
            if ($cita['estado'] !== 'Pendiente') {
                return [
                    'valido' => false,
                    'mensaje' => "La cita no puede ser cancelada. Estado actual: {$cita['estado']}. Solo se pueden cancelar citas en estado Pendiente.",
                    'estado_actual' => $cita['estado'],
                    'cita' => null
                ];
            }

            // La cita es válida para cancelar
            return [
                'valido' => true,
                'mensaje' => 'La cita es válida para ser cancelada',
                'estado_actual' => $cita['estado'],
                'cita' => $cita
            ];
        } catch (PDOException $e) {
            error_log("Error en Calendario::validarEstadoCita -> " . $e->getMessage());
            return [
                'valido' => false,
                'mensaje' => 'Error al validar el estado de la cita',
                'estado_actual' => null,
                'cita' => null
            ];
        }
    }

    /**
     * Registra el motivo de cancelación de una cita
     * 
     * @param int $id_agendamiento ID de la cita a cancelar
     * @param string $motivo_cancelacion Motivo por el cual se cancela
     * @param int|null $id_usuario_cancelo ID del usuario que cancela (opcional)
     * @return array Array con keys:
     *         - 'exito' (bool): Si se registró correctamente
     *         - 'mensaje' (string): Mensaje descriptivo
     *         - 'id_agendamiento' (int): ID de la cita procesada
     */
    public function registrarMotivoCancelacion($id_agendamiento, $motivo_cancelacion, $id_usuario_cancelo = null)
    {
        try {
            // Validar que el motivo no esté vacío
            if (empty($motivo_cancelacion) || strlen(trim($motivo_cancelacion)) === 0) {
                return [
                    'exito' => false,
                    'mensaje' => 'El motivo de cancelación no puede estar vacío',
                    'id_agendamiento' => $id_agendamiento
                ];
            }

            // Validar longitud del motivo (máximo 500 caracteres)
            if (strlen($motivo_cancelacion) > 500) {
                return [
                    'exito' => false,
                    'mensaje' => 'El motivo de cancelación no puede exceder 500 caracteres',
                    'id_agendamiento' => $id_agendamiento
                ];
            }

            // Preparar la consulta de actualización
            $consulta = "UPDATE agendamiento 
                        SET motivo_cancelacion = :motivo_cancelacion,
                            fecha_cancelacion = NOW()";

            // Agregar usuario que canceló si se proporciona
            if ($id_usuario_cancelo !== null) {
                $consulta .= ", usuario_cancelo = :id_usuario_cancelo";
            }

            $consulta .= " WHERE id_agendamiento = :id_agendamiento";

            $resultado = $this->conexion->prepare($consulta);

            // Vincular parámetros
            $resultado->bindParam(':motivo_cancelacion', $motivo_cancelacion, PDO::PARAM_STR);
            $resultado->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);

            if ($id_usuario_cancelo !== null) {
                $resultado->bindParam(':id_usuario_cancelo', $id_usuario_cancelo, PDO::PARAM_INT);
            }

            $resultado->execute();

            // Verificar que se actualizó al menos una fila
            if ($resultado->rowCount() === 0) {
                return [
                    'exito' => false,
                    'mensaje' => 'La cita no existe o no se pudo actualizar',
                    'id_agendamiento' => $id_agendamiento
                ];
            }

            return [
                'exito' => true,
                'mensaje' => 'Motivo de cancelación registrado exitosamente',
                'id_agendamiento' => $id_agendamiento
            ];
        } catch (PDOException $e) {
            error_log("Error en Calendario::registrarMotivoCancelacion -> " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al registrar el motivo de cancelación',
                'id_agendamiento' => $id_agendamiento
            ];
        }
    }

    /**
     * Obtiene los detalles de cancelación de una cita
     * 
     * @param int $id_agendamiento ID de la cita
     * @return array Array con los datos de cancelación o null
     */
    public function obtenerDetallesCancelacion($id_agendamiento)
    {
        try {
            $consulta = "SELECT 
                            id_agendamiento,
                            motivo_cancelacion,
                            fecha_cancelacion,
                            usuario_cancelo,
                            u.nombres as usuario_cancelo_nombre,
                            u.apellidos as usuario_cancelo_apellido
                        FROM agendamiento a
                        LEFT JOIN usuario u ON a.usuario_cancelo = u.id_usuario
                        WHERE a.id_agendamiento = :id_agendamiento
                        LIMIT 1";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Calendario::obtenerDetallesCancelacion -> " . $e->getMessage());
            return null;
        }
    }

    /**
     * SUBTAREA 3: Actualiza el estado de una cita a "Cancelada"
     * 
     * @param int $id_agendamiento ID de la cita a cancelar
     * @return array Array con keys:
     *         - 'exito' (bool): Si se actualizó correctamente
     *         - 'mensaje' (string): Mensaje descriptivo
     *         - 'id_agendamiento' (int): ID de la cita procesada
     */
    public function actualizarEstadoCancelada($id_agendamiento)
    {
        try {
            // Preparar la consulta de actualización de estado
            $consulta = "UPDATE agendamiento 
                        SET estado = 'Cancelada'
                        WHERE id_agendamiento = :id_agendamiento 
                        AND estado = 'Pendiente'";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $resultado->execute();

            // Verificar que se actualizó al menos una fila
            if ($resultado->rowCount() === 0) {
                return [
                    'exito' => false,
                    'mensaje' => 'No se pudo actualizar el estado. Verifique que la cita exista y esté en estado Pendiente.',
                    'id_agendamiento' => $id_agendamiento
                ];
            }

            return [
                'exito' => true,
                'mensaje' => 'Estado de la cita actualizado a Cancelada correctamente',
                'id_agendamiento' => $id_agendamiento
            ];
        } catch (PDOException $e) {
            error_log("Error en Calendario::actualizarEstadoCancelada -> " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al actualizar el estado de la cita',
                'id_agendamiento' => $id_agendamiento
            ];
        }
    }

    /**
     * SUBTAREA 4: Obtiene todos los datos necesarios para enviar notificación de cancelación
     * 
     * @param int $id_agendamiento ID de la cita cancelada
     * @return array Array con todos los datos de la cita cancelada o null
     */
    public function obtenerDatosParaNotificacionCancelacion($id_agendamiento)
    {
        try {
            $consulta = "SELECT 
                            a.id_agendamiento,
                            a.fecha_hora,
                            a.fecha_cancelacion,
                            a.motivo_cancelacion,
                            a.estado,
                            
                            -- Datos del propietario
                            pr.id_propietario,
                            pr.nombres as nombre_propietario,
                            pr.apellidos as apellido_propietario,
                            CONCAT(pr.nombres, ' ', pr.apellidos) as nombre_completo_propietario,
                            pr.email as email_propietario,
                            pr.telefono as telefono_propietario,
                            
                            -- Datos de la mascota
                            pac.id_paciente,
                            pac.nombre as nombre_mascota,
                            pac.especie,
                            pac.raza,
                            
                            -- Datos del servicio
                            s.id_servicio,
                            s.nombre as tipo_servicio,
                            s.descripcion as descripcion_servicio,
                            s.costo,
                            
                            -- Usuario que canceló
                            u.id_usuario as usuario_cancelo_id,
                            u.nombres as usuario_cancelo_nombre,
                            u.apellidos as usuario_cancelo_apellido
                         FROM agendamiento a
                         LEFT JOIN propietario pr ON a.id_propietario = pr.id_propietario
                         LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
                         LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                         LEFT JOIN usuario u ON a.usuario_cancelo = u.id_usuario
                         WHERE a.id_agendamiento = :id_agendamiento
                         LIMIT 1";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_agendamiento', $id_agendamiento, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Calendario::obtenerDatosParaNotificacionCancelacion -> " . $e->getMessage());
            return null;
        }
    }

    // ╔══════════════════════════════════════════════════════════════════════════════╗
    // ║            RFS 34: OBTENER CITAS DEL USUARIO AUTENTICADO                   ║
    // ║  Retorna todas las citas del usuario con detalles completos (propietario,   ║
    // ║  mascota, servicio, veterinario). Filtra según el rol del usuario.          ║
    // ╚══════════════════════════════════════════════════════════════════════════════╝
    
    /**
     * OBTENER CITAS SEGÚN EL USUARIO AUTENTICADO
     * 
     * @param int $id_usuario - ID del usuario autenticado
     * @param string $tipo_usuario - 'propietario', 'veterinario' o 'admin'
     * @param array $filtros - Filtros adicionales (estado, fecha_inicio, fecha_fin)
     * @return array - Citas con detalles completos
     * 
     * Ejemplo:
     * $citas = $calendario->obtenerCitasDelUsuario(5, 'propietario');
     * $citas = $calendario->obtenerCitasDelUsuario(12, 'veterinario', ['estado' => 'Pendiente']);
     */
    public function obtenerCitasDelUsuario($id_usuario, $tipo_usuario = 'propietario', $filtros = [])
    {
        try {
            // ┌─ CONSTRUIR CONSULTA BASE CON TODOS LOS DETALLES
            $sql = "SELECT 
                        a.id_agendamiento,
                        a.tipo,
                        a.observaciones,
                        a.fecha_hora,
                        a.fecha_hora_fin,
                        a.estado,
                        
                        -- Datos del propietario
                        pr.id_propietario,
                        CONCAT(pr.nombres, ' ', pr.apellidos) as propietario_nombre,
                        pr.email as propietario_email,
                        pr.telefono as propietario_telefono,
                        
                        -- Datos de la mascota
                        pac.id_paciente,
                        pac.nombre as mascota_nombre,
                        pac.especie as mascota_especie,
                        pac.raza as mascota_raza,
                        
                        -- Datos del servicio
                        s.id_servicio,
                        s.nombre as servicio_nombre,
                        s.descripcion as servicio_descripcion,
                        s.costo as servicio_costo,
                        
                        -- Datos del veterinario
                        u.id_usuario,
                        CONCAT(u.nombres, ' ', u.apellidos) as veterinario_nombre,
                        u.email as veterinario_email
                    FROM agendamiento a
                    LEFT JOIN propietario pr ON a.id_propietario = pr.id_propietario
                    LEFT JOIN paciente pac ON a.id_paciente = pac.id_paciente
                    LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                    LEFT JOIN usuario u ON a.id_usuario = u.id_usuario
                    WHERE 1=1";
            
            $parametros = [];
            
            // ┌─ FILTRAR SEGÚN EL TIPO DE USUARIO
            if ($tipo_usuario === 'propietario') {
                // El propietario solo ve sus propias citas
                $sql .= " AND a.id_propietario = :id_usuario";
                $parametros['id_usuario'] = $id_usuario;
            } 
            elseif ($tipo_usuario === 'veterinario') {
                // El veterinario ve las citas asignadas a él
                $sql .= " AND a.id_usuario = :id_usuario";
                $parametros['id_usuario'] = $id_usuario;
            }
            // Si es 'admin' o otro, ve todas sin filtrar por usuario
            
            // ┌─ APLICAR FILTROS ADICIONALES
            if (!empty($filtros['estado'])) {
                $sql .= " AND a.estado = :estado";
                $parametros['estado'] = $filtros['estado'];
            }
            
            if (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND DATE(a.fecha_hora) >= :fecha_inicio";
                $parametros['fecha_inicio'] = $filtros['fecha_inicio'];
            }
            
            if (!empty($filtros['fecha_fin'])) {
                $sql .= " AND DATE(a.fecha_hora) <= :fecha_fin";
                $parametros['fecha_fin'] = $filtros['fecha_fin'];
            }
            
            // ┌─ ORDENAR RESULTADOS POR FECHA
            $sql .= " ORDER BY a.fecha_hora ASC";
            
            // ┌─ EJECUTAR CONSULTA
            $stmt = $this->conexion->prepare($sql);
            
            foreach ($parametros as $clave => $valor) {
                $stmt->bindParam(':' . $clave, $parametros[$clave]);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en Calendario::obtenerCitasDelUsuario -> " . $e->getMessage());
            return [];
        }
    }
}

