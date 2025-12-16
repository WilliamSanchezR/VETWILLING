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
            var title = prompt('Ingresa el TIPO de Agendamiento:');

            if (title) {
                // 1. Preparamos los datos a enviar al servidor
                var newEventData = {
                    tipo: title,
                    fecha_hora: info.startStr,
                    fecha_hora_fin: info.endStr || null,
                    allDay: info.allDay ? 1 : 0
                };

                // 2. Llamada AJAX para guardar en la base de datos
                sendEventData(URLS.CREATE, newEventData, 
                    // Función de Éxito
                    function(result) {
                        calendar.addEvent({
                            id: result.id,
                            title: title,
                            start: info.startStr,
                            end: info.endStr,
                            allDay: info.allDay
                        });
                        alert('Agendamiento creado con éxito. ID: ' + result.id);
                    },
                    // Función de Error
                    function(errorMessage) {
                        alert('Error al guardar el agendamiento: ' + errorMessage);
                    }
                );
            }
            calendar.unselect();
        },
        
        // --- MODIFICACIÓN DE EVENTOS (editable) ---
        editable: true, 
        droppable: true, 

        // 1. Se ejecuta cuando un evento ya existente es MOVIDO.
        eventDrop: function(info) {
            if (!confirm("¿Estás seguro de mover el agendamiento?")) {
                info.revert(); 
                return;
            } 
            
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
                    console.log('Movimiento de agendamiento guardado.');
                },
                function(errorMessage) {
                    alert('Error al mover el agendamiento: ' + errorMessage);
                    info.revert(); 
                }
            );
        },
        
        // 2. Se ejecuta cuando un evento ya existente es REDIMENSIONADO.
        eventResize: function(info) {
            if (!confirm("¿Estás seguro de cambiar la duración del agendamiento?")) {
                info.revert(); 
                return;
            } 

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
                    console.log('Redimensión de agendamiento guardada.');
                },
                function(errorMessage) {
                    alert('Error al redimensionar el agendamiento: ' + errorMessage);
                    info.revert(); 
                }
            );
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
                    alert('Agendamiento externo creado con ID: ' + result.id);
                    
                    if (document.getElementById('drop-remove') && document.getElementById('drop-remove').checked) {
                        info.draggedEl.remove();
                    }
                },
                function(errorMessage) {
                    alert('Error al crear el agendamiento externo: ' + errorMessage);
                }
            );
        }
        
    });

    // Dibujar el calendario
    calendar.render();
});