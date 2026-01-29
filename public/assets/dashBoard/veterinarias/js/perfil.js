/**
 * ============================================
 * PERFIL VETERINARIO - JAVASCRIPT
 * Versión Mejorada con validación y mejor UX
 * ============================================
 */

// ============================================
// UTILIDADES
// ============================================

/**
 * Espera a que el DOM esté completamente cargado
 */
const domReady = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
    } else {
        callback();
    }
};

/**
 * Muestra un mensaje de error en el formulario
 */
const mostrarError = (mensaje) => {
    const mensajeError = document.getElementById('mensajeError');
    if (mensajeError) {
        mensajeError.textContent = mensaje;
        mensajeError.classList.remove('d-none');
    }
};

/**
 * Oculta el mensaje de error
 */
const ocultarError = () => {
    const mensajeError = document.getElementById('mensajeError');
    if (mensajeError) {
        mensajeError.classList.add('d-none');
    }
};


// ============================================
// FUNCIONALIDAD: MOSTRAR/OCULTAR CONTRASEÑA
// ============================================

/**
 * Alterna la visibilidad de un campo de contraseña
 * @param {HTMLButtonElement} button - Botón del ojo
 */
const togglePasswordVisibility = (button) => {
    // Encontrar el contenedor padre
    const container = button.closest('.password');
    if (!container) return;

    // Encontrar el input de contraseña
    const input = container.querySelector('input[type="password"], input[type="text"]');
    if (!input) return;

    // Encontrar el icono
    const icon = button.querySelector('i');
    if (!icon) return;

    // Alternar tipo de input
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';

    // Actualizar icono
    if (isPassword) {
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
        icon.setAttribute('data-visible', 'true');
        button.setAttribute('aria-label', 'Ocultar contraseña');
    } else {
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
        icon.setAttribute('data-visible', 'false');
        button.setAttribute('aria-label', 'Mostrar contraseña');
    }
};

/**
 * Inicializa los botones de mostrar/ocultar contraseña
 */
const initPasswordToggles = () => {
    const toggleButtons = document.querySelectorAll('.icon-view');
    
    toggleButtons.forEach(button => {
        // Remover listeners anteriores si existen
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        // Agregar nuevo listener
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            togglePasswordVisibility(this);
        });
    });
};


// ============================================
// VALIDACIÓN DE CONTRASEÑA
// ============================================

/**
 * Valida la fortaleza de una contraseña
 * @param {string} password - Contraseña a validar
 * @returns {Object} - {isValid: boolean, mensaje: string}
 */
const validarFortalezaContrasena = (password) => {
    if (password.length < 8) {
        return {
            isValid: false,
            mensaje: 'La contraseña debe tener al menos 8 caracteres'
        };
    }

    // Opcional: Agregar validaciones adicionales
    const tieneNumero = /\d/.test(password);
    const tieneMayuscula = /[A-Z]/.test(password);
    const tieneMinuscula = /[a-z]/.test(password);

    if (!tieneNumero || !tieneMayuscula || !tieneMinuscula) {
        return {
            isValid: false,
            mensaje: 'La contraseña debe contener al menos una mayúscula, una minúscula y un número'
        };
    }

    return {
        isValid: true,
        mensaje: 'Contraseña válida'
    };
};

/**
 * Marca un campo como válido
 * @param {HTMLInputElement} input - Campo de entrada
 */
const marcarComoValido = (input) => {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
};

/**
 * Marca un campo como inválido
 * @param {HTMLInputElement} input - Campo de entrada
 * @param {string} mensaje - Mensaje de error opcional
 */
const marcarComoInvalido = (input, mensaje = null) => {
    input.classList.remove('is-valid');
    input.classList.add('is-invalid');
    
    if (mensaje) {
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = mensaje;
        }
    }
};

/**
 * Limpia el estado de validación de un campo
 * @param {HTMLInputElement} input - Campo de entrada
 */
const limpiarValidacion = (input) => {
    input.classList.remove('is-valid', 'is-invalid');
};


// ============================================
// VALIDACIÓN DEL FORMULARIO EN TIEMPO REAL
// ============================================

/**
 * Inicializa la validación en tiempo real
 */
const initValidacionTiempoReal = () => {
    const nuevaContrasena = document.getElementById('nueva-contrasena');
    const confirmarContrasena = document.getElementById('confi-contrasena');

    if (!nuevaContrasena || !confirmarContrasena) return;

    // Validar nueva contraseña mientras se escribe
    nuevaContrasena.addEventListener('input', function() {
        if (this.value.length === 0) {
            limpiarValidacion(this);
            return;
        }

        const validacion = validarFortalezaContrasena(this.value);
        
        if (validacion.isValid) {
            marcarComoValido(this);
        } else {
            marcarComoInvalido(this, validacion.mensaje);
        }

        // También validar confirmación si ya tiene contenido
        if (confirmarContrasena.value.length > 0) {
            validarConfirmacion();
        }
    });

    // Validar confirmación mientras se escribe
    confirmarContrasena.addEventListener('input', validarConfirmacion);

    function validarConfirmacion() {
        if (confirmarContrasena.value.length === 0) {
            limpiarValidacion(confirmarContrasena);
            return;
        }

        if (confirmarContrasena.value === nuevaContrasena.value) {
            marcarComoValido(confirmarContrasena);
        } else {
            marcarComoInvalido(confirmarContrasena, 'Las contraseñas no coinciden');
        }
    }
};


// ============================================
// VALIDACIÓN Y ENVÍO DEL FORMULARIO
// ============================================

/**
 * Valida el formulario completo antes de enviar
 * @param {Event} event - Evento de submit
 */
const validarFormulario = (event) => {
    event.preventDefault();
    event.stopPropagation();

    const form = event.target;
    const contrasenaActual = document.getElementById('contrasena-actual');
    const nuevaContrasena = document.getElementById('nueva-contrasena');
    const confirmarContrasena = document.getElementById('confi-contrasena');

    let isValid = true;
    ocultarError();

    // Validar contraseña actual
    if (!contrasenaActual.value.trim()) {
        marcarComoInvalido(contrasenaActual, 'Por favor ingrese su contraseña actual');
        isValid = false;
    } else {
        marcarComoValido(contrasenaActual);
    }

    // Validar nueva contraseña
    if (!nuevaContrasena.value.trim()) {
        marcarComoInvalido(nuevaContrasena, 'Por favor ingrese una nueva contraseña');
        isValid = false;
    } else {
        const validacion = validarFortalezaContrasena(nuevaContrasena.value);
        if (!validacion.isValid) {
            marcarComoInvalido(nuevaContrasena, validacion.mensaje);
            mostrarError(validacion.mensaje);
            isValid = false;
        } else {
            marcarComoValido(nuevaContrasena);
        }
    }

    // Validar confirmación
    if (!confirmarContrasena.value.trim()) {
        marcarComoInvalido(confirmarContrasena, 'Por favor confirme su contraseña');
        isValid = false;
    } else if (confirmarContrasena.value !== nuevaContrasena.value) {
        marcarComoInvalido(confirmarContrasena, 'Las contraseñas no coinciden');
        mostrarError('Las contraseñas no coinciden');
        isValid = false;
    } else {
        marcarComoValido(confirmarContrasena);
    }

    // Validación adicional: la nueva contraseña debe ser diferente a la actual
    if (isValid && nuevaContrasena.value === contrasenaActual.value) {
        marcarComoInvalido(nuevaContrasena, 'La nueva contraseña debe ser diferente a la actual');
        mostrarError('La nueva contraseña debe ser diferente a la actual');
        isValid = false;
    }

    // Si todo es válido, enviar el formulario
    if (isValid) {
        form.classList.add('was-validated');
        
        // Opcional: Mostrar loading state
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...';
        }
        
        // Enviar el formulario
        form.submit();
    } else {
        form.classList.add('was-validated');
        
        // Hacer scroll al primer campo con error
        const primerError = form.querySelector('.is-invalid');
        if (primerError) {
            primerError.focus();
            primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
};

/**
 * Inicializa la validación del formulario
 */
const initFormularioValidacion = () => {
    const form = document.getElementById('formCambiarContrasena');
    
    if (!form) return;

    // Remover listener anterior si existe
    const newForm = form.cloneNode(true);
    form.parentNode.replaceChild(newForm, form);

    // Agregar listener de submit
    newForm.addEventListener('submit', validarFormulario);

    // Limpiar validación cuando se abre el modal
    const modal = document.getElementById('modalCambiarContrasena');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            // Limpiar campos
            newForm.reset();
            
            // Limpiar estados de validación
            const inputs = newForm.querySelectorAll('input');
            inputs.forEach(input => limpiarValidacion(input));
            
            // Limpiar mensaje de error
            ocultarError();
            
            // Remover clase was-validated
            newForm.classList.remove('was-validated');
            
            // Resetear iconos de contraseña
            const iconos = newForm.querySelectorAll('.icon-view i');
            iconos.forEach(icono => {
                icono.classList.remove('bi-eye-slash');
                icono.classList.add('bi-eye');
                icono.setAttribute('data-visible', 'false');
            });
            
            // Resetear tipos de input a password
            const passwordInputs = newForm.querySelectorAll('input[type="text"]');
            passwordInputs.forEach(input => {
                if (input.name && input.name.includes('contrasena')) {
                    input.type = 'password';
                }
            });
        });
    }
};


// ============================================
// FUNCIONALIDAD: EDITAR FOTO DE PERFIL
// ============================================

/**
 * Inicializa el botón de editar foto
 */
const initEditarFoto = () => {
    const avatarEdit = document.querySelector('.avatar-edit');
    
    if (!avatarEdit) return;

    avatarEdit.addEventListener('click', function() {
        // Crear input file dinámicamente
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/jpeg,image/png,image/jpg,image/webp';
        
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (!file) return;

            // Validar tipo de archivo
            const tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (!tiposPermitidos.includes(file.type)) {
                alert('Por favor seleccione una imagen válida (JPG, PNG, WEBP)');
                return;
            }

            // Validar tamaño (máximo 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                alert('La imagen debe ser menor a 5MB');
                return;
            }

            // Mostrar preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.querySelector('.fotito');
                if (img) {
                    img.src = e.target.result;
                }
            };
            reader.readAsDataURL(file);

            // Aquí puedes agregar lógica para subir la imagen al servidor
            // Por ejemplo, usando FormData y fetch
            console.log('Imagen seleccionada:', file.name);
            
            // TODO: Implementar subida de imagen
            // subirImagenPerfil(file);
        });

        input.click();
    });
};


// ============================================
// FUNCIONALIDAD: ANIMACIONES Y UX
// ============================================

/**
 * Agrega animaciones de entrada a los elementos
 */
const initAnimaciones = () => {
    // Observador de intersección para animaciones al hacer scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    // Observar tarjetas
    const cards = document.querySelectorAll('.foto, .info, .especialidades, .estadisticas, .horarios, .citas, .consultas-notas');
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
};


// ============================================
// FUNCIONALIDAD: COPIAR INFORMACIÓN
// ============================================

/**
 * Permite copiar información al hacer clic
 */
const initCopiarInfo = () => {
    // Hacer que el email y teléfono sean copiables
    const email = document.querySelector('.foto h5');
    const telefono = document.querySelector('.foto h4');

    if (email) {
        email.style.cursor = 'pointer';
        email.title = 'Click para copiar';
        email.addEventListener('click', function() {
            copiarAlPortapapeles(this.textContent);
        });
    }

    if (telefono) {
        telefono.style.cursor = 'pointer';
        telefono.title = 'Click para copiar';
        telefono.addEventListener('click', function() {
            copiarAlPortapapeles(this.textContent.replace(/\s/g, ''));
        });
    }
};

/**
 * Copia texto al portapapeles
 * @param {string} texto - Texto a copiar
 */
const copiarAlPortapapeles = (texto) => {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(texto).then(() => {
            mostrarNotificacion('Copiado al portapapeles', 'success');
        }).catch(err => {
            console.error('Error al copiar:', err);
        });
    } else {
        // Fallback para navegadores antiguos
        const textarea = document.createElement('textarea');
        textarea.value = texto;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        mostrarNotificacion('Copiado al portapapeles', 'success');
    }
};

/**
 * Muestra una notificación temporal
 * @param {string} mensaje - Mensaje a mostrar
 * @param {string} tipo - Tipo de notificación (success, error, info)
 */
const mostrarNotificacion = (mensaje, tipo = 'info') => {
    // Crear elemento de notificación
    const notificacion = document.createElement('div');
    notificacion.className = `alert alert-${tipo === 'success' ? 'success' : 'info'} position-fixed`;
    notificacion.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    notificacion.textContent = mensaje;
    
    document.body.appendChild(notificacion);
    
    // Animar entrada
    setTimeout(() => {
        notificacion.style.opacity = '1';
        notificacion.style.transform = 'translateY(0)';
    }, 10);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notificacion.style.opacity = '0';
        notificacion.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            notificacion.remove();
        }, 300);
    }, 3000);
};


// ============================================
// INICIALIZACIÓN PRINCIPAL
// ============================================

/**
 * Inicializa todas las funcionalidades
 */
const init = () => {
    console.log('🚀 Inicializando perfil veterinario...');
    
    try {
        // Funcionalidades principales
        initPasswordToggles();
        initFormularioValidacion();
        initValidacionTiempoReal();
        
        // Funcionalidades adicionales
        initEditarFoto();
        initCopiarInfo();
        initAnimaciones();
        
        console.log('✅ Perfil veterinario inicializado correctamente');
    } catch (error) {
        console.error('❌ Error al inicializar perfil:', error);
    }
};

// Ejecutar cuando el DOM esté listo
domReady(init);


// ============================================
// EXPORTAR FUNCIONES (OPCIONAL)
// ============================================

// Si usas módulos ES6, puedes exportar las funciones
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        togglePasswordVisibility,
        validarFortalezaContrasena,
        copiarAlPortapapeles,
        mostrarNotificacion
    };
}