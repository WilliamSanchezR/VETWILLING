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

$id_mascota = (int)($_GET['id'] ?? 0);
if ($id_mascota <= 0) {
    header('Location: ' . BASE_URL . '/cliente/mascotas');
    exit();
}

$mascota = consultarMascotaId($id_mascota);
if (!$mascota) {
    header('Location: ' . BASE_URL . '/cliente/mascotas');
    exit();
}

/* Verificar que la mascota pertenece al propietario en sesión */
$_connVacOwnCheck = (new Conexion())->getConexion();
$_stmtVacOwn = $_connVacOwnCheck->prepare(
    "SELECT id_propietario FROM propietario WHERE id_usuario = :uid LIMIT 1"
);
$_stmtVacOwn->bindValue(':uid', (int)$_SESSION['user']['id_usuario'], PDO::PARAM_INT);
$_stmtVacOwn->execute();
$_propVacRow = $_stmtVacOwn->fetch(PDO::FETCH_ASSOC);

if (!$_propVacRow || (int)$mascota['id_propietario'] !== (int)$_propVacRow['id_propietario']) {
    header('Location: ' . BASE_URL . '/cliente/mascotas');
    exit();
}

?>
<!DOCTYPE html>
<html lang="<?= $prefs['idioma'] === 'pt' ? 'pt-BR' : $prefs['idioma'] ?>">

<head>
    <meta charset="UTF-8">
    <script>(function(){var p=<?= json_encode($prefs) ?>;document.documentElement.setAttribute('data-tema',p.tema);if(p.tema==='oscuro'&&document.body)document.body.classList.add('dark-theme');}());</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico - Dashboard VetCare</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/theme.css?v=<?= APP_VERSION ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/vacunas.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css?v=<?= APP_VERSION ?>">

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
            <div class="stats-grid row row-cols-1 row-cols-md-4 g-3">
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
        window.MASCOTA_ID  = <?= $id_mascota ?>;
        window.API_VACUNAS = '<?= BASE_URL ?>/cliente/api/vacunas';

        /* ── Helpers ─────────────────────────────────────────── */
        function escHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function fmtFecha(iso) {
            if (!iso) return 'Sin fecha';
            const f = new Date(iso + 'T00:00:00');
            return f.toLocaleDateString('es-CO', { year: 'numeric', month: 'long', day: 'numeric' });
        }

        /* ── Renderizado ─────────────────────────────────────── */
        function renderAplicadas(lista) {
            const el = document.getElementById('vacunasAplicadas');
            if (!lista || lista.length === 0) return;  /* deja el empty-state */

            el.innerHTML = lista.map(v => `
                <div class="vaccine-card applied">
                    <div class="vaccine-header">
                        <div>
                            <div class="vaccine-name">${escHtml(v.nombre)}</div>
                            ${v.dosis ? `<small class="text-muted"><i class="bi bi-capsule me-1"></i>Dosis: ${escHtml(v.dosis)}</small>` : ''}
                        </div>
                        <span class="vaccine-status applied">Aplicada</span>
                    </div>
                    <div class="vaccine-details row row-cols-1 row-cols-md-2 g-2">
                        <div class="detail-item">
                            <i class="bi bi-calendar-event detail-icon"></i>
                            <div class="detail-content">
                                <div class="detail-label">Fecha de Aplicación</div>
                                <div class="detail-value">${fmtFecha(v.fecha)}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="bi bi-person-badge detail-icon"></i>
                            <div class="detail-content">
                                <div class="detail-label">Veterinario</div>
                                <div class="detail-value">${escHtml(v.veterinario)}</div>
                            </div>
                        </div>
                    </div>
                    ${v.observaciones ? `
                        <div class="mt-3">
                            <small class="text-muted"><strong>Observaciones:</strong> ${escHtml(v.observaciones)}</small>
                        </div>
                    ` : ''}
                </div>
            `).join('');
        }

        /* ── Carga principal ─────────────────────────────────── */
        async function cargarVacunas() {
            try {
                const res  = await fetch(`${window.API_VACUNAS}?id_mascota=${window.MASCOTA_ID}`);
                const data = await res.json();

                if (!res.ok || data.status !== 'ok') {
                    throw new Error(data.message || 'Error al obtener vacunas');
                }

                const aplicadas  = data.aplicadas  || [];
                const pendientes = data.pendientes  || [];
                const proximas   = data.proximas    || [];

                /* Contadores */
                document.getElementById('totalAplicadas').textContent  = aplicadas.length;
                document.getElementById('totalPendientes').textContent = pendientes.length;
                document.getElementById('totalProximas').textContent   = proximas.length;
                document.getElementById('costoTotal').textContent      = '—';

                document.getElementById('badgeAplicadas').textContent  = aplicadas.length;
                document.getElementById('badgePendientes').textContent = pendientes.length;
                document.getElementById('badgeProximas').textContent   = proximas.length;

                renderAplicadas(aplicadas);

            } catch (err) {
                console.error('cargarVacunas:', err);
                document.getElementById('vacunasAplicadas').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">⚠️</div>
                        <h4>No se pudieron cargar las vacunas</h4>
                        <p>Intenta recargar la página. Si el problema persiste, contacta soporte.</p>
                    </div>`;
            }
        }

        function exportarPDF(tipo) {
            alert('Exportar PDF — próximamente disponible');
        }

        document.addEventListener('DOMContentLoaded', cargarVacunas);
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js?v=<?= APP_VERSION ?>"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/vacunas.js?v=<?= APP_VERSION ?>"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/theme.js?v=<?= APP_VERSION ?>"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/i18n.js?v=<?= APP_VERSION ?>"></script>
</body>

</html>