/**
 * ═══════════════════════════════════════════════════════════════════
 *  Dashboard Seguimientos - JavaScript con Integración API
 *  Funcionalidad completa para gestión de seguimientos
 * ═══════════════════════════════════════════════════════════════════
 */

(function() {
    'use strict';

    // ============================================
    // CONFIGURACIÓN
    // ============================================
    const BASE_URL = window.location.origin + '/vetwilling';
    const API_URL = BASE_URL + '/veterinaria/api/seguimientos';
    
    let currentView = 'list';
    let searchTimeout = null;
    let seguimientosData = [];

    // ============================================
    // ELEMENTOS DEL DOM
    // ============================================
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearch');
    const listaSeguimientos = document.getElementById('listaSeguimientos');
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const viewListBtn = document.getElementById('viewList');
    const viewGridBtn = document.getElementById('viewGrid');
    const toastContainer = document.getElementById('toastContainer');

    // Elementos de estadísticas
    const statActivos = document.getElementById('statActivos');
    const statCasosCriticos = document.getElementById('statCriticos');
    const statProximasCitas = document.getElementById('statPendientes');
    const statCompletados = document.getElementById('statCompletados');


    // ============================================
    // API - CARGA DE DATOS
    // ============================================
    async function cargarSeguimientos() {
        try {
            console.log('🔄 Iniciando carga de seguimientos...');
            console.log('📡 URL del API:', `${API_URL}?action=listar`);
            showLoading(true);
            
            const response = await fetch(`${API_URL}?action=listar`);
            console.log('📨 Respuesta recibida:', response.status, response.statusText);
            
            const data = await response.json();
            console.log('📦 Datos parseados:', data);

            if (data.status === 'success') {
                seguimientosData = data.data;
                console.log(`✅ ${seguimientosData.length} seguimientos cargados`);
                renderizarSeguimientos(seguimientosData);
                cargarEstadisticas();
                updateLastUpdateTime();
                showToast('Seguimientos cargados correctamente', 'success', 2000);
            } else {
                console.error('❌ Error en respuesta:', data.message);
                throw new Error(data.message || 'Error al cargar seguimientos');
            }
        } catch (error) {
            console.error('💥 Error en cargarSeguimientos:', error);
            showToast('Error al cargar seguimientos: ' + error.message, 'error');
            toggleEmptyState(true);
        } finally {
            showLoading(false);
        }
    }

    async function cargarEstadisticas() {
        try {
            console.log('📊 Cargando estadísticas...');
            const response = await fetch(`${API_URL}?action=estadisticas`);
            const data = await response.json();
            console.log('📈 Estadísticas recibidas:', data);

            if (data.status === 'success') {
                const stats = data.data;
                if (statActivos) statActivos.textContent = stats.total_activos || 0;
                if (statCasosCriticos) statCasosCriticos.textContent = stats.criticos || 0;
                if (statProximasCitas) statProximasCitas.textContent = stats.requieren_atencion || 0;
                if (statCompletados) statCompletados.textContent = stats.revisiones_hoy || 0;
                console.log('✅ Estadísticas actualizadas');
            }
        } catch (error) {
            console.error('❌ Error cargando estadísticas:', error);
        }
    }


    // ============================================
    // RENDERIZADO DE SEGUIMIENTOS
    // ============================================
    function renderizarSeguimientos(seguimientos) {
        console.log('🎨 Iniciando renderizado de seguimientos:', seguimientos);
        if (!listaSeguimientos) {
            console.error('❌ Elemento listaSeguimientos no encontrado');
            return;
        }

        listaSeguimientos.innerHTML = '';

        if (!seguimientos || seguimientos.length === 0) {
            console.warn('⚠️ No hay seguimientos para mostrar');
            toggleEmptyState(true);
            return;
        }

        toggleEmptyState(false);

        seguimientos.forEach(seg => {
            console.log('📝 Creando tarjeta para seguimiento:', seg.id_seguimiento);
            const card = crearCardSeguimiento(seg);
            listaSeguimientos.appendChild(card);
        });
    }

    function crearCardSeguimiento(seg) {
        const article = document.createElement('article');
        const prioridad = seg.prioridad_calculada || seg.prioridad || 'normal';
        article.className = `card-seguimiento ${prioridad}`;
        article.dataset.prioridad = prioridad;
        article.dataset.estado = seg.estado_seguimiento || seg.estado || 'activo';
        article.dataset.paciente = seg.paciente_nombre || '';
        article.dataset.idSeguimiento = seg.id_seguimiento;
        article.setAttribute('role', 'listitem');

        const badgePrioridad = obtenerBadgePrioridad(prioridad);
        const badgeEstado = obtenerBadgeEstado(seg.estado_seguimiento);
        const ultimaCitaTexto = seg.ultima_cita ? formatearFecha(seg.ultima_cita) : 'Sin citas';
        const proximaCitaTexto = seg.proxima_cita ? formatearFecha(seg.proxima_cita) : 'Sin programar';
        const avatarUrl = seg.img_mascota || `https://api.dicebear.com/7.x/bottts/svg?seed=${seg.paciente_nombre}`;
        const progreso = seg.progreso_porcentaje || seg.progreso || 0;

        article.innerHTML = `
            <div class="header-seguimiento">
                <div class="info-paciente-seg">
                    <img src="${avatarUrl}" alt="Foto de ${seg.paciente_nombre}" class="avatar-seg">
                    <div>
                        <h6>${seg.paciente_nombre}</h6>
                        <small class="text-muted">
                            ${seg.especie} - ${seg.raza} | 
                            ${seg.propietario_nombres} ${seg.propietario_apellidos}
                        </small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    ${badgePrioridad}
                    ${badgeEstado}
                    <div class="dropdown">
                        <button class="btn btn-quick-action" type="button" data-bs-toggle="dropdown" 
                                aria-expanded="false" aria-label="Acciones rápidas para ${seg.paciente_nombre}">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" data-action="ver" data-id="${seg.id_seguimiento}">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-action="editar" data-id="${seg.id_seguimiento}">
                                <i class="bi bi-pencil"></i> Editar
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-action="notificar" data-id="${seg.id_seguimiento}">
                                <i class="bi bi-bell"></i> Notificar Propietario
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" data-action="completar" data-id="${seg.id_seguimiento}">
                                <i class="bi bi-check-circle"></i> Marcar Completado
                            </a></li>
                        </ul>
                    </div>
                    <button class="btn btn-toggle-expand" type="button" aria-expanded="false" aria-label="Expandir seguimiento">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
            </div>

            <div class="compact-view">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="info-item">
                        <i class="bi bi-calendar-check text-success" aria-hidden="true"></i>
                        <strong>Última cita:</strong>
                        <time datetime="${seg.ultima_cita || ''}">${ultimaCitaTexto}</time>
                    </div>
                    <div class="info-item">
                        <i class="bi bi-calendar-event text-primary" aria-hidden="true"></i>
                        <strong>Próxima cita:</strong>
                        <time datetime="${seg.proxima_cita || ''}">${proximaCitaTexto}</time>
                    </div>
                </div>
                <div class="info-item mb-2">
                    <i class="bi bi-clipboard-pulse text-danger" aria-hidden="true"></i>
                    <strong>Diagnóstico:</strong>
                    <span>${seg.ultimo_diagnostico || 'Sin diagnóstico registrado'}</span>
                </div>
                <div class="progreso-seguimiento mt-2">
                    <div class="label-progreso">
                        <span>Progreso del tratamiento</span>
                        <span class="porcentaje-prog" role="status" aria-label="${progreso}% completado">${progreso}%</span>
                    </div>
                    <div class="progress" style="height: 8px;" role="progressbar" aria-valuenow="${progreso}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-success" style="width: ${progreso}%"></div>
                    </div>
                </div>
            </div>

            <div class="expanded-view" style="display: none;">
                <div class="body-seguimiento">
                    <div class="info-row">
                        <i class="bi bi-capsule text-success" aria-hidden="true"></i>
                        <strong>Tratamiento:</strong>
                        <span>${seg.tratamiento_actual || 'No especificado'}</span>
                    </div>
                    <div class="info-row">
                        <i class="bi bi-people text-secondary" aria-hidden="true"></i>
                        <strong>Propietario:</strong>
                        <span>${seg.propietario_nombres} ${seg.propietario_apellidos}</span>
                    </div>
                    <div class="info-row">
                        <i class="bi bi-telephone text-info" aria-hidden="true"></i>
                        <strong>Teléfono:</strong>
                        <span>${seg.propietario_telefono || 'No disponible'}</span>
                    </div>
                    <div class="info-row">
                        <i class="bi bi-file-medical text-warning" aria-hidden="true"></i>
                        <strong>Total de citas:</strong>
                        <span>${seg.total_citas_realizadas || 0}</span>
                    </div>
                    ${seg.observaciones_generales ? `
                    <div class="info-row">
                        <i class="bi bi-journal-text text-primary" aria-hidden="true"></i>
                        <strong>Observaciones:</strong>
                        <span>${seg.observaciones_generales}</span>
                    </div>
                    ` : ''}
                </div>

                <div class="footer-seguimiento">
                    <button class="btn-accion-seg btn-actualizar" onclick="location.href='${BASE_URL}/veterinaria/calendario'">
                        <i class="bi bi-arrow-clockwise"></i> Programar Cita
                    </button>
                    <button class="btn-accion-seg btn-detalles" data-action="ver" data-id="${seg.id_seguimiento}">
                        <i class="bi bi-eye"></i> Ver Detalles
                    </button>
                    <button class="btn-accion-seg btn-completar" data-action="completar" data-id="${seg.id_seguimiento}">
                        <i class="bi bi-check-circle"></i> Completar
                    </button>
                </div>
            </div>
        `;

        setupCardEventListeners(article);
        return article;
    }

    function setupCardEventListeners(card) {
        card.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', handleQuickAction);
        });

        const toggleBtn = card.querySelector('.btn-toggle-expand');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleExpand);
        }
    }

    function obtenerBadgePrioridad(prioridad) {
        const badges = {
            'critica': '<span class="badge-prioridad critico"><i class="bi bi-exclamation-triangle-fill"></i> Crítico</span>',
            'critico': '<span class="badge-prioridad critico"><i class="bi bi-exclamation-triangle-fill"></i> Crítico</span>',
            'alta': '<span class="badge-prioridad alta"><i class="bi bi-exclamation-circle-fill"></i> Alta</span>',
            'normal': '<span class="badge-prioridad normal"><i class="bi bi-circle-fill"></i> Normal</span>',
            'baja': '<span class="badge-prioridad baja"><i class="bi bi-dash-circle-fill"></i> Baja</span>'
        };
        return badges[prioridad] || badges.normal;
    }

    function obtenerBadgeEstado(estado) {
        const badges = {
            'en-tratamiento': '<span class="badge-tipo activa">En Tratamiento</span>',
            'programado': '<span class="badge-tipo programada">Programado</span>',
            'pendiente': '<span class="badge-tipo pendiente">Pendiente</span>'
        };
        return badges[estado] || badges.pendiente;
    }

    function formatearFecha(fecha) {
        if (!fecha) return 'No disponible';
        const date = new Date(fecha);
        const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return date.toLocaleDateString('es-ES', options);
    }


    // ============================================
    // BÚSQUEDA Y FILTROS
    // ============================================
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim().toLowerCase();

            if (clearSearchBtn) {
                clearSearchBtn.style.display = query ? 'flex' : 'none';
            }

            searchTimeout = setTimeout(() => {
                filterSeguimientos(query);
            }, 300);
        });

        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                clearSearchBtn.style.display = 'none';
                filterSeguimientos('');
                searchInput.focus();
            });
        }
    }

    function filterSeguimientos(query) {
        const cards = listaSeguimientos.querySelectorAll('.card-seguimiento');
        let visibleCount = 0;

        cards.forEach(card => {
            const matches = !query || 
                card.dataset.paciente?.toLowerCase().includes(query) || 
                card.dataset.prioridad?.toLowerCase().includes(query) || 
                card.dataset.estado?.toLowerCase().includes(query) ||
                card.textContent.toLowerCase().includes(query);

            card.style.display = matches ? '' : 'none';
            if (matches) visibleCount++;
        });

        toggleEmptyState(visibleCount === 0);
    }


    // ============================================
    // ACCIONES DE SEGUIMIENTOS
    // ============================================
    async function handleQuickAction(e) {
        e.preventDefault();
        const action = this.dataset.action;
        const id = this.dataset.id;
        const card = this.closest('.card-seguimiento');
        const paciente = card.dataset.paciente || 'Paciente';

        switch(action) {
            case 'ver':
                window.location.href = `${BASE_URL}/veterinaria/calendario?id=${id}`;
                break;
            case 'editar':
                showToast(`Función de edición en desarrollo`, 'info');
                break;
            case 'completar':
                await completarSeguimiento(id, paciente, card);
                break;
            case 'notificar':
                await notificarPropietario(id, paciente);
                break;
        }
    }

    async function completarSeguimiento(id, paciente, card) {
        if (!confirm(`¿Marcar como completado el seguimiento de ${paciente}?`)) return;

        try {
            const response = await fetch(`${API_URL}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'finalizar', id_seguimiento: id })
            });

            const data = await response.json();

            if (data.status === 'success') {
                card.classList.add('animate__animated', 'animate__fadeOut');
                setTimeout(() => {
                    card.remove();
                    checkIfEmpty();
                    cargarEstadisticas();
                }, 500);
                showToast(`Seguimiento de ${paciente} completado`, 'success');
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error al completar: ' + error.message, 'error');
        }
    }

    async function notificarPropietario(id, paciente) {
        const mensaje = prompt(`Mensaje para propietario de ${paciente}:`, 
                               `Recordatorio de seguimiento para ${paciente}`);
        
        if (!mensaje) return;

        try {
            const response = await fetch(`${API_URL}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'notificar', id_seguimiento: id, mensaje })
            });

            const data = await response.json();

            if (data.status === 'success') {
                showToast(`Notificación enviada al propietario de ${paciente}`, 'success');
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error al notificar: ' + error.message, 'error');
        }
    }


    // ============================================
    // EXPANDIR / COLAPSAR
    // ============================================
    function toggleExpand(e) {
        const card = this.closest('.card-seguimiento');
        const compactView = card.querySelector('.compact-view');
        const expandedView = card.querySelector('.expanded-view');
        const isExpanded = this.getAttribute('aria-expanded') === 'true';

        if (isExpanded) {
            compactView.style.display = 'block';
            expandedView.style.display = 'none';
            this.setAttribute('aria-expanded', 'false');
        } else {
            compactView.style.display = 'none';
            expandedView.style.display = 'block';
            this.setAttribute('aria-expanded', 'true');
        }
    }


    // ============================================
    // VISTAS (LISTA / GRID)
    // ============================================
    function switchView(view) {
        currentView = view;

        if (view === 'list') {
            listaSeguimientos.classList.remove('grid-view');
            viewListBtn?.classList.add('active');
            viewGridBtn?.classList.remove('active');
        } else {
            listaSeguimientos.classList.add('grid-view');
            viewListBtn?.classList.remove('active');
            viewGridBtn?.classList.add('active');
        }
    }

    if (viewListBtn && viewGridBtn) {
        viewListBtn.addEventListener('click', () => switchView('list'));
        viewGridBtn.addEventListener('click', () => switchView('grid'));
    }


    // ============================================
    // FUNCIONES AUXILIARES
    // ============================================
    function showLoading(show) {
        if (!loadingState || !listaSeguimientos) return;

        if (show) {
            loadingState.style.display = 'block';
            listaSeguimientos.style.display = 'none';
        } else {
            setTimeout(() => {
                loadingState.style.display = 'none';
                listaSeguimientos.style.display = 'block';
            }, 300);
        }
    }

    function toggleEmptyState(show) {
        if (emptyState) emptyState.style.display = show ? 'block' : 'none';
        if (listaSeguimientos) listaSeguimientos.style.display = show ? 'none' : 'block';
    }

    function checkIfEmpty() {
        const visibleCards = listaSeguimientos?.querySelectorAll('.card-seguimiento:not([style*="display: none"])');
        toggleEmptyState(!visibleCards || visibleCards.length === 0);
    }

    function showToast(message, type = 'info', duration = 3000) {
        if (!toastContainer) return;

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<div class="toast-body"><i class="bi bi-${getToastIcon(type)} me-2"></i>${message}</div>`;
        
        toastContainer.appendChild(toast);

        setTimeout(() => toast.remove(), duration);
    }

    function getToastIcon(type) {
        return { success: 'check-circle-fill', error: 'x-circle-fill', warning: 'exclamation-triangle-fill', info: 'info-circle-fill' }[type] || 'info-circle-fill';
    }

    function updateLastUpdateTime() {
        const el = document.getElementById('lastUpdate');
        if (el) el.textContent = `Actualizado ${new Date().toLocaleTimeString('es-ES')}`;
    }


    // ============================================
    // NAVEGACIÓN POR TECLADO
    // ============================================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu.show').forEach(d => d.classList.remove('show'));
        }
        
        if ((e.ctrlKey || e.metaKey) && e.key === 'f' && searchInput) {
            e.preventDefault();
            searchInput.focus();
        }
    });


    // ============================================
    // INICIALIZACIÓN
    // ============================================
    async function init() {
        console.log('🚀 Dashboard Seguimientos iniciado');
        await cargarSeguimientos();
        console.log('✅ Sistema listo');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
