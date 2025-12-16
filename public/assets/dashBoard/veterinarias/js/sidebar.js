// ========== VARIABLES GLOBALES ==========
let sidebarColapsado = false;
const sidebar = document.getElementById('barraLateralIzquierda');
const overlay = document.getElementById('sidebarOverlay');
const iconoToggle = document.getElementById('iconoToggleFlotante');

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', function() {
    // Configurar tooltips para items del sidebar
    configurarTooltips();
    
    // Marcar el item activo según la URL actual
    marcarItemActivo();
    
    // Cargar estado guardado del sidebar
    cargarEstadoSidebar();
    
    // Agregar listeners para cierre con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) {
            cerrarSidebarMovil();
        }
    });
});

// ========== FUNCIÓN PRINCIPAL TOGGLE ==========
function alternarBarraIzquierda() {
    sidebarColapsado = !sidebarColapsado;
    sidebar.classList.toggle('collapsed');
    
    // Guardar estado en localStorage
    localStorage.setItem('sidebarColapsado', sidebarColapsado);
    
    // Animar icono
    animarIconoToggle();
    
    // Emitir evento personalizado para que otros componentes reaccionen
    window.dispatchEvent(new CustomEvent('sidebarToggled', { 
        detail: { colapsado: sidebarColapsado } 
    }));
}

// ========== ANIMACIÓN ICONO TOGGLE ==========
function animarIconoToggle() {
    iconoToggle.style.transform = 'rotate(180deg)';
    setTimeout(() => {
        iconoToggle.style.transform = 'rotate(0deg)';
    }, 300);
}

// ========== FUNCIONES MÓVIL ==========
function abrirSidebarMovil() {
    if (window.innerWidth <= 768) {
        sidebar.classList.add('mobile-open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function cerrarSidebarMovil() {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
}

// ========== CONFIGURAR TOOLTIPS ==========
function configurarTooltips() {
    const items = document.querySelectorAll('.item-sidebar');
    
    items.forEach(item => {
        const textoItem = item.querySelector('.item-texto');
        if (textoItem) {
            item.setAttribute('data-tooltip', textoItem.textContent.trim());
        }
    });
}

// ========== MARCAR ITEM ACTIVO ==========
function marcarItemActivo() {
    const urlActual = window.location.pathname;
    const items = document.querySelectorAll('.item-sidebar');
    
    items.forEach(item => {
        const href = item.getAttribute('href');
        
        // Remover clase active de todos
        item.classList.remove('active');
        
        // Comparar URLs
        if (href && urlActual.includes(href)) {
            item.classList.add('active');
            
            // Scroll suave al item activo
            setTimeout(() => {
                item.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'nearest' 
                });
            }, 100);
        }
    });
}

// ========== CARGAR ESTADO GUARDADO ==========
function cargarEstadoSidebar() {
    const estadoGuardado = localStorage.getItem('sidebarColapsado');
    
    if (estadoGuardado === 'true') {
        sidebarColapsado = true;
        sidebar.classList.add('collapsed');
    }
}

// ========== EFECTOS HOVER MEJORADOS ==========
document.querySelectorAll('.item-sidebar').forEach(item => {
    item.addEventListener('mouseenter', function() {
        this.style.transition = 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)';
    });
    
    item.addEventListener('mouseleave', function() {
        this.style.transition = 'all 0.2s ease';
    });
});

// ========== RESPONSIVE - AJUSTE AUTOMÁTICO ==========
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    
    resizeTimer = setTimeout(function() {
        if (window.innerWidth > 768) {
            // Desktop: cerrar móvil si está abierto
            cerrarSidebarMovil();
        } else {
            // Móvil: remover collapsed si existe
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                sidebarColapsado = false;
            }
        }
    }, 250);
});

// ========== FUNCIONES DE UTILIDAD ==========

// Función para agregar badge/notificación a un item
function agregarBadge(itemSelector, numero) {
    const item = document.querySelector(itemSelector);
    if (!item) return;
    
    // Remover badge existente
    const badgeExistente = item.querySelector('.item-badge');
    if (badgeExistente) {
        badgeExistente.remove();
    }
    
    // Crear nuevo badge
    const badge = document.createElement('span');
    badge.className = 'item-badge';
    badge.textContent = numero > 99 ? '99+' : numero;
    badge.style.cssText = `
        position: absolute;
        top: 8px;
        right: 12px;
        background: #ef4444;
        color: white;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
    `;
    
    item.style.position = 'relative';
    item.appendChild(badge);
}

// Función para remover badge
function removerBadge(itemSelector) {
    const item = document.querySelector(itemSelector);
    if (!item) return;
    
    const badge = item.querySelector('.item-badge');
    if (badge) {
        badge.style.opacity = '0';
        badge.style.transform = 'scale(0)';
        setTimeout(() => badge.remove(), 200);
    }
}

// Función para deshabilitar temporalmente un item
function deshabilitarItem(itemSelector, mensaje = 'Próximamente') {
    const item = document.querySelector(itemSelector);
    if (!item) return;
    
    item.style.opacity = '0.5';
    item.style.cursor = 'not-allowed';
    item.setAttribute('data-tooltip', mensaje);
    
    item.addEventListener('click', function(e) {
        e.preventDefault();
    });
}

// ========== EXPORTAR FUNCIONES PARA USO GLOBAL ==========
window.sidebarFunctions = {
    alternarBarraIzquierda,
    abrirSidebarMovil,
    cerrarSidebarMovil,
    agregarBadge,
    removerBadge,
    deshabilitarItem
};