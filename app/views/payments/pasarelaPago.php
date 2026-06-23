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

            <button type="button" class="btn-modal-open" onclick="abrirModal()">
                Ver modal de pago
            </button>

            <button type="button" class="btn-pagar" id="btnAbrirPasarela"
                    data-checkout-url="<?= htmlspecialchars($checkoutUrl, ENT_QUOTES, 'UTF-8') ?>"
                    data-verificar-url="<?= htmlspecialchars($verificarUrl, ENT_QUOTES, 'UTF-8') ?>">
                <span>🔒</span>
                Abrir Mercado Pago
            </button>

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

<div id="overlay" class="overlay" aria-hidden="true">
    <div class="wompi-modal" role="dialog" aria-modal="true" aria-labelledby="pasarelaModalTitle">
        <button type="button" class="btn-cerrar" onclick="cerrarModal()" aria-label="Cerrar modal">×</button>

        <div class="wompi-header">
            <div>
                <div class="wompi-brand" id="pasarelaModalTitle">Vet<span>Willing</span></div>
                <div class="subtitle">Modal de checkout</div>
            </div>
            <div class="monto-header">
                <div class="label">Total a pagar</div>
                <div class="valor">$ <?= number_format((float) $producto['monto'], 0, ',', '.') ?> COP</div>
            </div>
        </div>

        <div class="steps-indicator" aria-hidden="true">
            <span id="dot-1" class="step-dot active"></span>
            <span id="dot-2" class="step-dot"></span>
            <span id="dot-3" class="step-dot"></span>
        </div>

        <div class="wompi-body">
            <div id="step-1" class="step active">
                <div class="metodos-title">Selecciona un método de pago</div>

                <div class="metodo-item selected" onclick="seleccionarMetodo(this, 'Tarjeta de crédito/débito')">
                    <div class="metodo-icon">💳</div>
                    <div>
                        <div class="metodo-nombre">Tarjeta de crédito/débito</div>
                        <div class="metodo-desc">Pago seguro a través de Mercado Pago.</div>
                    </div>
                </div>

                <div class="metodo-item" onclick="seleccionarMetodo(this, 'PSE')">
                    <div class="metodo-icon">🏦</div>
                    <div>
                        <div class="metodo-nombre">PSE</div>
                        <div class="metodo-desc">Redirección al checkout oficial.</div>
                    </div>
                </div>
            </div>

            <div id="step-2" class="step">
                <div class="tarjeta-preview">
                    <div>Resumen de pago</div>
                    <div class="numero"><?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="fila">
                        <span><?= htmlspecialchars($producto['referencia'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span id="preview-exp">COP</span>
                    </div>
                </div>

                <div class="payment-test-alert">
                    <div class="payment-test-alert__title">Verificación previa</div>
                    <ul class="payment-test-alert__list">
                        <li>Revisa el resumen antes de continuar.</li>
                        <li>El pago final se procesa en Mercado Pago.</li>
                    </ul>
                </div>
            </div>

            <div id="step-3" class="step">
                <div class="procesando">
                    <div class="spinner" aria-hidden="true"></div>
                    <h3>Abriendo Mercado Pago</h3>
                    <p>Redirigiendo al checkout oficial para completar el pago.</p>
                </div>
            </div>
        </div>

        <div class="modal-footer" id="modal-footer">
            <button type="button" class="btn-modal-back" id="btn-back" onclick="pasoAnterior()" style="display:none;">
                Atrás
            </button>
            <button type="button" class="btn-modal-next" id="btn-next" onclick="pasoContinuar()" disabled>
                Continuar →
            </button>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/payments/js/pasarelaPago.js" defer></script>
</body>
</html>