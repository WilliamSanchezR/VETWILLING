<?php

require_once __DIR__ . '/../../config/database.php';

// =========================================
//  RFS 37: FUNCIONES PARA REGISTRO DE NOTIFICACIONES
//  Sistema de historial y auditoria de notificaciones enviadas
// =========================================

/**
 * REGISTRAR UNA NOTIFICACION ENVIADA EN EL HISTORIAL
 * 
 * Inserta un registro en la tabla notificaciones_enviadas con toda la informacion
 * del intento de envio de notificacion
 * 
 * @param array $datos Array con datos de la notificacion:
 *        - id_agendamiento (int): ID de la cita
 *        - medio_notificacion (string): 'email'
 *        - destinatario (string): Email del destinatario
 *        - estado_envio (string): 'exitoso', 'fallido', 'pendiente'
 *        - mensaje_error (string|null): Mensaje de error si fallo
 *        - intentos_envio (int): Numero de intentos (default: 1)
 * @return int|false ID de la notificacion registrada o false si falla
 */
function registrarNotificacionEnviada($datos)
{
    try {
        $db = new conexion();
        $conexion = $db->getConexion();

        $sql = "INSERT INTO notificaciones_enviadas (
                    id_agendamiento,
                    fecha_envio,
                    medio_notificacion,
                    destinatario,
                    estado_envio,
                    mensaje_error,
                    confirmacion_recepcion,
                    intentos_envio,
                    ultimo_intento
                ) VALUES (
                    :id_agendamiento,
                    NOW(),
                    :medio_notificacion,
                    :destinatario,
                    :estado_envio,
                    :mensaje_error,
                    :confirmacion_recepcion,
                    :intentos_envio,
                    NOW()
                )";

        $stmt = $conexion->prepare($sql);

        // Parametros obligatorios
        $stmt->bindParam(':id_agendamiento', $datos['id_agendamiento'], PDO::PARAM_INT);
        $stmt->bindParam(':medio_notificacion', $datos['medio_notificacion']);
        $stmt->bindParam(':destinatario', $datos['destinatario']);
        $stmt->bindParam(':estado_envio', $datos['estado_envio']);

        // Parametros opcionales
        $mensaje_error = $datos['mensaje_error'] ?? null;
        $stmt->bindParam(':mensaje_error', $mensaje_error);

        $confirmacion = $datos['confirmacion_recepcion'] ?? 0;
        $stmt->bindParam(':confirmacion_recepcion', $confirmacion, PDO::PARAM_INT);

        $intentos = $datos['intentos_envio'] ?? 1;
        $stmt->bindParam(':intentos_envio', $intentos, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $conexion->lastInsertId();
        }

        return false;
    } catch (PDOException $e) {
        error_log("Error al registrar notificacion: " . $e->getMessage());
        return false;
    }
}

/**
 * ACTUALIZAR ESTADO DE UNA NOTIFICACION EXISTENTE
 * 
 * Actualiza el estado de una notificacion ya registrada
 * Util para reintento de envios fallidos
 * 
 * @param int $idNotificacion ID de la notificacion
 * @param string $nuevoEstado 'exitoso', 'fallido', 'pendiente'
 * @param string|null $mensajeError Mensaje de error si aplica
 * @param int $intentos Numero total de intentos
 * @return bool True si se actualizo correctamente
 */
function actualizarEstadoNotificacion($idNotificacion, $nuevoEstado, $mensajeError = null, $intentos = 1)
{
    try {
        $db = new conexion();
        $conexion = $db->getConexion();

        $sql = "UPDATE notificaciones_enviadas 
                SET estado_envio = :estado_envio,
                    mensaje_error = :mensaje_error,
                    intentos_envio = :intentos_envio,
                    ultimo_intento = NOW()
                WHERE id_notificacion = :id_notificacion";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_notificacion', $idNotificacion, PDO::PARAM_INT);
        $stmt->bindParam(':estado_envio', $nuevoEstado);
        $stmt->bindParam(':mensaje_error', $mensajeError);
        $stmt->bindParam(':intentos_envio', $intentos, PDO::PARAM_INT);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error al actualizar notificacion: " . $e->getMessage());
        return false;
    }
}

/**
 * MARCAR NOTIFICACION COMO RECIBIDA/LEIDA
 * 
 * Actualiza la confirmacion de recepcion cuando se verifica que el email fue entregado
 * 
 * @param int $idNotificacion ID de la notificacion
 * @return bool True si se actualizo correctamente
 */
function marcarNotificacionRecibida($idNotificacion)
{
    try {
        $db = new conexion();
        $conexion = $db->getConexion();

        $sql = "UPDATE notificaciones_enviadas 
                SET confirmacion_recepcion = 1,
                    fecha_recepcion = NOW()
                WHERE id_notificacion = :id_notificacion";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_notificacion', $idNotificacion, PDO::PARAM_INT);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error al marcar notificacion como recibida: " . $e->getMessage());
        return false;
    }
}

/**
 * OBTENER HISTORIAL DE NOTIFICACIONES DE UNA CITA
 * 
 * Retorna todas las notificaciones enviadas para una cita especifica
 * Util para debugging y auditoria
 * 
 * @param int $idAgendamiento ID de la cita
 * @return array Array de notificaciones
 */
function obtenerHistorialNotificacionesCita($idAgendamiento)
{
    try {
        $db = new conexion();
        $conexion = $db->getConexion();

        $sql = "SELECT 
                    id_notificacion,
                    id_agendamiento,
                    fecha_envio,
                    medio_notificacion,
                    destinatario,
                    estado_envio,
                    mensaje_error,
                    confirmacion_recepcion,
                    fecha_recepcion,
                    intentos_envio,
                    ultimo_intento
                FROM notificaciones_enviadas
                WHERE id_agendamiento = :id_agendamiento
                ORDER BY fecha_envio DESC";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener historial de notificaciones: " . $e->getMessage());
        return [];
    }
}

/**
 * OBTENER NOTIFICACIONES FALLIDAS RECIENTES
 * 
 * Retorna las notificaciones que fallaron en las ultimas X horas
 * Util para monitoreo y reintento automatico
 * 
 * @param int $horas Numero de horas hacia atras a buscar (default: 24)
 * @param int $limite Maximo numero de resultados (default: 50)
 * @return array Array de notificaciones fallidas
 */
function obtenerNotificacionesFallidas($horas = 24, $limite = 50)
{
    try {
        $db = new conexion();
        $conexion = $db->getConexion();

        $sql = "SELECT 
                    n.id_notificacion,
                    n.id_agendamiento,
                    n.fecha_envio,
                    n.destinatario,
                    n.mensaje_error,
                    n.intentos_envio,
                    n.ultimo_intento,
                    
                    a.tipo as tipo_cita,
                    a.fecha_hora as fecha_hora_cita,
                    
                    CONCAT(p.nombres, ' ', p.apellidos) as nombre_propietario
                    
                FROM notificaciones_enviadas n
                INNER JOIN agendamiento a ON n.id_agendamiento = a.id_agendamiento
                INNER JOIN propietario p ON a.id_propietario = p.id_propietario
                
                WHERE n.estado_envio = 'fallido'
                AND n.fecha_envio >= DATE_SUB(NOW(), INTERVAL :horas HOUR)
                
                ORDER BY n.fecha_envio DESC
                LIMIT :limite";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':horas', $horas, PDO::PARAM_INT);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener notificaciones fallidas: " . $e->getMessage());
        return [];
    }
}

/**
 * OBTENER ESTADISTICAS DE NOTIFICACIONES
 * 
 * Retorna estadisticas generales de notificaciones enviadas
 * Util para reportes y dashboard administrativo
 * 
 * @param string $fechaInicio Fecha inicio (Y-m-d)
 * @param string $fechaFin Fecha fin (Y-m-d)
 * @return array Array con estadisticas
 */
function obtenerEstadisticasNotificaciones($fechaInicio = null, $fechaFin = null)
{
    try {
        $db = new conexion();
        $conexion = $db->getConexion();

        // Si no se especifican fechas, usar ultimos 30 dias
        if (!$fechaInicio) {
            $fechaInicio = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$fechaFin) {
            $fechaFin = date('Y-m-d');
        }

        $sql = "SELECT 
                    COUNT(*) as total_notificaciones,
                    
                    SUM(CASE WHEN estado_envio = 'exitoso' THEN 1 ELSE 0 END) as exitosas,
                    SUM(CASE WHEN estado_envio = 'fallido' THEN 1 ELSE 0 END) as fallidas,
                    SUM(CASE WHEN estado_envio = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    
                    SUM(CASE WHEN confirmacion_recepcion = 1 THEN 1 ELSE 0 END) as confirmadas,
                    
                    AVG(intentos_envio) as promedio_intentos,
                    
                    COUNT(DISTINCT id_agendamiento) as citas_notificadas,
                    
                    COUNT(DISTINCT destinatario) as destinatarios_unicos
                    
                FROM notificaciones_enviadas
                WHERE DATE(fecha_envio) BETWEEN :fecha_inicio AND :fecha_fin";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':fecha_inicio', $fechaInicio);
        $stmt->bindParam(':fecha_fin', $fechaFin);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener estadisticas de notificaciones: " . $e->getMessage());
        return [];
    }
}

/**
 * VERIFICAR SI YA SE ENVIO NOTIFICACION A UNA CITA
 * 
 * Consulta rapida para verificar si ya existe una notificacion exitosa
 * para evitar envios duplicados
 * 
 * @param int $idAgendamiento ID de la cita
 * @param string $medioNotificacion 'email' 
 * @return bool True si ya existe notificacion exitosa
 */
function existeNotificacionExitosa($idAgendamiento, $medioNotificacion = 'email')
{
    try {
        $db = new conexion();
        $conexion = $db->getConexion();

        $sql = "SELECT COUNT(*) as total
                FROM notificaciones_enviadas
                WHERE id_agendamiento = :id_agendamiento
                AND medio_notificacion = :medio_notificacion
                AND estado_envio = 'exitoso'";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_agendamiento', $idAgendamiento, PDO::PARAM_INT);
        $stmt->bindParam(':medio_notificacion', $medioNotificacion);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado && $resultado['total'] > 0;
    } catch (PDOException $e) {
        error_log("Error al verificar notificacion existente: " . $e->getMessage());
        return false;
    }
}

/**
 * LIMPIAR NOTIFICACIONES ANTIGUAS
 * 
 * Elimina registros de notificaciones mas antiguos que X dias
 * Util para mantenimiento de la base de datos
 * 
 * @param int $diasAntiguedad Dias de antiguedad (default: 365)
 * @return int Numero de registros eliminados
 */
function limpiarNotificacionesAntiguas($diasAntiguedad = 365)
{
    try {
        $db = new conexion();
        $conexion = $db->getConexion();

        $sql = "DELETE FROM notificaciones_enviadas
                WHERE fecha_envio < DATE_SUB(NOW(), INTERVAL :dias DAY)";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':dias', $diasAntiguedad, PDO::PARAM_INT);
        $stmt->execute();

        $eliminados = $stmt->rowCount();

        error_log("Limpieza de notificaciones: {$eliminados} registros eliminados");

        return $eliminados;
    } catch (PDOException $e) {
        error_log("Error al limpiar notificaciones antiguas: " . $e->getMessage());
        return 0;
    }
}
