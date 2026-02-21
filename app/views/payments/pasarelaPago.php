<?php
/*
Variables que deben venir del controller:

$titulo
$descripcion
$monto_en_centavos
$monto_formateado
$referencia
$public_key
*/
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">

    <title>Finalizar Pago | VetWilling</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkout-card {
            background: #ffffff;
            width: 100%;
            max-width: 480px;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
        }

        .checkout-title {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
            color: #2e7d32;
        }

        .resumen-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .resumen-total {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 18px;
            font-weight: 600;
            color: #2e7d32;
        }

        .secure-badge {
            text-align: center;
            font-size: 13px;
            margin-top: 20px;
            color: #6c757d;
        }
    </style>
</head>

<body>

    <div class="checkout-card">

        <h2 class="checkout-title">Resumen del Pago</h2>

        <div class="resumen-item">
            <span>Concepto:</span>
            <strong>
                <!-- <?= $titulo ?> -->
                purina
                <!-- se pone el titulo de que es  -->
            </strong>
        </div>

        <div class="resumen-item">
            <span>Detalle:</span>
            <strong>
                <!-- <?= $descripcion ?> -->
                1 bolsa de 5kg
            </strong>
        </div>

        <hr>

        <div class="resumen-total">
            <span>Total:</span>
            <span>$
                <!-- <?= $monto_formateado ?> -->
                150000
            </span>
        </div>

        <div class="mt-4 text-center">

            <!-- BOTÓN OFICIAL WOMPI -->
            <script
                src="https://checkout.wompi.co/widget.js"
                data-render="button"
                data-public-key="pub_test_xxxxxxxxxxxxx"
                data-currency="COP"
                data-amount-in-cents="15000000"
                data-reference="REF123456"
                data-redirect-url="http://localhost/tu_proyecto/pagos/confirmacion">
            </script>
            <!-- BOTÓN PERSONALIZADO -->
            <!-- <button id="wompi-button" class="btn btn-success">
                Pagar con Wompi
            </button> -->
        </div>

        <div class="secure-badge">
            Pago seguro procesado por Wompi 🔒
        </div>

    </div>

</body>

</html>