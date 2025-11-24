<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/panel.css">

<div class="barra-lateral-derecha oculta" id="barraLateralDerecha">
    <!-- Header del Panel -->
    <div class="encabezado-lateral-derecho">
        <div class="header-info">
            <i class="bi bi-grid-3x3-gap-fill"></i>
            <h5>Panel de Control</h5>
        </div>
        <button class="boton-cerrar-lateral" onclick="alternarBarraDerecha()" aria-label="Cerrar panel">
            <i class="bi bi-x"></i>
        </button>
    </div>

    <!-- Resumen Rápido -->
    <div class="seccion-resumen">
        <div class="tarjeta-resumen">
            <div class="resumen-icono resumen-azul">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="resumen-info">
                <span class="resumen-numero">2</span>
                <span class="resumen-label">Citas Próximas</span>
            </div>
        </div>
        <div class="tarjeta-resumen">
            <div class="resumen-icono resumen-verde">
                <i class="bi bi-heart-pulse"></i>
            </div>
            <div class="resumen-info">
                <span class="resumen-numero">3</span>
                <span class="resumen-label">Mascotas</span>
            </div>
        </div>
        <div class="tarjeta-resumen">
            <div class="resumen-icono resumen-naranja">
                <i class="bi bi-bell"></i>
            </div>
            <div class="resumen-info">
                <span class="resumen-numero">5</span>
                <span class="resumen-label">Notificaciones</span>
            </div>
        </div>
    </div>

    <!-- Sección Notificaciones -->
    <div class="seccion-panel">
        <div class="seccion-header">
            <h6>
                <i class="bi bi-bell-fill"></i>
                Notificaciones
            </h6>
            <button class="btn-ver-mas" onclick="verTodasNotificaciones()">Ver todas</button>
        </div>

        <div class="lista-items">
            <div class="item-notificacion no-leido" data-id="1">
                <div class="item-contenido">
                    <div class="icono-notificacion icono-notificacion-morado">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <div class="notif-detalles">
                        <div class="notif-titulo">Recordatorio de vacuna</div>
                        <div class="notif-descripcion">Max necesita su refuerzo antirrábico</div>
                        <div class="notif-meta">
                            <span class="notif-tiempo">
                                <i class="bi bi-clock"></i>
                                Ahora
                            </span>
                            <span class="notif-badge badge-urgente">Urgente</span>
                        </div>
                    </div>
                </div>
                <button class="btn-accion-item" onclick="marcarLeido(1)" title="Marcar como leído">
                    <i class="bi bi-check2"></i>
                </button>
            </div>

            <div class="item-notificacion" data-id="2">
                <div class="item-contenido">
                    <div class="icono-notificacion icono-notificacion-verde">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="notif-detalles">
                        <div class="notif-titulo">Cita confirmada</div>
                        <div class="notif-descripcion">Consulta con Dr. Martínez - Mañana 10:00 AM</div>
                        <div class="notif-meta">
                            <span class="notif-tiempo">
                                <i class="bi bi-clock"></i>
                                Hace 52 min
                            </span>
                        </div>
                    </div>
                </div>
                <button class="btn-accion-item" onclick="verDetalle(2)" title="Ver detalle">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>

            <div class="item-notificacion" data-id="3">
                <div class="item-contenido">
                    <div class="icono-notificacion icono-notificacion-azul">
                        <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <div class="notif-detalles">
                        <div class="notif-titulo">Mensaje del veterinario</div>
                        <div class="notif-descripcion">Resultados de exámenes disponibles</div>
                        <div class="notif-meta">
                            <span class="notif-tiempo">
                                <i class="bi bi-clock"></i>
                                Hace 3 h
                            </span>
                            <span class="notif-badge badge-info">Nuevo</span>
                        </div>
                    </div>
                </div>
                <button class="btn-accion-item" onclick="verMensaje(3)" title="Ver mensaje">
                    <i class="bi bi-envelope-open"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Sección Actividades Recientes -->
    <div class="seccion-panel">
        <div class="seccion-header">
            <h6>
                <i class="bi bi-activity"></i>
                Actividades Recientes
            </h6>
            <button class="btn-ver-mas" onclick="verHistorial()">Historial</button>
        </div>

        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-punto punto-rojo"></div>
                <div class="timeline-contenido">
                    <div class="actividad-header">
                        <div class="icono-actividad icono-actividad-rojo">
                            <i class="bi bi-heart-pulse-fill"></i>
                        </div>
                        <div class="actividad-info">
                            <div class="actividad-titulo">Consulta General</div>
                            <div class="actividad-subtitulo">Max - Labrador Retriever</div>
                        </div>
                    </div>
                    <div class="actividad-detalles">
                        <p>Chequeo de rutina completado. Todo normal.</p>
                        <div class="actividad-meta">
                            <span class="actividad-tiempo">
                                <i class="bi bi-clock-history"></i>
                                Justo ahora
                            </span>
                            <span class="actividad-doctor">Dr. Martínez</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-punto punto-azul"></div>
                <div class="timeline-contenido">
                    <div class="actividad-header">
                        <div class="icono-actividad icono-actividad-azul">
                            <i class="bi bi-shield-fill-check"></i>
                        </div>
                        <div class="actividad-info">
                            <div class="actividad-titulo">Vacunación</div>
                            <div class="actividad-subtitulo">Luna - Gato Persa</div>
                        </div>
                    </div>
                    <div class="actividad-detalles">
                        <p>Vacuna triple felina aplicada correctamente.</p>
                        <div class="actividad-meta">
                            <span class="actividad-tiempo">
                                <i class="bi bi-clock-history"></i>
                                Hace 59 min
                            </span>
                            <span class="actividad-doctor">Dra. González</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-punto punto-verde"></div>
                <div class="timeline-contenido">
                    <div class="actividad-header">
                        <div class="icono-actividad icono-actividad-verde">
                            <i class="bi bi-clipboard-check-fill"></i>
                        </div>
                        <div class="actividad-info">
                            <div class="actividad-titulo">Examen de Sangre</div>
                            <div class="actividad-subtitulo">Rocky - Bulldog</div>
                        </div>
                    </div>
                    <div class="actividad-detalles">
                        <p>Resultados dentro de parámetros normales.</p>
                        <div class="actividad-meta">
                            <span class="actividad-tiempo">
                                <i class="bi bi-clock-history"></i>
                                Hace 2 h
                            </span>
                            <span class="actividad-doctor">Lab. VetCare</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección Próximas Citas -->
    <div class="seccion-panel">
        <div class="seccion-header">
            <h6>
                <i class="bi bi-calendar-week"></i>
                Próximas Citas
            </h6>
        </div>

        <div class="lista-citas">
            <div class="cita-card">
                <div class="cita-fecha-badge">
                    <div class="dia">07</div>
                    <div class="mes">NOV</div>
                </div>
                <div class="cita-info">
                    <div class="cita-titulo">Chequeo General</div>
                    <div class="cita-mascota">
                        <i class="bi bi-heart-fill"></i>
                        Max
                    </div>
                    <div class="cita-hora">
                        <i class="bi bi-clock"></i>
                        10:00 AM
                    </div>
                </div>
                <button class="btn-cita-accion" onclick="verCita(1)">
                    <i class="bi bi-arrow-right-circle"></i>
                </button>
            </div>

            <div class="cita-card">
                <div class="cita-fecha-badge cita-urgente">
                    <div class="dia">11</div>
                    <div class="mes">NOV</div>
                </div>
                <div class="cita-info">
                    <div class="cita-titulo">Vacuna Antirrábica</div>
                    <div class="cita-mascota">
                        <i class="bi bi-heart-fill"></i>
                        Luna
                    </div>
                    <div class="cita-hora">
                        <i class="bi bi-clock"></i>
                        3:00 PM
                    </div>
                </div>
                <button class="btn-cita-accion" onclick="verCita(2)">
                    <i class="bi bi-arrow-right-circle"></i>
                </button>
            </div>
        </div>

        <button class="btn-agendar-cita" onclick="agendarCita()">
            <i class="bi bi-plus-circle"></i>
            Agendar Nueva Cita
        </button>
    </div>

    <!-- Acciones Rápidas -->
    <div class="seccion-panel seccion-acciones">
        <div class="seccion-header">
            <h6>
                <i class="bi bi-lightning-charge-fill"></i>
                Accesos Rápidos
            </h6>
        </div>

        <div class="grid-acciones">
            <button class="btn-accion-rapida" onclick="irMascotas()">
                <i class="bi bi-heart-pulse"></i>
                <span>Mis Mascotas</span>
            </button>
            <button class="btn-accion-rapida" onclick="irHistorial()">
                <i class="bi bi-file-medical"></i>
                <span>Historial</span>
            </button>
            <button class="btn-accion-rapida" onclick="irTienda()">
                <i class="bi bi-cart3"></i>
                <span>Tienda</span>
            </button>
            <button class="btn-accion-rapida" onclick="irChat()">
                <i class="bi bi-chat-dots"></i>
                <span>Chat</span>
            </button>
        </div>
    </div>
</div>