// calendar.js - Lógica de FullCalendar y manejo de Agendamiento (MVC)

// --- DEFINICIÓN DE RUTAS LÓGICAS ---
// Basado en tu archivo 'calendarioController.php', las rutas deben apuntar a ese controlador.
const URLS = {
    // 1. CARGAR EVENTOS (GET): Llama al método que trae todos los agendamientos.
    LOAD:   '/vetwilling/calendario/loadEvents', 
    
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
    GET_SERVICIOS: '/vetwilling/calendario/getServicios'
};


// Esperamos a que todo el contenido HTML de la página se haya cargado.
document.addEventListener('DOMContentLoaded', function() {

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
            
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error('Status ' + response.status + ': ' + response.statusText + ' - ' + text);
                });
            }
            return response.json(); 
        })
        .then(result => {
            console.log('Result:', result);
            if (result.status === 'success') {
                console.log('Operación exitosa:', result.message);
                if (successCallback) { successCallback(result); }
            } else {
                console.error('Error lógico en el servidor:', result.message);
                if (errorCallback) { errorCallback(result.message); }
            }
        })
        .catch(error => {
            console.error('Error de comunicación AJAX:', error);
            if (errorCallback) { errorCallback(error.message); }
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

    // Mapeo de servicios a colores
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

    // Mapeo de emojis por servicio
    const servicioEmojis = {
        'Consulta General': '🩺',
        'Vacunación': '💉',
        'Cirugía': '⚕️',
        'Control': '📋',
        'Emergencia': '🚨',
        'Desparasitación': '🐛',
        'Peluquería': '✂️',
        'Baño': '🛁',
        'Otro': '📌'
    };

    // --- Parte de Arrastre de Eventos Externos (Draggable) ---
    async function initializeExternalEvents() {
        var containerEl = document.getElementById('external-events');
        
        if (containerEl) {
            // Cargar servicios desde la base de datos
            const servicios = await cargarServicios();
            
            // Limpiar contenedor
            containerEl.innerHTML = '<h4>Servicios Disponibles</h4>';
            
            // Crear eventos arrastrables por cada servicio
            servicios.forEach(servicio => {
                const color = servicioColores[servicio.nombre] || '#6c757d';
                const emoji = servicioEmojis[servicio.nombre] || '📌';
                const className = 'fc-event-' + servicio.nombre.toLowerCase().replace(/\s+/g, '-').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                
                const eventDiv = document.createElement('div');
                eventDiv.className = `fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event ${className}`;
                eventDiv.setAttribute('data-duration', '01:00');
                eventDiv.setAttribute('data-servicio-id', servicio.id_servicio);
                eventDiv.setAttribute('data-servicio-nombre', servicio.nombre);
                eventDiv.style.backgroundColor = color;
                eventDiv.style.borderColor = color;
                
                const mainDiv = document.createElement('div');
                mainDiv.className = 'fc-event-main';
                mainDiv.textContent = `${emoji} ${servicio.nombre}`;
                
                eventDiv.appendChild(mainDiv);
                containerEl.appendChild(eventDiv);
            });
            
            // Inicializar draggable
            new FullCalendar.Draggable(containerEl, {
                itemSelector: '.fc-event',
                eventData: function(eventEl) {
                    return {
                        title: eventEl.getAttribute('data-servicio-nombre'),
                        duration: eventEl.getAttribute('data-duration'),
                        extendedProps: {
                            servicioId: eventEl.getAttribute('data-servicio-id'),
                            servicioNombre: eventEl.getAttribute('data-servicio-nombre')
                        }
                    };
                }
            });
        }
    }

    initializeExternalEvents();

    // --- Inicialización Principal del Calendario ---
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        
        // --- CONFIGURACIÓN DE APARIENCIA Y VISTA ---
        locale: 'es', 
        themeSystem: 'bootstrap5', 
        initialView: 'dayGridMonth', 
        headerToolbar: {
            left: 'prev,next today', 
            center: 'title', 
            right: 'dayGridMonth,timeGridWeek,timeGridDay' 
        },
        
        // --- CARGA DE EVENTOS ---
        events: URLS.LOAD, 
        
        // --- INTERACCIÓN Y CREACIÓN DE EVENTOS (Selectable) ---
        selectable: true, 
        
        select: async function(info) {
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
                fechaInicioStr = fechaInicioConHora.toISOString().slice(0, 16);
                
                // Establecer hora de fin por defecto 9:00 AM (1 hora después)
                var fechaFinConHora = new Date(fechaInicio);
                fechaFinConHora.setHours(9, 0, 0);
                fechaFinStr = fechaFinConHora.toISOString().slice(0, 16);
            } else {
                fechaInicioStr = fechaInicio.toISOString().slice(0, 16);
                fechaFinStr = fechaFin.toISOString().slice(0, 16);
            }

            // Cargar datos desde el servidor
            const propietarios = await cargarPropietarios();
            const servicios = await cargarServicios();

            // Crear opciones HTML para propietarios
            let propietariosOptions = '<option value="">Selecciona un propietario...</option>';
            propietarios.forEach(prop => {
                propietariosOptions += `<option value="${prop.id_propietario}">${prop.nombres} ${prop.apellidos}</option>`;
            });

            // Crear opciones HTML para servicios
            let serviciosOptions = '<option value="">Selecciona un servicio...</option>';
            servicios.forEach(servicio => {
                serviciosOptions += `<option value="${servicio.id_servicio}">${servicio.nombre} - $${parseFloat(servicio.costo).toLocaleString('es-CO')}</option>`;
            });

            Swal.fire({
                title: 'Nuevo Agendamiento',
                html: `
                    <div style="text-align: left; margin: 20px 0; max-height: 500px; overflow-y: auto;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-person-fill"></i> Propietario: *
                        </label>
                        <select id="swal-propietario" class="swal2-input" style="margin: 0 0 20px 0; width: 90%; padding: 10px;">
                            ${propietariosOptions}
                        </select>
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-heart-fill"></i> Mascota: *
                        </label>
                        <select id="swal-mascota" class="swal2-input" style="margin: 0 0 20px 0; width: 90%; padding: 10px;" disabled>
                            <option value="">Primero selecciona un propietario...</option>
                        </select>
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-briefcase-fill"></i> Servicio: *
                        </label>
                        <select id="swal-servicio" class="swal2-input" style="margin: 0 0 20px 0; width: 90%; padding: 10px;">
                            ${serviciosOptions}
                        </select>
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-clock"></i> Fecha y Hora de Inicio: *
                        </label>
                        <input id="swal-fecha-inicio" type="datetime-local" class="swal2-input" 
                               value="${fechaInicioStr}" style="margin: 0 0 20px 0; width: 90%;">
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-clock-history"></i> Fecha y Hora de Fin: *
                        </label>
                        <input id="swal-fecha-fin" type="datetime-local" class="swal2-input" 
                               value="${fechaFinStr}" style="margin: 0 0 20px 0; width: 90%;">
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-card-text"></i> Observaciones (opcional):
                        </label>
                        <textarea id="swal-observaciones" class="swal2-textarea" 
                                  placeholder="Agrega observaciones o detalles adicionales..." 
                                  style="margin: 0; width: 90%; min-height: 80px; resize: vertical;"></textarea>
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
                    
                    propietarioSelect.addEventListener('change', async function() {
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
                },
                preConfirm: () => {
                    const propietario = document.getElementById('swal-propietario').value;
                    const mascota = document.getElementById('swal-mascota').value;
                    const servicio = document.getElementById('swal-servicio').value;
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
                    const selectElement = document.getElementById('swal-servicio');
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
                    
                    // Convertir fechas a ISO format
                    const fechaInicioISO = new Date(datos.fechaInicio).toISOString();
                    const fechaFinISO = new Date(datos.fechaFin).toISOString();
                    
                    // 1. Preparamos los datos a enviar al servidor
                    var newEventData = {
                        id_propietario: parseInt(datos.propietario),
                        id_paciente: parseInt(datos.mascota),
                        id_servicio: parseInt(datos.servicio),
                        tipo: datos.servicioNombre, // Usar el nombre del servicio como tipo
                        observaciones: datos.observaciones,
                        fecha_hora: fechaInicioISO,
                        fecha_hora_fin: fechaFinISO,
                        estado: 'Pendiente',
                        allDay: 0 // Siempre será un evento con hora específica
                    };

                    // 2. Llamada AJAX para guardar en la base de datos
                    sendEventData(URLS.CREATE, newEventData, 
                        // Función de Éxito
                        function(response) {
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
                                        <p><strong>${datos.tipo}</strong> creado correctamente</p>
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
                        function(errorMessage) {
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
            calendar.unselect();
        },
        
        // --- MODIFICACIÓN DE EVENTOS (editable) ---
        editable: true, 
        droppable: true, 

        // EVENTO CLICK: Se ejecuta cuando se hace clic en un evento
        eventClick: function(info) {
            // Obtener información del evento
            var evento = info.event;
            var fechaInicio = evento.start;
            var fechaFin = evento.end;
            
            // Formatear las fechas para los inputs
            var fechaInicioStr = fechaInicio.toISOString().slice(0, 16); // YYYY-MM-DDTHH:MM
            var fechaFinStr = fechaFin ? fechaFin.toISOString().slice(0, 16) : '';

            Swal.fire({
                title: 'Editar Agendamiento',
                html: `
                    <div style="text-align: left; margin: 20px 0;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #00304D;">
                            Tipo de Servicio:
                        </label>
                        <input id="swal-tipo" class="swal2-input" value="${evento.title}" 
                               placeholder="Tipo de servicio" style="margin: 0 0 15px 0; width: 90%;">
                        
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #00304D;">
                            Fecha y Hora de Inicio:
                        </label>
                        <input id="swal-fecha-inicio" type="datetime-local" class="swal2-input" 
                               value="${fechaInicioStr}" style="margin: 0 0 15px 0; width: 90%;">
                        
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #00304D;">
                            Fecha y Hora de Fin:
                        </label>
                        <input id="swal-fecha-fin" type="datetime-local" class="swal2-input" 
                               value="${fechaFinStr}" placeholder="Opcional" style="margin: 0; width: 90%;">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar Cambios',
                cancelButtonText: 'Cancelar',
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
                    
                    // Convertir fechas a ISO format
                    const fechaInicioISO = new Date(datos.fechaInicio).toISOString();
                    const fechaFinISO = datos.fechaFin ? new Date(datos.fechaFin).toISOString() : null;
                    
                    var eventUpdateData = {
                        id_agendamiento: evento.id,
                        tipo: datos.tipo,
                        new_fecha_hora: fechaInicioISO,
                        new_fecha_hora_fin: fechaFinISO,
                        action: 'edit'
                    };

                    sendEventData(URLS.UPDATE, eventUpdateData,
                        function() {
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
                        function(errorMessage) {
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
        eventDrop: function(info) {
            Swal.fire({
                title: '¿Confirmar movimiento?',
                text: '¿Estás seguro de mover este agendamiento?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, mover',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 1. Preparamos los datos para la actualización
                    var eventUpdateData = {
                        id_agendamiento: info.event.id,
                        new_fecha_hora: info.event.start.toISOString(), 
                        new_fecha_hora_fin: info.event.end ? info.event.end.toISOString() : null, 
                        action: 'move' 
                    };

                    // 2. Llamada AJAX para actualizar el evento en la base de datos
                    sendEventData(URLS.UPDATE, eventUpdateData, 
                        function() {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: 'Agendamiento movido correctamente',
                                confirmButtonText: 'Aceptar',
                                timer: 2500,
                                timerProgressBar: true
                            });
                        },
                        function(errorMessage) {
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
        eventResize: function(info) {
            Swal.fire({
                title: '¿Confirmar cambio de duración?',
                text: '¿Estás seguro de cambiar la duración de este agendamiento?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 1. Preparamos los datos para la actualización
                    var eventUpdateData = {
                        id_agendamiento: info.event.id,
                        new_fecha_hora: info.event.start.toISOString(),
                        new_fecha_hora_fin: info.event.end ? info.event.end.toISOString() : null,
                        action: 'resize'
                    };

                    // 2. Llamada AJAX para actualizar el evento en la base de datos
                    sendEventData(URLS.UPDATE, eventUpdateData, 
                        function() {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: 'Duración del agendamiento modificada',
                                confirmButtonText: 'Aceptar',
                                timer: 2500,
                                timerProgressBar: true
                            });
                        },
                        function(errorMessage) {
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
        drop: async function(info) {
            // Prevenir creación automática del evento
            info.draggedEl.style.display = 'block';
            
            // Obtener información del servicio arrastrado
            const servicioId = info.draggedEl.getAttribute('data-servicio-id');
            const servicioNombre = info.draggedEl.getAttribute('data-servicio-nombre');
            
            // Determinar fechas iniciales basadas en donde se soltó
            var fechaInicio = info.date;
            var fechaInicioStr, fechaFinStr;
            
            if (info.allDay) {
                // Si es todo el día, establecer hora por defecto 8:00 AM
                var fechaInicioConHora = new Date(fechaInicio);
                fechaInicioConHora.setHours(8, 0, 0);
                fechaInicioStr = fechaInicioConHora.toISOString().slice(0, 16);
                
                // Establecer hora de fin por defecto 9:00 AM (1 hora después)
                var fechaFinConHora = new Date(fechaInicio);
                fechaFinConHora.setHours(9, 0, 0);
                fechaFinStr = fechaFinConHora.toISOString().slice(0, 16);
            } else {
                fechaInicioStr = fechaInicio.toISOString().slice(0, 16);
                
                // Agregar 1 hora a la fecha de inicio para la fecha de fin
                var fechaFin = new Date(fechaInicio);
                fechaFin.setHours(fechaFin.getHours() + 1);
                fechaFinStr = fechaFin.toISOString().slice(0, 16);
            }

            // Cargar datos desde el servidor
            const propietarios = await cargarPropietarios();
            const servicios = await cargarServicios();

            // Crear opciones HTML para propietarios
            let propietariosOptions = '<option value="">Selecciona un propietario...</option>';
            propietarios.forEach(prop => {
                propietariosOptions += `<option value="${prop.id_propietario}">${prop.nombres} ${prop.apellidos}</option>`;
            });

            // Crear opciones HTML para servicios con el servicio arrastrado pre-seleccionado
            let serviciosOptions = '<option value="">Selecciona un servicio...</option>';
            servicios.forEach(servicio => {
                const selected = servicio.id_servicio == servicioId ? 'selected' : '';
                serviciosOptions += `<option value="${servicio.id_servicio}" ${selected}>${servicio.nombre} - $${parseFloat(servicio.costo).toLocaleString('es-CO')}</option>`;
            });

            Swal.fire({
                title: 'Nuevo Agendamiento',
                html: `
                    <div style="text-align: left; margin: 20px 0; max-height: 500px; overflow-y: auto;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-person-fill"></i> Propietario: *
                        </label>
                        <select id="swal-propietario" class="swal2-input" style="margin: 0 0 20px 0; width: 90%; padding: 10px;">
                            ${propietariosOptions}
                        </select>
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-heart-fill"></i> Mascota: *
                        </label>
                        <select id="swal-mascota" class="swal2-input" style="margin: 0 0 20px 0; width: 90%; padding: 10px;" disabled>
                            <option value="">Primero selecciona un propietario...</option>
                        </select>
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-briefcase-fill"></i> Servicio: *
                        </label>
                        <select id="swal-servicio" class="swal2-input" style="margin: 0 0 20px 0; width: 90%; padding: 10px;">
                            ${serviciosOptions}
                        </select>
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-clock"></i> Fecha y Hora de Inicio: *
                        </label>
                        <input id="swal-fecha-inicio" type="datetime-local" class="swal2-input" 
                               value="${fechaInicioStr}" style="margin: 0 0 20px 0; width: 90%;">
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-clock-history"></i> Fecha y Hora de Fin: *
                        </label>
                        <input id="swal-fecha-fin" type="datetime-local" class="swal2-input" 
                               value="${fechaFinStr}" style="margin: 0 0 20px 0; width: 90%;">
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-card-text"></i> Observaciones (opcional):
                        </label>
                        <textarea id="swal-observaciones" class="swal2-textarea" 
                                  placeholder="Agrega observaciones o detalles adicionales..." 
                                  style="margin: 0; width: 90%; min-height: 80px; resize: vertical;"></textarea>
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
                    
                    propietarioSelect.addEventListener('change', async function() {
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
                },
                preConfirm: () => {
                    const propietario = document.getElementById('swal-propietario').value;
                    const mascota = document.getElementById('swal-mascota').value;
                    const servicio = document.getElementById('swal-servicio').value;
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
                    const selectElement = document.getElementById('swal-servicio');
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
                    
                    // Convertir fechas a ISO format
                    const fechaInicioISO = new Date(datos.fechaInicio).toISOString();
                    const fechaFinISO = new Date(datos.fechaFin).toISOString();
                    
                    // Preparamos los datos a enviar al servidor
                    var newEventData = {
                        id_propietario: parseInt(datos.propietario),
                        id_paciente: parseInt(datos.mascota),
                        id_servicio: parseInt(datos.servicio),
                        tipo: datos.servicioNombre, // Usar el nombre del servicio como tipo
                        observaciones: datos.observaciones,
                        fecha_hora: fechaInicioISO,
                        fecha_hora_fin: fechaFinISO,
                        estado: 'Pendiente',
                        allDay: 0
                    };

                    // Llamada AJAX para guardar en la base de datos
                    sendEventData(URLS.CREATE, newEventData, 
                        function(response) {
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
                        function(errorMessage) {
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
});