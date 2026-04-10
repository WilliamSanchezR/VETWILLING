<?php
$estado = $estado ?? ($_GET['estado'] ?? 'pending');
$paymentId = $paymentId ?? ($_GET['payment_id'] ?? '-');
$merchantOrderId = $merchantOrderId ?? ($_GET['merchant_order_id'] ?? '-');
$referencia = $referencia ?? ($_GET['external_reference'] ?? '-');
$titulo = $titulo ?? 'Pago en proceso';
$mensaje = $mensaje ?? 'Estamos validando tu transaccion. En breve veras el estado final.';
$detalleConfirmacion = $detalleConfirmacion ?? '';
$reintentarUrl = $reintentarUrl ?? (BASE_URL . '/pasarela-pago?origen=suscripcion&plan=procare');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmacion de pago | VetWilling</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f4f6f8;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
        }

        .card {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
            padding: 28px;
        }

        .estado {
            display: inline-block;
            background: #e8f5e9;
            color: #2e7d32;
            font-weight: 700;
            font-size: 12px;
            border-radius: 999px;
            padding: 6px 12px;
            margin-bottom: 14px;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 10px;
            color: #1e293b;
            font-size: 28px;
        }

        p {
            margin: 0 0 20px;
            color: #475569;
            line-height: 1.5;
        }

        .row {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #e2e8f0;
            padding: 12px 0;
            color: #334155;
            font-size: 14px;
        }

        .acciones {
            margin-top: 22px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            border: 0;
            text-decoration: none;
            border-radius: 10px;
            padding: 12px 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary {
            background: #2e7d32;
            color: #fff;
        }

        .btn-light {
            background: #eef2f7;
            color: #1e293b;
        }
    </style>
</head>

<body>
    <section class="card">
        <span class="estado"><?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?></span>
        <h1><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>

        <?php if (!empty($detalleConfirmacion)) : ?>
            <div class="row">
                <span>Activación</span>
                <strong><?= htmlspecialchars($detalleConfirmacion, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        <?php endif; ?>

        <div class="row">
            <span>ID de pago</span>
            <strong><?= htmlspecialchars($paymentId, ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="row">
            <span>ID orden</span>
            <strong><?= htmlspecialchars($merchantOrderId, ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="row">
            <span>Referencia</span>
            <strong><?= htmlspecialchars($referencia, ENT_QUOTES, 'UTF-8') ?></strong>
        </div>

        <div class="acciones">
            <a class="btn btn-primary" href="<?= BASE_URL ?>/representante/suscripcion">Volver a suscripcion</a>
            <a class="btn btn-light" href="<?= htmlspecialchars($reintentarUrl, ENT_QUOTES, 'UTF-8') ?>">Intentar de nuevo</a>
        </div>
    </section>
</body>

</html>