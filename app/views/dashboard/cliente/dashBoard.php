<?php
/**
 * dashboard_cliente.php
 * Vista principal del propietario (cliente).
 *
 * Variables disponibles desde session_propietario.php:
 *   $usuario['nombres']   — Nombre(s) del propietario
 *   $usuario['apellidos'] — Apellidos (si se necesitan)
 *
 * Datos de mascotas, citas y recordatorios: quemados por ahora.
 * Cuando el backend esté listo, reemplazar los arrays de ejemplo
 * por consultas al modelo correspondiente.
 */
require_once BASE_PATH . '/app/helpers/session_propietario.php';

/* ----------------------------------------------------------
   Detectar género para elegir la ilustración del banner.
   Por ahora: si el nombre termina en 'a' asumimos femenino.
   Esto se puede mejorar con un campo en la tabla usuario.
   ---------------------------------------------------------- */
$nombre_display = $usuario['nombres'] ?? 'Bienvenido';
$primera_letra  = strtoupper(substr(trim($nombre_display), 0, 2));
$ultimo_char    = strtolower(substr(trim($nombre_display), -1));
$es_femenino    = in_array($ultimo_char, ['a', 'e']); // heurística simple

/* Ilustración según género (emoji o SVG inline) */
$ilustracion = $es_femenino ? '🐾' : '🐾'; // se puede cambiar por SVG
/* Nota: si tienes imágenes PNG de ilustración puedes usar:
   $ilustracion_img = $es_femenino
       ? BASE_URL . '/public/assets/img/mascota-mujer.svg'
       : BASE_URL . '/public/assets/img/mascota-hombre.svg';
*/

/* ----------------------------------------------------------
   DATOS DE EJEMPLO — reemplazar con consultas reales
   ---------------------------------------------------------- */

/* Próximas citas */
$citas = [
    [
        'dia'      => '22',
        'mes'      => 'Nov',
        'nombre'   => 'Max — Control general',
        'detalle'  => 'Dr. Martínez · Consulta',
        'hora'     => '10:30 AM',
        'urgente'  => true,
    ],
    [
        'dia'      => '25',
        'mes'      => 'Nov',
        'nombre'   => 'Max — Vacunación',
        'detalle'  => 'Dra. López · Antirrábica',
        'hora'     => '3:00 PM',
        'urgente'  => false,
    ],
    [
        'dia'      => '28',
        'mes'      => 'Nov',
        'nombre'   => 'Luna — Baño y corte',
        'detalle'  => 'Peluquería · Grooming',
        'hora'     => '11:00 AM',
        'urgente'  => false,
    ],
];

/* Chips de recordatorio (barra superior) */
$chips = [
    [
        'icono'   => 'bi-capsule',
        'texto'   => 'Medicamento',
        'clase'   => 'chip-medicina',
        'tooltip' => 'Rocky: Antiinflamatorio — 5 ml cada 24 h hasta el 30 nov.',
        'urgente' => false,
    ],
    [
        'icono'   => 'bi-syringe',
        'texto'   => 'Vacuna pendiente',
        'clase'   => 'chip-vacuna',
        'tooltip' => 'Max: Vacuna antirrábica programada para el 25 de noviembre.',
        'urgente' => true,
    ],
    [
        'icono'   => 'bi-heart-pulse',
        'texto'   => 'Control anual',
        'clase'   => 'chip-control',
        'tooltip' => 'Rocky: Programar chequeo general para diciembre.',
        'urgente' => false,
    ],
    [
        'icono'   => 'bi-droplet',
        'texto'   => 'Baño programado',
        'clase'   => 'chip-bano',
        'tooltip' => 'Luna: Baño y corte el 28 de noviembre a las 11:00 AM.',
        'urgente' => false,
    ],
];

/* Recordatorios detallados (card derecha) */
$recordatorios = [
    [
        'icono'  => 'bi-syringe',
        'titulo' => 'Vacuna antirrábica — Max',
        'desc'   => 'Programada el 25 de noviembre',
        'color'  => '#7c3aed',
        'bg'     => '#faf5ff',
    ],
    [
        'icono'  => 'bi-capsule',
        'titulo' => 'Desparasitación — Luna',
        'desc'   => 'Próxima dosis en 5 días',
        'color'  => '#0284c7',
        'bg'     => '#f0f9ff',
    ],
    [
        'icono'  => 'bi-heart-pulse',
        'titulo' => 'Control anual — Rocky',
        'desc'   => 'Programar chequeo para diciembre',
        'color'  => '#0a932c',
        'bg'     => '#f0fdf4',
    ],
    [
        'icono'  => 'bi-droplet',
        'titulo' => 'Baño programado — Luna',
        'desc'   => '28 de noviembre · 11:00 AM',
        'color'  => '#0891b2',
        'bg'     => '#ecfeff',
    ],
];

/* Notificaciones del panel lateral */
$notificaciones = [
    [
        'icono'    => 'bi-calendar2-check',
        'color'    => 'verde',
        'mensaje'  => 'Tu cita para Max fue confirmada para el 22 de noviembre.',
        'tiempo'   => 'Hace 10 minutos',
        'leida'    => false,
    ],
    [
        'icono'    => 'bi-syringe',
        'color'    => 'naranja',
        'mensaje'  => 'Recuerda: Max tiene vacuna antirrábica el 25 de nov.',
        'tiempo'   => 'Hace 1 hora',
        'leida'    => false,
    ],
    [
        'icono'    => 'bi-file-medical',
        'color'    => 'azul',
        'mensaje'  => 'El historial clínico de Luna fue actualizado.',
        'tiempo'   => 'Ayer, 4:30 PM',
        'leida'    => true,
    ],
];

$total_no_leidas = count(array_filter($notificaciones, fn($n) => !$n['leida']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — VetWilling</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (único set de iconos del proyecto) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

        <!-- Favicon -->
        <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS del módulo cliente (sidebar, navbar, base) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">

    <!-- CSS exclusivo de ESTA vista -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/dashboard-cliente.css">
</head>
<body>

<!-- ============================================================
     SIDEBAR
     ============================================================ -->
<?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

<!-- ============================================================
     OVERLAY + PANEL LATERAL DE NOTIFICACIONES
     ============================================================ -->
<div class="notif-panel-overlay" id="notifOverlay" aria-hidden="true"></div>

<aside class="notif-panel" id="notifPanel" role="dialog" aria-modal="true" aria-label="Notificaciones">

    <div class="notif-panel-header">
        <span class="notif-panel-titulo">
            <i class="bi bi-bell-fill"></i>
            Notificaciones
            <?php if ($total_no_leidas > 0): ?>
                <span class="badge bg-danger rounded-pill" style="font-size:11px;">
                    <?= $total_no_leidas ?>
                </span>
            <?php endif; ?>
        </span>
        <button class="notif-panel-cerrar" id="btnCerrarNotif" aria-label="Cerrar notificaciones">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="notif-panel-body">
        <?php if (empty($notificaciones)): ?>
            <div class="notif-vacio">
                <i class="bi bi-bell-slash"></i>
                <p>Sin notificaciones por ahora</p>
            </div>
        <?php else: ?>
            <?php foreach ($notificaciones as $notif): ?>
                <div class="notif-item <?= !$notif['leida'] ? 'no-leida' : '' ?>">
                    <div class="notif-icono-wrap <?= $notif['color'] ?>">
                        <i class="bi <?= $notif['icono'] ?>"></i>
                    </div>
                    <div class="notif-contenido">
                        <p class="notif-msg"><?= htmlspecialchars($notif['mensaje']) ?></p>
                        <span class="notif-tiempo"><?= $notif['tiempo'] ?></span>
                    </div>
                    <?php if (!$notif['leida']): ?>
                        <span class="notif-punto" aria-label="No leída"></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="notif-panel-footer">
        <a href="#" class="btn-ver-todas">Ver todas las notificaciones</a>
    </div>

</aside>


<!-- ============================================================
     CONTENIDO PRINCIPAL
     ============================================================ -->
<main class="contenido-principal" id="contenidoPrincipal">

    <!-- Navbar superior -->
    <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

    <div class="area-contenido">
        <div class="dashboard-wrapper">
            <div class="container-dashboard">

                <!-- ----------------------------------------
                     BANNER DE BIENVENIDA
                     ---------------------------------------- -->
                <div class="bienvenida-banner dash-fade-in">

                    <div class="bienvenida-texto">
                        <!-- Chip de fecha dinámica (llenado por JS) -->
                        <div class="bienvenida-fecha">
                            <i class="bi bi-calendar3"></i>
                            <span id="dashFecha">Cargando fecha…</span>
                        </div>

                        <p class="bienvenida-saludo">Bienvenido de nuevo</p>
                        <h2 class="bienvenida-nombre">
                            <?= htmlspecialchars($nombre_display) ?>
                        </h2>
                        <p class="bienvenida-frase">
                            En VetWilling cuidamos de tus mascotas con amor,
                            profesionalismo y dedicación.
                            <strong>Tu familia está en buenas patas.</strong>
                        </p>
                    </div>

                    <!-- Ilustración flotante -->
                    <div class="bienvenida-ilustracion" aria-hidden="true">
                        <?= $ilustracion ?>
                    </div>

                </div>


                <!-- ----------------------------------------
                     CHIPS DE RECORDATORIO
                     ---------------------------------------- -->
                <?php if (!empty($chips)): ?>
                <section class="recordatorios-section dash-fade-in" aria-label="Recordatorios rápidos">
                    <h3 class="recordatorios-titulo">
                        <i class="bi bi-bell"></i>
                        Recordatorios
                    </h3>
                    <div class="recordatorios-chips" role="list">
                        <?php foreach ($chips as $chip): ?>
                            <div
                                class="chip-recordatorio <?= $chip['clase'] ?>"
                                role="listitem"
                                aria-label="<?= htmlspecialchars($chip['tooltip']) ?>"
                            >
                                <i class="bi <?= $chip['icono'] ?>"></i>
                                <span><?= htmlspecialchars($chip['texto']) ?></span>
                                <?php if ($chip['urgente']): ?>
                                    <span class="chip-badge" aria-label="Urgente">!</span>
                                <?php endif; ?>
                                <!-- Tooltip (visible al hover / focus) -->
                                <span class="chip-tooltip" role="tooltip">
                                    <?= htmlspecialchars($chip['tooltip']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>


                <!-- ----------------------------------------
                     GRID: CITAS + RECORDATORIOS DETALLADOS
                     ---------------------------------------- -->
                <div class="dash-content-grid">

                    <!-- PRÓXIMAS CITAS -->
                    <div class="dash-card dash-fade-in" aria-label="Próximas citas">
                        <div class="dash-card-header">
                            <h3 class="dash-card-titulo">
                                <i class="bi bi-calendar2-week"></i>
                                Próximas citas
                            </h3>
                            <a href="#" class="dash-card-accion">Ver todas</a>
                        </div>

                        <div class="citas-lista">
                            <?php if (empty($citas)): ?>
                                <div class="citas-vacio">
                                    <i class="bi bi-calendar-x"></i>
                                    <p>No tienes citas programadas</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($citas as $cita): ?>
                                    <div class="cita-row <?= $cita['urgente'] ? 'es-urgente' : '' ?>">

                                        <!-- Bloque día / mes -->
                                        <div class="cita-fecha-bloque">
                                            <span class="cita-dia-num"><?= $cita['dia'] ?></span>
                                            <span class="cita-mes-txt"><?= $cita['mes'] ?></span>
                                        </div>

                                        <!-- Info central -->
                                        <div class="cita-info-bloque">
                                            <div class="cita-nombre"><?= htmlspecialchars($cita['nombre']) ?></div>
                                            <div class="cita-detalle"><?= htmlspecialchars($cita['detalle']) ?></div>
                                        </div>

                                        <!-- Hora -->
                                        <span class="cita-hora-tag"><?= $cita['hora'] ?></span>

                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>


                    <!-- RECORDATORIOS DETALLADOS -->
                    <div class="dash-card dash-fade-in" aria-label="Recordatorios detallados">
                        <div class="dash-card-header">
                            <h3 class="dash-card-titulo">
                                <i class="bi bi-bell-fill"></i>
                                Recordatorios
                            </h3>
                        </div>

                        <div class="recordatorios-lista">
                            <?php foreach ($recordatorios as $rec): ?>
                                <div
                                    class="recordatorio-row"
                                    style="--rec-color:<?= $rec['color'] ?>; --rec-bg:<?= $rec['bg'] ?>;"
                                >
                                    <div class="recordatorio-icono">
                                        <i class="bi <?= $rec['icono'] ?>"></i>
                                    </div>
                                    <div class="recordatorio-info">
                                        <div class="recordatorio-titulo-item">
                                            <?= htmlspecialchars($rec['titulo']) ?>
                                        </div>
                                        <div class="recordatorio-desc">
                                            <?= htmlspecialchars($rec['desc']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div><!-- /dash-content-grid -->

            </div><!-- /container-dashboard -->
        </div><!-- /dashboard-wrapper -->
    </div><!-- /area-contenido -->

</main>


<!-- ============================================================
     SCRIPTS
     ORDEN IMPORTANTE:
       1. Bootstrap bundle (incluye Popper)
       2. clientes.js   — lógica global del módulo (sidebar, tema…)
       3. dashboard-cliente.js — lógica exclusiva de ESTA vista
     NO se carga clientes.js si el nombre original era ese;
     ajusta la ruta según tu estructura real.
     ============================================================ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/dashboard-cliente.js"></script>

</body>
</html>