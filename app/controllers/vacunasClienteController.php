<?php
/**
 * vacunasClienteController.php
 * API: GET /cliente/api/vacunas?id_mascota=X
 * Devuelve las vacunas aplicadas a una mascota del propietario en sesión.
 */

require_once BASE_PATH . '/app/helpers/session_propietario.php';

header('Content-Type: application/json; charset=utf-8');

/* ── Validar sesión ─────────────────────────────────────────────────── */
if (!isset($_SESSION['user']['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit();
}

$id_usuario = (int)$_SESSION['user']['id_usuario'];

/* ── Parámetro ──────────────────────────────────────────────────────── */
$id_mascota = (int)($_GET['id_mascota'] ?? 0);
if ($id_mascota <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'id_mascota inválido']);
    exit();
}

/* ── Conexión ───────────────────────────────────────────────────────── */
require_once BASE_PATH . '/app/models/Conexion.php';
$conexion = (new Conexion())->getConexion();

/* ── Verificar propiedad: la mascota debe pertenecer al propietario ─── */
$stmtProp = $conexion->prepare(
    "SELECT p.id_propietario
     FROM propietario p
     WHERE p.id_usuario = :id_usuario
     LIMIT 1"
);
$stmtProp->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
$stmtProp->execute();
$propRow = $stmtProp->fetch(PDO::FETCH_ASSOC);

if (!$propRow) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Propietario no encontrado']);
    exit();
}

$id_propietario = (int)$propRow['id_propietario'];

$stmtOwner = $conexion->prepare(
    "SELECT id_paciente
     FROM paciente
     WHERE id_paciente = :id_paciente
       AND id_propietario = :id_propietario
       AND estado = 'Activo'
     LIMIT 1"
);
$stmtOwner->bindValue(':id_paciente',    $id_mascota,    PDO::PARAM_INT);
$stmtOwner->bindValue(':id_propietario', $id_propietario, PDO::PARAM_INT);
$stmtOwner->execute();

if (!$stmtOwner->fetch(PDO::FETCH_ASSOC)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No tienes acceso a esta mascota']);
    exit();
}

/* ── Consultar vacunas aplicadas ────────────────────────────────────── */
try {
    $stmtVac = $conexion->prepare(
        "SELECT
             pv.id_vacuna,
             pv.tipo_vacuna,
             pv.dosis,
             pv.fecha_aplicacion,
             pv.observaciones,
             pv.created_at,
             CONCAT(u.nombres, ' ', u.apellidos) AS nombre_veterinario
         FROM paciente_vacuna pv
         LEFT JOIN usuario u ON u.id_usuario = pv.id_usuario_profesional
         WHERE pv.id_paciente = :id_paciente
         ORDER BY pv.fecha_aplicacion DESC, pv.id_vacuna DESC"
    );
    $stmtVac->bindValue(':id_paciente', $id_mascota, PDO::PARAM_INT);
    $stmtVac->execute();
    $vacunasRaw = $stmtVac->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error en vacunasClienteController: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al consultar vacunas']);
    exit();
}

/* ── Formatear respuesta ────────────────────────────────────────────── */
$aplicadas = array_map(function (array $v): array {
    return [
        'id'          => (int)$v['id_vacuna'],
        'nombre'      => $v['tipo_vacuna'],
        'dosis'       => $v['dosis'],
        'fecha'       => $v['fecha_aplicacion'],
        'veterinario' => $v['nombre_veterinario'] ?? 'No registrado',
        'observaciones' => $v['observaciones'] ?? null,
    ];
}, $vacunasRaw);

echo json_encode([
    'status'    => 'ok',
    'aplicadas' => $aplicadas,
    'pendientes' => [],
    'proximas'   => [],
], JSON_UNESCAPED_UNICODE);
