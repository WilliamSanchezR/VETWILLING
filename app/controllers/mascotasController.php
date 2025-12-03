<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/mascotas.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            actualizarMascota();
        } else {
            registrarMascota();
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {
            eliminarMascota($_GET['id']);
        } else if (isset($_GET['id'])) {
            consultarMascotaId($_GET['id']);
        } else {
            listarMascotas();
        }
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}



// =================================================================
// ✔ FUNCIONES CRUD MASCOTAS
// =================================================================

function registrarMascota()
{
    $id_propietario = $_POST['id_propietario'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $especie = $_POST['especie'] ?? '';
    $raza = $_POST['raza'] ?? '';
    $edad = $_POST['edad'] ?? '';
    $sexo = $_POST['sexo'] ?? '';

    if (empty($id_propietario) || empty($nombre) || empty($especie)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Complete como mínimo: propietario, nombre y especie');
        exit();
    }

    // SUBIR FOTO
    $img_mascota = 'default_pet.jpg';

    if (!empty($_FILES['img_mascota']['name'])) {
        $file = $_FILES['img_mascota'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no válida', 'Solo JPG, JPEG y PNG');
            exit();
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Foto muy grande', 'Máximo 2MB');
            exit();
        }

        $img_mascota = uniqid('pet_') . ".$ext";
        $destino = BASE_PATH . '/public/uploads/mascotas/'. $img_mascota;
        move_uploaded_file($file['tmp_name'], $destino);
    }

    $obj = new Mascota();

    $data = [
        'id_propietario' => $id_propietario,
        'nombre' => $nombre,
        'especie' => $especie,
        'raza' => $raza,
        'edad' => $edad,
        'sexo' => $sexo,
        'img_mascota' => $img_mascota
    ];

    $res = $obj->registrar($data);

    if ($res) {
        mostrarSweetAlert('success', 'Mascota registrada', 'Mascota creada correctamente', '/vetwilling/cliente/perfil');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo registrar la mascota');
    }

    exit();
}


function listarMascotas()
{
    $obj = new Mascota();
    return $obj->listar();
}

function consultarMascotaId($id)
{
    $obj = new Mascota();
    return $obj->consultar($id);
}

function actualizarMascota()
{
    $id = $_POST['id_mascota'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $especie = $_POST['especie'] ?? '';
    $raza = $_POST['raza'] ?? '';
    $edad = $_POST['edad'] ?? '';
    $sexo = $_POST['sexo'] ?? '';

    if (empty($id) || empty($nombre) || empty($especie)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Complete los campos obligatorios');
        exit();
    }

    $obj = new Mascota();

    // FOTO
    $nuevaFoto = null;
    if (!empty($_FILES['img_mascota']['name'])) {
        $file = $_FILES['img_mascota'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Solo JPG, JPEG, PNG');
            exit();
        }

        $nuevaFoto = uniqid('pet_') . '.' . $ext;
        $destino = BASE_PATH . "/public/uploads/mascotas/$nuevaFoto";
        move_uploaded_file($file['tmp_name'], $destino);
    }

    $data = [
        'id' => $id,
        'nombre' => $nombre,
        'especie' => $especie,
        'raza' => $raza,
        'edad' => $edad,
        'sexo' => $sexo,
        'img_mascota' => $nuevaFoto
    ];

    $res = $obj->actualizar($data);

    if ($res) {
        mostrarSweetAlert('success', 'Actualizado', 'La mascota fue actualizada', '/vetwilling/cliente/perfil');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar la mascota');
    }

    exit();
}

function eliminarMascota($id)
{
    $obj = new Mascota();
    $res = $obj->eliminar($id);

    if ($res) {
        mostrarSweetAlert('success', 'Eliminada', 'La mascota fue eliminada', '/vetwilling/cliente/perfil');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar la mascota');
    }

    exit();
}
