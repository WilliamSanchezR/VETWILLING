// ============================================
// CONFIGURACIÓN DE TABS
// ============================================
document.querySelector('.avatar-icon').addEventListener('click', () => {
    document.getElementById('upload-logo').click();
});

document.getElementById('upload-logo').addEventListener('change', function() {

    document.getElementById('form_cambio_imagen').submit();
});


/**
 * Cambia entre las diferentes pestañas de configuración
 */
function cambiarTab(tabName) {
    // Ocultar todos los contenidos de tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.style.display = 'none';
    });

    // Remover clase active de todos los botones
    const buttons = document.querySelectorAll('.tab-config');
    buttons.forEach(btn => {
        btn.classList.remove('active');
    });

    // Mostrar el tab seleccionado
    const selectedTab = document.getElementById(`tab-${tabName}`);
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }

    // Agregar clase active al botón clickeado
    event.target.closest('.tab-config').classList.add('active');

    // Guardar tab actual en localStorage
    localStorage.setItem('currentTab', tabName);
}

// Restaurar tab activo al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const savedTab = localStorage.getItem('currentTab');
    if (savedTab) {
        cambiarTab(savedTab);
    }
});


// ============================================
// GESTIÓN DE FOTO DE PERFIL
// ============================================

/**
 * Vista previa de la foto antes de subirla
 */
function previewFoto(event) {
    const file = event.target.files[0];
    
    if (file) {
        // Validar tamaño (2MB máximo)
        if (file.size > 2 * 1024 * 1024) {
            mostrarNotificacion('El archivo es demasiado grande. Máximo 2MB', 'error');
            event.target.value = '';
            return;
        }

        // Validar tipo
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            mostrarNotificacion('Formato no válido. Solo JPG, PNG o GIF', 'error');
            event.target.value = '';
            return;
        }

        // Mostrar preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.querySelector('.avatar-grande img');
            if (img) {
                img.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);

        mostrarNotificacion('Foto cargada. Haz clic en "Guardar Cambios" para actualizar', 'success');
    }
}

/**
 * Eliminar foto de perfil
 */
function eliminarFoto() {
    if (confirm('¿Estás seguro de que quieres eliminar tu foto de perfil?')) {
        const img = document.querySelector('.avatar-grande img');
        const input = document.getElementById('inputFoto');
        
        if (img) {
            // Poner imagen por defecto
            img.src = BASE_URL + '/public/assets/img/default-avatar.png';
        }
        
        if (input) {
            input.value = '';
        }

        mostrarNotificacion('Foto eliminada. Haz clic en "Guardar Cambios" para confirmar', 'info');
    }
}


// ============================================
// GESTIÓN DE CONTRASEÑAS
// ============================================

/**
 * Alternar visibilidad de contraseña
 */
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

/**
 * Verificar fortaleza de la contraseña
 */
function checkPasswordStrength() {
    const password = document.getElementById('newPassword').value;
    const indicator = document.getElementById('strengthIndicator');
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');

    if (password.length === 0) {
        indicator.style.display = 'none';
        return;
    }

    indicator.style.display = 'block';

    let strength = 0;
    const checks = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };

    // Calcular fortaleza
    if (checks.length) strength += 20;
    if (checks.uppercase) strength += 20;
    if (checks.lowercase) strength += 20;
    if (checks.number) strength += 20;
    if (checks.special) strength += 20;

    // Actualizar indicador visual
    fill.style.width = strength + '%';
    
    if (strength <= 40) {
        fill.style.background = '#ef4444';
        text.textContent = 'Débil';
        text.style.color = '#ef4444';
    } else if (strength <= 60) {
        fill.style.background = '#f59e0b';
        text.textContent = 'Media';
        text.style.color = '#f59e0b';
    } else if (strength <= 80) {
        fill.style.background = '#3b82f6';
        text.textContent = 'Buena';
        text.style.color = '#3b82f6';
    } else {
        fill.style.background = '#10b981';
        text.textContent = 'Excelente';
        text.style.color = '#10b981';
    }

    // Actualizar requisitos visuales
    updateRequirement('req-length', checks.length);
    updateRequirement('req-uppercase', checks.uppercase);
    updateRequirement('req-number', checks.number);
}

/**
 * Actualizar estado visual de requisitos
 */
function updateRequirement(id, met) {
    const elem = document.getElementById(id);
    const icon = elem.querySelector('i');
    
    if (met) {
        elem.style.color = '#10b981';
        icon.classList.remove('bi-circle');
        icon.classList.add('bi-check-circle-fill');
    } else {
        elem.style.color = '#94a3b8';
        icon.classList.remove('bi-check-circle-fill');
        icon.classList.add('bi-circle');
    }
}

/**
 * Verificar que las contraseñas coincidan
 */
function checkPasswordMatch() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const indicator = document.getElementById('matchIndicator');

    if (confirmPassword.length === 0) {
        indicator.innerHTML = '';
        return;
    }

    if (newPassword === confirmPassword) {
        indicator.innerHTML = '<i class="bi bi-check-circle-fill" style="color: #10b981;"></i> <span style="color: #10b981;">Las contraseñas coinciden</span>';
    } else {
        indicator.innerHTML = '<i class="bi bi-x-circle-fill" style="color: #ef4444;"></i> <span style="color: #ef4444;">Las contraseñas no coinciden</span>';
    }
}

/**
 * Validar formulario de contraseña antes de enviar
 */
document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.getElementById('passwordForm');
    
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                mostrarNotificacion('Las contraseñas no coinciden', 'error');
                return false;
            }

            // Verificar requisitos mínimos
            if (newPassword.length < 8) {
                e.preventDefault();
                mostrarNotificacion('La contraseña debe tener al menos 8 caracteres', 'error');
                return false;
            }

            if (!/[A-Z]/.test(newPassword)) {
                e.preventDefault();
                mostrarNotificacion('La contraseña debe contener al menos una mayúscula', 'error');
                return false;
            }

            if (!/[0-9]/.test(newPassword)) {
                e.preventDefault();
                mostrarNotificacion('La contraseña debe contener al menos un número', 'error');
                return false;
            }
        });
    }
});


// ============================================
// FAQ - PREGUNTAS FRECUENTES
// ============================================

/**
 * Expandir/colapsar preguntas FAQ
 */
function toggleFAQ(element) {
    const faqItem = element.parentElement;
    const answer = faqItem.querySelector('.faq-answer');
    const icon = element.querySelector('i');
    
    // Cerrar otras FAQs abiertas
    document.querySelectorAll('.faq-item').forEach(item => {
        if (item !== faqItem) {
            item.classList.remove('active');
            const otherIcon = item.querySelector('.faq-question i');
            if (otherIcon) {
                otherIcon.classList.remove('bi-chevron-up');
                otherIcon.classList.add('bi-chevron-down');
            }
        }
    });

    // Toggle actual
    faqItem.classList.toggle('active');
    
    if (faqItem.classList.contains('active')) {
        icon.classList.remove('bi-chevron-down');
        icon.classList.add('bi-chevron-up');
    } else {
        icon.classList.remove('bi-chevron-up');
        icon.classList.add('bi-chevron-down');
    }
}


// ============================================
// TOGGLE SWITCHES (NOTIFICACIONES)
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todos los toggle switches
    const toggles = document.querySelectorAll('.toggle-switch');
    
    toggles.forEach(toggle => {
        const setting = toggle.getAttribute('data-setting');
        
        // Cargar estado guardado
        const savedState = localStorage.getItem(setting);
        if (savedState === 'true') {
            toggle.classList.add('active');
        }

        // Agregar evento click
        toggle.addEventListener('click', function() {
            this.classList.toggle('active');
            const isActive = this.classList.contains('active');
            
            // Guardar estado
            localStorage.setItem(setting, isActive);
            
            // Actualizar estadísticas
            updateNotificationStats();
            
            // Mostrar notificación
            const settingName = this.closest('.config-item').querySelector('h4').textContent;
            mostrarNotificacion(`${settingName} ${isActive ? 'activado' : 'desactivado'}`, 'success');
        });
    });

    // Actualizar estadísticas al cargar
    updateNotificationStats();
});

/**
 * Actualizar estadísticas de notificaciones
 */
function updateNotificationStats() {
    const pushToggles = document.querySelectorAll('[data-setting^="push-"]');
    const emailToggles = document.querySelectorAll('[data-setting^="email-"]');
    const smsToggles = document.querySelectorAll('[data-setting^="sms-"]');

    let pushCount = 0;
    let emailCount = 0;
    let smsCount = 0;

    pushToggles.forEach(toggle => {
        if (toggle.classList.contains('active')) pushCount++;
    });

    emailToggles.forEach(toggle => {
        if (toggle.classList.contains('active')) emailCount++;
    });

    smsToggles.forEach(toggle => {
        if (toggle.classList.contains('active')) smsCount++;
    });

    const total = pushCount + emailCount + smsCount;

    // Actualizar UI
    const totalElem = document.getElementById('totalNotifications');
    const pushElem = document.getElementById('pushCount');
    const emailElem = document.getElementById('emailCount');
    const smsElem = document.getElementById('smsCount');

    if (totalElem) totalElem.textContent = total;
    if (pushElem) pushElem.textContent = pushCount;
    if (emailElem) emailElem.textContent = emailCount;
    if (smsElem) smsElem.textContent = smsCount;
}


// ============================================
// CONFIGURACIÓN GENERAL
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Idioma
    const selectIdioma = document.getElementById('selectIdioma');
    if (selectIdioma) {
        // Cargar idioma guardado
        const savedLang = localStorage.getItem('language') || 'es';
        selectIdioma.value = savedLang;

        selectIdioma.addEventListener('change', function() {
            const lang = this.value;
            localStorage.setItem('language', lang);
            
            const languageInfo = document.getElementById('languageInfo');
            if (languageInfo) {
                languageInfo.style.display = 'flex';
                setTimeout(() => {
                    languageInfo.style.display = 'none';
                }, 3000);
            }

            mostrarNotificacion('Idioma actualizado correctamente', 'success');
        });
    }

    // Zona Horaria
    const selectZonaHoraria = document.getElementById('selectZonaHoraria');
    if (selectZonaHoraria) {
        const savedTimezone = localStorage.getItem('timezone') || 'gmt-5';
        selectZonaHoraria.value = savedTimezone;

        selectZonaHoraria.addEventListener('change', function() {
            localStorage.setItem('timezone', this.value);
            mostrarNotificacion('Zona horaria actualizada', 'success');
        });
    }

    // Formato de Fecha
    const selectFormatoFecha = document.getElementById('selectFormatoFecha');
    if (selectFormatoFecha) {
        const savedFormat = localStorage.getItem('dateFormat') || 'dd/mm/yyyy';
        selectFormatoFecha.value = savedFormat;

        selectFormatoFecha.addEventListener('change', function() {
            localStorage.setItem('dateFormat', this.value);
            mostrarNotificacion('Formato de fecha actualizado', 'success');
        });
    }
});


// ============================================
// SISTEMA DE NOTIFICACIONES
// ============================================

/**
 * Mostrar notificación toast
 */
function mostrarNotificacion(mensaje, tipo = 'success') {
    const container = document.getElementById('toastContainer');
    
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${tipo}`;
    
    const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };

    toast.innerHTML = `
        <i class="bi ${icons[tipo] || icons.success}"></i>
        <span>${mensaje}</span>
    `;

    container.appendChild(toast);

    // Animar entrada
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Remover después de 4 segundos
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 4000);
}

/**
 * Mostrar notificación de guardado global
 */
function mostrarNotificacionGuardado(texto = 'Configuración guardada') {
    const notification = document.getElementById('saveNotification');
    const notificationText = document.getElementById('notificationText');
    
    if (notification && notificationText) {
        notificationText.textContent = texto;
        notification.classList.add('show');
        
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }
}


// ============================================
// VALIDACIONES DE FORMULARIOS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Validación de teléfono
    const telefonoInputs = document.querySelectorAll('input[type="tel"]');
    telefonoInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Permitir solo números, espacios y + -
            this.value = this.value.replace(/[^0-9\s\+\-]/g, '');
        });
    });

    // Validación de documento
    const docInput = document.querySelector('input[name="numero_documento"]');
    if (docInput) {
        docInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    // Confirmación antes de enviar formulario principal
    const mainForm = document.querySelector('form[action*="actualizar"]');
    if (mainForm) {
        mainForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
            }
        });
    }
});


// ============================================
// UTILIDADES
// ============================================

/**
 * Formatear fecha según preferencia del usuario
 */
function formatDate(date) {
    const format = localStorage.getItem('dateFormat') || 'dd/mm/yyyy';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();

    switch(format) {
        case 'mm/dd/yyyy':
            return `${month}/${day}/${year}`;
        case 'yyyy-mm-dd':
            return `${year}-${month}-${day}`;
        default: // dd/mm/yyyy
            return `${day}/${month}/${year}`;
    }
}

/**
 * Detectar cambios no guardados
 */
let hasUnsavedChanges = false;

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                hasUnsavedChanges = true;
            });
        });

        form.addEventListener('submit', function() {
            hasUnsavedChanges = false;
        });
    });

    // Advertir antes de salir si hay cambios no guardados
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});


// ============================================
// INICIALIZACIÓN
// ============================================

console.log('✅ Configuración de VetWilling cargada correctamente');