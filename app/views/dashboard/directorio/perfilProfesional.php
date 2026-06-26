<?php
require_once BASE_PATH . '/app/helpers/session_all.php';
$idRol      = (int) ($_SESSION['user']['id_rol'] ?? 0);
$idUsuario  = (int) ($_SESSION['user']['id_usuario'] ?? 0);
$idPerfil   = (int) ($_GET['id_usuario'] ?? 0);
$final_path = 'directorio';

if ($idPerfil <= 0) {
    header('Location: ' . BASE_URL . '/directorio');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Profesional – VetWilling</title>

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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/directorio/css/perfilProfesional.css">
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

        <nav aria-label="breadcrumb" class="mb-3">
            <a href="<?= BASE_URL ?>/directorio" class="breadcrumb-link">
                <i class="bi bi-arrow-left"></i> Volver al Directorio
            </a>
        </nav>

        <div id="contenidoPerfil">
            <div class="spinner-wrap">
                <div class="spinner-border text-success" role="status"></div>
                <p class="mt-2 small text-muted">Cargando perfil…</p>
            </div>
        </div>

    </div>
</div>

<!-- Modal reseña -->
<div class="modal fade" id="modalResenia" tabindex="-1" aria-labelledby="lblModalResenia" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="lblModalResenia">
                    <i class="bi bi-star-fill text-warning"></i> Dejar reseña
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Selecciona una calificación y escribe tu comentario</p>
                <div class="star-select mb-3" id="starSelector">
                    <i class="bi bi-star-fill" data-val="1"></i>
                    <i class="bi bi-star-fill" data-val="2"></i>
                    <i class="bi bi-star-fill" data-val="3"></i>
                    <i class="bi bi-star-fill" data-val="4"></i>
                    <i class="bi bi-star-fill" data-val="5"></i>
                </div>
                <input type="hidden" id="calificacionSeleccionada" value="0">
                <textarea id="comentarioResenia" class="form-control mb-3" rows="3"
                          placeholder="Describe tu experiencia con este profesional…"></textarea>
                <div id="msgResenia" class="small mb-2"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnEnviarResenia" class="btn-enviar-res">
                    <i class="bi bi-send-fill"></i> Enviar reseña
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.BASE_URL   = "<?= BASE_URL ?>";
    window.ID_ROL     = <?= $idRol ?>;
    window.ID_USUARIO = <?= $idUsuario ?>;
    window.ID_PERFIL  = <?= $idPerfil ?>;
</script>
<script src="<?= BASE_URL ?>/public/assets/dashBoard/directorio/js/perfilProfesional.js"></script>
</body>
</html>
