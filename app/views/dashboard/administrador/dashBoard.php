<?php
require_once BASE_PATH . '/app/helpers/session_administrador.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DashBoard Veterinario</title>

    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/administracionStyle.css">

</head>

<body>

    <!-- BARRA LATERAL IZQUIERDA -->
    <!-- Include de la barra lateral izquierda -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php'
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <!-- Aqui va el include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_administrador.php'
        ?>


        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <!-- HEADER ADMIN -->
            <div class="header-admin">
                <div class="header-info">
                    <h1>👋 Bienvenido, Administrador <?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?></h1>
                    <p>Panel de control principal - Gestiona todo el sistema VetWilling</p>
                </div>
                <div class="header-acciones">
                    <button class="btn-header" onclick="exportarReporte()">
                        <i class="bi bi-download"></i>
                        Exportar Reporte
                    </button>
                    <button class="btn-header" onclick="abrirConfiguracion()">
                        <i class="bi bi-gear"></i>
                        Configuración
                    </button>
                </div>
            </div>

            <!-- ESTADÍSTICAS -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="bi bi-arrow-up"></i>
                            12%
                        </div>
                    </div>
                    <div class="stat-value">1,248</div>
                    <div class="stat-label">Total Usuarios</div>
                    <div class="stat-footer">
                        <i class="bi bi-person-plus"></i> 24 nuevos esta semana
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="bi bi-arrow-up"></i>
                            8%
                        </div>
                    </div>
                    <div class="stat-value">142</div>
                    <div class="stat-label">Veterinarias Activas</div>
                    <div class="stat-footer">
                        <i class="bi bi-check-circle"></i> 5 pendientes de aprobación
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="bi bi-arrow-up"></i>
                            15%
                        </div>
                    </div>
                    <div class="stat-value">3,567</div>
                    <div class="stat-label">Productos</div>
                    <div class="stat-footer">
                        <i class="bi bi-clock"></i> 89 Productos agregados esta semana
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="stat-trend down">
                            <i class="bi bi-arrow-down"></i>
                            5%
                        </div>
                    </div>
                    <div class="stat-value">23</div>
                    <div class="stat-label">Nuevos este mes</div>
                    <div class="stat-footer">
                        <i class="bi bi-eye"></i> Requieren atención
                    </div>
                </div>
            </div>

            <!-- TABS -->
            <div class="tabs-admin">
                <button class="tab-btn active" onclick="cambiarTab('usuarios')">
                    <i class="bi bi-people"></i>
                    Usuarios
                </button>
                <button class="tab-btn" onclick="cambiarTab('veterinarias')">
                    <i class="bi bi-building"></i>
                    Veterinarias
                </button>
                <button class="tab-btn" onclick="cambiarTab('productos')">
                    <i class="bi bi-box"></i>
                    Productos
                </button>
            </div>

            <!-- CONTENIDO TAB USUARIOS -->
            <div id="tab-usuarios" class="contenido-tab activo">

                <!-- TABLA -->
                <div class="grafico-card">
                    <div class="grafico-header">
                        <h3 class="grafico-titulo">Gestión de Usuarios</h3>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn-accion btn-secondary" onclick="refrescarTabla()">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>

                    <div class="contenedor-tabla">
                        <table class="tabla-admin">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll" onchange="seleccionarTodos()"></th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Registro</th>
                                    <th>Última Actividad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaUsuarios">
                                <!-- Todos los Usuarios -->

                                <?php if (!empty($datos)) : ?>
                                    <?php foreach ($datos as $usuario):  ?>
                                        <tr class="fila-blanca">
                                            <td><img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['img_perfil'] ?>" alt=""></td>
                                            <td><?= $usuario['tipo_documento'] ?> - <?= $usuario['numero_documento'] ?></td>
                                            <td><?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?></td>
                                            <td><?= $usuario['telefono'] ?></td>
                                            <td><?= $usuario['email'] ?></td>
                                            <td><?= $usuario['estado'] ?></td>
                                            <td><?= $usuario['rol'] ?></td>
                                            <td>
                                                <button class="btn-accion btn-editar" title="Editar">
                                                    <a href="<?= BASE_URL ?>/admin/editar-usuario?id=<?= $usuario['id_usuario'] ?>"><i class="bi bi-pencil"></i></a>
                                                </button>
                                                <button class="btn-accion btn-eliminar" title="Eliminar">
                                                    <a href="<?= BASE_URL ?>/admin/eliminar-usuario?accion=eliminar&id=<?= $usuario['id_usuario'] ?>"><i class="bi bi-trash"></i></a>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PAGINACIÓN -->
                <div class="paginacion">
                    <div class="paginacion-info">
                        Mostrando <strong>1-10</strong> de <strong>48</strong> usuarios
                    </div>
                    <div class="paginacion-botones">
                        <button class="pagina-btn" disabled onclick="cambiarPagina('prev')">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button class="pagina-btn active" onclick="irAPagina(1)">1</button>
                        <button class="pagina-btn" onclick="irAPagina(2)">2</button>
                        <button class="pagina-btn" onclick="irAPagina(3)">3</button>
                        <button class="pagina-btn" onclick="irAPagina(4)">4</button>
                        <button class="pagina-btn" onclick="irAPagina(5)">5</button>
                        <button class="pagina-btn" onclick="cambiarPagina('next')">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO TAB VETERINARIAS -->
            <div id="tab-veterinarias" class="contenido-tab">
                <div class="grafico-card">
                    <div class="grafico-header">
                        <h3 class="grafico-titulo">Gestión de Veterinarias</h3>
                    </div>
                    <p style="text-align: center; padding: 40px; color: #7f8c8d;">
                        <i class="bi bi-building" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                        Contenido de veterinarias próximamente...
                    </p>
                </div>
            </div>

            <!-- CONTENIDO TAB PRODUCTOS -->
            <div id="tab-productos" class="contenido-tab">
                <div class="grafico-card">
                    <div class="grafico-header">
                        <h3 class="grafico-titulo">Gestión de Productos</h3>
                    </div>
                    <p style="text-align: center; padding: 40px; color: #7f8c8d;">
                        <i class="bi bi-box" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                        Contenido de productos próximamente...
                    </p>
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