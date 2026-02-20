/* ============================================
   PAGO — Integración MercadoPago (modo demo)
   ============================================ */

// ---- Configuración ----
const MP_PUBLIC_KEY = 'TEST-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'; // Demo key
const BASE_URL      = window.BASE_URL || '';

// ---- Referencias DOM ----
const form           = document.getElementById('payment-form');
const cardNumberInput = document.getElementById('card-number');
const expiryDateInput = document.getElementById('expiry-date');
const cvvInput        = document.getElementById('cvv');
const fullNameInput   = document.getElementById('full-name');
const emailInput      = document.getElementById('email');
const btnPay          = document.getElementById('btn-pay');
const paymentResult   = document.getElementById('payment-result');
const cardTypeIcon    = document.getElementById('card-type');

// ============================================
// DETECCIÓN DE TIPO DE TARJETA
// ============================================
const CARD_PATTERNS = {
    visa:       { pattern: /^4/,                      label: 'Visa',       icon: 'bi-credit-card-2-front' },
    mastercard: { pattern: /^5[1-5]|^2[2-7]/,         label: 'Mastercard', icon: 'bi-credit-card'         },
    amex:       { pattern: /^3[47]/,                   label: 'Amex',       icon: 'bi-credit-card-fill'    },
    diners:     { pattern: /^3(?:0[0-5]|[68])/,       label: 'Diners',     icon: 'bi-credit-card'         },
};

function detectCardType(number) {
    const clean = number.replace(/\s/g, '');
    for (const [type, data] of Object.entries(CARD_PATTERNS)) {
        if (data.pattern.test(clean)) {
            cardTypeIcon.innerHTML = `<i class="bi ${data.icon}"></i> ${data.label}`;
            cardNumberInput.setAttribute('data-card-type', type);
            return type;
        }
    }
    cardTypeIcon.innerHTML = '';
    cardNumberInput.removeAttribute('data-card-type');
    return null;
}

// ============================================
// FORMATEO AUTOMÁTICO
// ============================================
cardNumberInput.addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '').slice(0, 16);
    e.target.value = value.replace(/(.{4})/g, '$1 ').trim();
    detectCardType(value);
    validateCardNumber(value);
});

expiryDateInput.addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '').slice(0, 4);
    if (value.length >= 3) value = value.slice(0, 2) + '/' + value.slice(2);
    e.target.value = value;
    validateExpiryDate(value);
});

cvvInput.addEventListener('input', function (e) {
    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
    validateCVV(e.target.value);
});

fullNameInput.addEventListener('input', () => validateFullName(fullNameInput.value));
emailInput.addEventListener('input',    () => validateEmail(emailInput.value));

// ============================================
// VALIDACIONES
// ============================================
function luhnCheck(number) {
    let sum = 0, isEven = false;
    for (let i = number.length - 1; i >= 0; i--) {
        let digit = parseInt(number[i]);
        if (isEven) { digit *= 2; if (digit > 9) digit -= 9; }
        sum += digit;
        isEven = !isEven;
    }
    return sum % 10 === 0;
}

function validateCardNumber(number) {
    const clean = number.replace(/\s/g, '');
    const input = cardNumberInput;
    const err   = getError(input);

    if (!clean) return setField(input, err, '', 'normal'), false;
    if (clean.length < 13 || clean.length > 19)
        return setField(input, err, 'Número de tarjeta inválido', 'error'), false;
    if (!luhnCheck(clean))
        return setField(input, err, 'Número de tarjeta inválido', 'error'), false;

    setField(input, err, '✓ Tarjeta válida', 'success');
    return true;
}

function validateExpiryDate(value) {
    const input = expiryDateInput;
    const err   = getError(input);

    if (!value) return setField(input, err, '', 'normal'), false;
    if (value.length < 5) return setField(input, err, 'Formato: MM/AA', 'error'), false;

    const [mm, yy] = value.split('/');
    const month = parseInt(mm), year = parseInt('20' + yy);
    const now = new Date();

    if (month < 1 || month > 12)
        return setField(input, err, 'Mes inválido', 'error'), false;
    if (year < now.getFullYear() || (year === now.getFullYear() && month < now.getMonth() + 1))
        return setField(input, err, 'Tarjeta expirada', 'error'), false;

    setField(input, err, '✓ Fecha válida', 'success');
    return true;
}

function validateCVV(cvv) {
    const input = cvvInput;
    const err   = getError(input);
    const isAmex = cardNumberInput.getAttribute('data-card-type') === 'amex';
    const required = isAmex ? 4 : 3;

    if (!cvv) return setField(input, err, '', 'normal'), false;
    if (cvv.length < required)
        return setField(input, err, `CVV inválido (${required} dígitos)`, 'error'), false;

    setField(input, err, '✓ CVV válido', 'success');
    return true;
}

function validateFullName(name) {
    const input = fullNameInput;
    const err   = getError(input);

    if (!name) return setField(input, err, '', 'normal'), false;
    if (name.trim().length < 3) return setField(input, err, 'Nombre muy corto', 'error'), false;
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(name))
        return setField(input, err, 'Solo letras y espacios', 'error'), false;

    setField(input, err, '✓ Nombre válido', 'success');
    return true;
}

function validateEmail(email) {
    const input = emailInput;
    const err   = getError(input);

    if (!email) return setField(input, err, '', 'normal'), false;
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))
        return setField(input, err, 'Email inválido', 'error'), false;

    setField(input, err, '✓ Email válido', 'success');
    return true;
}

function validateForm() {
    return [
        validateFullName(fullNameInput.value),
        validateEmail(emailInput.value),
        validateCardNumber(cardNumberInput.value),
        validateExpiryDate(expiryDateInput.value),
        validateCVV(cvvInput.value),
    ].every(Boolean);
}

// ============================================
// HELPERS UI
// ============================================
function getError(input) {
    return input.parentElement.querySelector('.error-message')
        || input.nextElementSibling;
}

function setField(input, err, msg, state) {
    if (err) err.textContent = msg;
    input.classList.remove('is-error', 'is-success');
    if (state === 'error')   input.classList.add('is-error');
    if (state === 'success') input.classList.add('is-success');
}

function setBtnLoading(loading) {
    btnPay.disabled = loading;
    btnPay.querySelector('.btn-text').style.display   = loading ? 'none'         : 'inline-block';
    btnPay.querySelector('.btn-loader').style.display = loading ? 'inline-block' : 'none';
}

// ============================================
// SIMULACIÓN DE TOKENIZACIÓN MP (demo segura)
// ============================================
function simulateTokenize(cardData) {
    return new Promise((resolve) => {
        // En producción: mp.createCardToken(cardData).then(resolve)
        setTimeout(() => {
            resolve({
                id:              'demo_token_' + Math.random().toString(36).slice(2, 12),
                last_four_digits: cardData.cardNumber.slice(-4),
                expiration_month: cardData.expirationMonth,
                expiration_year:  cardData.expirationYear,
                cardholder:       { name: cardData.cardholderName },
                status:           'active',
            });
        }, 900);
    });
}

function simulatePayment(token, email) {
    return new Promise((resolve) => {
        // En producción: POST al backend con el token (nunca los datos de tarjeta)
        setTimeout(() => {
            resolve({
                status:           'approved',
                status_detail:    'accredited',
                id:               'DEMO-' + Date.now(),
                transaction_amount: window.MONTO_PAGO || 0,
                payer:            { email },
                token:            token.id,
            });
        }, 1100);
    });
}

// ============================================
// SUBMIT — FLUJO DE PAGO
// ============================================
form.addEventListener('submit', async function (e) {
    e.preventDefault();

    if (!validateForm()) {
        showResult('error', 'Revisa los campos marcados en rojo antes de continuar.');
        return;
    }

    setBtnLoading(true);
    showResult('info', 'Procesando pago de forma segura…');

    try {
        // 1 — Tokenizar tarjeta (demo: nunca se envían datos reales)
        const [expiryMM, expiryYY] = expiryDateInput.value.split('/');
        const cardData = {
            cardNumber:      cardNumberInput.value.replace(/\s/g, ''),
            cardholderName:  fullNameInput.value.trim(),
            expirationMonth: expiryMM,
            expirationYear:  '20' + expiryYY,
            securityCode:    cvvInput.value,
        };

        showResult('info', '<i class="bi bi-shield-lock me-1"></i> Tokenizando tarjeta…');
        const token = await simulateTokenize(cardData);

        // 2 — Enviar SOLO el token al backend (nunca datos de tarjeta)
        showResult('info', '<i class="bi bi-arrow-repeat spin me-1"></i> Confirmando con MercadoPago…');
        const payment = await simulatePayment(token, emailInput.value.trim());

        // 3 — Evaluar respuesta
        if (payment.status === 'approved') {
            showResult('success',
                '<i class="bi bi-check-circle-fill me-2"></i> ¡Pago aprobado!',
                `Número de operación: <strong>${payment.id}</strong><br>Redirigiendo…`
            );

            // 4 — Redirigir a /confirmacion-pago con datos por query (demo)
            //     En producción el backend hace la redirección tras validar el webhook
            setTimeout(() => {
                const params = new URLSearchParams({
                    status:     payment.status,
                    payment_id: payment.id,
                    token:      token.id,
                    email:      payment.payer.email,
                });
                window.location.href = `${BASE_URL}/confirmacion-pago?${params.toString()}`;
            }, 1800);

        } else if (payment.status === 'pending') {
            showResult('warning',
                '<i class="bi bi-clock-history me-2"></i> Pago pendiente',
                'Tu pago está en revisión. Te notificaremos por correo.'
            );
            setBtnLoading(false);

        } else {
            throw new Error('Pago rechazado: ' + payment.status_detail);
        }

    } catch (error) {
        console.error('[Pago] Error:', error.message);
        showResult('error',
            '<i class="bi bi-x-circle-fill me-2"></i> No pudimos procesar el pago',
            'Verifica tus datos o intenta con otra tarjeta.'
        );
        setBtnLoading(false);
    }
});

// ============================================
// MOSTRAR RESULTADO
// ============================================
function showResult(type, title, body = '') {
    const icons = {
        success: 'text-success',
        error:   'text-danger',
        warning: 'text-warning',
        info:    'text-primary',
    };

    paymentResult.className = `payment-result ${type}`;
    paymentResult.innerHTML = `
        <p class="mb-1 fw-semibold ${icons[type] || ''}">${title}</p>
        ${body ? `<small>${body}</small>` : ''}
    `;
    paymentResult.style.display = 'block';
    paymentResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    // Auto-ocultar solo mensajes de error/warning
    if (type === 'error' || type === 'warning') {
        clearTimeout(paymentResult._timeout);
        paymentResult._timeout = setTimeout(() => {
            paymentResult.style.display = 'none';
        }, 6000);
    }
}

// ---- Animación spinner ----
const style = document.createElement('style');
style.textContent = `
    .spin { display: inline-block; animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .is-error   { border-color: #e53935 !important; box-shadow: 0 0 0 3px rgba(229,57,53,0.12) !important; }
    .is-success { border-color: #2e7d32 !important; box-shadow: 0 0 0 3px rgba(46,125,50,0.12)  !important; }
    .error-message { color: #e53935; font-size: 12px; margin-top: 4px; display: block; min-height: 16px; }
`;
document.head.appendChild(style);