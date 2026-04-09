<?php
$producto = $producto ?? [
    'nombre' => 'Producto VetWilling',
    'detalle' => 'Resumen de pago no disponible',
    'monto' => 0,
    'icono' => '🐾',
    'referencia' => 'N/A',
];

$checkoutUrl = $checkoutUrl ?? (BASE_URL . '/pagos/mercadopago?action=checkout');
$verificarUrl = $verificarUrl ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Pago | VetWilling</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/payments/css/pasarelaPago.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
</head>
<body>

<!-- ===== PÁGINA CHECKOUT ===== -->
<div id="pagina-checkout">
    <div class="checkout-card">

        <div class="checkout-header">
            <div class="logo">🐾 VetWilling</div>
            <div class="subtitle">Checkout de Mercado Pago</div>
        </div>

        <div class="checkout-body">

            <div class="producto-box">
                <div class="producto-icon"><?= htmlspecialchars($producto['icono'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="producto-info">
                    <div class="nombre"><?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="detalle"><?= htmlspecialchars($producto['detalle'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>

            <div class="resumen-fila">
                <span>Subtotal</span>
                <span>$ <?= number_format((float) $producto['monto'], 0, ',', '.') ?></span>
            </div>
            <div class="resumen-fila">
                <span>Referencia</span>
                <span><?= htmlspecialchars($producto['referencia'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <div class="resumen-total">
                <span>Total</span>
                <span>$ <?= number_format((float) $producto['monto'], 0, ',', '.') ?> COP</span>
            </div>

            <a class="btn-pagar" href="<?= htmlspecialchars($checkoutUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                <span>🔒</span>
                Abrir Mercado Pago
            </a>

            <?php if (!empty($verificarUrl)) : ?>
                <a class="btn-verificar" href="<?= htmlspecialchars($verificarUrl, ENT_QUOTES, 'UTF-8') ?>">
                    Ya pagué, verificar estado
                </a>
                <p class="payment-help-text">Si Mercado Pago no regresa automáticamente a la aplicación, vuelve a esta pestaña y usa este botón para procesar la confirmación.</p>
            <?php endif; ?>

            <div class="secure-badge">
                🛡️ Pago procesado por Mercado Pago · SSL 256-bit
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    const verificarUrl = <?= json_encode($verificarUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const botonPagar = document.querySelector('.btn-pagar');

    if (!verificarUrl || !botonPagar) {
        return;
    }

    let intervalo = null;

    const revisarEstado = async () => {
        try {
            const separador = verificarUrl.includes('?') ? '&' : '?';
            const respuesta = await fetch(verificarUrl + separador + 'format=json', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store'
            });

            if (!respuesta.ok) {
                return;
            }

            const data = await respuesta.json();
            if (data && (data.estado === 'success' || data.estado === 'failure') && data.redirect_url) {
                if (intervalo) {
                    clearInterval(intervalo);
                }

                window.location.href = data.redirect_url;
            }
        } catch (error) {
            console.debug('No fue posible verificar el pago automáticamente.', error);
        }
    };

    const iniciarSeguimiento = () => {
        if (intervalo) {
            return;
        }

        revisarEstado();
        intervalo = setInterval(revisarEstado, 8000);
    };

    botonPagar.addEventListener('click', iniciarSeguimiento);
})();
</script>
</body>
</html>