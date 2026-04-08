<?php

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/models/PagoSuscripcion.php';

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

$action = $_GET['action'] ?? 'checkout';

if ($action === 'webhook') {
    procesarWebhookMercadoPago();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo 'Metodo no permitido';
    exit();
}

switch ($action) {
    case 'pasarela':
        mostrarPasarelaPago();
        break;

    case 'checkout':
        crearPreferenciaYRedirigir();
        break;

    case 'confirmacion':
        mostrarConfirmacionPago();
        break;

    default:
        http_response_code(400);
        echo 'Accion no valida';
        break;
}

function mostrarPasarelaPago()
{
    $origen = $_GET['origen'] ?? 'tienda';
    $plan = $_GET['plan'] ?? 'producto';
    $idSuscripcion = isset($_GET['id_suscripcion']) ? (int) $_GET['id_suscripcion'] : 0;

    $modeloPago = new PagoSuscripcion();
    $producto = $modeloPago->obtenerProducto($origen, $plan, $idSuscripcion);

    $queryCheckout = [
        'action' => 'checkout',
        'origen' => $origen,
        'plan' => $producto['slug'] ?? $plan,
    ];

    if ($idSuscripcion > 0) {
        $queryCheckout['id_suscripcion'] = $idSuscripcion;
    }

    $checkoutUrl = BASE_URL . '/pagos/mercadopago?' . http_build_query($queryCheckout);

    require BASE_PATH . '/app/views/payments/pasarelaPago.php';
}

function crearPreferenciaYRedirigir()
{
    $accessToken = function_exists('env_value')
        ? env_value('MP_ACCESS_TOKEN', '')
        : (getenv('MP_ACCESS_TOKEN') ?: (defined('MP_ACCESS_TOKEN') ? MP_ACCESS_TOKEN : ''));

    if (empty($accessToken)) {
        http_response_code(500);
        echo 'No se encontro MP_ACCESS_TOKEN en variables de entorno del servidor.';
        exit();
    }

    $origen = $_GET['origen'] ?? 'tienda';
    $plan = $_GET['plan'] ?? 'producto';
    $idSuscripcion = isset($_GET['id_suscripcion']) ? (int) $_GET['id_suscripcion'] : 0;

    $modeloPago = new PagoSuscripcion();
    $producto = $modeloPago->obtenerProducto($origen, $plan, $idSuscripcion);

    $queryConfirmacion = [
        'origen' => $origen,
        'plan' => $producto['slug'] ?? $plan,
    ];

    if ($idSuscripcion > 0) {
        $queryConfirmacion['id_suscripcion'] = $idSuscripcion;
    }

    try {
        MercadoPagoConfig::setAccessToken($accessToken);
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

        $request = [
            'items' => [
                [
                    'title' => $producto['titulo'],
                    'description' => $producto['descripcion'],
                    'quantity' => 1,
                    'currency_id' => 'COP',
                    'unit_price' => (float) $producto['monto'],
                ],
            ],
            'external_reference' => $producto['referencia'],
            'binary_mode' => true,
            'metadata' => [
                'origen' => $origen,
                'plan' => $producto['slug'] ?? $plan,
                'id_suscripcion' => $idSuscripcion,
            ],
            'back_urls' => [
                'success' => BASE_URL . '/pagos/confirmacion?' . http_build_query($queryConfirmacion + ['estado' => 'success']),
                'failure' => BASE_URL . '/pagos/confirmacion?' . http_build_query($queryConfirmacion + ['estado' => 'failure']),
                'pending' => BASE_URL . '/pagos/confirmacion?' . http_build_query($queryConfirmacion + ['estado' => 'pending']),
            ],
        ];

        $esLocal = str_contains(BASE_URL, 'localhost') || str_contains(BASE_URL, '127.0.0.1');
        if (!$esLocal) {
            $request['auto_return'] = 'approved';
            $request['notification_url'] = BASE_URL . '/pagos/mercadopago?action=webhook';
        }

        $client = new PreferenceClient();
        $preference = $client->create($request);

        $checkoutUrl = !empty($preference->sandbox_init_point)
            ? $preference->sandbox_init_point
            : ($preference->init_point ?? '');

        if (empty($checkoutUrl)) {
            http_response_code(500);
            echo 'No se pudo obtener la URL de Checkout Pro.';
            exit();
        }

        header('Location: ' . $checkoutUrl);
        exit();
    } catch (MPApiException $e) {
        $respuesta = $e->getApiResponse();
        $statusCode = $respuesta ? $respuesta->getStatusCode() : 'N/A';
        $contenido  = $respuesta ? $respuesta->getContent() : [];

        http_response_code(500);
        echo '<pre>';
        echo '<strong>HTTP ' . htmlspecialchars((string) $statusCode, ENT_QUOTES, 'UTF-8') . ' - Error de Mercado Pago</strong>' . PHP_EOL;
        echo htmlspecialchars(json_encode($contenido, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        echo '</pre>';
        exit();
    } catch (Exception $e) {
        http_response_code(500);
        echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
        exit();
    }
}

function mostrarConfirmacionPago()
{
    $estado = $_GET['estado'] ?? 'pending';
    $origen = $_GET['origen'] ?? 'suscripcion';
    $plan = $_GET['plan'] ?? 'procare';
    $idSuscripcion = isset($_GET['id_suscripcion']) ? (int) $_GET['id_suscripcion'] : 0;
    $paymentId = $_GET['payment_id'] ?? '-';
    $merchantOrderId = $_GET['merchant_order_id'] ?? '-';
    $referencia = $_GET['external_reference'] ?? '-';

    $titulo = 'Pago en proceso';
    $mensaje = 'Estamos validando tu transaccion. En breve veras el estado final.';
    $detalleConfirmacion = '';

    if ($estado === 'success') {
        $titulo = 'Pago aprobado';
        $mensaje = 'Tu pago fue procesado correctamente.';
    } elseif ($estado === 'failure') {
        $titulo = 'Pago rechazado';
        $mensaje = 'No fue posible procesar el pago. Intenta nuevamente con otro metodo.';
    }

    if ($idSuscripcion > 0) {
        $modeloPago = new PagoSuscripcion();
        $resultadoConfirmacion = $modeloPago->registrarResultadoPagoSuscripcion($idSuscripcion, $estado, [
            'payment_id' => $paymentId !== '-' ? $paymentId : null,
            'merchant_order_id' => $merchantOrderId !== '-' ? $merchantOrderId : null,
            'external_reference' => $referencia !== '-' ? $referencia : null,
        ]);

        if (!empty($resultadoConfirmacion['ok'])) {
            $estadoSuscripcion = $resultadoConfirmacion['estado_suscripcion'] ?? 'pendiente';

            if ($estado === 'success') {
                $detalleConfirmacion = 'La suscripción quedó ' . $estadoSuscripcion . ', se registró en el histórico y la cuenta fue activada.';
            } elseif ($estado === 'pending') {
                $detalleConfirmacion = 'El intento de pago quedó registrado y la suscripción continúa en estado ' . $estadoSuscripcion . '.';
            } else {
                $detalleConfirmacion = 'El intento de pago quedó registrado y la suscripción permanece en estado ' . $estadoSuscripcion . '.';
            }
        } else {
            $detalleConfirmacion = 'No fue posible actualizar el estado interno de la suscripción: ' . ($resultadoConfirmacion['message'] ?? 'Error no identificado.');
        }
    }

    $queryReintentar = [
        'origen' => $origen,
        'plan' => $plan,
    ];

    if ($idSuscripcion > 0) {
        $queryReintentar['id_suscripcion'] = $idSuscripcion;
    }

    $reintentarUrl = BASE_URL . '/pasarela-pago?' . http_build_query($queryReintentar);

    require BASE_PATH . '/app/views/payments/confirmacion.php';
}

function procesarWebhookMercadoPago()
{
    $rawBody = file_get_contents('php://input') ?: '';
    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        $payload = [];
    }

    $tipo = $payload['type'] ?? ($_GET['type'] ?? ($_GET['topic'] ?? 'desconocido'));
    $idDato = $payload['data']['id'] ?? ($_GET['data_id'] ?? ($_GET['id'] ?? null));

    if (empty($idDato) && !empty($payload['resource'])) {
        $path = parse_url((string) $payload['resource'], PHP_URL_PATH);
        if (!empty($path)) {
            $idDato = basename($path);
        }
    }

    $log = [
        'fecha' => date('Y-m-d H:i:s'),
        'tipo' => $tipo,
        'id_dato' => $idDato,
        'query' => $_GET,
        'payload' => $payload,
    ];

    $accessToken = function_exists('env_value')
        ? env_value('MP_ACCESS_TOKEN', '')
        : (getenv('MP_ACCESS_TOKEN') ?: (defined('MP_ACCESS_TOKEN') ? MP_ACCESS_TOKEN : ''));

    if (!empty($accessToken) && !empty($idDato) && (strpos((string) $tipo, 'payment') !== false || is_numeric($idDato))) {
        try {
            MercadoPagoConfig::setAccessToken($accessToken);
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

            $paymentClient = new PaymentClient();
            $payment = $paymentClient->get((int) $idDato);

            $log['payment'] = [
                'id' => $payment->id ?? null,
                'status' => $payment->status ?? null,
                'status_detail' => $payment->status_detail ?? null,
                'external_reference' => $payment->external_reference ?? null,
                'transaction_amount' => $payment->transaction_amount ?? null,
                'date_approved' => $payment->date_approved ?? null,
                'payment_method_id' => $payment->payment_method_id ?? null,
            ];

            $metadata = $payment->metadata ?? [];
            if (is_object($metadata)) {
                $metadata = (array) $metadata;
            }

            $idSuscripcion = (int) ($metadata['id_suscripcion'] ?? ($_GET['id_suscripcion'] ?? 0));
            $estadoPago = $payment->status ?? null;

            if ($idSuscripcion > 0) {
                $modeloPago = new PagoSuscripcion();
                $log['suscripcion_confirmada'] = $modeloPago->registrarResultadoPagoSuscripcion($idSuscripcion, (string) $estadoPago, [
                    'id' => $payment->id ?? null,
                    'payment_id' => $payment->id ?? null,
                    'external_reference' => $payment->external_reference ?? null,
                    'date_approved' => $payment->date_approved ?? null,
                ]);
            }
        } catch (Exception $e) {
            $log['payment_error'] = $e->getMessage();
        }
    }

    escribirLogWebhook($log);

    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode(['ok' => true]);
}

function escribirLogWebhook(array $data)
{
    $ruta = BASE_PATH . '/app/logs/mercadopago_webhook.log';
    $linea = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    file_put_contents($ruta, $linea, FILE_APPEND);
}
