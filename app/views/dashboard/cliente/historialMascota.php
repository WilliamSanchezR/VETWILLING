<?php
/**
 * historialMascota.php – VetWilling
 * RFS 27 – Vista de historial médico con control de acceso temporal
 */
require_once BASE_PATH . '/app/helpers/session_propietario.php';
require_once BASE_PATH . '/app/models/CitasCliente.php';
require_once BASE_PATH . '/app/models/AccesoHistorial.php';

$id_mascota = (int)($_GET['id'] ?? 0);
if ($id_mascota <= 0) {
    header('Location: ' . BASE_URL . '/cliente/mascotas');
    exit;
}

$_cm            = new CitasCliente();
$_id_propietario = $_cm->obtenerIdPropietarioPorUsuario($_SESSION['user']['id_usuario']);

if (!$_id_propietario) {
    header('Location: ' . BASE_URL . '/cliente/mascotas');
    exit;
}

$mascota = $_cm->obtenerInfoPacienteConPropiedad($_id_propietario, $id_mascota);
if (!$mascota) {
    header('Location: ' . BASE_URL . '/cliente/mascotas');
    exit;
}

/* ── Control de acceso al historial clínico ── */
$_accesoModel  = new AccesoHistorial();
$accesoInfo    = $_accesoModel->estadoAcceso($id_mascota, $_id_propietario);
// Forzar acceso directo al historial clínico sin solicitar permiso
$tieneAcceso   = true;
$accesoInfo['estado'] = 'aprobado';

function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico - <?= e($mascota['nombre']) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/historialM.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    <style>
        /* ── Bloqueo de sección ── */
        .seccion-bloqueada {
            position: relative;
            min-height: 200px;
        }
        .seccion-bloqueada .bloqueo-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(4px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            border-radius: 8px;
            z-index: 10;
        }
        .bloqueo-overlay i { font-size: 2.5rem; color: #6c757d; }
        .bloqueo-overlay p { color: #495057; font-size: .95rem; text-align: center; max-width: 340px; margin: 0; }

        /* ── Cuenta regresiva ── */
        .acceso-timer {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .85rem;
            color: #198754;
            font-weight: 500;
        }
        .acceso-timer.expira-pronto { color: #dc3545; }

        /* ── Badge de estado ── */
        .estado-acceso-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <main class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <div class="container-fluid py-4">

                <!-- HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h2 class="mb-1">
                            <i class="bi bi-heart-pulse-fill text-primary me-2"></i>Historial Médico
                        </h2>
                        <p class="text-muted mb-0">Información completa del paciente</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <?php if ($tieneAcceso): ?>
                            <span class="acceso-timer" id="countdownTimer"
                                  data-expira="<?= e($accesoInfo['fecha_expiracion']) ?>">
                                <i class="bi bi-clock"></i>
                                <span id="timerTexto">Calculando...</span>
                            </span>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/cliente/mascotas" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                        <?php if ($tieneAcceso): ?>
                            <button class="btn btn-danger btn-sm" onclick="generarPDF()">
                                <i class="bi bi-file-pdf me-1"></i>PDF
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- INFO MASCOTA -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center mb-3 mb-md-0">
                                <img src="<?= BASE_URL ?>/public/uploads/mascotas/<?= e($mascota['img_mascota'] ?? 'default.png') ?>"
                                     alt="<?= e($mascota['nombre']) ?>"
                                     onerror="this.src='<?= BASE_URL ?>/public/assets/webSite/img/default-pet.png'"
                                     style="width:90px;height:90px;border-radius:50%;object-fit:cover;">
                            </div>
                            <div class="col-md-10">
                                <div class="row g-2">
                                    <?php
                                    $campos = [
                                        'Nombre'   => $mascota['nombre'],
                                        'Especie'  => $mascota['especie'],
                                        'Raza'     => $mascota['raza'],
                                        'Edad'     => $mascota['edad_numero'] . ' ' . $mascota['edad_unidad'],
                                        'Sexo'     => $mascota['sexo'],
                                        'Peso'     => !empty($mascota['peso']) ? $mascota['peso'] . ' kg' : null,
                                    ];
                                    foreach ($campos as $label => $valor): ?>
                                        <div class="col-6 col-md-2">
                                            <label class="text-muted small d-block"><?= e($label) ?></label>
                                            <p class="fw-bold mb-0">
                                                <?= $valor ? e($valor) : '<span class="text-muted fw-normal">—</span>' ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="col-6 col-md-2">
                                        <label class="text-muted small d-block">Estado de Salud</label>
                                        <?php
                                        $es = $mascota['estado_salud'] ?? '';
                                        $bg = match($es) { 'Bueno' => 'success', 'Regular' => 'warning', 'Delicado' => 'danger', default => 'secondary' };
                                        echo $es
                                            ? '<span class="badge bg-' . $bg . '">' . e($es) . '</span>'
                                            : '<span class="text-muted small">—</span>';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABS -->
                <ul class="nav nav-tabs mb-4" id="historialTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabCitas">
                            <i class="bi bi-calendar2-check me-1"></i>Citas
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabHistorial">
                            <i class="bi bi-clipboard2-pulse me-1"></i>Historial Clínico
                            <?php if (!$tieneAcceso): ?>
                                <i class="bi bi-lock-fill text-warning ms-1" title="Requiere permiso"></i>
                            <?php else: ?>
                                <i class="bi bi-unlock-fill text-success ms-1"></i>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabVacunas">
                            <i class="bi bi-shield-plus me-1"></i>Vacunas
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabTratamientos">
                            <i class="bi bi-capsule me-1"></i>Tratamientos
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCuidados">
                            <i class="bi bi-heart me-1"></i>Cuidados
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- TAB CITAS (libre, no requiere permiso) -->
                    <div class="tab-pane fade show active" id="tabCitas">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div id="loadingCitas" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">Cargando citas...</p>
                                </div>
                                <div class="table-responsive d-none" id="wrapperCitas">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr><th>Fecha</th><th>Servicio</th><th>Tipo</th><th>Estado</th><th>Observaciones</th></tr>
                                        </thead>
                                        <tbody id="bodyCitas"></tbody>
                                    </table>
                                </div>
                                <div id="emptyCitas" class="text-center py-4 d-none">
                                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No hay citas registradas.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB HISTORIAL CLÍNICO (requiere permiso) -->
                    <div class="tab-pane fade" id="tabHistorial">
                        <div class="card shadow-sm">
                            <div class="card-body seccion-bloqueada" id="contenedorHistorial">

                                <?php if (!$tieneAcceso): ?>
                                <!-- ── OVERLAY DE BLOQUEO ── -->
                                <div class="bloqueo-overlay" id="overlayHistorial">
                                    <i class="bi bi-lock-fill"></i>
                                    <p>
                                        <?php if ($accesoInfo['estado'] === 'pendiente'): ?>
                                            <strong>Solicitud pendiente.</strong><br>
                                            Tu solicitud fue enviada. El veterinario la revisará pronto.
                                        <?php elseif ($accesoInfo['estado'] === 'rechazado'): ?>
                                            <strong>Solicitud rechazada.</strong><br>
                                            <?= $accesoInfo['motivo_rechazo'] ? 'Motivo: ' . e($accesoInfo['motivo_rechazo']) : 'Contacta a tu veterinario para más información.' ?>
                                        <?php elseif ($accesoInfo['estado'] === 'expirado'): ?>
                                            <strong>El acceso ha expirado.</strong><br>
                                            Puedes solicitar un nuevo acceso al veterinario.
                                        <?php else: ?>
                                            <strong>Sección restringida.</strong><br>
                                            El historial clínico requiere autorización del veterinario.
                                        <?php endif; ?>
                                    </p>

                                    <?php if (in_array($accesoInfo['estado'], ['sin_solicitud', 'rechazado', 'expirado'])): ?>
                                        <button class="btn btn-primary btn-sm" id="btnSolicitarAcceso"
                                                onclick="solicitarAcceso(<?= (int)$mascota['id_paciente'] ?>)">
                                            <i class="bi bi-send me-1"></i>
                                            <?= $accesoInfo['estado'] === 'sin_solicitud' ? 'Solicitar acceso al historial' : 'Solicitar nuevo acceso' ?>
                                        </button>
                                    <?php elseif ($accesoInfo['estado'] === 'pendiente'): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-hourglass-split me-1"></i>En revisión
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <!-- Tabla (visible si tiene acceso, borrosa si no) -->
                                <div id="loadingHistorial" class="text-center py-4 <?= $tieneAcceso ? '' : 'd-none' ?>">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">Cargando historial clínico...</p>
                                </div>
                                <div class="table-responsive d-none" id="wrapperHistorial">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr><th>Fecha</th><th>Motivo</th><th>Diagnóstico</th><th>Tratamientos</th><th>Profesional</th></tr>
                                        </thead>
                                        <tbody id="bodyHistorial"></tbody>
                                    </table>
                                </div>
                                <div id="emptyHistorial" class="text-center py-4 d-none">
                                    <i class="bi bi-file-medical fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No hay historial clínico registrado.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB VACUNAS (libre) -->
                    <div class="tab-pane fade" id="tabVacunas">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div id="loadingVacunas" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </div>
                                <div class="table-responsive d-none" id="wrapperVacunas">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr><th>Vacuna</th><th>Dosis</th><th>Fecha</th><th>Profesional</th><th>Observaciones</th></tr>
                                        </thead>
                                        <tbody id="bodyVacunas"></tbody>
                                    </table>
                                </div>
                                <div id="emptyVacunas" class="text-center py-4 d-none">
                                    <i class="bi bi-shield-x fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No hay vacunas registradas.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB TRATAMIENTOS (libre) -->
                    <div class="tab-pane fade" id="tabTratamientos">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div id="loadingTratamientos" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </div>
                                <div class="table-responsive d-none" id="wrapperTratamientos">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr><th>Medicamento</th><th>Dosis</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Profesional</th></tr>
                                        </thead>
                                        <tbody id="bodyTratamientos"></tbody>
                                    </table>
                                </div>
                                <div id="emptyTratamientos" class="text-center py-4 d-none">
                                    <i class="bi bi-capsule fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No hay tratamientos registrados.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB CUIDADOS (libre) -->
                    <div class="tab-pane fade" id="tabCuidados">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php
                                    $cuidados = [
                                        ['bi-speedometer2',  'Peso actual',             !empty($mascota['peso']) ? $mascota['peso'] . ' kg' : null],
                                        ['bi-activity',      'Estado de salud',          $mascota['estado_salud'] ?? null],
                                        ['bi-calendar-check','Última desparasitación',   !empty($mascota['fecha_ultima_desparasitacion'])
                                            ? date('d/m/Y', strtotime($mascota['fecha_ultima_desparasitacion'])) : null],
                                    ];
                                    foreach ($cuidados as [$icon, $label, $valor]): ?>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <label class="text-muted small d-block mb-1">
                                                    <i class="bi <?= e($icon) ?> me-1"></i><?= e($label) ?>
                                                </label>
                                                <p class="fw-bold fs-5 mb-0">
                                                    <?= $valor ? e($valor) : '<span class="text-muted fs-6">No registrado</span>' ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    Para actualizar los datos de salud, contacta a tu veterinario o visita
                                    <a href="<?= BASE_URL ?>/cliente/mascotas" class="alert-link">Mis Mascotas</a>.
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /tab-content -->
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>

    <script>
    /* ── Variables globales ── */
    const BASE_URL    = '<?= BASE_URL ?>';
    const ID_PACIENTE = <?= (int)$mascota['id_paciente'] ?>;
    const TIENE_ACCESO= <?= $tieneAcceso ? 'true' : 'false' ?>;
    const ACCESO_INFO = <?= json_encode($accesoInfo, JSON_HEX_TAG) ?>;
    let FICHA_DATA = null;

    /* ── Helpers ── */
    function esc(str) {
        const d = document.createElement('div');
        d.textContent = (str ?? '');
        return d.innerHTML;
    }
    function fmtFecha(iso) {
        if (!iso) return '<span class="text-muted">—</span>';
        const d = new Date(iso.replace(' ','T'));
        return d.toLocaleDateString('es-CO',{day:'2-digit',month:'2-digit',year:'numeric'})
             + ' ' + d.toLocaleTimeString('es-CO',{hour:'2-digit',minute:'2-digit'});
    }
    function fmtFechaSolo(iso) {
        if (!iso) return '<span class="text-muted">—</span>';
        const [y,m,d] = iso.split('-');
        return `${d}/${m}/${y}`;
    }
    function badge(estado) {
        const map = {Pendiente:'warning',Confirmada:'primary',Realizada:'success',
                     Cancelada:'danger',Activo:'success',Completado:'secondary',Inactivo:'secondary'};
        return `<span class="badge bg-${map[estado]||'secondary'}">${esc(estado)}</span>`;
    }
    function mostrarTabla(wId, lId, eId, bId, filas) {
        document.getElementById(lId).classList.add('d-none');
        if (!filas || !filas.length) {
            document.getElementById(eId).classList.remove('d-none');
        } else {
            document.getElementById(wId).classList.remove('d-none');
            document.getElementById(bId).innerHTML = filas;
        }
    }

    /* ── Oculta todos los spinners y muestra estado vacío (error) ── */
    function resolverCargaConError() {
        ['wrapperCitas','wrapperHistorial','wrapperVacunas','wrapperTratamientos'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });
        const spinners = ['loadingCitas','loadingHistorial','loadingVacunas','loadingTratamientos'];
        spinners.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });
        ['emptyCitas','emptyVacunas','emptyTratamientos'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('d-none');
        });
        if (TIENE_ACCESO) {
            const emptyH = document.getElementById('emptyHistorial');
            if (emptyH) emptyH.classList.remove('d-none');
        }
    }

    /* ── Carga de datos ── */
    async function cargarFicha() {
        const url = `${BASE_URL}/cliente/api/citas/listar?accion=ficha_paciente&id_paciente=${ID_PACIENTE}`;
        try {
            const res  = await fetch(url);
            const data = await res.json();
            if (data.status !== 'success') {
                console.warn('API ficha_paciente devolvió error:', data);
                resolverCargaConError();
                return;
            }

            /* Citas */
            mostrarTabla('wrapperCitas','loadingCitas','emptyCitas','bodyCitas',
                (data.citas||[]).map(c=>`<tr>
                    <td>${fmtFecha(c.fecha_hora)}</td>
                    <td>${esc(c.servicio_nombre||c.tipo)}</td>
                    <td>${esc(c.tipo)}</td>
                    <td>${badge(c.estado)}</td>
                    <td class="text-truncate" style="max-width:200px">${esc(c.observaciones)||'<span class="text-muted">—</span>'}</td>
                </tr>`).join(''));

            /* Historial clínico — solo si tiene acceso */
            if (TIENE_ACCESO) {
                mostrarTabla('wrapperHistorial','loadingHistorial','emptyHistorial','bodyHistorial',
                    (data.historial_clinico||[]).map(h=>`<tr>
                        <td>${fmtFecha(h.fecha_atencion)}</td>
                        <td>${esc(h.motivo_consulta)}</td>
                        <td>${esc(h.diagnostico)||'<span class="text-muted">—</span>'}</td>
                        <td>${esc(h.tratamientos_aplicados)||'<span class="text-muted">—</span>'}</td>
                        <td>${esc(h.profesional_nombre)}</td>
                    </tr>`).join(''));
            }

            /* Vacunas */
            mostrarTabla('wrapperVacunas','loadingVacunas','emptyVacunas','bodyVacunas',
                (data.vacunas||[]).map(v=>`<tr>
                    <td>${esc(v.tipo_vacuna)}</td><td>${esc(v.dosis)}</td>
                    <td>${fmtFechaSolo(v.fecha_aplicacion)}</td>
                    <td>${esc(v.profesional_nombre)}</td>
                    <td>${esc(v.observaciones)||'<span class="text-muted">—</span>'}</td>
                </tr>`).join(''));

            /* Tratamientos */
            mostrarTabla('wrapperTratamientos','loadingTratamientos','emptyTratamientos','bodyTratamientos',
                (data.tratamientos||[]).map(t=>`<tr>
                    <td>${esc(t.medicamento)}</td><td>${esc(t.dosis)}</td>
                    <td>${fmtFechaSolo(t.fecha_inicio)}</td><td>${fmtFechaSolo(t.fecha_fin)}</td>
                    <td>${badge(t.estado)}</td><td>${esc(t.profesional_nombre)}</td>
                </tr>`).join(''));

            FICHA_DATA = data;

        } catch(e) {
            console.error('Error cargando ficha:', e);
            resolverCargaConError();
        }
    }

    /* ── Solicitar acceso ── */
    async function solicitarAcceso(id_paciente) {
        const btn = document.getElementById('btnSolicitarAcceso');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Enviando...'; }

        try {
            const res  = await fetch(`${BASE_URL}/cliente/api/historial/acceso/solicitar`, {
                method : 'POST',
                headers: {'Content-Type':'application/json'},
                body   : JSON.stringify({id_paciente}),
            });
            const data = await res.json();

            if (data.status === 'ok') {
                /* Reemplazar overlay con mensaje de pendiente */
                const overlay = document.getElementById('overlayHistorial');
                if (overlay) {
                    overlay.innerHTML = `
                        <i class="bi bi-hourglass-split" style="font-size:2.5rem;color:#ffc107"></i>
                        <p><strong>Solicitud enviada.</strong><br>
                           El veterinario recibirá una notificación y revisará tu solicitud pronto.</p>
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-clock me-1"></i>En revisión
                        </span>`;
                }
            } else if (data.code === 'ya_existe') {
                alert('Ya tienes una solicitud pendiente o un acceso activo.');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-send me-1"></i>Solicitar acceso'; }
            } else {
                alert('No se pudo enviar la solicitud. Intenta de nuevo.');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-send me-1"></i>Solicitar acceso'; }
            }
        } catch(e) {
            alert('Error de conexión. Verifica tu internet.');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-send me-1"></i>Solicitar acceso'; }
        }
    }

    /* ── Cuenta regresiva de acceso ── */
    function iniciarCuentaRegresiva() {
        const timerEl = document.getElementById('countdownTimer');
        const textoEl = document.getElementById('timerTexto');
        if (!timerEl || !textoEl) return;

        const expira = new Date(timerEl.dataset.expira.replace(' ','T'));

        function actualizar() {
            const diff = expira - Date.now();
            if (diff <= 0) {
                /* Acceso expirado: recargar para mostrar overlay */
                location.reload();
                return;
            }
            const h  = Math.floor(diff / 3600000);
            const m  = Math.floor((diff % 3600000) / 60000);
            const s  = Math.floor((diff % 60000) / 1000);
            textoEl.textContent = `Acceso expira en ${h}h ${m}m ${s}s`;

            /* Cambiar a rojo si quedan menos de 30 min */
            timerEl.classList.toggle('expira-pronto', diff < 1800000);
        }

        actualizar();
        setInterval(actualizar, 1000);
    }

/* ── Generar PDF ── */
    /* ── Convierte una imagen a base64, recortada en círculo o esquinas redondeadas ── */
    function cargarImagenBase64(url, size = 128, forma = 'redondeado', radio = 14) {
        return new Promise((resolve) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = size;
                    canvas.height = size;
                    const ctx = canvas.getContext('2d');

                    // Cubrir el cuadro manteniendo proporción (como background-size: cover)
                    const escala = Math.max(size / img.naturalWidth, size / img.naturalHeight);
                    const w = img.naturalWidth * escala;
                    const h = img.naturalHeight * escala;
                    const dx = (size - w) / 2;
                    const dy = (size - h) / 2;

                    // Definir la máscara de recorte
                    ctx.save();
                    ctx.beginPath();
                    if (forma === 'circulo') {
                        ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
                    } else {
                        const r = radio;
                        ctx.moveTo(r, 0);
                        ctx.arcTo(size, 0, size, size, r);
                        ctx.arcTo(size, size, 0, size, r);
                        ctx.arcTo(0, size, 0, 0, r);
                        ctx.arcTo(0, 0, size, 0, r);
                    }
                    ctx.closePath();
                    ctx.clip();

                    ctx.drawImage(img, dx, dy, w, h);
                    ctx.restore();

                    resolve(canvas.toDataURL('image/png'));
                } catch (e) {
                    resolve(null);
                }
            };
            img.onerror = () => resolve(null);
            img.src = url;
        });
    }

    async function generarPDF() {
        if (!TIENE_ACCESO) {
            alert('Necesitas permiso del veterinario para descargar el PDF del historial clínico.');
            return;
        }
        if (!FICHA_DATA) {
            alert('Espera a que se cargue la información antes de exportar el PDF.');
            return;
        }

        const btnPdf = document.querySelector('[onclick="generarPDF()"]');
        if (btnPdf) { btnPdf.disabled = true; btnPdf.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generando...'; }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ unit: 'pt', format: 'letter' });
        const paciente = FICHA_DATA.paciente || {};
        const pageW = doc.internal.pageSize.getWidth();
        const pageH = doc.internal.pageSize.getHeight();
        const margin = 48;

        /* ── Paleta clínica ── */
        const azulPrincipal = [13, 94, 138];   // header / marca
        const azulOscuro    = [8, 56, 84];     // títulos
        const tealAcento    = [16, 140, 120];  // citas
        const moradoSalud   = [99, 78, 168];   // historial clínico
        const verdeVacunas  = [56, 142, 60];   // vacunas
        const naranjaTrat   = [196, 110, 18];  // tratamientos
        const grisTexto     = [55, 58, 56];
        const grisClaro     = [120, 122, 119];
        const fondoTarjeta  = [244, 247, 249];
        const bordeSutil    = [220, 226, 230];

        const seccionColor = { 1: tealAcento, 2: moradoSalud, 3: verdeVacunas, 4: naranjaTrat };

        let y = 0;
        let pageNum = 1;

        /* ── Encabezado ── */
        function dibujarEncabezado(esPrimera) {
            const altoHeader = esPrimera ? 96 : 58;
            doc.setFillColor(...azulPrincipal);
            doc.rect(0, 0, pageW, altoHeader, 'F');
            doc.setFillColor(...azulOscuro);
            doc.rect(0, altoHeader - 3, pageW, 3, 'F');

            if (esPrimera) {
                doc.setTextColor(255, 255, 255);
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(20);
                doc.text('Historial médico', margin, 40);

                doc.setFont('helvetica', 'normal');
                doc.setFontSize(10);
                doc.setTextColor(220, 235, 242);
                doc.text('VetWilling · Sistema de gestión veterinaria', margin, 58);

                doc.setFontSize(9);
                const fechaGen = `Generado el ${new Date().toLocaleDateString('es-CO', { day: '2-digit', month: 'long', year: 'numeric' })}`;
                doc.text(fechaGen, pageW - margin, 58, { align: 'right' });
            } else {
                doc.setTextColor(255, 255, 255);
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(12);
                doc.text(`Historial médico — ${paciente.nombre || 'Paciente'}`, margin, altoHeader / 2 + 4);
            }
            return altoHeader + 24;
        }

        function dibujarPie() {
            const yPie = pageH - 36;
            doc.setDrawColor(...bordeSutil);
            doc.setLineWidth(0.5);
            doc.line(margin, yPie, pageW - margin, yPie);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            doc.setTextColor(...grisClaro);
            doc.text('Documento confidencial · Uso exclusivo del propietario registrado', margin, yPie + 14);
            doc.text(`Página ${pageNum}`, pageW - margin, yPie + 14, { align: 'right' });
        }

        doc.setProperties({ title: `Historial médico - ${paciente.nombre || 'mascota'}` });

        y = dibujarEncabezado(true);
        dibujarPie();

        /* ── Cargar foto de la mascota (recortada en canvas, sin afectar el estado gráfico del PDF) ── */
        const nombreImg = paciente.img_mascota || 'default.png';
        const urlImg = `${BASE_URL}/public/uploads/mascotas/${nombreImg}`;
        const imgRecortada = await cargarImagenBase64(urlImg, 128, 'redondeado', 20);

        /* ── Tarjeta de datos de la mascota ── */
        const altoTarjeta = 96;
        doc.setFillColor(...fondoTarjeta);
        doc.setDrawColor(...bordeSutil);
        doc.setLineWidth(0.5);
        doc.roundedRect(margin, y, pageW - margin * 2, altoTarjeta, 8, 8, 'FD');

        const fotoSize = 64;
        const fotoX = margin + 14;
        const fotoY = y + (altoTarjeta - fotoSize) / 2;

        if (imgRecortada) {
            doc.addImage(imgRecortada, 'PNG', fotoX, fotoY, fotoSize, fotoSize);
            doc.setDrawColor(...azulPrincipal);
            doc.setLineWidth(1);
            doc.roundedRect(fotoX, fotoY, fotoSize, fotoSize, 10, 10, 'S');
        } else {
            doc.setFillColor(...azulPrincipal);
            doc.roundedRect(fotoX, fotoY, fotoSize, fotoSize, 10, 10, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(22);
            doc.text((paciente.nombre || '?').charAt(0).toUpperCase(), fotoX + fotoSize / 2, fotoY + fotoSize / 2 + 8, { align: 'center' });
        }

        const colX = fotoX + fotoSize + 18;
        doc.setTextColor(...azulOscuro);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(15);
        doc.text(paciente.nombre || '—', colX, y + 28);

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9.5);
        doc.setTextColor(...grisClaro);
        doc.text(`${paciente.especie || '—'} · ${paciente.raza || '—'} · ${paciente.sexo || '—'}`, colX, y + 43);

        const datosGrid = [
            ['Edad', paciente.edad_numero ? `${paciente.edad_numero} ${paciente.edad_unidad || ''}` : '—'],
            ['Peso', paciente.peso ? `${paciente.peso} kg` : '—'],
            ['Estado de salud', paciente.estado_salud || '—'],
        ];
        let gx = colX;
        datosGrid.forEach(([label, valor]) => {
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            doc.setTextColor(...grisClaro);
            doc.text(label, gx, y + 66);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(10.5);
            doc.setTextColor(...grisTexto);
            doc.text(valor, gx, y + 80);
            gx += 145;
        });

        y += altoTarjeta + 28;

        /* ── Sección con tabla ── */
        function appendSection(numero, title, headers, rows) {
            if (!rows || !rows.length) return;
            const color = seccionColor[numero];

            if (y + 50 > pageH - 50) {
                doc.addPage();
                pageNum++;
                y = dibujarEncabezado(false);
                dibujarPie();
            }

            doc.setFillColor(...color);
            doc.circle(margin + 8, y + 4, 9, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(9);
            doc.text(String(numero), margin + 8, y + 7, { align: 'center' });

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(12.5);
            doc.setTextColor(...color);
            doc.text(title, margin + 24, y + 8);

            y += 20;

            doc.autoTable({
                head: [headers],
                body: rows,
                startY: y,
                theme: 'striped',
                styles: {
                    fontSize: 8.5,
                    textColor: grisTexto,
                    cellPadding: { top: 6, bottom: 6, left: 8, right: 8 },
                    lineColor: bordeSutil,
                    lineWidth: 0.5,
                },
                headStyles: {
                    fillColor: color,
                    textColor: [255, 255, 255],
                    fontStyle: 'bold',
                    fontSize: 8.5,
                },
                alternateRowStyles: { fillColor: fondoTarjeta },
                margin: { left: margin, right: margin, bottom: 50 },
                didDrawPage: () => {
                    if (doc.internal.getCurrentPageInfo().pageNumber > pageNum) {
                        pageNum = doc.internal.getCurrentPageInfo().pageNumber;
                        dibujarEncabezado(false);
                        dibujarPie();
                    }
                },
            });

            y = doc.lastAutoTable.finalY + 32;
        }

        appendSection(1, 'Citas', ['Fecha', 'Servicio', 'Tipo', 'Estado', 'Observaciones'],
            (FICHA_DATA.citas || []).map(c => [
                fmtFecha(c.fecha_hora), c.servicio_nombre || c.tipo || '—',
                c.tipo || '—', c.estado || '—', c.observaciones || '—',
            ])
        );

        appendSection(2, 'Historial clínico', ['Fecha', 'Motivo', 'Diagnóstico', 'Tratamientos', 'Profesional'],
            (FICHA_DATA.historial_clinico || []).map(h => [
                fmtFecha(h.fecha_atencion), h.motivo_consulta || '—',
                h.diagnostico || '—', h.tratamientos_aplicados || '—', h.profesional_nombre || '—',
            ])
        );

        appendSection(3, 'Vacunas', ['Vacuna', 'Dosis', 'Fecha', 'Profesional', 'Observaciones'],
            (FICHA_DATA.vacunas || []).map(v => [
                v.tipo_vacuna || '—', v.dosis || '—', fmtFechaSolo(v.fecha_aplicacion),
                v.profesional_nombre || '—', v.observaciones || '—',
            ])
        );

        appendSection(4, 'Tratamientos', ['Medicamento', 'Dosis', 'Inicio', 'Fin', 'Estado', 'Profesional'],
            (FICHA_DATA.tratamientos || []).map(t => [
                t.medicamento || '—', t.dosis || '—', fmtFechaSolo(t.fecha_inicio),
                fmtFechaSolo(t.fecha_fin), t.estado || '—', t.profesional_nombre || '—',
            ])
        );

        const filename = `historial_${(paciente.nombre || 'mascota').replace(/\s+/g, '_')}.pdf`;
        doc.save(filename);

        if (btnPdf) { btnPdf.disabled = false; btnPdf.innerHTML = '<i class="bi bi-file-pdf me-1"></i>PDF'; }
    }

    /* ── Init ── */
    document.addEventListener('DOMContentLoaded', () => {
        cargarFicha();
        if (TIENE_ACCESO) iniciarCuentaRegresiva();
    });
    </script>
</body>
</html>