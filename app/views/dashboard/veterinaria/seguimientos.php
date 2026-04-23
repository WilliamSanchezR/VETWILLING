<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';

// Seguridad: nunca exponer datos de sesión en producción
$usuarioNombre = htmlspecialchars($_SESSION['user']['nombre'] ?? 'Veterinario');
$usuarioId     = (int)($_SESSION['user']['id_usuario'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimientos | Dashboard Veterinario – VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleGestionPacientesHistorial.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardSeguimientos.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar_veterinario.php' ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php' ?>

        <div class="area-contenido">
            <section class="seg-shell" aria-label="Panel de seguimientos">

                <!-- ── ENCABEZADO ── -->
                <div class="seg-header">
                    <div class="seg-header-texto">
                        <div class="seg-eyebrow">
                            <i class="bi bi-clipboard2-pulse-fill"></i>
                            Control Clínico
                        </div>
                        <h1>Seguimiento de Pacientes</h1>
                        <p>Tratamientos activos, medicaciones y evolución clínica</p>
                    </div>
                    <div class="seg-header-acciones">
                        <button class="btn-seg-secundario" type="button" id="btnExportar">
                            <i class="bi bi-download"></i>
                            <span>Exportar</span>
                        </button>
                        <button class="btn-seg-primario" type="button" onclick="nuevoSeguimiento()">
                            <i class="bi bi-plus-lg"></i>
                            <span>Nuevo Seguimiento</span>
                        </button>
                    </div>
                </div>

                <!-- ── ESTADÍSTICAS ── -->
                <div class="seg-stats" role="region" aria-label="Estadísticas de seguimientos">
                    <div class="stat-tile" data-color="green">
                        <div class="stat-tile-icon">
                            <i class="bi bi-activity"></i>
                        </div>
                        <div class="stat-tile-data">
                            <strong id="statActivos">—</strong>
                            <span>Activos</span>
                        </div>
                    </div>
                    <div class="stat-tile" data-color="red">
                        <div class="stat-tile-icon">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                        </div>
                        <div class="stat-tile-data">
                            <strong id="statCriticos">—</strong>
                            <span>Críticos</span>
                        </div>
                    </div>
                    <div class="stat-tile" data-color="amber">
                        <div class="stat-tile-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="stat-tile-data">
                            <strong id="statPendientes">—</strong>
                            <span>Requieren Atención</span>
                        </div>
                    </div>
                    <div class="stat-tile" data-color="teal">
                        <div class="stat-tile-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="stat-tile-data">
                            <strong id="statCompletados">—</strong>
                            <span>Revisiones Hoy</span>
                        </div>
                    </div>
                </div>

                <!-- ── BARRA DE HERRAMIENTAS ── -->
                <div class="seg-toolbar" role="search">

                    <!-- Búsqueda -->
                    <div class="seg-search-wrap">
                        <i class="bi bi-search seg-search-ico"></i>
                        <input
                            type="search"
                            id="searchInput"
                            class="seg-search"
                            placeholder="Buscar paciente, diagnóstico, propietario…"
                            autocomplete="off"
                            aria-label="Buscar seguimientos">
                        <button type="button"
                                id="clearSearch"
                                class="seg-search-clear"
                                aria-label="Limpiar búsqueda"
                                style="display:none">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- Filtros de estado -->
                    <div class="seg-filtros" role="group" aria-label="Filtrar por estado">
                        <button class="seg-filtro-btn active" data-filtro="todos" aria-pressed="true">
                            <i class="bi bi-grid-3x3-gap"></i> Todos
                        </button>
                        <button class="seg-filtro-btn" data-filtro="activos" aria-pressed="false">
                            <i class="bi bi-activity"></i> Activos
                        </button>
                        <button class="seg-filtro-btn" data-filtro="criticos" aria-pressed="false">
                            <i class="bi bi-exclamation-triangle"></i> Críticos
                        </button>
                        <button class="seg-filtro-btn" data-filtro="completados" aria-pressed="false">
                            <i class="bi bi-check-circle"></i> Completados
                        </button>
                    </div>

                    <!-- Ordenar + vista -->
                    <div class="seg-controls">
                        <select class="seg-select" id="sortSelect" aria-label="Ordenar por">
                            <option value="recientes">Más recientes</option>
                            <option value="prioridad">Por prioridad</option>
                            <option value="paciente">Por paciente A–Z</option>
                            <option value="fecha">Por fecha fin</option>
                        </select>

                        <div class="seg-view-toggle" role="group" aria-label="Cambiar vista">
                            <button type="button" id="viewList" class="seg-view-btn active"
                                    aria-pressed="true" title="Vista lista">
                                <i class="bi bi-list-ul"></i>
                            </button>
                            <button type="button" id="viewGrid" class="seg-view-btn"
                                    aria-pressed="false" title="Vista cuadrícula">
                                <i class="bi bi-grid-3x2"></i>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Meta: contador + sync -->
                <div class="seg-meta">
                    <span id="resultCount" class="seg-meta-count"></span>
                    <span class="seg-meta-sync" id="lastUpdate" aria-live="polite"></span>
                </div>

                <!-- ── CONTENIDO PRINCIPAL ── -->
                <div class="seg-content-area">

                    <!-- Skeleton loader -->
                    <div id="loadingState" aria-hidden="true" aria-label="Cargando seguimientos" style="display:none">
                        <?php for ($i = 0; $i < 3; $i++): ?>
                        <div class="skel-card">
                            <div class="skel-row">
                                <div class="skel-circle"></div>
                                <div class="skel-lines">
                                    <div class="skel-line skel-lg"></div>
                                    <div class="skel-line skel-md"></div>
                                </div>
                                <div class="skel-badges">
                                    <div class="skel-badge"></div>
                                    <div class="skel-badge"></div>
                                </div>
                            </div>
                            <div class="skel-body">
                                <div class="skel-line skel-full"></div>
                                <div class="skel-line skel-wide"></div>
                                <div class="skel-line skel-med"></div>
                            </div>
                            <div class="skel-progress"></div>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <!-- Estado vacío -->
                    <div id="emptyState" class="seg-empty" role="status" style="display:none">
                        <div class="seg-empty-icon">
                            <i class="bi bi-clipboard2-x"></i>
                        </div>
                        <h3>Sin seguimientos</h3>
                        <p>No hay seguimientos que coincidan con los filtros aplicados.</p>
                        <button class="btn-seg-primario" type="button" onclick="nuevoSeguimiento()">
                            <i class="bi bi-plus-lg"></i> Crear Seguimiento
                        </button>
                    </div>

                    <!-- Lista dinámica -->
                    <div id="listaSeguimientos"
                         class="seg-lista"
                         role="list"
                         aria-label="Lista de seguimientos"
                         aria-live="polite">
                    </div>

                    <!-- Paginación -->
                    <div id="paginacion" class="seg-paginacion" style="display:none" role="navigation" aria-label="Paginación">
                        <button type="button" id="btnPagAnterior" class="seg-pag-btn" disabled>
                            <i class="bi bi-chevron-left"></i> Anterior
                        </button>
                        <span id="paginaInfo" class="seg-pag-info"></span>
                        <button type="button" id="btnPagSiguiente" class="seg-pag-btn">
                            Siguiente <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>

                </div>

            </section>
        </div>
    </div>

    <!-- Toast container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"
         id="toastContainer"
         style="z-index:11000;"
         aria-live="assertive"
         aria-atomic="true">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.BASE_URL   = "<?= BASE_URL ?>";
        window.USUARIO_ID = <?= $usuarioId ?>;
    </script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoardSeguimientos.js"></script>
</body>
</html>