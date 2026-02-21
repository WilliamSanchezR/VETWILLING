<?php
/*
Variables que deberían venir del controller:

$estado        // approved | declined | error
$mensaje       // texto explicativo
$referencia    // referencia interna
$monto         // valor pagado formateado
$dashboard_url // a dónde regresar según rol
*/
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">

    <title>Confirmación de Pago | VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .confirmation-card {
            background: #ffffff;
            width: 100%;
            max-width: 500px;
            padding: 40px;
            border-radius: 18px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .icon-success {
            font-size: 60px;
            color: #2e7d32;
        }

        .icon-error {
            font-size: 60px;
            color: #dc3545;
        }

        .btn-primary-custom {
            background: #2e7d32;
            border: none;
        }

        .btn-primary-custom:hover {
            background: #1b5e20;
        }
    </style>
</head>

<body>

    <div class="confirmation-card">

        <?php if ($estado === 'approved'): ?>

            <div class="icon-success">✔</div>
            <h3 class="mt-3">Pago exitoso</h3>
            <p class="text-muted"><?= $mensaje ?></p>

            <div class="mt-3">
                <p><strong>Referencia:</strong> <?= $referencia ?></p>
                <p><strong>Monto pagado:</strong> $<?= $monto ?></p>
            </div>

            <a href="<?= $dashboard_url ?>" class="btn btn-primary-custom mt-4">
                Volver al panel
            </a>

        <?php else: ?>

            <div class="icon-error">✖</div>
            <h3 class="mt-3">Pago no aprobado</h3>
            <p class="text-muted"><?= $mensaje ?></p>

            <a href="<?= BASE_URL ?>/pagos/reintentar?ref=<?= $referencia ?>" class="btn btn-outline-danger mt-4">
                Intentar nuevamente
            </a>

        <?php endif; ?>

    </div>

</body>

</html>