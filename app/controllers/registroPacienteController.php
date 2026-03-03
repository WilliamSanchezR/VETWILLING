<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Propietario.php';
require_once __DIR__ . '/../models/Mascotas.php';
require_once __DIR__ . '/../models/PacienteProfesionalAsignacion.php';
require_once __DIR__ . '/../models/DisponibilidadUsuario.php';
require_once __DIR__ . '/../models/Veterinario.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    registrarPacienteConPropietario();
}

function resolverIdVeterinariaRegistro(): ?int
{
    $idVeterinariaSesion = $_SESSION['user']['id_veterinaria'] ?? ($_SESSION['id_veterinaria'] ?? null);

    if (!empty($idVeterinariaSesion)) {
        if (is_string($idVeterinariaSesion) && strpos($idVeterinariaSesion, ',') !== false) {
            $idVeterinariaSesion = trim(explode(',', $idVeterinariaSesion)[0]);
        }

        if (is_numeric($idVeterinariaSesion)) {
            return (int) $idVeterinariaSesion;
        }
    }

    $idUsuario = $_SESSION['user']['id_usuario'] ?? null;
    if (empty($idUsuario)) {
        return null;
    }

    $disponibilidadModel = new DisponibilidadUsuario();
    $idVeterinariaRelacion = $disponibilidadModel->obtenerVeterinariaPorUsuario((int) $idUsuario);
    if (!empty($idVeterinariaRelacion)) {
        return (int) $idVeterinariaRelacion;
    }

    return null;
}

function registrarPacienteConPropietario()
{
    // Establecer header JSON
    header('Content-Type: application/json');

    $id_veterinaria = resolverIdVeterinariaRegistro();

    // Verificar que el usuario esté autenticado
    if (empty($id_veterinaria)) {
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo identificar la veterinaria'
        ]);
        exit();
    }

    $id_usuario_profesional = isset($_SESSION['user']['id_usuario']) ? (int) $_SESSION['user']['id_usuario'] : null;

    // Log para debug
    error_log("ID Veterinaria obtenida: " . $id_veterinaria);
    error_log("POST recibido: " . print_r($_POST, true));

    // Capturar datos del propietario
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    // Capturar datos de mascotas (nuevo formato) o mascota única (compatibilidad)
    $mascotas = [];

    if (!empty($_POST['mascotas'])) {
        $mascotasDecodificadas = json_decode($_POST['mascotas'], true);
        if (is_array($mascotasDecodificadas)) {
            $mascotas = $mascotasDecodificadas;
        }
    }

    if (empty($mascotas)) {
        $mascotas[] = [
            'nombre' => $_POST['nombre_mascota'] ?? '',
            'especie' => $_POST['especie'] ?? '',
            'raza' => $_POST['raza'] ?? '',
            'sexo' => $_POST['sexo'] ?? '',
            'edad_numero' => $_POST['edad_numero'] ?? '',
            'edad_unidad' => $_POST['edad_unidad'] ?? ''
        ];
    }

    // Validar campos obligatorios del propietario
    if (
        empty($nombres) || empty($apellidos) || empty($tipo_documento) || empty($numero_documento) ||
        empty($telefono) || empty($email) || empty($direccion)
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Complete todos los campos del propietario'
        ]);
        exit();
    }

    // Validar campos obligatorios de cada mascota
    foreach ($mascotas as $index => $mascota) {
        $nombreMascota = trim($mascota['nombre'] ?? '');
        $especieMascota = trim($mascota['especie'] ?? '');
        $razaMascota = trim($mascota['raza'] ?? '');
        $sexoMascota = trim($mascota['sexo'] ?? '');
        $edadNumeroMascota = trim((string) ($mascota['edad_numero'] ?? ''));
        $edadUnidadMascota = trim($mascota['edad_unidad'] ?? '');

        if (
            $nombreMascota === '' || $especieMascota === '' || $razaMascota === '' || $sexoMascota === '' ||
            $edadNumeroMascota === '' || $edadUnidadMascota === ''
        ) {
            $numeroMascota = $index + 1;
            echo json_encode([
                'success' => false,
                'message' => "Complete todos los campos de la mascota #{$numeroMascota}"
            ]);
            exit();
        }
    }

    try {
        // 1. Registrar el propietario
        $propietarioModel = new Propietario();

        $dataPropietario = [
            'tipo_documento' => $tipo_documento,
            'numero_documento' => $numero_documento,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'id_veterinaria' => $id_veterinaria,
            'email' => $email
        ];

        error_log("Datos propietario a insertar: " . print_r($dataPropietario, true));

        $id_propietario = $propietarioModel->registrar($dataPropietario);

        error_log("ID Propietario generado: " . $id_propietario);

        if (!$id_propietario) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo registrar el propietario'
            ]);
            exit();
        }

        // 2. Registrar mascotas
        $mascotaModel = new Mascota();
        $asignacionModel = new PacienteProfesionalAsignacion();

        $registradas = 0;
        foreach ($mascotas as $mascota) {
            $dataMascota = [
                'id_propietario' => $id_propietario,
                'nombre' => trim($mascota['nombre']),
                'especie' => trim($mascota['especie']),
                'raza' => trim($mascota['raza']),
                'edad_numero' => (int) $mascota['edad_numero'],
                'edad_unidad' => trim($mascota['edad_unidad']),
                'sexo' => trim($mascota['sexo']),
                'img_mascota' => null,
                'id_usuario_profesional' => $id_usuario_profesional,
                'id_usuario_asigno' => $id_usuario_profesional
            ];

            error_log("Datos mascota a insertar: " . print_r($dataMascota, true));
            $id_mascota_registrada = $mascotaModel->registrar($dataMascota);

            if (!$id_mascota_registrada) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo registrar una de las mascotas'
                ]);
                exit();
            }

            if ($id_usuario_profesional !== null && $asignacionModel->tablaExiste()) {
                $okAsignacion = $asignacionModel->asegurarAsignacionActiva(
                    (int) $id_mascota_registrada,
                    $id_usuario_profesional,
                    $id_usuario_profesional,
                    'Asignación inicial desde registro de pacientes'
                );

                if (!$okAsignacion) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'La mascota se registró pero falló su asignación al profesional'
                    ]);
                    exit();
                }
            }

            $registradas++;
        }

        echo json_encode([
            'success' => true,
            'message' => "Registro exitoso: propietario y {$registradas} mascota(s) guardadas correctamente"
        ]);
    } catch (Exception $e) {
        error_log("Error en registroPacienteController: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Ocurrió un error al procesar el registro: ' . $e->getMessage()
        ]);
    }

    exit();
}
