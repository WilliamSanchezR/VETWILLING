<?php

/**
 * dashboard_cliente.php  —  v2.0
 * Vista principal del propietario (cliente).
 *
 * Variables disponibles desde session_propietario.php:
 *   $usuario['nombres']    — Nombre(s) del propietario
 *   $usuario['apellidos']  — Apellidos
 *
 * Los arrays $citas, $chips, $recordatorios y $notificaciones
 * son datos de ejemplo. Reemplazar por consultas al modelo
 * cuando el backend esté listo.
 */
require_once BASE_PATH . '/app/helpers/session_propietario.php';
// Cargar preferencias e i18n
if (!isset($prefs)) {
    if (!class_exists('PreferenciasManager')) { require_once BASE_PATH . '/app/services/PreferenciasManager.php'; }
    $_pm = new PreferenciasManager((int)$_SESSION['user']['id_usuario']);
    $prefs = $_pm->obtener();
}
if (!isset($t)) {
    if (!class_exists('I18n')) { require_once BASE_PATH . '/app/lang/i18n.php'; }
    $t = I18n::cargar($prefs['idioma']);
}
// esta parte es para lo que seria cargar las citas y recomendaciones
require_once BASE_PATH . '/app/models/CitasCliente.php';

$usuario = $_SESSION['user'] ?? [];
$id_propietario = $_SESSION['user']['id_propietario'] ?? null;

if (!$id_propietario && isset($_SESSION['user']['id_usuario'])) {
    $modeloCitas = new CitasCliente();
    $id_propietario = $modeloCitas->obtenerIdPropietarioPorUsuario((int)$_SESSION['user']['id_usuario']);
    if ($id_propietario) {
        $_SESSION['user']['id_propietario'] = $id_propietario;
    }
}

$proximasCitas = [];

if ($id_propietario) {
    if (!isset($modeloCitas)) {
        $modeloCitas = new CitasCliente();
    }

    $filtros = ['fecha_inicio' => date('Y-m-d')];
    $citas = $modeloCitas->listarCitasPropietario($id_propietario, $filtros);

    $proximasCitas = array_filter($citas, function ($cita) {
        return !in_array($cita['estado'], ['Cancelada', 'Realizada']) &&
            strtotime($cita['fecha_hora']) >= strtotime(date('Y-m-d') . ' 00:00:00');
    });

    usort($proximasCitas, function ($a, $b) {
        return strtotime($a['fecha_hora']) - strtotime($b['fecha_hora']);
    });

    $proximasCitas = array_slice($proximasCitas, 0, 3);
}

function formatCitaFechaDia($fechaHora)
{
    $dt = new DateTime($fechaHora);
    return $dt->format('d');
}

function formatCitaFechaMes($fechaHora)
{
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $dt = new DateTime($fechaHora);
    return $meses[(int)$dt->format('n') - 1];
}

function formatCitaHora($fechaHora)
{
    $dt = new DateTime($fechaHora);
    return $dt->format('h:i A');
}

function cleanText($texto)
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function formatReminderDate($fecha)
{
    if (empty($fecha)) {
        return 'Fecha no disponible';
    }

    try {
        $dt = new DateTime($fecha);
        return $dt->format('d/m/Y');
    } catch (Exception $e) {
        return 'Fecha no válida';
    }
}

function obtenerRecordatorios($id_propietario)
{
    $recordatorios = [];
    $modeloCitas = new CitasCliente();
    $mascotas = $modeloCitas->obtenerMascotasPropietario($id_propietario);
    $hoy = new DateTime('today');

    foreach ($mascotas as $mascota) {
        $id_paciente = (int)$mascota['id_paciente'];
        $vacunas = $modeloCitas->obtenerVacunasPorPaciente($id_propietario, $id_paciente);

        foreach ($vacunas as $vacuna) {
            try {
                $fechaAplicacion = new DateTime($vacuna['fecha_aplicacion']);
            } catch (Exception $e) {
                continue;
            }

            if ($fechaAplicacion >= $hoy) {
                $recordatorios[] = [
                    'tipo' => 'vacuna',
                    'mascota' => $mascota['nombre'] ?? 'Mascota',
                    'titulo' => $vacuna['tipo_vacuna'] ?: 'Vacuna próxima',
                    'detalle' => trim(($vacuna['dosis'] ? $vacuna['dosis'] . ' - ' : '') . 'Profesional: ' . ($vacuna['profesional_nombre'] ?? 'No especificado')),
                    'fecha' => $vacuna['fecha_aplicacion'],
                ];
            }
        }

        $tratamientos = $modeloCitas->obtenerTratamientosPorPaciente($id_propietario, $id_paciente);

        foreach ($tratamientos as $tratamiento) {
            $estado = strtolower(trim($tratamiento['estado'] ?? ''));
            $fechaFin = $tratamiento['fecha_fin'] ?? null;
            $fechaInicio = $tratamiento['fecha_inicio'] ?? null;
            $vencimientoOK = false;

            try {
                $fechaFinObj = $fechaFin ? new DateTime($fechaFin) : null;
                if ($fechaFinObj) {
                    $vencimientoOK = $fechaFinObj >= $hoy;
                } else {
                    $vencimientoOK = true;
                }
            } catch (Exception $e) {
                $vencimientoOK = true;
            }

            if (($estado === 'activo' || $estado === 'en curso' || $estado === 'en proceso') && $vencimientoOK) {
                $recordatorios[] = [
                    'tipo' => 'tratamiento',
                    'mascota' => $mascota['nombre'] ?? 'Mascota',
                    'titulo' => ($tratamiento['medicamento'] ?: 'Tratamiento en proceso') . ($tratamiento['dosis'] ? ' - ' . $tratamiento['dosis'] : ''),
                    'detalle' => trim('Desde: ' . ($fechaInicio ?? 'N/A') . ($fechaFin ? ' Hasta: ' . $fechaFin : '')),
                    'fecha' => $fechaFin ?: $fechaInicio,
                ];
            }
        }
    }

    usort($recordatorios, function ($a, $b) {
        $fechaA = strtotime($a['fecha'] ?? '9999-12-31');
        $fechaB = strtotime($b['fecha'] ?? '9999-12-31');
        return $fechaA - $fechaB;
    });

    return array_slice($recordatorios, 0, 4);
}

$recordatorios = [];
if ($id_propietario) {
    $recordatorios = obtenerRecordatorios($id_propietario);
}

?>

<!-- Aca se finaliza lo que seria todo el php de las citas y los recorfatorios -->

<!DOCTYPE html>
<html lang="<?= $prefs['idioma'] === 'pt' ? 'pt-BR' : $prefs['idioma'] ?>">

<head>
    <meta charset="UTF-8">
    <script>(function(){var p=<?= json_encode($prefs) ?>;document.documentElement.setAttribute('data-tema',p.tema);if(p.tema==='oscuro'&&document.body)document.body.classList.add('dark-theme');}());</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — VetWilling</title>

        <!-- Bootstrap -->
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/theme.css?v=<?= APP_VERSION ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">


    <!-- CSS del módulo cliente (sidebar, navbar, base) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/adactavilidad.css?v=<?= APP_VERSION ?>">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css?v=<?= APP_VERSION ?>">

    <!-- CSS exclusivo de ESTA vista -->
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/dashboard-cliente.css"> -->
</head>

<body class="<?= $prefs['tema'] === 'oscuro' ? 'dark-theme' : '' ?>" data-tema="<?= $prefs['tema'] ?>">

    <!-- ============================================================
     SIDEBAR
     ============================================================ -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>


    <!-- ============================================================
     CONTENIDO PRINCIPAL
     ============================================================ -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- Navbar superior -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <div class="container-dashboard">

                <!-- ------------------------------------------------
                 BANNER DE BIENVENIDA
                 ------------------------------------------------ -->
                <div class="bienvenida-banner dash-fade-in">

                    <div class="row align-items-center">

                        <!-- COL IZQUIERDA: texto -->
                        <div class="col-md-6 ban-col-left">
                            <div class="bienvenida-fecha">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                <span id="dashFecha">Cargando fecha…</span>
                            </div>

                            <p class="bienvenida-saludo">Te damos la bienvenida</p>

                            <h2 class="bienvenida-nombre">
                                <?= htmlspecialchars($datosUsuario['nombres']) ?>
                                <?= htmlspecialchars($datosUsuario['apellidos']) ?>
                            </h2>

                            <p class="bienvenida-frase">
                                En VetWilling cuidamos de tus mascotas con amor y profesionalismo.
                                <strong>Tu familia está en buenas patas.</strong>
                            </p>
                        </div>

                        <!-- COL DERECHA: avatar + stats -->
                        <div class="col-md-6 ban-col-right">

                            <div class="bienvenida-avatar">
                                <i class="bi bi-heart-pulse"></i>
                            </div>

                            <div class="bienvenida-stats">
                                <div class="banner-stat">
                                    <span class="banner-stat-num">1</span>
                                    <span class="banner-stat-lbl">Citas</span>
                                </div>

                                <div class="banner-stat">
                                    <span class="banner-stat-num">2</span>
                                    <span class="banner-stat-lbl">Mascotas</span>
                                </div>

                                <div class="banner-stat">
                                    <span class="banner-stat-num">4</span>
                                    <span class="banner-stat-lbl">Alertas</span>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- ------------------------------------------------
                 CHIPS DE RECORDATORIO
                 ------------------------------------------------ -->


                <!-- ------------------------------------------------
                 GRID: CITAS + RECORDATORIOS DETALLADOS
                 ------------------------------------------------ -->
                <div class="dash-content-grid">

                    <!-- PRÓXIMAS CITAS -->
                    <div class="dash-card dash-fade-in" aria-label="Próximas citas">
                        <div class="dash-card-header">
                            <h3 class="dash-card-titulo">
                                <i class="bi bi-calendar2-week" aria-hidden="true"></i>
                                Próximas citas
                            </h3>
                            <a href="<?= BASE_URL ?>/cliente/citas" class="dash-card-accion">Ver todas</a>
                        </div>

                        <div class="citas-lista">

                            <?php if (empty($proximasCitas)): ?>
                                <div class="citas-vacio">
                                    <i class="bi bi-calendar-x" aria-hidden="true"></i>
                                    <p>No tienes citas programadas</p>
                                </div>
                            <?php else: ?>

                                <?php foreach ($proximasCitas as $cita): ?>

                                    <div class="cita-row <?= strtolower($cita['tipo'] ?? '') === 'urgente' ? 'es-urgente' : '' ?>">

                                        <div class="cita-fecha-bloque" aria-hidden="true">
                                            <span class="cita-dia-num">
                                                <?= formatCitaFechaDia($cita['fecha_hora']) ?>
                                            </span>

                                            <span class="cita-mes-txt">
                                                <?= formatCitaFechaMes($cita['fecha_hora']) ?>
                                            </span>
                                        </div>

                                        <div class="cita-info-bloque">

                                            <div class="cita-nombre">
                                                <?= cleanText($cita['mascota_nombre'] . ' - ' . ($cita['tipo'] ?: 'Cita')) ?>
                                            </div>

                                            <div class="cita-detalle">
                                                <?= cleanText(
                                                    $cita['veterinario_nombre']
                                                        . ' - '
                                                        . ($cita['subservicio_nombre']
                                                            ?: $cita['servicio_nombre']
                                                            ?: 'Sin servicio')
                                                ) ?>
                                            </div>

                                        </div>

                                        <span class="cita-hora-tag">
                                            <?= formatCitaHora($cita['fecha_hora']) ?>
                                        </span>

                                    </div>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>
                    </div>


                    <!-- RECORDATORIOS DETALLADOS -->
                    <div class="dash-card dash-fade-in" aria-label="Recordatorios detallados">
                        <div class="dash-card-header">
                            <h3 class="dash-card-titulo">
                                <i class="bi bi-bell-fill" aria-hidden="true"></i>
                                Recordatorios
                            </h3>
                        </div>

                        <div class="recordatorios-lista">

                            <?php if (!empty($recordatorios)): ?>

                                <?php foreach ($recordatorios as $item): ?>

                                    <div
                                        class="recordatorio-row"
                                        style="
                                --rec-color: <?= $item['tipo'] === 'vacuna' ? '#dc3545' : '#0d6efd' ?>;
                                --rec-bg: <?= $item['tipo'] === 'vacuna' ? '#fdeaea' : '#eaf2ff' ?>;
                            ">

                                        <div class="recordatorio-icono">
                                            <i class="bi <?= $item['tipo'] === 'vacuna'
                                                                ? 'bi-heart-pulse-fill'
                                                                : 'bi-capsule' ?>"
                                                aria-hidden="true"></i>
                                        </div>

                                        <div class="recordatorio-info">

                                            <div class="recordatorio-titulo-item">
                                                <?= cleanText($item['titulo'] . ' - ' . $item['mascota']) ?>
                                            </div>

                                            <div class="recordatorio-desc">
                                                <?= cleanText($item['detalle'] . ' · ' . formatReminderDate($item['fecha'])) ?>
                                            </div>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <div class="citas-vacio">
                                    <i class="bi bi-bell" aria-hidden="true"></i>
                                    <p>No hay recordatorios programados</p>
                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div><!-- /container-dashboard -->
        </div><!-- /area-contenido -->

    </main>


    <!-- ============================================================
     SCRIPTS
     ============================================================ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js?v=<?= APP_VERSION ?>"></script>
    <!-- <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/dashboard-cliente.js"></script> -->

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/theme.js?v=<?= APP_VERSION ?>"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/i18n.js?v=<?= APP_VERSION ?>"></script>
</body>

</html>
