/**
 * SOLUCIÓN MEJORADA - Sistema de Toggle de Contraseñas
 * Elimina la dependencia de checkboxes ocultos y simplifica la lógica
 */

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * Función mejorada para alternar visibilidad de contraseñas
     * @param {HTMLElement} button - El botón que fue clickeado
     */
    function togglePassword(button) {
        // Encontrar el contenedor padre más cercano
        const passwordGroup = button.closest('.form-group.password');
        
        if (!passwordGroup) {
            console.error('No se encontró el contenedor .form-group.password');
            return;
        }
        
        // Encontrar el input de contraseña dentro del grupo
        const passwordInput = passwordGroup.querySelector('input[type="password"], input[type="text"]');
        
        if (!passwordInput) {
            console.error('No se encontró el input de contraseña');
            return;
        }
        
        // Encontrar los iconos
        const iconVisible = button.querySelector('.bi-eye');
        const iconHidden = button.querySelector('.bi-eye-slash');
        
        if (!iconVisible || !iconHidden) {
            console.error('No se encontraron los iconos');
            return;
        }
        
        // Alternar el tipo de input y la visibilidad de los iconos
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            iconVisible.style.display = 'none';
            iconHidden.style.display = 'block';
        } else {
            passwordInput.type = 'password';
            iconVisible.style.display = 'block';
            iconHidden.style.display = 'none';
        }
    }
    
    /**
     * Inicializar los event listeners para todos los botones de toggle
     */
    function initPasswordToggles() {
        // Seleccionar TODOS los botones con la clase icon-view
        const toggleButtons = document.querySelectorAll('.form-group.password .icon-view');
        
        console.log(`Se encontraron ${toggleButtons.length} botones de toggle`);
        
        toggleButtons.forEach((button, index) => {
            // Remover listeners previos (si existen)
            button.replaceWith(button.cloneNode(true));
        });
        
        // Volver a seleccionar después de clonar (esto limpia los event listeners)
        const freshButtons = document.querySelectorAll('.form-group.password .icon-view');
        
        freshButtons.forEach((button, index) => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Prevenir comportamiento por defecto
                e.stopPropagation(); // Evitar propagación del evento
                togglePassword(this);
                console.log(`Toggle activado en botón ${index + 1}`);
            });
        });
    }
    
    /**
     * Validación de contraseñas en tiempo real
     */
    function setupPasswordValidation() {
        const newPasswordInput = document.getElementById('nueva-contrasena');
        const confirmPasswordInput = document.getElementById('confi-contrasena');
        
        if (!newPasswordInput || !confirmPasswordInput) {
            console.warn('Campos de validación no encontrados');
            return;
        }
        
        // Validar al escribir en confirmar contraseña
        confirmPasswordInput.addEventListener('input', function() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = this.value;
            
            if (confirmPassword && newPassword !== confirmPassword) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (confirmPassword && newPassword === confirmPassword) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-invalid', 'is-valid');
            }
        });
        
        // Validar también al cambiar la nueva contraseña
        newPasswordInput.addEventListener('input', function() {
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword) {
                confirmPasswordInput.dispatchEvent(new Event('input'));
            }
        });
    }
    
    /**
     * Validación del formulario antes de enviar
     */
    function setupFormValidation() {
        const form = document.querySelector('form[action*="actualizar-contrasena"]');
        
        if (!form) {
            console.warn('Formulario no encontrado');
            return;
        }
        
        form.addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('contrasena-actual');
            const newPassword = document.getElementById('nueva-contrasena');
            const confirmPassword = document.getElementById('confi-contrasena');
            
            let isValid = true;
            let errorMessage = '';
            
            // Validar que todos los campos estén llenos
            if (!currentPassword.value.trim()) {
                isValid = false;
                errorMessage = 'La contraseña actual es requerida';
                currentPassword.classList.add('is-invalid');
            }
            
            if (!newPassword.value.trim()) {
                isValid = false;
                errorMessage = 'La nueva contraseña es requerida';
                newPassword.classList.add('is-invalid');
            }
            
            if (!confirmPassword.value.trim()) {
                isValid = false;
                errorMessage = 'Debe confirmar la nueva contraseña';
                confirmPassword.classList.add('is-invalid');
            }
            
            // Validar que las contraseñas coincidan
            if (newPassword.value && confirmPassword.value && newPassword.value !== confirmPassword.value) {
                isValid = false;
                errorMessage = 'Las contraseñas no coinciden';
                confirmPassword.classList.add('is-invalid');
            }
            
            // Validar longitud mínima
            if (newPassword.value && newPassword.value.length < 6) {
                isValid = false;
                errorMessage = 'La nueva contraseña debe tener al menos 6 caracteres';
                newPassword.classList.add('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
                return false;
            }
            
            return true;
        });
    }
    
    /**
     * Limpiar el formulario cuando se cierre el modal
     */
    function setupModalReset() {
        const modal = document.getElementById('exampleModal');
        
        if (!modal) {
            console.warn('Modal no encontrado');
            return;
        }
        
        modal.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            
            if (form) {
                form.reset();
                
                // Limpiar clases de validación
                const inputs = form.querySelectorAll('input');
                inputs.forEach(input => {
                    input.classList.remove('is-valid', 'is-invalid');
                    input.type = 'password'; // Restaurar tipo password
                });
                
                // Restaurar iconos
                const eyeIcons = form.querySelectorAll('.bi-eye');
                const eyeSlashIcons = form.querySelectorAll('.bi-eye-slash');
                
                eyeIcons.forEach(icon => icon.style.display = 'block');
                eyeSlashIcons.forEach(icon => icon.style.display = 'none');
            }
        });
    }
    
    // ========================================
    // INICIALIZACIÓN
    // ========================================
    
    console.log('Inicializando sistema de contraseñas...');
    
    // Inicializar toggles de contraseña
    initPasswordToggles();
    
    // Inicializar validación
    setupPasswordValidation();
    
    // Inicializar validación del formulario
    setupFormValidation();
    
    // Inicializar reset del modal
    setupModalReset();
    
    console.log('Sistema de contraseñas inicializado correctamente');
    
    // Re-inicializar cuando se muestra el modal (por si acaso)
    const modal = document.getElementById('exampleModal');
    if (modal) {
        modal.addEventListener('shown.bs.modal', function() {
            initPasswordToggles();
            console.log('Toggles reinicializados al abrir modal');
        });
    }
});