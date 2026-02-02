<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté autenticado
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

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/citas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">

    <style>
        /* ═══════════════════════════════════════════════════════════ */
        /*  ESTILOS MEJORADOS PARA VISTA DE CITAS                     */
        /* ═══════════════════════════════════════════════════════════ */

        .container-dashboard {
            padding: 30px 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  HEADER PRINCIPAL                                          */
        /* ═══════════════════════════════════════════════════════════ */

        .header-citas {
            background: linear-gradient(135deg, #0a932c 0%, #0c7a25 100%);
            border-radius: 20px;
            padding: 35px 30px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(10, 147, 44, 0.2);
            position: relative;
            overflow: hidden;
        }

        .header-citas::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -5%;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: 0;
        }

        .header-titulo {
            position: relative;
            z-index: 1;
        }

        .header-titulo h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-titulo h1 i {
            font-size: 36px;
        }

        .header-titulo p {
            font-size: 16px;
            margin: 0;
            opacity: 0.95;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  ESTADÍSTICAS RÁPIDAS                                       */
        /* ═══════════════════════════════════════════════════════════ */

        .stats-rapidas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-item {
            background: white;
            border-radius: 16px;
            padding: 25px 20px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
        }

        .stat-icon.proximas {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .stat-icon.completadas {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .stat-icon.pendientes {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .stat-icon.canceladas {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .stat-content h3 {
            font-size: 32px;
            font-weight: 700;
            color: #00304D;
            margin: 0;
            line-height: 1;
        }

        .stat-content p {
            font-size: 14px;
            color: #666;
            margin: 5px 0 0 0;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  TABS DE FILTRADO                                          */
        /* ═══════════════════════════════════════════════════════════ */

        .tabs-container {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            overflow-x: auto;
            padding-bottom: 5px;
        }

        .tab-btn {
            padding: 12px 25px;
            border: 2px solid #e0e0e0;
            background: white;
            color: #666;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .tab-btn:hover {
            border-color: #0a932c;
            color: #0a932c;
            background: rgba(10, 147, 44, 0.05);
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #0a932c 0%, #0c7a25 100%);
            border-color: #0a932c;
            color: white;
            box-shadow: 0 4px 12px rgba(10, 147, 44, 0.3);
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  FILTROS AVANZADOS                                         */
        /* ═══════════════════════════════════════════════════════════ */

        .filtros-avanzados {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .filtro-grupo {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filtro-grupo label {
            font-weight: 600;
            color: #00304D;
            font-size: 14px;
        }

        .filtro-grupo select,
        .filtro-grupo input {
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .filtro-grupo select:focus,
        .filtro-grupo input:focus {
            outline: none;
            border-color: #0a932c;
            background: white;
            box-shadow: 0 0 0 3px rgba(10, 147, 44, 0.1);
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  TIMELINE DE CITAS                                         */
        /* ═══════════════════════════════════════════════════════════ */

        .citas-timeline {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .timeline-dia {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .dia-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .dia-fecha h3 {
            font-size: 20px;
            font-weight: 700;
            color: #00304D;
            margin: 0 0 5px 0;
        }

        .dia-fecha p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }

        .dia-badge {
            background: linear-gradient(135deg, #0a932c 0%, #0c7a25 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  TARJETAS DE CITAS                                         */
        /* ═══════════════════════════════════════════════════════════ */

        .cita-card {
            display: flex;
            gap: 20px;
            padding: 25px;
            background: #fafafa;
            border: 2px solid #e0e0e0;
            border-radius: 16px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            position: relative;
        }

        .cita-card:last-child {
            margin-bottom: 0;
        }

        .cita-card:hover {
            border-color: #0a932c;
            box-shadow: 0 5px 15px rgba(10, 147, 44, 0.1);
            transform: translateY(-3px);
        }

        .cita-card.urgente {
            border-color: #ef4444;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(220, 38, 38, 0.05) 100%);
        }

        .cita-card.urgente::before {
            content: "⚠️ URGENTE";
            position: absolute;
            top: -12px;
            left: 20px;
            background: #ef4444;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .cita-hora {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 80px;
            background: white;
            border-radius: 12px;
            padding: 15px 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .hora-numero {
            font-size: 24px;
            font-weight: 700;
            color: #0a932c;
            line-height: 1;
        }

        .hora-periodo {
            font-size: 13px;
            color: #666;
            font-weight: 600;
            margin-top: 5px;
        }

        .cita-mascota-avatar {
            font-size: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cita-info {
            flex: 1;
        }

        .cita-titulo {
            font-size: 18px;
            font-weight: 700;
            color: #00304D;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .tipo-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tipo-badge.emergencia {
            background: #fee2e2;
            color: #dc2626;
        }

        .tipo-badge.consulta {
            background: #dbeafe;
            color: #2563eb;
        }

        .tipo-badge.vacuna {
            background: #d1fae5;
            color: #059669;
        }

        .tipo-badge.control {
            background: #fef3c7;
            color: #d97706;
        }

        .tipo-badge.bano {
            background: #e0e7ff;
            color: #6366f1;
        }

        .cita-detalles {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 12px;
        }

        .detalle {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #666;
        }

        .detalle i {
            color: #0a932c;
            font-size: 16px;
        }

        .cita-notas {
            font-size: 14px;
            color: #666;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #0a932c;
        }

        .cita-acciones {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-accion {
            padding: 10px 18px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-accion i {
            font-size: 14px;
        }

        .btn-ver {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .btn-ver:hover {
            background: #c7d2fe;
            transform: translateY(-2px);
        }

        .btn-reagendar {
            background: #fef3c7;
            color: #d97706;
        }

        .btn-reagendar:hover {
            background: #fde68a;
            transform: translateY(-2px);
        }

        .btn-cancelar {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-cancelar:hover {
            background: #fecaca;
            transform: translateY(-2px);
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  ESTADO VACÍO                                              */
        /* ═══════════════════════════════════════════════════════════ */

        .estado-vacio {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .estado-vacio i {
            font-size: 80px;
            color: #e0e0e0;
            margin-bottom: 20px;
        }

        .estado-vacio h3 {
            font-size: 24px;
            font-weight: 700;
            color: #00304D;
            margin-bottom: 10px;
        }

        .estado-vacio p {
            font-size: 16px;
            color: #666;
            margin-bottom: 25px;
        }

        .btn-agendar-nueva {
            padding: 14px 30px;
            background: linear-gradient(135deg, #0a932c 0%, #0c7a25 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-agendar-nueva:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(10, 147, 44, 0.4);
            color: white;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  LOADING SPINNER                                           */
        /* ═══════════════════════════════════════════════════════════ */

        .loading-container {
            text-align: center;
            padding: 60px 20px;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #f0f0f0;
            border-top-color: #0a932c;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  DARK MODE                                                 */
        /* ═══════════════════════════════════════════════════════════ */

        body.dark-mode .stat-item,
        body.dark-mode .timeline-dia,
        body.dark-mode .filtros-avanzados,
        body.dark-mode .estado-vacio {
            background: #1e1e1e;
        }

        body.dark-mode .stat-content h3,
        body.dark-mode .dia-fecha h3,
        body.dark-mode .cita-titulo,
        body.dark-mode .estado-vacio h3 {
            color: #ffffff;
        }

        body.dark-mode .cita-card {
            background: #2d2d2d;
            border-color: #444;
        }

        body.dark-mode .cita-hora {
            background: #1e1e1e;
        }

        body.dark-mode .filtro-grupo select,
        body.dark-mode .filtro-grupo input {
            background: #2d2d2d;
            border-color: #444;
            color: #ffffff;
        }

        body.dark-mode .tab-btn {
            background: #2d2d2d;
            border-color: #444;
            color: #dddddd;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /*  RESPONSIVE                                                */
        /* ═══════════════════════════════════════════════════════════ */

        @media (max-width: 768px) {
            .header-citas {
                padding: 25px 20px;
            }

            .header-titulo h1 {
                font-size: 24px;
            }

            .stats-rapidas {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .cita-card {
                flex-direction: column;
            }

            .cita-hora {
                flex-direction: row;
                min-width: auto;
                width: 100%;
                justify-content: center;
            }

            .filtros-avanzados {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <!-- DASHBOARD CONTENT -->
            <div class="container-dashboard">

                <!-- Header -->
                <div class="header-citas">
                    <div class="header-titulo">
                        <h1>
                            <i class="bi bi-calendar2-week"></i>
                            Mis Citas
                        </h1>
                        <p>Gestiona tus citas veterinarias</p>
                    </div>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="stats-rapidas">
                    <div class="stat-item">
                        <div class="stat-icon proximas">
                            <i class="bi bi-calendar2-week"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="stat-proximas">-</h3>
                            <p>Próximas citas</p>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon completadas">✓</div>
                        <div class="stat-content">
                            <h3 id="stat-completadas">-</h3>
                            <p>Completadas</p>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon pendientes">⏱</div>
                        <div class="stat-content">
                            <h3 id="stat-hoy">-</h3>
                            <p>Hoy</p>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon canceladas">✕</div>
                        <div class="stat-content">
                            <h3 id="stat-canceladas">-</h3>
                            <p>Canceladas</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="tabs-container">
                    <button class="tab-btn active" data-filtro="todas">Todas</button>
                    <button class="tab-btn" data-filtro="proximas">Próximas</button>
                    <button class="tab-btn" data-filtro="hoy">Hoy</button>
                    <button class="tab-btn" data-filtro="esta-semana">Esta Semana</button>
                    <button class="tab-btn" data-filtro="pendientes">Pendientes</button>
                    <button class="tab-btn" data-filtro="canceladas">Canceladas</button>
                </div>

                <!-- Filtros Avanzados -->
                <div class="filtros-avanzados">
                    <div class="filtro-grupo">
                        <label>Mascota</label>
                        <select id="filtro-mascota">
                            <option value="">Todas las mascotas</option>
                            <!-- Se llenarán dinámicamente -->
                        </select>
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

                <!-- Timeline de Citas -->
                <div class="citas-timeline" id="citasContainer">
                    <!-- Loading inicial -->
                    <div class="loading-container">
                        <div class="spinner"></div>
                        <p>Cargando tus citas...</p>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>

    <script>
        // ═══════════════════════════════════════════════════════════
        //  CONFIGURACIÓN Y CONSTANTES
        // ═══════════════════════════════════════════════════════════

        const BASE_URL = '<?= BASE_URL ?>';
        const URLS = {
            MIS_CITAS: BASE_URL + '/calendario/cargar?accion=mis_citas',
            CANCELAR_CITA: BASE_URL + '/cancelarCita'
        };

        let citasData = []; // Almacenar todas las citas
        let filtroActual = 'todas';

        // ═══════════════════════════════════════════════════════════
        //  CARGAR CITAS AL INICIO
        // ═══════════════════════════════════════════════════════════

        document.addEventListener('DOMContentLoaded', function() {
            cargarCitas();

            // Event listeners para tabs
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remover active de todos
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    // Agregar active al clickeado
                    this.classList.add('active');
                    
                    // Aplicar filtro
                    filtroActual = this.getAttribute('data-filtro');
                    aplicarFiltros();
                });
            });

            // Event listeners para filtros avanzados
            document.getElementById('filtro-mascota').addEventListener('change', aplicarFiltros);
            document.getElementById('filtro-estado').addEventListener('change', aplicarFiltros);
            document.getElementById('filtro-fecha-inicio').addEventListener('change', aplicarFiltros);
            document.getElementById('filtro-fecha-fin').addEventListener('change', aplicarFiltros);
        });

        // ═══════════════════════════════════════════════════════════
        //  FUNCIÓN PARA CARGAR CITAS DESDE EL SERVIDOR
        // ═══════════════════════════════════════════════════════════

        async function cargarCitas() {
            try {
                const response = await fetch(URLS.MIS_CITAS);
                const result = await response.json();

                if (result.status === 'success') {
                    citasData = result.citas || [];
                    actualizarEstadisticas(citasData);
                    cargarFiltroMascotas(citasData);
                    renderizarCitas(citasData);
                } else {
                    throw new Error(result.message || 'Error al cargar citas');
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('citasContainer').innerHTML = `
                    <div class="estado-vacio">
                        <i class="bi bi-exclamation-triangle"></i>
                        <h3>Error al cargar las citas</h3>
                        <p>${error.message}</p>
                        <button onclick="cargarCitas()" class="btn-agendar-nueva">
                            <i class="bi bi-arrow-clockwise"></i>
                            Reintentar
                        </button>
                    </div>
                `;
            }
        }

        // ═══════════════════════════════════════════════════════════
        //  ACTUALIZAR ESTADÍSTICAS
        // ═══════════════════════════════════════════════════════════

        function actualizarEstadisticas(citas) {
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            
            const proximas = citas.filter(c => {
                const fecha = new Date(c.fecha_hora);
                return fecha >= hoy && c.estado !== 'Cancelada' && c.estado !== 'Realizada';
            }).length;

            const completadas = citas.filter(c => c.estado === 'Realizada').length;
            
            const citasHoy = citas.filter(c => {
                const fecha = new Date(c.fecha_hora);
                return fecha.toDateString() === hoy.toDateString() && c.estado !== 'Cancelada';
            }).length;

            const canceladas = citas.filter(c => c.estado === 'Cancelada').length;

            document.getElementById('stat-proximas').textContent = proximas;
            document.getElementById('stat-completadas').textContent = completadas;
            document.getElementById('stat-hoy').textContent = citasHoy;
            document.getElementById('stat-canceladas').textContent = canceladas;
        }

        // ═══════════════════════════════════════════════════════════
        //  CARGAR MASCOTAS EN FILTRO
        // ═══════════════════════════════════════════════════════════

        function cargarFiltroMascotas(citas) {
            const selectMascota = document.getElementById('filtro-mascota');
            const mascotasUnicas = [...new Set(citas.map(c => c.mascota_nombre))].filter(Boolean);
            
            let options = '<option value="">Todas las mascotas</option>';
            mascotasUnicas.forEach(mascota => {
                options += `<option value="${mascota}">${mascota}</option>`;
            });
            
            selectMascota.innerHTML = options;
        }

        // ═══════════════════════════════════════════════════════════
        //  APLICAR FILTROS
        // ═══════════════════════════════════════════════════════════

        function aplicarFiltros() {
            let citasFiltradas = [...citasData];
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            // Filtro por tab
            switch(filtroActual) {
                case 'proximas':
                    citasFiltradas = citasFiltradas.filter(c => {
                        const fecha = new Date(c.fecha_hora);
                        return fecha >= hoy && c.estado !== 'Cancelada' && c.estado !== 'Realizada';
                    });
                    break;
                case 'hoy':
                    citasFiltradas = citasFiltradas.filter(c => {
                        const fecha = new Date(c.fecha_hora);
                        return fecha.toDateString() === hoy.toDateString() && c.estado !== 'Cancelada';
                    });
                    break;
                case 'esta-semana':
                    const finSemana = new Date(hoy);
                    finSemana.setDate(finSemana.getDate() + 7);
                    citasFiltradas = citasFiltradas.filter(c => {
                        const fecha = new Date(c.fecha_hora);
                        return fecha >= hoy && fecha <= finSemana && c.estado !== 'Cancelada';
                    });
                    break;
                case 'pendientes':
                    citasFiltradas = citasFiltradas.filter(c => c.estado === 'Pendiente');
                    break;
                case 'canceladas':
                    citasFiltradas = citasFiltradas.filter(c => c.estado === 'Cancelada');
                    break;
            }

            // Filtro por mascota
            const mascotaFiltro = document.getElementById('filtro-mascota').value;
            if (mascotaFiltro) {
                citasFiltradas = citasFiltradas.filter(c => c.mascota_nombre === mascotaFiltro);
            }

            // Filtro por estado
            const estadoFiltro = document.getElementById('filtro-estado').value;
            if (estadoFiltro) {
                citasFiltradas = citasFiltradas.filter(c => c.estado === estadoFiltro);
            }

            // Filtro por fecha inicio
            const fechaInicio = document.getElementById('filtro-fecha-inicio').value;
            if (fechaInicio) {
                citasFiltradas = citasFiltradas.filter(c => {
                    const fechaCita = new Date(c.fecha_hora);
                    return fechaCita >= new Date(fechaInicio);
                });
            }

            // Filtro por fecha fin
            const fechaFin = document.getElementById('filtro-fecha-fin').value;
            if (fechaFin) {
                citasFiltradas = citasFiltradas.filter(c => {
                    const fechaCita = new Date(c.fecha_hora);
                    return fechaCita <= new Date(fechaFin + 'T23:59:59');
                });
            }

            renderizarCitas(citasFiltradas);
        }

        // ═══════════════════════════════════════════════════════════
        //  RENDERIZAR CITAS
        // ═══════════════════════════════════════════════════════════

        function renderizarCitas(citas) {
            const container = document.getElementById('citasContainer');

            if (citas.length === 0) {
                container.innerHTML = `
                    <div class="estado-vacio">
                        <i class="bi bi-calendar-x"></i>
                        <h3>No tienes citas</h3>
                        <p>No hay citas programadas en este momento</p>
                        <a href="${BASE_URL}/cliente/agendar-cita" class="btn-agendar-nueva">
                            <i class="bi bi-calendar-plus"></i>
                            Agendar Nueva Cita
                        </a>
                    </div>
                `;
                return;
            }

            // Agrupar citas por día
            const citasPorDia = agruparPorDia(citas);
            
            let html = '';
            
            for (const [fecha, citasDelDia] of Object.entries(citasPorDia)) {
                const fechaObj = new Date(fecha);
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                
                let etiquetaDia = '';
                if (fechaObj.toDateString() === hoy.toDateString()) {
                    etiquetaDia = 'Hoy';
                } else {
                    const manana = new Date(hoy);
                    manana.setDate(manana.getDate() + 1);
                    if (fechaObj.toDateString() === manana.toDateString()) {
                        etiquetaDia = 'Mañana';
                    } else {
                        const diff = Math.floor((fechaObj - hoy) / (1000 * 60 * 60 * 24));
                        if (diff > 0) {
                            etiquetaDia = `En ${diff} día${diff > 1 ? 's' : ''}`;
                        } else {
                            etiquetaDia = 'Pasada';
                        }
                    }
                }

                html += `
                    <div class="timeline-dia">
                        <div class="dia-header">
                            <div class="dia-fecha">
                                <h3>${formatearFecha(fechaObj)}</h3>
                                <p>${etiquetaDia}</p>
                            </div>
                            <span class="dia-badge">${citasDelDia.length} cita${citasDelDia.length > 1 ? 's' : ''}</span>
                        </div>
                `;

                citasDelDia.forEach(cita => {
                    html += generarTarjetaCita(cita);
                });

                html += `</div>`;
            }

            container.innerHTML = html;

            // Agregar event listeners a los botones
            agregarEventListeners();
        }

        // ═══════════════════════════════════════════════════════════
        //  AGRUPAR CITAS POR DÍA
        // ═══════════════════════════════════════════════════════════

        function agruparPorDia(citas) {
            const grupos = {};
            
            citas.forEach(cita => {
                const fecha = new Date(cita.fecha_hora);
                const fechaKey = fecha.toISOString().split('T')[0];
                
                if (!grupos[fechaKey]) {
                    grupos[fechaKey] = [];
                }
                grupos[fechaKey].push(cita);
            });

            // Ordenar grupos por fecha
            const gruposOrdenados = {};
            Object.keys(grupos).sort().forEach(key => {
                gruposOrdenados[key] = grupos[key];
            });

            return gruposOrdenados;
        }

        // ═══════════════════════════════════════════════════════════
        //  GENERAR TARJETA DE CITA
        // ═══════════════════════════════════════════════════════════

        function generarTarjetaCita(cita) {
            const fecha = new Date(cita.fecha_hora);
            const hora = fecha.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
            const periodo = fecha.getHours() >= 12 ? 'PM' : 'AM';
            
            const tipoBadge = obtenerTipoBadge(cita.tipo);
            const iconoMascota = obtenerIconoMascota(cita.mascota_especie);

            const esUrgente = cita.tipo && cita.tipo.toLowerCase().includes('emergencia');
            const claseUrgente = esUrgente ? 'urgente' : '';

            return `
                <div class="cita-card ${claseUrgente}" data-cita-id="${cita.id_agendamiento}">
                    <div class="cita-hora">
                        <div class="hora-numero">${hora.split(':')[0]}:${hora.split(':')[1]}</div>
                        <div class="hora-periodo">${periodo}</div>
                    </div>

                    <div class="cita-mascota-avatar">${iconoMascota}</div>

                    <div class="cita-info">
                        <div class="cita-titulo">
                            ${cita.tipo || 'Consulta'} - ${cita.mascota_nombre || 'Mascota'}
                            <span class="tipo-badge ${tipoBadge}">${cita.tipo || 'Consulta'}</span>
                        </div>

                        <div class="cita-detalles">
                            <div class="detalle">
                                <i class="bi bi-person"></i>
                                <span>${cita.veterinario_nombre || 'Por asignar'}</span>
                            </div>
                            <div class="detalle">
                                <i class="bi bi-heart"></i>
                                <span>${cita.mascota_especie || ''} - ${cita.mascota_raza || ''}</span>
                            </div>
                            <div class="detalle">
                                <i class="bi bi-clock"></i>
                                <span>${calcularDuracion(cita.fecha_hora, cita.fecha_hora_fin)}</span>
                            </div>
                            <div class="detalle">
                                <i class="bi bi-check-circle"></i>
                                <span>Estado: ${cita.estado}</span>
                            </div>
                        </div>

                        ${cita.observaciones ? `
                            <div class="cita-notas">
                                <strong>Observaciones:</strong> ${cita.observaciones}
                            </div>
                        ` : ''}

                        <div class="cita-acciones">
                            <button class="btn-accion btn-ver" onclick="verDetallesCita(${cita.id_agendamiento})">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </button>
                            ${cita.estado === 'Pendiente' ? `
                                <button class="btn-accion btn-reagendar" onclick="reagendarCita(${cita.id_agendamiento})">
                                    <i class="bi bi-calendar"></i> Reagendar
                                </button>
                                <button class="btn-accion btn-cancelar" onclick="cancelarCita(${cita.id_agendamiento}, '${cita.mascota_nombre}')">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        }

        // ═══════════════════════════════════════════════════════════
        //  FUNCIONES AUXILIARES
        // ═══════════════════════════════════════════════════════════

        function formatearFecha(fecha) {
            const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return fecha.toLocaleDateString('es-ES', opciones);
        }

        function obtenerTipoBadge(tipo) {
            if (!tipo) return 'consulta';
            const tipoLower = tipo.toLowerCase();
            if (tipoLower.includes('emergencia')) return 'emergencia';
            if (tipoLower.includes('vacuna')) return 'vacuna';
            if (tipoLower.includes('control')) return 'control';
            if (tipoLower.includes('baño') || tipoLower.includes('peluqueria')) return 'bano';
            return 'consulta';
        }

        function obtenerIconoMascota(especie) {
            if (!especie) return '🐾';
            const especieLower = especie.toLowerCase();
            if (especieLower.includes('perro')) return '🐕';
            if (especieLower.includes('gato')) return '🐈';
            if (especieLower.includes('ave') || especieLower.includes('pájaro')) return '🦜';
            if (especieLower.includes('conejo')) return '🐰';
            return '🐾';
        }

        function calcularDuracion(inicio, fin) {
            if (!fin) return '1 hora';
            const diff = new Date(fin) - new Date(inicio);
            const minutos = Math.floor(diff / 60000);
            if (minutos < 60) return `${minutos} minutos`;
            const horas = Math.floor(minutos / 60);
            const minutosRestantes = minutos % 60;
            return minutosRestantes > 0 ? `${horas}h ${minutosRestantes}m` : `${horas} hora${horas > 1 ? 's' : ''}`;
        }

        // ═══════════════════════════════════════════════════════════
        //  FUNCIONES DE ACCIONES (Ver, Reagendar, Cancelar)
        // ═══════════════════════════════════════════════════════════

        function verDetallesCita(idCita) {
            const cita = citasData.find(c => c.id_agendamiento == idCita);
            if (!cita) return;

            const fecha = new Date(cita.fecha_hora);
            const fechaFormateada = fecha.toLocaleDateString('es-ES', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            Swal.fire({
                title: 'Detalles de la Cita',
                html: `
                    <div style="text-align: left; padding: 20px;">
                        <p><strong>Mascota:</strong> ${cita.mascota_nombre}</p>
                        <p><strong>Especie:</strong> ${cita.mascota_especie} - ${cita.mascota_raza}</p>
                        <p><strong>Servicio:</strong> ${cita.tipo}</p>
                        <p><strong>Veterinario:</strong> ${cita.veterinario_nombre || 'Por asignar'}</p>
                        <p><strong>Fecha:</strong> ${fechaFormateada}</p>
                        <p><strong>Duración:</strong> ${calcularDuracion(cita.fecha_hora, cita.fecha_hora_fin)}</p>
                        <p><strong>Estado:</strong> ${cita.estado}</p>
                        ${cita.observaciones ? `<p><strong>Observaciones:</strong> ${cita.observaciones}</p>` : ''}
                    </div>
                `,
                confirmButtonText: 'Cerrar',
                width: '600px'
            });
        }

        function reagendarCita(idCita) {
            Swal.fire({
                icon: 'info',
                title: 'Reagendar Cita',
                text: 'Esta función estará disponible próximamente. Por favor, contacta a la veterinaria.',
                confirmButtonText: 'Entendido'
            });
        }

        async function cancelarCita(idCita, nombreMascota) {
            const result = await Swal.fire({
                title: '¿Cancelar cita?',
                html: `
                    <p>¿Estás seguro de que deseas cancelar la cita de <strong>${nombreMascota}</strong>?</p>
                    <textarea id="motivo-cancelacion" class="swal2-input" 
                              placeholder="Motivo de cancelación (opcional)" 
                              style="height: 100px; resize: vertical;"></textarea>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, mantener',
                confirmButtonColor: '#dc2626',
                preConfirm: () => {
                    return document.getElementById('motivo-cancelacion').value;
                }
            });

            if (result.isConfirmed) {
                const motivo = result.value || 'Sin motivo especificado';

                try {
                    Swal.fire({
                        title: 'Cancelando cita...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const response = await fetch(URLS.CANCELAR_CITA, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_agendamiento: idCita,
                            motivo_cancelacion: motivo
                        })
                    });

                    const resultData = await response.json();

                    if (resultData.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Cita Cancelada!',
                            text: 'La cita ha sido cancelada exitosamente',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            cargarCitas(); // Recargar citas
                        });
                    } else {
                        throw new Error(resultData.message || 'Error al cancelar cita');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'No se pudo cancelar la cita',
                        confirmButtonText: 'Entendido'
                    });
                }
            }
        }

        function agregarEventListeners() {
            // Los event listeners se agregan inline en el HTML generado
            // Ver onclick en los botones de las tarjetas
        }

        console.log('✅ Vista de Citas del Cliente cargada correctamente');
    </script>

</body>

</html>