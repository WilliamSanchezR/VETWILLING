<?php

require_once BASE_PATH . '/app/models/PagoSuscripcion.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

$mercadoPagoAutoload = BASE_PATH . '/vendor/autoload.php';
$mercadoPagoSdkDisponible = file_exists($mercadoPagoAutoload);

if ($mercadoPagoSdkDisponible) {
    require_once $mercadoPagoAutoload;
}

$action = resolverAccionMercadoPago();

if (in_array($action, ['checkout', 'webhook'], true) && !$mercadoPagoSdkDisponible) {
    http_response_code(500);
    echo 'No se encontró la dependencia de Mercado Pago (vendor/autoload.php). Ejecuta composer install para habilitar el checkout.';
    exit();
}

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

    case 'verificar':
        verificarPagoYRedirigirConfirmacion();
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

    $producto = [
        'slug' => $plan,
        'titulo' => 'Plan de suscripción',
        'nombre' => 'Plan de suscripción',
        'descripcion' => 'Suscripción mensual VetWilling',
        'detalle' => 'Suscripción mensual VetWilling',
        'monto' => 0,
        'icono' => '🐾',
        'referencia' => 'SUS-' . strtoupper($plan) . '-' . date('YmdHis'),
    ];

    try {
        $modeloPago = new PagoSuscripcion();

        if ($origen === 'suscripcion' && $idSuscripcion <= 0) {
            $idVeterinariaSesion = isset($_SESSION['user']['id_veterinaria']) ? (int) $_SESSION['user']['id_veterinaria'] : 0;
            $idSuscripcion = $modeloPago->obtenerOCrearSuscripcionPendiente($plan, $idVeterinariaSesion);
        }

        $producto = $modeloPago->obtenerProducto($origen, $plan, $idSuscripcion);
    } catch (Throwable $e) {
        error_log('mostrarPasarelaPago fallback por error de BD: ' . $e->getMessage());

        $catalogo = [
            'basico' => ['nombre' => 'Plan Essential', 'detalle' => 'Suscripcion mensual para clinicas en crecimiento', 'monto' => 7900, 'icono' => '🐾'],
            'procare' => ['nombre' => 'Plan ProCare', 'detalle' => 'Suscripcion mensual para operacion avanzada', 'monto' => 14900, 'icono' => '⭐'],
            'mastervet' => ['nombre' => 'Plan MasterVet', 'detalle' => 'Suscripcion mensual completa para alta demanda', 'monto' => 40900, 'icono' => '👑'],
        ];

        if ($origen === 'suscripcion' && isset($catalogo[$plan])) {
            $producto['slug'] = $plan;
            $producto['titulo'] = $catalogo[$plan]['nombre'];
            $producto['nombre'] = $catalogo[$plan]['nombre'];
            $producto['descripcion'] = $catalogo[$plan]['detalle'];
            $producto['detalle'] = $catalogo[$plan]['detalle'];
            $producto['monto'] = $catalogo[$plan]['monto'];
            $producto['icono'] = $catalogo[$plan]['icono'];
        }
    }

    $queryCheckout = [
        'action' => 'checkout',
        'origen' => $origen,
        'plan' => $producto['slug'] ?? $plan,
    ];

    if ($idSuscripcion > 0) {
        $queryCheckout['id_suscripcion'] = $idSuscripcion;
    }

    $checkoutUrl = BASE_URL . '/pagos/mercadopago?' . http_build_query($queryCheckout);

    $queryVerificar = [
        'action' => 'verificar',
        'origen' => $origen,
        'plan' => $producto['slug'] ?? $plan,
    ];

    if ($idSuscripcion > 0) {
        $queryVerificar['id_suscripcion'] = $idSuscripcion;
    }

    $verificarUrl = BASE_URL . '/pagos/mercadopago?' . http_build_query($queryVerificar);

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

    if ($origen === 'suscripcion' && $idSuscripcion <= 0) {
        $idVeterinariaSesion = isset($_SESSION['user']['id_veterinaria']) ? (int) $_SESSION['user']['id_veterinaria'] : 0;
        $idSuscripcion = $modeloPago->obtenerOCrearSuscripcionPendiente($plan, $idVeterinariaSesion);
    }

    $producto = $modeloPago->obtenerProducto($origen, $plan, $idSuscripcion);

    $queryConfirmacion = [
        'action' => 'confirmacion',
        'origen' => $origen,
        'plan' => $producto['slug'] ?? $plan,
    ];

    $baseRetorno = obtenerBaseUrlRetornoMercadoPago();

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
                'success' => $baseRetorno . '/pagos/mercadopago?' . http_build_query($queryConfirmacion + ['estado' => 'success']),
                'failure' => $baseRetorno . '/pagos/mercadopago?' . http_build_query($queryConfirmacion + ['estado' => 'failure']),
                'pending' => $baseRetorno . '/pagos/mercadopago?' . http_build_query($queryConfirmacion + ['estado' => 'pending']),
            ],
        ];

        $esLocal = str_contains(BASE_URL, 'localhost') || str_contains(BASE_URL, '127.0.0.1');
        $retornoPublico = esUrlRetornoPublica($baseRetorno);

        if ($retornoPublico) {
            $request['auto_return'] = 'approved';
        }

        $forzarWalletLocal = function_exists('env_value')
            ? strtolower(trim((string) env_value('MP_FORCE_WALLET_LOCAL', '0')))
            : '0';

        if ($esLocal && in_array($forzarWalletLocal, ['1', 'true', 'yes'], true)) {
            $request['purpose'] = 'wallet_purchase';
            $request['payment_methods'] = [
                'excluded_payment_types' => [
                    ['id' => 'credit_card'],
                    ['id' => 'debit_card'],
                    ['id' => 'prepaid_card'],
                ],
                'installments' => 1,
            ];
        }

        $webhookUrl = function_exists('env_value')
            ? trim((string) env_value('MP_WEBHOOK_URL', ''))
            : '';

        if (!empty($webhookUrl)) {
            $request['notification_url'] = $webhookUrl;
        } elseif ($retornoPublico) {
            $request['notification_url'] = $baseRetorno . '/pagos/mercadopago?action=webhook';
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

function verificarPagoYRedirigirConfirmacion()
{
    $origen = $_GET['origen'] ?? 'suscripcion';
    $plan = $_GET['plan'] ?? 'procare';
    $idSuscripcion = isset($_GET['id_suscripcion']) ? (int) $_GET['id_suscripcion'] : 0;
    $formatoJson = strtolower((string) ($_GET['format'] ?? '')) === 'json';

    $queryConfirmacion = [
        'action' => 'confirmacion',
        'origen' => $origen,
        'plan' => $plan,
        'id_suscripcion' => $idSuscripcion,
        'estado' => 'pending',
    ];

    if ($idSuscripcion > 0) {
        $queryConfirmacion['id_suscripcion'] = $idSuscripcion;
    }

    $redirectUrl = BASE_URL . '/pagos/mercadopago?' . http_build_query($queryConfirmacion);

    if ($idSuscripcion <= 0) {
        responderVerificacionPago($formatoJson, $queryConfirmacion['estado'], $redirectUrl);
    }

    $accessToken = function_exists('env_value')
        ? env_value('MP_ACCESS_TOKEN', '')
        : (getenv('MP_ACCESS_TOKEN') ?: (defined('MP_ACCESS_TOKEN') ? MP_ACCESS_TOKEN : ''));

    if (empty($accessToken)) {
        responderVerificacionPago($formatoJson, $queryConfirmacion['estado'], $redirectUrl);
    }

    $modeloPago = new PagoSuscripcion();

    if ($idSuscripcion <= 0 && $origen === 'suscripcion') {
        $idVeterinariaSesion = isset($_SESSION['user']['id_veterinaria']) ? (int) $_SESSION['user']['id_veterinaria'] : 0;
        $idSuscripcion = $modeloPago->obtenerOCrearSuscripcionPendiente($plan, $idVeterinariaSesion);
        $queryConfirmacion['id_suscripcion'] = $idSuscripcion;
        $redirectUrl = BASE_URL . '/pagos/mercadopago?' . http_build_query($queryConfirmacion);
    }

    $suscripcion = $modeloPago->obtenerSuscripcionPorId($idSuscripcion);

    if (!$suscripcion) {
        responderVerificacionPago($formatoJson, $queryConfirmacion['estado'], $redirectUrl);
    }

    $externalReference = $modeloPago->asegurarReferenciaSuscripcionPorId($idSuscripcion) ?? '';
    if ($externalReference !== '') {
        $queryConfirmacion['external_reference'] = $externalReference;
    }

    $pago = consultarPagoMercadoPagoPorReferencia($accessToken, $externalReference);

    if ($pago !== null) {
        $queryConfirmacion['estado'] = normalizarEstadoPagoParaVista((string) ($pago['status'] ?? 'pending'));

        if (!empty($pago['id'])) {
            $queryConfirmacion['payment_id'] = (string) $pago['id'];
        }

        if (!empty($pago['order']['id'])) {
            $queryConfirmacion['merchant_order_id'] = (string) $pago['order']['id'];
        }

        if (!empty($pago['external_reference'])) {
            $queryConfirmacion['external_reference'] = (string) $pago['external_reference'];
        }
    }

    $redirectUrl = BASE_URL . '/pagos/mercadopago?' . http_build_query($queryConfirmacion);
    responderVerificacionPago($formatoJson, $queryConfirmacion['estado'], $redirectUrl);
}

function mostrarConfirmacionPago()
{
    $estado = resolverEstadoRetornoMercadoPago();
    $origen = $_GET['origen'] ?? 'suscripcion';
    $plan = $_GET['plan'] ?? 'procare';
    $idSuscripcion = isset($_GET['id_suscripcion']) ? (int) $_GET['id_suscripcion'] : 0;
    $paymentId = $_GET['payment_id'] ?? ($_GET['collection_id'] ?? '-');
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

function obtenerBaseUrlRetornoMercadoPago(): string
{
    $basePublica = function_exists('env_value')
        ? trim((string) env_value('MP_PUBLIC_BASE_URL', env_value('APP_PUBLIC_URL', '')))
        : '';

    if (!empty($basePublica)) {
        return rtrim($basePublica, '/');
    }

    return rtrim(BASE_URL, '/');
}

function esUrlRetornoPublica(string $baseUrl): bool
{
    $host = (string) parse_url($baseUrl, PHP_URL_HOST);

    if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
        return false;
    }

    return true;
}

function resolverAccionMercadoPago(): string
{
    if (!empty($_GET['action'])) {
        return (string) $_GET['action'];
    }

    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
    $baseFolderActual = $GLOBALS['baseFolder'] ?? '';

    if (!empty($baseFolderActual) && str_starts_with($requestPath, $baseFolderActual)) {
        $requestPath = substr($requestPath, strlen($baseFolderActual));
    }

    return match ($requestPath) {
        '/pasarela-pago' => 'pasarela',
        '/pagos/confirmacion' => 'confirmacion',
        default => 'checkout',
    };
}

function responderVerificacionPago(bool $formatoJson, string $estado, string $redirectUrl): void
{
    if ($formatoJson) {
        header('Content-Type: application/json');
        echo json_encode([
            'ok' => true,
            'estado' => $estado,
            'redirect_url' => $redirectUrl,
        ]);
        exit();
    }

    header('Location: ' . $redirectUrl);
    exit();
}

function resolverEstadoRetornoMercadoPago(): string
{
    if (!empty($_GET['estado'])) {
        return (string) $_GET['estado'];
    }

    $estadoMp = (string) ($_GET['status'] ?? ($_GET['collection_status'] ?? 'pending'));
    return normalizarEstadoPagoParaVista($estadoMp);
}

function normalizarEstadoPagoParaVista(string $estadoPago): string
{
    return match (strtolower(trim($estadoPago))) {
        'approved', 'success' => 'success',
        'rejected', 'cancelled', 'canceled', 'failure', 'failed' => 'failure',
        default => 'pending',
    };
}

function consultarPagoMercadoPagoPorReferencia(string $accessToken, string $externalReference): ?array
{
    if ($externalReference === '') {
        return null;
    }

    $url = 'https://api.mercadopago.com/v1/payments/search?sort=date_created&criteria=desc&external_reference='
        . rawurlencode($externalReference)
        . '&limit=1';

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ];

    $response = false;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => 20,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
    }

    if ($response === false || $response === '') {
        return null;
    }

    $data = json_decode($response, true);
    if (!is_array($data) || empty($data['results']) || !is_array($data['results'])) {
        return null;
    }

    return $data['results'][0] ?? null;
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
