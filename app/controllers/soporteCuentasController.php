<?php

// API JSON de Soporte de cuentas (/admin/api/soporte-cuentas). Solo el superadministrador.
//   GET  accion=buscar&q=texto      cuentas que coinciden por correo o nombre
//   GET  accion=cuenta&id=N         datos, historial de correos del sistema y estado en Brevo
//   POST accion=enviar_prueba id=N  correo de prueba a la cuenta
//   POST accion=clave_temporal id=N clave temporal (se muestra una sola vez)

require_once BASE_PATH . '/app/helpers/acceso_helper.php';
require_once BASE_PATH . '/app/models/SoporteCuenta.php';
require_once BASE_PATH . '/app/services/SoporteCuentaService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$admin  = $metodo === 'POST' ? acceso_exigir_accion_post([1]) : acceso_exigir_rol([1]);
$accion = (string) ($metodo === 'POST' ? ($_POST['accion'] ?? '') : ($_GET['accion'] ?? ''));
$modelo = new SoporteCuenta();

function soporte_responder(array $datos, int $codigo = 200): never
{
    http_response_code($codigo);
    echo json_encode($datos);
    exit();
}

try {
    switch ("$metodo $accion") {
        case 'GET buscar':
            $texto = trim((string) ($_GET['q'] ?? ''));
            if (mb_strlen($texto) < 3) {
                soporte_responder(['ok' => false, 'mensaje' => 'Escribe al menos 3 letras del correo o del nombre.'], 422);
            }
            soporte_responder(['ok' => true, 'data' => $modelo->buscar($texto)]);

        case 'GET cuenta':
            $id = acceso_entero_positivo($_GET['id'] ?? null);
            $cuenta = $id === null ? null : $modelo->obtener($id);
            if ($cuenta === null) {
                soporte_responder(['ok' => false, 'mensaje' => 'La cuenta no existe.'], 404);
            }
            soporte_responder([
                'ok'        => true,
                'cuenta'    => $cuenta,
                'historial' => $modelo->historialCorreos($cuenta['email']),
                'brevo'     => (new SoporteCuentaService())->estadoBrevo($cuenta['email']),
                'es_yo'     => (int) $cuenta['id_usuario'] === (int) $admin['id_usuario'],
            ]);

        case 'POST enviar_prueba':
            // Un correo real por cuenta cada 30 s, para no saturar la bandeja del usuario
            $id = acceso_entero_positivo($_POST['id'] ?? null);
            $clave = 'soporte_ultima_prueba_' . (int) $id;
            $espera = 30 - (time() - (int) ($_SESSION[$clave] ?? 0));
            if ($espera > 0) {
                soporte_responder(['ok' => false, 'mensaje' => "Espera {$espera} s antes de enviar otra prueba a esta cuenta."], 429);
            }
            $_SESSION[$clave] = time();
            $r = $id === null ? ['ok' => false, 'mensaje' => 'La cuenta no existe.']
                : (new SoporteCuentaService())->enviarPrueba($id, (int) $admin['id_usuario']);
            soporte_responder($r, $r['ok'] ? 200 : 422);

        case 'POST clave_temporal':
            $id = acceso_entero_positivo($_POST['id'] ?? null);
            $r = $id === null ? ['ok' => false, 'mensaje' => 'La cuenta no existe.']
                : (new SoporteCuentaService())->claveTemporal($id, (int) $admin['id_usuario']);
            soporte_responder($r, $r['ok'] ? 200 : 422);

        default:
            soporte_responder(['ok' => false, 'mensaje' => 'Acción no reconocida.'], 400);
    }
} catch (Throwable $e) {
    error_log('[soporteCuentasController] ' . $e->getMessage());
    soporte_responder(['ok' => false, 'mensaje' => 'Error interno del servidor.'], 500);
}
