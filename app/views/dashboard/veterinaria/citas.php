<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
require_once BASE_PATH . '/app/models/CitasVeterinario.php';

$modelo       = new CitasVeterinario();
$citas        = $modelo->listarCitas();
$veterinarios = $modelo->obtenerVeterinarios();
$stats        = $modelo->obtenerEstadisticas();

function citaAvatar(string $img, string $base): string
{
    if (!empty($img)) {
        return htmlspecialchars($base . '/public/uploads/mascotas/' . $img);
    }
    return htmlspecialchars($base . '/public/assets/webSite/img/perrito.png');
}

function citaBadgeLabel(string $estado): string
{
    return ['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada',
            'completada' => 'Completada', 'cancelada' => 'Cancelada'][$estado] ?? 'Pendiente';
}

function citaBotonesHTML(string $estado): string
{
    switch ($estado) {
        case 'pendiente':
            return '
                <button class="btn-accion btn-confirmar"><i class="bi bi-check-lg"></i> Confirmar</button>
                <button class="btn-accion btn-editar"><i class="bi bi-pencil-fill"></i> Editar</button>
                <button class="btn-accion btn-cancelar"><i class="bi bi-x-lg"></i> Cancelar</button>';
        case 'confirmada':
            return '
                <button class="btn-accion btn-completar"><i class="bi bi-check-circle-fill"></i> Completar</button>
                <button class="btn-accion btn-editar"><i class="bi bi-pencil-fill"></i> Editar</button>
                <button class="btn-accion btn-cancelar"><i class="bi bi-x-lg"></i> Cancelar</button>';
        default:
            return '<button class="btn-accion btn-editar btn-accion-neutral"><i class="bi bi-eye-fill"></i> Ver Detalles</button>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <title>Gestión de Citas | Dashboard Veterinario</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:wght@300..800&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleCitas.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar_veterinario.php' ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php' ?>

        <div class="area-contenido">
            <section class="citas-shell">

                <!-- ══ ENCABEZADO ══ -->
                <header class="encabezado-citas mb-4">
                    <div>
                        <h4 class="titulo-citas">
                            <i class="bi bi-calendar-check"></i>
                            Gestión de Citas
                        </h4>
                        <p class="subtitulo-citas mb-0">
                            Programa, confirma y controla todas las citas veterinarias
                        </p>
                    </div>
                    <button class="btn-nueva-cita" onclick="nuevaCita()">
                        <i class="bi bi-plus-circle"></i> Nueva Cita
                    </button>
                </header>

                <!-- ══ ESTADÍSTICAS ══ -->
                <!-- El JS usa querySelectorAll('.stat-card')[0..3].querySelector('h3') -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-3">
                        <div class="stat-card pendientes">
                            <div class="stat-icon pendientes"><i class="bi bi-clock-history"></i></div>
                            <div class="stat-content">
                                <h3><?= $stats['pendientes'] ?></h3>
                                <p>Pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="stat-card confirmadas">
                            <div class="stat-icon confirmadas"><i class="bi bi-check-circle"></i></div>
                            <div class="stat-content">
                                <h3><?= $stats['confirmadas'] ?></h3>
                                <p>Confirmadas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="stat-card completadas">
                            <div class="stat-icon completadas"><i class="bi bi-check-circle-fill"></i></div>
                            <div class="stat-content">
                                <h3><?= $stats['completadas'] ?></h3>
                                <p>Completadas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="stat-card canceladas">
                            <div class="stat-icon canceladas"><i class="bi bi-x-circle"></i></div>
                            <div class="stat-content">
                                <h3><?= $stats['canceladas'] ?></h3>
                                <p>Canceladas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ FILTROS + TABS ══ -->
                <div class="citas-control-card mb-4">
                    <div class="filtros-row">
                        <div class="campo-busqueda">
                            <i class="bi bi-search"></i>
                            <input type="text" id="buscarCita"
                                   placeholder="Buscar por paciente, propietario o servicio…">
                        </div>
                        <select class="filtro-select" id="filtroVeterinario">
                            <option value="">Todos los veterinarios</option>
                            <?php foreach ($veterinarios as $v): ?>
                                <option value="<?= (int)$v['id_usuario'] ?>">
                                    <?= htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select class="filtro-select" id="filtroFecha">
                            <option value="hoy">Hoy</option>
                            <option value="manana">Mañana</option>
                            <option value="semana">Esta Semana</option>
                            <option value="mes" selected>Este Mes</option>
                        </select>
                    </div>

                    <div class="tabs-container" role="tablist">
                        <button class="tab-btn active" data-tab="todas">
                            <i class="bi bi-grid-3x3-gap-fill"></i> Todas
                        </button>
                        <button class="tab-btn" data-tab="pendientes">
                            <i class="bi bi-clock-history"></i> Pendientes
                        </button>
                        <button class="tab-btn" data-tab="confirmadas">
                            <i class="bi bi-check-circle"></i> Confirmadas
                        </button>
                        <button class="tab-btn" data-tab="completadas">
                            <i class="bi bi-check-circle-fill"></i> Completadas
                        </button>
                    </div>
                </div>

                <!-- ══ LISTA DE CITAS ══ -->
                <div class="citas-lista" id="citasLista">
                    <?php if (empty($citas)): ?>
                        <div class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <h3>Sin citas registradas</h3>
                            <p>No hay citas en el sistema. Crea una nueva con el botón de arriba.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($citas as $cita):
                            $estado = $cita['estado_normalizado'];
                            $hora   = date('H:i', strtotime($cita['fecha_hora']));
                            $fecha  = date('d M Y', strtotime($cita['fecha_hora']));
                            $img    = citaAvatar($cita['img_mascota'] ?? '', BASE_URL);
                            $label  = citaBadgeLabel($estado);
                            $especie = strtolower($cita['mascota_especie'] ?? '');
                            $iconEspecie = in_array($especie, ['gato', 'felino', 'cat']) ? 'bi-heart-fill' : 'bi-paw-fill';
                        ?>
                        <div class="cita-card<?= $estado === 'cancelada' ? ' urgente' : '' ?>"
                             data-estado="<?= $estado ?>"
                             data-id-agendamiento="<?= (int)$cita['id_agendamiento'] ?>">

                            <div class="cita-tiempo <?= $estado ?>">
                                <span class="hora"><?= htmlspecialchars($hora) ?></span>
                                <span class="fecha"><?= htmlspecialchars($fecha) ?></span>
                            </div>

                            <div class="cita-info">
                                <div class="cita-header">
                                    <img src="<?= $img ?>"
                                         alt="<?= htmlspecialchars($cita['mascota_nombre']) ?>"
                                         class="paciente-avatar"
                                         onerror="this.src='<?= htmlspecialchars(BASE_URL . '/public/assets/webSite/img/perrito.png') ?>'">
                                    <div>
                                        <h4 class="paciente-nombre">
                                            <?= htmlspecialchars($cita['mascota_nombre']) ?>
                                            — <?= htmlspecialchars($cita['mascota_raza'] ?? $cita['mascota_especie']) ?>
                                        </h4>
                                        <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                                            <span class="tipo-mascota">
                                                <i class="bi <?= $iconEspecie ?>"></i>
                                                <?= htmlspecialchars($cita['mascota_especie']) ?>
                                            </span>
                                            <span class="estado-badge <?= $estado ?>"><?= $label ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="cita-detalles">
                                    <div class="detalle-item">
                                        <i class="bi bi-person-fill"></i>
                                        <span><?= htmlspecialchars($cita['propietario_nombre']) ?></span>
                                    </div>
                                    <div class="detalle-item">
                                        <i class="bi bi-telephone-fill"></i>
                                        <span><?= htmlspecialchars($cita['propietario_telefono'] ?? '—') ?></span>
                                    </div>
                                    <div class="detalle-item">
                                        <i class="bi bi-person-badge-fill"></i>
                                        <span><?= htmlspecialchars($cita['veterinario_nombre']) ?></span>
                                    </div>
                                    <div class="detalle-item">
                                        <i class="bi bi-clipboard-pulse-fill"></i>
                                        <span><?= htmlspecialchars($cita['servicio_nombre'] ?? $cita['tipo'] ?? '—') ?></span>
                                    </div>
                                </div>

                                <div class="cita-acciones">
                                    <?= citaBotonesHTML($estado) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div><!-- /#citasLista -->

            </section>
        </div><!-- /.area-contenido -->
    </div><!-- /.contenido-principal -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>window.BASE_URL = "<?= BASE_URL ?>";</script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/citas.js"></script>
</body>
</html>
