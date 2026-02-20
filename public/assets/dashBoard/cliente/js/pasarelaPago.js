// ============================================
// VARIABLES GLOBALES
// ============================================
const form = document.getElementById('payment-form');
const cardNumberInput = document.getElementById('card-number');
const expiryDateInput = document.getElementById('expiry-date');
const cvvInput = document.getElementById('cvv');
const fullNameInput = document.getElementById('full-name');
const emailInput = document.getElementById('email');
const btnPay = document.getElementById('btn-pay');
const paymentResult = document.getElementById('payment-result');
const cardTypeIcon = document.getElementById('card-type');

// ============================================
// FORMATEO AUTOMÁTICO DE CAMPOS
// ============================================

// Formatear número de tarjeta (añade espacios cada 4 dígitos)
cardNumberInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, ''); // Quitar espacios
    let formattedValue = value.replace(/(\d{4})/g, '$1 ').trim(); // Añadir espacios
    e.target.value = formattedValue;
    
    // Detectar tipo de tarjeta
    detectCardType(value);
    
    // Validar en tiempo real
    validateCardNumber(value);
});

// Formatear fecha de expiración (MM/AA)
expiryDateInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, ''); // Solo números
    
    if (value.length >= 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }
    
    e.target.value = value;
    validateExpiryDate(value);
});

// Solo números en CVV
cvvInput.addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, ''); // Solo números
    validateCVV(e.target.value);
});

// Validar nombre en tiempo real
fullNameInput.addEventListener('input', function(e) {
    validateFullName(e.target.value);
});

// Validar email en tiempo real
emailInput.addEventListener('input', function(e) {
    validateEmail(e.target.value);
});

// ============================================
// DETECCIÓN DE TIPO DE TARJETA
// ============================================
// function detectCardType(cardNumber) {
//     const firstDigit = cardNumber.charAt(0);
//     const firstTwoDigits = cardNumber.slice(0, 2);
    
//     let cardType = '';
    
//     if (firstDigit === '4') {
//         cardType = '💳 Visa';
//     } else if (firstTwoDigits >= '51' && firstTwoDigits <= '55') {
//         cardType = '💳 Mastercard';
//     } else if (firstTwoDigits === '34' || firstTwoDigits === '37') {
//         cardType = '💳 Amex';
//     } else if (cardNumber.length > 0) {
//         cardType = '💳';
//     }
    
//     cardTypeIcon.textContent = cardType;
// }

// ============================================
// FUNCIONES DE VALIDACIÓN
// ============================================

// Validar número de tarjeta usando algoritmo de Luhn
function validateCardNumber(cardNumber) {
    const cleanNumber = cardNumber.replace(/\s/g, '');
    const input = cardNumberInput;
    const errorMsg = input.nextElementSibling.nextElementSibling;
    
    if (cleanNumber.length === 0) {
        setFieldState(input, errorMsg, '', 'normal');
        return false;
    }
    
    if (cleanNumber.length < 13 || cleanNumber.length > 19) {
        setFieldState(input, errorMsg, 'Número de tarjeta inválido', 'error');
        return false;
    }
    
    // Algoritmo de Luhn
    if (!luhnCheck(cleanNumber)) {
        setFieldState(input, errorMsg, 'Número de tarjeta inválido', 'error');
        return false;
    }
    
    setFieldState(input, errorMsg, '', 'success');
    return true;
}

// Algoritmo de Luhn para validar tarjetas
function luhnCheck(cardNumber) {
    let sum = 0;
    let isEven = false;
    
    for (let i = cardNumber.length - 1; i >= 0; i--) {
        let digit = parseInt(cardNumber.charAt(i));
        
        if (isEven) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }
        
        sum += digit;
        isEven = !isEven;
    }
    
    return (sum % 10) === 0;
}

// Validar fecha de expiración
function validateExpiryDate(expiryDate) {
    const input = expiryDateInput;
    const errorMsg = input.nextElementSibling;
    
    if (expiryDate.length === 0) {
        setFieldState(input, errorMsg, '', 'normal');
        return false;
    }
    
    if (expiryDate.length < 5) {
        setFieldState(input, errorMsg, 'Formato: MM/AA', 'error');
        return false;
    }
    
    const parts = expiryDate.split('/');
    const month = parseInt(parts[0]);
    const year = parseInt('20' + parts[1]);
    
    if (month < 1 || month > 12) {
        setFieldState(input, errorMsg, 'Mes inválido', 'error');
        return false;
    }
    
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth() + 1;
    
    if (year < currentYear || (year === currentYear && month < currentMonth)) {
        setFieldState(input, errorMsg, 'Tarjeta expirada', 'error');
        return false;
    }
    
    setFieldState(input, errorMsg, '', 'success');
    return true;
}

// Validar CVV
function validateCVV(cvv) {
    const input = cvvInput;
    const errorMsg = input.nextElementSibling;
    
    if (cvv.length === 0) {
        setFieldState(input, errorMsg, '', 'normal');
        return false;
    }
    
    if (cvv.length < 3 || cvv.length > 4) {
        setFieldState(input, errorMsg, 'CVV inválido (3-4 dígitos)', 'error');
        return false;
    }
    
    setFieldState(input, errorMsg, '', 'success');
    return true;
}

// Validar nombre completo
function validateFullName(name) {
    const input = fullNameInput;
    const errorMsg = input.nextElementSibling;
    
    if (name.length === 0) {
        setFieldState(input, errorMsg, '', 'normal');
        return false;
    }
    
    if (name.length < 3) {
        setFieldState(input, errorMsg, 'Nombre muy corto', 'error');
        return false;
    }
    
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(name)) {
        setFieldState(input, errorMsg, 'Solo letras y espacios', 'error');
        return false;
    }
    
    setFieldState(input, errorMsg, '', 'success');
    return true;
}

// Validar email
function validateEmail(email) {
    const input = emailInput;
    const errorMsg = input.nextElementSibling;
    
    if (email.length === 0) {
        setFieldState(input, errorMsg, '', 'normal');
        return false;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!emailRegex.test(email)) {
        setFieldState(input, errorMsg, 'Email inválido', 'error');
        return false;
    }
    
    setFieldState(input, errorMsg, '', 'success');
    return true;
}

// Establecer estado visual del campo
function setFieldState(input, errorMsg, message, state) {
    errorMsg.textContent = message;
    
    input.classList.remove('error', 'success');
    
    if (state === 'error') {
        input.classList.add('error');
    } else if (state === 'success') {
        input.classList.add('success');
    }
}

// ============================================
// VALIDACIÓN COMPLETA DEL FORMULARIO
// ============================================
function validateForm() {
    const isNameValid = validateFullName(fullNameInput.value);
    const isEmailValid = validateEmail(emailInput.value);
    const isCardValid = validateCardNumber(cardNumberInput.value.replace(/\s/g, ''));
    const isExpiryValid = validateExpiryDate(expiryDateInput.value);
    const isCvvValid = validateCVV(cvvInput.value);
    
    return isNameValid && isEmailValid && isCardValid && isExpiryValid && isCvvValid;
}

// ============================================
// PROCESAMIENTO DEL PAGO
// ============================================
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validar formulario completo
    if (!validateForm()) {
        showPaymentResult('error', 'Por favor, corrige los errores en el formulario');
        return;
    }
    
    // Deshabilitar botón y mostrar loader
    btnPay.disabled = true;
    btnPay.querySelector('.btn-text').style.display = 'none';
    btnPay.querySelector('.btn-loader').style.display = 'inline-block';
    
    // Simular procesamiento de pago (2 segundos)
    setTimeout(function() {
        // Simular éxito aleatorio (80% éxito, 20% fallo)
        const isSuccess = Math.random() > 0.2;
        
        if (isSuccess) {
            showPaymentResult('success', '¡Pago procesado exitosamente!', 
                'Tu pedido ha sido confirmado. Recibirás un correo de confirmación en breve.');
            form.reset();
            cardTypeIcon.textContent = '';
        } else {
            showPaymentResult('error', 'Error al procesar el pago', 
                'Por favor, verifica tus datos e intenta nuevamente.');
        }
        
        // Restaurar botón
        btnPay.disabled = false;
        btnPay.querySelector('.btn-text').style.display = 'inline-block';
        btnPay.querySelector('.btn-loader').style.display = 'none';
        
        // Limpiar estados de validación
        document.querySelectorAll('.form-group input').forEach(input => {
            input.classList.remove('error', 'success');
        });
        document.querySelectorAll('.error-message').forEach(msg => {
            msg.textContent = '';
        });
        
    }, 2000);
});

// ============================================
// MOSTRAR RESULTADO DEL PAGO
// ============================================
function showPaymentResult(type, title, message = '') {
    paymentResult.className = 'payment-result ' + type;
    paymentResult.innerHTML = `
        <h3>${title}</h3>
        ${message ? `<p>${message}</p>` : ''}
    `;
    paymentResult.style.display = 'block';
    
    // Scroll suave al resultado
    paymentResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    // Ocultar mensaje después de 5 segundos
    setTimeout(function() {
        paymentResult.style.display = 'none';
    }, 5000);
}