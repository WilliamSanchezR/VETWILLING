// ========== MANEJADOR CENTRAL DE ESTILOS Y CONFLICTOS ==========

(function() {
    'use strict';
    
    // ========== CONFIGURACIÓN INICIAL ==========
    const MasterHandler = {
        // Estado del tema
        currentTheme: localStorage.getItem('theme') || 'light',
        
        // Estado del sidebar
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        
        // Inicializar todo
        init() {
            console.log('🚀 Master Handler iniciando...');
            this.setupTheme();
            this.setupSidebarSync();
            this.fixConflicts();
            this.setupDarkModeToggle();
            console.log('✅ Master Handler listo');
        },
        
        // ========== TEMA OSCURO/CLARO ==========
        setupTheme() {
            // Aplicar tema guardado
            if (this.currentTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
            
            // Escuchar cambios de tema
            window.addEventListener('themeChange', (e) => {
                this.currentTheme = e.detail.theme;
                this.applyTheme(this.currentTheme);
            });
        },
        
        applyTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
            }
            
            // Forzar recálculo de estilos
            this.forceStyleRecalculation();
        },
        
        // ========== TOGGLE DE MODO OSCURO ==========
        setupDarkModeToggle() {
            // Si existe un botón de tema en el navbar
            const themeToggle = document.getElementById('themeToggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const newTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
                    this.applyTheme(newTheme);
                });
            }
        },
        
        // ========== SINCRONIZACIÓN CON SIDEBAR ==========
        setupSidebarSync() {
            // Escuchar cambios del sidebar
            window.addEventListener('sidebarToggled', (e) => {
                this.sidebarCollapsed = e.detail.colapsado;
                this.adjustContentMargin();
            });
            
            // Aplicar estado inicial
            this.adjustContentMargin();
        },
        
        adjustContentMargin() {
            const content = document.querySelector('.area-contenido');
            const navbar = document.querySelector('.barra-navegacion-superior');
            
            if (!content) return;
            
            const margin = this.sidebarCollapsed ? '80px' : '260px';
            
            if (window.innerWidth > 768) {
                content.style.marginLeft = margin;
                if (navbar) {
                    navbar.style.left = margin;
                }
            } else {
                content.style.marginLeft = '0';
                if (navbar) {
                    navbar.style.left = '0';
                }
            }
        },
        
        // ========== CORRECCIÓN DE CONFLICTOS ==========
        fixConflicts() {
            // Corregir colores forzados en elementos específicos
            this.fixTableColors();
            this.fixCardColors();
            this.fixFormColors();
            this.fixTextColors();
        },
        
        fixTableColors() {
            // Esperar a que DataTables se inicialice
            setTimeout(() => {
                const tables = document.querySelectorAll('.tabla-pacientes, .tabla-laboratorio, #tablaCitas');
                tables.forEach(table => {
                    // Quitar estilos inline que puedan causar conflicto
                    const cells = table.querySelectorAll('th, td');
                    cells.forEach(cell => {
                        if (cell.style.backgroundColor && 
                            !cell.classList.contains('badge-especie') &&
                            !cell.classList.contains('badge-estado')) {
                            cell.style.backgroundColor = '';
                        }
                    });
                });
            }, 500);
        },
        
        fixCardColors() {
            const cards = document.querySelectorAll('.tarjeta-estadistica, .card-personal, .card-sala');
            cards.forEach(card => {
                // Asegurar que usen las variables CSS
                if (card.style.background) {
                    card.style.background = '';
                }
                if (card.style.backgroundColor) {
                    card.style.backgroundColor = '';
                }
            });
        },
        
        fixFormColors() {
            const inputs = document.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                // Quitar estilos inline de fondo
                if (input.style.backgroundColor && 
                    !input.classList.contains('custom-bg')) {
                    input.style.backgroundColor = '';
                }
            });
        },
        
        fixTextColors() {
            // Corregir textos que tengan color inline sin necesidad
            const texts = document.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span');
            texts.forEach(text => {
                if (text.style.color && 
                    !text.classList.contains('custom-color') &&
                    !text.closest('.badge-estado') &&
                    !text.closest('.badge-especie')) {
                    text.style.color = '';
                }
            });
        },
        
        // ========== FORZAR RECÁLCULO DE ESTILOS ==========
        forceStyleRecalculation() {
            // Trucos para forzar el navegador a recalcular estilos
            document.body.style.display = 'none';
            document.body.offsetHeight; // Trigger reflow
            document.body.style.display = '';
            
            // Disparar evento de resize
            window.dispatchEvent(new Event('resize'));
        },
        
        // ========== RESPONSIVE ==========
        setupResponsive() {
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    this.adjustContentMargin();
                    this.fixConflicts();
                }, 250);
            });
        }
    };
    
    // ========== AUTO-INICIALIZAR ==========
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            MasterHandler.init();
            MasterHandler.setupResponsive();
        });
    } else {
        MasterHandler.init();
        MasterHandler.setupResponsive();
    }
    
    // ========== EXPONER GLOBALMENTE ==========
    window.MasterHandler = MasterHandler;
    
    // ========== FUNCIONES HELPER GLOBALES ==========
    window.toggleTheme = function() {
        const newTheme = MasterHandler.currentTheme === 'dark' ? 'light' : 'dark';
        MasterHandler.applyTheme(newTheme);
    };
    
    window.getCurrentTheme = function() {
        return MasterHandler.currentTheme;
    };
    
    // ========== OBSERVADOR DE MUTACIONES (OPCIONAL) ==========
    // Descomenta si hay elementos que se agregan dinámicamente
    /*
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length) {
                MasterHandler.fixConflicts();
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    */
    
})();

// ========== UTILIDADES ADICIONALES ==========

// Función para depurar conflictos
window.debugStyles = function(selector) {
    const elements = document.querySelectorAll(selector);
    console.log(`🔍 Depurando: ${selector}`);
    elements.forEach((el, index) => {
        const computed = window.getComputedStyle(el);
        console.log(`Elemento ${index}:`, {
            backgroundColor: computed.backgroundColor,
            color: computed.color,
            border: computed.border,
            inlineStyles: el.style.cssText
        });
    });
};

// Función para limpiar estilos inline
window.cleanInlineStyles = function(selector, properties = []) {
    const elements = document.querySelectorAll(selector);
    let cleaned = 0;
    
    elements.forEach(el => {
        if (properties.length === 0) {
            // Limpiar todos
            el.removeAttribute('style');
            cleaned++;
        } else {
            // Limpiar solo propiedades específicas
            properties.forEach(prop => {
                if (el.style[prop]) {
                    el.style[prop] = '';
                    cleaned++;
                }
            });
        }
    });
    
    console.log(`🧹 Limpiados ${cleaned} estilos de "${selector}"`);
};

// Función para verificar tema actual
window.checkTheme = function() {
    console.log('📊 Estado del tema:', {
        current: window.getCurrentTheme(),
        dataAttribute: document.documentElement.getAttribute('data-theme'),
        localStorage: localStorage.getItem('theme')
    });
};