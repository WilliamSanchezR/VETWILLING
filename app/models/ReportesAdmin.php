<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo de reportes para administradores — RFS 14
 * Consultas globales (todas las veterinarias) filtrables por id_veterinaria.
 */
class ReportesAdmin
{
    private $conexion;

    private const ESTADOS_ATENDIDOS = [
        'REALIZADA', 'REALIZADO', 'ATENDIDA', 'ATENDIDO',
        'COMPLETADA', 'COMPLETADO', 'FINALIZADA', 'FINALIZADO'
    ];

    private const ESTADOS_CANCELADOS = [
        'CANCELADA', 'CANCELADO', 'ANULADA', 'ANULADO'
    ];

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    // -----------------------------------------------------------------------
    // Helpers internos
    // -----------------------------------------------------------------------

    private function estadoNormalizadoSql(string $columna): string
    {
        $atendidos  = $this->listaSql(self::ESTADOS_ATENDIDOS);
        $cancelados = $this->listaSql(self::ESTADOS_CANCELADOS);

        return "CASE
                    WHEN UPPER(TRIM(COALESCE({$columna}, ''))) IN ({$atendidos})  THEN 'ATENDIDA'
                    WHEN UPPER(TRIM(COALESCE({$columna}, ''))) IN ({$cancelados}) THEN 'CANCELADA'
                    ELSE 'PENDIENTE'
                END";
    }

    private function listaSql(array $values): string
    {
        return implode(', ', array_map(function ($v) {
            return "'" . str_replace("'", "''", $v) . "'";
        }, $values));
    }

    /**
     * Devuelve fragmento WHERE/JOIN para filtrar por veterinaria opcional.
     * Requiere que la query ya tenga JOIN a profesional (alias prof) y
     * profesional_veterinaria (alias pv).
     */
    private function whereVeterinaria(?int $idVeterinaria): array
    {
        if ($idVeterinaria === null) {
            return ['', []];
        }
        return [
            ' AND pv.id_veterinaria = :id_veterinaria ',
            [':id_veterinaria' => ['value' => $idVeterinaria, 'type' => PDO::PARAM_INT]]
        ];
    }

    private function bindParams($stmt, array $params): void
    {
        foreach ($params as $key => $meta) {
            $stmt->bindValue($key, $meta['value'], $meta['type']);
        }
    }

    // -----------------------------------------------------------------------
    // Catálogos
    // -----------------------------------------------------------------------

    public function obtenerListaVeterinarias(): array
    {
        try {
            $sql = "SELECT id_veterinaria, nombre FROM veterinaria ORDER BY nombre ASC";
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en ReportesAdmin::obtenerListaVeterinarias - ' . $e->getMessage());
            return [];
        }
    }

    // -----------------------------------------------------------------------
    // Resumen global de citas e ingresos — subtarea 1, 2, 4
    // -----------------------------------------------------------------------

    public function obtenerResumenGlobal(string $fechaInicio, string $fechaFin, ?int $idVeterinaria = null): array
    {
        try {
            $estadoNorm = $this->estadoNormalizadoSql('a.estado');
            [$whereVet, $paramsVet] = $this->whereVeterinaria($idVeterinaria);

            $sql = "SELECT
                        COALESCE(SUM(CASE
                            WHEN {$estadoNorm} <> 'CANCELADA' THEN COALESCE(sub.costo, 0)
                            ELSE 0
                        END), 0) AS ingresos_totales,
                        SUM(CASE WHEN {$estadoNorm} = 'ATENDIDA' THEN 1 ELSE 0 END) AS citas_atendidas,
                        SUM(CASE WHEN {$estadoNorm} = 'CANCELADA' THEN 1 ELSE 0 END) AS citas_canceladas,
                        COUNT(*) AS total_citas
                    FROM agendamiento a
                    INNER JOIN profesional prof ON a.id_usuario = prof.id_usuario
                    INNER JOIN profesional_veterinaria pv ON prof.id_profesional = pv.id_profesional
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    WHERE DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                    {$whereVet}";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $this->bindParams($stmt, $paramsVet);
            $stmt->execute();
            $resumen = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            // Nuevos pacientes en el período
            $sqlNuevos = "SELECT COUNT(DISTINCT a.id_paciente)
                          FROM agendamiento a
                          INNER JOIN profesional prof ON a.id_usuario = prof.id_usuario
                          INNER JOIN profesional_veterinaria pv ON prof.id_profesional = pv.id_profesional
                          WHERE a.id_paciente IS NOT NULL
                            AND DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                          {$whereVet}";

            $stmtNuevos = $this->conexion->prepare($sqlNuevos);
            $stmtNuevos->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmtNuevos->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $this->bindParams($stmtNuevos, $paramsVet);
            $stmtNuevos->execute();
            $pacientesAtendidos = (int)$stmtNuevos->fetchColumn();

            $citasAtendidas = (int)($resumen['citas_atendidas'] ?? 0);
            $totalCitas     = (int)($resumen['total_citas'] ?? 0);
            $cumplimiento   = $totalCitas > 0 ? round(($citasAtendidas / $totalCitas) * 100, 1) : 0;

            return [
                'ingresos_totales'   => (float)($resumen['ingresos_totales'] ?? 0),
                'citas_atendidas'    => $citasAtendidas,
                'citas_canceladas'   => (int)($resumen['citas_canceladas'] ?? 0),
                'pacientes_atendidos'=> $pacientesAtendidos,
                'cumplimiento'       => $cumplimiento,
                'total_citas'        => $totalCitas,
            ];
        } catch (PDOException $e) {
            error_log('Error en ReportesAdmin::obtenerResumenGlobal - ' . $e->getMessage());
            return [
                'ingresos_totales' => 0, 'citas_atendidas' => 0,
                'citas_canceladas' => 0, 'pacientes_atendidos' => 0,
                'cumplimiento' => 0, 'total_citas' => 0
            ];
        }
    }

    // -----------------------------------------------------------------------
    // Estados de citas — subtarea 4
    // -----------------------------------------------------------------------

    public function obtenerEstadosCitas(string $fechaInicio, string $fechaFin, ?int $idVeterinaria = null): array
    {
        try {
            $estadoNorm = $this->estadoNormalizadoSql('a.estado');
            [$whereVet, $paramsVet] = $this->whereVeterinaria($idVeterinaria);

            $sql = "SELECT
                        {$estadoNorm} AS estado,
                        COUNT(*) AS total
                    FROM agendamiento a
                    INNER JOIN profesional prof ON a.id_usuario = prof.id_usuario
                    INNER JOIN profesional_veterinaria pv ON prof.id_profesional = pv.id_profesional
                    WHERE DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                    {$whereVet}
                    GROUP BY {$estadoNorm}";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $this->bindParams($stmt, $paramsVet);
            $stmt->execute();

            $resultado = ['atendidas' => 0, 'canceladas' => 0, 'pendientes' => 0, 'total' => 0];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $cnt = (int)$row['total'];
                if ($row['estado'] === 'ATENDIDA')       { $resultado['atendidas']  += $cnt; }
                elseif ($row['estado'] === 'CANCELADA')  { $resultado['canceladas'] += $cnt; }
                else                                     { $resultado['pendientes'] += $cnt; }
                $resultado['total'] += $cnt;
            }
            return $resultado;
        } catch (PDOException $e) {
            error_log('Error en ReportesAdmin::obtenerEstadosCitas - ' . $e->getMessage());
            return ['atendidas' => 0, 'canceladas' => 0, 'pendientes' => 0, 'total' => 0];
        }
    }

    // -----------------------------------------------------------------------
    // Desempeño del personal — subtarea 5
    // -----------------------------------------------------------------------

    public function obtenerDesempenioPersonal(string $fechaInicio, string $fechaFin, ?int $idVeterinaria = null): array
    {
        try {
            $estadoNorm = $this->estadoNormalizadoSql('a.estado');
            [$whereVet, $paramsVet] = $this->whereVeterinaria($idVeterinaria);

            $sql = "SELECT
                        prof.id_profesional,
                        CONCAT(prof.nombres, ' ', prof.apellidos) AS nombre_profesional,
                        v.nombre AS veterinaria,
                        COUNT(*) AS total_citas,
                        SUM(CASE WHEN {$estadoNorm} = 'ATENDIDA' THEN 1 ELSE 0 END) AS atendidas,
                        SUM(CASE WHEN {$estadoNorm} = 'CANCELADA' THEN 1 ELSE 0 END) AS canceladas,
                        ROUND(AVG(CASE
                            WHEN {$estadoNorm} = 'ATENDIDA'
                            THEN TIMESTAMPDIFF(MINUTE, a.fecha_hora, a.fecha_hora_fin)
                            ELSE NULL
                        END), 0) AS promedio_minutos
                    FROM agendamiento a
                    INNER JOIN profesional prof ON a.id_usuario = prof.id_usuario
                    INNER JOIN profesional_veterinaria pv ON prof.id_profesional = pv.id_profesional
                    INNER JOIN veterinaria v ON pv.id_veterinaria = v.id_veterinaria
                    WHERE DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                    {$whereVet}
                    GROUP BY prof.id_profesional, prof.nombres, prof.apellidos, v.nombre
                    ORDER BY atendidas DESC, total_citas DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $this->bindParams($stmt, $paramsVet);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($rows as &$row) {
                $total = (int)$row['total_citas'];
                $atendidas = (int)$row['atendidas'];
                $row['tasa_cumplimiento'] = $total > 0 ? round(($atendidas / $total) * 100, 1) : 0;
                $row['promedio_minutos'] = $row['promedio_minutos'] !== null ? (int)$row['promedio_minutos'] : null;
            }
            unset($row);

            return $rows;
        } catch (PDOException $e) {
            error_log('Error en ReportesAdmin::obtenerDesempenioPersonal - ' . $e->getMessage());
            return [];
        }
    }

    // -----------------------------------------------------------------------
    // Inventario global — subtarea 3
    // -----------------------------------------------------------------------

    public function obtenerResumenInventario(?int $idVeterinaria = null): array
    {
        try {
            $whereVet = $idVeterinaria !== null ? 'AND i.id_veterinaria = :id_veterinaria' : '';
            $params   = [];
            if ($idVeterinaria !== null) {
                $params[':id_veterinaria'] = ['value' => $idVeterinaria, 'type' => PDO::PARAM_INT];
            }

            $sql = "SELECT
                        COUNT(*) AS total_productos,
                        SUM(CASE WHEN p.fecha_vencimiento < CURDATE() THEN 1 ELSE 0 END) AS vencidos,
                        SUM(CASE WHEN p.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                                 THEN 1 ELSE 0 END) AS por_vencer,
                        SUM(CASE WHEN p.fecha_vencimiento > DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                                 OR p.fecha_vencimiento IS NULL
                                 THEN 1 ELSE 0 END) AS vigentes,
                        SUM(COALESCE(i.cantidad, 0)) AS cantidad_total,
                        SUM(COALESCE(p.precio_venta, 0)) AS valor_total_estimado
                    FROM inventario i
                    INNER JOIN producto p ON p.id_inventario = i.id_inventario
                    WHERE 1=1 {$whereVet}";

            $stmt = $this->conexion->prepare($sql);
            $this->bindParams($stmt, $params);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'total_productos'      => (int)($row['total_productos'] ?? 0),
                'vencidos'             => (int)($row['vencidos'] ?? 0),
                'por_vencer'           => (int)($row['por_vencer'] ?? 0),
                'vigentes'             => (int)($row['vigentes'] ?? 0),
                'cantidad_total'       => (int)($row['cantidad_total'] ?? 0),
                'valor_total_estimado' => (float)($row['valor_total_estimado'] ?? 0),
            ];
        } catch (PDOException $e) {
            error_log('Error en ReportesAdmin::obtenerResumenInventario - ' . $e->getMessage());
            return [
                'total_productos' => 0, 'vencidos' => 0, 'por_vencer' => 0,
                'vigentes' => 0, 'cantidad_total' => 0, 'valor_total_estimado' => 0
            ];
        }
    }

    public function obtenerProductosProximosVencer(?int $idVeterinaria = null, int $dias = 60): array
    {
        try {
            $whereVet = $idVeterinaria !== null ? 'AND i.id_veterinaria = :id_veterinaria' : '';
            $params   = [];
            if ($idVeterinaria !== null) {
                $params[':id_veterinaria'] = ['value' => $idVeterinaria, 'type' => PDO::PARAM_INT];
            }

            $sql = "SELECT
                        p.id_producto,
                        p.nombre,
                        p.descripcion,
                        p.fecha_vencimiento,
                        DATEDIFF(p.fecha_vencimiento, CURDATE()) AS dias_restantes,
                        i.cantidad,
                        i.categoria,
                        i.numero_lote,
                        v.nombre AS veterinaria
                    FROM producto p
                    INNER JOIN inventario i ON p.id_inventario = i.id_inventario
                    INNER JOIN veterinaria v ON i.id_veterinaria = v.id_veterinaria
                    WHERE p.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                    {$whereVet}
                    ORDER BY p.fecha_vencimiento ASC
                    LIMIT 50";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
            $this->bindParams($stmt, $params);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en ReportesAdmin::obtenerProductosProximosVencer - ' . $e->getMessage());
            return [];
        }
    }

    // -----------------------------------------------------------------------
    // Ranking de veterinarias — subtarea 2, 5
    // -----------------------------------------------------------------------

    public function obtenerTopVeterinarias(string $fechaInicio, string $fechaFin): array
    {
        try {
            $estadoNorm = $this->estadoNormalizadoSql('a.estado');

            $sql = "SELECT
                        v.id_veterinaria,
                        v.nombre AS veterinaria,
                        COUNT(*) AS total_citas,
                        SUM(CASE WHEN {$estadoNorm} = 'ATENDIDA' THEN 1 ELSE 0 END) AS atendidas,
                        SUM(CASE WHEN {$estadoNorm} = 'CANCELADA' THEN 1 ELSE 0 END) AS canceladas,
                        COALESCE(SUM(CASE
                            WHEN {$estadoNorm} <> 'CANCELADA' THEN COALESCE(sub.costo, 0)
                            ELSE 0
                        END), 0) AS ingresos
                    FROM agendamiento a
                    INNER JOIN profesional prof ON a.id_usuario = prof.id_usuario
                    INNER JOIN profesional_veterinaria pv ON prof.id_profesional = pv.id_profesional
                    INNER JOIN veterinaria v ON pv.id_veterinaria = v.id_veterinaria
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    WHERE DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                    GROUP BY v.id_veterinaria, v.nombre
                    ORDER BY ingresos DESC, atendidas DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en ReportesAdmin::obtenerTopVeterinarias - ' . $e->getMessage());
            return [];
        }
    }

    // -----------------------------------------------------------------------
    // Ingresos mensuales últimos 6 meses — subtarea 2
    // -----------------------------------------------------------------------

    public function obtenerIngresosMensuales(?int $idVeterinaria = null): array
    {
        try {
            $inicio = (new DateTime('first day of -5 months'))->format('Y-m-01');
            $estadoNorm = $this->estadoNormalizadoSql('a.estado');
            [$whereVet, $paramsVet] = $this->whereVeterinaria($idVeterinaria);

            $sql = "SELECT
                        DATE_FORMAT(a.fecha_hora, '%Y-%m') AS periodo,
                        COALESCE(SUM(COALESCE(sub.costo, 0)), 0) AS total
                    FROM agendamiento a
                    INNER JOIN profesional prof ON a.id_usuario = prof.id_usuario
                    INNER JOIN profesional_veterinaria pv ON prof.id_profesional = pv.id_profesional
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    WHERE a.fecha_hora >= :inicio
                      AND {$estadoNorm} <> 'CANCELADA'
                    {$whereVet}
                    GROUP BY DATE_FORMAT(a.fecha_hora, '%Y-%m')
                    ORDER BY periodo ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':inicio', $inicio, PDO::PARAM_STR);
            $this->bindParams($stmt, $paramsVet);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $mapa = [];
            foreach ($rows as $row) {
                $mapa[$row['periodo']] = (float)$row['total'];
            }

            $mesesES = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',
                        7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];

            $labels = [];
            $data   = [];
            $cursor = new DateTime('first day of -5 months');
            for ($i = 0; $i < 6; $i++) {
                $clave    = $cursor->format('Y-m');
                $labels[] = $mesesES[(int)$cursor->format('n')];
                $data[]   = $mapa[$clave] ?? 0;
                $cursor->modify('+1 month');
            }

            return ['labels' => $labels, 'data' => $data];
        } catch (PDOException $e) {
            error_log('Error en ReportesAdmin::obtenerIngresosMensuales - ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }
}
