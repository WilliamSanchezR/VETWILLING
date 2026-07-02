<?php

require_once __DIR__ . '/../../config/database.php';

class CitasVeterinario
{
    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    private function normalizarEstado(string $estado): string
    {
        $e = strtolower(trim($estado));
        if ($e === 'realizada' || $e === 'completada') return 'completada';
        if ($e === 'confirmada') return 'confirmada';
        if ($e === 'cancelada')  return 'cancelada';
        return 'pendiente';
    }

    private function estadoParaDB(string $estadoFrontend): string
    {
        $map = [
            'confirmada' => 'Confirmada',
            'completada' => 'Realizada',
            'cancelada'  => 'Cancelada',
            'pendiente'  => 'Pendiente',
        ];
        return $map[$estadoFrontend] ?? 'Pendiente';
    }

    public function listarCitas(array $filtros = []): array
    {
        try {
            $sql = "SELECT
                        a.id_agendamiento,
                        a.estado,
                        a.fecha_hora,
                        a.fecha_hora_fin,
                        a.observaciones,
                        a.tipo,
                        a.id_usuario as id_veterinario,
                        pac.id_paciente,
                        pac.nombre  AS mascota_nombre,
                        pac.especie AS mascota_especie,
                        pac.raza    AS mascota_raza,
                        pac.img_mascota,
                        pr.id_propietario,
                        CONCAT(pr.nombres, ' ', pr.apellidos) AS propietario_nombre,
                        pr.telefono AS propietario_telefono,
                        COALESCE(sub.nombre, s.nombre, a.tipo) AS servicio_nombre,
                        COALESCE(CONCAT(prf.nombres, ' ', prf.apellidos), 'Sin asignar') AS veterinario_nombre
                    FROM agendamiento a
                    INNER JOIN paciente    pac ON a.id_paciente   = pac.id_paciente
                    INNER JOIN propietario pr  ON pac.id_propietario = pr.id_propietario
                    LEFT JOIN  servicio    s   ON a.id_servicio   = s.id_servicio
                    LEFT JOIN  subservicios sub ON a.id_subservicio = sub.id_subservicio
                    LEFT JOIN  profesional prf ON a.id_usuario    = prf.id_usuario
                    WHERE 1=1";

            $params = [];

            if (!empty($filtros['id_veterinario'])) {
                $sql .= " AND a.id_usuario = :id_veterinario";
                $params[':id_veterinario'] = (int)$filtros['id_veterinario'];
            }

            if (!empty($filtros['estado'])) {
                $estadoDB = $this->estadoParaDB($filtros['estado']);
                $sql .= " AND LOWER(a.estado) = LOWER(:estado)";
                $params[':estado'] = $estadoDB;
            }

            if (!empty($filtros['fecha'])) {
                switch ($filtros['fecha']) {
                    case 'hoy':
                        $sql .= " AND DATE(a.fecha_hora) = CURDATE()";
                        break;
                    case 'manana':
                        $sql .= " AND DATE(a.fecha_hora) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
                        break;
                    case 'semana':
                        $sql .= " AND YEARWEEK(a.fecha_hora, 1) = YEARWEEK(CURDATE(), 1)";
                        break;
                    case 'mes':
                        $sql .= " AND YEAR(a.fecha_hora) = YEAR(CURDATE()) AND MONTH(a.fecha_hora) = MONTH(CURDATE())";
                        break;
                }
            }

            $sql .= " ORDER BY a.fecha_hora DESC";

            $stmt = $this->conexion->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->execute();

            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($filas as &$fila) {
                $fila['estado_normalizado'] = $this->normalizarEstado($fila['estado']);
            }
            unset($fila);

            return $filas;
        } catch (PDOException $e) {
            error_log("CitasVeterinario::listarCitas -> " . $e->getMessage());
            return [];
        }
    }

    public function actualizarEstado(int $id_agendamiento, string $estadoFrontend): bool
    {
        try {
            $estadoDB = $this->estadoParaDB($estadoFrontend);
            $stmt = $this->conexion->prepare(
                "UPDATE agendamiento SET estado = :estado WHERE id_agendamiento = :id"
            );
            $stmt->bindValue(':estado', $estadoDB);
            $stmt->bindValue(':id',     $id_agendamiento, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("CitasVeterinario::actualizarEstado -> " . $e->getMessage());
            return false;
        }
    }

    public function obtenerEstadisticas(): array
    {
        try {
            $stmt = $this->conexion->query(
                "SELECT
                    SUM(CASE WHEN LOWER(estado) = 'pendiente'  THEN 1 ELSE 0 END) AS pendientes,
                    SUM(CASE WHEN LOWER(estado) = 'confirmada' THEN 1 ELSE 0 END) AS confirmadas,
                    SUM(CASE WHEN LOWER(estado) IN ('realizada','completada') THEN 1 ELSE 0 END) AS completadas,
                    SUM(CASE WHEN LOWER(estado) = 'cancelada'  THEN 1 ELSE 0 END) AS canceladas
                FROM agendamiento"
            );
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'pendientes'  => (int)($row['pendientes']  ?? 0),
                'confirmadas' => (int)($row['confirmadas'] ?? 0),
                'completadas' => (int)($row['completadas'] ?? 0),
                'canceladas'  => (int)($row['canceladas']  ?? 0),
            ];
        } catch (PDOException $e) {
            error_log("CitasVeterinario::obtenerEstadisticas -> " . $e->getMessage());
            return ['pendientes' => 0, 'confirmadas' => 0, 'completadas' => 0, 'canceladas' => 0];
        }
    }

    public function obtenerVeterinarios(): array
    {
        try {
            $stmt = $this->conexion->query(
                "SELECT DISTINCT p.id_usuario, p.nombres, p.apellidos
                 FROM profesional p
                 INNER JOIN agendamiento a ON p.id_usuario = a.id_usuario
                 ORDER BY p.nombres ASC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("CitasVeterinario::obtenerVeterinarios -> " . $e->getMessage());
            return [];
        }
    }
}
