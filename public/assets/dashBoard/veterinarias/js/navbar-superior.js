// ========== FUNCIONALIDAD DEL NAVBAR SUPERIOR ==========

(function() {
    'use strict';
    
    // ========== ELEMENTOS DEL DOM ==========
    const elements = {
        searchInput: document.getElementById('searchInput'),
        btnClearSearch: document.getElementById('btnClearSearch'),
        searchResults: document.getElementById('searchResults'),
        searchItems: document.getElementById('searchItems'),
        notificationsPanel: document.getElementById('notificationsPanel'),
        perfilDropdown: document.getElementById('perfilDropdown'),
        themeIcon: document.getElementById('themeIcon')
    };
    
    // ========== INICIALIZACIÓN ==========
    document.addEventListener('DOMContentLoaded', function() {
        initializeSearch();
        initializeTheme();
        setupClickOutside();
        updateThemeIcon();
    });
    
    // ========== BÚSQUEDA ========== 
    function initializeSearch() {
        if (!elements.searchInput) return;
        
        elements.searchInput.addEventListener('input', handleSearchInput);
        elements.searchInput.addEventListener('focus', handleSearchFocus);
        
        if (elements.btnClearSearch) {
            elements.btnClearSearch.addEventListener('click', clearSearch);
        }
    }
    
    function handleSearchInput(e) {
        const query = e.target.value.trim();
        
        if (query.length > 0) {
            elements.btnClearSearch.style.display = 'flex';
            performSearch(query);
        } else {
            elements.btnClearSearch.style.display = 'none';
            elements.searchResults.style.display = 'none';
        }
    }
    
    function handleSearchFocus() {
        const query = elements.searchInput.value.trim();
        if (query.length > 0) {
            elements.searchResults.style.display = 'block';
        }
    }
    
    function performSearch(query) {
        // Datos de ejemplo - reemplazar con búsqueda real
        const resultados = [
            {
                icon: 'bi-heart-fill',
                title: 'Luna',
                description: 'Paciente - Golden Retriever',
                link: '#'
            },
            {
                icon: 'bi-calendar-check',
                title: 'Cita con Max',
                description: 'Hoy 15:00 - Consulta general',
                link: '#'
            },
            {
                icon: 'bi-file-medical',
                title: 'Historia clínica',
                description: 'Rocky - Última actualización',
                link: '#'
            }
        ];
        
        displaySearchResults(resultados);
    }
    
    function displaySearchResults(resultados) {
        if (!elements.searchItems) return;
        
        elements.searchItems.innerHTML = '';
        
        if (resultados.length === 0) {
            elements.searchItems.innerHTML = `
                <div style="padding: 20px; text-align: center; color: var(--text-muted);">
                    <i class="bi bi-search" style="font-size: 32px; margin-bottom: 8px;"></i>
                    <p>No se encontraron resultados</p>
                </div>
            `;
        } else {
            resultados.forEach(resultado => {
                const item = document.createElement('div');
                item.className = 'search-item';
                item.innerHTML = `
                    <div class="search-item-icon">
                        <i class="bi ${resultado.icon}"></i>
                    </div>
                    <div class="search-item-content">
                        <h5>${resultado.title}</h5>
                        <p>${resultado.description}</p>
                    </div>
                `;
                item.addEventListener('click', () => {
                    window.location.href = resultado.link;
                });
                elements.searchItems.appendChild(item);
            });
        }
        
        elements.searchResults.style.display = 'block';
    }
    
    function clearSearch() {
        elements.searchInput.value = '';
        elements.btnClearSearch.style.display = 'none';
        elements.searchResults.style.display = 'none';
        elements.searchInput.focus();
    }
    
    // ========== TOGGLE TEMA ========== 
    window.toggleTheme = function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        if (newTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
        }
        
        updateThemeIcon();
        
        // Disparar evento personalizado
        window.dispatchEvent(new CustomEvent('themeChange', {
            detail: { theme: newTheme }
        }));
    };
    
    function initializeTheme() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
        updateThemeIcon();
    }
    
    function updateThemeIcon() {
        if (!elements.themeIcon) return;
        
        const currentTheme = document.documentElement.getAttribute('data-theme');
        if (currentTheme === 'dark') {
            elements.themeIcon.className = 'bi bi-sun-fill';
        } else {
            elements.themeIcon.className = 'bi bi-moon-stars-fill';
        }
    }
    
    // ========== TOGGLE NOTIFICACIONES ========== 
    window.toggleNotificaciones = function() {
        if (!elements.notificationsPanel) return;
        
        const isVisible = elements.notificationsPanel.style.display === 'block';
        
        // Cerrar otros paneles
        if (elements.perfilDropdown) {
            elements.perfilDropdown.style.display = 'none';
        }
        
        elements.notificationsPanel.style.display = isVisible ? 'none' : 'block';
    };
    
    // ========== TOGGLE PERFIL ========== 
    window.togglePerfilMenu = function() {
        if (!elements.perfilDropdown) return;
        
        const isVisible = elements.perfilDropdown.style.display === 'block';
        
        // Cerrar otros paneles
        if (elements.notificationsPanel) {
            elements.notificationsPanel.style.display = 'none';
        }
        
        elements.perfilDropdown.style.display = isVisible ? 'none' : 'block';
    };
    
    // ========== MARCAR TODAS COMO LEÍDAS ========== 
    document.querySelector('.btn-mark-read')?.addEventListener('click', function() {
        const unreadItems = document.querySelectorAll('.notification-item.unread');
        unreadItems.forEach(item => {
            item.classList.remove('unread');
        });
        
        // Actualizar badge
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.style.display = 'none';
        }
        
        console.log('Todas las notificaciones marcadas como leídas');
    });
    
    // ========== CERRAR AL HACER CLICK FUERA ========== 
    function setupClickOutside() {
        document.addEventListener('click', function(e) {
            // Cerrar búsqueda
            if (elements.searchResults && 
                !elements.searchResults.contains(e.target) && 
                !elements.searchInput.contains(e.target)) {
                elements.searchResults.style.display = 'none';
            }
            
            // Cerrar notificaciones
            if (elements.notificationsPanel && 
                !elements.notificationsPanel.contains(e.target) && 
                !e.target.closest('.btn-action')) {
                elements.notificationsPanel.style.display = 'none';
            }
            
            // Cerrar perfil
            if (elements.perfilDropdown && 
                !elements.perfilDropdown.contains(e.target) && 
                !e.target.closest('.btn-perfil')) {
                elements.perfilDropdown.style.display = 'none';
            }
        });
        
        // Cerrar con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (elements.searchResults) elements.searchResults.style.display = 'none';
                if (elements.notificationsPanel) elements.notificationsPanel.style.display = 'none';
                if (elements.perfilDropdown) elements.perfilDropdown.style.display = 'none';
            }
        });
    }
    
    // ========== ABRIR SIDEBAR EN MÓVIL ========== 
    window.abrirSidebarMovil = function() {
        const sidebar = document.getElementById('barraLateralIzquierda');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (sidebar) {
            sidebar.classList.add('mobile-open');
        }
        if (overlay) {
            overlay.classList.add('active');
        }
        
        document.body.style.overflow = 'hidden';
    };
    
    // ========== ACTUALIZAR BREADCRUMB ========== 
    function updateBreadcrumb() {
        const paginaActual = document.getElementById('paginaActual');
        if (!paginaActual) return;
        
        // Obtener de la URL o título de la página
        const path = window.location.pathname;
        const segments = path.split('/').filter(Boolean);
        
        if (segments.length > 0) {
            const ultimoSegmento = segments[segments.length - 1];
            const nombrePagina = ultimoSegmento
                .replace(/-/g, ' ')
                .replace(/_/g, ' ')
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
            
            paginaActual.textContent = nombrePagina || 'Dashboard';
        }
    }
    
    // Actualizar breadcrumb al cargar
    updateBreadcrumb();
    
    // ========== ANIMACIÓN DE SCROLL ========== 
    let lastScroll = 0;
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar-superior');
        if (!navbar) return;
        
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
    
    // ========== FUNCIONES DE UTILIDAD ========== 
    
    // Agregar notificación programáticamente
    window.agregarNotificacion = function(tipo, titulo, texto) {
        const panelBody = document.querySelector('.notifications-panel .panel-body');
        if (!panelBody) return;
        
        const iconos = {
            success: { icon: 'bi-check-circle', bg: 'bg-success' },
            warning: { icon: 'bi-exclamation-triangle', bg: 'bg-warning' },
            info: { icon: 'bi-info-circle', bg: 'bg-info' }
        };
        
        const config = iconos[tipo] || iconos.info;
        
        const item = document.createElement('div');
        item.className = 'notification-item unread';
        item.innerHTML = `
            <div class="notification-icon ${config.bg}">
                <i class="bi ${config.icon}"></i>
            </div>
            <div class="notification-content">
                <p class="notification-title">${titulo}</p>
                <p class="notification-text">${texto}</p>
                <span class="notification-time">Ahora</span>
            </div>
        `;
        
        panelBody.insertBefore(item, panelBody.firstChild);
        
        // Actualizar badge
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            const count = parseInt(badge.textContent) || 0;
            badge.textContent = count + 1;
            badge.style.display = 'flex';
        }
    };
    
    // Ejemplo de uso:
    // agregarNotificacion('success', 'Nueva cita', 'Max - Consulta general');
    
})();

// ========== EXPORTAR FUNCIONES GLOBALES ========== 
window.NavbarSuperior = {
    toggleTheme: window.toggleTheme,
    toggleNotificaciones: window.toggleNotificaciones,
    togglePerfilMenu: window.togglePerfilMenu,
    abrirSidebarMovil: window.abrirSidebarMovil,
    agregarNotificacion: window.agregarNotificacion
};