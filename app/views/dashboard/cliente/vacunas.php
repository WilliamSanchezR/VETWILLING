<?php
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
require_once BASE_PATH . '/app/controllers/mascotasController.php';

$id_mascota = $_GET['id'];
$mascota = consultarMascotaId($id_mascota);

?>
<!DOCTYPE html>
<html lang="<?= $prefs['idioma'] === 'pt' ? 'pt-BR' : $prefs['idioma'] ?>">

<head>
    <meta charset="UTF-8">
    <script>(function(){var p=<?= json_encode($prefs) ?>;document.documentElement.setAttribute('data-tema',p.tema);if(p.tema==='oscuro'&&document.body)document.body.classList.add('dark-theme');}());</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico - Dashboard VetCare</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/vacunas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">

    <!-- jsPDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

</head>

<body class="<?= $prefs['tema'] === 'oscuro' ? 'dark-theme' : '' ?>" data-tema="<?= $prefs['tema'] ?>">

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>
        <div class="area-contenido">
            <!-- Estadísticas Generales -->
            <div class="stats-grid">
                <div class="stat-card success">
                    <div class="stat-value" id="totalAplicadas">0</div>
                    <div class="stat-label">Vacunas Aplicadas</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-value" id="totalPendientes">0</div>
                    <div class="stat-label">Vacunas Pendientes</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-value" id="totalProximas">0</div>
                    <div class="stat-label">Próximas Vacunas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="costoTotal">$0</div>
                    <div class="stat-label">Inversión en Salud</div>
                </div>
            </div>

            <!-- Vacunas Aplicadas -->
            <div class="vaccine-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        Vacunas Aplicadas
                        <span class="section-badge" id="badgeAplicadas">0</span>
                    </div>
                    <button class="btn btn-custom btn-export" onclick="exportarPDF('aplicadas')">
                        <i class="bi bi-download me-2"></i>Exportar PDF
                    </button>
                </div>
                <div id="vacunasAplicadas">
                    <div class="empty-state">
                        <div class="empty-icon">✅</div>
                        <h4>No hay vacunas aplicadas</h4>
                        <p>Las vacunas que reciba tu mascota aparecerán aquí</p>
                    </div>
                </div>
            </div>

            <!-- Vacunas Pendientes -->
            <div class="vaccine-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="bi bi-exclamation-circle-fill text-danger"></i>
                        Vacunas Pendientes
                        <span class="section-badge" id="badgePendientes">0</span>
                    </div>
                </div>
                <div id="vacunasPendientes">
                    <div class="empty-state">
                        <div class="empty-icon">⏳</div>
                        <h4>No hay vacunas pendientes</h4>
                        <p>Tu mascota está al día con sus vacunas</p>
                    </div>
                </div>
            </div>

            <!-- Próximas Vacunas -->
            <div class="vaccine-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="bi bi-calendar-check-fill text-info"></i>
                        Próximas Vacunas Programadas
                        <span class="section-badge" id="badgeProximas">0</span>
                    </div>
                </div>
                <div id="vacunasProximas">
                    <div class="empty-state">
                        <div class="empty-icon">📅</div>
                        <h4>No hay vacunas programadas</h4>
                        <p>Las próximas citas de vacunación aparecerán aquí</p>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script>
        // Datos de ejemplo - reemplazar con datos reales desde PHP/AJAX
        const vacunasData = {
            aplicadas: [{
                    nombre: "Vacuna Antirrábica",
                    fecha: "2024-12-15",
                    veterinario: "Dr. Juan Pérez",
                    lote: "VR-2024-12345",
                    proximaDosis: "2025-12-15",
                    costo: 45000,
                    observaciones: "Sin reacciones adversas"
                },
                {
                    nombre: "Vacuna Séxtuple (DHPPL)",
                    fecha: "2024-11-20",
                    veterinario: "Dra. María González",
                    lote: "SX-2024-67890",
                    proximaDosis: "2025-11-20",
                    costo: 65000,
                    observaciones: "Refuerzo anual aplicado"
                }
            ],
            pendientes: [{
                    nombre: "Vacuna contra Leptospirosis",
                    edadRecomendada: "8-12 semanas",
                    importancia: "Alta",
                    costoEstimado: 55000,
                    descripcion: "Protege contra infecciones bacterianas graves"
                },
                {
                    nombre: "Vacuna contra Tos de las Perreras",
                    edadRecomendada: "6-8 semanas",
                    importancia: "Media",
                    costoEstimado: 40000,
                    descripcion: "Recomendada para perros socializados"
                }
            ],
            proximas: [{
                nombre: "Refuerzo Antirrábica",
                fechaProgramada: "2025-12-15",
                veterinario: "Dr. Juan Pérez",
                costoEstimado: 45000,
                diasFaltantes: 329
            }]
        };

        function cargarVacunas() {
            // Actualizar estadísticas
            document.getElementById('totalAplicadas').textContent = vacunasData.aplicadas.length;
            document.getElementById('totalPendientes').textContent = vacunasData.pendientes.length;
            document.getElementById('totalProximas').textContent = vacunasData.proximas.length;

            document.getElementById('badgeAplicadas').textContent = vacunasData.aplicadas.length;
            document.getElementById('badgePendientes').textContent = vacunasData.pendientes.length;
            document.getElementById('badgeProximas').textContent = vacunasData.proximas.length;

            const costoTotal = vacunasData.aplicadas.reduce((sum, v) => sum + v.costo, 0);
            document.getElementById('costoTotal').textContent = `$${costoTotal.toLocaleString('es-CO')}`;

            // Cargar vacunas aplicadas
            if (vacunasData.aplicadas.length > 0) {
                document.getElementById('vacunasAplicadas').innerHTML = vacunasData.aplicadas.map(v => `
                    <div class="vaccine-card applied">
                        <div class="vaccine-header">
                            <div>
                                <div class="vaccine-name">${v.nombre}</div>
                                <span class="price-tag">
                                    <i class="bi bi-cash-coin"></i>
                                    $${v.costo.toLocaleString('es-CO')}
                                </span>
                            </div>
                            <span class="vaccine-status applied">Aplicada</span>
                        </div>
                        <div class="vaccine-details">
                            <div class="detail-item">
                                <i class="bi bi-calendar-event detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Fecha de Aplicación</div>
                                    <div class="detail-value">${new Date(v.fecha).toLocaleDateString('es-CO')}</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-person-badge detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Veterinario</div>
                                    <div class="detail-value">${v.veterinario}</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-upc-scan detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Lote</div>
                                    <div class="detail-value">${v.lote}</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-arrow-repeat detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Próxima Dosis</div>
                                    <div class="detail-value">${new Date(v.proximaDosis).toLocaleDateString('es-CO')}</div>
                                </div>
                            </div>
                        </div>
                        ${v.observaciones ? `
                            <div class="mt-3">
                                <small class="text-muted"><strong>Observaciones:</strong> ${v.observaciones}</small>
                            </div>
                        ` : ''}
                    </div>
                `).join('');
            }

            // Cargar vacunas pendientes
            if (vacunasData.pendientes.length > 0) {
                document.getElementById('vacunasPendientes').innerHTML = vacunasData.pendientes.map(v => `
                    <div class="vaccine-card pending">
                        <div class="vaccine-header">
                            <div>
                                <div class="vaccine-name">${v.nombre}</div>
                                <span class="price-tag">
                                    <i class="bi bi-cash-coin"></i>
                                    $${v.costoEstimado.toLocaleString('es-CO')} (estimado)
                                </span>
                            </div>
                            <span class="vaccine-status pending">Pendiente</span>
                        </div>
                        <div class="vaccine-details">
                            <div class="detail-item">
                                <i class="bi bi-clock-history detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Edad Recomendada</div>
                                    <div class="detail-value">${v.edadRecomendada}</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-star-fill detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Importancia</div>
                                    <div class="detail-value">${v.importancia}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted"><strong>Descripción:</strong> ${v.descripcion}</small>
                        </div>
                    </div>
                `).join('');
            }

            // Cargar próximas vacunas
            if (vacunasData.proximas.length > 0) {
                document.getElementById('vacunasProximas').innerHTML = vacunasData.proximas.map(v => `
                    <div class="vaccine-card upcoming">
                        <div class="vaccine-header">
                            <div>
                                <div class="vaccine-name">${v.nombre}</div>
                                <span class="price-tag">
                                    <i class="bi bi-cash-coin"></i>
                                    $${v.costoEstimado.toLocaleString('es-CO')}
                                </span>
                            </div>
                            <span class="vaccine-status upcoming">Programada</span>
                        </div>
                        <div class="vaccine-details">
                            <div class="detail-item">
                                <i class="bi bi-calendar-check detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Fecha Programada</div>
                                    <div class="detail-value">${new Date(v.fechaProgramada).toLocaleDateString('es-CO')}</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-person-badge detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Veterinario</div>
                                    <div class="detail-value">${v.veterinario}</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-hourglass-split detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Días Faltantes</div>
                                    <div class="detail-value">${v.diasFaltantes} días</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }

        function exportarPDF(tipo) {
            alert('Función de exportar PDF - Implementar con jsPDF');
        }

        // Cargar vacunas al iniciar
        document.addEventListener('DOMContentLoaded', cargarVacunas);
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/vacunas.js"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/theme.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/i18n.js"></script>
</body>

</html>