<?php
// Iniciales del usuario para avatar placeholder
$iniciales = strtoupper(
    substr($datosUsuario['nombres'] ?? 'V', 0, 1) .
    substr($datosUsuario['apellidos'] ?? 'T', 0, 1)
);
$seccionActual = $final_path ?? '';
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

        <!-- Sección General -->
        <div class="sv-section">
            <div class="sv-section-title">General</div>

            <?php if ($_SESSION['user']['id_rol'] === 4): ?>
                <a href="<?= BASE_URL ?>/representante/dashboard"
                   class="sv-item <?= $seccionActual === 'dashBoard' ? 'active' : '' ?>"
                   data-section="dashBoard"
                   data-tooltip="Inicio">
                    <i class="bi bi-house-door"></i>
                    <span class="sv-text">Inicio</span>
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/veterinaria/dashboard"
                   class="sv-item <?= $seccionActual === 'dashBoard' ? 'active' : '' ?>"
                   data-section="dashBoard"
                   data-tooltip="Inicio">
                    <i class="bi bi-house-door"></i>
                    <span class="sv-text">Inicio</span>
                </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/veterinaria/seguimientos"
               class="sv-item <?= $seccionActual === 'seguimientos' ? 'active' : '' ?>"
               data-section="seguimientos"
               data-tooltip="Seguimientos">
                <i class="bi bi-card-checklist"></i>
                <span class="sv-text">Seguimientos</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/calendario"
               class="sv-item <?= $seccionActual === 'calendario' ? 'active' : '' ?>"
               data-section="calendario"
               data-tooltip="Calendario">
                <i class="bi bi-calendar-week"></i>
                <span class="sv-text">Calendario</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/mi-agenda"
               class="sv-item <?= $seccionActual === 'mi-agenda' ? 'active' : '' ?>"
               data-section="mi-agenda"
               data-tooltip="Mi Disponibilidad">
                <i class="bi bi-clock"></i>
                <span class="sv-text">Mi Disponibilidad</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/gestion-pacientes"
               class="sv-item <?= $seccionActual === 'gestion-pacientes' ? 'active' : '' ?>"
               data-section="gestion-pacientes"
               data-tooltip="Gestión Pacientes">
                <i class="bi bi-journal-medical"></i>
                <span class="sv-text">Gestión Pacientes</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/laboratorio"
               class="sv-item <?= $seccionActual === 'laboratorio' ? 'active' : '' ?>"
               data-section="laboratorio"
               data-tooltip="Laboratorio">
                <i class="bi bi-beaker"></i>
                <span class="sv-text">Laboratorio</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/recetas"
               class="sv-item <?= $seccionActual === 'recetas' ? 'active' : '' ?>"
               data-section="recetas"
               data-tooltip="Recetas">
                <i class="bi bi-journal-text"></i>
                <span class="sv-text">Recetas</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinaria/reportes"
               class="sv-item <?= $seccionActual === 'reportes' ? 'active' : '' ?>"
               data-section="reportes"
               data-tooltip="Reportes">
                <i class="bi bi-bar-chart"></i>
                <span class="sv-text">Reportes</span>
            </a>

            <a href="<?= BASE_URL ?>/directorio"
               class="sv-item <?= $seccionActual === 'directorio' ? 'active' : '' ?>"
               data-section="directorio"
               data-tooltip="Directorio">
                <i class="bi bi-people-fill"></i>
                <span class="sv-text">Directorio</span>
            </a>
        </div>

        <div class="sv-divider"></div>

        <!-- Sección Gestión -->
        <div class="sv-section">
            <div class="sv-section-title">Gestión</div>

            <a href="<?= BASE_URL ?>/veterinario/registrar-pacientes"
               class="sv-item <?= $seccionActual === 'registrar-pacientes' ? 'active' : '' ?>"
               data-section="registrar-pacientes"
               data-tooltip="Registro Pacientes">
                <i class="bi bi-pencil-square"></i>
                <span class="sv-text">Registro Pacientes</span>
            </a>

            <a href="<?= BASE_URL ?>/veterinario/consultar-citas"
               class="sv-item <?= $seccionActual === 'consultar-citas' ? 'active' : '' ?>"
               data-section="consultar-citas"
               data-tooltip="Citas">
                <i class="bi bi-search"></i>
                <span class="sv-text">Citas</span>
                <span class="sv-badge" style="display:none;">0</span>
            </a>
        </div>

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

    <!-- Cuerpo scrollable -->
    <div class="sv-sheet-body">

        <!-- Sección General en grid 2 columnas -->
        <div class="sv-sheet-section-title">General</div>
        <div class="sv-sheet-grid">

            <?php if ($_SESSION['user']['id_rol'] === 4): ?>
                <a href="<?= BASE_URL ?>/representante/dashboard" class="sv-sheet-grid-item <?= $seccionActual === 'dashBoard' ? 'active' : '' ?>">
                    <i class="bi bi-house-door" aria-hidden="true"></i>Inicio
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/veterinaria/dashboard" class="sv-sheet-grid-item <?= $seccionActual === 'dashBoard' ? 'active' : '' ?>">
                    <i class="bi bi-house-door" aria-hidden="true"></i>Inicio
                </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/veterinaria/seguimientos" class="sv-sheet-grid-item <?= $seccionActual === 'seguimientos' ? 'active' : '' ?>">
                <i class="bi bi-card-checklist" aria-hidden="true"></i>Seguimientos
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/calendario" class="sv-sheet-grid-item <?= $seccionActual === 'calendario' ? 'active' : '' ?>">
                <i class="bi bi-calendar-week" aria-hidden="true"></i>Calendario
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/mi-agenda" class="sv-sheet-grid-item <?= $seccionActual === 'mi-agenda' ? 'active' : '' ?>">
                <i class="bi bi-clock" aria-hidden="true"></i>Disponibilidad
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/gestion-pacientes" class="sv-sheet-grid-item <?= $seccionActual === 'gestion-pacientes' ? 'active' : '' ?>">
                <i class="bi bi-journal-medical" aria-hidden="true"></i>Pacientes
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/laboratorio" class="sv-sheet-grid-item <?= $seccionActual === 'laboratorio' ? 'active' : '' ?>">
                <i class="bi bi-beaker" aria-hidden="true"></i>Laboratorio
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/recetas" class="sv-sheet-grid-item <?= $seccionActual === 'recetas' ? 'active' : '' ?>">
                <i class="bi bi-journal-text" aria-hidden="true"></i>Recetas
            </a>
            <a href="<?= BASE_URL ?>/veterinaria/reportes" class="sv-sheet-grid-item <?= $seccionActual === 'reportes' ? 'active' : '' ?>">
                <i class="bi bi-bar-chart" aria-hidden="true"></i>Reportes
            </a>
            <a href="<?= BASE_URL ?>/directorio" class="sv-sheet-grid-item <?= $seccionActual === 'directorio' ? 'active' : '' ?>">
                <i class="bi bi-people-fill" aria-hidden="true"></i>Directorio
            </a>

        </div>

        <!-- Sección Gestión en lista -->
        <div class="sv-sheet-section-title">Gestión</div>

        <a href="<?= BASE_URL ?>/veterinario/registrar-pacientes"
           class="sv-sheet-list-item <?= $seccionActual === 'registrar-pacientes' ? 'active' : '' ?>">
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
            Registro de Pacientes
        </a>
        <a href="<?= BASE_URL ?>/veterinario/consultar-citas"
           class="sv-sheet-list-item <?= $seccionActual === 'consultar-citas' ? 'active' : '' ?>">
            <i class="bi bi-search" aria-hidden="true"></i>
            Consultar Citas
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