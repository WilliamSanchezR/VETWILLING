<?php

require_once __DIR__ . '/../helpers/session_veterinario.php';
require_once __DIR__ . '/../models/Veterinario.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido'
    ]);
    exit();
}

$rawInput = file_get_contents('php://input');
$payload = [];

if (!empty($rawInput)) {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

if (empty($payload)) {
    $payload = $_POST;
}

$accion = $payload['accion'] ?? '';
$idUsuario = (int) ($_SESSION['user']['id_usuario'] ?? 0);

if ($idUsuario <= 0) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Usuario no autenticado'
    ]);
    exit();
}

$model = new Veterinario();

switch ($accion) {
    case 'ficha_clinica':
        obtenerFichaClinica($model, $idUsuario, $payload);
        break;

    case 'guardar_vacuna':
        guardarVacuna($model, $idUsuario, $payload);
        break;

    case 'guardar_tratamiento':
        guardarTratamiento($model, $idUsuario, $payload);
        break;

    case 'guardar_consulta':
        guardarConsulta($model, $idUsuario, $payload);
        break;

    case 'guardar_nota':
        guardarNotaClinica($model, $idUsuario, $payload);
        break;

    case 'actualizar_vacuna':
        actualizarVacuna($model, $idUsuario, $payload);
        break;

    case 'eliminar_vacuna':
        eliminarVacuna($model, $idUsuario, $payload);
        break;

    case 'actualizar_tratamiento':
        actualizarTratamiento($model, $idUsuario, $payload);
        break;

    case 'eliminar_tratamiento':
        eliminarTratamiento($model, $idUsuario, $payload);
        break;

    case 'actualizar_consulta':
        actualizarConsulta($model, $idUsuario, $payload);
        break;

    case 'eliminar_consulta':
        eliminarConsulta($model, $idUsuario, $payload);
        break;

    case 'actualizar_nota':
        actualizarNotaClinica($model, $idUsuario, $payload);
        break;

    case 'eliminar_nota':
        eliminarNotaClinica($model, $idUsuario, $payload);
        break;

    case 'listar_historiales':
        listarHistorialesClinicos($model, $idUsuario, $payload);
        break;

    case 'guardar_historial':
        guardarHistorialClinico($model, $idUsuario, $payload);
        break;

    case 'listar_versiones_historial':
        listarVersionesHistorialClinico($model, $idUsuario, $payload);
        break;

    case 'consultar':
        consultarPaciente($model, $idUsuario, $payload);
        break;

    case 'actualizar':
        actualizarPaciente($model, $idUsuario, $payload);
        break;

    case 'desactivar':
        desactivarPaciente($model, $idUsuario, $payload);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Acción no válida'
        ]);
        break;
}

function obtenerFichaClinica(Veterinario $model, int $idUsuario, array $payload): void
{
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);

    if ($idPaciente <= 0) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'ID de paciente inválido',
        ]);
        return;
    }

    $data = $model->obtenerFichaClinicaCompletaPorProfesional($idUsuario, $idPaciente);
    if (!$data) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No tienes permisos para consultar la ficha clínica de este paciente',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Ficha clínica cargada correctamente',
        'data' => $data,
    ]);
}

function guardarVacuna(Veterinario $model, int $idUsuario, array $payload): void
{
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);
    $tipoVacuna = trim((string) ($payload['tipo_vacuna'] ?? ''));
    $dosis = trim((string) ($payload['dosis'] ?? ''));
    $fechaAplicacion = trim((string) ($payload['fecha_aplicacion'] ?? ''));
    $observaciones = trim((string) ($payload['observaciones'] ?? ''));

    if ($idPaciente <= 0 || $tipoVacuna === '' || $dosis === '' || $fechaAplicacion === '') {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'id_paciente, tipo_vacuna, dosis y fecha_aplicacion son obligatorios',
        ]);
        return;
    }

    $ok = $model->guardarVacunaPorProfesional($idUsuario, [
        'id_paciente' => $idPaciente,
        'tipo_vacuna' => $tipoVacuna,
        'dosis' => $dosis,
        'fecha_aplicacion' => $fechaAplicacion,
        'observaciones' => $observaciones,
    ]);

    if (!$ok) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible guardar la vacuna. Verifica permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Vacuna registrada correctamente',
    ]);
}

function guardarTratamiento(Veterinario $model, int $idUsuario, array $payload): void
{
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);
    $medicamento = trim((string) ($payload['medicamento'] ?? ''));
    $dosis = trim((string) ($payload['dosis'] ?? ''));
    $fechaInicio = trim((string) ($payload['fecha_inicio'] ?? ''));
    $fechaFin = trim((string) ($payload['fecha_fin'] ?? ''));
    $estado = trim((string) ($payload['estado'] ?? 'Activo'));
    $observaciones = trim((string) ($payload['observaciones'] ?? ''));

    if ($idPaciente <= 0 || $medicamento === '' || $dosis === '' || $fechaInicio === '') {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'id_paciente, medicamento, dosis y fecha_inicio son obligatorios',
        ]);
        return;
    }

    $ok = $model->guardarTratamientoPorProfesional($idUsuario, [
        'id_paciente' => $idPaciente,
        'medicamento' => $medicamento,
        'dosis' => $dosis,
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => $fechaFin,
        'estado' => $estado,
        'observaciones' => $observaciones,
    ]);

    if (!$ok) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible guardar el tratamiento. Verifica permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Tratamiento registrado correctamente',
    ]);
}

function guardarConsulta(Veterinario $model, int $idUsuario, array $payload): void
{
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);
    $fechaConsulta = trim((string) ($payload['fecha_consulta'] ?? ''));
    $motivo = trim((string) ($payload['motivo'] ?? ''));
    $diagnostico = trim((string) ($payload['diagnostico'] ?? ''));
    $observaciones = trim((string) ($payload['observaciones'] ?? ''));

    if ($idPaciente <= 0 || $fechaConsulta === '' || $motivo === '') {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'id_paciente, fecha_consulta y motivo son obligatorios',
        ]);
        return;
    }

    $ok = $model->guardarConsultaPorProfesional($idUsuario, [
        'id_paciente' => $idPaciente,
        'fecha_consulta' => $fechaConsulta,
        'motivo' => $motivo,
        'diagnostico' => $diagnostico,
        'observaciones' => $observaciones,
    ]);

    if (!$ok) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible guardar la consulta. Verifica permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Consulta registrada correctamente',
    ]);
}

function guardarNotaClinica(Veterinario $model, int $idUsuario, array $payload): void
{
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);
    $nota = trim((string) ($payload['nota'] ?? ''));

    if ($idPaciente <= 0 || $nota === '') {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'id_paciente y nota son obligatorios',
        ]);
        return;
    }

    $ok = $model->guardarNotaClinicaPorProfesional($idUsuario, [
        'id_paciente' => $idPaciente,
        'nota' => $nota,
    ]);

    if (!$ok) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible guardar la nota clínica. Verifica permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Nota clínica registrada correctamente',
    ]);
}

function actualizarVacuna(Veterinario $model, int $idUsuario, array $payload): void
{
    $idVacuna = (int) ($payload['id_vacuna'] ?? 0);
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);
    $tipoVacuna = trim((string) ($payload['tipo_vacuna'] ?? ''));
    $dosis = trim((string) ($payload['dosis'] ?? ''));
    $fechaAplicacion = trim((string) ($payload['fecha_aplicacion'] ?? ''));
    $observaciones = trim((string) ($payload['observaciones'] ?? ''));

    if ($idVacuna <= 0 || $idPaciente <= 0 || $tipoVacuna === '' || $dosis === '' || $fechaAplicacion === '') {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'id_vacuna, id_paciente, tipo_vacuna, dosis y fecha_aplicacion son obligatorios',
        ]);
        return;
    }

    $ok = $model->actualizarVacunaPorProfesional($idUsuario, [
        'id_vacuna' => $idVacuna,
        'id_paciente' => $idPaciente,
        'tipo_vacuna' => $tipoVacuna,
        'dosis' => $dosis,
        'fecha_aplicacion' => $fechaAplicacion,
        'observaciones' => $observaciones,
    ]);

    if (!$ok) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible actualizar la vacuna. Verifica permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Vacuna actualizada correctamente',
    ]);
}

function eliminarVacuna(Veterinario $model, int $idUsuario, array $payload): void
{
    $idVacuna = (int) ($payload['id_vacuna'] ?? 0);
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);

    if ($idVacuna <= 0 || $idPaciente <= 0) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'id_vacuna e id_paciente son obligatorios',
        ]);
        return;
    }

    $ok = $model->eliminarVacunaPorProfesional($idUsuario, $idPaciente, $idVacuna);
    if (!$ok) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible eliminar la vacuna. Verifica permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Vacuna eliminada correctamente',
    ]);
}

function actualizarTratamiento(Veterinario $model, int $idUsuario, array $payload): void
{
    $idTratamiento = (int) ($payload['id_tratamiento'] ?? 0);
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);
    $medicamento = trim((string) ($payload['medicamento'] ?? ''));
    $dosis = trim((string) ($payload['dosis'] ?? ''));
    $fechaInicio = trim((string) ($payload['fecha_inicio'] ?? ''));
    $fechaFin = trim((string) ($payload['fecha_fin'] ?? ''));
    $estado = trim((string) ($payload['estado'] ?? 'Activo'));
    $observaciones = trim((string) ($payload['observaciones'] ?? ''));

    if ($idTratamiento <= 0 || $idPaciente <= 0 || $medicamento === '' || $dosis === '' || $fechaInicio === '') {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'id_tratamiento, id_paciente, medicamento, dosis y fecha_inicio son obligatorios',
        ]);
        return;
    }

    $ok = $model->actualizarTratamientoPorProfesional($idUsuario, [
        'id_tratamiento' => $idTratamiento,
        'id_paciente' => $idPaciente,
        'medicamento' => $medicamento,
        'dosis' => $dosis,
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => $fechaFin,
        'estado' => $estado,
        'observaciones' => $observaciones,
    ]);

    if (!$ok) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible actualizar el tratamiento. Verifica permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Tratamiento actualizado correctamente',
    ]);
}

function eliminarTratamiento(Veterinario $model, int $idUsuario, array $payload): void
{
    $idTratamiento = (int) ($payload['id_tratamiento'] ?? 0);
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);

    if ($idTratamiento <= 0 || $idPaciente <= 0) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'id_tratamiento e id_paciente son obligatorios',
        ]);
        return;
    }

    $ok = $model->eliminarTratamientoPorProfesional($idUsuario, $idPaciente, $idTratamiento);
    if (!$ok) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible eliminar el tratamiento. Verifica permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Tratamiento eliminado correctamente',
    ]);
}

function actualizarConsulta(Veterinario $model, int $idUsuario, array $payload): void
{
    $idConsulta = (int) ($payload['id_consulta'] ?? 0);
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);
    $fechaConsulta = trim((string) ($payload['fecha_consulta'] ?? ''));
    $motivo = trim((string) ($payload['motivo'] ?? ''));
    $diagnostico = trim((string) ($payload['diagnostico'] ?? ''));
    $observaciones = trim((string) ($payload['observaciones'] ?? ''));

    if ($idConsulta <= 0 || $idPaciente <= 0 || $fechaConsulta === '' || $motivo === '') {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'id_consulta, id_paciente, fecha_consulta y motivo son obligatorios',
        ]);
        return;
    }

    $ok = $model->actualizarConsultaPorProfesional($idUsuario, [
        'id_consulta' => $idConsulta,
        'id_paciente' => $idPaciente,
        'fecha_consulta' => $fechaConsulta,
        'motivo' => $motivo,
        'diagnostico' => $diagnostico,
        'observaciones' => $observaciones,
    ]);

    if (!$ok) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible actualizar la consulta. Verifica permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Consulta actualizada correctamente',
    ]);
}

function eliminarConsulta(Veterinario $model, int $idUsuario, array $payload): void
{
    $idConsulta = (int) ($payload['id_consulta'] ?? 0);
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);

    if ($idConsulta <= 0 || $idPaciente <= 0) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'id_consulta e id_paciente son obligatorios',
        ]);
        return;
    }

    $ok = $model->eliminarConsultaPorProfesional($idUsuario, $idPaciente, $idConsulta);
    if (!$ok) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible eliminar la consulta. Verifica permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Consulta eliminada correctamente',
    ]);
}

function actualizarNotaClinica(Veterinario $model, int $idUsuario, array $payload): void
{
    $idNota = (int) ($payload['id_nota'] ?? 0);
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);
    $nota = trim((string) ($payload['nota'] ?? ''));

    if ($idNota <= 0 || $idPaciente <= 0 || $nota === '') {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'id_nota, id_paciente y nota son obligatorios',
        ]);
        return;
    }

    $ok = $model->actualizarNotaClinicaPorProfesional($idUsuario, [
        'id_nota' => $idNota,
        'id_paciente' => $idPaciente,
        'nota' => $nota,
    ]);

    if (!$ok) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible actualizar la nota clínica. Verifica permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Nota clínica actualizada correctamente',
    ]);
}

function eliminarNotaClinica(Veterinario $model, int $idUsuario, array $payload): void
{
    $idNota = (int) ($payload['id_nota'] ?? 0);
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);

    if ($idNota <= 0 || $idPaciente <= 0) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'id_nota e id_paciente son obligatorios',
        ]);
        return;
    }

    $ok = $model->eliminarNotaClinicaPorProfesional($idUsuario, $idPaciente, $idNota);
    if (!$ok) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible eliminar la nota clínica. Verifica permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Nota clínica eliminada correctamente',
    ]);
}

function listarHistorialesClinicos(Veterinario $model, int $idUsuario, array $payload): void
{
    $filtros = [
        'paciente' => trim((string) ($payload['paciente'] ?? '')),
        'fecha' => trim((string) ($payload['fecha'] ?? '')),
        'veterinario' => trim((string) ($payload['veterinario'] ?? '')),
        'acceso' => trim((string) ($payload['acceso'] ?? '')),
    ];

    $historiales = $model->listarHistorialesClinicosPorProfesional($idUsuario, $filtros);

    echo json_encode([
        'status' => 'success',
        'message' => 'Historiales cargados correctamente',
        'data' => $historiales,
    ]);
}

function guardarHistorialClinico(Veterinario $model, int $idUsuario, array $payload): void
{
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);
    $idHistorial = (int) ($payload['id_historial'] ?? 0);

    $fechaAtencion = trim((string) ($payload['fecha_atencion'] ?? ''));
    $motivoConsulta = trim((string) ($payload['motivo_consulta'] ?? ''));
    $diagnostico = trim((string) ($payload['diagnostico'] ?? ''));
    $tratamientos = trim((string) ($payload['tratamientos_aplicados'] ?? ''));
    $medicacion = trim((string) ($payload['medicacion_recetada'] ?? ''));
    $observaciones = trim((string) ($payload['observaciones_adicionales'] ?? ''));

    if ($idPaciente <= 0) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'Debe seleccionar un paciente válido',
        ]);
        return;
    }

    if ($fechaAtencion === '' || $motivoConsulta === '') {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'La fecha de atención y el motivo de consulta son obligatorios',
        ]);
        return;
    }

    $resultado = $model->guardarHistorialClinicoPorProfesional($idUsuario, [
        'id_historial' => $idHistorial,
        'id_paciente' => $idPaciente,
        'fecha_atencion' => $fechaAtencion,
        'motivo_consulta' => $motivoConsulta,
        'diagnostico' => $diagnostico,
        'tratamientos_aplicados' => $tratamientos,
        'medicacion_recetada' => $medicacion,
        'observaciones_adicionales' => $observaciones,
    ]);

    if (!$resultado) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible guardar el historial. Verifique permisos y datos.',
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Historial clínico guardado correctamente',
        'data' => $resultado,
    ]);
}

function listarVersionesHistorialClinico(Veterinario $model, int $idUsuario, array $payload): void
{
    $idHistorial = (int) ($payload['id_historial'] ?? 0);

    if ($idHistorial <= 0) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'Debe indicar un historial válido para consultar versiones',
        ]);
        return;
    }

    $versiones = $model->listarVersionesHistorialClinicoPorProfesional($idUsuario, $idHistorial);

    echo json_encode([
        'status' => 'success',
        'message' => 'Versiones consultadas correctamente',
        'data' => $versiones,
    ]);
}

function consultarPaciente(Veterinario $model, int $idUsuario, array $payload): void
{
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);

    if ($idPaciente <= 0) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'ID de paciente inválido'
        ]);
        return;
    }

    $paciente = $model->obtenerDetallePacientePorVeterinario($idUsuario, $idPaciente);
    if (!$paciente) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Paciente no encontrado o no asignado al profesional'
        ]);
        return;
    }

    $historial = $model->obtenerHistorialPacientePorVeterinario($idUsuario, $idPaciente, 8);

    echo json_encode([
        'status' => 'success',
        'message' => 'Paciente encontrado',
        'data' => [
            'paciente' => $paciente,
            'historial' => $historial
        ]
    ]);
}

function actualizarPaciente(Veterinario $model, int $idUsuario, array $payload): void
{
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);
    $nombre = trim((string) ($payload['nombre'] ?? ''));
    $especie = trim((string) ($payload['especie'] ?? ''));
    $raza = trim((string) ($payload['raza'] ?? ''));
    $edadNumero = (int) ($payload['edad_numero'] ?? 0);
    $edadUnidad = trim((string) ($payload['edad_unidad'] ?? ''));
    $sexo = trim((string) ($payload['sexo'] ?? ''));

    if ($idPaciente <= 0 || $nombre === '' || $especie === '' || $raza === '' || $edadNumero <= 0 || $edadUnidad === '' || $sexo === '') {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'Todos los campos obligatorios deben ser válidos'
        ]);
        return;
    }

    $ok = $model->actualizarDatosPacientePorVeterinario($idUsuario, [
        'id_paciente' => $idPaciente,
        'nombre' => $nombre,
        'especie' => $especie,
        'raza' => $raza,
        'edad_numero' => $edadNumero,
        'edad_unidad' => $edadUnidad,
        'sexo' => $sexo
    ]);

    if (!$ok) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'No se pudo actualizar el paciente'
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Paciente actualizado correctamente'
    ]);
}

function desactivarPaciente(Veterinario $model, int $idUsuario, array $payload): void
{
    $idPaciente = (int) ($payload['id_paciente'] ?? 0);

    if ($idPaciente <= 0) {
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'ID de paciente inválido'
        ]);
        return;
    }

    $ok = $model->desactivarPacientePorVeterinario($idUsuario, $idPaciente);

    if (!$ok) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'No se pudo desactivar el paciente'
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Paciente desactivado correctamente'
    ]);
}
