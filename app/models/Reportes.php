<?php

require_once __DIR__ . '/../../config/database.php';

class Reportes
{
    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    public function obtenerResumenGeneral($idUsuario, $fechaInicio, $fechaFin)
    {
        try {
            $sql = "SELECT
                        COALESCE(SUM(CASE
                            WHEN UPPER(a.estado) NOT IN ('CANCELADA', 'CANCELADO') THEN COALESCE(sub.costo, 0)
                            ELSE 0
                        END), 0) AS ingresos_totales,
                        SUM(CASE
                            WHEN UPPER(a.estado) IN ('REALIZADA', 'ATENDIDA', 'COMPLETADA') THEN 1
                            ELSE 0
                        END) AS citas_atendidas,
                        COUNT(*) AS total_citas
                    FROM agendamiento a
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $stmt->execute();
            $resumen = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $sqlNuevos = "SELECT COUNT(*)
                          FROM (
                              SELECT a.id_paciente, MIN(DATE(a.fecha_hora)) AS primera_fecha
                              FROM agendamiento a
                              WHERE a.id_usuario = :id_usuario
                                AND a.id_paciente IS NOT NULL
                              GROUP BY a.id_paciente
                          ) t
                          WHERE t.primera_fecha BETWEEN :fecha_inicio AND :fecha_fin";

            $stmtNuevos = $this->conexion->prepare($sqlNuevos);
            $stmtNuevos->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmtNuevos->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmtNuevos->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
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

    public function obtenerIngresosUltimosSeisMeses($idUsuario)
    {
        try {
            $inicio = (new DateTime('first day of -5 months'))->format('Y-m-01');

            $sql = "SELECT
                        DATE_FORMAT(a.fecha_hora, '%Y-%m') AS periodo,
                        COALESCE(SUM(COALESCE(sub.costo, 0)), 0) AS total
                    FROM agendamiento a
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    WHERE a.id_usuario = :id_usuario
                      AND a.fecha_hora >= :inicio
                      AND UPPER(a.estado) NOT IN ('CANCELADA', 'CANCELADO')
                    GROUP BY DATE_FORMAT(a.fecha_hora, '%Y-%m')
                    ORDER BY periodo ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':inicio', $inicio, PDO::PARAM_STR);
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

    public function obtenerServiciosMasSolicitados($idUsuario, $fechaInicio, $fechaFin, $limite = 4)
    {
        try {
            $sql = "SELECT
                        COALESCE(s.nombre, 'Sin servicio') AS nombre,
                        COUNT(*) AS total
                    FROM agendamiento a
                    LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                    GROUP BY COALESCE(s.nombre, 'Sin servicio')
                    ORDER BY total DESC
                    LIMIT :limite";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
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

    public function obtenerTopTratamientos($idUsuario, $fechaInicio, $fechaFin, $limite = 5)
    {
        try {
            $sql = "SELECT
                        COALESCE(sub.nombre, a.tipo, 'Sin tratamiento') AS nombre,
                        COUNT(*) AS total
                    FROM agendamiento a
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                    GROUP BY COALESCE(sub.nombre, a.tipo, 'Sin tratamiento')
                    ORDER BY total DESC
                    LIMIT :limite";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en Reportes::obtenerTopTratamientos - ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerPacientesPorEspecie($idUsuario, $fechaInicio, $fechaFin)
    {
        try {
            $sql = "SELECT
                        COALESCE(p.especie, 'Sin especie') AS especie,
                        COUNT(DISTINCT a.id_paciente) AS total
                    FROM agendamiento a
                    LEFT JOIN paciente p ON a.id_paciente = p.id_paciente
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                    GROUP BY COALESCE(p.especie, 'Sin especie')
                    ORDER BY total DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
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

    public function obtenerResumenFinancieroMensual($idUsuario, $anio)
    {
        try {
            $anio = (int)$anio;
            $mesActual = (int)date('n');
            $ultimoMes = ((int)date('Y') === $anio) ? $mesActual : 12;

            $sql = "SELECT
                        COALESCE(s.nombre, 'Sin servicio') AS concepto,
                        MONTH(a.fecha_hora) AS mes,
                        COALESCE(SUM(COALESCE(sub.costo, 0)), 0) AS total
                    FROM agendamiento a
                    LEFT JOIN servicio s ON a.id_servicio = s.id_servicio
                    LEFT JOIN subservicios sub ON a.id_subservicio = sub.id_subservicio
                    WHERE a.id_usuario = :id_usuario
                      AND YEAR(a.fecha_hora) = :anio
                      AND MONTH(a.fecha_hora) BETWEEN 1 AND :ultimo_mes
                      AND UPPER(a.estado) NOT IN ('CANCELADA', 'CANCELADO')
                    GROUP BY COALESCE(s.nombre, 'Sin servicio'), MONTH(a.fecha_hora)
                    ORDER BY concepto ASC, mes ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->bindParam(':ultimo_mes', $ultimoMes, PDO::PARAM_INT);
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
}
