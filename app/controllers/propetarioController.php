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


// ==========
//  FUNCIONES CRUD
// ==========

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
    // Capturamos los datos del formulario (Asegúrate de que el ID del propietario esté en el formulario)
    $id_propietario   = $_POST['id_propietario'] ?? ''; // Asumimos que viene desde un campo oculto del form
    $tipo_documento   = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $nombres          = $_POST['nombres'] ?? '';
    $apellidos        = $_POST['apellidos'] ?? '';
    $telefono         = $_POST['telefono'] ?? '';
    $direccion        = $_POST['direccion'] ?? ''; // Asegúrate de que este campo exista en el HTML
    $email            = $_POST['email'] ?? '';
    $id_veterinaria   = $_POST['id_veterinaria'] ?? ''; // Asegúrate de que este campo exista en el HTML

    // Validamos los campos obligatorios
    if (empty($numero_documento) || empty($nombres) || empty($apellidos) || empty($tipo_documento) || empty($telefono) || empty($email)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor, completar todos los campos');
        exit();
    }

    /* PROCESAR IMAGEN (solo si llega un nuevo archivo) */
    $img_perfil = null;
    $actualizarImagen = false;

    if (isset($_FILES['img_perfil']) && $_FILES['img_perfil']['error'] === 0) {
        $ruta = __DIR__ . "/../../public/uploads/usuarios/";
        if (!is_dir($ruta)) {
            mkdir($ruta, 0777, true);
        }

        $nombreArchivo = time() . "_" . basename($_FILES['img_perfil']['name']);
        $destino = $ruta . $nombreArchivo;

        if (move_uploaded_file($_FILES['img_perfil']['tmp_name'], $destino)) {
            $img_perfil = $nombreArchivo;
            $actualizarImagen = true; // Indicamos que SÍ se debe actualizar la columna de imagen
        }
    }

    // POO - Instanciamos la clase
    $prop = new Propietario();
    $data = [
        'id_propietario'   => $id_propietario,
        'tipo_documento'   => $tipo_documento,
        'numero_documento' => $numero_documento,
        'nombres'          => $nombres,
        'apellidos'        => $apellidos,
        'telefono'         => $telefono,
        'direccion'        => $direccion,
        'email'            => $email,
        'id_veterinaria'   => $id_veterinaria,
        'img_perfil'       => $img_perfil    // Valor: null si no se subió, o el nombre del archivo si se subió
    ];

    // Enviamos la data y la bandera $actualizarImagen al método actualizar del Modelo
    $resultado = $prop->actualizar($data, $actualizarImagen);

    // Si la respuesta del modelo es verdadera confirmamos la actualización
    if ($resultado === true) {
        mostrarSweetAlert('success', 'Actualización exitosa', 'Datos actualizados correctamente', '/vetwilling/Cliente/perfil');
    } else {
        mostrarSweetAlert('error', 'Error al actualizar', 'No se pudo actualizar el propietario. Intenta nuevamente');
    }
    exit();
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
