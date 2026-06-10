<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Veterinario</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/navbar-superior.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/notificaciones.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_veterinario.php'; ?>
    <div class="contenido-principal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php'; ?>

        <div class="area-contenido">
            <section class="page-section page-section--notifications">
                <header class="page-section__header">
                    <div>
                        <span class="section-label">Notificaciones</span>
                        <h1>Mis avisos</h1>
                        <p class="section-description">Revisa todas las notificaciones de tu cuenta y marca las que ya has leído.</p>
                    </div>
                    <div class="page-actions">
                        <button type="button" class="btn btn-primary" id="btnMarkAllRead">Marcar todas leídas</button>
                        <a href="<?= BASE_URL ?>/veterinaria/dashboard" class="btn btn-secondary">Volver al dashboard</a>
                    </div>
                </header>

                <div class="notifications-page-card">
                    <div id="notificationsPageList" class="notifications-list notifications-page-list"></div>
                    <div id="notificationsEmpty" class="empty-state is-hidden">
                        <div class="empty-icon">
                            <i class="bi bi-bell-slash"></i>
                        </div>
                        <h2>No hay notificaciones por leer</h2>
                        <p>Las notificaciones aparecerán aquí cuando haya novedades sobre citas, seguimientos o recordatorios.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        window.BASE_URL = "<?= BASE_URL ?>";
    </script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/notificaciones.js"></script>
</body>

</html>