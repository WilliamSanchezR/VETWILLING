<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Propietario.php';
require_once __DIR__ . '/../models/mascotas.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    registrarPacienteConPropietario();
}

function registrarPacienteConPropietario()
{
    // Establecer header JSON
    header('Content-Type: application/json');

    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['user']['id_veterinaria'])) {
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo identificar la veterinaria'
        ]);
        exit();
    }

    // Manejar múltiples veterinarias (separadas por coma)
    $id_veterinaria = $_SESSION['user']['id_veterinaria'];
    if (strpos($id_veterinaria, ',') !== false) {
        // Si hay múltiples, tomar la primera
        $id_veterinaria = explode(',', $id_veterinaria)[0];
        $id_veterinaria = trim($id_veterinaria);
    }

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
                'img_mascota' => null
            ];

            error_log("Datos mascota a insertar: " . print_r($dataMascota, true));
            $resultado = $mascotaModel->registrar($dataMascota);

            if (!$resultado) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo registrar una de las mascotas'
                ]);
                exit();
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
