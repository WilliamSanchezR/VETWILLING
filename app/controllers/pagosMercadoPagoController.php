<?php

require_once BASE_PATH . '/vendor/autoload.php';

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
    case 'checkout':
        crearPreferenciaYRedirigir();
        break;

    default:
        http_response_code(400);
        echo 'Accion no valida';
        break;
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

    $producto = resolverProducto($origen, $plan);

    try {
        MercadoPagoConfig::setAccessToken($accessToken);
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

        $request = [
            'items' => [
                [
                    'title' => 'Mensualidad VetWilling ',
                    'description' => 'Plan de suscripcion mensual - VetWilling',
                    'quantity' => 1,
                    'currency_id' => 'COP',
                    'unit_price' => (float) 11000,
                ],
            ],
            'external_reference' => 'ref-1204000' . date('YmdHis'),
            'binary_mode' => true,
            'back_urls' => [
                'success' => BASE_URL . '/pagos/confirmacion?estado=success',
                'failure' => BASE_URL . '/pagos/confirmacion?estado=failure',
                'pending' => BASE_URL . '/pagos/confirmacion?estado=pending',
            ],
        ];

        // auto_return y notification_url solo funcionan con URLs públicas (producción).
        // En localhost Mercado Pago las rechaza con error 400.
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

function resolverProducto($origen, $plan)
{
    if ($origen === 'suscripcion') {
        $planes = [
            'basico' => [
                'titulo' => 'Plan Essential',
                'descripcion' => 'Suscripcion mensual VetWilling - Plan Essential',
                'monto' => 7900,
                'referencia' => 'SUS-BASICO-' . date('YmdHis'),
            ],
            'procare' => [
                'titulo' => 'Plan ProCare',
                'descripcion' => 'Suscripcion mensual VetWilling - Plan ProCare',
                'monto' => 14900,
                'referencia' => 'SUS-PROCARE-' . date('YmdHis'),
            ],
            'mastervet' => [
                'titulo' => 'Plan MasterVet',
                'descripcion' => 'Suscripcion mensual VetWilling - Plan MasterVet',
                'monto' => 40900,
                'referencia' => 'SUS-MASTERVET-' . date('YmdHis'),
            ],
        ];

        if (isset($planes[$plan])) {
            return $planes[$plan];
        }
    }

    return [
        'titulo' => 'Producto VetWilling',
        'descripcion' => 'Compra en tienda VetWilling',
        'monto' => 150000,
        'referencia' => 'ORD-' . date('YmdHis'),
    ];
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
