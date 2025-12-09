// ==================== ESTADO GLOBAL ====================
const ConfigState = {
    settings: {},
    animations: true,
    autoSave: true
};

// ==================== INICIALIZACIÓN ====================
document.addEventListener('DOMContentLoaded', function() {
    initializeConfiguration();
    loadSavedSettings();
    initializeToggles();
    initializeLanguage();
    initializeTheme();
    initializePasswordValidation();
    updateNotificationStats();
});

// ==================== CONFIGURACIÓN INICIAL ====================
function initializeConfiguration() {
    // Cargar configuraciones guardadas del localStorage
    const savedConfig = localStorage.getItem('vetWillingConfig');
    if (savedConfig) {
        ConfigState.settings = JSON.parse(savedConfig);
    } else {
        // Configuración por defecto
        ConfigState.settings = {
            language: 'es',
            timezone: 'gmt-5',
            theme: 'light',
            textSize: '16',
            notifications: {
                'push-enabled': false,
                'push-appointments': false,
                'push-vaccines': false,
                'push-messages': false,
                'email-appointments': false,
                'email-promotions': false,
                'email-newsletter': false,
                'sms-enabled': false,
                'sms-confirmations': false
            }
        };
    }
}

// ==================== SISTEMA DE TABS ====================
function cambiarTab(tabName) {
    // Ocultar todos los contenidos
    const allTabs = document.querySelectorAll('.tab-content');
    allTabs.forEach(tab => {
        tab.style.display = 'none';
        tab.style.opacity = '0';
    });

    // Remover clase activa de todos los botones
    const allButtons = document.querySelectorAll('.tab-config');
    allButtons.forEach(btn => btn.classList.remove('active'));

    // Mostrar tab seleccionado con animación
    const selectedTab = document.getElementById(`tab-${tabName}`);
    const selectedButton = event.target.closest('.tab-config');
    
    if (selectedTab) {
        selectedTab.style.display = 'block';
        setTimeout(() => {
            selectedTab.style.opacity = '1';
        }, 10);
    }

    if (selectedButton) {
        selectedButton.classList.add('active');
    }

    // Animación de entrada
    animateTabContent(selectedTab);
}

function animateTabContent(tab) {
    if (!tab) return;
    
    const cards = tab.querySelectorAll('.config-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// ==================== SEGURIDAD: VALIDACIÓN DE CONTRASEÑA ====================
function initializePasswordValidation() {
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    
    if (newPassword) {
        newPassword.addEventListener('input', checkPasswordStrength);
    }
    
    if (confirmPassword) {
        confirmPassword.addEventListener('input', checkPasswordMatch);
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('newPassword').value;
    const strengthIndicator = document.getElementById('strengthIndicator');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    if (!password) {
        strengthIndicator.style.display = 'none';
        return;
    }
    
    strengthIndicator.style.display = 'block';
    
    let strength = 0;
    let strengthLabel = '';
    let color = '';
    
    // Calcular fortaleza
    if (password.length >= 8) strength += 25;
    if (password.length >= 12) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 25;
    if (/[^A-Za-z0-9]/.test(password)) strength += 25;
    
    // Determinar nivel
    if (strength <= 25) {
        strengthLabel = 'Muy débil';
        color = '#ef4444';
    } else if (strength <= 50) {
        strengthLabel = 'Débil';
        color = '#f97316';
    } else if (strength <= 75) {
        strengthLabel = 'Buena';
        color = '#eab308';
    } else {
        strengthLabel = 'Fuerte';
        color = '#22c55e';
    }
    
    // Aplicar estilos con animación
    strengthFill.style.width = strength + '%';
    strengthFill.style.backgroundColor = color;
    strengthText.textContent = strengthLabel;
    strengthText.style.color = color;
    
    // Actualizar requisitos
    updateRequirement('req-length', password.length >= 8);
    updateRequirement('req-uppercase', /[A-Z]/.test(password));
    updateRequirement('req-number', /[0-9]/.test(password));
}

function updateRequirement(id, met) {
    const element = document.getElementById(id);
    if (!element) return;
    
    const icon = element.querySelector('i');
    
    if (met) {
        element.classList.add('met');
        icon.className = 'bi bi-check-circle-fill';
    } else {
        element.classList.remove('met');
        icon.className = 'bi bi-circle';
    }
}

function checkPasswordMatch() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const matchIndicator = document.getElementById('matchIndicator');
    
    if (!confirmPassword) {
        matchIndicator.textContent = '';
        return;
    }
    
    if (newPassword === confirmPassword) {
        matchIndicator.innerHTML = '<i class="bi bi-check-circle-fill" style="color: #22c55e;"></i> Las contraseñas coinciden';
        matchIndicator.style.color = '#22c55e';
    } else {
        matchIndicator.innerHTML = '<i class="bi bi-x-circle-fill" style="color: #ef4444;"></i> Las contraseñas no coinciden';
        matchIndicator.style.color = '#ef4444';
    }
}

function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
    
    // Animación del botón
    button.style.transform = 'scale(0.9)';
    setTimeout(() => {
        button.style.transform = 'scale(1)';
    }, 100);
}