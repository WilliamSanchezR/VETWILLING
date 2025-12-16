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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/mascotas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/noche.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">


    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">

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
                    <span class="badge-count"><?= count($mascotas) ?> Mascota<?= count($mascotas) != 1 ? 's' : '' ?></span>
                </div>
                <a href="<?= BASE_URL ?>/reporte-mascotas?action=reporteMascotas" class="btn-agregar" target="_blank" style="background: #dc3545;">
                    <i class="bi bi-file-pdf"></i>
                    Exportar PDF
                </a>
                <a href="<?= BASE_URL ?>/cliente/registrar-mascota" class="btn-agregar">
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

                <?php if (empty($mascotas)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        No tienes mascotas registradas. ¡Agrega tu primera mascota!
                    </div>
                <?php else: ?>
                    <?php foreach ($mascotas as $m): ?>
                        <div class="mascota-card">
                            <div class="mascota-card-header">

                                <!-- MENU SUPERIOR -->
                                <div class="mascota-menu">
                                    <button type="button"
                                        class="btn-eliminar-mascota btn btn-link p-0"
                                        data-id="<?= $m['id_paciente'] ?>"
                                        data-nombre="<?= htmlspecialchars($m['nombre']) ?>"
                                        title="Eliminar mascota">
                                       <i class="fa-solid fa-trash-can" style="color: red;"></i>
                                    </button>

                                </div>

                                <!-- FOTO -->
                                <div class="mascota-avatar-grande">
                                    <img src="<?= BASE_URL ?>/public/uploads/mascotas/<?= $m['img_mascota'] ?>"
                                        alt="<?= htmlspecialchars($m['nombre']) ?>"
                                        style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover;">
                                </div>

                                <!-- INFO PRINCIPAL -->
                                <div class="mascota-info-header">
                                    <h3><?= htmlspecialchars($m['nombre']) ?></h3>
                                    <p><?= htmlspecialchars($m['raza']) ?></p>
                                    <div>
                                        <span class="mascota-chip"><?= htmlspecialchars($m['sexo']) ?></span>
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
                                            <div class="info-value"><?= $m['edad_numero'] ?> <?= $m['edad_unidad'] ?></div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon"><i class="fa-solid fa-dog"></i></div>
                                        <div class="info-content">
                                            <div class="info-label">Especie</div>
                                            <div class="info-value"><?= htmlspecialchars($m['especie']) ?></div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon"><i class="fa-notdog fa-solid fa-paw"></i></div>
                                        <div class="info-content">
                                            <div class="info-label">Raza</div>
                                            <div class="info-value"><?= htmlspecialchars($m['raza']) ?></div>
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

                                    <a href="<?= BASE_URL ?>/cliente/editar-mascota?id=<?= $m['id_paciente'] ?>"
                                        class="action-btn action-btn-secondary">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>

                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>

        </div>

    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/cliente/js/clientes.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>

    <script>
        // ========================================
        // FILTROS
        // ========================================
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // ========================================
        // BÚSQUEDA
        // ========================================
        document.querySelector('.search-box input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.mascota-card').forEach(card => {
                const nombre = card.querySelector('.mascota-info-header h3').textContent.toLowerCase();
                card.style.display = nombre.includes(searchTerm) ? 'block' : 'none';
            });
        });

        // ========================================
        // ANIMACIÓN DE ENTRADA
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.mascota-card').forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });

        // ========================================
        // MENÚ FLOTANTE (3 puntos)
        // ========================================
        document.addEventListener("DOMContentLoaded", () => {
            const botones = document.querySelectorAll(".mascota-menu .menu-btn");

            botones.forEach(btn => {
                btn.addEventListener("click", (e) => {
                    e.stopPropagation();

                    const menu = btn.nextElementSibling;

                    // Cierra otros menús abiertos
                    document.querySelectorAll(".menu-flotante.active")
                        .forEach(m => m !== menu && m.classList.remove("active"));

                    // Alterna el menú actual
                    menu.classList.toggle("active");
                });
            });

            // Cerrar al hacer click fuera
            document.addEventListener("click", () => {
                document.querySelectorAll(".menu-flotante.active")
                    .forEach(m => m.classList.remove("active"));
            });
        });

        // ========================================
        // ✅ ELIMINAR MASCOTA CON GET
        // ========================================
        document.addEventListener('DOMContentLoaded', () => {

            document.querySelectorAll('.btn-eliminar-mascota').forEach(btn => {

                btn.addEventListener('click', () => {

                    const id = btn.dataset.id;
                    const nombre = btn.dataset.nombre;

                    // Confirmar eliminación
                    if (confirm(`¿Estás seguro de eliminar la mascota "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {

                        // ✅ REDIRIGIR CON GET (como tu ejemplo de veterinario)
                        window.location.href = `<?= BASE_URL ?>/app/controllers/mascotasController.php?accion=eliminar&id=${id}`;

                        console.log(`🗑️ Eliminando mascota ID: ${id}, Nombre: ${nombre}`);
                    }
                });
            });

            console.log('✅ Script de eliminación cargado correctamente');
        });

        console.log('✅ Vista de Mascotas cargada correctamente');
    </script>

</body>

</html>