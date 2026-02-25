<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Pago | VetWilling</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/payments/css/pasarelaPago.css">
</head>
<body>

<!-- ===== PÁGINA CHECKOUT ===== -->
<div id="pagina-checkout">
    <div class="checkout-card">

        <div class="checkout-header">
            <div class="logo">🐾 VetWilling</div>
            <div class="subtitle">Checkout seguro</div>
        </div>

        <div class="checkout-body">

            <div class="producto-box">
                <div class="producto-icon">🐶</div>
                <div class="producto-info">
                    <div class="nombre">Purina Pro Plan</div>
                    <div class="detalle">1 bolsa de 5kg · Adulto razas medianas</div>
                </div>
            </div>

            <div class="resumen-fila">
                <span>Subtotal</span>
                <span>$ 150.000</span>
            </div>
            <div class="resumen-fila">
                <span>Envío</span>
                <span style="color: #2e7d32; font-weight:600;">Gratis</span>
            </div>
            <div class="resumen-fila">
                <span>Referencia</span>
                <span>ORD-2024-001</span>
            </div>

            <div class="resumen-total">
                <span>Total</span>
                <span>$ 150.000 COP</span>
            </div>

            <button class="btn-pagar" onclick="abrirModal()">
                <span>🔒</span>
                Pagar ahora
            </button>

            <div class="secure-badge">
                🛡️ Pago procesado por Wompi · SSL 256-bit
            </div>

        </div>
    </div>
</div>

<!-- ===== PÁGINA CONFIRMACIÓN ===== -->
<div id="pagina-confirmacion">
    <div class="confirmacion-card">

        <div class="confirmacion-header">
            <div class="checkmark">✓</div>
            <h2>¡Pago Aprobado!</h2>
            <p>Tu transacción fue procesada exitosamente</p>
        </div>

        <div class="confirmacion-body">
            <div class="detalle-fila">
                <span class="key">Estado</span>
                <span class="val"><span class="estado-badge">APROBADO</span></span>
            </div>
            <div class="detalle-fila">
                <span class="key">ID Transacción</span>
                <span class="val" id="conf-id">-</span>
            </div>
            <div class="detalle-fila">
                <span class="key">Referencia</span>
                <span class="val">ORD-2024-001</span>
            </div>
            <div class="detalle-fila">
                <span class="key">Concepto</span>
                <span class="val">Purina Pro Plan</span>
            </div>
            <div class="detalle-fila">
                <span class="key">Monto pagado</span>
                <span class="val" style="color: var(--verde);">$ 150.000 COP</span>
            </div>
            <div class="detalle-fila">
                <span class="key">Método</span>
                <span class="val" id="conf-metodo">-</span>
            </div>
            <div class="detalle-fila">
                <span class="key">Fecha</span>
                <span class="val" id="conf-fecha">-</span>
            </div>
        </div>

        <button class="btn-volver" onclick="volverInicio()">← Volver al inicio</button>

        <div class="wompi-footer">
            Procesado por <strong>WOMPI</strong> · Transacción simulada para proyecto educativo
        </div>

    </div>
</div>

<!-- ===== MODAL WOMPI SIMULADO ===== -->
<div class="overlay" id="overlay">
    <div class="wompi-modal">

        <div class="wompi-header">
            <div class="wompi-brand">WOM<span>PI</span></div>
            <div class="monto-header">
                <div class="label">Total a pagar</div>
                <div class="valor">$ 150.000 COP</div>
            </div>
            <button class="btn-cerrar" onclick="cerrarModal()">✕</button>
        </div>

        <div class="steps-indicator">
            <div class="step-dot active" id="dot-1"></div>
            <div class="step-dot" id="dot-2"></div>
            <div class="step-dot" id="dot-3"></div>
        </div>

        <!-- STEP 1: Elegir método de pago -->
        <div class="step active" id="step-1">
            <div style="padding: 20px 24px 12px">
                <p class="metodos-title">Elige tu método de pago</p>

                <div class="metodo-item" onclick="seleccionarMetodo(this, 'Tarjeta de crédito/débito')">
                    <div class="metodo-icon">💳</div>
                    <div>
                        <div class="metodo-nombre">Tarjeta crédito / débito</div>
                        <div class="metodo-desc">Visa, Mastercard, Amex</div>
                    </div>
                </div>

                <div class="metodo-item" onclick="seleccionarMetodo(this, 'PSE - Débito bancario')">
                    <div class="metodo-icon">🏦</div>
                    <div>
                        <div class="metodo-nombre">PSE</div>
                        <div class="metodo-desc">Débito desde tu cuenta bancaria</div>
                    </div>
                </div>

                <div class="metodo-item" onclick="seleccionarMetodo(this, 'Nequi')">
                    <div class="metodo-icon">📱</div>
                    <div>
                        <div class="metodo-nombre">Nequi</div>
                        <div class="metodo-desc">Paga desde tu app Nequi</div>
                    </div>
                </div>

                <div class="metodo-item" onclick="seleccionarMetodo(this, 'Bancolombia QR')">
                    <div class="metodo-icon">⬛</div>
                    <div>
                        <div class="metodo-nombre">Bancolombia QR</div>
                        <div class="metodo-desc">Escanea y paga</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: Formulario -->
        <div class="step" id="step-2">
            <div style="padding: 20px 24px 12px">

                <div class="tarjeta-preview">
                    <div style="font-size:11px; opacity:0.7">TARJETA DE PAGO</div>
                    <div class="numero" id="preview-numero">•••• •••• •••• ••••</div>
                    <div class="fila">
                        <span id="preview-nombre">NOMBRE TITULAR</span>
                        <span id="preview-exp">MM/AA</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Número de tarjeta</label>
                    <input class="form-input" type="text" maxlength="19" placeholder="0000 0000 0000 0000"
                        oninput="formatCard(this)" />
                </div>

                <div class="form-group">
                    <label class="form-label">Nombre del titular</label>
                    <input class="form-input" type="text" placeholder="Como aparece en la tarjeta"
                        oninput="document.getElementById('preview-nombre').textContent = this.value.toUpperCase() || 'NOMBRE TITULAR'" />
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Vencimiento</label>
                        <input class="form-input" type="text" maxlength="5" placeholder="MM/AA"
                            oninput="formatExp(this)" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">CVV</label>
                        <input class="form-input" type="password" maxlength="3" placeholder="•••" />
                    </div>
                </div>

            </div>
        </div>

        <!-- STEP 3: Procesando -->
        <div class="step" id="step-3">
            <div style="padding: 30px 24px">
                <div class="procesando">
                    <div class="spinner"></div>
                    <h3>Procesando pago...</h3>
                    <p>No cierres esta ventana.<br>Estamos verificando tu transacción.</p>
                </div>
            </div>
        </div>

        <!-- Footer botones -->
        <div class="modal-footer" id="modal-footer">
            <button class="btn-modal-back" id="btn-back" onclick="pasoAnterior()" style="display:none">← Atrás</button>
            <button class="btn-modal-next" id="btn-next" onclick="pasoContinuar()" disabled>Continuar →</button>
        </div>

    </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/payments/js/pasarelaPago.js"></script>

</body>
</html>