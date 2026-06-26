<?php
require_once BASE_PATH . '/app/helpers/session_all.php';
$idRol     = (int) ($_SESSION['user']['id_rol'] ?? 0);
$idUsuario = (int) ($_SESSION['user']['id_usuario'] ?? 0);
$final_path = 'directorio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio de Profesionales – VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <?php if ($idRol === 3): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <?php elseif ($idRol === 2 || $idRol === 4): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/sidebarV.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <?php else: ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/global/css/menuStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <?php endif; ?>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/directorio/css/directorio.css">
</head>
<body>

<?php
if ($idRol === 3):
    include_once __DIR__ . '/../../layouts/sidebar_pasiente.php';
elseif ($idRol === 2 || $idRol === 4):
    include_once __DIR__ . '/../../layouts/sidebar_veterinario.php';
else:
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php';
endif;
?>

<div class="contenido-principal" id="contenidoPrincipal">

    <?php
    if ($idRol === 3):
        include_once __DIR__ . '/../../layouts/panel_superio_paciente.php';
    elseif ($idRol === 2 || $idRol === 4):
        include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php';
    else:
        include_once __DIR__ . '/../../layouts/panel_superior_administrador.php';
    endif;
    ?>

    <div class="area-contenido">

        <div class="encabezado-modulo d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-people-fill" style="font-size:1.4rem;color:#0a932c;"></i>
            <h3 class="mb-0">Directorio de Profesionales</h3>
        </div>

        <?php if ($idRol === 2): ?>
        <div class="banner-estado" id="bannerEstado">
            <i class="bi bi-person-badge-fill" style="font-size:1.6rem;"></i>
            <div>
                <div class="lbl">Tu estado de disponibilidad</div>
                <small>Los propietarios verán este estado en tu perfil del directorio</small>
            </div>
            <select id="selectEstadoPropio" class="ms-auto">
                <option value="Activo">🟢 Activo</option>
                <option value="En consulta">🟡 En consulta</option>
                <option value="De vacaciones">🔵 De vacaciones</option>
                <option value="No disponible">⚫ No disponible</option>
            </select>
            <button type="button" class="btn btn-light btn-sm" id="btnGuardarEstado">
                <i class="bi bi-check-lg"></i> Guardar
            </button>
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="dir-filtros">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Buscar profesional</label>
                    <div class="dir-input-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" id="inputBusqueda" class="dir-input" placeholder="Nombre, apellido…">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Especialidad</label>
                    <select id="filtroEsp" class="dir-select">
                        <option value="">Todas las especialidades</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Disponibilidad</label>
                    <select id="filtroDisp" class="dir-select">
                        <option value="">Todos los estados</option>
                        <option value="Activo">Activo</option>
                        <option value="En consulta">En consulta</option>
                        <option value="De vacaciones">De vacaciones</option>
                        <option value="No disponible">No disponible</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" id="btnLimpiar" class="btn btn-outline-secondary w-100" title="Limpiar filtros">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>

        <p class="text-muted small mb-3" id="totalResultados"></p>

        <div id="gridProfesionales">
            <div class="spinner-wrap">
                <div class="spinner-border text-success" role="status"></div>
                <p class="mt-2 small text-muted">Cargando directorio…</p>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.BASE_URL  = "<?= BASE_URL ?>";
    window.ID_ROL    = <?= $idRol ?>;
    window.ID_USUARIO = <?= $idUsuario ?>;
</script>
<script src="<?= BASE_URL ?>/public/assets/dashBoard/directorio/js/directorio.js"></script>
</body>
</html>
