<?php

require_once __DIR__ . '/../helpers/alert_helpers.php';
require_once __DIR__ . '/../models/Propietario.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':

        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            actualizarPropietario();
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {
            eliminarPropietario($_GET['id']);
        } else if (isset($_GET['id'])) {
            consultarPropietarioId($_GET['id']);
        } else {
            listarPropietarios();
        }
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}


// =========================================
//  FUNCIONES CRUD
// =========================================

function registrarPropietario()
{
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $email = $_POST['email'] ?? '';
    $estado = 'activo';
    $id_veterinaria = $_POST['id_veterinaria'] ?? null;

    if (
        empty($tipo_documento) || empty($numero_documento) ||
        empty($nombres) || empty($apellidos) || empty($telefono)
    ) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor complete todos los campos obligatorios');
        exit();
    }

    $obj = new Propietario();

    $data = [
        'tipo_documento' => $tipo_documento,
        'numero_documento' => $numero_documento,
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'telefono' => $telefono,
        'direccion' => $direccion,
        'id_veterinaria' => $id_veterinaria
    ];

    $resultado = $obj->registrar($data);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Propietario registrado',
            'El propietario ha sido agregado correctamente',
            '/vetwilling/admin/listar-propietarios'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo registrar el propietario');
    }

    exit();
}

function listarPropietarios()
{
    $resultado = new Propietario();
    return $resultado->listar();
}

function consultarPropietarioId($id)
{
    $obj = new Propietario();
    return $obj->consultarPropietario($id);
}

function actualizarPropietario()
{
    if (!isset($_POST['id_propietario'])) {
        mostrarSweetAlert("error", "Error", "ID no recibido");
        return;
    }

    $id_propietario   = $_POST['id_propietario'];
    $tipo_documento   = $_POST['tipo_documento'] ?? null;
    $numero_documento = $_POST['numero_documento'] ?? null;
    $nombres          = $_POST['nombres'] ?? null;
    $apellidos        = $_POST['apellidos'] ?? null;
    $telefono         = $_POST['telefono'] ?? null;
    $direccion        = $_POST['direccion'] ?? null;
    $id_veterinaria   = $_POST['id_veterinaria'] ?? null;
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;

    /* ===============================
       PROCESAR IMAGEN (solo si llega)
       =============================== */
    $img_perfil = null;

    if (isset($_FILES['img_perfil']) && $_FILES['img_perfil']['error'] === 0) {

        $ruta = __DIR__ . "/../../public/uploads/usuarios/";
        if (!is_dir($ruta)) {
            mkdir($ruta, 0777, true);
        }

        $nombreArchivo = time() . "_" . basename($_FILES['img_perfil']['name']);
        $destino = $ruta . $nombreArchivo;

        if (move_uploaded_file($_FILES['img_perfil']['tmp_name'], $destino)) {
            // Guardamos solo el nombre del archivo
            $img_perfil = $nombreArchivo;
        }
    }

    /* ===============================
       PREPARAR DATA
       =============================== */
    $data = [
        'id_propietario'  => $id_propietario,
        'tipo_documento'   => $tipo_documento,
        'numero_documento' => $numero_documento,
        'nombres'          => $nombres,
        'apellidos'        => $apellidos,
        'telefono'         => $telefono,
        'direccion'        => $direccion,
        'id_veterinaria'   => $id_veterinaria,
        'fecha_nacimiento' => $fecha_nacimiento,
        'img_perfil'       => $img_perfil  // puede ser null
    ];

    /* ===============================
       LLAMAR AL MODELO
       =============================== */
    $prop = new Propietario();

    // Si NO se envió imagen → que el modelo no la modifique
    $resultado = $prop->actualizar($data, $img_perfil ? true : false);

    if ($resultado) {
        mostrarSweetAlert("success", "Actualizado", "Datos actualizados correctamente", "/vetwilling/Cliente/perfil");
    } else {
        mostrarSweetAlert("error", "Error", "No se pudo actualizar");
    }
}

function eliminarPropietario($id)
{
    $obj = new Propietario();
    $resultado = $obj->eliminar($id);

    if ($resultado) {
        mostrarSweetAlert(
            'success',
            'Propietario inhabilitado',
            'El propietario ha sido inhabilitado correctamente',
            '/vetwilling/admin/listar-propietarios'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar');
    }

    exit();
}
