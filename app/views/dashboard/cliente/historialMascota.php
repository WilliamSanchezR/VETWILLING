<?php
// ─────────────────────────────────────────────────────────────────
//  RFS 27 – CONSULTA DE INFORMACIÓN DEL ANIMAL
//  Subtareas 1/6: autenticación y autorización PRIMERO
// ─────────────────────────────────────────────────────────────────
require_once BASE_PATH . '/app/helpers/session_propietario.php';   // valida sesión y rol=3

// Subtareas 1/6: validar id_mascota en la URL
$id_mascota = (int)($_GET['id'] ?? 0);
if ($id_mascota <= 0) {
    header('Location: ' . BASE_URL . '/cliente/mascotas');
    exit();
}

// Subtareas 2/6: obtener propietario y verificar propiedad antes de consultar
require_once BASE_PATH . '/app/models/CitasCliente.php';
$_modeloAuth = new CitasCliente();
$_id_propietario = $_modeloAuth->obtenerIdPropietarioPorUsuario($_SESSION['user']['id_usuario']);

if (!$_id_propietario) {
    header('Location: ' . BASE_URL . '/cliente/mascotas');
    exit();
}

$mascota = $_modeloAuth->obtenerInfoPacienteConPropiedad($_id_propietario, $id_mascota);
if (!$mascota) {
    header('Location: ' . BASE_URL . '/cliente/mascotas');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico - <?= htmlspecialchars($mascota['nombre']) ?></title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/historialM.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">

    <!-- jsPDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

</head>

<body>

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <div class="container-fluid py-4">

                <!-- HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">
                            <i class="bi bi-heart-pulse-fill text-primary me-2"></i>Historial Médico
                        </h2>
                        <p class="text-muted mb-0">Información completa del paciente</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= BASE_URL ?>/cliente/mascotas" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                        <button class="btn btn-danger" onclick="generarPDF()">
                            <i class="bi bi-file-pdf me-1"></i>Descargar PDF
                        </button>
                    </div>
                </div>

                <!-- ── INFO MASCOTA (subtarea 2: campos completos de RFS 27/28) ── -->
                <div class="card shadow-sm mb-4" id="infoMascota">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center mb-3 mb-md-0">
                                <img src="<?= BASE_URL ?>/public/uploads/mascotas/<?= htmlspecialchars($mascota['img_mascota'] ?? 'default.png') ?>"
                                     alt="<?= htmlspecialchars($mascota['nombre']) ?>"
                                     onerror="this.src='<?= BASE_URL ?>/public/assets/webSite/img/default-pet.png'"
                                     style="width:90px;height:90px;border-radius:50%;object-fit:cover;">
                            </div>
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-6 col-md-3 mb-2">
                                        <label class="text-muted small">Nombre</label>
                                        <p class="fw-bold mb-0" id="nombre"><?= htmlspecialchars($mascota['nombre']) ?></p>
                                    </div>
                                    <div class="col-6 col-md-3 mb-2">
                                        <label class="text-muted small">Especie</label>
                                        <p class="fw-bold mb-0" id="especie"><?= htmlspecialchars($mascota['especie']) ?></p>
                                    </div>
                                    <div class="col-6 col-md-3 mb-2">
                                        <label class="text-muted small">Raza</label>
                                        <p class="fw-bold mb-0" id="raza"><?= htmlspecialchars($mascota['raza']) ?></p>
                                    </div>
                                    <div class="col-6 col-md-3 mb-2">
                                        <label class="text-muted small">Edad</label>
                                        <p class="fw-bold mb-0" id="edad"><?= htmlspecialchars($mascota['edad_numero'] . ' ' . $mascota['edad_unidad']) ?></p>
                                    </div>
                                    <div class="col-6 col-md-3 mb-2">
                                        <label class="text-muted small">Sexo</label>
                                        <p class="fw-bold mb-0" id="sexo"><?= htmlspecialchars($mascota['sexo']) ?></p>
                                    </div>
                                    <!-- Campos clínicos de RFS 28 -->
                                    <div class="col-6 col-md-3 mb-2">
                                        <label class="text-muted small">Peso</label>
                                        <p class="fw-bold mb-0" id="peso">
                                            <?= !empty($mascota['peso']) ? htmlspecialchars($mascota['peso']) . ' kg' : '<span class="text-muted">No registrado</span>' ?>
                                        </p>
                                    </div>
                                    <div class="col-6 col-md-3 mb-2">
                                        <label class="text-muted small">Estado de Salud</label>
                                        <p class="fw-bold mb-0" id="estadoSalud">
                                            <?php
                                                $estado_salud = $mascota['estado_salud'] ?? '';
                                                $badge = match($estado_salud) {
                                                    'Bueno'    => 'success',
                                                    'Regular'  => 'warning',
                                                    'Delicado' => 'danger',
                                                    default    => 'secondary'
                                                };
                                                echo $estado_salud
                                                    ? '<span class="badge bg-' . $badge . '">' . htmlspecialchars($estado_salud) . '</span>'
                                                    : '<span class="text-muted">No registrado</span>';
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-6 col-md-3 mb-2">
                                        <label class="text-muted small">Última Desparasitación</label>
                                        <p class="fw-bold mb-0" id="ultDesparasitacion">
                                            <?= !empty($mascota['fecha_ultima_desparasitacion'])
                                                ? htmlspecialchars(date('d/m/Y', strtotime($mascota['fecha_ultima_desparasitacion'])))
                                                : '<span class="text-muted">No registrada</span>' ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── TABS (subtareas 3, 4, 5, 7) ── -->
                <ul class="nav nav-tabs mb-4" id="historialTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabCitas" type="button">
                            <i class="bi bi-calendar2-check me-1"></i>Citas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabHistorial" type="button">
                            <i class="bi bi-clipboard2-pulse me-1"></i>Historial Clínico
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabVacunas" type="button">
                            <i class="bi bi-shield-plus me-1"></i>Vacunas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabTratamientos" type="button">
                            <i class="bi bi-capsule me-1"></i>Tratamientos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCuidados" type="button">
                            <i class="bi bi-heart me-1"></i>Cuidados
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="historialTabsContent">

                    <!-- ── TAB CITAS (subtarea 5) ── -->
                    <div class="tab-pane fade show active" id="tabCitas" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div id="loadingCitas" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">Cargando citas...</p>
                                </div>
                                <div class="table-responsive d-none" id="wrapperCitas">
                                    <table class="table table-hover align-middle" id="tablaCitas">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Servicio</th>
                                                <th>Tipo</th>
                                                <th>Estado</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bodyCitas"></tbody>
                                    </table>
                                </div>
                                <div id="emptyCitas" class="text-center py-4 d-none">
                                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No hay citas registradas para este paciente.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── TAB HISTORIAL CLÍNICO (subtarea 5) ── -->
                    <div class="tab-pane fade" id="tabHistorial" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div id="loadingHistorial" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">Cargando historial clínico...</p>
                                </div>
                                <div class="table-responsive d-none" id="wrapperHistorial">
                                    <table class="table table-hover align-middle" id="tablaHistorial">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Motivo</th>
                                                <th>Diagnóstico</th>
                                                <th>Tratamientos</th>
                                                <th>Profesional</th>
                                            </tr>
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

                    <!-- ── TAB VACUNAS (subtarea 3) ── -->
                    <div class="tab-pane fade" id="tabVacunas" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div id="loadingVacunas" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">Cargando vacunas...</p>
                                </div>
                                <div class="table-responsive d-none" id="wrapperVacunas">
                                    <table class="table table-hover align-middle" id="tablaVacunas">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Vacuna</th>
                                                <th>Dosis</th>
                                                <th>Fecha Aplicación</th>
                                                <th>Profesional</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bodyVacunas"></tbody>
                                    </table>
                                </div>
                                <div id="emptyVacunas" class="text-center py-4 d-none">
                                    <i class="bi bi-shield-x fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No hay vacunas registradas para este paciente.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── TAB TRATAMIENTOS (subtarea 5) ── -->
                    <div class="tab-pane fade" id="tabTratamientos" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div id="loadingTratamientos" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">Cargando tratamientos...</p>
                                </div>
                                <div class="table-responsive d-none" id="wrapperTratamientos">
                                    <table class="table table-hover align-middle" id="tablaTratamientos">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Medicamento</th>
                                                <th>Dosis</th>
                                                <th>Fecha Inicio</th>
                                                <th>Fecha Fin</th>
                                                <th>Estado</th>
                                                <th>Profesional</th>
                                            </tr>
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

                    <!-- ── TAB CUIDADOS GENERALES (subtarea 4) ── -->
                    <div class="tab-pane fade" id="tabCuidados" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">
                                    <i class="bi bi-heart-fill text-danger me-2"></i>Información de Salud y Cuidados
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <label class="text-muted small d-block mb-1">
                                                <i class="bi bi-speedometer2 me-1"></i>Peso actual
                                            </label>
                                            <p class="fw-bold fs-5 mb-0">
                                                <?= !empty($mascota['peso'])
                                                    ? htmlspecialchars($mascota['peso']) . ' kg'
                                                    : '<span class="text-muted fs-6">No registrado</span>' ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <label class="text-muted small d-block mb-1">
                                                <i class="bi bi-activity me-1"></i>Estado de salud
                                            </label>
                                            <p class="fw-bold fs-5 mb-0">
                                                <?php
                                                    $es = $mascota['estado_salud'] ?? '';
                                                    $bg = match($es) { 'Bueno' => 'success', 'Regular' => 'warning', 'Delicado' => 'danger', default => 'secondary' };
                                                    echo $es ? '<span class="badge bg-' . $bg . ' fs-6">' . htmlspecialchars($es) . '</span>'
                                                             : '<span class="text-muted fs-6">No registrado</span>';
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <label class="text-muted small d-block mb-1">
                                                <i class="bi bi-calendar-check me-1"></i>Última desparasitación
                                            </label>
                                            <p class="fw-bold fs-5 mb-0">
                                                <?= !empty($mascota['fecha_ultima_desparasitacion'])
                                                    ? htmlspecialchars(date('d/m/Y', strtotime($mascota['fecha_ultima_desparasitacion'])))
                                                    : '<span class="text-muted fs-6">No registrada</span>' ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    Para actualizar los datos de salud y cuidados generales, contacte a su veterinario o visite la sección de
                                    <a href="<?= BASE_URL ?>/cliente/mascotas" class="alert-link">Mis Mascotas</a>.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/historial.js"></script>

    <script>
    /* =========================================================
       RFS 27 – Carga de ficha completa del paciente vía AJAX
       Subtareas 5/7: datos reales + carga asíncrona
    ========================================================= */
    const ID_PACIENTE = <?= (int)$mascota['id_paciente'] ?>;
    const URL_FICHA   = '<?= BASE_URL ?>/cliente/api/citas/listar?accion=ficha_paciente&id_paciente=' + ID_PACIENTE;

    function esc(str) {
        const d = document.createElement('div');
        d.textContent = (str === null || str === undefined) ? '' : String(str);
        return d.innerHTML;
    }

    function fmtFecha(iso) {
        if (!iso) return '<span class="text-muted">—</span>';
        const d = new Date(iso.replace(' ', 'T'));
        return d.toLocaleDateString('es-CO', { day:'2-digit', month:'2-digit', year:'numeric' })
             + ' ' + d.toLocaleTimeString('es-CO', { hour:'2-digit', minute:'2-digit' });
    }

    function fmtFechaSolo(iso) {
        if (!iso) return '<span class="text-muted">—</span>';
        const [y, m, d2] = iso.split('-');
        return `${d2}/${m}/${y}`;
    }

    function estadoBadge(estado) {
        const map = {
            'Pendiente':   'warning',
            'Confirmada':  'primary',
            'Realizada':   'success',
            'Cancelada':   'danger',
            'Activo':      'success',
            'Completado':  'secondary',
            'Inactivo':    'secondary',
        };
        const cls = map[estado] || 'secondary';
        return `<span class="badge bg-${cls}">${esc(estado)}</span>`;
    }

    function mostrarTabla(wrapperId, loadingId, emptyId, bodyId, filas) {
        document.getElementById(loadingId).classList.add('d-none');
        if (!filas || filas.length === 0) {
            document.getElementById(emptyId).classList.remove('d-none');
        } else {
            document.getElementById(wrapperId).classList.remove('d-none');
            document.getElementById(bodyId).innerHTML = filas;
        }
    }

    async function cargarFicha() {
        try {
            const res  = await fetch(URL_FICHA);
            const data = await res.json();

            if (data.status !== 'success') {
                console.error('Error al cargar ficha:', data.message);
                return;
            }

            // ── Citas ──────────────────────────────────────────
            const filasCitas = (data.citas || []).map(c =>
                `<tr>
                    <td>${fmtFecha(c.fecha_hora)}</td>
                    <td>${esc(c.servicio_nombre || c.tipo)}</td>
                    <td>${esc(c.tipo)}</td>
                    <td>${estadoBadge(c.estado)}</td>
                    <td class="text-truncate" style="max-width:200px">${esc(c.observaciones) || '<span class="text-muted">—</span>'}</td>
                </tr>`
            ).join('');
            mostrarTabla('wrapperCitas', 'loadingCitas', 'emptyCitas', 'bodyCitas', filasCitas);

            // ── Historial Clínico ──────────────────────────────
            const filasHistorial = (data.historial_clinico || []).map(h =>
                `<tr>
                    <td>${fmtFecha(h.fecha_atencion)}</td>
                    <td>${esc(h.motivo_consulta)}</td>
                    <td>${esc(h.diagnostico) || '<span class="text-muted">—</span>'}</td>
                    <td>${esc(h.tratamientos_aplicados) || '<span class="text-muted">—</span>'}</td>
                    <td>${esc(h.profesional_nombre)}</td>
                </tr>`
            ).join('');
            mostrarTabla('wrapperHistorial', 'loadingHistorial', 'emptyHistorial', 'bodyHistorial', filasHistorial);

            // ── Vacunas ────────────────────────────────────────
            const filasVacunas = (data.vacunas || []).map(v =>
                `<tr>
                    <td>${esc(v.tipo_vacuna)}</td>
                    <td>${esc(v.dosis)}</td>
                    <td>${fmtFechaSolo(v.fecha_aplicacion)}</td>
                    <td>${esc(v.profesional_nombre)}</td>
                    <td>${esc(v.observaciones) || '<span class="text-muted">—</span>'}</td>
                </tr>`
            ).join('');
            mostrarTabla('wrapperVacunas', 'loadingVacunas', 'emptyVacunas', 'bodyVacunas', filasVacunas);

            // ── Tratamientos ───────────────────────────────────
            const filasTrat = (data.tratamientos || []).map(t =>
                `<tr>
                    <td>${esc(t.medicamento)}</td>
                    <td>${esc(t.dosis)}</td>
                    <td>${fmtFechaSolo(t.fecha_inicio)}</td>
                    <td>${fmtFechaSolo(t.fecha_fin)}</td>
                    <td>${estadoBadge(t.estado)}</td>
                    <td>${esc(t.profesional_nombre)}</td>
                </tr>`
            ).join('');
            mostrarTabla('wrapperTratamientos', 'loadingTratamientos', 'emptyTratamientos', 'bodyTratamientos', filasTrat);

        } catch (err) {
            console.error('Error cargando ficha del paciente:', err);
        }
    }

    document.addEventListener('DOMContentLoaded', cargarFicha);
    </script>

</body>
</html>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico - Dashboard VetCare</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/historialM.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">

    <!-- jsPDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

</head>

<body>

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <!-- DASHBOARD CONTENT -->
            <div class="container-fluid py-4">

                <!-- HEADER CON BOTÓN PDF -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1"><i class="bi bi-heart-pulse-fill text-primary me-2"></i>Historial Médico</h2>
                        <p class="text-muted mb-0">Información completa del paciente</p>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="generarPDF()">
                            <i class="bi bi-file-pdf me-2"></i>Descargar PDF
                        </button>
                    </div>
                </div>

                <!-- INFO MASCOTA -->
                <div class="card shadow-sm mb-4" id="infoMascota">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <img src="<?= BASE_URL ?>/public/uploads/mascotas/<?= $mascota['img_mascota'] ?>"
                                        alt="<?= htmlspecialchars($mascota['nombre']) ?>"
                                        style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover;">
                            </div>
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="text-muted small">Nombre</label>
                                        <p class="fw-bold mb-2" id="nombre"><?= ($mascota['nombre']) ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="text-muted small">Especie</label>
                                        <p class="fw-bold mb-2" id="especie"><?= ($mascota['especie']) ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="text-muted small">Raza</label>
                                        <p class="fw-bold mb-2" id="raza"><?= ($mascota['raza']) ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="text-muted small">Edad</label>
                                        <p class="fw-bold mb-2" id="edad"><?= ($mascota['edad_numero']) ?> <?= ($mascota['edad_unidad']) ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="text-muted small">Sexo</label>
                                        <p class="fw-bold mb-2" id="sexo"><?= ($mascota['sexo']) ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="text-muted small">Microchip</label>
                                        <p class="fw-bold mb-2" id="microchip">982000123456789</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABS DE NAVEGACIÓN -->
                <ul class="nav nav-tabs mb-4" id="historialTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="consultas-tab" data-bs-toggle="tab" data-bs-target="#consultas" type="button">
                            <i class="bi bi-clipboard2-pulse me-2"></i>Consultas
                        </button>
                    </li>
                    
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="medicamentos-tab" data-bs-toggle="tab" data-bs-target="#medicamentos" type="button">
                            <i class="bi bi-capsule me-2"></i>Medicamentos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="examenes-tab" data-bs-toggle="tab" data-bs-target="#examenes" type="button">
                            <i class="bi bi-file-medical me-2"></i>Exámenes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="cirugias-tab" data-bs-toggle="tab" data-bs-target="#cirugias" type="button">
                            <i class="bi bi-scissors me-2"></i>Cirugías
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="alergias-tab" data-bs-toggle="tab" data-bs-target="#alergias" type="button">
                            <i class="bi bi-exclamation-triangle me-2"></i>Alergias
                        </button>
                    </li>
                </ul>

                <!-- CONTENIDO DE TABS -->
                <div class="tab-content" id="historialTabsContent">

                    <!-- TAB CONSULTAS -->
                    <div class="tab-pane fade show active" id="consultas" role="tabpanel">
                        <div class="card shadow-sm">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSolicitarConsulta">Solicitar Consulta</button>
                        </div>
                    </div>

                    <!-- TAB MEDICAMENTOS -->
                    <div class="tab-pane fade" id="medicamentos" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tablaMedicamentos">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Medicamento</th>
                                                <th>Dosis</th>
                                                <th>Frecuencia</th>
                                                <th>Fecha Inicio</th>
                                                <th>Fecha Fin</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Omeprazol</td>
                                                <td>20 mg</td>
                                                <td>Cada 12 horas</td>
                                                <td>03/11/2024</td>
                                                <td>17/11/2024</td>
                                                <td><span class="badge bg-success">Completado</span></td>
                                            </tr>
                                            <tr>
                                                <td>Antiparasitario interno</td>
                                                <td>1 tableta</td>
                                                <td>Dosis única</td>
                                                <td>15/10/2024</td>
                                                <td>15/10/2024</td>
                                                <td><span class="badge bg-success">Completado</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB EXÁMENES -->
                    <div class="tab-pane fade" id="examenes" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tablaExamenes">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Tipo de Examen</th>
                                                <th>Resultados</th>
                                                <th>Veterinario</th>
                                                <th>Archivo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>15/12/2024</td>
                                                <td>Análisis de sangre completo</td>
                                                <td>Normal</td>
                                                <td>Dr. García López</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-download"></i> Descargar
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>05/11/2024</td>
                                                <td>Ecografía abdominal</td>
                                                <td>Ligera inflamación gástrica</td>
                                                <td>Dra. Martínez</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-download"></i> Descargar
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB CIRUGÍAS -->
                    <div class="tab-pane fade" id="cirugias" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tablaCirugias">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Procedimiento</th>
                                                <th>Veterinario</th>
                                                <th>Notas</th>
                                                <!-- <th>Detalles</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>10/01/2023</td>
                                                <td>Castración</td>
                                                <td>Dr. Ramírez</td>
                                                <td>Procedimiento sin complicaciones</td>
                                                <!-- <td>
                                                    <button class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </td> -->
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB ALERGIAS -->
                    <div class="tab-pane fade" id="alergias" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-body" id="alergiasContainer">
                                <div class="alert alert-warning" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Alergias conocidas:</strong>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border-danger mb-3">
                                            <div class="card-body">
                                                <h5 class="card-title text-danger">
                                                    <i class="bi bi-exclamation-octagon-fill me-2"></i>Penicilina
                                                </h5>
                                                <p class="card-text">Reacción severa detectada en enero 2023</p>
                                                <p class="text-muted small mb-0"><strong>Síntomas:</strong> Urticaria, dificultad respiratoria</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle-fill me-2"></i>
                                            No se han registrado alergias alimentarias.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- MODALES (sin cambios) -->
            <div class="modal fade" id="modalDetalleConsulta" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-clipboard2-pulse me-2"></i>Detalle de Consulta</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">Fecha</label>
                                    <p class="fw-bold">15 de Diciembre, 2024</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Veterinario</label>
                                    <p class="fw-bold">Dr. García López</p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Motivo de Consulta</label>
                                <p>Control general y revisión anual</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Síntomas</label>
                                <p>Ninguno. Mascota en buen estado general.</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Diagnóstico</label>
                                <p>Paciente saludable. Todos los parámetros dentro de rangos normales.</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Tratamiento</label>
                                <p>No requiere tratamiento. Se recomienda continuar con dieta balanceada y ejercicio regular.</p>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="text-muted small">Peso</label>
                                    <p class="fw-bold">28 kg</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small">Temperatura</label>
                                    <p class="fw-bold">38.5°C</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small">Frecuencia Cardíaca</label>
                                    <p class="fw-bold">95 lpm</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/historial.js"></script>
    
</body>

</html>