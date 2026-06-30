<?php
/* ============================================================
   SIDEBAR VETERINARIO — VetWilling
   Datos esperados del scope padre: $datosUsuario, $final_path
   ============================================================ */

// Iniciales para el avatar placeholder
$iniciales = strtoupper(
    substr($datosUsuario['nombres']   ?? 'V', 0, 1) .
    substr($datosUsuario['apellidos'] ?? 'T', 0, 1)
);

// Datos de usuario para el footer
$nombreCompleto = trim(($datosUsuario['nombres'] ?? '') . ' ' . ($datosUsuario['apellidos'] ?? ''));
if ($nombreCompleto === '') {
    $nombreCompleto = 'Usuario';
}
$rolUsuario = $datosUsuario['rol'] ?? 'Veterinario';

// Sección actual (en minúsculas para comparar sin importar mayúsculas)
$seccionActual = strtolower($final_path ?? '');

// Rol como entero, para comparar sin problemas de tipo (BD suele devolver string)
$idRol = (int)($_SESSION['user']['id_rol'] ?? 0);

// Ruta de inicio según el rol
$rutaInicio = $idRol === 4
    ? BASE_URL . '/representante/dashboard'
    : BASE_URL . '/veterinaria/dashboard';

// Helper: devuelve 'active' si la sección coincide. Guardado contra redeclaración.
if (!function_exists('sv_active')) {
    function sv_active(string $seccion, string $actual): string
    {
        return $seccion === $actual ? 'active' : '';
    }
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/sidebarV.css">

<!-- ════════════════════════════════════════════
     SIDEBAR ESCRITORIO
════════════════════════════════════════════ -->
<aside class="sidebar-vet" id="sidebarVet" role="navigation" aria-label="Menú principal veterinario">

    <!-- Header / Logo -->
    <div class="sv-header">
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-NEGATIVO.png"
             alt="VetWilling"
             class="sv-logo-full">
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-VERTICAL-NEGATIVA.png"
             alt="VW"
             class="sv-logo-icon">
        <span class="sv-role-badge">Veterinario</span>
    </div>

    <!-- Navegación -->
    <nav class="sv-nav">

        <!-- ── Principal ── -->
        <div class="sv-section">
            <div class="sv-section-title">Principal</div>

            <a href="<?= $rutaInicio ?>"
               class="sv-item <?= sv_active('dashboard', $seccionActual) ?>"
               data-section="dashboard"
               data-tooltip="Inicio">
                <i class="bi bi-house-door"></i>
                <span class="sv-text">Inicio</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/reportes"
               class="sv-item <?= sv_active('reportes', $seccionActual) ?>"
               data-section="reportes"
               data-tooltip="Reportes">
                <i class="bi bi-bar-chart"></i>
                <span class="sv-text">Reportes</span>
            </a>
        </div>

        <div class="sv-divider"></div>

        <!-- ── Agenda ── -->
        <div class="sv-section">
            <div class="sv-section-title">Agenda</div>

            <a href="<?= BASE_URL ?>/veterinaria/calendario"
               class="sv-item <?= sv_active('calendario', $seccionActual) ?>"
               data-section="calendario"
               data-tooltip="Calendario">
                <i class="bi bi-calendar-week"></i>
                <span class="sv-text">Calendario</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/mi-agenda"
               class="sv-item <?= sv_active('mi-agenda', $seccionActual) ?>"
               data-section="mi-agenda"
               data-tooltip="Mi Disponibilidad">
                <i class="bi bi-clock"></i>
                <span class="sv-text">Mi Disponibilidad</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinario/consultar-citas"
               class="sv-item <?= sv_active('consultar-citas', $seccionActual) ?>"
               data-section="consultar-citas"
               data-tooltip="Citas">
                <i class="bi bi-search"></i>
                <span class="sv-text">Citas</span>
                <span class="sv-badge" style="display:none;">0</span>
            </a>
        </div>

        <div class="sv-divider"></div>

        <!-- ── Pacientes ── -->
        <div class="sv-section">
            <div class="sv-section-title">Pacientes</div>

            <a href="<?= BASE_URL ?>/veterinaria/gestion-pacientes"
               class="sv-item <?= sv_active('gestion-pacientes', $seccionActual) ?>"
               data-section="gestion-pacientes"
               data-tooltip="Gestión Pacientes">
                <i class="bi bi-journal-medical"></i>
                <span class="sv-text">Gestión Pacientes</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinario/registrar-pacientes"
               class="sv-item <?= sv_active('registrar-pacientes', $seccionActual) ?>"
               data-section="registrar-pacientes"
               data-tooltip="Registro Pacientes">
                <i class="bi bi-pencil-square"></i>
                <span class="sv-text">Registro Pacientes</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/seguimientos"
               class="sv-item <?= sv_active('seguimientos', $seccionActual) ?>"
               data-section="seguimientos"
               data-tooltip="Seguimientos">
                <i class="bi bi-card-checklist"></i>
                <span class="sv-text">Seguimientos</span>
            </a>
        </div>

        <div class="sv-divider"></div>

        <!-- ── Clínico ── -->
        <div class="sv-section">
            <div class="sv-section-title">Clínico</div>

            <a href="<?= BASE_URL ?>/veterinaria/laboratorio"
               class="sv-item <?= sv_active('laboratorio', $seccionActual) ?>"
               data-section="laboratorio"
               data-tooltip="Laboratorio">
                <i class="bi bi-beaker"></i>
                <span class="sv-text">Laboratorio</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/recetas"
               class="sv-item <?= sv_active('recetas', $seccionActual) ?>"
               data-section="recetas"
               data-tooltip="Recetas">
                <i class="bi bi-journal-text"></i>
                <span class="sv-text">Recetas</span>
            </a>
        </div>

        <div class="sv-divider"></div>

        <!-- ── Más ── -->
        <div class="sv-section">
            <div class="sv-section-title">Más</div>

            <a href="<?= BASE_URL ?>/directorio"
               class="sv-item <?= sv_active('directorio', $seccionActual) ?>"
               data-section="directorio"
               data-tooltip="Directorio">
                <i class="bi bi-people-fill"></i>
                <span class="sv-text">Directorio</span>
            </a>


    </nav>

    <!-- Botón toggle (colapsar/expandir) -->
    <button class="sv-toggle" id="svToggle" aria-label="Colapsar menú">
        <i class="bi bi-chevron-left"></i>
    </button>

</aside>


<!-- ════════════════════════════════════════════
     OVERLAY MÓVIL
════════════════════════════════════════════ -->
<div class="sv-mobile-overlay" id="svOverlay" aria-hidden="true"></div>


<!-- ════════════════════════════════════════════
     BOTTOM SHEET MÓVIL
════════════════════════════════════════════ -->
<div class="sv-bottom-sheet" id="svBottomSheet" role="dialog" aria-modal="true" aria-label="Menú de navegación">

    <!-- Handle de arrastre -->
    <div class="sv-sheet-handle" id="svSheetHandle" aria-hidden="true"></div>

    <!-- Header -->
    <div class="sv-sheet-header">
        <div class="sv-sheet-title">
            <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-VERTICAL-NEGATIVA.png"
                 alt="VW"
                 class="sv-sheet-logo">
            <span class="sv-sheet-name">VetWilling</span>
        </div>
        <button class="sv-sheet-close" id="svSheetClose" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Usuario -->
    <div class="sv-sheet-user">
        <div class="sv-sheet-avatar"><?= htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8') ?></div>
        <div>
            <div class="sv-sheet-user-name"><?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="sv-sheet-user-role"><?= htmlspecialchars($rolUsuario, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>

    <!-- Cuerpo scrollable -->
    <div class="sv-sheet-body">

        <!-- ── Principal ── -->
        <div class="sv-sheet-section-title">Principal</div>
        <div class="sv-sheet-grid">
            <a href="<?= $rutaInicio ?>" class="sv-sheet-grid-item <?= sv_active('dashboard', $seccionActual) ?>">
                <i class="bi bi-house-door" aria-hidden="true"></i>Inicio
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/reportes" class="sv-sheet-grid-item <?= sv_active('reportes', $seccionActual) ?>">
                <i class="bi bi-bar-chart" aria-hidden="true"></i>Reportes
            </a>
        </div>

        <!-- ── Agenda ── -->
        <div class="sv-sheet-section-title">Agenda</div>
        <div class="sv-sheet-grid">
            <a href="<?= BASE_URL ?>/veterinaria/calendario" class="sv-sheet-grid-item <?= sv_active('calendario', $seccionActual) ?>">
                <i class="bi bi-calendar-week" aria-hidden="true"></i>Calendario
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/mi-agenda" class="sv-sheet-grid-item <?= sv_active('mi-agenda', $seccionActual) ?>">
                <i class="bi bi-clock" aria-hidden="true"></i>Disponibilidad
            </a>
            <a href="<?= BASE_URL ?>/veterinario/consultar-citas" class="sv-sheet-grid-item <?= sv_active('consultar-citas', $seccionActual) ?>">
                <i class="bi bi-search" aria-hidden="true"></i>Citas
            </a>
        </div>

        <!-- ── Pacientes ── -->
        <div class="sv-sheet-section-title">Pacientes</div>
        <div class="sv-sheet-grid">
            <a href="<?= BASE_URL ?>/veterinaria/gestion-pacientes" class="sv-sheet-grid-item <?= sv_active('gestion-pacientes', $seccionActual) ?>">
                <i class="bi bi-journal-medical" aria-hidden="true"></i>Pacientes
            </a>
            <a href="<?= BASE_URL ?>/veterinario/registrar-pacientes" class="sv-sheet-grid-item <?= sv_active('registrar-pacientes', $seccionActual) ?>">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>Registro
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/seguimientos" class="sv-sheet-grid-item <?= sv_active('seguimientos', $seccionActual) ?>">
                <i class="bi bi-card-checklist" aria-hidden="true"></i>Seguimientos
            </a>
        </div>

        <!-- ── Clínico ── -->
        <div class="sv-sheet-section-title">Clínico</div>
        <div class="sv-sheet-grid">
            <a href="<?= BASE_URL ?>/veterinaria/laboratorio" class="sv-sheet-grid-item <?= sv_active('laboratorio', $seccionActual) ?>">
                <i class="bi bi-beaker" aria-hidden="true"></i>Laboratorio
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/recetas" class="sv-sheet-grid-item <?= sv_active('recetas', $seccionActual) ?>">
                <i class="bi bi-journal-text" aria-hidden="true"></i>Recetas
            </a>
        </div>

        <!-- ── Más ── -->
        <div class="sv-sheet-section-title">Más</div>

        <a href="<?= BASE_URL ?>/directorio"
           class="sv-sheet-list-item <?= sv_active('directorio', $seccionActual) ?>">
            <i class="bi bi-people-fill" aria-hidden="true"></i>
            Directorio
        </a>

    </div>
</div>


<!-- ════════════════════════════════════════════
     FAB (botón flotante móvil)
════════════════════════════════════════════ -->
<button class="sv-fab" id="svFab" aria-label="Abrir menú de navegación">
    <i class="bi bi-grid" id="svFabIcon"></i>
</button>


<script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/sidebarV.js" defer></script>