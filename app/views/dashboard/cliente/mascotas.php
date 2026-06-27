<?php

// Cargar preferencias e i18n
if (!isset($prefs)) {
    if (!class_exists('PreferenciasManager')) { require_once BASE_PATH . '/app/services/PreferenciasManager.php'; }
    $_pm = new PreferenciasManager((int)$_SESSION['user']['id_usuario']);
    $prefs = $_pm->obtener();
}
if (!isset($t)) {
    if (!class_exists('I18n')) { require_once BASE_PATH . '/app/lang/i18n.php'; }
    $t = I18n::cargar($prefs['idioma']);
}require_once BASE_PATH . '/app/controllers/mascotasController.php';

$mascotas = listarMascotas();
?>
<!DOCTYPE html>
<html lang="<?= $prefs['idioma'] === 'pt' ? 'pt-BR' : $prefs['idioma'] ?>">

<head>
    <meta charset="UTF-8">
    <script>(function(){var p=<?= json_encode($prefs) ?>;document.documentElement.setAttribute('data-tema',p.tema);if(p.tema==='oscuro'&&document.body)document.body.classList.add('dark-theme');}());</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Mascotas - VetWilling</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS propios -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/mascotas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
</head>

<body class="<?= $prefs['tema'] === 'oscuro' ? 'dark-theme' : '' ?>" data-tema="<?= $prefs['tema'] ?>">

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <!-- ── Header ── -->
            <div class="header-mascotas">
                <div class="header-titulo">
                    <i class="bi bi-heart-pulse-fill header-icon"></i>
                    <h1 data-i18n="mascotas.titulo"><?= $t('mascotas.titulo') ?></h1>
                    <span class="badge-count">
                        <?= count($mascotas) ?> Mascota<?= count($mascotas) != 1 ? 's' : '' ?>
                    </span>
                </div>

                <a href="<?= BASE_URL ?>/reporte-mascotas?action=reporteMascotas"
                    class="btn-action btn-pdf"
                    target="_blank"
                    rel="noopener">
                    <i class="bi bi-file-earmark-pdf"></i>
                    Exportar PDF
                </a>

                <a href="<?= BASE_URL ?>/cliente/registrar-mascota" class="btn-action">
                    <i class="bi bi-plus-lg"></i>
                    Agregar mascota
                </a>

                <a href="<?= BASE_URL ?>/cliente/agendar-cita" class="btn-action btn-cita">
                    <i class="bi bi-calendar-plus"></i>
                    Agendar cita
                </a>
            </div>

            <!-- ── Filtros ── -->
            <div class="filtros-container">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input
                        type="text"
                        id="inputBuscarMascota"
                        placeholder="Buscar mascota por nombre..."
                        autocomplete="off">
                </div>
                <button class="filter-btn active" data-filtro="todas">
                    <i class="bi bi-grid-3x3-gap"></i> Todas
                </button>
                <button class="filter-btn" data-filtro="perro">
                    <i class="bi bi-github"></i> Perros
                </button>
                <button class="filter-btn" data-filtro="gato">
                    <i class="bi bi-skip-forward"></i> Gatos
                </button>
                <button class="filter-btn" data-filtro="al-dia">
                    <i class="bi bi-shield-check"></i> Vacunas al día
                </button>
                <button class="filter-btn" data-filtro="pendiente">
                    <i class="bi bi-clock-history"></i> Pendientes
                </button>
            </div>

            <!-- ── Grid de Mascotas ── -->
            <div class="mascotas-grid" id="mascotasGrid">

                <?php if (empty($mascotas)): ?>
                    <!-- Estado vacío -->
                    <div class="sin-mascotas">
                        <div class="sin-mascotas-icon">
                            <i class="bi bi-heart-pulse"></i>
                        </div>
                        <h3>No tienes mascotas registradas</h3>
                        <p>¡Agrega tu primera mascota y lleva el control de su salud!</p>
                        <a href="<?= BASE_URL ?>/cliente/registrar-mascota" class="btn-action">
                            <i class="bi bi-plus-lg"></i> Agregar mascota
                        </a>
                    </div>

                <?php else: ?>
                    <?php
                    // Colores de acento para las cards (se asigna por índice en ciclo)
                    $colores = ['hero-green', 'hero-blue', 'hero-amber', 'hero-purple', 'hero-coral'];
                    $ico_colores = ['ico-green', 'ico-blue', 'ico-amber', 'ico-purple', 'ico-coral'];
                    $i = 0;
                    foreach ($mascotas as $m):
                        $colorHero = $colores[$i % count($colores)];
                        $colorIco  = $ico_colores[$i % count($ico_colores)];
                        $i++;
                    ?>

                        <div class="mascota-card"
                             data-nombre="<?= htmlspecialchars(strtolower($m['nombre'])) ?>"
                             data-especie="<?= htmlspecialchars(strtolower($m['especie'])) ?>"
                             data-estado="al-dia">

                            <!-- ── Header de tarjeta ── -->
                            <div class="mascota-card-header <?= $colorHero ?>">

                                <!-- Avatar -->
                                <div class="mascota-avatar-grande">
                                    <img
                                        src="<?= BASE_URL ?>/public/uploads/mascotas/<?= htmlspecialchars($m['img_mascota']) ?>"
                                        alt="Foto de <?= htmlspecialchars($m['nombre']) ?>"
                                        onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                                    <span class="avatar-fallback" style="display:none">
                                        <i class="bi bi-image-alt"></i>
                                    </span>
                                </div>

                                <!-- Info principal -->
                                <div class="mascota-info-header">
                                    <h3><?= htmlspecialchars($m['nombre']) ?></h3>
                                    <div class="hero-chips">
                                        <span class="mascota-chip">
                                            <i class="bi bi-<?= strtolower($m['sexo']) === 'macho' ? 'gender-male' : 'gender-female' ?>"></i>
                                            <?= htmlspecialchars($m['sexo']) ?>
                                        </span>
                                        <span class="mascota-chip">
                                            <i class="bi bi-tag"></i>
                                            Activo
                                        </span>
                                    </div>
                                </div>

                                <!-- Indicador de estado -->
                                <div class="status-badge">
                                    <span class="status-dot dot-green"></span>
                                    <span>Activa</span>
                                </div>

                            </div>

                            <!-- ── Cuerpo de tarjeta ── -->
                            <div class="mascota-card-body">

                                <!-- Info detallada -->
                                <div class="info-grid">

                                    <div class="info-item">
                                        <div class="info-icon <?= $colorIco ?>">
                                            <i class="bi bi-calendar3"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Edad</div>
                                            <div class="info-value">
                                                <?= htmlspecialchars($m['edad_numero']) ?>
                                                <?= htmlspecialchars($m['edad_unidad']) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon <?= $colorIco ?>">
                                            <i class="bi bi-box"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Especie</div>
                                            <div class="info-value"><?= htmlspecialchars($m['especie']) ?></div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon <?= $colorIco ?>">
                                            <i class="bi bi-award"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Raza</div>
                                            <div class="info-value"><?= htmlspecialchars($m['raza']) ?></div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon <?= $colorIco ?>">
                                            <i class="bi bi-clipboard-pulse"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Última visita</div>
                                            <div class="info-value">—</div>
                                        </div>
                                    </div>

                                </div>

                                <div class="card-divider"></div>

                                <!-- Botones de acción -->
                                <div class="mascota-actions">

                                    <a href="<?= BASE_URL ?>/cliente/historial-mascota?id=<?= (int)$m['id_paciente'] ?>"
                                        class="action-btn action-btn-info">
                                        <i class="bi bi-file-medical"></i> Historial
                                    </a>

                                    <a href="<?= BASE_URL ?>/cliente/vacunas-mascota?id=<?= (int)$m['id_paciente'] ?>"
                                        class="action-btn action-btn-success">
                                        <i class="bi bi-shield-plus"></i> Vacunas
                                    </a>

                                    <a href="<?= BASE_URL ?>/cliente/editar-mascota?id=<?= (int)$m['id_paciente'] ?>"
                                        class="action-btn action-btn-secondary">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>

                                    <button
                                        type="button"
                                        class="action-btn action-btn-danger btn-eliminar-mascota"
                                        data-id="<?= (int)$m['id_paciente'] ?>"
                                        data-nombre="<?= htmlspecialchars($m['nombre']) ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminarMascota">
                                        <i class="bi bi-trash3"></i> Eliminar
                                    </button>

                                </div>

                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php endif; ?>

            </div><!-- /mascotas-grid -->

        </div><!-- /area-contenido -->

    </main>

    <!-- ================================================================
         MODAL DE CONFIRMACIÓN DE ELIMINACIÓN
    ================================================================ -->
    <div class="modal fade" id="modalEliminarMascota" tabindex="-1"
         aria-labelledby="modalEliminarTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <form id="formEliminarMascota" method="POST">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-danger d-flex align-items-center gap-2" id="modalEliminarTitulo">
                            <i class="bi bi-exclamation-triangle"></i>
                            Eliminar mascota
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <p class="mb-3">¿Seguro que quieres eliminar a
                            <strong id="nombreMascotaEliminar"></strong>?
                        </p>
                        <div class="mb-3">
                            <label for="motivoEliminacion" class="form-label">
                                <span class="text-danger">*</span> Motivo de eliminación:
                            </label>
                            <textarea class="form-control form-control-sm" id="motivoEliminacion" 
                                      name="motivo" rows="3" placeholder="Ej: Mudanza, razones personales, fallecimiento, etc." 
                                      required></textarea>
                            <small class="text-muted">Este registro quedará en la auditoría del sistema.</small>
                        </div>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-info-circle"></i> Esta acción no se puede deshacer.
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-0 gap-2">
                        <button type="button" class="btn btn-sm btn-secondary d-flex align-items-center gap-1"
                                data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </button>
                        <button type="submit" id="btnConfirmarEliminar"
                               class="btn btn-sm btn-danger d-flex align-items-center gap-1">
                            <i class="bi bi-trash3"></i> Sí, eliminar
                        </button>
                    </div>
                    <input type="hidden" id="idMascotaEliminar" name="id">
                    <input type="hidden" name="accion" value="eliminar">
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        var cards = document.querySelectorAll('.mascota-card');

        /* ── Filtros por especie / estado ── */
        document.querySelectorAll('.filter-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.filter-btn').forEach(function (b) {
                    b.classList.remove('active');
                });
                this.classList.add('active');

                var filtro = this.dataset.filtro;

                cards.forEach(function (card) {
                    var especie = card.dataset.especie || '';
                    var estado  = card.dataset.estado  || '';
                    var mostrar =
                        filtro === 'todas'       ||
                        especie.includes(filtro) ||
                        estado === filtro;
                    card.style.display = mostrar ? '' : 'none';
                });
            });
        });

        /* ── Búsqueda por nombre ── */
        var inputBuscar = document.getElementById('inputBuscarMascota');
        if (inputBuscar && cards.length > 0) {
            inputBuscar.addEventListener('input', function () {
                var termino = this.value.toLowerCase().trim();
                cards.forEach(function (card) {
                    var nombre = card.dataset.nombre || '';
                    card.style.display = nombre.includes(termino) ? '' : 'none';
                });
            });
        }

        /* ── Animación de entrada escalonada ── */
        cards.forEach(function (card, index) {
            card.style.animationDelay = (index * 0.08) + 's';
        });

        /* ── Modal de eliminación ── */
        var formEliminar = document.getElementById('formEliminarMascota');
        var inputIdMascota = document.getElementById('idMascotaEliminar');
        var inputMotivo = document.getElementById('motivoEliminacion');

        document.querySelectorAll('.btn-eliminar-mascota').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id     = this.dataset.id;
                var nombre = this.dataset.nombre;

                var spanNombre = document.getElementById('nombreMascotaEliminar');
                if (spanNombre) spanNombre.textContent = nombre;

                if (inputIdMascota) inputIdMascota.value = id;
                if (inputMotivo) inputMotivo.value = '';  // Limpiar motivo anterior
            });
        });

        // Manejar envío del formulario
        if (formEliminar) {
            formEliminar.addEventListener('submit', function (e) {
                e.preventDefault();

                var motivo = inputMotivo ? inputMotivo.value.trim() : '';
                if (!motivo) {
                    alert('Por favor, ingresa un motivo para la eliminación.');
                    return;
                }

                // Enviar formulario por POST
                var formData = new FormData(formEliminar);
                fetch('<?= BASE_URL ?>/app/controllers/mascotasController.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // Cerrar modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalEliminarMascota'));
                    if (modal) modal.hide();

                    // Mostrar respuesta (el controller ya envía JSON con sweetAlert)
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar la mascota. Intenta de nuevo.');
                });
            });
        }

    });
    </script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/theme.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/i18n.js"></script>
</body>
</html>