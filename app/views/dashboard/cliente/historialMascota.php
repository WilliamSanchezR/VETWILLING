<?php
require_once BASE_PATH . '/app/controllers/mascotasController.php';

$id_mascota = $_GET['id'];
$mascota = consultarMascotaId($id_mascota);


?>
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
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tablaConsultas">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Motivo</th>
                                                <th>Veterinario</th>
                                                <th>Diagnóstico</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>15/12/2024</td>
                                                <td>Control general</td>
                                                <td>Dr. García López</td>
                                                <td>Saludable</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalDetalleConsulta">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>03/11/2024</td>
                                                <td>Vómitos frecuentes</td>
                                                <td>Dra. Martínez</td>
                                                <td>Gastritis leve</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>20/09/2024</td>
                                                <td>Vacunación anual</td>
                                                <td>Dr. García López</td>
                                                <td>N/A</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
                                                <th>Detalles</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>10/01/2023</td>
                                                <td>Castración</td>
                                                <td>Dr. Ramírez</td>
                                                <td>Procedimiento sin complicaciones</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </td>
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