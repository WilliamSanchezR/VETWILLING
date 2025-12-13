// ========================================
// CONFIGURACIÓN DE RUTAS Y BREADCRUMBS
// ========================================

const rutasBreadcrumb = {
    '/veterinaria/dashboard': {
        titulo: 'Dashboard',
        icono: 'bi-speedometer2'
    },
    '/veterinaria/seguimientos': {
        titulo: 'Seguimientos',
        icono: 'bi-arrow-repeat'
    },
    '/veterinaria/calendario': {
        titulo: 'Calendario',
        icono: 'bi-calendar3'
    },
    '/veterinario/consultar-veterinario': {
        titulo: 'Citas',
        icono: 'bi-calendar-check'
    },
    '/veterinaria/laboratorio': {
        titulo: 'Laboratorio',
        icono: 'bi-flask'
    },
    '/veterinario/registrar-veterinario': {
        titulo: 'Registro',
        icono: 'bi-person-plus'
    },
    '/veterinaria/gestion_clinica': {
        titulo: 'Gestión Clínica',
        icono: 'bi-hospital'
    },
    '/veterinaria/reportes': {
        titulo: 'Reportes',
        icono: 'bi-file-earmark-text'
    },
    '/veterinaria/recetas': {
        titulo: 'Recetas',
        icono: 'bi-receipt'
    },
    '/veterinario/consultar-perfil': {
        titulo: 'Mi Perfil',
        icono: 'bi-person-circle'
    },
    '/veterinario/configuracion': {
        titulo: 'Configuración',
        icono: 'bi-gear'
    },
    '/veterinario/ayuda': {
        titulo: 'Ayuda',
        icono: 'bi-question-circle'
    }
};

// ========================================
// ESTADO DE LA APLICACIÓN
// ========================================

let estadoUI = {
    sidebarColapsado: false,
    panelDerechoAbierto: false,
    menuPerfilAbierto: false,
    notificacionesAbiertas: false
};

// ========================================
// INICIALIZACIÓN
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    inicializarNavegacion();
    restaurarEstadoSidebar();
    configurarEventosNavegacion();
    actualizarBreadcrumbActual();
});

// ========================================
// FUNCIONES DE INICIALIZACIÓN
// ========================================

function inicializarNavegacion() {
    const itemsSidebar = document.querySelectorAll('.item-sidebar:not(.item-logout)');
    
    itemsSidebar.forEach(item => {
        item.addEventListener('click', function(e) {
            // Remover active de todos
            itemsSidebar.forEach(i => i.classList.remove('active'));
            
            // Agregar active al clickeado
            this.classList.add('active');
            
            // Actualizar breadcrumb
            const href = this.getAttribute('href');
            if (href) {
                actualizarBreadcrumb(href);
            }
        });
    });
}

function restaurarEstadoSidebar() {
    const sidebarColapsado = localStorage.getItem('sidebarColapsado') === 'true';
    const sidebar = document.getElementById('barraLateralIzquierda');
    const navbar = document.getElementById('navbarSuperior');
    
    if (sidebarColapsado) {
        sidebar.classList.add('colapsado');
        navbar.classList.add('sidebar-colapsado');
        estadoUI.sidebarColapsado = true;
        actualizarIconoColapsar();
    }
}

function configurarEventosNavegacion() {
    // Cerrar dropdowns al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.user-profile-container')) {
            cerrarPerfilMenu();
        }
        if (!e.target.closest('.action-item[data-tooltip="Notificaciones"]')) {
            cerrarNotificaciones();
        }
        if (!e.target.closest('.buscador-navegacion')) {
            cerrarBuscador();
        }
    });
    
    // Prevenir que los clicks dentro de los dropdowns los cierren
    document.querySelectorAll('.perfil-dropdown, .notifications-panel').forEach(el => {
        el.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
}

// ========================================
// FUNCIONES DE BREADCRUMB
// ========================================

function actualizarBreadcrumbActual() {
    const rutaActual = window.location.pathname;
    actualizarBreadcrumb(rutaActual);
}

function actualizarBreadcrumb(ruta) {
    const paginaActual = document.getElementById('paginaActual');
    const breadcrumbItem = paginaActual.closest('.breadcrumb-item');
    
    if (!paginaActual) return;
    
    // Buscar coincidencia exacta o parcial
    let infoRuta = rutasBreadcrumb[ruta];
    
    if (!infoRuta) {
        // Buscar coincidencia parcial
        for (let key in rutasBreadcrumb) {
            if (ruta.includes(key)) {
                infoRuta = rutasBreadcrumb[key];
                break;
            }
        }
    }
    
    if (infoRuta) {
        // Actualizar el texto
        paginaActual.textContent = infoRuta.titulo;
        
        // Actualizar título de la página
        document.title = `${infoRuta.titulo} | VetCare`;
        
        // Animación suave
        breadcrumbItem.style.opacity = '0';
        setTimeout(() => {
            breadcrumbItem.style.opacity = '1';
        }, 150);
    }
}

// ========================================
// FUNCIONES DE SIDEBAR
// ========================================

function alternarBarraIzquierda() {
    const sidebar = document.getElementById('barraLateralIzquierda');
    const navbar = document.getElementById('navbarSuperior');
    const contenidoPrincipal = document.querySelector('.contenido-principal');
    
    estadoUI.sidebarColapsado = !estadoUI.sidebarColapsado;
    
    sidebar.classList.toggle('colapsado');
    navbar.classList.toggle('sidebar-colapsado');
    
    if (contenidoPrincipal) {
        contenidoPrincipal.classList.toggle('sidebar-colapsado');
    }
    
    // Guardar estado
    localStorage.setItem('sidebarColapsado', estadoUI.sidebarColapsado);
    
    // Actualizar icono
    actualizarIconoColapsar();
    
    // Cerrar menú de perfil y notificaciones si están abiertos
    if (estadoUI.sidebarColapsado) {
        cerrarPerfilMenu();
        cerrarNotificaciones();
    }
}

function actualizarIconoColapsar() {
    const icono = document.getElementById('iconoColapsar');
    if (icono) {
        icono.className = estadoUI.sidebarColapsado ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
    }
}

// ========================================
// FUNCIONES DE SIDEBAR MÓVIL
// ========================================

function abrirSidebarMovil() {
    const sidebar = document.getElementById('barraLateralIzquierda');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.add('abierto');
    overlay.classList.add('activo');
    document.body.style.overflow = 'hidden';
}

function cerrarSidebarMovil() {
    const sidebar = document.getElementById('barraLateralIzquierda');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.remove('abierto');
    overlay.classList.remove('activo');
    document.body.style.overflow = '';
}

// ========================================
// FUNCIONES DE MENÚ DE PERFIL
// ========================================

function togglePerfilMenu() {
    const dropdown = document.getElementById('perfilDropdown');
    const boton = document.querySelector('.btn-perfil');
    
    estadoUI.menuPerfilAbierto = !estadoUI.menuPerfilAbierto;
    
    if (estadoUI.menuPerfilAbierto) {
        dropdown.style.display = 'block';
        boton.classList.add('activo');
        cerrarNotificaciones();
        
        // Animación de entrada
        setTimeout(() => {
            dropdown.classList.add('mostrar');
        }, 10);
    } else {
        cerrarPerfilMenu();
    }
}

function cerrarPerfilMenu() {
    const dropdown = document.getElementById('perfilDropdown');
    const boton = document.querySelector('.btn-perfil');
    
    if (dropdown && estadoUI.menuPerfilAbierto) {
        dropdown.classList.remove('mostrar');
        boton.classList.remove('activo');
        
        setTimeout(() => {
            dropdown.style.display = 'none';
        }, 300);
        
        estadoUI.menuPerfilAbierto = false;
    }
}

// ========================================
// FUNCIONES DE NOTIFICACIONES
// ========================================

function toggleNotificaciones() {
    const panel = document.getElementById('notificationsPanel');
    
    estadoUI.notificacionesAbiertas = !estadoUI.notificacionesAbiertas;
    
    if (estadoUI.notificacionesAbiertas) {
        panel.style.display = 'block';
        cerrarPerfilMenu();
        
        // Animación de entrada
        setTimeout(() => {
            panel.classList.add('mostrar');
        }, 10);
    } else {
        cerrarNotificaciones();
    }
}

function cerrarNotificaciones() {
    const panel = document.getElementById('notificationsPanel');
    
    if (panel && estadoUI.notificacionesAbiertas) {
        panel.classList.remove('mostrar');
        
        setTimeout(() => {
            panel.style.display = 'none';
        }, 300);
        
        estadoUI.notificacionesAbiertas = false;
    }
}

// ========================================
// FUNCIONES DE PANEL DERECHO
// ========================================

function alternarBarraDerecha() {
    const panelDerecho = document.getElementById('panelLateralDerecho');
    
    if (panelDerecho) {
        estadoUI.panelDerechoAbierto = !estadoUI.panelDerechoAbierto;
        panelDerecho.classList.toggle('abierto');
    }
}

// ========================================
// FUNCIONES DE BÚSQUEDA
// ========================================

const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
const btnClearSearch = document.getElementById('btnClearSearch');

if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        if (query.length > 0) {
            btnClearSearch.style.display = 'block';
            mostrarResultadosBusqueda(query);
        } else {
            btnClearSearch.style.display = 'none';
            cerrarBuscador();
        }
    });
    
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            searchResults.style.display = 'block';
        }
    });
}

if (btnClearSearch) {
    btnClearSearch.addEventListener('click', function() {
        searchInput.value = '';
        this.style.display = 'none';
        cerrarBuscador();
        searchInput.focus();
    });
}

function mostrarResultadosBusqueda(query) {
    const searchItems = document.getElementById('searchItems');
    searchResults.style.display = 'block';
    
    // Aquí puedes agregar lógica real de búsqueda
    searchItems.innerHTML = `
        <div class="search-item">
            <i class="bi bi-search"></i>
            <span>Buscando: "${query}"</span>
        </div>
        <div class="search-item">
            <i class="bi bi-clock-history"></i>
            <span>No hay resultados recientes</span>
        </div>
    `;
}

function cerrarBuscador() {
    if (searchResults) {
        searchResults.style.display = 'none';
    }
}

// ========================================
// FUNCIONES DE TEMA
// ========================================

function toggleTheme() {
    const body = document.body;
    const themeIcon = document.getElementById('themeIcon');
    const isDark = body.classList.toggle('dark-theme');
    
    // Actualizar icono
    if (themeIcon) {
        themeIcon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
    }
    
    // Guardar preferencia
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

// Restaurar tema al cargar
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    const themeIcon = document.getElementById('themeIcon');
    
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        if (themeIcon) {
            themeIcon.className = 'bi bi-sun-fill';
        }
    }
});

// ========================================
// RESPONSIVE - MANEJO DE VENTANA
// ========================================

let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        // Si es móvil y el sidebar está abierto, cerrarlo
        if (window.innerWidth <= 768) {
            cerrarSidebarMovil();
        }
    }, 250);
});

// ========================================
// SCROLL NAVBAR
// ========================================

let lastScroll = 0;
window.addEventListener('scroll', function() {
    const navbar = document.getElementById('navbarSuperior');
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > lastScroll && currentScroll > 100) {
        // Scroll hacia abajo
        navbar.style.transform = 'translateY(-100%)';
    } else {
        // Scroll hacia arriba
        navbar.style.transform = 'translateY(0)';
    }
    
    lastScroll = currentScroll;
});