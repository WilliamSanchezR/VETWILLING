<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']['id_usuario'])) {
    header('Location: ' . BASE_URL . '/login');
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/citas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">

    <style>
        /* ═══════════════════════════════════════════════════════════ */
        /*  LAYOUT GENERAL                                            */
        /* ═══════════════════════════════════════════════════════════ */
        .container-dashboard {
            padding: 30px 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  HEADER                                                    */
        /* ═══════════════════════════════════════════════════════════ */
        .header-citas {
            background: linear-gradient(135deg, #0a932c 0%, #0c7a25 100%);
            border-radius: 20px;
            padding: 35px 30px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(10,147,44,.2);
            position: relative;
            overflow: hidden;
        }
        .header-citas::before {
            content: "";
            position: absolute;
            top: -50%; right: -5%;
            width: 250px; height: 250px;
            background: rgba(255,255,255,.1);
            border-radius: 50%;
            z-index: 0;
        }
        .header-titulo { position: relative; z-index: 1; }
        .header-titulo h1 {
            font-size: 32px; font-weight: 700;
            margin: 0 0 8px;
            display: flex; align-items: center; gap: 12px;
        }
        .header-titulo h1 i { font-size: 36px; }
        .header-titulo p { font-size: 16px; margin: 0; opacity: .95; }

        /* ═══════════════════════════════════════════════════════════ */
        /*  ESTADÍSTICAS                                              */
        /* ═══════════════════════════════════════════════════════════ */
        .stats-rapidas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px,1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-item {
            background: white; border-radius: 16px;
            padding: 25px 20px;
            display: flex; align-items: center; gap: 18px;
            box-shadow: 0 5px 15px rgba(0,0,0,.05);
            transition: all .3s ease;
        }
        .stat-item:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,.1); }
        .stat-icon {
            width: 60px; height: 60px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; font-weight: bold;
        }
        .stat-icon.proximas    { background: linear-gradient(135deg,#3b82f6,#2563eb); color: white; }
        .stat-icon.completadas { background: linear-gradient(135deg,#10b981,#059669); color: white; }
        .stat-icon.pendientes  { background: linear-gradient(135deg,#f59e0b,#d97706); color: white; }
        .stat-icon.canceladas  { background: linear-gradient(135deg,#ef4444,#dc2626); color: white; }
        .stat-content h3 { font-size: 32px; font-weight: 700; color: #00304D; margin: 0; line-height: 1; }
        .stat-content p  { font-size: 14px; color: #666; margin: 5px 0 0; }

        /* ═══════════════════════════════════════════════════════════ */
        /*  TABS                                                      */
        /* ═══════════════════════════════════════════════════════════ */
        .tabs-container {
            display: flex; gap: 10px;
            margin-bottom: 25px;
            overflow-x: auto; padding-bottom: 5px;
        }
        .tab-btn {
            padding: 12px 25px;
            border: 2px solid #e0e0e0;
            background: white; color: #666;
            border-radius: 12px; font-weight: 600; font-size: 14px;
            cursor: pointer; transition: all .3s ease; white-space: nowrap;
        }
        .tab-btn:hover { border-color: #0a932c; color: #0a932c; background: rgba(10,147,44,.05); }
        .tab-btn.active {
            background: linear-gradient(135deg,#0a932c,#0c7a25);
            border-color: #0a932c; color: white;
            box-shadow: 0 4px 12px rgba(10,147,44,.3);
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  FILTROS                                                   */
        /* ═══════════════════════════════════════════════════════════ */
        .filtros-avanzados {
            background: white; border-radius: 16px;
            padding: 25px; margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,.05);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px,1fr));
            gap: 20px;
        }
        .filtro-grupo { display: flex; flex-direction: column; gap: 8px; }
        .filtro-grupo label { font-weight: 600; color: #00304D; font-size: 14px; }
        .filtro-grupo select,
        .filtro-grupo input {
            padding: 10px 14px;
            border: 2px solid #e0e0e0; border-radius: 10px;
            font-size: 14px; transition: all .3s ease; background: #fafafa;
        }
        .filtro-grupo select:focus,
        .filtro-grupo input:focus {
            outline: none; border-color: #0a932c;
            background: white; box-shadow: 0 0 0 3px rgba(10,147,44,.1);
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  TIMELINE                                                  */
        /* ═══════════════════════════════════════════════════════════ */
        .citas-timeline { display: flex; flex-direction: column; gap: 30px; }
        .timeline-dia {
            background: white; border-radius: 16px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,.05);
        }
        .dia-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 25px; padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .dia-fecha h3 { font-size: 20px; font-weight: 700; color: #00304D; margin: 0 0 5px; }
        .dia-fecha h3::first-letter { text-transform: uppercase; }
        .dia-fecha p  { font-size: 14px; color: #666; margin: 0; }
        .dia-badge {
            background: linear-gradient(135deg,#0a932c,#0c7a25);
            color: white; padding: 8px 16px;
            border-radius: 20px; font-weight: 600; font-size: 13px;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  GRID DE TARJETAS                                          */
        /* ═══════════════════════════════════════════════════════════ */
        .citas-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        @media (max-width: 1200px) { .citas-grid { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 700px)  { .citas-grid { grid-template-columns: 1fr; } }

        /* ═══════════════════════════════════════════════════════════ */
        /*  TARJETA DE CITA                                           */
        /* ═══════════════════════════════════════════════════════════ */
        .cita-card {
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 18px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            transition: all .3s ease;
        }
        .cita-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,.1);
            transform: translateY(-3px);
            border-color: #d1d5db;
        }

        /* Barra lateral izquierda según estado */
        .cita-card::after {
            content: "";
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 5px;
            border-radius: 18px 0 0 18px;
        }
        .cita-card[data-estado="Confirmada"]::after { background: #3b82f6; }
        .cita-card[data-estado="Pendiente"]::after  { background: #f59e0b; }
        .cita-card[data-estado="Realizada"]::after  { background: #10b981; }
        .cita-card[data-estado="Cancelada"]::after  { background: #ef4444; }
        .cita-card.urgente::after { background: #ef4444; }

        /* Etiqueta urgente */
        .urgente-label {
            display: none;
            position: absolute; top: 12px; right: 12px;
            background: #ef4444; color: white;
            padding: 3px 10px; border-radius: 20px;
            font-size: 10px; font-weight: 700;
            letter-spacing: .5px; text-transform: uppercase;
            z-index: 2;
        }
        .cita-card.urgente .urgente-label { display: block; }

        /* ── CABECERA: hora izquierda · tipo derecha ── */
        .cita-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 50px;
            padding: 14px 16px 12px 22px;
            border-bottom: 1.5px solid #f3f4f6;
        }
        .cita-hora-grande { display: flex; align-items: baseline; gap: 5px; flex-shrink: 0; }
        .hora-h  { font-size: 26px; font-weight: 800; color: #111827; line-height: 1; }
        .hora-am { font-size: 12px; font-weight: 600; color: #9ca3af; }

        /* ── INTERIOR: columna única ── */
        .cita-card-inner {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        /* ── COLUMNA DE CONTENIDO ── */
        .cita-card-content {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 0;
        }

        /* ── FILA: avatar (50%) + info (50%) ── */
        .cita-card-body {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            gap: 30px;
            padding: 14px 16px 14px 22px;
        }

        .cita-mascota-avatar {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            background-size: cover;
            background-position: center;
            background-color: #f3f4f6;
            flex-shrink: 0;
            border: 2px solid #e5e7eb;
        }

        .cita-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .cita-nombre-mascota {
            font-size: 15px; font-weight: 700;
            color: #111827; margin: 0; line-height: 1.2;
        }

        .cita-meta { display: flex; flex-direction: column; gap: 3px; }
        .cita-meta-item {
            display: flex; align-items: center;
            gap: 5px; font-size: 12px; color: #6b7280;
        }
        .cita-meta-item i { font-size: 12px; color: #0a932c; flex-shrink: 0; width: 13px; }

        /* Badge de estado */
        .estado-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
            margin-top: 3px; width: fit-content;
        }
        .estado-badge::before {
            content: ""; width: 6px; height: 6px;
            border-radius: 50%; flex-shrink: 0;
        }
        .estado-badge.confirmada { background: #dbeafe; color: #2563eb; }
        .estado-badge.confirmada::before { background: #3b82f6; }
        .estado-badge.pendiente  { background: #fef3c7; color: #d97706; }
        .estado-badge.pendiente::before  { background: #f59e0b; }
        .estado-badge.realizada  { background: #d1fae5; color: #059669; }
        .estado-badge.realizada::before  { background: #10b981; }
        .estado-badge.cancelada  { background: #fee2e2; color: #dc2626; }
        .estado-badge.cancelada::before  { background: #ef4444; }

        /* ── OBSERVACIONES: fila completa debajo del body ── */
        .cita-notas {
            font-size: 12px; color: #6b7280;
            padding: 8px 12px 10px 22px;
            background: #f9fafb;
            border-top: 1.5px solid #f3f4f6;
            line-height: 1.5;
            width: 100%;
        }
        .cita-notas strong { color: #374151; font-weight: 600; }

        /* Tipo badge (cabecera) */
        .tipo-badge {
            padding: 5px 11px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .5px;
            display: inline-flex; align-items: center; gap: 5px;
            white-space: nowrap;
        }
        .tipo-badge::before {
            content: ""; width: 7px; height: 7px;
            border-radius: 50%; flex-shrink: 0;
        }
        .tipo-badge.emergencia { background: #fee2e2; color: #dc2626; }
        .tipo-badge.emergencia::before { background: #dc2626; }
        .tipo-badge.consulta   { background: #dbeafe; color: #2563eb; }
        .tipo-badge.consulta::before   { background: #2563eb; }
        .tipo-badge.vacuna     { background: #d1fae5; color: #059669; }
        .tipo-badge.vacuna::before     { background: #059669; }
        .tipo-badge.control    { background: #fef3c7; color: #d97706; }
        .tipo-badge.control::before    { background: #d97706; }
        .tipo-badge.bano       { background: #e0e7ff; color: #6366f1; }
        .tipo-badge.bano::before       { background: #6366f1; }

        /* ── ACCIONES HORIZONTALES (fila inferior) ── */
        .cita-acciones {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            border-top: 1.5px solid #f3f4f6;
            overflow: hidden;
        }

        .cita-acciones.solo-ver {
            grid-template-columns: 1fr;
        }

        .cita-acciones.solo-dos {
            grid-template-columns: repeat(2, 1fr);
        }

        .btn-accion {
            padding: 12px 8px;
            border: none;
            border-right: 1.5px solid #f3f4f6;
            border-radius: 8px; 
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: background .2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            white-space: nowrap;
            margin-top: 10px;
            margin-bottom: 8px;
        }
        .btn-accion:last-child { border-right: none; }
        .btn-accion i { font-size: 14px; }

        .btn-ver       { background: #f9fafb; color: #374151; }
        .btn-ver:hover { background: #f3f4f6; }

        .btn-historial       { background: #ecfeff; color: #0e7490; }
        .btn-historial:hover { background: #cffafe; }

        .btn-reagendar       { background: #fffbeb; color: #d97706; }
        .btn-reagendar:hover { background: #fef3c7; }

        .btn-cancelar       { background: #ef4444; color: #fff; }
        .btn-cancelar:hover { background: #dc2626; }

        /* ═══════════════════════════════════════════════════════════ */
        /*  ESTADO VACÍO                                              */
        /* ═══════════════════════════════════════════════════════════ */
        .estado-vacio {
            text-align: center; padding: 60px 20px;
            background: white; border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,.05);
        }
        .estado-vacio i { font-size: 80px; color: #e0e0e0; margin-bottom: 20px; }
        .estado-vacio h3 { font-size: 24px; font-weight: 700; color: #00304D; margin-bottom: 10px; }
        .estado-vacio p  { font-size: 16px; color: #666; margin-bottom: 25px; }
        .btn-agendar-nueva {
            padding: 14px 30px;
            background: linear-gradient(135deg,#0a932c,#0c7a25);
            color: white; border: none; border-radius: 12px;
            font-weight: 700; font-size: 15px; cursor: pointer;
            transition: all .3s ease;
            display: inline-flex; align-items: center; gap: 10px;
            text-decoration: none;
        }
        .btn-agendar-nueva:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(10,147,44,.4);
            color: white;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  SPINNER                                                   */
        /* ═══════════════════════════════════════════════════════════ */
        .loading-container { text-align: center; padding: 60px 20px; }
        .spinner {
            width: 60px; height: 60px;
            border: 5px solid #f0f0f0; border-top-color: #0a932c;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ═══════════════════════════════════════════════════════════ */
        /*  DARK MODE                                                 */
        /* ═══════════════════════════════════════════════════════════ */
        body.dark-mode .stat-item,
        body.dark-mode .timeline-dia,
        body.dark-mode .filtros-avanzados,
        body.dark-mode .estado-vacio          { background: #1e1e1e; }
        body.dark-mode .stat-content h3,
        body.dark-mode .dia-fecha h3,
        body.dark-mode .estado-vacio h3       { color: #fff; }
        body.dark-mode .cita-card             { background: #1f2937; border-color: #374151; }
        body.dark-mode .cita-card-header,
        body.dark-mode .cita-notas            { border-color: #374151; }
        body.dark-mode .hora-h,
        body.dark-mode .cita-nombre-mascota   { color: #f9fafb; }
        body.dark-mode .cita-notas            { background: #111827; }
        body.dark-mode .cita-acciones         { border-left-color: #374151; }
        body.dark-mode .btn-accion            { border-bottom-color: #374151; }
        body.dark-mode .btn-ver               { background: #374151; color: #d1d5db; }
        body.dark-mode .btn-historial         { background: #164e63; color: #bae6fd; }
        body.dark-mode .filtro-grupo select,
        body.dark-mode .filtro-grupo input    { background: #2d2d2d; border-color: #444; color: #fff; }
        body.dark-mode .tab-btn               { background: #2d2d2d; border-color: #444; color: #ddd; }
        body.dark-mode .dia-header            { border-bottom-color: #374151; }
        body.dark-mode .cita-card-body        { border-color: #374151; }

        /* ═══════════════════════════════════════════════════════════ */
        /*  RESPONSIVE                                                */
        /* ═══════════════════════════════════════════════════════════ */
        @media (max-width: 768px) {
            .header-citas          { padding: 25px 20px; }
            .header-titulo h1      { font-size: 24px; }
            .stats-rapidas         { grid-template-columns: repeat(2,1fr); gap: 15px; }
            .filtros-avanzados     { grid-template-columns: 1fr; }
            .cita-mascota-avatar   { max-width: 60px; }
        }
    </style>
</head>

<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <main class="contenido-principal" id="contenidoPrincipal">

        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <div class="container-dashboard">

                <!-- Header -->
                <div class="header-citas">
                    <div class="header-titulo">
                        <h1><i class="bi bi-calendar2-week"></i> Mis Citas</h1>
                        <p>Gestiona tus citas veterinarias</p>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="stats-rapidas">
                    <div class="stat-item">
                        <div class="stat-icon proximas"><i class="bi bi-calendar2-week"></i></div>
                        <div class="stat-content"><h3 id="stat-proximas">-</h3><p>Próximas citas</p></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon completadas">✓</div>
                        <div class="stat-content"><h3 id="stat-completadas">-</h3><p>Completadas</p></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon pendientes">⏱</div>
                        <div class="stat-content"><h3 id="stat-hoy">-</h3><p>Hoy</p></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon canceladas">✕</div>
                        <div class="stat-content"><h3 id="stat-canceladas">-</h3><p>Canceladas</p></div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="tabs-container">
                    <button class="tab-btn active" data-filtro="todas">Todas</button>
                    <button class="tab-btn" data-filtro="hoy">Hoy</button>
                    <button class="tab-btn" data-filtro="proximas">Próximas</button>
                    <button class="tab-btn" data-filtro="esta-semana">Esta Semana</button>
                    <button class="tab-btn" data-filtro="pendientes">Pendientes</button>
                    <button class="tab-btn" data-filtro="canceladas">Canceladas</button>
                </div>

                <!-- Filtros -->
                <div class="filtros-avanzados">
                    <div class="filtro-grupo">
                        <label>Mascota</label>
                        <select id="filtro-mascota"><option value="">Todas las mascotas</option></select>
                    </div>
                    <div class="filtro-grupo">
                        <label>Estado</label>
                        <select id="filtro-estado">
                            <option value="">Todos los estados</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Confirmada">Confirmada</option>
                            <option value="Realizada">Realizada</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <label>Desde</label>
                        <input type="date" id="filtro-fecha-inicio">
                    </div>
                    <div class="filtro-grupo">
                        <label>Hasta</label>
                        <input type="date" id="filtro-fecha-fin">
                    </div>
                </div>

                <!-- Citas -->
                <div class="citas-timeline" id="citasContainer">
                    <div class="loading-container">
                        <div class="spinner"></div>
                        <p>Cargando tus citas...</p>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const URLS = {
            MIS_CITAS:     BASE_URL + '/cliente/api/citas/listar?accion=listar',
            HISTORIAL_PACIENTE: BASE_URL + '/cliente/api/citas/listar?accion=historial_paciente',
            CANCELAR_CITA: BASE_URL + '/cliente/api/citas/cancelar'
        };

        let citasData  = [];
        let filtroActual = 'todas';

        // ─── INIT ────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            cargarCitas();

            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    filtroActual = this.dataset.filtro;
                    aplicarFiltros();
                });
            });

            ['filtro-mascota','filtro-estado','filtro-fecha-inicio','filtro-fecha-fin']
                .forEach(id => document.getElementById(id).addEventListener('change', aplicarFiltros));
        });

        // ─── CARGAR ──────────────────────────────────────────────────
        async function cargarCitas() {
            try {
                const res  = await fetch(URLS.MIS_CITAS);
                const data = await res.json();
                if (data.status !== 'success') throw new Error(data.message || 'Error al cargar citas');
                citasData = data.citas || [];
                actualizarEstadisticas(citasData);
                cargarFiltroMascotas(citasData);
                renderizarCitas(citasData);
            } catch (err) {
                document.getElementById('citasContainer').innerHTML = `
                    <div class="estado-vacio">
                        <i class="bi bi-exclamation-triangle"></i>
                        <h3>Error al cargar las citas</h3>
                        <p>${err.message}</p>
                        <button onclick="cargarCitas()" class="btn-agendar-nueva">
                            <i class="bi bi-arrow-clockwise"></i> Reintentar
                        </button>
                    </div>`;
            }
        }

        // ─── ESTADÍSTICAS ────────────────────────────────────────────
        function actualizarEstadisticas(citas) {
            const hoy = new Date(); hoy.setHours(0,0,0,0);
            document.getElementById('stat-proximas').textContent   = citas.filter(c => new Date(c.fecha_hora) >= hoy && c.estado !== 'Cancelada' && c.estado !== 'Realizada').length;
            document.getElementById('stat-completadas').textContent = citas.filter(c => c.estado === 'Realizada').length;
            document.getElementById('stat-hoy').textContent        = citas.filter(c => new Date(c.fecha_hora).toDateString() === hoy.toDateString() && c.estado !== 'Cancelada').length;
            document.getElementById('stat-canceladas').textContent  = citas.filter(c => c.estado === 'Cancelada').length;
        }

        // ─── FILTRO MASCOTAS ─────────────────────────────────────────
        function cargarFiltroMascotas(citas) {
            const sel = document.getElementById('filtro-mascota');
            const unicas = [...new Set(citas.map(c => c.mascota_nombre))].filter(Boolean);
            sel.innerHTML = '<option value="">Todas las mascotas</option>' +
                unicas.map(m => `<option value="${m}">${m}</option>`).join('');
        }

        // ─── FILTROS ─────────────────────────────────────────────────
        function aplicarFiltros() {
            let f = [...citasData];
            const hoy = new Date(); hoy.setHours(0,0,0,0);

            if (filtroActual === 'proximas')    f = f.filter(c => new Date(c.fecha_hora) >= hoy && c.estado !== 'Cancelada' && c.estado !== 'Realizada');
            else if (filtroActual === 'hoy')    f = f.filter(c => new Date(c.fecha_hora).toDateString() === hoy.toDateString() && c.estado !== 'Cancelada');
            else if (filtroActual === 'esta-semana') {
                const fin = new Date(hoy); fin.setDate(fin.getDate() + 7);
                f = f.filter(c => { const d = new Date(c.fecha_hora); return d >= hoy && d <= fin && c.estado !== 'Cancelada'; });
            } else if (filtroActual === 'pendientes') f = f.filter(c => c.estado === 'Pendiente');
            else if (filtroActual === 'canceladas')   f = f.filter(c => c.estado === 'Cancelada');

            const mascota = document.getElementById('filtro-mascota').value;
            if (mascota) f = f.filter(c => c.mascota_nombre === mascota);

            const estado = document.getElementById('filtro-estado').value;
            if (estado) f = f.filter(c => c.estado === estado);

            const desde = document.getElementById('filtro-fecha-inicio').value;
            if (desde) { const [y,m,d] = desde.split('-'); f = f.filter(c => new Date(c.fecha_hora) >= new Date(+y,+m-1,+d)); }

            const hasta = document.getElementById('filtro-fecha-fin').value;
            if (hasta) { const [y,m,d] = hasta.split('-'); f = f.filter(c => new Date(c.fecha_hora) <= new Date(+y,+m-1,+d,23,59,59)); }

            renderizarCitas(f);
        }

        // ─── RENDERIZAR ──────────────────────────────────────────────
        function renderizarCitas(citas) {
            const container = document.getElementById('citasContainer');
            if (!citas.length) {
                container.innerHTML = `
                    <div class="estado-vacio">
                        <i class="bi bi-calendar-x"></i>
                        <h3>No tienes citas</h3>
                        <p>No hay citas programadas en este momento</p>
                        <a href="${BASE_URL}/cliente/agendar-cita" class="btn-agendar-nueva">
                            <i class="bi bi-calendar-plus"></i> Agendar Nueva Cita
                        </a>
                    </div>`;
                return;
            }

            const grupos = agruparPorDia(citas);
            const hoy    = new Date(); hoy.setHours(0,0,0,0);
            let html = '';

            for (const [fecha, lista] of Object.entries(grupos)) {
                const [y,m,d] = fecha.split('-');
                const fObj    = new Date(+y, +m-1, +d);
                const diff    = Math.floor((fObj - hoy) / 86400000);
                const etiqueta = diff === 0 ? 'Hoy' : diff === 1 ? 'Mañana' : diff > 1 ? `En ${diff} días` : 'Pasada';

                html += `
                    <div class="timeline-dia">
                        <div class="dia-header">
                            <div class="dia-fecha">
                                <h3>${formatearFecha(fObj)}</h3>
                                <p>${etiqueta}</p>
                            </div>
                            <span class="dia-badge">${lista.length} cita${lista.length > 1 ? 's' : ''}</span>
                        </div>
                        <div class="citas-grid">
                            ${lista.map(generarTarjetaCita).join('')}
                        </div>
                    </div>`;
            }
            container.innerHTML = html;
        }

        // ─── AGRUPAR POR DÍA ─────────────────────────────────────────
        function agruparPorDia(citas) {
            const g = {};
            citas.forEach(c => {
                const f = new Date(c.fecha_hora);
                const k = `${f.getFullYear()}-${String(f.getMonth()+1).padStart(2,'0')}-${String(f.getDate()).padStart(2,'0')}`;
                (g[k] = g[k] || []).push(c);
            });
            return Object.fromEntries(Object.keys(g).sort().map(k => [k, g[k]]));
        }

        // ─── GENERAR TARJETA ─────────────────────────────────────────
        function generarTarjetaCita(cita) {
            const fecha   = new Date(cita.fecha_hora);
            const horaStr = fecha.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: false });
            const periodo = fecha.getHours() >= 12 ? 'PM' : 'AM';
            const tipoCss = obtenerTipoBadge(cita.tipo);
            const esUrg   = cita.tipo && cita.tipo.toLowerCase().includes('emergencia');

            const foto = cita.img_mascota && cita.img_mascota !== 'default_pet.jpg'
                ? `${BASE_URL}/public/uploads/mascotas/${cita.img_mascota}`
                : `${BASE_URL}/public/uploads/mascotas/default_pet.jpg`;

            const estadoClass = { Confirmada:'confirmada', Pendiente:'pendiente', Realizada:'realizada', Cancelada:'cancelada' }[cita.estado] || 'pendiente';
            const esPendiente = cita.estado === 'Pendiente';

            return `
                <div class="cita-card ${esUrg ? 'urgente' : ''}"
                     data-cita-id="${cita.id_agendamiento}"
                     data-estado="${cita.estado}">

                    <span class="urgente-label">⚠ Urgente</span>

                    <!-- Cabecera -->
                    <div class="cita-card-header">
                        <div class="cita-hora-grande">
                            <span class="hora-h">${horaStr}</span>
                            <span class="hora-am">${periodo}</span>
                        </div>
                        <span class="tipo-badge ${tipoCss}">${cita.tipo || 'Consulta'}</span>
                    </div>

                    <!-- Interior: body + observaciones -->
                    <div class="cita-card-inner">
                        <div class="cita-card-content">
                            <!-- Fila: avatar + info -->
                            <div class="cita-card-body">
                                <div class="cita-mascota-avatar"
                                     style="background-image:url('${foto}');"></div>
                                <div class="cita-info">
                                    <p class="cita-nombre-mascota">${cita.mascota_nombre || 'Mascota'}</p>
                                    <div class="cita-meta">
                                        <div class="cita-meta-item">
                                            <i class="bi bi-person-fill"></i>
                                            <span>${cita.veterinario_nombre || 'No asignado'}</span>
                                        </div>
                                        <div class="cita-meta-item">
                                            <i class="bi bi-heart-fill"></i>
                                            <span>${cita.mascota_especie || ''} · ${cita.mascota_raza || ''}</span>
                                        </div>
                                        <div class="cita-meta-item">
                                            <i class="bi bi-clock-fill"></i>
                                            <span>${calcularDuracion(cita.fecha_hora, cita.fecha_hora_fin)}</span>
                                        </div>
                                        <span class="estado-badge ${estadoClass}">${cita.estado}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Observaciones debajo (ancho completo) -->
                            ${cita.observaciones ? `
                            <div class="cita-notas">
                                <strong>Observaciones:</strong> ${cita.observaciones}
                            </div>` : ''}
                        </div>
                    </div>

                    <!-- Acciones horizontales debajo -->
                    <div class="cita-acciones ${esPendiente ? '' : 'solo-dos'}">
                        <button class="btn-accion btn-ver"
                                onclick="verDetallesCita(${cita.id_agendamiento})">
                            <i class="bi bi-eye"></i> Ver
                        </button>
                        <button class="btn-accion btn-historial"
                                onclick="verHistorialMascota(${cita.id_paciente}, ${JSON.stringify(cita.mascota_nombre || 'Mascota')})">
                            <i class="bi bi-clock-history"></i> Historial
                        </button>
                        ${esPendiente ? `
                            <button class="btn-accion btn-reagendar"
                                    onclick="reagendarCita(${cita.id_agendamiento})">
                                <i class="bi bi-calendar2"></i> Reagendar
                            </button>
                            <button class="btn-accion btn-cancelar"
                                    onclick="cancelarCita(${cita.id_agendamiento}, '${cita.mascota_nombre}')">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>` : ''}
                    </div>
                </div>`;
        }

        // ─── AUXILIARES ──────────────────────────────────────────────
        function formatearFecha(f) {
            return f.toLocaleDateString('es-ES', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
        }

        function obtenerTipoBadge(tipo) {
            if (!tipo) return 'consulta';
            const t = tipo.toLowerCase();
            if (t.includes('emergencia'))                         return 'emergencia';
            if (t.includes('vacuna'))                             return 'vacuna';
            if (t.includes('control'))                            return 'control';
            if (t.includes('baño') || t.includes('peluqueria'))   return 'bano';
            return 'consulta';
        }

        function calcularDuracion(inicio, fin) {
            if (!fin) return '1 hora';
            const mins = Math.floor((new Date(fin) - new Date(inicio)) / 60000);
            if (mins < 60) return `${mins} minutos`;
            const h = Math.floor(mins / 60), m = mins % 60;
            return m > 0 ? `${h}h ${m}m` : `${h} hora${h > 1 ? 's' : ''}`;
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatearFechaHistorial(valor) {
            const f = new Date(valor);
            return f.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // ─── ACCIONES ────────────────────────────────────────────────
        function verDetallesCita(id) {
            const cita = citasData.find(c => c.id_agendamiento == id);
            if (!cita) return;
            const fecha = new Date(cita.fecha_hora).toLocaleDateString('es-ES', {
                weekday:'long', year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit'
            });
            const foto = cita.img_mascota && cita.img_mascota !== 'default_pet.jpg'
                ? `${BASE_URL}/public/uploads/mascotas/${cita.img_mascota}`
                : `${BASE_URL}/public/uploads/mascotas/default_pet.jpg`;

            Swal.fire({
                title: 'Detalles de la Cita',
                html: `
                    <div style="text-align:left;padding:10px 20px;">
                        <div style="text-align:center;margin-bottom:20px;">
                            <img src="${foto}" alt="${cita.mascota_nombre}"
                                 style="width:130px;height:130px;border-radius:12px;object-fit:cover;">
                        </div>
                        <p><strong>Mascota:</strong> ${cita.mascota_nombre}</p>
                        <p><strong>Especie:</strong> ${cita.mascota_especie} · ${cita.mascota_raza}</p>
                        <p><strong>Servicio:</strong> ${cita.tipo}</p>
                        <p><strong>Veterinario:</strong> ${cita.veterinario_nombre || 'Por asignar'}</p>
                        <p><strong>Fecha:</strong> ${fecha}</p>
                        <p><strong>Duración:</strong> ${calcularDuracion(cita.fecha_hora, cita.fecha_hora_fin)}</p>
                        <p><strong>Estado:</strong> ${cita.estado}</p>
                        ${cita.observaciones ? `<p><strong>Observaciones:</strong> ${cita.observaciones}</p>` : ''}
                    </div>`,
                confirmButtonText: 'Cerrar',
                width: '580px'
            });
        }

        function reagendarCita(id) {
            Swal.fire({
                icon: 'info',
                title: 'Reagendar Cita',
                text: 'Esta función estará disponible próximamente. Por favor, contacta a la veterinaria.',
                confirmButtonText: 'Entendido'
            });
        }

        async function verHistorialMascota(idPaciente, nombreMascota) {
            try {
                Swal.fire({
                    title: 'Cargando historial...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const res = await fetch(`${URLS.HISTORIAL_PACIENTE}&id_paciente=${encodeURIComponent(idPaciente)}&limite=20`);
                const data = await res.json();

                if (data.status !== 'success') {
                    throw new Error(data.message || 'No se pudo cargar el historial');
                }

                const historial = data.historial || [];
                if (!historial.length) {
                    await Swal.fire({
                        icon: 'info',
                        title: `Sin historial para ${escapeHtml(nombreMascota)}`,
                        text: 'No hay citas registradas para esta mascota.',
                        confirmButtonText: 'Cerrar'
                    });
                    return;
                }

                const filas = historial.map(item => {
                    const estado = escapeHtml(item.estado || 'Pendiente');
                    const servicio = escapeHtml(item.subservicio_nombre || item.servicio_nombre || item.tipo || 'Sin servicio');
                    const vet = escapeHtml(item.veterinario_nombre || 'No asignado');
                    const fecha = formatearFechaHistorial(item.fecha_hora);
                    const motivoCancelacion = item.estado === 'Cancelada' && item.motivo_cancelacion
                        ? `<br><small><strong>Motivo:</strong> ${escapeHtml(item.motivo_cancelacion)}</small>`
                        : '';

                    return `
                        <tr>
                            <td style="padding:8px;border-bottom:1px solid #e5e7eb;">${fecha}</td>
                            <td style="padding:8px;border-bottom:1px solid #e5e7eb;">${servicio}</td>
                            <td style="padding:8px;border-bottom:1px solid #e5e7eb;">${vet}</td>
                            <td style="padding:8px;border-bottom:1px solid #e5e7eb;">
                                <span class="estado-badge ${estado.toLowerCase()}">${estado}</span>${motivoCancelacion}
                            </td>
                        </tr>`;
                }).join('');

                await Swal.fire({
                    title: `Historial de ${escapeHtml(nombreMascota)}`,
                    width: 900,
                    html: `
                        <div style="max-height:420px;overflow:auto;text-align:left;">
                            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                                <thead>
                                    <tr style="background:#f8fafc;">
                                        <th style="padding:10px;border-bottom:1px solid #e5e7eb;">Fecha</th>
                                        <th style="padding:10px;border-bottom:1px solid #e5e7eb;">Servicio</th>
                                        <th style="padding:10px;border-bottom:1px solid #e5e7eb;">Veterinario</th>
                                        <th style="padding:10px;border-bottom:1px solid #e5e7eb;">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${filas}
                                </tbody>
                            </table>
                        </div>`,
                    confirmButtonText: 'Cerrar'
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo cargar el historial de la mascota.'
                });
            }
        }

        async function cancelarCita(id, nombre) {
            const result = await Swal.fire({
                title: '¿Cancelar cita?',
                html: `
                    <p>¿Estás seguro de cancelar la cita de <strong>${nombre}</strong>?</p>
                    <textarea id="motivo-cancelacion" class="swal2-input"
                              placeholder="Motivo de cancelación (opcional)"
                              style="height:100px;resize:vertical;"></textarea>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, mantener',
                confirmButtonColor: '#dc2626',
                preConfirm: () => document.getElementById('motivo-cancelacion').value
            });

            if (!result.isConfirmed) return;

            try {
                Swal.fire({ title: 'Cancelando cita...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                const res  = await fetch(URLS.CANCELAR_CITA, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        accion: 'cancelar',
                        id_agendamiento: id,
                        motivo_cancelacion: result.value || 'Sin motivo especificado'
                    })
                });
                const data = await res.json();

                if (data.status === 'success') {
                    await Swal.fire({ icon:'success', title:'¡Cita Cancelada!', text:'La cita fue cancelada exitosamente.', confirmButtonText:'Aceptar' });
                    cargarCitas();
                } else {
                    throw new Error(data.message || 'Error al cancelar');
                }
            } catch (err) {
                Swal.fire({ icon:'error', title:'Error', text: err.message || 'No se pudo cancelar la cita.', confirmButtonText:'Entendido' });
            }
        }

        console.log('✅ Vista de Citas del Cliente cargada correctamente');
    </script>

</body>
</html>