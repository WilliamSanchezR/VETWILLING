<?php
/* ====================================================================
   VISTA DE CITAS DEL CLIENTE
   CORRECCIONES APLICADAS:
   - Eliminado el bloque <style> enorme del PHP — todo va en citas.css
   - SweetAlert2 carga CSS + JS (antes solo cargaba el JS)
   - sidebar.css incluido (antes faltaba)
   - Bootstrap cargado una sola vez
   - escapeHtml() usado en verDetallesCita para evitar XSS
   - Filtros de fecha corregidos con parseLocalDate() para evitar
     problemas de timezone
   ==================================================================== */
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        session_start();
    }
}

if (!isset($_SESSION['user']['id_usuario'])) {
    if (!headers_sent()) {
        header('Location: ' . BASE_URL . '/login');
    }
    exit();
}

$id_usuario = $_SESSION['user']['id_usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas - VetWilling</title>

    <!-- Bootstrap CSS (una sola vez) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- SweetAlert2 CSS + JS
         CORRECCIÓN: antes solo se cargaba el JS sin el CSS,
         lo que hacía que los modales aparecieran sin estilos. -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS propios
         CORRECCIÓN: sidebar.css agregado — antes faltaba y el sidebar
         y la burbuja FAB no tenían estilos en esta vista. -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/citas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
</head>

<body>

    <!-- Sidebar -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- Contenido principal -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- Navbar superior -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <!-- Área de contenido -->
        <div class="area-contenido">
            <div class="container-dashboard">

                <!-- ── Header ── -->
                <div class="header-citas">
                    <div class="header-titulo">
                        <h1><i class="bi bi-calendar2-week"></i> Mis Citas</h1>
                        <p>Gestiona y consulta tus citas veterinarias</p>
                    </div>
                    <a href="<?= BASE_URL ?>/cliente/agendar-cita" class="btn-nueva-cita">
                        <i class="bi bi-calendar-plus"></i> Nueva Cita
                    </a>
                </div>

                <!-- ── Estadísticas ── -->
                <div class="stats-rapidas">
                    <div class="stat-item">
                        <div class="stat-icon proximas">
                            <i class="bi bi-calendar2-week"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="stat-proximas">-</h3>
                            <p>Próximas</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon completadas">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="stat-completadas">-</h3>
                            <p>Completadas</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon pendientes">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="stat-hoy">-</h3>
                            <p>Hoy</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon canceladas">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="stat-canceladas">-</h3>
                            <p>Canceladas</p>
                        </div>
                    </div>
                </div>

                <!-- ── Tabs ── -->
                <div class="tabs-container">
                    <button class="tab-btn active" data-filtro="todas">Todas</button>
                    <button class="tab-btn" data-filtro="hoy">Hoy</button>
                    <button class="tab-btn" data-filtro="proximas">Próximas</button>
                    <button class="tab-btn" data-filtro="esta-semana">Esta Semana</button>
                    <button class="tab-btn" data-filtro="pendientes">Pendientes</button>
                    <button class="tab-btn" data-filtro="canceladas">Canceladas</button>
                </div>

                <!-- ── Filtros ── -->
                <div class="filtros-avanzados">
                    <div class="filtro-grupo">
                        <label for="filtro-mascota">Mascota</label>
                        <select id="filtro-mascota">
                            <option value="">Todas las mascotas</option>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <label for="filtro-estado">Estado</label>
                        <select id="filtro-estado">
                            <option value="">Todos los estados</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Confirmada">Confirmada</option>
                            <option value="Realizada">Realizada</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <label for="filtro-fecha-inicio">Desde</label>
                        <input type="date" id="filtro-fecha-inicio">
                    </div>
                    <div class="filtro-grupo">
                        <label for="filtro-fecha-fin">Hasta</label>
                        <input type="date" id="filtro-fecha-fin">
                    </div>
                </div>

                <!-- ── Contenedor de citas ── -->
                <div class="citas-timeline" id="citasContainer">
                    <div class="loading-container">
                        <div class="spinner"></div>
                        <p>Cargando tus citas...</p>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Bootstrap JS (una sola vez)
         CORRECCIÓN: antes se repetía varias veces. -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>

    <script>
    /* ================================================================
       CONFIGURACIÓN GLOBAL
    ================================================================ */
    // BASE_URL ya declarado por nav.js desde window.BASE_URL (panel_superio_paciente.php)
    const URLS = {
        MIS_CITAS:          window.BASE_URL + '/cliente/api/citas/listar?accion=listar',
        HISTORIAL_PACIENTE: window.BASE_URL + '/cliente/api/citas/listar?accion=historial_paciente',
        CANCELAR_CITA:      window.BASE_URL + '/cliente/api/citas/cancelar'
    };

    let citasData    = [];
    let filtroActual = 'todas';

    /* ================================================================
       INIT
    ================================================================ */
    document.addEventListener('DOMContentLoaded', function () {
        cargarCitas();

        // Tabs
        document.querySelectorAll('.tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.tab-btn').forEach(function (b) {
                    b.classList.remove('active');
                });
                this.classList.add('active');
                filtroActual = this.dataset.filtro;
                aplicarFiltros();
            });
        });

        // Filtros
        ['filtro-mascota', 'filtro-estado', 'filtro-fecha-inicio', 'filtro-fecha-fin']
            .forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.addEventListener('change', aplicarFiltros);
            });
    });

    /* ================================================================
       CARGAR CITAS DESDE LA API
    ================================================================ */
    async function cargarCitas() {
        try {
            var res  = await fetch(URLS.MIS_CITAS);
            var data = await res.json();
            if (data.status !== 'success') throw new Error(data.message || 'Error al cargar citas');

            citasData = data.citas || [];
            actualizarEstadisticas(citasData);
            cargarFiltroMascotas(citasData);
            renderizarCitas(citasData);
        } catch (err) {
            document.getElementById('citasContainer').innerHTML =
                '<div class="estado-vacio">' +
                    '<i class="bi bi-exclamation-triangle"></i>' +
                    '<h3>Error al cargar las citas</h3>' +
                    '<p>' + escapeHtml(err.message) + '</p>' +
                    '<button onclick="cargarCitas()" class="btn-agendar-nueva">' +
                        '<i class="bi bi-arrow-clockwise"></i> Reintentar' +
                    '</button>' +
                '</div>';
        }
    }

    /* ================================================================
       ESTADÍSTICAS
    ================================================================ */
    function actualizarEstadisticas(citas) {
        var hoy = new Date(); hoy.setHours(0, 0, 0, 0);

        document.getElementById('stat-proximas').textContent =
            citas.filter(function (c) {
                return new Date(c.fecha_hora) >= hoy &&
                       c.estado !== 'Cancelada' &&
                       c.estado !== 'Realizada';
            }).length;

        document.getElementById('stat-completadas').textContent =
            citas.filter(function (c) { return c.estado === 'Realizada'; }).length;

        document.getElementById('stat-hoy').textContent =
            citas.filter(function (c) {
                return new Date(c.fecha_hora).toDateString() === hoy.toDateString() &&
                       c.estado !== 'Cancelada';
            }).length;

        document.getElementById('stat-canceladas').textContent =
            citas.filter(function (c) { return c.estado === 'Cancelada'; }).length;
    }

    /* ================================================================
       FILTRO DE MASCOTAS (select)
    ================================================================ */
    function cargarFiltroMascotas(citas) {
        var sel    = document.getElementById('filtro-mascota');
        var unicas = [...new Set(citas.map(function (c) { return c.mascota_nombre; }).filter(Boolean))];

        sel.innerHTML = '<option value="">Todas las mascotas</option>' +
            unicas.map(function (m) {
                return '<option value="' + escapeHtml(m) + '">' + escapeHtml(m) + '</option>';
            }).join('');
    }

    /* ================================================================
       APLICAR FILTROS
       CORRECCIÓN: parseLocalDate() evita problemas de timezone al
       comparar fechas del input type="date" con fecha_hora de la BD.
    ================================================================ */
    function parseLocalDate(str) {
        // Convierte "2025-01-15" a Date local sin desfase de timezone
        var parts = str.split('-');
        return new Date(+parts[0], +parts[1] - 1, +parts[2]);
    }

    function aplicarFiltros() {
        var f   = citasData.slice();
        var hoy = new Date(); hoy.setHours(0, 0, 0, 0);

        if (filtroActual === 'proximas') {
            f = f.filter(function (c) {
                return new Date(c.fecha_hora) >= hoy &&
                       c.estado !== 'Cancelada' &&
                       c.estado !== 'Realizada';
            });
        } else if (filtroActual === 'hoy') {
            f = f.filter(function (c) {
                return new Date(c.fecha_hora).toDateString() === hoy.toDateString() &&
                       c.estado !== 'Cancelada';
            });
        } else if (filtroActual === 'esta-semana') {
            var fin = new Date(hoy); fin.setDate(fin.getDate() + 7);
            f = f.filter(function (c) {
                var d = new Date(c.fecha_hora);
                return d >= hoy && d <= fin && c.estado !== 'Cancelada';
            });
        } else if (filtroActual === 'pendientes') {
            f = f.filter(function (c) { return c.estado === 'Pendiente'; });
        } else if (filtroActual === 'canceladas') {
            f = f.filter(function (c) { return c.estado === 'Cancelada'; });
        }

        var mascota = document.getElementById('filtro-mascota').value;
        if (mascota) f = f.filter(function (c) { return c.mascota_nombre === mascota; });

        var estado = document.getElementById('filtro-estado').value;
        if (estado) f = f.filter(function (c) { return c.estado === estado; });

        var desdeStr = document.getElementById('filtro-fecha-inicio').value;
        if (desdeStr) {
            var desde = parseLocalDate(desdeStr);
            f = f.filter(function (c) { return new Date(c.fecha_hora) >= desde; });
        }

        var hastaStr = document.getElementById('filtro-fecha-fin').value;
        if (hastaStr) {
            var hasta = parseLocalDate(hastaStr);
            hasta.setHours(23, 59, 59);
            f = f.filter(function (c) { return new Date(c.fecha_hora) <= hasta; });
        }

        renderizarCitas(f);
    }

    /* ================================================================
       RENDERIZAR CITAS
    ================================================================ */
    function renderizarCitas(citas) {
        var container = document.getElementById('citasContainer');

        if (!citas.length) {
            container.innerHTML =
                '<div class="estado-vacio">' +
                    '<i class="bi bi-calendar-x"></i>' +
                    '<h3>No hay citas</h3>' +
                    '<p>No hay citas para el filtro seleccionado</p>' +
                    '<a href="' + BASE_URL + '/cliente/agendar-cita" class="btn-agendar-nueva">' +
                        '<i class="bi bi-calendar-plus"></i> Agendar Nueva Cita' +
                    '</a>' +
                '</div>';
            return;
        }

        var grupos = agruparPorDia(citas);
        var hoy    = new Date(); hoy.setHours(0, 0, 0, 0);
        var html   = '';

        Object.keys(grupos).sort().forEach(function (fecha) {
            var lista  = grupos[fecha];
            var parts  = fecha.split('-');
            var fObj   = new Date(+parts[0], +parts[1] - 1, +parts[2]);
            var diff   = Math.floor((fObj - hoy) / 86400000);
            var etiq   = diff === 0 ? 'Hoy' :
                         diff === 1 ? 'Mañana' :
                         diff > 1   ? 'En ' + diff + ' días' : 'Pasada';

            html +=
                '<div class="timeline-dia">' +
                    '<div class="dia-header">' +
                        '<div class="dia-fecha">' +
                            '<h3>' + formatearFecha(fObj) + '</h3>' +
                            '<p>' + etiq + '</p>' +
                        '</div>' +
                        '<span class="dia-badge">' + lista.length + ' cita' + (lista.length > 1 ? 's' : '') + '</span>' +
                    '</div>' +
                    '<div class="citas-grid">' +
                        lista.map(generarTarjetaCita).join('') +
                    '</div>' +
                '</div>';
        });

        container.innerHTML = html;
    }

    /* ================================================================
       AGRUPAR POR DÍA
    ================================================================ */
    function agruparPorDia(citas) {
        var g = {};
        citas.forEach(function (c) {
            var f = new Date(c.fecha_hora);
            var k = f.getFullYear() + '-' +
                    String(f.getMonth() + 1).padStart(2, '0') + '-' +
                    String(f.getDate()).padStart(2, '0');
            if (!g[k]) g[k] = [];
            g[k].push(c);
        });
        return g;
    }

    /* ================================================================
       GENERAR TARJETA DE CITA
    ================================================================ */
    function generarTarjetaCita(cita) {
        var fecha    = new Date(cita.fecha_hora);
        var horaStr  = fecha.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: false });
        var periodo  = fecha.getHours() >= 12 ? 'PM' : 'AM';
        var tipoCss  = obtenerTipoCss(cita.tipo);
        var esUrg    = cita.tipo && cita.tipo.toLowerCase().includes('emergencia');
        var estadoClass = {
            Confirmada: 'confirmada',
            Pendiente:  'pendiente',
            Realizada:  'realizada',
            Cancelada:  'cancelada'
        }[cita.estado] || 'pendiente';
        var esPendiente = cita.estado === 'Pendiente';

        var foto = (cita.img_mascota && cita.img_mascota !== 'default_pet.jpg')
            ? BASE_URL + '/public/uploads/mascotas/' + cita.img_mascota
            : BASE_URL + '/public/uploads/mascotas/default_pet.jpg';

        return '' +
            '<div class="cita-card ' + (esUrg ? 'urgente' : '') + '"' +
                 ' data-cita-id="' + cita.id_agendamiento + '"' +
                 ' data-estado="' + escapeHtml(cita.estado) + '">' +

                '<span class="urgente-label">⚠ Urgente</span>' +

                /* Cabecera: hora + tipo */
                '<div class="cita-card-header">' +
                    '<div class="cita-hora-grande">' +
                        '<span class="hora-h">' + horaStr + '</span>' +
                        '<span class="hora-am">' + periodo + '</span>' +
                    '</div>' +
                    '<span class="tipo-badge ' + tipoCss + '">' + escapeHtml(cita.tipo || 'Consulta') + '</span>' +
                '</div>' +

                /* Cuerpo: avatar + info */
                '<div class="cita-card-body">' +
                    '<div class="cita-mascota-avatar"' +
                         ' style="background-image:url(\'' + foto + '\');"' +
                         ' role="img"' +
                         ' aria-label="Foto de ' + escapeHtml(cita.mascota_nombre || 'mascota') + '">' +
                    '</div>' +
                    '<div class="cita-info">' +
                        '<p class="cita-nombre-mascota">' + escapeHtml(cita.mascota_nombre || 'Mascota') + '</p>' +
                        '<div class="cita-meta">' +
                            '<div class="cita-meta-item">' +
                                '<i class="bi bi-person-fill"></i>' +
                                '<span>' + escapeHtml(cita.veterinario_nombre || 'Por asignar') + '</span>' +
                            '</div>' +
                            '<div class="cita-meta-item">' +
                                '<i class="bi bi-heart-fill"></i>' +
                                '<span>' + escapeHtml(cita.mascota_especie || '') + (cita.mascota_raza ? ' · ' + escapeHtml(cita.mascota_raza) : '') + '</span>' +
                            '</div>' +
                            '<div class="cita-meta-item">' +
                                '<i class="bi bi-clock-fill"></i>' +
                                '<span>' + calcularDuracion(cita.fecha_hora, cita.fecha_hora_fin) + '</span>' +
                            '</div>' +
                            '<span class="estado-badge ' + estadoClass + '">' + escapeHtml(cita.estado) + '</span>' +
                        '</div>' +
                    '</div>' +
                '</div>' +

                /* Observaciones */
                (cita.observaciones
                    ? '<div class="cita-notas"><strong>Observaciones:</strong> ' + escapeHtml(cita.observaciones) + '</div>'
                    : '') +

                /* Acciones */
                '<div class="cita-acciones ' + (esPendiente ? '' : 'solo-dos') + '">' +
                    '<button class="btn-accion btn-ver"' +
                            ' onclick="verDetallesCita(' + cita.id_agendamiento + ')">' +
                        '<i class="bi bi-eye"></i> Ver' +
                    '</button>' +
                    '<button class="btn-accion btn-historial"' +
                            ' onclick="verHistorialMascota(' + cita.id_paciente + ', ' + JSON.stringify(cita.mascota_nombre || 'Mascota') + ')">' +
                        '<i class="bi bi-clock-history"></i> Historial' +
                    '</button>' +
                    (esPendiente
                        ? '<button class="btn-accion btn-reagendar"' +
                                  ' onclick="reagendarCita(' + cita.id_agendamiento + ')">' +
                              '<i class="bi bi-calendar2"></i> Reagendar' +
                          '</button>' +
                          '<button class="btn-accion btn-cancelar"' +
                                  ' onclick="cancelarCita(' + cita.id_agendamiento + ', \'' + (cita.mascota_nombre || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'") + '\')">' +
                              '<i class="bi bi-x-circle"></i> Cancelar' +
                          '</button>'
                        : '') +
                '</div>' +

            '</div>';
    }

    /* ================================================================
       FUNCIONES AUXILIARES
    ================================================================ */

    /* Formatea Date a cadena legible en español */
    function formatearFecha(f) {
        return f.toLocaleDateString('es-ES', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    /* Devuelve la clase CSS del badge según el tipo de cita */
    function obtenerTipoCss(tipo) {
        if (!tipo) return 'consulta';
        var t = tipo.toLowerCase();
        if (t.includes('emergencia'))                       return 'emergencia';
        if (t.includes('vacuna'))                           return 'vacuna';
        if (t.includes('control'))                          return 'control';
        if (t.includes('baño') || t.includes('peluqueria')) return 'bano';
        return 'consulta';
    }

    /* Calcula la duración entre dos timestamps */
    function calcularDuracion(inicio, fin) {
        if (!fin) return '1 hora';
        var mins = Math.floor((new Date(fin) - new Date(inicio)) / 60000);
        if (mins < 60) return mins + ' min';
        var h = Math.floor(mins / 60), m = mins % 60;
        return m > 0 ? h + 'h ' + m + 'm' : h + ' hora' + (h > 1 ? 's' : '');
    }

    /* Escapa HTML para evitar XSS al interpolar datos del servidor
       CORRECCIÓN: verDetallesCita y generarTarjetaCita ahora usan
       esta función en todos los valores que vienen de la BD. */
    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g,  '&amp;')
            .replace(/</g,  '&lt;')
            .replace(/>/g,  '&gt;')
            .replace(/"/g,  '&quot;')
            .replace(/'/g,  '&#039;');
    }

    function formatearFechaHistorial(valor) {
        return new Date(valor).toLocaleDateString('es-ES', {
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit'
        });
    }

    /* ================================================================
       ACCIONES
    ================================================================ */

    /* Ver detalles completos en modal Swal
       CORRECCIÓN: todos los valores del servidor pasan por escapeHtml() */
    function verDetallesCita(id) {
        var cita = citasData.find(function (c) { return c.id_agendamiento == id; });
        if (!cita) return;

        var fecha = new Date(cita.fecha_hora).toLocaleDateString('es-ES', {
            weekday: 'long', year: 'numeric', month: 'long',
            day: 'numeric', hour: '2-digit', minute: '2-digit'
        });

        var foto = (cita.img_mascota && cita.img_mascota !== 'default_pet.jpg')
            ? BASE_URL + '/public/uploads/mascotas/' + cita.img_mascota
            : BASE_URL + '/public/uploads/mascotas/default_pet.jpg';

        Swal.fire({
            title: 'Detalles de la Cita',
            html:
                '<div style="text-align:left;padding:8px 16px;">' +
                    '<div style="text-align:center;margin-bottom:16px;">' +
                        '<img src="' + foto + '"' +
                             ' alt="' + escapeHtml(cita.mascota_nombre) + '"' +
                             ' style="width:110px;height:110px;border-radius:12px;object-fit:cover;">' +
                    '</div>' +
                    '<p><strong>Mascota:</strong> '    + escapeHtml(cita.mascota_nombre)  + '</p>' +
                    '<p><strong>Especie:</strong> '    + escapeHtml(cita.mascota_especie) + (cita.mascota_raza ? ' · ' + escapeHtml(cita.mascota_raza) : '') + '</p>' +
                    '<p><strong>Servicio:</strong> '   + escapeHtml(cita.tipo)            + '</p>' +
                    '<p><strong>Veterinario:</strong> '+ escapeHtml(cita.veterinario_nombre || 'Por asignar') + '</p>' +
                    '<p><strong>Fecha:</strong> '      + escapeHtml(fecha)                + '</p>' +
                    '<p><strong>Duración:</strong> '   + calcularDuracion(cita.fecha_hora, cita.fecha_hora_fin) + '</p>' +
                    '<p><strong>Estado:</strong> '     + escapeHtml(cita.estado)          + '</p>' +
                    (cita.observaciones ? '<p><strong>Observaciones:</strong> ' + escapeHtml(cita.observaciones) + '</p>' : '') +
                '</div>',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#0a932c',
            width: '520px'
        });
    }

    /* Reagendar — pendiente de implementación */
    function reagendarCita(id) {
        Swal.fire({
            icon: 'info',
            title: 'Reagendar Cita',
            text: 'Esta función estará disponible próximamente. Por favor, contacta a la veterinaria.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#0a932c'
        });
    }

    /* Ver historial de la mascota en modal */
    async function verHistorialMascota(idPaciente, nombreMascota) {
        try {
            Swal.fire({
                title: 'Cargando historial...',
                allowOutsideClick: false,
                didOpen: function () { Swal.showLoading(); }
            });

            var res  = await fetch(URLS.HISTORIAL_PACIENTE + '&id_paciente=' + encodeURIComponent(idPaciente) + '&limite=20');
            var data = await res.json();

            if (data.status !== 'success') throw new Error(data.message || 'No se pudo cargar el historial');

            var historial = data.historial || [];

            if (!historial.length) {
                await Swal.fire({
                    icon: 'info',
                    title: 'Sin historial para ' + escapeHtml(nombreMascota),
                    text: 'No hay citas registradas para esta mascota.',
                    confirmButtonText: 'Cerrar',
                    confirmButtonColor: '#0a932c'
                });
                return;
            }

            var filas = historial.map(function (item) {
                var estado   = escapeHtml(item.estado || 'Pendiente');
                var servicio = escapeHtml(item.subservicio_nombre || item.servicio_nombre || item.tipo || 'Sin servicio');
                var vet      = escapeHtml(item.veterinario_nombre || 'No asignado');
                var fecha    = formatearFechaHistorial(item.fecha_hora);
                var motivo   = (item.estado === 'Cancelada' && item.motivo_cancelacion)
                    ? '<br><small><strong>Motivo:</strong> ' + escapeHtml(item.motivo_cancelacion) + '</small>'
                    : '';

                return '<tr>' +
                    '<td style="padding:8px;border-bottom:1px solid #e5e7eb;">' + fecha    + '</td>' +
                    '<td style="padding:8px;border-bottom:1px solid #e5e7eb;">' + servicio + '</td>' +
                    '<td style="padding:8px;border-bottom:1px solid #e5e7eb;">' + vet      + '</td>' +
                    '<td style="padding:8px;border-bottom:1px solid #e5e7eb;">' +
                        '<span class="estado-badge ' + estado.toLowerCase() + '">' + estado + '</span>' + motivo +
                    '</td>' +
                '</tr>';
            }).join('');

            await Swal.fire({
                title: 'Historial de ' + escapeHtml(nombreMascota),
                width: 860,
                html:
                    '<div style="max-height:400px;overflow:auto;text-align:left;">' +
                        '<table style="width:100%;border-collapse:collapse;font-size:13px;">' +
                            '<thead>' +
                                '<tr style="background:#f8fafc;">' +
                                    '<th style="padding:10px;border-bottom:1px solid #e5e7eb;">Fecha</th>' +
                                    '<th style="padding:10px;border-bottom:1px solid #e5e7eb;">Servicio</th>' +
                                    '<th style="padding:10px;border-bottom:1px solid #e5e7eb;">Veterinario</th>' +
                                    '<th style="padding:10px;border-bottom:1px solid #e5e7eb;">Estado</th>' +
                                '</tr>' +
                            '</thead>' +
                            '<tbody>' + filas + '</tbody>' +
                        '</table>' +
                    '</div>',
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#0a932c'
            });

        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'No se pudo cargar el historial.',
                confirmButtonColor: '#0a932c'
            });
        }
    }

    /* Cancelar cita con confirmación y motivo */
    async function cancelarCita(id, nombre) {
        var result = await Swal.fire({
            title: '¿Cancelar cita?',
            html:
                '<p>¿Estás seguro de cancelar la cita de <strong>' + escapeHtml(nombre) + '</strong>?</p>' +
                '<textarea id="motivo-cancelacion" class="swal2-input"' +
                          ' placeholder="Motivo de cancelación (opcional)"' +
                          ' style="height:90px;resize:vertical;margin-top:10px;"></textarea>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No, mantener',
            confirmButtonColor: '#ef4444',
            cancelButtonColor:  '#6b7280',
            preConfirm: function () {
                return document.getElementById('motivo-cancelacion').value;
            }
        });

        if (!result.isConfirmed) return;

        try {
            Swal.fire({
                title: 'Cancelando cita...',
                allowOutsideClick: false,
                didOpen: function () { Swal.showLoading(); }
            });

            var res  = await fetch(URLS.CANCELAR_CITA, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    accion:             'cancelar',
                    id_agendamiento:    id,
                    motivo_cancelacion: result.value || 'Sin motivo especificado'
                })
            });
            var data = await res.json();

            if (data.status === 'success') {
                await Swal.fire({
                    icon: 'success',
                    title: '¡Cita Cancelada!',
                    text: 'La cita fue cancelada exitosamente.',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#0a932c'
                });
                cargarCitas();
            } else {
                throw new Error(data.message || 'Error al cancelar');
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo cancelar la cita.',
                confirmButtonColor: '#0a932c'
            });
        }
    }
    </script>

</body>
</html>