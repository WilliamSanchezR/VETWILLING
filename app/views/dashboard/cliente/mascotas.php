<?php
require_once BASE_PATH . '/app/controllers/mascotasController.php';

$mascotas = listarMascotas();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente - Mascotas VetCare</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/mascotas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/noche.css">

</head>


<body>
    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <!-- Header -->
            <div class="header-mascotas">
                <div class="header-titulo">
                    <h1>🐾 Mis Mascotas</h1>
                    <span class="badge-count"> <?= count($mascotas) ?> Mascota</span>
                </div>
                <a href="<?= BASE_URL ?>/Cliente/registrar-mascota" class="btn-agregar">
                    <i class="bi bi-plus-lg"></i>
                    Agregar Mascota
                </a>
            </div>

            <!-- Filtros -->
            <div class="filtros-container">
                <div class="search-box">
                    <input type="text" placeholder="Buscar mascota por nombre...">
                    <i class="bi bi-search"></i>
                </div>
                <button class="filter-btn active">Todas</button>
                <button class="filter-btn">Perros 🐕</button>
                <button class="filter-btn">Gatos 🐈</button>
                <button class="filter-btn">Vacunas al día</button>
                <button class="filter-btn">Pendientes</button>
            </div>

            <!-- Grid de Mascotas -->
            <div class="mascotas-grid">

                <?php foreach ($mascotas as $m): ?>
                    <div class="mascota-card">
                        <div class="mascota-card-header">

                            <!-- MENU SUPERIOR -->
                            <div class="mascota-menu">
                                <button class="menu-btn"><i class="bi bi-three-dots-vertical"></i></button>
                            </div>

                            <!-- FOTO -->
                            <div class="mascota-avatar-grande">
                                <img src="<?= BASE_URL ?>/public/uploads/mascotas/<?= $m['img_mascota'] ?>"
                                    alt="Mascota"
                                    style="width: 90px; height: 90px; border-radius: 50%;">
                            </div>

                            <!-- INFO PRINCIPAL -->
                            <div class="mascota-info-header">
                                <h3><?= $m['nombre'] ?></h3>
                                <p><?= $m['raza'] ?></p>
                                <div>
                                    <span class="mascota-chip"><?= $m['sexo'] ?></span>
                                    <span class="mascota-chip">Activo</span>
                                </div>
                            </div>
                        </div>

                        <div class="mascota-card-body">

                            <!-- INFO DETALLADA -->
                            <div class="info-grid">

                                <div class="info-item">
                                    <div class="info-icon"><i class="bi bi-calendar3"></i></div>
                                    <div class="info-content">
                                        <div class="info-label">Edad</div>
                                        <div class="info-value"><?= $m['edad'] ?> años</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon"><i class="bi bi-speedometer2"></i></div>
                                    <div class="info-content">
                                        <div class="info-label">Especie</div>
                                        <div class="info-value"><?= $m['especie'] ?></div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon"><i class="bi bi-palette"></i></div>
                                    <div class="info-content">
                                        <div class="info-label">Raza</div>
                                        <div class="info-value"><?= $m['raza'] ?></div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon"><i class="bi bi-clipboard-pulse"></i></div>
                                    <div class="info-content">
                                        <div class="info-label">Última Visita</div>
                                        <div class="info-value">—</div>
                                    </div>
                                </div>
                            </div>

                            <!-- BOTONES -->
                            <div class="mascota-actions">

                                <button class="action-btn action-btn-primary">
                                    <i class="bi bi-calendar-plus"></i> Agendar Cita
                                </button>

                                <button class="action-btn action-btn-info">
                                    <i class="bi bi-file-medical"></i> Ver Historial
                                </button>

                                <button class="action-btn action-btn-success">
                                    <i class="bi bi-syringe"></i> Vacunas
                                </button>

                                <a href="<?= BASE_URL ?>/mascotas/editar/<?= $m['id_paciente'] ?>"
                                    class="action-btn action-btn-secondary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>

                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            </div>


        </div>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="<?= BASE_URL ?>/public/assets/dashboard/cliente/js/clientes.js"></script>

        <script>
            // Filtros
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Búsqueda
            document.querySelector('.search-box input').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                document.querySelectorAll('.mascota-card').forEach(card => {
                    const nombre = card.querySelector('.mascota-info-header h3').textContent.toLowerCase();
                    if (nombre.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

            // Animación de entrada
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.mascota-card').forEach((card, index) => {
                    card.style.animationDelay = `${index * 0.1}s`;
                });
            });

            console.log('✅ Vista de Mascotas cargada correctamente');
        </script>
</body>

</html>