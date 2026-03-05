<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimientos | Dashboard Veterinario</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleGestionPacientesHistorial.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardSeguimientos.css">

    <!-- Animate.css para animaciones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

</head>

<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar_veterinario.php' ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php' ?>

        <div class="area-contenido">
            <!-- ENCABEZADO DE SECCIÓN -->
            <section class="historial-shell">
                <div class="encabezado-seccion mb-0">
                    <div>
                        <h4><i class="bi bi-clipboard2-pulse me-2"></i>Seguimiento de Pacientes</h4>
                        <p class="subtitulo-historial mb-0">Control de tratamientos, medicaciones y evolución clínica</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn-secundario" type="button">
                            <i class="bi bi-download me-1"></i> Exportar Reporte
                        </button>
                        <button class="boton-agregar" type="button" onclick="nuevoSeguimiento()">
                            <i class="bi bi-plus-circle"></i> Nuevo Seguimiento
                        </button>
                    </div>
                </div>

                <!-- ESTADÍSTICAS -->
                <div class="row g-3 mt-1">
                    <div class="col-md-3 col-sm-6">
                        <div class="mini-stat">
                            <div class="mini-stat-icon" style="background: rgba(10, 147, 44, 0.1); color: #0a932c;">
                                <i class="bi bi-activity"></i>
                            </div>
                            <div>
                                <strong id="statActivos">0</strong>
                                <small>Seguimientos Activos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="mini-stat">
                            <div class="mini-stat-icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                <i class="bi bi-exclamation-octagon"></i>
                            </div>
                            <div>
                                <strong id="statCriticos">0</strong>
                                <small>Casos Críticos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="mini-stat">
                            <div class="mini-stat-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <strong id="statPendientes">0</strong>
                                <small>Requieren Atención</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="mini-stat">
                            <div class="mini-stat-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div>
                                <strong id="statCompletados">0</strong>
                                <small>Revisiones Hoy</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BARRA DE BÚSQUEDA Y FILTROS -->
                <div class="historial-card mt-3">
                    <div class="bloque-titulo mb-3">
                        <h5><i class="bi bi-funnel me-2"></i>Búsqueda y Filtros</h5>
                    </div>
                    
                    <!-- Búsqueda en tiempo real -->
                    <div class="search-wrapper mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control border-start-0" 
                                id="searchInput" 
                                placeholder="Buscar por paciente, diagnóstico, doctor..."
                                aria-label="Buscar seguimientos"
                                aria-describedby="searchHelp"
                            >
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" style="display: none;" aria-label="Limpiar búsqueda">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <small id="searchHelp" class="form-text text-muted" role="status" aria-live="polite"></small>
                    </div>

                    <!-- Filtros -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div class="d-flex gap-2 flex-wrap" role="group" aria-label="Filtros de seguimiento">
                            <button class="boton-filtro-seg active" data-filtro="todos" aria-pressed="true">
                                <i class="bi bi-grid-3x3-gap"></i> Todos
                            </button>
                            <button class="boton-filtro-seg" data-filtro="activos" aria-pressed="false">
                                <i class="bi bi-activity"></i> Activos
                            </button>
                            <button class="boton-filtro-seg" data-filtro="criticos" aria-pressed="false">
                                <i class="bi bi-exclamation-triangle"></i> Críticos
                            </button>
                            <button class="boton-filtro-seg" data-filtro="completados" aria-pressed="false">
                                <i class="bi bi-check-circle"></i> Completados
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <select class="select-filtro" id="sortSelect" aria-label="Ordenar seguimientos">
                                <option value="recientes">Ordenar: Más recientes</option>
                                <option value="prioridad">Ordenar: Por prioridad</option>
                                <option value="paciente">Ordenar: Por paciente</option>
                                <option value="fecha">Ordenar: Por fecha fin</option>
                            </select>
                            <!-- Toggle de vista -->
                            <div class="btn-group" role="group" aria-label="Cambiar vista">
                                <button type="button" class="btn btn-outline-secondary active" id="viewList" aria-pressed="true" title="Vista lista">
                                    <i class="bi bi-list-ul"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="viewGrid" aria-pressed="false" title="Vista cuadrícula">
                                    <i class="bi bi-grid-3x2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LISTADO DE SEGUIMIENTOS -->
                <div class="historial-card mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Seguimientos <span id="resultCount">Activos</span></h5>
                        <span class="badge-sync"><i class="bi bi-arrow-repeat"></i> <span id="lastUpdate">Actualizado</span></span>
                    </div>

                    <!-- Estado de carga (Skeleton Loader) -->
                    <div id="loadingState" class="lista-seguimientos" style="display: none;" aria-hidden="true">
                        <div class="skeleton-card">
                            <div class="skeleton-header">
                                <div class="skeleton-avatar"></div>
                                <div class="skeleton-text-group">
                                    <div class="skeleton-text skeleton-title"></div>
                                    <div class="skeleton-text skeleton-subtitle"></div>
                                </div>
                            </div>
                            <div class="skeleton-body">
                                <div class="skeleton-text"></div>
                                <div class="skeleton-text"></div>
                                <div class="skeleton-text"></div>
                            </div>
                            <div class="skeleton-progress"></div>
                        </div>
                        <div class="skeleton-card">
                            <div class="skeleton-header">
                                <div class="skeleton-avatar"></div>
                                <div class="skeleton-text-group">
                                    <div class="skeleton-text skeleton-title"></div>
                                    <div class="skeleton-text skeleton-subtitle"></div>
                                </div>
                            </div>
                            <div class="skeleton-body">
                                <div class="skeleton-text"></div>
                                <div class="skeleton-text"></div>
                                <div class="skeleton-text"></div>
                            </div>
                            <div class="skeleton-progress"></div>
                        </div>
                    </div>

                    <!-- Estado vacío -->
                    <div id="emptyState" class="empty-state" style="display: none;" role="status">
                        <div class="empty-state-icon">
                            <i class="bi bi-inbox"></i>
                        </div>
                        <h4>No se encontraron seguimientos</h4>
                        <p>No hay seguimientos que coincidan con los filtros actuales.</p>
                        <button class="btn boton-agregar" onclick="nuevoSeguimiento()">
                            <i class="bi bi-plus-circle"></i> Crear Nuevo Seguimiento
                        </button>
                    </div>

                    <!-- Lista de seguimientos (Se carga dinámicamente desde el API) -->
                    <div class="lista-seguimientos" id="listaSeguimientos" role="list">
                        <!-- Los seguimientos se cargarán aquí dinámicamente -->
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Toast Container para notificaciones -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer" style="z-index: 11000;">
        <!-- Los toasts se generarán dinámicamente aquí -->
    </div>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Script de configuración -->
    <script>
        // Verificación de configuración
        console.log('=== DIAGNÓSTICO DE SEGUIMIENTOS ===');
        console.log('BASE_URL PHP:', '<?= BASE_URL ?>');
        console.log('Usuario sesión:', <?= json_encode($_SESSION['user'] ?? null) ?>);
        console.log('URL actual:', window.location.href);
        console.log('API URL esperada:', window.location.origin + '/vetwilling/veterinaria/api/seguimientos');
        console.log('===================================');
    </script>

    <!-- Scripts propios -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoardSeguimientos.js"></script>

</body>

</html>
