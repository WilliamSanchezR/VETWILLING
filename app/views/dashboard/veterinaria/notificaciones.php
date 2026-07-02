<?php

require_once BASE_PATH . '/app/helpers/session_veterinario.php';

$baseUrl = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Notificaciones · VetWilling</title>

    <!-- Preconnect a Google Fonts: evita el "descubrimiento tardío" que provocaba
         el @import dentro del CSS (bloqueaba un ciclo extra de render) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap">

    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES) ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES) ?>/public/assets/dashBoard/veterinarias/css/navbar-superior.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES) ?>/public/assets/dashBoard/veterinarias/css/notificaciones.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_veterinario.php'; ?>

    <div class="contenido-principal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php'; ?>

        <main class="area-contenido">
            <section class="page-section page-section--notifications" aria-labelledby="notifTitle">

                <header class="page-section__header">
                    <div>
                        <span class="section-label">Notificaciones</span>
                        <h1 id="notifTitle">Mis avisos</h1>
                        <p class="section-description">
                            Revisa todas las notificaciones de tu cuenta y marca las que ya has leído.
                        </p>
                    </div>

                    <div class="page-actions">
                        <button type="button" class="btn btn-primary" id="btnMarkAllRead">
                            <i class="bi bi-check2-all" aria-hidden="true"></i>
                            Marcar todas leídas
                        </button>
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES) ?>/veterinaria/dashboard" class="btn btn-secondary">
                            <i class="bi bi-arrow-left" aria-hidden="true"></i>
                            Volver al dashboard
                        </a>
                    </div>
                </header>

                <div class="notifications-page-card">

                    <!-- Estado de carga inicial: evita el "flash" de card vacía mientras responde el fetch -->
                    <div id="notificationsLoading" class="loading-state" role="status" aria-live="polite">
                        <div class="spinner" aria-hidden="true"></div>
                        <p>Cargando notificaciones…</p>
                    </div>

                    <!-- Estado de error: notificaciones.js lo muestra si falla el fetch -->
                    <div id="notificationsError" class="error-state is-hidden" role="alert">
                        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                        <p>
                            No pudimos cargar tus notificaciones.
                            <button type="button" id="btnRetryNotifications" class="btn-link">Reintentar</button>
                        </p>
                    </div>

                    <!-- Listado. role="feed" + aria-live: lectores de pantalla anuncian novedades -->
                    <div
                        id="notificationsPageList"
                        class="notifications-list notifications-page-list"
                        role="feed"
                        aria-busy="true"
                        aria-live="polite">
                    </div>

                    <div id="notificationsEmpty" class="empty-state is-hidden">
                        <div class="empty-icon">
                            <i class="bi bi-bell-slash" aria-hidden="true"></i>
                        </div>
                        <h2>No hay notificaciones por leer</h2>
                        <p>Las notificaciones aparecerán aquí cuando haya novedades sobre citas, seguimientos o recordatorios.</p>
                    </div>

                </div>
            </section>
        </main>
    </div>

    <script>
        // json_encode evita romper el JS si BASE_URL llegara a traer comillas/backslashes
        window.BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="<?= htmlspecialchars($baseUrl, ENT_QUOTES) ?>/public/assets/dashBoard/veterinarias/js/notificaciones.js" defer></script>
</body>

</html>