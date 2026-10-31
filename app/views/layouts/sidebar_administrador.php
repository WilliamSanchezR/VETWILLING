<?php
/* ============================================================
   SIDEBAR ADMINISTRADOR — VetWilling
   Estilos y JS propios (prefijo "sa-"): no comparte clases con
   menuStyle.css ni con menu.js, para no cruzar estilos con otros roles.
   ============================================================ */

// Sección actual: último segmento de la URL, sin parámetros
$rutaActual = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');
$saSeccion = strtolower(basename($rutaActual));

// Datos del usuario para el pie de la barra
$saNombres = trim($_SESSION['user']['nombres'] ?? '');
$saApellidos = trim($_SESSION['user']['apellidos'] ?? '');
$saNombre = trim($saNombres . ' ' . $saApellidos) ?: 'Administrador';
$saIniciales = strtoupper(mb_substr($saNombres ?: 'A', 0, 1) . mb_substr($saApellidos ?: 'D', 0, 1));

// Helper: 'is-active' si la sección actual está en la lista. Guardado contra redeclaración.
if (!function_exists('sa_active')) {
    function sa_active(array $secciones, string $actual): string
    {
        return in_array($actual, $secciones, true) ? 'is-active' : '';
    }
}

$saVeterinarias = ['registro-veterinaria', 'listar-veterinarias', 'editar-veterinaria'];
$saUsuarios = ['registro-usuario', 'listar-usuarios', 'editar-usuario'];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/sidebarA.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/layoutAdmin.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/adminUI.css">

<!-- Aplica el estado colapsado antes de pintar, para que la página no "salte" al cargar -->
<script>
    try {
        if (localStorage.getItem('saCollapsed') === '1') document.documentElement.classList.add('sa-collapsed');
    } catch (e) {}
    document.documentElement.classList.add('sa-sin-animacion');
</script>

<div class="sa-overlay" id="saOverlay" hidden></div>

<aside class="sa-sidebar" id="sidebarAdmin" aria-label="Menú del administrador">

    <!-- Encabezado -->
    <div class="sa-header">
        <a href="<?= BASE_URL ?>/admin/dashBoard" class="sa-brand" aria-label="VetWilling - Inicio">
            <img src="<?= BASE_URL ?>/public/assets/webSite/img/LOGO-PRINCIPAL.webp" alt="" class="sa-logo-full"
                width="150" height="63">
            <img src="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" alt="" class="sa-logo-icon" width="36"
                height="36">
        </a>
        <button type="button" class="sa-collapse" id="saCollapse" aria-label="Contraer menú" aria-pressed="false" data-label="">
            <i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i>
        </button>
        <button type="button" class="sa-close" id="saClose" aria-label="Cerrar menú">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </div>

    <!-- Navegación -->
    <nav class="sa-nav">

        <ul class="sa-list">
            <li>
                <a href="<?= BASE_URL ?>/admin/dashBoard" class="sa-item <?= sa_active(['dashboard'], $saSeccion) ?>"
                    data-label="Inicio" <?= $saSeccion === 'dashboard' ? 'aria-current="page"' : '' ?>>
                    <span class="sa-icon"><i class="bi bi-grid-1x2" aria-hidden="true"></i></span>
                    <span class="sa-text">Inicio</span>
                </a>
            </li>
        </ul>

        <p class="sa-label">Gestión</p>
        <ul class="sa-list">
            <!-- Veterinarias -->
            <li class="sa-group <?= sa_active($saVeterinarias, $saSeccion) ? 'is-open is-active' : '' ?>">
                <button type="button" class="sa-item sa-group-toggle" data-label="Veterinarias"
                    aria-expanded="<?= sa_active($saVeterinarias, $saSeccion) ? 'true' : 'false' ?>"
                    aria-controls="sa-sub-veterinarias">
                    <span class="sa-icon"><i class="bi bi-hospital" aria-hidden="true"></i></span>
                    <span class="sa-text">Veterinarias</span>
                    <i class="bi bi-chevron-down sa-chevron" aria-hidden="true"></i>
                </button>
                <div class="sa-sub" id="sa-sub-veterinarias" data-title="Veterinarias">
                    <ul>
                        <li><a href="<?= BASE_URL ?>/admin/listar-veterinarias"
                                class="<?= sa_active(['listar-veterinarias', 'editar-veterinaria'], $saSeccion) ?>"
                                <?= in_array($saSeccion, ['listar-veterinarias', 'editar-veterinaria'], true) ? 'aria-current="page"' : '' ?>>Listado</a></li>
                        <li><a href="<?= BASE_URL ?>/admin/registro-veterinaria"
                                class="<?= sa_active(['registro-veterinaria'], $saSeccion) ?>"
                                <?= $saSeccion === 'registro-veterinaria' ? 'aria-current="page"' : '' ?>>Registrar nueva</a></li>
                    </ul>
                </div>
            </li>

            <!-- Usuarios -->
            <li class="sa-group <?= sa_active($saUsuarios, $saSeccion) ? 'is-open is-active' : '' ?>">
                <button type="button" class="sa-item sa-group-toggle" data-label="Usuarios"
                    aria-expanded="<?= sa_active($saUsuarios, $saSeccion) ? 'true' : 'false' ?>"
                    aria-controls="sa-sub-usuarios">
                    <span class="sa-icon"><i class="bi bi-person-gear" aria-hidden="true"></i></span>
                    <span class="sa-text">Usuarios</span>
                    <i class="bi bi-chevron-down sa-chevron" aria-hidden="true"></i>
                </button>
                <div class="sa-sub" id="sa-sub-usuarios" data-title="Usuarios">
                    <ul>
                        <li><a href="<?= BASE_URL ?>/admin/listar-usuarios"
                                class="<?= sa_active(['listar-usuarios', 'editar-usuario'], $saSeccion) ?>"
                                <?= in_array($saSeccion, ['listar-usuarios', 'editar-usuario'], true) ? 'aria-current="page"' : '' ?>>Listado</a></li>
                        <li><a href="<?= BASE_URL ?>/admin/registro-usuario"
                                class="<?= sa_active(['registro-usuario'], $saSeccion) ?>"
                                <?= $saSeccion === 'registro-usuario' ? 'aria-current="page"' : '' ?>>Registrar nuevo</a></li>
                    </ul>
                </div>
            </li>

            <!-- Propietarios: un solo destino, enlace directo -->
            <li>
                <a href="<?= BASE_URL ?>/admin/listar-propietarios"
                    class="sa-item <?= sa_active(['listar-propietarios'], $saSeccion) ?>" data-label="Propietarios"
                    <?= $saSeccion === 'listar-propietarios' ? 'aria-current="page"' : '' ?>>
                    <span class="sa-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                    <span class="sa-text">Propietarios</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/directorio"
                    class="sa-item <?= sa_active(['directorio', 'ver-perfil', 'profesionales'], $saSeccion) ?>"
                    data-label="Directorio"
                    <?= in_array($saSeccion, ['directorio', 'ver-perfil', 'profesionales'], true) ? 'aria-current="page"' : '' ?>>
                    <span class="sa-icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></span>
                    <span class="sa-text">Directorio</span>
                </a>
            </li>
        </ul>

        <p class="sa-label">Sistema</p>
        <ul class="sa-list">
            <li>
                <a href="<?= BASE_URL ?>/admin/listar-tickets"
                    class="sa-item <?= sa_active(['listar-tickets', 'gestion-tickets'], $saSeccion) ?>"
                    data-label="Tickets de soporte"
                    <?= in_array($saSeccion, ['listar-tickets', 'gestion-tickets'], true) ? 'aria-current="page"' : '' ?>>
                    <span class="sa-icon"><i class="bi bi-life-preserver" aria-hidden="true"></i></span>
                    <span class="sa-text">Tickets de soporte</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/reportes" class="sa-item <?= sa_active(['reportes'], $saSeccion) ?>"
                    data-label="Reportes" <?= $saSeccion === 'reportes' ? 'aria-current="page"' : '' ?>>
                    <span class="sa-icon"><i class="bi bi-bar-chart-line" aria-hidden="true"></i></span>
                    <span class="sa-text">Reportes</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/medios-envio"
                    class="sa-item <?= sa_active(['medios-envio'], $saSeccion) ?>" data-label="Medios de envío"
                    <?= $saSeccion === 'medios-envio' ? 'aria-current="page"' : '' ?>>
                    <span class="sa-icon"><i class="bi bi-send" aria-hidden="true"></i></span>
                    <span class="sa-text">Medios de envío</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Pie: usuario y cerrar sesión -->
    <div class="sa-footer">
        <a href="<?= BASE_URL ?>/admin/perfil-administrador"
            class="sa-user <?= sa_active(['perfil-administrador'], $saSeccion) ?>" data-label="Mi perfil">
            <span class="sa-avatar" aria-hidden="true"><?= htmlspecialchars($saIniciales, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="sa-user-info sa-text">
                <strong><?= htmlspecialchars($saNombre, ENT_QUOTES, 'UTF-8') ?></strong>
                <small>Super administrador</small>
            </span>
        </a>
        <a href="<?= BASE_URL ?>/cerrar-sesion" class="sa-logout" data-label="Cerrar sesión" aria-label="Cerrar sesión">
            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
        </a>
    </div>
</aside>

<!-- Tooltip y menú flotante compartidos para el modo colapsado (los posiciona el JS) -->
<div class="sa-tooltip" id="saTooltip" role="tooltip" hidden></div>

<script src="<?= BASE_URL ?>/public/assets/dashBoard/administrador/js/sidebarA.js" defer></script>
