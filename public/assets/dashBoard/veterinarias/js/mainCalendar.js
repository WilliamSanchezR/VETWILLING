// calendar.js - Lógica de FullCalendar y manejo de Agendamiento (MVC)

// --- DEFINICIÓN DE RUTAS LÓGICAS ---
// Basado en tu archivo 'calendarioController.php', las rutas deben apuntar a ese controlador.
const URLS = {
    // 1. CARGAR EVENTOS (GET): Llama al método que trae todos los agendamientos.
    LOAD: '/vetwilling/calendario/loadEvents',

    // 2. CREAR EVENTO (POST): Llama al método para insertar un nuevo agendamiento.
    CREATE: '/vetwilling/calendario/storeEvent',

    // 3. MODIFICAR EVENTO (POST): Llama al método para actualizar fechas/horas.
    UPDATE: '/vetwilling/calendario/updateEvent',

    // 4. ELIMINAR EVENTO (DELETE): Llama al método para eliminar un agendamiento.
    DELETE: '/vetwilling/calendario/deleteEvent',

    // 5. OBTENER PROPIETARIOS: Lista de propietarios
    GET_PROPIETARIOS: '/vetwilling/calendario/getPropietarios',

    // 6. OBTENER MASCOTAS: Lista de mascotas por propietario
    GET_MASCOTAS: '/vetwilling/calendario/getMascotas',

    // 7. OBTENER SERVICIOS: Lista de servicios disponibles
    GET_SERVICIOS: '/vetwilling/calendario/getServicios',

    // 7b. OBTENER SUBSERVICIOS: Lista de subservicios disponibles
    GET_SUBSERVICIOS: '/vetwilling/calendario/getSubservicios',

    // 8. OBTENER VETERINARIOS: Lista de veterinarios de la veterinaria
    GET_VETERINARIOS: '/vetwilling/calendario/getVeterinarios',

    // 9. OBTENER DISPONIBILIDAD DEL PROFESIONAL
    GET_DISPONIBILIDAD: '/vetwilling/disponibilidad/horarios'
};


// Esperamos a que todo el contenido HTML de la página se haya cargado.
document.addEventListener('DOMContentLoaded', function () {

    // ╔═══════════════════════════════════════════════════════════════════════╗
    // ║  FUNCIÓN PARA CONVERTIR FECHAS LOCALES A FORMATO MYSQL               ║
    // ║  Evita problemas de zona horaria (UTC vs Local)                      ║
    // ╚═══════════════════════════════════════════════════════════════════════╝
    function formatDateForMySQL(date) {
        if (!date) return null;

        // Si es un string, convertir a Date
        if (typeof date === 'string') {
            date = new Date(date);
        }

        // Extraer componentes en hora local (no UTC)
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');

        // Formato MySQL: YYYY-MM-DD HH:MM:SS
        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

    // ╔═══════════════════════════════════════════════════════════════════════╗
    // ║  FUNCIÓN PARA FORMATEAR FECHAS PARA INPUT DATETIME-LOCAL             ║
    // ║  Convierte Date a formato YYYY-MM-DDTHH:MM (hora local)              ║
    // ╚═══════════════════════════════════════════════════════════════════════╝
    function formatDateForInput(date) {
        if (!date) return '';

        // Si es un string, convertir a Date
        if (typeof date === 'string') {
            date = new Date(date);
        }

        // Extraer componentes en hora local (no UTC)
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        // Formato para input datetime-local: YYYY-MM-DDTHH:MM
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    // --- Función de Utilidad para Peticiones AJAX (Fetch) ---
    function sendEventData(url, data, successCallback, errorCallback) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        })
            .then(response => {
                // Log para debugging
                console.log('Response status:', response.status);
                console.log('Response statusText:', response.statusText);

                // Siempre intentar parsear como JSON, incluso en errores
                return response.json().then(data => {
                    if (!response.ok) {
                        // Si es un error HTTP, lanzar con el mensaje del servidor
                        throw {
                            status: response.status,
                            message: data.message || response.statusText,
                            data: data
                        };
                    }
                    return data;
                });
            })
            .then(result => {
                console.log('Result:', result);
                if (result.status === 'success') {
                    console.log('Operación exitosa:', result.message);
                    if (successCallback) { successCallback(result); }
                } else {
                    console.error('Error lógico en el servidor:', result.message);
                    if (errorCallback) { errorCallback(result.message, result); }
                }
            })
            .catch(error => {
                console.error('Error de comunicación AJAX:', error);

                // Si el error viene con data que contiene información de disponibilidad
                if (error.data && error.data.tipo === 'disponibilidad') {
                    mostrarErrorDisponibilidad(error.data);
                } else if (error.data && error.data.tipo === 'conflicto') {
                    // Error de conflicto con otra cita
                    const errorMessage = error.message || error.statusText || 'Error desconocido';
                    if (errorCallback) { errorCallback(errorMessage, error.data); }
                } else {
                    // Error genérico
                    const errorMessage = error.message || error.statusText || 'Error desconocido';
                    if (errorCallback) { errorCallback(errorMessage, error.data); }
                }
            });
    }

    /**
     * Muestra un mensaje de error específico cuando la cita está fuera de disponibilidad
     */
    function mostrarErrorDisponibilidad(data) {
        let rangosHTML = '';

        if (data.rangos_disponibles && data.rangos_disponibles.length > 0) {
            rangosHTML = '<div style="margin-top: 15px; text-align: left; background: #f8f9fa; padding: 15px; border-radius: 8px;">';
            rangosHTML += '<strong style="color: #28a745;">📅 Horarios disponibles:</strong><ul style="margin: 10px 0; padding-left: 20px;">';

            data.rangos_disponibles.forEach(rango => {
                const horaInicio = rango.hora_inicio.substring(0, 5);
                const horaFin = rango.hora_fin.substring(0, 5);
                const especialidad = rango.especialidad ? ` (${rango.especialidad})` : '';
                rangosHTML += `<li style="margin: 5px 0; color: #495057;">${horaInicio} - ${horaFin}${especialidad}</li>`;
            });

            rangosHTML += '</ul></div>';
        }

        // Extraer solo el primer mensaje antes de "Rangos disponibles:"
        const mensajePrincipal = data.message.split('\n')[0];

        Swal.fire({
            icon: 'warning',
            title: '⚠️ Horario No Disponible',
            html: `
                <div style="text-align: center;">
                    <p style="font-size: 18px; margin-bottom: 10px;">${mensajePrincipal}</p>
                    ${rangosHTML}
                    <p style="margin-top: 15px; color: #6c757d; font-size: 13px;">
                        <strong>💡 Sugerencia:</strong> Selecciona un horario dentro de la disponibilidad del veterinario.
                    </p>
                </div>
            `,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#3085d6',
            width: '600px'
        });
    }

    // --- Funciones para cargar datos del servidor ---
    async function cargarPropietarios() {
        try {
            const response = await fetch(URLS.GET_PROPIETARIOS);
            const result = await response.json();
            return result.status === 'success' ? result.data : [];
        } catch (error) {
            console.error('Error al cargar propietarios:', error);
            return [];
        }
    }

    async function cargarMascotasPorPropietario(idPropietario) {
        try {
            const response = await fetch(`${URLS.GET_MASCOTAS}?id_propietario=${idPropietario}`);
            const result = await response.json();
            return result.status === 'success' ? result.data : [];
        } catch (error) {
            console.error('Error al cargar mascotas:', error);
            return [];
        }
    }

    async function cargarServicios() {
        try {
            const response = await fetch(URLS.GET_SERVICIOS);
            const result = await response.json();
            return result.status === 'success' ? result.data : [];
        } catch (error) {
            console.error('Error al cargar servicios:', error);
            return [];
        }
    }

    async function cargarSubservicios() {
        try {
            const response = await fetch(URLS.GET_SUBSERVICIOS);
            const result = await response.json();
            return result.status === 'success' ? result.data : [];
        } catch (error) {
            console.error('Error al cargar subservicios:', error);
            return [];
        }
    }

    // 8. OBTENER VETERINARIOS: Lista de veterinarios de la veterinaria
    async function cargarVeterinarios() {
        try {
            console.log('🔄 Cargando veterinarios desde:', URLS.GET_VETERINARIOS);
            const response = await fetch(URLS.GET_VETERINARIOS);
            console.log('📡 Respuesta recibida:', response.status, response.statusText);

            const data = await response.json();
            console.log('📦 Datos recibidos:', data);

            if (data.status === 'success') {
                return data.data;
            } else {
                console.error('❌ Error del servidor:', data.message);
                return [];
            }
        } catch (error) {
            console.error('❌ Error en la petición de veterinarios:', error);
            return [];
        }
    }

    // Mapeo de servicios a colores
    const servicioColores = {
        'Consulta general': '#0A932C',
        'Exámenes de laboratorio': '#93BEDF',
        'Baño y peluquería': '#9DE795',
        'Otro': '#6c757d'
    };

    // Función para calcular luminosidad de un color y determinar si necesita texto oscuro
    function getTextColor(bgColor) {
        // Extraer RGB del color
        let r, g, b;

        if (bgColor.startsWith('#')) {
            r = parseInt(bgColor.slice(1, 3), 16);
            g = parseInt(bgColor.slice(3, 5), 16);
            b = parseInt(bgColor.slice(5, 7), 16);
        } else if (bgColor.startsWith('rgb')) {
            const match = bgColor.match(/\d+/g);
            r = parseInt(match[0]);
            g = parseInt(match[1]);
            b = parseInt(match[2]);
        }

        // Calcular luminosidad relativa (formula W3C)
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

        // Si es claro (> 0.6), usar texto oscuro; si es oscuro, usar texto claro
        return luminance > 0.6 ? '#1a1a1a' : '#ffffff';
    }

    // Función para generar variación de color para subservicios
    function getSubservicioColor(servicioColor, index, total) {
        // Convertir hex a RGB
        const r = parseInt(servicioColor.slice(1, 3), 16);
        const g = parseInt(servicioColor.slice(3, 5), 16);
        const b = parseInt(servicioColor.slice(5, 7), 16);

        // Generar variación más oscura/clara con mejor contraste
        // Rango ajustado para evitar colores demasiado claros
        const factor = 0.7 + (index / total) * 0.4; // Rango de 0.7 a 1.1
        const newR = Math.min(255, Math.round(r * factor));
        const newG = Math.min(255, Math.round(g * factor));
        const newB = Math.min(255, Math.round(b * factor));

        return `rgb(${newR}, ${newG}, ${newB})`;
    }

    // Mapeo de emojis por servicio
    const servicioEmojis = {
        'Consulta general': '🩺',
        'Exámenes de laboratorio': '🔬',
        'Baño y peluquería': '✂️',
        'Otro': '📌'
    };

    // --- Parte de Arrastre de Eventos Externos (Draggable) ---
    async function initializeExternalEvents() {
        var containerEl = document.getElementById('external-events');

        if (containerEl) {
            // Cargar servicios y subservicios desde la base de datos
            const servicios = await cargarServicios();
            const subservicios = await cargarSubservicios();

            // Limpiar contenedor
            containerEl.innerHTML = '<h4>Servicios Disponibles</h4>';

            // Agrupar subservicios por servicio
            const subserviciosPorServicio = {};
            subservicios.forEach(subservicio => {
                const idServicio = subservicio.id_servicio;
                if (!subserviciosPorServicio[idServicio]) {
                    subserviciosPorServicio[idServicio] = [];
                }
                subserviciosPorServicio[idServicio].push(subservicio);
            });

            // Crear grupos colapsables por cada servicio
            servicios.forEach((servicio, index) => {
                const color = servicioColores[servicio.nombre] || '#6c757d';
                const emoji = servicioEmojis[servicio.nombre] || '📌';
                const subsDelServicio = subserviciosPorServicio[servicio.id_servicio] || [];

                // Crear el grupo de servicio
                const grupoDiv = document.createElement('div');
                grupoDiv.className = 'servicio-grupo collapsed';

                // Crear header del grupo (colapsable)
                const headerDiv = document.createElement('div');
                headerDiv.className = 'servicio-header';
                headerDiv.style.borderLeft = `4px solid ${color}`;
                headerDiv.innerHTML = `
                    <div class="servicio-header-content">
                        <span class="servicio-icono">${emoji}</span>
                        <span class="servicio-nombre">${servicio.nombre}</span>
                        <span class="servicio-count">(${subsDelServicio.length})</span>
                    </div>
                    <i class="bi bi-chevron-right servicio-toggle"></i>
                `;

                // Crear contenedor de subservicios
                const subserviciosContainer = document.createElement('div');
                subserviciosContainer.className = 'subservicios-container';
                subserviciosContainer.style.display = 'none'; // Contraído por defecto

                // Agregar subservicios al contenedor
                subsDelServicio.forEach((subservicio, subIndex) => {
                    // Generar color variante para cada subservicio
                    const subservicioColor = getSubservicioColor(color, subIndex, subsDelServicio.length);
                    const textColor = getTextColor(subservicioColor);

                    const eventDiv = document.createElement('div');
                    eventDiv.className = `fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event`;
                    eventDiv.setAttribute('data-duration', '01:00');
                    eventDiv.setAttribute('data-subservicio-id', subservicio.id_subservicio);
                    eventDiv.setAttribute('data-subservicio-nombre', subservicio.nombre_subservicio);
                    eventDiv.setAttribute('data-servicio-nombre', servicio.nombre);
                    eventDiv.style.backgroundColor = subservicioColor;
                    eventDiv.style.borderColor = subservicioColor;
                    eventDiv.style.color = textColor;

                    const mainDiv = document.createElement('div');
                    mainDiv.className = 'fc-event-main';
                    const precio = new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP',
                        minimumFractionDigits: 0
                    }).format(subservicio.costo);
                    mainDiv.innerHTML = `
                        <span class="subservicio-nombre">${subservicio.nombre_subservicio}</span>
                        <span class="subservicio-precio">${precio}</span>
                    `;

                    eventDiv.appendChild(mainDiv);
                    subserviciosContainer.appendChild(eventDiv);
                });

                // Evento de toggle collapse
                headerDiv.addEventListener('click', function () {
                    const isExpanded = subserviciosContainer.style.display === 'block';
                    subserviciosContainer.style.display = isExpanded ? 'none' : 'block';
                    const icon = headerDiv.querySelector('.servicio-toggle');
                    icon.className = isExpanded ? 'bi bi-chevron-right servicio-toggle' : 'bi bi-chevron-down servicio-toggle';
                    grupoDiv.classList.toggle('collapsed', isExpanded);
                });

                grupoDiv.appendChild(headerDiv);
                grupoDiv.appendChild(subserviciosContainer);
                containerEl.appendChild(grupoDiv);
            });

            // Inicializar draggable
            new FullCalendar.Draggable(containerEl, {
                itemSelector: '.fc-event',
                eventData: function (eventEl) {
                    return {
                        title: eventEl.getAttribute('data-subservicio-nombre'),
                        duration: eventEl.getAttribute('data-duration'),
                        extendedProps: {
                            subservicioId: eventEl.getAttribute('data-subservicio-id'),
                            servicioNombre: eventEl.getAttribute('data-servicio-nombre')
                        }
                    };
                }
            });
        }
    }

    initializeExternalEvents();

    async function cargarDisponibilidad() {
        try {
            const response = await fetch(`${URLS.GET_DISPONIBILIDAD}?action=horarios`);
            if (!response.ok) {
                return [];
            }
            const data = await response.json();
            return Array.isArray(data) ? data : [];
        } catch (error) {
            return [];
        }
    }

    // --- Inicialización Principal del Calendario ---
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {

        // --- CONFIGURACIÓN DE APARIENCIA Y VISTA ---
        locale: 'es',
        timeZone: 'local',
        themeSystem: 'bootstrap5',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },

        // --- CARGA DE EVENTOS ---
        events: URLS.LOAD,

        // --- DISPONIBILIDAD (Business Hours) ---
        businessHours: true,
        selectConstraint: 'businessHours',
        eventConstraint: 'businessHours',

        // --- INTERACCIÓN Y CREACIÓN DE EVENTOS (Selectable) ---
        selectable: true,

        select: async function (info) {
            // Se ejecuta cuando el usuario selecciona un rango de fechas.

            // Formatear las fechas iniciales
            var fechaInicio = info.start;
            var fechaFin = info.end || info.start;

            // Si es todo el día, ajustar las horas
            var fechaInicioStr, fechaFinStr;
            if (info.allDay) {
                // Establecer hora por defecto 8:00 AM
                var fechaInicioConHora = new Date(fechaInicio);
                fechaInicioConHora.setHours(8, 0, 0);
                fechaInicioStr = formatDateForInput(fechaInicioConHora);

                // Establecer hora de fin por defecto 9:00 AM (1 hora después)
                var fechaFinConHora = new Date(fechaInicio);
                fechaFinConHora.setHours(9, 0, 0);
                fechaFinStr = formatDateForInput(fechaFinConHora);
            } else {
                fechaInicioStr = formatDateForInput(fechaInicio);
                fechaFinStr = formatDateForInput(fechaFin);
            }

            // Cargar datos desde el servidor
            const propietarios = await cargarPropietarios();
            const servicios = await cargarServicios();
            const subservicios = await cargarSubservicios();

            console.log('Servicios cargados:', servicios);
            console.log('Subservicios cargados:', subservicios);

            // Crear opciones HTML para propietarios
            let propietariosOptions = '<option value="">Selecciona un propietario...</option>';
            propietarios.forEach(prop => {
                propietariosOptions += `<option value="${prop.id_propietario}">${prop.nombres} ${prop.apellidos}</option>`;
            });

            // Crear opciones HTML para servicios principales
            let serviciosOptions = '<option value="">Selecciona un servicio...</option>';
            servicios.forEach(servicio => {
                serviciosOptions += `<option value="${servicio.id_servicio}">${servicio.nombre}</option>`;
            });
            // lo que yo modifique
            Swal.fire({
                title: '<div style="display: flex; align-items: center; gap: 12px; justify-content: center;"><i class="bi bi-calendar-plus" style="font-size: 28px; color: #0a932c;"></i><span>Nuevo Agendamiento</span></div>',
                html: `
        <style>
            .form-agendamiento {
                text-align: left;
                margin: 20px 0;
                max-height: 500px;
                overflow-y: auto;
                padding: 0 5px;
            }
            
            .form-agendamiento::-webkit-scrollbar {
                width: 8px;
            }
            
            .form-agendamiento::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }
            
            .form-agendamiento::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 10px;
            }
            
            .form-agendamiento::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
            
            .form-group-ag {
                margin-bottom: 24px;
            }
            
            .form-label-ag {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 10px;
                font-weight: 600;
                color: #00304D;
                font-size: 14px;
            }
            
            .form-label-ag i {
                font-size: 16px;
                color: #0a932c;
            }
            
            .form-label-ag .required {
                color: #e74c3c;
                margin-left: 2px;
            }
            
            .form-control-ag {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                font-size: 14px;
                font-family: inherit;
                transition: all 0.3s ease;
                background: #ffffff;
                box-sizing: border-box;
            }
            
            .form-control-ag:focus {
                outline: none;
                border-color: #0a932c;
                box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
                background: #ffffff;
            }
            
            .form-control-ag:disabled {
                background: #f5f5f5;
                cursor: not-allowed;
                color: #999;
            }
            
            .form-control-ag option {
                padding: 10px;
            }
            
            textarea.form-control-ag {
                resize: vertical;
                min-height: 100px;
                font-family: inherit;
                line-height: 1.5;
            }
            
            .form-helper {
                font-size: 12px;
                color: #666;
                margin-top: 6px;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .form-helper i {
                font-size: 11px;
            }
            
            .form-divider {
                height: 1px;
                background: linear-gradient(to right, transparent, #e0e0e0, transparent);
                margin: 25px 0;
            }
            
            /* Dark mode support */
            body.dark-mode .form-label-ag {
                color: #dddddd !important;
            }
            
            body.dark-mode .form-control-ag {
                background: #2d2d2d !important;
                border-color: #444 !important;
                color: #ffffff !important;
            }
            
            body.dark-mode .form-control-ag:focus {
                background: #333333 !important;
                border-color: #0a932c !important;
            }
            
            body.dark-mode .form-control-ag:disabled {
                background: #252525 !important;
            }
            
            body.dark-mode .form-helper {
                color: #aaaaaa !important;
            }
            
            body.dark-mode .form-divider {
                background: linear-gradient(to right, transparent, #444, transparent) !important;
            }
        </style>
        
        <div class="form-agendamiento">
            <div class="form-group-ag">
                <label class="form-label-ag">
                    <i class="bi bi-person-fill"></i>
                    Propietario
                    <span class="required">*</span>
                </label>
                <select id="swal-propietario" class="form-control-ag">
                    ${propietariosOptions}
                </select>
                <div class="form-helper">
                    <i class="bi bi-info-circle"></i>
                    Selecciona el propietario de la mascota
                </div>
            </div>
            
            <div class="form-group-ag">
                <label class="form-label-ag">
                    <i class="bi bi-heart-fill"></i>
                    Mascota
                    <span class="required">*</span>
                </label>
                <select id="swal-mascota" class="form-control-ag" disabled>
                    <option value="">Primero selecciona un propietario...</option>
                </select>
            </div>
            
            <div class="form-divider"></div>
            
            <div class="form-group-ag">
                <label class="form-label-ag">
                    <i class="bi bi-briefcase-fill"></i>
                    Servicio
                    <span class="required">*</span>
                </label>
                <select id="swal-servicio" class="form-control-ag">
                    ${serviciosOptions}
                </select>
                <div class="form-helper">
                    <i class="bi bi-info-circle"></i>
                    Selecciona el tipo de servicio principal
                </div>
            </div>
            
            <div class="form-group-ag">
                <label class="form-label-ag">
                    <i class="bi bi-list-check"></i>
                    Subservicio
                    <span class="required">*</span>
                </label>
                <select id="swal-subservicio" class="form-control-ag" disabled>
                    <option value="">Primero selecciona un servicio...</option>
                </select>
                <div class="form-helper">
                    <i class="bi bi-info-circle"></i>
                    Selecciona el subservicio específico
                </div>
            </div>
            
            <div class="form-divider"></div>
            
            <div class="form-group-ag">
                <label class="form-label-ag">
                    <i class="bi bi-calendar-event"></i>
                    Fecha y Hora de Inicio
                    <span class="required">*</span>
                </label>
                <input id="swal-fecha-inicio" type="datetime-local" class="form-control-ag" 
                    value="${fechaInicioStr}">
            </div>
            
            <div class="form-group-ag">
                <label class="form-label-ag">
                    <i class="bi bi-calendar-check"></i>
                    Fecha y Hora de Fin
                    <span class="required">*</span>
                </label>
                <input id="swal-fecha-fin" type="datetime-local" class="form-control-ag" 
                    value="${fechaFinStr}">
                <div class="form-helper">
                    <i class="bi bi-clock"></i>
                    La duración del servicio se calculará automáticamente
                </div>
            </div>
            
            <div class="form-divider"></div>
            
            <div class="form-group-ag">
                <label class="form-label-ag">
                    <i class="bi bi-card-text"></i>
                    Observaciones
                </label>
                <textarea id="swal-observaciones" class="form-control-ag" 
                        placeholder="Agrega observaciones o detalles adicionales sobre el agendamiento..."></textarea>
            </div>
        </div>
    `,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-check-circle"></i> Crear Agendamiento',
                cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
                width: '650px',
                padding: '25px',
                background: '#ffffff',
                customClass: {
                    popup: 'popup-agendamiento',
                    title: 'title-agendamiento',
                    confirmButton: 'btn-confirmar-agendamiento',
                    cancelButton: 'btn-cancelar-agendamiento'
                },
                buttonsStyling: false,
                focusConfirm: false,
                didOpen: () => {
                    // Agregar estilos adicionales para los botones y el popup
                    const style = document.createElement('style');
                    style.textContent = `
            .popup-agendamiento {
                border-radius: 16px !important;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
            }
            
            .title-agendamiento {
                font-size: 24px !important;
                font-weight: 700 !important;
                color: #00304D !important;
                padding: 20px 20px 10px 20px !important;
            }
            
            .swal2-actions {
                gap: 12px !important;
                margin-top: 25px !important;
            }
            
            .btn-confirmar-agendamiento,
            .btn-cancelar-agendamiento {
                padding: 12px 30px !important;
                border-radius: 10px !important;
                font-weight: 600 !important;
                font-size: 15px !important;
                transition: all 0.3s ease !important;
                border: none !important;
                cursor: pointer !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 8px !important;
            }
            
            .btn-confirmar-agendamiento {
                background: linear-gradient(135deg, #0a932c 0%, #0a932c 100%) !important;
                color: white !important;
            }
            
            .btn-confirmar-agendamiento:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4) !important;
            }
            
            .btn-confirmar-agendamiento:active {
                transform: translateY(0) !important;
            }
            
            .btn-cancelar-agendamiento {
                background: #f0f0f0 !important;
                color: #666 !important;
            }
            
            .btn-cancelar-agendamiento:hover {
                background: #e0e0e0 !important;
            }
            
            /* Dark mode support para el popup */
            body.dark-mode .popup-agendamiento {
                background: #1e1e1e !important;
            }
            
            body.dark-mode .title-agendamiento {
                color: #ffffff !important;
            }
            
            body.dark-mode .btn-cancelar-agendamiento {
                background: #2d2d2d !important;
                color: #dddddd !important;
            }
            
            body.dark-mode .btn-cancelar-agendamiento:hover {
                background: #3d3d3d !important;
            }
        `;
                    document.head.appendChild(style);

                    // Agregar evento al cambiar el propietario
                    const propietarioSelect = document.getElementById('swal-propietario');
                    const mascotaSelect = document.getElementById('swal-mascota');

                    propietarioSelect.addEventListener('change', async function () {
                        const idPropietario = this.value;

                        if (idPropietario) {
                            mascotaSelect.disabled = true;
                            mascotaSelect.innerHTML = '<option value="">Cargando mascotas...</option>';

                            const mascotas = await cargarMascotasPorPropietario(idPropietario);

                            let mascotasOptions = '<option value="">Selecciona una mascota...</option>';
                            mascotas.forEach(mascota => {
                                mascotasOptions += `<option value="${mascota.id_paciente}">${mascota.nombre} (${mascota.especie})</option>`;
                            });

                            mascotaSelect.innerHTML = mascotasOptions;
                            mascotaSelect.disabled = false;
                        } else {
                            mascotaSelect.innerHTML = '<option value="">Primero selecciona un propietario...</option>';
                            mascotaSelect.disabled = true;
                        }
                    });

                    // Agregar evento al cambiar el servicio para filtrar subservicios
                    const servicioSelect = document.getElementById('swal-servicio');
                    const subservicioSelect = document.getElementById('swal-subservicio');

                    servicioSelect.addEventListener('change', function () {
                        const idServicio = this.value;

                        if (idServicio) {
                            // Filtrar subservicios por el servicio seleccionado
                            const subserviciosFiltrados = subservicios.filter(sub =>
                                sub.id_servicio == idServicio
                            );

                            if (subserviciosFiltrados.length > 0) {
                                let subserviciosOptions = '<option value="">Selecciona un subservicio...</option>';
                                subserviciosFiltrados.forEach(sub => {
                                    const costoCOP = new Intl.NumberFormat('es-CO', {
                                        style: 'currency',
                                        currency: 'COP',
                                        minimumFractionDigits: 0
                                    }).format(sub.costo);
                                    subserviciosOptions += `<option value="${sub.id_subservicio}">${sub.nombre_subservicio} - ${costoCOP}</option>`;
                                });

                                subservicioSelect.innerHTML = subserviciosOptions;
                                subservicioSelect.disabled = false;
                            } else {
                                subservicioSelect.innerHTML = '<option value="">No hay subservicios para este servicio</option>';
                                subservicioSelect.disabled = true;
                            }
                        } else {
                            subservicioSelect.innerHTML = '<option value="">Primero selecciona un servicio...</option>';
                            subservicioSelect.disabled = true;
                        }
                    });
                },
                preConfirm: () => {
                    const propietario = document.getElementById('swal-propietario').value;
                    const mascota = document.getElementById('swal-mascota').value;
                    const servicio = document.getElementById('swal-servicio').value;
                    const subservicio = document.getElementById('swal-subservicio').value;
                    const fechaInicio = document.getElementById('swal-fecha-inicio').value;
                    const fechaFin = document.getElementById('swal-fecha-fin').value;
                    const observaciones = document.getElementById('swal-observaciones').value;

                    if (!propietario) {
                        Swal.showValidationMessage('Debes seleccionar un propietario');
                        return false;
                    }

                    if (!mascota) {
                        Swal.showValidationMessage('Debes seleccionar una mascota');
                        return false;
                    }

                    if (!servicio) {
                        Swal.showValidationMessage('Debes seleccionar un servicio');
                        return false;
                    }

                    if (!subservicio) {
                        Swal.showValidationMessage('Debes seleccionar un subservicio');
                        return false;
                    }

                    if (!fechaInicio) {
                        Swal.showValidationMessage('Debes ingresar la fecha y hora de inicio');
                        return false;
                    }

                    if (!fechaFin) {
                        Swal.showValidationMessage('Debes ingresar la fecha y hora de fin');
                        return false;
                    }

                    // Validar que la fecha de fin sea posterior a la de inicio
                    if (new Date(fechaFin) <= new Date(fechaInicio)) {
                        Swal.showValidationMessage('La fecha de fin debe ser posterior a la fecha de inicio');
                        return false;
                    }

                    // Obtener el nombre del servicio seleccionado
                    const selectElement = document.getElementById('swal-subservicio');
                    const selectedOption = selectElement.options[selectElement.selectedIndex];
                    const servicioNombre = selectedOption.text.split(' - ')[0]; // Extraer solo el nombre sin el costo

                    // Obtener el color basado en el nombre del servicio
                    const servicioColores = {
                        'Consulta General': '#007832',
                        'Vacunación': '#17a2b8',
                        'Cirugía': '#dc3545',
                        'Control': '#ffc107',
                        'Emergencia': '#fd7e14',
                        'Desparasitación': '#6f42c1',
                        'Peluquería': '#e83e8c',
                        'Baño': '#20c997',
                        'Otro': '#6c757d'
                    };
                    const color = servicioColores[servicioNombre] || '#6c757d';

                    return {
                        propietario: propietario,
                        mascota: mascota,
                        servicio: servicio,
                        subservicio: subservicio,
                        servicioNombre: servicioNombre,
                        fechaInicio: fechaInicio,
                        fechaFin: fechaFin,
                        observaciones: observaciones,
                        color: color
                    };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const datos = result.value;

                    // Convertir fechas a formato MySQL (hora local, no UTC)
                    const fechaInicioMySQL = formatDateForMySQL(new Date(datos.fechaInicio));
                    const fechaFinMySQL = formatDateForMySQL(new Date(datos.fechaFin));

                    // 1. Preparamos los datos a enviar al servidor
                    var newEventData = {
                        id_paciente: parseInt(datos.mascota),
                        id_servicio: parseInt(datos.servicio), // ID del servicio principal
                        id_subservicio: parseInt(datos.subservicio), // ID del subservicio específico
                        id_especialidad: 1, // Especialidad por defecto
                        tipo: datos.servicioNombre, // Usar el nombre del subservicio como tipo
                        observaciones: datos.observaciones,
                        fecha_hora: fechaInicioMySQL,
                        fecha_hora_fin: fechaFinMySQL,
                        estado: 'Pendiente',
                        allDay: 0 // Siempre será un evento con hora específica
                    };

                    // 2. Llamada AJAX para guardar en la base de datos
                    sendEventData(URLS.CREATE, newEventData,
                        // Función de Éxito
                        function (response) {
                            calendar.addEvent({
                                id: response.id,
                                title: datos.servicioNombre,
                                start: datos.fechaInicio,
                                end: datos.fechaFin,
                                allDay: false,
                                backgroundColor: datos.color,
                                borderColor: datos.color
                            });
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                html: `
                        <div style="text-align: center;">
                            <p><strong>${datos.servicioNombre}</strong> creado correctamente</p>
                            <p style="color: #6c757d; font-size: 0.9em;">
                                ${new Date(datos.fechaInicio).toLocaleString('es-ES', {
                                    dateStyle: 'short',
                                    timeStyle: 'short'
                                })} - 
                                ${new Date(datos.fechaFin).toLocaleString('es-ES', {
                                    timeStyle: 'short'
                                })}
                            </p>
                        </div>
                    `,
                                confirmButtonText: 'Aceptar',
                                timer: 3000,
                                timerProgressBar: true
                            });
                        },
                        // Función de Error
                        function (errorMessage, errorData) {
                            // Si es un error de disponibilidad, ya se mostró en mostrarErrorDisponibilidad()
                            if (errorData && errorData.tipo === 'disponibilidad') {
                                return; // No mostrar alerta duplicada
                            }

                            // Para otros tipos de error, mostrar alerta
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo guardar el agendamiento: ' + errorMessage,
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    );
                }
            });

            // desde aca esta inctacto
            calendar.unselect();
        },

        // --- MODIFICACIÓN DE EVENTOS (editable) ---
        editable: true,
        droppable: true,

        // EVENTO CLICK: Se ejecuta cuando se hace clic en un evento
        eventClick: function (info) {
            // Obtener información del evento
            var evento = info.event;
            var fechaInicio = evento.start;
            var fechaFin = evento.end;

            // Formatear las fechas para los inputs (hora local, no UTC)
            var fechaInicioStr = formatDateForInput(fechaInicio); // YYYY-MM-DDTHH:MM
            var fechaFinStr = fechaFin ? formatDateForInput(fechaFin) : '';

            Swal.fire({
                title: '<i class="bi bi-calendar-check" style="color:#2e7d32; margin-right:8px;"></i> Editar Agendamiento',
                html: `
                <div class="swal-edit-form">

                    <div class="swal-field-group">
                        <label class="swal-label" for="swal-tipo">
                            <i class="bi bi-scissors swal-label-icon"></i>
                            Tipo de Servicio <span class="swal-required">*</span>
                        </label>
                        <input id="swal-tipo" class="swal-custom-input" value="${evento.title}"
                            placeholder="Ej: Consulta general, Vacunación...">
                        <span class="swal-hint">
                            <i class="bi bi-info-circle"></i> Selecciona el tipo de servicio principal
                        </span>
                    </div>

                    <div class="swal-field-group">
                        <label class="swal-label" for="swal-fecha-inicio">
                            <i class="bi bi-calendar-date swal-label-icon"></i>
                            Fecha y Hora de Inicio <span class="swal-required">*</span>
                        </label>
                        <input id="swal-fecha-inicio" type="datetime-local" class="swal-custom-input"
                            value="${fechaInicioStr}">
                    </div>

                    <div class="swal-field-group">
                        <label class="swal-label" for="swal-fecha-fin">
                            <i class="bi bi-calendar2-check swal-label-icon"></i>
                            Fecha y Hora de Fin
                            <span class="swal-optional">(opcional)</span>
                        </label>
                        <input id="swal-fecha-fin" type="datetime-local" class="swal-custom-input"
                            value="${fechaFinStr}">
                        <span class="swal-hint">
                            <i class="bi bi-info-circle"></i> Si no se indica, se usará la duración por defecto
                        </span>
                    </div>

                </div>

                <style>
                    /* Popup más ancho */
                    .swal2-popup {
                        width: 480px !important;
                        border-radius: 14px !important;
                    }

                    /* Título */
                    .swal2-title {
                        font-size: 22px !important;
                        font-weight: 700 !important;
                        color: #1a1a2e !important;
                        padding-bottom: 0 !important;
                    }

                    /* Contenedor general */
                    .swal-edit-form {
                        text-align: left;
                        padding: 6px 4px 4px;
                        display: flex;
                        flex-direction: column;
                        gap: 20px;
                    }

                    /* Grupo label + input + hint */
                    .swal-field-group {
                        display: flex;
                        flex-direction: column;
                        gap: 5px;
                    }

                    /* Label */
                    .swal-label {
                        font-size: 13px;
                        font-weight: 700;
                        color: #1a1a2e;
                        display: flex;
                        align-items: center;
                        gap: 7px;
                        margin-bottom: 1px;
                    }

                    .swal-label-icon {
                        color: #2e7d32;
                        font-size: 15px;
                    }

                    .swal-required {
                        color: #e53935;
                        font-size: 14px;
                        line-height: 1;
                    }

                    .swal-optional {
                        font-weight: 400;
                        font-size: 11px;
                        color: #9ca3af;
                    }

                    /* Hint debajo del input */
                    .swal-hint {
                        font-size: 11.5px;
                        color: #6b7280;
                        display: flex;
                        align-items: center;
                        gap: 5px;
                        margin-top: 2px;
                    }

                    .swal-hint i {
                        font-size: 12px;
                        color: #9ca3af;
                    }

                    /* Input */
                    .swal-custom-input {
                        width: 100%;
                        padding: 10px 14px;
                        border: 1.5px solid #d1d5db;
                        border-radius: 8px;
                        font-size: 14px;
                        font-family: inherit;
                        color: #1a1a2e;
                        background: #f9fafb;
                        transition: border-color 0.2s ease, box-shadow 0.2s ease;
                        outline: none;
                        box-sizing: border-box;
                    }

                    .swal-custom-input:focus {
                        border-color: #2e7d32;
                        background: #fff;
                        box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.12);
                    }

                    .swal-custom-input::placeholder {
                        color: #9ca3af;
                        font-size: 13px;
                    }

                    /* Botón confirmar */
                    .swal2-confirm {
                        background-color: #2e7d32 !important;
                        border-radius: 8px !important;
                        font-weight: 600 !important;
                        padding: 10px 24px !important;
                        font-size: 14px !important;
                    }

                    .swal2-confirm:hover {
                        background-color: #1b5e20 !important;
                    }

                    /* Botón cancelar */
                    .swal2-cancel {
                        background-color: #6b7280 !important;
                        border-radius: 8px !important;
                        font-weight: 600 !important;
                        padding: 10px 24px !important;
                        font-size: 14px !important;
                    }

                    /* Divider entre content y botones */
                    .swal2-actions {
                        margin-top: 8px !important;
                        gap: 10px !important;
                    }
                </style>
            `,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-check-circle"></i> Guardar Cambios',
                cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
                showCancelButton: true,

                showDenyButton: true,
                denyButtonText: 'Eliminar',
                focusConfirm: false,
                preConfirm: () => {
                    const tipo = document.getElementById('swal-tipo').value;
                    const fechaInicio = document.getElementById('swal-fecha-inicio').value;
                    const fechaFin = document.getElementById('swal-fecha-fin').value;

                    if (!tipo || !fechaInicio) {
                        Swal.showValidationMessage('El tipo y la fecha de inicio son obligatorios');
                        return false;
                    }

                    return { tipo: tipo, fechaInicio: fechaInicio, fechaFin: fechaFin };
                },
                customClass: {
                    denyButton: 'swal2-styled swal2-deny-custom'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Usuario quiere guardar cambios
                    const datos = result.value;

                    // Convertir fechas a formato MySQL (hora local, no UTC)
                    const fechaInicioMySQL = formatDateForMySQL(new Date(datos.fechaInicio));
                    const fechaFinMySQL = datos.fechaFin ? formatDateForMySQL(new Date(datos.fechaFin)) : null;

                    var eventUpdateData = {
                        id_agendamiento: evento.id,
                        tipo: datos.tipo,
                        new_fecha_hora: fechaInicioMySQL,
                        new_fecha_hora_fin: fechaFinMySQL,
                        action: 'edit'
                    };

                    sendEventData(URLS.UPDATE, eventUpdateData,
                        function () {
                            // Actualizar el evento en el calendario
                            evento.setProp('title', datos.tipo);
                            evento.setStart(datos.fechaInicio);
                            if (datos.fechaFin) {
                                evento.setEnd(datos.fechaFin);
                            }

                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: 'Agendamiento modificado correctamente',
                                confirmButtonText: 'Aceptar',
                                timer: 2500,
                                timerProgressBar: true
                            });
                        },
                        function (errorMessage) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo actualizar: ' + errorMessage,
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    );
                } else if (result.isDenied) {
                    // Usuario quiere eliminar el evento
                    Swal.fire({
                        title: '¿Eliminar agendamiento?',
                        text: '¿Estás seguro de que deseas eliminar este agendamiento?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((deleteResult) => {
                        if (deleteResult.isConfirmed) {
                            // Eliminar del servidor
                            fetch(URLS.DELETE + '?id=' + evento.id, {
                                method: 'GET'
                            })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.status === 'success') {
                                        // Eliminar del calendario
                                        evento.remove();
                                        Swal.fire({
                                            icon: 'success',
                                            title: '¡Eliminado!',
                                            text: 'Agendamiento eliminado correctamente',
                                            confirmButtonText: 'Aceptar',
                                            timer: 2000,
                                            timerProgressBar: true
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'No se pudo eliminar el agendamiento: ' + (result.message || 'Error desconocido'),
                                            confirmButtonText: 'Aceptar'
                                        });
                                    }
                                })
                                .catch(error => {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Error al eliminar: ' + error.message,
                                        confirmButtonText: 'Aceptar'
                                    });
                                });
                        }
                    });
                }
            });
        },

        // 1. Se ejecuta cuando un evento ya existente es MOVIDO.
        eventDrop: function (info) {
            Swal.fire({
                title: '¿Confirmar movimiento?',
                text: '¿Estás seguro de mover este agendamiento?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, mover',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 1. Preparamos los datos para la actualización (hora local, no UTC)
                    var eventUpdateData = {
                        id_agendamiento: info.event.id,
                        new_fecha_hora: formatDateForMySQL(info.event.start),
                        new_fecha_hora_fin: info.event.end ? formatDateForMySQL(info.event.end) : null,
                        action: 'move'
                    };

                    // 2. Llamada AJAX para actualizar el evento en la base de datos
                    sendEventData(URLS.UPDATE, eventUpdateData,
                        function () {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: 'Agendamiento movido correctamente',
                                confirmButtonText: 'Aceptar',
                                timer: 2500,
                                timerProgressBar: true
                            });
                        },
                        function (errorMessage, errorData) {
                            // Si es un error de disponibilidad, ya se mostró en mostrarErrorDisponibilidad()
                            if (errorData && errorData.tipo === 'disponibilidad') {
                                info.revert();
                                return;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo mover el agendamiento: ' + errorMessage,
                                confirmButtonText: 'Aceptar'
                            });
                            info.revert();
                        }
                    );
                } else {
                    info.revert();
                }
            });
        },

        // 2. Se ejecuta cuando un evento ya existente es REDIMENSIONADO.
        eventResize: function (info) {
            Swal.fire({
                title: '¿Confirmar cambio de duración?',
                text: '¿Estás seguro de cambiar la duración de este agendamiento?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 1. Preparamos los datos para la actualización (hora local, no UTC)
                    var eventUpdateData = {
                        id_agendamiento: info.event.id,
                        new_fecha_hora: formatDateForMySQL(info.event.start),
                        new_fecha_hora_fin: info.event.end ? formatDateForMySQL(info.event.end) : null,
                        action: 'resize'
                    };

                    // 2. Llamada AJAX para actualizar el evento en la base de datos
                    sendEventData(URLS.UPDATE, eventUpdateData,
                        function () {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: 'Duración del agendamiento modificada',
                                confirmButtonText: 'Aceptar',
                                timer: 2500,
                                timerProgressBar: true
                            });
                        },
                        function (errorMessage, errorData) {
                            // Si es un error de disponibilidad, ya se mostró en mostrarErrorDisponibilidad()
                            if (errorData && errorData.tipo === 'disponibilidad') {
                                info.revert();
                                return;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo redimensionar: ' + errorMessage,
                                confirmButtonText: 'Aceptar'
                            });
                            info.revert();
                        }
                    );
                } else {
                    info.revert();
                }
            });
        },

        // 3. Se ejecuta cuando un evento EXTERNO es soltado en el calendario.
        drop: async function (info) {
            // Prevenir creación automática del evento
            info.draggedEl.style.display = 'block';

            // Obtener información del subservicio arrastrado
            const subservicioIdArrastrado = info.draggedEl.getAttribute('data-subservicio-id');
            const subservicioNombre = info.draggedEl.getAttribute('data-subservicio-nombre');
            const servicioNombre = info.draggedEl.getAttribute('data-servicio-nombre');

            // Determinar fechas iniciales basadas en donde se soltó
            var fechaInicio = info.date;
            var fechaInicioStr, fechaFinStr;

            if (info.allDay) {
                // Si es todo el día, establecer hora por defecto 8:00 AM
                var fechaInicioConHora = new Date(fechaInicio);
                fechaInicioConHora.setHours(8, 0, 0);
                fechaInicioStr = formatDateForInput(fechaInicioConHora);

                // Establecer hora de fin por defecto 9:00 AM (1 hora después)
                var fechaFinConHora = new Date(fechaInicio);
                fechaFinConHora.setHours(9, 0, 0);
                fechaFinStr = formatDateForInput(fechaFinConHora);
            } else {
                fechaInicioStr = formatDateForInput(fechaInicio);

                // Agregar 1 hora a la fecha de inicio para la fecha de fin
                var fechaFin = new Date(fechaInicio);
                fechaFin.setHours(fechaFin.getHours() + 1);
                fechaFinStr = formatDateForInput(fechaFin);
            }

            // Cargar datos desde el servidor
            const propietarios = await cargarPropietarios();
            const servicios = await cargarServicios();
            const subservicios = await cargarSubservicios();

            // Encontrar el subservicio arrastrado para obtener su id_servicio
            const subservicioArrastrado = subservicios.find(sub => sub.id_subservicio == subservicioIdArrastrado);
            const servicioIdPreseleccionado = subservicioArrastrado ? subservicioArrastrado.id_servicio : null;

            // Crear opciones HTML para propietarios
            let propietariosOptions = '<option value="">Selecciona un propietario...</option>';
            propietarios.forEach(prop => {
                propietariosOptions += `<option value="${prop.id_propietario}">${prop.nombres} ${prop.apellidos}</option>`;
            });

            // Crear opciones HTML para servicios
            let serviciosOptions = '<option value="">Selecciona un servicio...</option>';
            servicios.forEach(servicio => {
                const selected = servicio.id_servicio == servicioIdPreseleccionado ? 'selected' : '';
                serviciosOptions += `<option value="${servicio.id_servicio}" ${selected}>${servicio.nombre}</option>`;
            });

            // Filtrar subservicios del servicio pre-seleccionado
            const subserviciosFiltrados = servicioIdPreseleccionado
                ? subservicios.filter(sub => sub.id_servicio == servicioIdPreseleccionado)
                : [];

            // Crear opciones HTML para subservicios
            let subserviciosOptions = '<option value="">Selecciona un subservicio...</option>';
            subserviciosFiltrados.forEach(subservicio => {
                const selected = subservicio.id_subservicio == subservicioIdArrastrado ? 'selected' : '';
                const precio = new Intl.NumberFormat('es-CO', {
                    style: 'currency',
                    currency: 'COP',
                    minimumFractionDigits: 0
                }).format(subservicio.costo);
                subserviciosOptions += `<option value="${subservicio.id_subservicio}" ${selected}>${subservicio.nombre_subservicio} - ${precio}</option>`;
            });

            Swal.fire({
                title: 'Nuevo Agendamiento',
                html: `
    <style>
        .form-container {
            text-align: left;
            margin: 20px 0;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #00304D;
            font-size: 14px;
        }
        
        .form-label i {
            margin-right: 6px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #00304D;
            box-shadow: 0 0 0 3px rgba(0, 48, 77, 0.1);
        }
        
        .form-control:disabled {
            background-color: #f3f4f6;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .form-textarea {
            width: 100%;
            min-height: 80px;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            resize: vertical;
            font-family: inherit;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-textarea:focus {
            outline: none;
            border-color: #00304D;
            box-shadow: 0 0 0 3px rgba(0, 48, 77, 0.1);
        }
        
        /* Scrollbar personalizado */
        .form-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .form-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .form-container::-webkit-scrollbar-thumb {
            background: #00304D;
            border-radius: 10px;
        }
        
        .form-container::-webkit-scrollbar-thumb:hover {
            background: #002038;
        }
    </style>
    
    <div class="form-container">
        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-person-fill"></i> Propietario: *
            </label>
            <select id="swal-propietario" class="form-control">
                ${propietariosOptions}
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-heart-fill"></i> Mascota: *
            </label>
            <select id="swal-mascota" class="form-control" disabled>
                <option value="">Primero selecciona un propietario...</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-briefcase-fill"></i> Servicio: *
            </label>
            <select id="swal-servicio" class="form-control">
                ${serviciosOptions}
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-list-check"></i> Subservicio: *
            </label>
            <select id="swal-subservicio" class="form-control" ${servicioIdPreseleccionado ? '' : 'disabled'}>
                ${subserviciosOptions}
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-clock"></i> Fecha y Hora de Inicio: *
            </label>
            <input id="swal-fecha-inicio" type="datetime-local" class="form-control" value="${fechaInicioStr}">
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-clock-history"></i> Fecha y Hora de Fin: *
            </label>
            <input id="swal-fecha-fin" type="datetime-local" class="form-control" value="${fechaFinStr}">
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <i class="bi bi-card-text"></i> Observaciones (opcional):
            </label>
            <textarea id="swal-observaciones" class="form-textarea" 
                    placeholder="Agrega observaciones o detalles adicionales..."></textarea>
        </div>
    </div>
`,
                showCancelButton: true,
                confirmButtonText: 'Crear Agendamiento',
                cancelButtonText: 'Cancelar',
                width: '700px',
                focusConfirm: false,
                didOpen: () => {
                    // Agregar evento al cambiar el propietario
                    const propietarioSelect = document.getElementById('swal-propietario');
                    const mascotaSelect = document.getElementById('swal-mascota');

                    propietarioSelect.addEventListener('change', async function () {
                        const idPropietario = this.value;

                        if (idPropietario) {
                            mascotaSelect.disabled = true;
                            mascotaSelect.innerHTML = '<option value="">Cargando mascotas...</option>';

                            const mascotas = await cargarMascotasPorPropietario(idPropietario);

                            let mascotasOptions = '<option value="">Selecciona una mascota...</option>';
                            mascotas.forEach(mascota => {
                                mascotasOptions += `<option value="${mascota.id_paciente}">${mascota.nombre} (${mascota.especie})</option>`;
                            });

                            mascotaSelect.innerHTML = mascotasOptions;
                            mascotaSelect.disabled = false;
                        } else {
                            mascotaSelect.innerHTML = '<option value="">Primero selecciona un propietario...</option>';
                            mascotaSelect.disabled = true;
                        }
                    });

                    // Agregar evento al cambiar el servicio para filtrar subservicios
                    const servicioSelect = document.getElementById('swal-servicio');
                    const subservicioSelect = document.getElementById('swal-subservicio');

                    servicioSelect.addEventListener('change', function () {
                        const idServicio = this.value;

                        if (idServicio) {
                            // Filtrar subservicios por el servicio seleccionado
                            const subserviciosFiltrados = subservicios.filter(sub =>
                                sub.id_servicio == idServicio
                            );

                            if (subserviciosFiltrados.length > 0) {
                                let subserviciosOptions = '<option value="">Selecciona un subservicio...</option>';
                                subserviciosFiltrados.forEach(sub => {
                                    const costoCOP = new Intl.NumberFormat('es-CO', {
                                        style: 'currency',
                                        currency: 'COP',
                                        minimumFractionDigits: 0
                                    }).format(sub.costo);
                                    subserviciosOptions += `<option value="${sub.id_subservicio}">${sub.nombre_subservicio} - ${costoCOP}</option>`;
                                });

                                subservicioSelect.innerHTML = subserviciosOptions;
                                subservicioSelect.disabled = false;
                            } else {
                                subservicioSelect.innerHTML = '<option value="">No hay subservicios para este servicio</option>';
                                subservicioSelect.disabled = true;
                            }
                        } else {
                            subservicioSelect.innerHTML = '<option value="">Primero selecciona un servicio...</option>';
                            subservicioSelect.disabled = true;
                        }
                    });
                },
                preConfirm: () => {
                    const propietario = document.getElementById('swal-propietario').value;
                    const mascota = document.getElementById('swal-mascota').value;
                    const servicio = document.getElementById('swal-servicio').value;
                    const subservicio = document.getElementById('swal-subservicio').value;
                    const fechaInicio = document.getElementById('swal-fecha-inicio').value;
                    const fechaFin = document.getElementById('swal-fecha-fin').value;
                    const observaciones = document.getElementById('swal-observaciones').value;

                    if (!propietario) {
                        Swal.showValidationMessage('Debes seleccionar un propietario');
                        return false;
                    }

                    if (!mascota) {
                        Swal.showValidationMessage('Debes seleccionar una mascota');
                        return false;
                    }

                    if (!servicio) {
                        Swal.showValidationMessage('Debes seleccionar un servicio');
                        return false;
                    }

                    if (!subservicio) {
                        Swal.showValidationMessage('Debes seleccionar un subservicio');
                        return false;
                    }

                    if (!fechaInicio) {
                        Swal.showValidationMessage('Debes seleccionar un servicio');
                        return false;
                    }

                    if (!fechaInicio) {
                        Swal.showValidationMessage('Debes ingresar la fecha y hora de inicio');
                        return false;
                    }

                    if (!fechaFin) {
                        Swal.showValidationMessage('Debes ingresar la fecha y hora de fin');
                        return false;
                    }

                    // Validar que la fecha de fin sea posterior a la de inicio
                    if (new Date(fechaFin) <= new Date(fechaInicio)) {
                        Swal.showValidationMessage('La fecha de fin debe ser posterior a la fecha de inicio');
                        return false;
                    }

                    // Obtener el nombre del subservicio seleccionado
                    const selectSubservicio = document.getElementById('swal-subservicio');
                    const selectedSubservicio = selectSubservicio.options[selectSubservicio.selectedIndex];
                    const subservicioNombre = selectedSubservicio.text.split(' - ')[0]; // Extraer solo el nombre sin el costo

                    // Obtener el color basado en el nombre del servicio
                    const servicioColores = {
                        'Consulta general': '#0A932C',
                        'Exámenes de laboratorio': '#93BEDF',
                        'Baño y peluquería': '#9DE795',
                        'Otro': '#6c757d'
                    };
                    const color = servicioColores[servicioNombre] || '#6c757d';

                    return {
                        propietario: propietario,
                        mascota: mascota,
                        servicio: servicio,
                        subservicio: subservicio,
                        servicioNombre: servicioNombre,
                        subservicioNombre: subservicioNombre,
                        fechaInicio: fechaInicio,
                        fechaFin: fechaFin,
                        observaciones: observaciones,
                        color: color
                    };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const datos = result.value;

                    // Convertir fechas a formato MySQL (hora local, no UTC)
                    const fechaInicioMySQL = formatDateForMySQL(new Date(datos.fechaInicio));
                    const fechaFinMySQL = formatDateForMySQL(new Date(datos.fechaFin));

                    // Preparamos los datos a enviar al servidor
                    var newEventData = {
                        id_paciente: parseInt(datos.mascota),
                        id_servicio: parseInt(datos.servicio), // ID servicio padre
                        id_subservicio: parseInt(datos.subservicio), // ID subservicio
                        id_especialidad: 1, // Especialidad por defecto
                        tipo: datos.subservicioNombre, // Usar el nombre del subservicio como tipo
                        observaciones: datos.observaciones,
                        fecha_hora: fechaInicioMySQL,
                        fecha_hora_fin: fechaFinMySQL,
                        estado: 'Pendiente',
                        allDay: 0
                    };

                    // Llamada AJAX para guardar en la base de datos
                    sendEventData(URLS.CREATE, newEventData,
                        function (response) {
                            calendar.addEvent({
                                id: response.id,
                                title: datos.servicioNombre,
                                start: datos.fechaInicio,
                                end: datos.fechaFin,
                                allDay: false,
                                backgroundColor: datos.color,
                                borderColor: datos.color
                            });

                            Swal.fire({
                                icon: 'success',
                                title: '¡Agendamiento creado!',
                                text: 'El agendamiento se ha registrado correctamente',
                                confirmButtonText: 'Aceptar',
                                timer: 2500,
                                timerProgressBar: true,
                                customClass: {
                                    confirmButton: 'swal2-styled swal2-confirm-custom'
                                }
                            });
                        },
                        function (errorMessage, errorData) {
                            // Si es un error de disponibilidad, ya se mostró en mostrarErrorDisponibilidad()
                            if (errorData && errorData.tipo === 'disponibilidad') {
                                return; // No mostrar alerta duplicada
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo crear el agendamiento: ' + errorMessage,
                                confirmButtonText: 'Aceptar',
                                customClass: {
                                    confirmButton: 'swal2-styled swal2-confirm-custom'
                                }
                            });
                        }
                    );
                }
            });
        }

    }); // Cierre del objeto de configuración del calendario

    // Dibujar el calendario
    calendar.render();

    // ╔═══════════════════════════════════════════════════════════════════════╗
    // ║                  FUNCIONALIDADES DE BARRA DE ACCIONES                ║
    // ╚═══════════════════════════════════════════════════════════════════════╝

    // ═══════════════════════════════════════════════════════════════════════
    //  INICIALIZACIÓN: CARGAR VETERINARIOS EN EL SELECT
    // ═══════════════════════════════════════════════════════════════════════
    const selectVeterinario = document.querySelector('.select-veterinario');

    if (selectVeterinario) {
        // Cargar veterinarios desde la base de datos
        cargarVeterinarios().then(veterinarios => {
            console.log('Veterinarios cargados:', veterinarios);

            // Limpiar el select
            selectVeterinario.innerHTML = '<option value="">Todos los veterinarios</option>';

            if (veterinarios && veterinarios.length > 0) {
                // Agregar cada veterinario al select
                veterinarios.forEach(vet => {
                    const option = document.createElement('option');
                    option.value = vet.id_usuario;
                    option.textContent = `${vet.nombres} ${vet.apellidos}`;
                    selectVeterinario.appendChild(option);
                });
                console.log(`✅ ${veterinarios.length} veterinarios cargados en el select`);
            } else {
                console.warn('⚠️ No se encontraron veterinarios en la base de datos');
            }
        }).catch(error => {
            console.error('❌ Error al inicializar veterinarios:', error);
            // Mostrar "Todos" aunque haya error
            selectVeterinario.innerHTML = '<option value="">Todos los veterinarios</option>';
        });
    } else {
        console.error('❌ No se encontró el elemento .select-veterinario');
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  FUNCIÓN 1: CAMBIO DE VISTAS DEL CALENDARIO
    // ═══════════════════════════════════════════════════════════════════════
    const botonesVista = document.querySelectorAll('.boton-vista');

    botonesVista.forEach(boton => {
        boton.addEventListener('click', function () {
            const vista = this.getAttribute('data-vista');

            // Remover clase active de todos los botones
            botonesVista.forEach(btn => btn.classList.remove('active'));

            // Agregar clase active al botón clickeado
            this.classList.add('active');

            // Cambiar vista del calendario
            switch (vista) {
                case 'calendario':
                    calendar.changeView('dayGridMonth');
                    break;
                case 'lista':
                    calendar.changeView('listMonth');
                    break;
                case 'dia':
                    calendar.changeView('timeGridDay');
                    break;
            }
        });
    });

    // Cargar disponibilidad y aplicarla al calendario
    cargarDisponibilidad().then((businessHours) => {
        if (businessHours.length > 0) {
            calendar.setOption('businessHours', businessHours);

            const disponibilidadBackground = businessHours.map((bh) => ({
                daysOfWeek: bh.daysOfWeek,
                startTime: bh.startTime,
                endTime: bh.endTime,
                display: 'background',
                color: '#c8f7d2'
            }));

            calendar.addEventSource(disponibilidadBackground);
        }
    });

    // ═══════════════════════════════════════════════════════════════════════
    //  FUNCIÓN 2: FILTRO POR VETERINARIO (Con recarga desde servidor)
    // ═══════════════════════════════════════════════════════════════════════
    if (selectVeterinario) {
        selectVeterinario.addEventListener('change', async function () {
            const veterinarioId = this.value;

            console.log('🔄 Filtrando por veterinario:', veterinarioId || 'Todos');

            try {
                // Construir URL con o sin filtro
                let url = URLS.LOAD;
                if (veterinarioId) {
                    url += '?id_usuario=' + veterinarioId;
                }

                console.log('📡 Cargando eventos desde:', url);

                // Hacer petición al servidor
                const response = await fetch(url);
                const eventos = await response.json();

                console.log('📦 Eventos recibidos:', eventos.length);

                // Limpiar todos los eventos del calendario
                calendar.removeAllEvents();

                // Agregar los nuevos eventos filtrados
                eventos.forEach(evento => {
                    calendar.addEvent(evento);
                });

                console.log('✅ Calendario actualizado con', eventos.length, 'eventos');

            } catch (error) {
                console.error('❌ Error al filtrar eventos:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los eventos del veterinario',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  FUNCIÓN 3: ABRIR MODAL NUEVA CITA (Botón "Nueva Cita")
    // ═══════════════════════════════════════════════════════════════════════
    window.abrirModalNuevaCita = async function () {
        // Obtener fecha y hora actual
        const ahora = new Date();

        // Establecer hora de inicio (redondear a la próxima hora)
        const fechaInicio = new Date(ahora);
        fechaInicio.setMinutes(0, 0, 0);
        if (ahora.getMinutes() > 0) {
            fechaInicio.setHours(fechaInicio.getHours() + 1);
        }

        // Establecer hora de fin (1 hora después)
        const fechaFin = new Date(fechaInicio);
        fechaFin.setHours(fechaFin.getHours() + 1);

        // Formatear fechas para los inputs
        const fechaInicioStr = formatDateForInput(fechaInicio);
        const fechaFinStr = formatDateForInput(fechaFin);

        // Cargar datos desde el servidor
        const propietarios = await cargarPropietarios();
        const servicios = await cargarServicios();
        const subservicios = await cargarSubservicios();

        // Crear opciones HTML para propietarios
        let propietariosOptions = '<option value="">Selecciona un propietario...</option>';
        propietarios.forEach(prop => {
            propietariosOptions += `<option value="${prop.id_propietario}">${prop.nombres} ${prop.apellidos}</option>`;
        });

        // Crear opciones HTML para servicios
        let serviciosOptions = '<option value="">Selecciona un servicio...</option>';
        servicios.forEach(servicio => {
            serviciosOptions += `<option value="${servicio.id_servicio}">${servicio.nombre}</option>`;
        });

        // Mostrar SweetAlert con formulario
        Swal.fire({
            title: '<i class="bi bi-calendar-plus" style="color: #0a932c;"></i> Nueva Cita',
            html: `
                <style>
                    .form-agendamiento {
                        max-height: 500px;
                        overflow-y: auto;
                        padding: 0 5px;
                        text-align: left;
                    }
                    
                    .form-agendamiento::-webkit-scrollbar {
                        width: 8px;
                    }
                    
                    .form-agendamiento::-webkit-scrollbar-track {
                        background: #f1f1f1;
                        border-radius: 10px;
                    }
                    
                    .form-agendamiento::-webkit-scrollbar-thumb {
                        background: #888;
                        border-radius: 10px;
                    }
                    
                    .form-agendamiento::-webkit-scrollbar-thumb:hover {
                        background: #555;
                    }
                    
                    .form-group-ag {
                        margin-bottom: 20px;
                    }
                    
                    .form-label-ag {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        margin-bottom: 8px;
                        font-weight: 600;
                        color: #00304D;
                        font-size: 14px;
                    }
                    
                    .form-label-ag i {
                        font-size: 16px;
                        color: #0a932c;
                    }
                    
                    .form-label-ag .required {
                        color: #e74c3c;
                        margin-left: 2px;
                    }
                    
                    .form-control-ag {
                        width: 100%;
                        padding: 10px 14px;
                        border: 2px solid #e0e0e0;
                        border-radius: 8px;
                        font-size: 14px;
                        font-family: inherit;
                        transition: all 0.3s ease;
                        background: #ffffff;
                        box-sizing: border-box;
                    }
                    
                    .form-control-ag:focus {
                        outline: none;
                        border-color: #0a932c;
                        box-shadow: 0 0 0 3px rgba(10, 147, 44, 0.1);
                        background: #ffffff;
                    }
                    
                    .form-control-ag:disabled {
                        background: #f5f5f5;
                        cursor: not-allowed;
                        color: #999;
                    }
                    
                    textarea.form-control-ag {
                        resize: vertical;
                        min-height: 80px;
                        font-family: inherit;
                        line-height: 1.5;
                    }
                    
                    .form-helper {
                        font-size: 12px;
                        color: #666;
                        margin-top: 5px;
                        display: flex;
                        align-items: center;
                        gap: 4px;
                    }
                    
                    .form-helper i {
                        font-size: 11px;
                    }
                    
                    .form-divider {
                        height: 1px;
                        background: linear-gradient(to right, transparent, #e0e0e0, transparent);
                        margin: 20px 0;
                    }
                </style>
                
                <div class="form-agendamiento">
                    <div class="form-group-ag">
                        <label class="form-label-ag">
                            <i class="bi bi-person-fill"></i>
                            Propietario
                            <span class="required">*</span>
                        </label>
                        <select id="swal-propietario" class="form-control-ag">
                            ${propietariosOptions}
                        </select>
                        <div class="form-helper">
                            <i class="bi bi-info-circle"></i>
                            Selecciona el propietario de la mascota
                        </div>
                    </div>
                    
                    <div class="form-group-ag">
                        <label class="form-label-ag">
                            <i class="bi bi-heart-fill"></i>
                            Mascota
                            <span class="required">*</span>
                        </label>
                        <select id="swal-mascota" class="form-control-ag" disabled>
                            <option value="">Primero selecciona un propietario...</option>
                        </select>
                    </div>
                    
                    <div class="form-divider"></div>
                    
                    <div class="form-group-ag">
                        <label class="form-label-ag">
                            <i class="bi bi-briefcase-fill"></i>
                            Servicio
                            <span class="required">*</span>
                        </label>
                        <select id="swal-servicio" class="form-control-ag">
                            ${serviciosOptions}
                        </select>
                        <div class="form-helper">
                            <i class="bi bi-info-circle"></i>
                            Selecciona el tipo de servicio principal
                        </div>
                    </div>
                    
                    <div class="form-group-ag">
                        <label class="form-label-ag">
                            <i class="bi bi-list-check"></i>
                            Subservicio
                            <span class="required">*</span>
                        </label>
                        <select id="swal-subservicio" class="form-control-ag" disabled>
                            <option value="">Primero selecciona un servicio...</option>
                        </select>
                        <div class="form-helper">
                            <i class="bi bi-info-circle"></i>
                            Selecciona el subservicio específico
                        </div>
                    </div>
                    
                    <div class="form-divider"></div>
                    
                    <div class="form-group-ag">
                        <label class="form-label-ag">
                            <i class="bi bi-calendar-event"></i>
                            Fecha y Hora de Inicio
                            <span class="required">*</span>
                        </label>
                        <input type="datetime-local" id="swal-fecha-inicio" class="form-control-ag" 
                            value="${fechaInicioStr}">
                    </div>
                    
                    <div class="form-group-ag">
                        <label class="form-label-ag">
                            <i class="bi bi-calendar-check"></i>
                            Fecha y Hora de Fin
                            <span class="required">*</span>
                        </label>
                        <input type="datetime-local" id="swal-fecha-fin" class="form-control-ag" 
                            value="${fechaFinStr}">
                        <div class="form-helper">
                            <i class="bi bi-clock"></i>
                            La duración del servicio se calculará automáticamente
                        </div>
                    </div>
                    
                    <div class="form-divider"></div>
                    
                    <div class="form-group-ag">
                        <label class="form-label-ag">
                            <i class="bi bi-card-text"></i>
                            Observaciones
                        </label>
                        <textarea id="swal-observaciones" class="form-control-ag" 
                                placeholder="Agrega observaciones o detalles adicionales..."></textarea>
                    </div>
                </div>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check-circle"></i> Crear Cita',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
            customClass: {
                confirmButton: 'swal2-styled swal2-confirm-custom',
                cancelButton: 'swal2-styled swal2-cancel-custom'
            },
            focusConfirm: false,
            didOpen: () => {
                // Evento para cargar mascotas cuando se seleccione propietario
                const selectPropietario = document.getElementById('swal-propietario');
                const selectMascota = document.getElementById('swal-mascota');

                selectPropietario.addEventListener('change', async function () {
                    const idPropietario = this.value;

                    if (idPropietario) {
                        selectMascota.disabled = true;
                        selectMascota.innerHTML = '<option value="">Cargando mascotas...</option>';

                        const mascotas = await cargarMascotasPorPropietario(idPropietario);

                        let mascotasOptions = '<option value="">Selecciona una mascota...</option>';
                        mascotas.forEach(mascota => {
                            mascotasOptions += `<option value="${mascota.id_paciente}">${mascota.nombre} (${mascota.especie})</option>`;
                        });

                        selectMascota.innerHTML = mascotasOptions;
                        selectMascota.disabled = false;
                    } else {
                        selectMascota.disabled = true;
                        selectMascota.innerHTML = '<option value="">Primero selecciona un propietario...</option>';
                    }
                });

                // Agregar evento al cambiar el servicio para filtrar subservicios
                const servicioSelect = document.getElementById('swal-servicio');
                const subservicioSelect = document.getElementById('swal-subservicio');

                servicioSelect.addEventListener('change', function () {
                    const idServicio = this.value;

                    if (idServicio) {
                        // Filtrar subservicios por el servicio seleccionado
                        const subserviciosFiltrados = subservicios.filter(sub =>
                            sub.id_servicio == idServicio
                        );

                        if (subserviciosFiltrados.length > 0) {
                            let subserviciosOptions = '<option value="">Selecciona un subservicio...</option>';
                            subserviciosFiltrados.forEach(sub => {
                                const costoCOP = new Intl.NumberFormat('es-CO', {
                                    style: 'currency',
                                    currency: 'COP',
                                    minimumFractionDigits: 0
                                }).format(sub.costo);
                                subserviciosOptions += `<option value="${sub.id_subservicio}">${sub.nombre_subservicio} - ${costoCOP}</option>`;
                            });

                            subservicioSelect.innerHTML = subserviciosOptions;
                            subservicioSelect.disabled = false;
                        } else {
                            subservicioSelect.innerHTML = '<option value="">No hay subservicios para este servicio</option>';
                            subservicioSelect.disabled = true;
                        }
                    } else {
                        subservicioSelect.innerHTML = '<option value="">Primero selecciona un servicio...</option>';
                        subservicioSelect.disabled = true;
                    }
                });
            },
            preConfirm: () => {
                const propietario = document.getElementById('swal-propietario').value;
                const mascota = document.getElementById('swal-mascota').value;
                const servicio = document.getElementById('swal-servicio').value;
                const subservicio = document.getElementById('swal-subservicio').value;
                const fechaInicio = document.getElementById('swal-fecha-inicio').value;
                const fechaFin = document.getElementById('swal-fecha-fin').value;
                const observaciones = document.getElementById('swal-observaciones').value;

                // Validaciones
                if (!propietario) {
                    Swal.showValidationMessage('Debes seleccionar un propietario');
                    return false;
                }

                if (!mascota) {
                    Swal.showValidationMessage('Debes seleccionar una mascota');
                    return false;
                }

                if (!servicio) {
                    Swal.showValidationMessage('Debes seleccionar un servicio');
                    return false;
                }

                if (!subservicio) {
                    Swal.showValidationMessage('Debes seleccionar un subservicio');
                    return false;
                }

                if (!fechaInicio) {
                    Swal.showValidationMessage('Debes ingresar la fecha y hora de inicio');
                    return false;
                }

                if (!fechaFin) {
                    Swal.showValidationMessage('Debes ingresar la fecha y hora de fin');
                    return false;
                }

                // Validar que la fecha de fin sea posterior a la de inicio
                if (new Date(fechaFin) <= new Date(fechaInicio)) {
                    Swal.showValidationMessage('La fecha de fin debe ser posterior a la fecha de inicio');
                    return false;
                }

                // Obtener el nombre del subservicio seleccionado
                const selectSubservicio = document.getElementById('swal-subservicio');
                const selectedSubservicio = selectSubservicio.options[selectSubservicio.selectedIndex];
                const subservicioNombre = selectedSubservicio.text.split(' - ')[0];

                // Obtener el nombre del servicio para el color
                const selectServicio = document.getElementById('swal-servicio');
                const servicioNombre = selectServicio.options[selectServicio.selectedIndex].text;

                // Obtener el color basado en el nombre del servicio
                const servicioColores = {
                    'Consulta general': '#0A932C',
                    'Exámenes de laboratorio': '#93BEDF',
                    'Baño y peluquería': '#9DE795',
                    'Otro': '#6c757d'
                };
                const color = servicioColores[servicioNombre] || '#6c757d';

                return {
                    propietario: propietario,
                    mascota: mascota,
                    servicio: servicio,
                    subservicio: subservicio,
                    servicioNombre: servicioNombre,
                    subservicioNombre: subservicioNombre,
                    fechaInicio: fechaInicio,
                    fechaFin: fechaFin,
                    observaciones: observaciones,
                    color: color
                };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const datos = result.value;

                console.log('Datos del formulario:', datos);

                // Convertir fechas a formato MySQL (hora local, no UTC)
                const fechaInicioMySQL = formatDateForMySQL(new Date(datos.fechaInicio));
                const fechaFinMySQL = formatDateForMySQL(new Date(datos.fechaFin));

                // Preparar datos a enviar al servidor
                var newEventData = {
                    id_paciente: parseInt(datos.mascota),
                    id_servicio: parseInt(datos.servicio), // ID del servicio principal
                    id_subservicio: parseInt(datos.subservicio), // ID del subservicio específico
                    id_especialidad: 1, // Especialidad por defecto
                    tipo: datos.subservicioNombre, // Usar el nombre del subservicio como tipo
                    observaciones: datos.observaciones,
                    fecha_hora: fechaInicioMySQL,
                    fecha_hora_fin: fechaFinMySQL,
                    estado: 'Pendiente',
                    allDay: 0
                };

                console.log('Datos a enviar al servidor:', newEventData);

                // Llamada AJAX para guardar en la base de datos
                sendEventData(URLS.CREATE, newEventData,
                    function (response) {
                        // Agregar evento al calendario
                        calendar.addEvent({
                            id: response.id,
                            title: datos.subservicioNombre,
                            start: datos.fechaInicio,
                            end: datos.fechaFin,
                            backgroundColor: datos.color,
                            borderColor: datos.color,
                            allDay: false
                        });

                        Swal.fire({
                            icon: 'success',
                            title: '¡Cita Creada!',
                            text: 'La cita ha sido registrada correctamente',
                            confirmButtonText: 'Aceptar',
                            timer: 3000,
                            timerProgressBar: true
                        });
                    },
                    function (errorMessage, errorData) {
                        // Si es un error de disponibilidad, ya se mostró en mostrarErrorDisponibilidad()
                        if (errorData && errorData.tipo === 'disponibilidad') {
                            return; // No mostrar alerta duplicada
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo crear la cita: ' + errorMessage,
                            confirmButtonText: 'Aceptar'
                        });
                    }
                );
            }
        });
    };

});
