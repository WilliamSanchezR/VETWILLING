<?php

require_once __DIR__ . '/../../config/database.php';

class Reportes
{
    private $conexion;

    private const ESTADOS_ATENDIDOS = [
        'REALIZADA',
        'REALIZADO',
        'ATENDIDA',
        'ATENDIDO',
        'COMPLETADA',
        'COMPLETADO',
        'FINALIZADA',
        'FINALIZADO'
    ];

    private const ESTADOS_CANCELADOS = [
        'CANCELADA',
        'CANCELADO',
        'ANULADA',
        'ANULADO'
    ];

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    private function estadoNormalizadoSql($columna)
    {
        $atendidos = $this->listaSql(self::ESTADOS_ATENDIDOS);
        $cancelados = $this->listaSql(self::ESTADOS_CANCELADOS);

        return "CASE
                    WHEN UPPER(TRIM(COALESCE({$columna}, ''))) IN ({$atendidos}) THEN 'ATENDIDA'
                    WHEN UPPER(TRIM(COALESCE({$columna}, ''))) IN ({$cancelados}) THEN 'CANCELADA'
                    ELSE 'PENDIENTE'
                END";
    }

    private function listaSql(array $values)
    {
        return implode(', ', array_map(function ($valor) {
            return "'" . str_replace("'", "''", $valor) . "'";
        }, $values));
    }

    /**
     * Construye fragmentos JOIN/WHERE/params para filtros opcionales (RFS 39).
     */
    private function construirFiltrosSQL(array $filtros = [])
    {
        $joins = '';
        $where = '';
        $params = [];

        if (!empty($filtros['id_veterinaria'])) {
            $joins .= " INNER JOIN profesional prof_f ON a.id_usuario = prof_f.id_usuario
                         INNER JOIN profesional_veterinaria pv_f ON prof_f.id_profesional = pv_f.id_profesional";
            $where .= " AND pv_f.id_veterinaria = :filtro_id_veterinaria";
            $params[':filtro_id_veterinaria'] = ['value' => (int)$filtros['id_veterinaria'], 'type' => PDO::PARAM_INT];
        }

        if (!empty($filtros['id_propietario'])) {
            $joins .= " INNER JOIN paciente pac_f ON a.id_paciente = pac_f.id_paciente";
            $where .= " AND pac_f.id_propietario = :filtro_id_propietario";
            $params[':filtro_id_propietario'] = ['value' => (int)$filtros['id_propietario'], 'type' => PDO::PARAM_INT];
        }

        if (!empty($filtros['estado_cita'])) {
            $estadoNorm = $this->estadoNormalizadoSql('a.estado');
            $where .= " AND {$estadoNorm} = :filtro_estado_cita";
            $params[':filtro_estado_cita'] = ['value' => $filtros['estado_cita'], 'type' => PDO::PARAM_STR];
        }

        return [$joins, $where, $params];
    }

    private function bindFiltros($stmt, array $paramsFiltro)
    {
        foreach ($paramsFiltro as $key => $meta) {
            $stmt->bindValue($key, $meta['value'], $meta['type']);
        }
    }

    public function obtenerResumenGeneral($idUsuario, $fechaInicio, $fechaFin, array $filtros = [])
    {
        try {
            $estadoNorm = $this->estadoNormalizadoSql('a.estado');
            [$joinsFiltro, $whereFiltro, $paramsFiltro] = $this->construirFiltrosSQL($filtros);

            $sql = "SELECT
                        COALESCE(SUM(CASE
                            WHEN {$estadoNorm} <> 'CANCELADA' THEN COALESCE(sub.costo, 0)
                            ELSE 0
                        END), 0) AS ingresos_totales,
                        SUM(CASE
                            WHEN {$estadoNorm} = 'ATENDIDA' THEN 1
                            ELSE 0
                        END) AS citas_atendidas,
                        COUNT(*) AS total_citas
                    FROM agendamiento a
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    {$joinsFiltro}
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                      {$whereFiltro}";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $this->bindFiltros($stmt, $paramsFiltro);
            $stmt->execute();
            $resumen = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $sqlNuevos = "SELECT COUNT(*)
                          FROM (
                              SELECT a.id_paciente, MIN(DATE(a.fecha_hora)) AS primera_fecha
                              FROM agendamiento a
                              {$joinsFiltro}
                              WHERE a.id_usuario = :id_usuario
                                AND a.id_paciente IS NOT NULL
                                {$whereFiltro}
                              GROUP BY a.id_paciente
                          ) t
                          WHERE t.primera_fecha BETWEEN :fecha_inicio AND :fecha_fin";

            $stmtNuevos = $this->conexion->prepare($sqlNuevos);
            $stmtNuevos->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmtNuevos->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmtNuevos->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $this->bindFiltros($stmtNuevos, $paramsFiltro);
            $stmtNuevos->execute();
            $nuevosPacientes = (int)$stmtNuevos->fetchColumn();

            $citasAtendidas = (int)($resumen['citas_atendidas'] ?? 0);
            $totalCitas = (int)($resumen['total_citas'] ?? 0);
            $cumplimiento = $totalCitas > 0 ? round(($citasAtendidas / $totalCitas) * 100, 1) : 0;

            return [
                'ingresos_totales' => (float)($resumen['ingresos_totales'] ?? 0),
                'citas_atendidas' => $citasAtendidas,
                'nuevos_pacientes' => $nuevosPacientes,
                'cumplimiento' => $cumplimiento,
                'total_citas' => $totalCitas
            ];
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerResumenGeneral - ' . $e->getMessage());
            return [
                'ingresos_totales' => 0,
                'citas_atendidas' => 0,
                'nuevos_pacientes' => 0,
                'cumplimiento' => 0,
                'total_citas' => 0
            ];
        }
    }

    public function obtenerIngresosUltimosSeisMeses($idUsuario, array $filtros = [])
    {
        try {
            $inicio = (new DateTime('first day of -5 months'))->format('Y-m-01');
            $estadoNorm = $this->estadoNormalizadoSql('a.estado');
            [$joinsFiltro, $whereFiltro, $paramsFiltro] = $this->construirFiltrosSQL($filtros);

            $sql = "SELECT
                        DATE_FORMAT(a.fecha_hora, '%Y-%m') AS periodo,
                        COALESCE(SUM(COALESCE(sub.costo, 0)), 0) AS total
                    FROM agendamiento a
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    {$joinsFiltro}
                    WHERE a.id_usuario = :id_usuario
                      AND a.fecha_hora >= :inicio
                      AND {$estadoNorm} <> 'CANCELADA'
                      {$whereFiltro}
                    GROUP BY DATE_FORMAT(a.fecha_hora, '%Y-%m')
                    ORDER BY periodo ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':inicio', $inicio, PDO::PARAM_STR);
            $this->bindFiltros($stmt, $paramsFiltro);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $mapa = [];
            foreach ($rows as $row) {
                $mapa[$row['periodo']] = (float)$row['total'];
            }

            $labels = [];
            $data = [];
            $mesesES = [
                1 => 'Enero',
                2 => 'Febrero',
                3 => 'Marzo',
                4 => 'Abril',
                5 => 'Mayo',
                6 => 'Junio',
                7 => 'Julio',
                8 => 'Agosto',
                9 => 'Septiembre',
                10 => 'Octubre',
                11 => 'Noviembre',
                12 => 'Diciembre'
            ];

            $cursor = new DateTime('first day of -5 months');
            for ($i = 0; $i < 6; $i++) {
                $clave = $cursor->format('Y-m');
                $labels[] = $mesesES[(int)$cursor->format('n')];
                $data[] = $mapa[$clave] ?? 0;
                $cursor->modify('+1 month');
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerIngresosUltimosSeisMeses - ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    public function obtenerServiciosMasSolicitados($idUsuario, $fechaInicio, $fechaFin, $limite = 4, array $filtros = [])
    {
        try {
            [$joinsFiltro, $whereFiltro, $paramsFiltro] = $this->construirFiltrosSQL($filtros);

            $sql = "SELECT
                        COALESCE(s.nombre, 'Sin servicio') AS nombre,
                        COUNT(*) AS total
                    FROM agendamiento a
                    LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                    {$joinsFiltro}
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                      {$whereFiltro}
                    GROUP BY COALESCE(s.nombre, 'Sin servicio')
                    ORDER BY total DESC
                    LIMIT :limite";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $this->bindFiltros($stmt, $paramsFiltro);
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $totalGeneral = 0;
            foreach ($items as $item) {
                $totalGeneral += (int)$item['total'];
            }

            $resultado = [];
            foreach ($items as $item) {
                $cantidad = (int)$item['total'];
                $porcentaje = $totalGeneral > 0 ? round(($cantidad / $totalGeneral) * 100, 1) : 0;
                $resultado[] = [
                    'nombre' => $item['nombre'],
                    'total' => $cantidad,
                    'porcentaje' => $porcentaje
                ];
            }

            return $resultado;
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerServiciosMasSolicitados - ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerTopTratamientos($idUsuario, $fechaInicio, $fechaFin, $limite = 5, array $filtros = [])
    {
        try {
            [$joinsFiltro, $whereFiltro, $paramsFiltro] = $this->construirFiltrosSQL($filtros);

            $sql = "SELECT
                        COALESCE(sub.nombre, a.tipo, 'Sin tratamiento') AS nombre,
                        COUNT(*) AS total
                    FROM agendamiento a
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    {$joinsFiltro}
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                      {$whereFiltro}
                    GROUP BY COALESCE(sub.nombre, a.tipo, 'Sin tratamiento')
                    ORDER BY total DESC
                    LIMIT :limite";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $this->bindFiltros($stmt, $paramsFiltro);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerTopTratamientos - ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerPacientesPorEspecie($idUsuario, $fechaInicio, $fechaFin, array $filtros = [])
    {
        try {
            [$joinsFiltro, $whereFiltro, $paramsFiltro] = $this->construirFiltrosSQL($filtros);

            $sql = "SELECT
                        COALESCE(p.especie, 'Sin especie') AS especie,
                        COUNT(DISTINCT a.id_paciente) AS total
                    FROM agendamiento a
                    LEFT JOIN paciente p ON a.id_paciente = p.id_paciente
                    {$joinsFiltro}
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                      {$whereFiltro}
                    GROUP BY COALESCE(p.especie, 'Sin especie')
                    ORDER BY total DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $this->bindFiltros($stmt, $paramsFiltro);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $suma = 0;
            foreach ($rows as $row) {
                $suma += (int)$row['total'];
            }

            $resultado = [];
            foreach ($rows as $row) {
                $cantidad = (int)$row['total'];
                $resultado[] = [
                    'especie' => $row['especie'],
                    'total' => $cantidad,
                    'porcentaje' => $suma > 0 ? round(($cantidad / $suma) * 100, 1) : 0
                ];
            }

            return $resultado;
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerPacientesPorEspecie - ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerResumenFinancieroMensual($idUsuario, $anio, array $filtros = [])
    {
        try {
            $anio = (int)$anio;
            $mesActual = (int)date('n');
            $ultimoMes = ((int)date('Y') === $anio) ? $mesActual : 12;
            $estadoNorm = $this->estadoNormalizadoSql('a.estado');
            [$joinsFiltro, $whereFiltro, $paramsFiltro] = $this->construirFiltrosSQL($filtros);

            $sql = "SELECT
                        COALESCE(s.nombre, 'Sin servicio') AS concepto,
                        MONTH(a.fecha_hora) AS mes,
                        COALESCE(SUM(COALESCE(sub.costo, 0)), 0) AS total
                    FROM agendamiento a
                    LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    {$joinsFiltro}
                    WHERE a.id_usuario = :id_usuario
                      AND YEAR(a.fecha_hora) = :anio
                      AND MONTH(a.fecha_hora) BETWEEN 1 AND :ultimo_mes
                      AND {$estadoNorm} <> 'CANCELADA'
                      {$whereFiltro}
                    GROUP BY COALESCE(s.nombre, 'Sin servicio'), MONTH(a.fecha_hora)
                    ORDER BY concepto ASC, mes ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->bindParam(':ultimo_mes', $ultimoMes, PDO::PARAM_INT);
            $this->bindFiltros($stmt, $paramsFiltro);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $mesesES = [
                1 => 'Enero',
                2 => 'Febrero',
                3 => 'Marzo',
                4 => 'Abril',
                5 => 'Mayo',
                6 => 'Junio',
                7 => 'Julio',
                8 => 'Agosto',
                9 => 'Septiembre',
                10 => 'Octubre',
                11 => 'Noviembre',
                12 => 'Diciembre'
            ];

            $meses = [];
            for ($m = 1; $m <= $ultimoMes; $m++) {
                $meses[] = $mesesES[$m];
            }

            $mapaConceptos = [];
            foreach ($rows as $row) {
                $concepto = $row['concepto'];
                $mes = (int)$row['mes'];
                $valor = (float)$row['total'];

                if (!isset($mapaConceptos[$concepto])) {
                    $mapaConceptos[$concepto] = [
                        'concepto' => $concepto,
                        'valores' => array_fill(0, $ultimoMes, 0)
                    ];
                }

                $mapaConceptos[$concepto]['valores'][$mes - 1] = $valor;
            }

            $filas = array_values($mapaConceptos);
            foreach ($filas as &$fila) {
                $fila['total'] = array_sum($fila['valores']);
            }
            unset($fila);

            usort($filas, function ($a, $b) {
                return $b['total'] <=> $a['total'];
            });

            $filas = array_slice($filas, 0, 6);

            $totalesMes = array_fill(0, $ultimoMes, 0);
            $granTotal = 0;

            foreach ($filas as $fila) {
                foreach ($fila['valores'] as $idx => $valor) {
                    $totalesMes[$idx] += $valor;
                }
                $granTotal += $fila['total'];
            }

            return [
                'meses' => $meses,
                'filas' => $filas,
                'totales_mes' => $totalesMes,
                'gran_total' => $granTotal
            ];
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerResumenFinancieroMensual - ' . $e->getMessage());
            return [
                'meses' => [],
                'filas' => [],
                'totales_mes' => [],
                'gran_total' => 0
            ];
        }
    }

    public function obtenerResumenEstadosCitas($idUsuario, $fechaInicio, $fechaFin, array $filtros = [])
    {
        try {
            $estadoNorm = $this->estadoNormalizadoSql('a.estado');
            [$joinsFiltro, $whereFiltro, $paramsFiltro] = $this->construirFiltrosSQL($filtros);

            $sql = "SELECT
                        {$estadoNorm} AS estado_normalizado,
                        COUNT(*) AS total
                    FROM agendamiento a
                    {$joinsFiltro}
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                      {$whereFiltro}
                    GROUP BY {$estadoNorm}";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $this->bindFiltros($stmt, $paramsFiltro);
            $stmt->execute();

            $resumen = [
                'atendidas' => 0,
                'canceladas' => 0,
                'pendientes' => 0,
                'total' => 0
            ];

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as $row) {
                $estado = $row['estado_normalizado'] ?? 'PENDIENTE';
                $cantidad = (int)($row['total'] ?? 0);

                if ($estado === 'ATENDIDA') {
                    $resumen['atendidas'] += $cantidad;
                } elseif ($estado === 'CANCELADA') {
                    $resumen['canceladas'] += $cantidad;
                } else {
                    $resumen['pendientes'] += $cantidad;
                }

                $resumen['total'] += $cantidad;
            }

            return $resumen;
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerResumenEstadosCitas - ' . $e->getMessage());
            return [
                'atendidas' => 0,
                'canceladas' => 0,
                'pendientes' => 0,
                'total' => 0
            ];
        }
    }

    public function obtenerPacientesAsignadosActivos($idUsuario)
    {
        try {
            if (!$this->tablaAsignacionExiste()) {
                return [];
            }

            $sql = "SELECT
                        ppa.id_paciente,
                        ppa.fecha_inicio,
                        p.nombre AS paciente_nombre,
                        p.especie,
                        p.raza,
                        CONCAT(prop.nombres, ' ', prop.apellidos) AS propietario_nombre,
                        (
                            SELECT MAX(a.fecha_hora)
                            FROM agendamiento a
                            WHERE a.id_usuario = :id_usuario
                              AND a.id_paciente = ppa.id_paciente
                        ) AS ultima_visita
                    FROM paciente_profesional_asignacion ppa
                    INNER JOIN paciente p ON p.id_paciente = ppa.id_paciente
                    INNER JOIN propietario prop ON prop.id_propietario = p.id_propietario
                    WHERE ppa.id_usuario_profesional = :id_usuario
                      AND ppa.estado = 'Activo'
                      AND ppa.fecha_fin IS NULL
                    ORDER BY ppa.fecha_inicio DESC, ppa.id_asignacion DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerPacientesAsignadosActivos - ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerHistorialAsignacionesPeriodo($idUsuario, $fechaInicio, $fechaFin, $limite = 10)
    {
        try {
            if (!$this->tablaAsignacionExiste()) {
                return [];
            }

            $sql = "SELECT
                        ppa.id_asignacion,
                        ppa.id_paciente,
                        ppa.fecha_inicio,
                        ppa.fecha_fin,
                        ppa.estado,
                        ppa.motivo_cambio,
                        ppa.observacion,
                        p.nombre AS paciente_nombre,
                        CONCAT(prop.nombres, ' ', prop.apellidos) AS propietario_nombre
                    FROM paciente_profesional_asignacion ppa
                    INNER JOIN paciente p ON p.id_paciente = ppa.id_paciente
                    INNER JOIN propietario prop ON prop.id_propietario = p.id_propietario
                    WHERE ppa.id_usuario_profesional = :id_usuario
                      AND (
                          DATE(ppa.fecha_inicio) BETWEEN :fecha_inicio AND :fecha_fin
                          OR (ppa.fecha_fin IS NOT NULL AND DATE(ppa.fecha_fin) BETWEEN :fecha_inicio AND :fecha_fin)
                      )
                    ORDER BY ppa.fecha_inicio DESC, ppa.id_asignacion DESC
                    LIMIT :limite";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerHistorialAsignacionesPeriodo - ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerDetalleCitas($idUsuario, $fechaInicio, $fechaFin, array $filtros = [], $limite = 50)
    {
        try {
            $estadoNorm = $this->estadoNormalizadoSql('a.estado');
            [$joinsFiltro, $whereFiltro, $paramsFiltro] = $this->construirFiltrosSQL($filtros);

            $sql = "SELECT
                        a.id_agendamiento,
                        DATE_FORMAT(a.fecha_hora, '%Y-%m-%d %H:%i') AS fecha,
                        COALESCE(p.id_paciente, 0) AS id_paciente,
                        COALESCE(p.nombre, 'Sin paciente') AS paciente,
                        COALESCE(CONCAT(prop.nombres, ' ', prop.apellidos), 'Sin propietario') AS propietario,
                        COALESCE(s.nombre, 'Sin servicio') AS servicio,
                        COALESCE(sub.nombre, a.tipo, 'Sin subservicio') AS subservicio,
                        {$estadoNorm} AS estado,
                        a.observaciones
                    FROM agendamiento a
                    LEFT JOIN paciente p ON a.id_paciente = p.id_paciente
                    LEFT JOIN propietario prop ON p.id_propietario = prop.id_propietario
                    LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    {$joinsFiltro}
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                      {$whereFiltro}
                    ORDER BY a.fecha_hora DESC
                    LIMIT :limite";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $this->bindFiltros($stmt, $paramsFiltro);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerDetalleCitas - ' . $e->getMessage());
            return [];
        }
    }

    private function tablaAsignacionExiste()
    {
        try {
            $sql = "SELECT COUNT(*)
                    FROM information_schema.TABLES
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'paciente_profesional_asignacion'";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return ((int) $stmt->fetchColumn() > 0);
        } catch (PDOException $e) {
            error_log('Error en Reportes::tablaAsignacionExiste - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * RFS 32 subtask 6 & 8: Registra cada generación de reporte en el historial.
     */
    public function registrarGeneracion(int $idUsuario, string $tipoReporte, ?int $idPaciente, array $parametros): void
    {
        try {
            $this->asegurarTablaReporteGenerado();
            $sql = "INSERT INTO reporte_generado (id_usuario, tipo_reporte, id_paciente, parametros, generado_en)
                    VALUES (:id_usuario, :tipo_reporte, :id_paciente, :parametros, CURRENT_TIMESTAMP)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':id_usuario'   => $idUsuario,
                ':tipo_reporte' => $tipoReporte,
                ':id_paciente'  => $idPaciente,
                ':parametros'   => json_encode($parametros, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (PDOException $e) {
            error_log('Error en Reportes::registrarGeneracion - ' . $e->getMessage());
        }
    }

    /**
     * RFS 32 subtask 8: Devuelve el historial de reportes generados por el profesional.
     */
    public function obtenerHistorialGeneracion(int $idUsuario, ?int $idPaciente = null): array
    {
        try {
            $this->asegurarTablaReporteGenerado();
            $where = $idPaciente ? 'AND id_paciente = :id_paciente' : '';
            $sql = "SELECT id, tipo_reporte, id_paciente, parametros, generado_en
                    FROM reporte_generado
                    WHERE id_usuario = :id_usuario {$where}
                    ORDER BY generado_en DESC
                    LIMIT 100";
            $stmt = $this->conexion->prepare($sql);
            $params = [':id_usuario' => $idUsuario];
            if ($idPaciente) {
                $params[':id_paciente'] = $idPaciente;
            }
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerHistorialGeneracion - ' . $e->getMessage());
            return [];
        }
    }

    private function asegurarTablaReporteGenerado(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS reporte_generado (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario   INT NOT NULL,
            tipo_reporte VARCHAR(60) NOT NULL,
            id_paciente  INT NULL,
            parametros   JSON NULL,
            generado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->conexion->exec($sql);
    }

    // -----------------------------------------------------------------------
    // RFS 14 — subtarea 3: Inventario de la veterinaria del usuario
    // -----------------------------------------------------------------------

    /**
     * Devuelve el id_veterinaria asociado al profesional (usuario).
     * Retorna null si no tiene veterinaria asignada.
     */
    private function obtenerVeterinariaDeUsuario(int $idUsuario): ?int
    {
        try {
            $sql = "SELECT pv.id_veterinaria
                    FROM profesional prof
                    INNER JOIN profesional_veterinaria pv ON prof.id_profesional = pv.id_profesional
                    WHERE prof.id_usuario = :id_usuario
                    LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['id_veterinaria'] : null;
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerVeterinariaDeUsuario - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Resumen de inventario de la veterinaria del usuario autenticado.
     */
    public function obtenerResumenInventario(int $idUsuario): array
    {
        try {
            $idVeterinaria = $this->obtenerVeterinariaDeUsuario($idUsuario);
            if ($idVeterinaria === null) {
                return [
                    'total_productos' => 0, 'vencidos' => 0,
                    'por_vencer' => 0, 'vigentes' => 0, 'cantidad_total' => 0,
                ];
            }

            $sql = "SELECT
                        COUNT(*) AS total_productos,
                        SUM(CASE WHEN p.fecha_vencimiento < CURDATE() THEN 1 ELSE 0 END) AS vencidos,
                        SUM(CASE WHEN p.fecha_vencimiento BETWEEN CURDATE()
                                      AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS por_vencer,
                        SUM(CASE WHEN p.fecha_vencimiento > DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                                      OR p.fecha_vencimiento IS NULL THEN 1 ELSE 0 END) AS vigentes,
                        SUM(COALESCE(i.cantidad, 0)) AS cantidad_total
                    FROM inventario i
                    INNER JOIN producto p ON p.id_inventario = i.id_inventario
                    WHERE i.id_veterinaria = :id_veterinaria";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_veterinaria', $idVeterinaria, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'total_productos' => (int)($row['total_productos'] ?? 0),
                'vencidos'        => (int)($row['vencidos']        ?? 0),
                'por_vencer'      => (int)($row['por_vencer']      ?? 0),
                'vigentes'        => (int)($row['vigentes']        ?? 0),
                'cantidad_total'  => (int)($row['cantidad_total']  ?? 0),
            ];
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerResumenInventario - ' . $e->getMessage());
            return ['total_productos' => 0, 'vencidos' => 0, 'por_vencer' => 0, 'vigentes' => 0, 'cantidad_total' => 0];
        }
    }

    /**
     * Lista de productos próximos a vencer (o ya vencidos) de la veterinaria.
     */
    public function obtenerProductosProximosVencer(int $idUsuario, int $dias = 60): array
    {
        try {
            $idVeterinaria = $this->obtenerVeterinariaDeUsuario($idUsuario);
            if ($idVeterinaria === null) {
                return [];
            }

            $sql = "SELECT
                        p.nombre,
                        p.descripcion,
                        p.fecha_vencimiento,
                        DATEDIFF(p.fecha_vencimiento, CURDATE()) AS dias_restantes,
                        i.cantidad,
                        i.categoria,
                        i.numero_lote
                    FROM producto p
                    INNER JOIN inventario i ON p.id_inventario = i.id_inventario
                    WHERE i.id_veterinaria = :id_veterinaria
                      AND p.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                    ORDER BY p.fecha_vencimiento ASC
                    LIMIT 30";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_veterinaria', $idVeterinaria, PDO::PARAM_INT);
            $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerProductosProximosVencer - ' . $e->getMessage());
            return [];
        }
    }
}
