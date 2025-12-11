
// Definimos la URL base para las peticiones al backend.
// *** ¡Importante! Asegúrate de que estas rutas son correctas en tu servidor. ***




// POR AHORA LAS RUTAS NO SE HAN CREADO POR LO TANTO AUN NO LAS VOY AGREGAR





// Esperamos a que todo el contenido HTML de la página se haya cargado.
document.addEventListener('DOMContentLoaded', function() {

    // --- Función de Utilidad para Peticiones AJAX (Fetch) ---
    // Esta función maneja la comunicación con el servidor (backend).
    // Es centralizada para evitar repetir código en cada evento del calendario.
    function sendEventData(url, data, successCallback, errorCallback) {
        // Hacemos una petición POST al servidor usando Fetch API.
        fetch(url, {
            method: 'POST', // Usamos POST para enviar datos al servidor
            headers: {
                'Content-Type': 'application/json', // Le decimos al servidor que enviamos JSON
            },
            body: JSON.stringify(data), // Convertimos el objeto JavaScript 'data' a una cadena JSON
        })
        .then(response => {
            // Verificamos si la respuesta del servidor es OK (código 200-299)
            if (!response.ok) {
                throw new Error('La respuesta del servidor no fue exitosa: ' + response.statusText);
            }
            // Parseamos la respuesta del servidor como JSON
            return response.json(); 
        })
        .then(result => {
            // Si el servidor responde con un status 'success' (definido en tu PHP)
            if (result.status === 'success') {
                console.log('Operación exitosa:', result.message);
                if (successCallback) {
                    // Llamamos a la función de éxito, pasando el resultado
                    successCallback(result); 
                }
            } else {
                // Si el servidor respondió, pero hubo un error en la lógica (ej: error SQL)
                console.error('Error lógico en el servidor:', result.message);
                if (errorCallback) {
                    errorCallback(result.message);
                }
            }
        })
        .catch(error => {
            // Manejamos errores de red o errores al parsear el JSON
            console.error('Error de comunicación AJAX:', error);
            if (errorCallback) {
                errorCallback(error.message);
            }
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
                        // Duración necesaria para que FullCalendar calcule el fin
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
        // FullCalendar hará una petición GET a 'cargar_eventos.php' al iniciar y al cambiar de mes.
        events: './cargar_eventos.php', // *** ¡Ruta importante! ***
        
        // --- INTERACCIÓN Y CREACIÓN DE EVENTOS (Selectable) ---
        selectable: true, 
        
        select: function(info) {
            // Se ejecuta cuando el usuario selecciona un rango de fechas.
            var title = prompt('Ingresa el título del nuevo evento:');

            if (title) {
                // 1. Preparamos los datos a enviar al servidor
                var newEventData = {
                    title: title,
                    start: info.startStr,
                    end: info.endStr, // El servidor recibirá la fecha de fin
                    allDay: info.allDay ? 1 : 0 // Enviamos 1 o 0 para la base de datos
                };

                // 2. Llamada AJAX para guardar en la base de datos
                sendEventData(CREATE_URL, newEventData, 
                    // Función de Éxito (si el servidor responde correctamente)
                    function(result) {
                        // El servidor debe devolver el ID que le asignó la DB (ej: result.id)
                        // Esto es clave para poder actualizarlo después.
                        calendar.addEvent({
                            id: result.id, // Asignamos el ID retornado
                            title: title,
                            start: info.startStr,
                            end: info.endStr,
                            allDay: info.allDay
                        });
                        alert('Evento creado con éxito en la DB con ID: ' + result.id);
                    },
                    // Función de Error (si falla la comunicación o la lógica del servidor)
                    function(errorMessage) {
                        alert('Error al guardar el evento: ' + errorMessage);
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
            // El evento vuelve a su lugar si la confirmación es rechazada
            if (!confirm("¿Estás seguro de mover el evento a esta nueva fecha?")) {
                info.revert(); 
                return;
            } 
            
            // 1. Preparamos los datos para la actualización
            var eventUpdateData = {
                id: info.event.id,
                // Usamos toISOString() para tener un formato de fecha estándar que la DB entienda
                new_start: info.event.start.toISOString(),
                // Se verifica si existe hora de fin (puede ser nulo en eventos de día completo)
                new_end: info.event.end ? info.event.end.toISOString() : null, 
                action: 'move' // Indicamos qué acción se realizó
            };

            // 2. Llamada AJAX para actualizar el evento en la base de datos
            sendEventData(UPDATE_URL, eventUpdateData, 
                function() {
                    console.log('Movimiento de evento guardado.');
                },
                function(errorMessage) {
                    alert('Error al mover el evento: ' + errorMessage);
                    info.revert(); // Revertimos el movimiento si falla el guardado
                }
            );
        },
        
        // 2. Se ejecuta cuando un evento ya existente es REDIMENSIONADO.
        eventResize: function(info) {
            // ¡Esta función es nueva y crucial para guardar cambios de duración!
            if (!confirm("¿Estás seguro de cambiar la duración del evento?")) {
                info.revert(); 
                return;
            } 

            // 1. Preparamos los datos para la actualización
            var eventUpdateData = {
                id: info.event.id,
                new_start: info.event.start.toISOString(),
                new_end: info.event.end ? info.event.end.toISOString() : null,
                action: 'resize' // Indicamos qué acción se realizó
            };

            // 2. Llamada AJAX para actualizar el evento en la base de datos
            sendEventData(UPDATE_URL, eventUpdateData, 
                function() {
                    console.log('Redimensión de evento guardada.');
                },
                function(errorMessage) {
                    alert('Error al redimensionar el evento: ' + errorMessage);
                    info.revert(); // Revertimos si falla el guardado
                }
            );
        },

        // 3. Se ejecuta cuando un evento EXTERNO es soltado en el calendario.
        drop: function(info) {
            // info.draggedEl contiene el elemento HTML que fue soltado.
            
            var title = info.draggedEl.innerText.trim();
            
            // 1. Preparamos los datos para crear un nuevo evento
            var newExternalEventData = {
                title: title,
                start: info.dateStr, // Fecha y hora donde se soltó
                allDay: info.allDay,
                // Si el evento tiene duración (definida en initializeExternalEvents), 
                // FullCalendar calcula el 'end' automáticamente, no es necesario enviarlo aquí, 
                // pero si quieres, lo podrías calcular y enviar.
            };

            // 2. Llamada AJAX para crear el evento en la base de datos
            sendEventData(CREATE_URL, newExternalEventData, 
                function(result) {
                    // Si el servidor es exitoso, FullCalendar necesita un ID para poder moverlo después.
                    // ¡Agregamos el evento al calendario con el ID que viene de la DB!
                    calendar.addEvent({
                        id: result.id, 
                        title: title,
                        start: info.date,
                        allDay: info.allDay
                    });
                    alert('Evento externo creado con ID: ' + result.id);
                    
                    // Si el elemento externo debe desaparecer después de soltarse
                    if (document.getElementById('drop-remove').checked) {
                        info.draggedEl.remove();
                    }
                },
                function(errorMessage) {
                    alert('Error al crear el evento externo: ' + errorMessage);
                }
            );
            
            // Nota: FullCalendar crea un evento temporal automáticamente. La lógica anterior
            // lo elimina y añade el evento con el ID real de la DB para que sea editable.
            
        }
        
    });

    // Dibujar el calendario
    calendar.render();
});