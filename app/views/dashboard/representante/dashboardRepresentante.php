<?php
// 
require_once BASE_PATH . '/app/helpers/session_representante.php';
require_once BASE_PATH . '/app/controllers/usuarioController.php';

$usuario = $_SESSION['user'];

// Resumen de la clínica de la sesión (consultas existentes de los modelos)
require_once BASE_PATH . '/app/models/veterinaria.php';
require_once BASE_PATH . '/app/models/Profesional.php';
require_once BASE_PATH . '/app/models/Propietario.php';
require_once BASE_PATH . '/app/models/Servicio.php';
require_once BASE_PATH . '/app/models/Inventario.php';
$idVeterinaria = acceso_exigir_clinica();
$clinica = (new Veterinaria())->consultarVeterinariasRegistradas($idVeterinaria) ?: [];
$profesionales = (new Profesional())->listar($idVeterinaria);
$resumen = [
    'profesionales_activos' => count(array_filter($profesionales, fn($p) => ($p['estado'] ?? '') === 'Activo')),
    'profesionales_total'   => count($profesionales),
    'propietarios'          => count((new Propietario())->listarPropietariosVeterinaria($idVeterinaria)),
    'servicios'             => count((new Servicio())->obtenerServiciosPorVeterinariaActivos($idVeterinaria)),
    'inventario'            => (new Inventario())->obtenerResumen($idVeterinaria),
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del representante</title>

    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/representante.styles.css">

</head>

<body>

    <!-- BARRA LATERAL IZQUIERDA -->
    <!-- Include de la barra lateral izquierda -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_representante.php'
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <!-- Aqui va el include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_representante.php'
        ?>


        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <!-- HEADER ADMIN -->
            <div class="header-admin">
                <div class="header-info">
                    <h1>👋 Bienvenido, <?= htmlspecialchars($usuario['nombres']) ?> <?= htmlspecialchars($usuario['apellidos']) ?></h1>
                    <p>Panel de tu clínica<?= !empty($clinica['nombre']) ? ': ' . htmlspecialchars($clinica['nombre']) : '' ?></p>
                </div>
                <div class="header-acciones">
                    <!-- <button class="btn-header" onclick="exportarReporte()">
                        <i class="bi bi-download"></i>
                        Exportar Reporte
                    </button>
                    <button class="btn-header" onclick="abrirConfiguracion()">
                        <i class="bi bi-gear"></i>
                        Configuración
                    </button> -->
                </div>
            </div>

            <!-- ESTADÍSTICAS DE LA CLÍNICA -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $resumen['profesionales_activos'] ?></div>
                    <div class="stat-label">Profesionales activos</div>
                    <div class="stat-footer">
                        <i class="bi bi-people"></i> <?= $resumen['profesionales_total'] ?> vinculados a la clínica
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-person-hearts"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $resumen['propietarios'] ?></div>
                    <div class="stat-label">Propietarios</div>
                    <div class="stat-footer">
                        <i class="bi bi-house-heart"></i> Registrados en la clínica
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-clipboard2-pulse"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $resumen['servicios'] ?></div>
                    <div class="stat-label">Servicios activos</div>
                    <div class="stat-footer">
                        <i class="bi bi-check-circle"></i> Ofrecidos por la clínica
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $resumen['inventario']['total_productos'] ?></div>
                    <div class="stat-label">Productos en inventario</div>
                    <div class="stat-footer">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?= $resumen['inventario']['por_vencer'] ?> por vencer en 30 días · <?= $resumen['inventario']['vencidos'] ?> vencidos
                    </div>
                </div>
            </div>

        </div>

    </div>

    </div>

    <!-- Bootstrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Propio -->

    <!-- Global Script -->
    <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>
</body>

</html>