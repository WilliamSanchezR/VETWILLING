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
                            descripcion,
                            costo
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
}
