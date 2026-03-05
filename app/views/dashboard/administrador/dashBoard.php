<?php
require_once BASE_PATH . '/app/helpers/session_administrador.php';
require_once BASE_PATH . '/app/controllers/dashboardsAdminControllers.php';
require_once BASE_PATH . '/app/controllers/usuarioController.php';
require_once BASE_PATH . '/app/controllers/veterinariaController.php';

$totalUsuarios = getTotalUsuarios();
$usuariosRegistradosUltimoMes = getUsuariosRegistradosUltimoMes();
$totalVeterinarias = getTotalVeterinarias();
$porcentajeVeterinarias = getPorcentajeVeterinarias();
$totalProfesionales = getTotalProfesionales();
$profesionalesUltimoMes = getProfesionalesUltimoMes();
$porcentajeProfesionales = getPorcentajeProfesionales();
$usuariosUltimoMes = getUsuariosUltimoMes();
$datos = ListarTodosUsuarios();
$datosVeterinarias = listarVeterinariasRegistradas();
// Aquí puedes usar $dashboardInfo para mostrar la información en el dashboard, por ejemplo:
// echo "Total Veterinarias Activas: " . $dashboardInfo;

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

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/styleTableAdmin.css">

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


            <!-- ESTADÍSTICAS -->

            <!-- Estadistica total usuarios -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="bi bi-arrow-up"></i>
                            <?= $totalUsuarios['porcentaje_activos'] ?>%
                        </div>
                    </div>
                    <div class="stat-value"><?= $totalUsuarios['total_activos'] ?></div>
                    <div class="stat-label">Total Usuarios</div>
                    <div class="stat-footer">
                        <i class="bi bi-person-plus"></i> <?= $usuariosRegistradosUltimoMes ?> nuevos usuarios este mes
                    </div>
                </div>

                <!-- Estadistica total veterinarias -->
                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="bi bi-arrow-up"></i>
                            <?= $porcentajeVeterinarias['porcentaje_activas'] ?>%
                        </div>
                    </div>
                    <div class="stat-value"><?= $totalVeterinarias ?></div>
                    <div class="stat-label">Veterinarias Activas</div>
                    <div class="stat-footer">
                        <i class="bi bi-check-circle"></i> <?= $totalVeterinarias ?> veterinarias activas en el sistema
                    </div>
                </div>

                <!-- Estadistica total profesionales -->
                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="bi bi-arrow-up"></i>
                            <?= $porcentajeProfesionales['porcentaje_activas'] ?>%
                        </div>
                    </div>
                    <div class="stat-value"><?= $totalProfesionales ?></div>
                    <div class="stat-label">Total profesionales</div>
                    <div class="stat-footer">
                        <i class="bi bi-clock"></i> <?= $profesionalesUltimoMes ?> profesionales registrados este mes
                    </div>
                </div>

                <!-- Estadistica total nuevos usuarioseste mes -->
                <div class="stat-card danger">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="stat-trend down">
                            <i class="bi bi-arrow-down"></i>
                            <?= $usuariosUltimoMes['porcentaje_activos_ultimo_mes'] ?>%
                        </div>
                    </div>
                    <div class="stat-value"><?= $usuariosUltimoMes['usuarios_ultimo_mes'] ?></div>
                    <div class="stat-label">Usuarios que usaron el sistema este mes</div>
                    <div class="stat-footer">
                        <i class="bi bi-eye"></i> <?= $usuariosUltimoMes['usuarios_ultimo_mes'] ?> usuarios que se logearon este mes
                    </div>
                </div>
            </div>

            <!-- TABS -->
            <div class="tabs-admin">
                <button class="tab-btn active">
                    <i class="bi bi-people"></i>
                    Usuarios
                </button>
                <button class="tab-btn">
                    <i class="bi bi-building"></i>
                    Veterinarias
                </button>

            </div>

            <!-- CONTENIDO TAB USUARIOS -->
            <div id="tab-usuarios" class="contenido-tab activo">

                <!-- TABLA -->
                <div class="grafico-card">
                    <div class="grafico-header">
                        <h3 class="grafico-titulo">Lista de todos los Usuarios</h3>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn-accion btn-secondary" onclick="refrescarTabla()">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>

                    <div class="contenedor-tabla">
                        <table class="tabla-admin" id="tbl_user_admin">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Numero Documento</th>
                                    <th>Nombre y apellidos</th>
                                    <th>Numero Telefono</th>
                                    <th>Email</th>
                                    <th>Estado</th>
                                    <th>Rol</th>
                                </tr>
                            </thead>
                            <tbody id="tablaUsuarios">
                                <!-- Todos los Usuarios -->

                                <?php if (!empty($datos)) : ?>
                                    <?php foreach ($datos as $usuario):  ?>
                                        <tr class="fila-blanca">
                                            <td class="tb_foto"><?php if (!empty($usuario['img_perfil'])): ?><?php if ($usuario['id_rol'] == 2): ?><img src="<?= BASE_URL ?>/public/uploads/profesionales/<?= $usuario['img_perfil'] ?>" alt=""><?php else: ?><img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['img_perfil'] ?>" alt=""><?php endif; ?><?php else: ?><i class="bi bi-image"></i><?php endif; ?></td>
                                            <td><?= $usuario['tipo_documento'] ?> - <?= $usuario['numero_documento'] ?></td>
                                            <td><?= $usuario['nombres'] ?> <?= $usuario['apellidos'] ?></td>
                                            <td><?= $usuario['telefono'] ?></td>
                                            <td><?= $usuario['email'] ?></td>
                                            <td><?= $usuario['estado'] ?></td>
                                            <td><?= $usuario['rol'] ?></td>
                                            <!-- <td class="content-action">
                                                <button class="btn-accion btn-editar" title="Editar">
                                                    <a href="<?= BASE_URL ?>/admin/editar-usuario?id=<?= $usuario['id_usuario'] ?>"><i class="bi bi-pencil"></i></a>
                                                </button>
                                                <button class="btn-accion btn-eliminar" title="Eliminar">
                                                    <a href="<?= BASE_URL ?>/admin/eliminar-usuario?accion=eliminar&id=<?= $usuario['id_usuario'] ?>"><i class="bi bi-trash"></i></a>
                                                </button>
                                            </td> -->
                                        </tr>
                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-veterinarias" class="contenido-tab">

                <div class="grafico-card">
                    <div class="grafico-header">
                        <h3 class="grafico-titulo">Lista de Veterinarias</h3>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn-accion btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>

                    <div class="contenedor-tabla">
                        <table id="tablaListaVeterinarias" class="display tabla-admin" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Logo</th>
                                    <th>nit</th>
                                    <th>Razón Social</th>
                                    <th>Ciudad</th>
                                    <th>Representante Legal</th>
                                    <th>Email</th>
                                    <th>Telefono</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($datosVeterinarias)) : ?>
                                    <?php foreach ($datosVeterinarias as $veterinaria):  ?>
                                        <tr class="fila-blanca">
                                            <td class="tb_foto"><?php if (!empty($veterinaria['logo'])): ?><img src="<?= BASE_URL ?>/public/uploads/veterinaria/<?= $veterinaria['logo'] ?>" alt=""><?php else: ?><i class="bi bi-image"></i><?php endif; ?></td>
                                            <td><?= $veterinaria['nit'] ?></td>
                                            <td><?= $veterinaria['razon_social'] ?></td>
                                            <td><?= $veterinaria['ciudad'] ?></td>
                                            <td><?= $veterinaria['nombre'] ?></td>
                                            <td><?= $veterinaria['email'] ?></td>
                                            <td><?= $veterinaria['telefono'] ?></td>
                                            <td><?= $veterinaria['estado'] ?></td>

                                        </tr>
                                    <?php endforeach; ?>

                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

    </div>

    <!-- Bootstrap -->

    <!-- SCRIPTS -->
    <!-- 1. jQuery PRIMERO -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- 2. Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- 3. DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrador/js/dashboardsAdmin.js"></script>
    <!-- Global Script -->
    <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>
</body>

</html>