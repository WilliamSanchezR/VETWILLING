// =======================================================
// Archivo: js/main_calendar.js
// Propósito: Inicializar el calendario FullCalendar y todas sus interacciones.
// =======================================================

// 1. Esperamos a que todo el contenido HTML de la página se haya cargado
// antes de intentar buscar el calendario o los eventos externos.
document.addEventListener('DOMContentLoaded', function() {

    // --- Parte de Arrastre de Eventos Externos (Opcional C.2) ---
    // Esta sección hace que los elementos con la clase 'fc-event' fuera del calendario
    // puedan ser arrastrados.
    function initializeExternalEvents() {
        // Obtenemos el contenedor de los eventos externos (debe tener el ID 'external-events' en el HTML).
        var containerEl = document.getElementById('external-events');
        
        if (containerEl) {
            // Creamos una nueva instancia de Draggable de FullCalendar.
            new FullCalendar.Draggable(containerEl, {
                // Indicamos qué elementos dentro del contenedor son arrastrables.
                itemSelector: '.fc-event',
                
                // Definimos los datos del evento que se crearán cuando se suelte en el calendario.
                eventData: function(eventEl) {
                    return {
                        title: eventEl.innerText.trim(),
                        // Si el elemento HTML tiene un atributo 'data-duration', FullCalendar lo usa para la duración.
                        duration: eventEl.getAttribute('data-duration') 
                    };
                }
            });
        }
    }

    // Llamamos a la función para habilitar el arrastre de eventos externos.
    initializeExternalEvents();

    // --- Inicialización Principal del Calendario ---
    
    // Obtenemos la referencia al elemento <div> donde se dibujará el calendario.
    var calendarEl = document.getElementById('calendar');

    // Creamos la instancia principal del calendario con todas sus opciones.
    var calendar = new FullCalendar.Calendar(calendarEl, {
        
        // --- CONFIGURACIÓN DE APARIENCIA Y VISTA ---
        locale: 'es', // Idioma en español
        themeSystem: 'bootstrap5', // Usamos el tema de Bootstrap para un mejor estilo
        initialView: 'dayGridMonth', // Vista de mes al inicio

        // Define los botones y el título en la parte superior del calendario.
        headerToolbar: {
            left: 'prev,next today', // Botones de navegación
            center: 'title', // Título del mes/año
            right: 'dayGridMonth,timeGridWeek,timeGridDay' // Vistas disponibles
        },
        
        // --- CARGA DE EVENTOS (Opcional B) ---
        // Aquí le indicamos a FullCalendar que cargue los eventos desde tu servidor.
        // Debe ser la ruta donde tienes tu archivo 'events.php' (o similar) que devuelve JSON.
        events: './events.php', // ¡CAMBIA ESTA RUTA SI ES NECESARIO!
        
        // --- INTERACCIÓN Y CREACIÓN DE EVENTOS (Selectable) ---
        selectable: true, // Permite seleccionar rangos de fechas arrastrando el ratón.
        
        select: function(info) {
            // Se ejecuta cuando el usuario selecciona un rango de fechas.
            var title = prompt('Ingresa el título del nuevo evento:');

            if (title) {
                // Si se introduce un título, agregamos el evento al calendario.
                calendar.addEvent({
                    title: title,
                    start: info.startStr,
                    end: info.endStr,
                    allDay: info.allDay
                });
                // NOTA: Aquí DEBES hacer una llamada AJAX para guardar el evento
                // en tu base de datos del lado del servidor.
            }
            // Limpiamos la selección.
            calendar.unselect();
        },
        
        // --- ARRASTRAR Y MODIFICAR EVENTOS (Opcional C.1 y C.2) ---
        
        editable: true, // Permite que los eventos se puedan arrastrar y redimensionar.
        droppable: true, // Permite que se suelten eventos EXTERNOS en el calendario.

        // Se ejecuta cuando un evento ya existente es movido a una nueva fecha/hora.
        eventDrop: function(info) {
            // info.event contiene el evento movido y sus nuevas fechas/horas.
            
            // Pedimos confirmación antes de guardar el cambio.
            if (!confirm("¿Estás seguro de mover el evento a esta nueva fecha?")) {
                info.revert(); // Si cancela, el evento vuelve a su posición original.
            } else {
                // PASO CRUCIAL: Debes enviar una petición AJAX a tu backend (ej: update_event.php)
                // para actualizar el registro del evento en tu base de datos con:
                // - ID del evento: info.event.id
                // - Nueva fecha de inicio: info.event.start.toISOString()
                // - Nueva fecha de fin: info.event.end ? info.event.end.toISOString() : null
                
                alert('¡Felicidades! Aquí guardarías el cambio en el servidor para el evento con ID: ' + info.event.id);
            }
        },

        // Se ejecuta cuando un evento EXTERNO (de la barra lateral) es soltado.
        drop: function(info) {
            // info.draggedEl contiene el elemento HTML que fue soltado.
            // info.dateStr contiene la fecha donde fue soltado.
            
            // PASO CRUCIAL: Debes enviar una petición AJAX a tu backend (ej: create_event.php)
            // para crear un NUEVO registro en tu base de datos.
            
            alert('Evento externo soltado y listo para crear en el servidor en la fecha: ' + info.dateStr);

            // Si el elemento externo debe desaparecer después de soltarse, se usa:
            // info.draggedEl.remove();
        }
        
    });

    // 2. Dibujar el calendario en el <div>
    calendar.render();
});