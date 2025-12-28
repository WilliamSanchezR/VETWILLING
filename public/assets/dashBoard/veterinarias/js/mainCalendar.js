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
    DELETE: '/vetwilling/calendario/deleteEvent'
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


    // --- Parte de Arrastre de Eventos Externos (Draggable) ---
    function initializeExternalEvents() {
        var containerEl = document.getElementById('external-events');
        
        if (containerEl) {
            new FullCalendar.Draggable(containerEl, {
                itemSelector: '.fc-event',
                eventData: function(eventEl) {
                    return {
                        title: eventEl.innerText.trim(),
                        duration: eventEl.getAttribute('data-duration') 
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
        
        select: function(info) {
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

            Swal.fire({
                title: 'Nuevo Agendamiento',
                html: `
                    <div style="text-align: left; margin: 20px 0;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-bookmark-fill"></i> Tipo de Agendamiento:
                        </label>
                        <select id="swal-tipo-evento" class="swal2-input" style="margin: 0 0 20px 0; width: 90%; padding: 10px;">
                            <option value="">Selecciona un tipo...</option>
                            <option value="Consulta General" data-color="#007832">🩺 Consulta General</option>
                            <option value="Vacunación" data-color="#17a2b8">💉 Vacunación</option>
                            <option value="Cirugía" data-color="#dc3545">⚕️ Cirugía</option>
                            <option value="Control" data-color="#ffc107">📋 Control</option>
                            <option value="Emergencia" data-color="#fd7e14">🚨 Emergencia</option>
                            <option value="Desparasitación" data-color="#6f42c1">🐛 Desparasitación</option>
                            <option value="Peluquería" data-color="#e83e8c">✂️ Peluquería</option>
                            <option value="Baño" data-color="#20c997">🛁 Baño</option>
                            <option value="Otro" data-color="#6c757d">📌 Otro</option>
                        </select>
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-clock"></i> Fecha y Hora de Inicio:
                        </label>
                        <input id="swal-fecha-inicio" type="datetime-local" class="swal2-input" 
                               value="${fechaInicioStr}" style="margin: 0 0 20px 0; width: 90%;">
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #00304D;">
                            <i class="bi bi-clock-history"></i> Fecha y Hora de Fin:
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
                width: '600px',
                focusConfirm: false,
                preConfirm: () => {
                    const tipo = document.getElementById('swal-tipo-evento').value;
                    const fechaInicio = document.getElementById('swal-fecha-inicio').value;
                    const fechaFin = document.getElementById('swal-fecha-fin').value;
                    const observaciones = document.getElementById('swal-observaciones').value;
                    
                    if (!tipo) {
                        Swal.showValidationMessage('Debes seleccionar un tipo de agendamiento');
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
                    
                    // Obtener el color del tipo seleccionado
                    const selectElement = document.getElementById('swal-tipo-evento');
                    const selectedOption = selectElement.options[selectElement.selectedIndex];
                    const color = selectedOption.getAttribute('data-color');
                    
                    return { 
                        tipo: tipo, 
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
                        tipo: datos.tipo,
                        observaciones: datos.observaciones,
                        fecha_hora: fechaInicioISO,
                        fecha_hora_fin: fechaFinISO,
                        allDay: 0 // Siempre será un evento con hora específica
                    };

                    // 2. Llamada AJAX para guardar en la base de datos
                    sendEventData(URLS.CREATE, newEventData, 
                        // Función de Éxito
                        function(response) {
                            calendar.addEvent({
                                id: response.id,
                                title: datos.tipo,
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
                            Tipo de Agendamiento:
                        </label>
                        <input id="swal-tipo" class="swal2-input" value="${evento.title}" 
                               placeholder="Tipo de agendamiento" style="margin: 0 0 15px 0; width: 90%;">
                        
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
        drop: function(info) {
            var title = info.draggedEl.innerText.trim();
            
            // 1. Preparamos los datos para crear un nuevo evento
            var newExternalEventData = {
                tipo: title,
                fecha_hora: info.dateStr, 
                allDay: info.allDay
            };

            // 2. Llamada AJAX para crear el evento en la base de datos
            sendEventData(URLS.CREATE, newExternalEventData, 
                function(result) {
                    calendar.addEvent({
                        id: result.id, 
                        title: title,
                        start: info.date,
                        allDay: info.allDay
                    });
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Evento creado!',
                        text: 'Agendamiento externo creado correctamente',
                        confirmButtonText: 'Aceptar',
                        timer: 2500,
                        timerProgressBar: true
                    });
                    
                    if (document.getElementById('drop-remove') && document.getElementById('drop-remove').checked) {
                        info.draggedEl.remove();
                    }
                },
                function(errorMessage) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo crear el agendamiento: ' + errorMessage,
                        confirmButtonText: 'Aceptar'
                    });
                }
            );
        }
        
    });

    // Dibujar el calendario
    calendar.render();
});