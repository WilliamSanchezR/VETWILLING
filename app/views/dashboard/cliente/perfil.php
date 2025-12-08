<?php
require_once BASE_PATH . '/app/controllers/mascotasController.php';

$mascotas = listarMascotas();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente - Dashboard VetCare</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/perfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/noche.css">

</head>

<body>

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="contenido-principal">
            <!-- DASHBOARD CONTENT -->
            <div class="container-dashboard">

                <!-- Header Perfil -->
                <div class="header-perfil">
                    <div class="avatar-grande">
                        <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['img_perfil'] ?>" alt="">
                        <div class="avatar-edit">
                            <i class="bi bi-camera-fill" style="color: white; font-size: 16px;"></i>
                        </div>
                    </div>
                    <div class="info-perfil-header">
                        <h1><?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?></h1>
                        <p><?= $usuario['email'] ?> <br> <?= $usuario['telefono'] ?></p>
                        <div class="badges-perfil">
                            <span class="badge-item">
                                <i class="bi bi-star-fill"></i>
                                Cliente VIP
                            </span>
                            <span class="badge-item">
                                <i class="bi bi-calendar-check"></i>
                                Miembro desde 2023
                            </span>
                            <span class="badge-item">
                                <i class="bi bi-heart-fill"></i>
                                <?= count($mascotas) ?> Mascotas
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="tabs-perfil">
                    <button class="tab-perfil active">
                        <i class="bi bi-person-fill"></i>
                        Información Personal
                    </button>
                    <button class="tab-perfil">
                        <i class="bi bi-heart-pulse"></i>
                        Mis Mascotas
                    </button>
                </div>

                <!-- Grid de Contenido -->
                <div class="perfil-grid">

                    <!-- Información Personal -->
                    <div class="card-perfil">
                        <div class="card-header-perfil">
                            <h2 class="card-titulo">
                                <i class="bi bi-person-circle"></i>
                                Datos Personales
                            </h2>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Nombre Completo:</span>
                            <span class="info-valor"><?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Documento:</span>
                            <span class="info-valor"><?= $usuario['tipo_documento'] ?>. <?= $usuario['numero_documento'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-valor"><?= $usuario['email'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Teléfono:</span>
                            <span class="info-valor">+57 <?= $usuario['telefono'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Dirección:</span>
                            <span class="info-valor"><?= $usuario['direccion'] ?>.</span>
                        </div>
                    </div>

                    <!-- Mis Mascotas -->
                    <div class="card-perfil">
                        <div class="card-header-perfil">
                            <h2 class="card-titulo">
                                <i class="bi bi-heart-fill"></i>
                                Mis Mascotas (<?= count($mascotas) ?>)
                            </h2>
                            <a href="<?= BASE_URL ?>/Cliente/registrar-mascota" class="btn-editar">
                                <i class="bi bi-plus-circle"></i>
                                Agregar
                            </a>
                        </div>

                        <?php foreach ($mascotas as $m) : ?>
                            <div class="mascota-mini-item">
                                <div class="mascota-mini-avatar">
                                    <img src="<?= BASE_URL ?>/public/uploads/mascotas/<?= $m['img_mascota'] ?>"
                                        alt="Mascota"
                                        style="width:50px; height:50px; border-radius:50%;">

                                </div>

                                <div class="mascota-mini-info">
                                    <div class="mascota-mini-nombre"><?= $m['nombre'] ?></div>
                                    <div class="mascota-mini-raza">
                                        <?= $m['raza'] ?> • <?= $m['edad'] ?> meses
                                    </div>
                                </div>

                                <a href="<?= BASE_URL ?>/Cliente/mascotas" class="btn-editar" style="padding: 8px 16px; font-size: 13px;">
                                    Ver
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Historial Reciente -->
                    <div class="card-perfil full">
                        <div class="card-header-perfil">
                            <h2 class="card-titulo">
                                <i class="bi bi-clock-history"></i>
                                Historial Reciente
                            </h2>
                            <button class="btn-editar">
                                Ver Todo
                            </button>
                        </div>

                        <div class="historial-item">
                            <div class="historial-fecha">
                                <div class="historial-dia">15</div>
                                <div class="historial-mes">Nov</div>
                            </div>
                            <div class="historial-info">
                                <div class="historial-titulo">Consulta General - Max</div>
                                <div class="historial-detalle">Dr. Juan Martínez • Consultorio 2 • $45.000</div>
                            </div>
                        </div>

                        <div class="historial-item">
                            <div class="historial-fecha">
                                <div class="historial-dia">10</div>
                                <div class="historial-mes">Nov</div>
                            </div>
                            <div class="historial-info">
                                <div class="historial-titulo">Vacunación - Luna</div>
                                <div class="historial-detalle">Dra. Ana García • Consultorio 1 • $35.000</div>
                            </div>
                        </div>

                        <div class="historial-item">
                            <div class="historial-fecha">
                                <div class="historial-dia">08</div>
                                <div class="historial-mes">Nov</div>
                            </div>
                            <div class="historial-info">
                                <div class="historial-titulo">Control Postoperatorio - Rocky</div>
                                <div class="historial-detalle">Dr. Carlos Rodríguez • Consultorio 3 • $30.000</div>
                            </div>
                        </div>

                        <div class="historial-item">
                            <div class="historial-fecha">
                                <div class="historial-dia">05</div>
                                <div class="historial-mes">Nov</div>
                            </div>
                            <div class="historial-info">
                                <div class="historial-titulo">Baño y Peluquería - Luna</div>
                                <div class="historial-detalle">María López • Sala de Estética • $40.000</div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/cliente/js/clientes.js"></script>
    <!-- JavaScript -->
    <script>
        // Tabs
        document.querySelectorAll('.tab-perfil').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab-perfil').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Toggle Switches
        document.querySelectorAll('.toggle-switch').forEach(toggle => {
            toggle.addEventListener('click', function() {
                this.classList.toggle('active');
            });
        });

        // Botón Editar Avatar
        document.querySelector('.avatar-edit').addEventListener('click', function() {
            alert('Función para cambiar foto de perfil');
        });

        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.card-perfil').forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });

        console.log('✅ Perfil del Cliente cargado correctamente');
    </script>
</body>

</html>