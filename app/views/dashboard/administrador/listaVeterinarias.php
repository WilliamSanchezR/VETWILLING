<?php
// Enlazamos la ruta para tomar la session del administrador
require_once BASE_PATH . '/app/helpers/session_administrador.php';
// Enlazamos el controlador de veterinaria para listar las veterinarias
require_once BASE_PATH . '/app/controllers/veterinariaController.php';
// Llamamos la función para listar las veterinarias
$datos = listarVeterinariasRegistradas();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Veterinarias</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:wght@300..800&display=swap"
        rel="stylesheet">

        <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <!-- Tus CSS -->
     
       
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/administracionStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/styleTableAdmin.css">
   
   <!-- Global Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">

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

        <!-- Include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_administrador.php'
        ?>


        <!-- ÁREA DE CONTENIDO - MÓDULO GESTIÓN DE VETERINARIAS -->

        <div class="area-contenido">

            <!-- Encabezado del Módulo -->
            <div class="encabezado-modulo">
                <h3>Lista Veterinarias Registradas</h3>
            </div>

            <!-- Controles de la Tabla -->
            <div class="controles-tabla">
                <div class="controles-izquierda">
                    <div class="campo-buscar">
                        <i class="bi bi-search"></i>
                        <input type="text" id="buscarVeterinaria" placeholder="Buscar Veterinaria...">
                    </div>
                </div>
                <div class="controles-derecha">
                    <button class="btn-control" id="btnVer">
                        <i class="bi bi-eye"></i> Ver 0/0
                    </button>
                    <button class="btn-control" id="btnFiltrar">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    <button class="btn-control" id="btnOrdenar">
                        <i class="bi bi-sort-down"></i> Ordenar
                    </button>
                    <button class="btn-control" id="btnExport">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <button class="btn-agregar" id="btnAgregarNuevo">
                        <i class="bi bi-plus-lg"></i> Agregar Nuevo
                    </button>
                </div>
            </div>

            <!-- Tabla de Citas -->
            <div class="contenedor-tabla">
                <table id="tablaListaVeterinarias" class="display tabla-admin" style="width:100%">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>nit</th>
                            <th>Nombre</th>
                            <th>Direccion</th>
                            <th>Ciudad</th>
                            <th>Telefono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($datos)) : ?>
                            <?php foreach ($datos as $veterinaria):  ?>
                                <tr class="fila-blanca">
                                    <td class="tb_foto"><?php if (!empty($veterinaria['foto'])): ?><img src="<?= BASE_URL ?>/public/uploads/veterinaria/<?= $veterinaria['foto'] ?>" alt=""><?php else: ?><i class="bi bi-image"></i><?php endif; ?></td>
                                    <td><?= $veterinaria['nit'] ?></td>
                                    <td><?= $veterinaria['nombre'] ?></td>
                                    <td><?= $veterinaria['direccion'] ?></td>
                                    <td><?= $veterinaria['ciudad'] ?></td>
                                    <td><?= $veterinaria['telefono'] ?></td>
                                    <td><?= $veterinaria['email'] ?></td>
                                    <td><?= $veterinaria['estado'] ?></td>
                                    
                                    
                                    <td>
                                        <button class="btn-accion btn-editar" title="Editar">
                                            <a href="<?= BASE_URL ?>/admin/editar-veterinaria?id=<?= $veterinaria['id_veterinaria'] ?>"><i class="bi bi-pencil"></i></a>
                                        </button>
                                        <button class="btn-accion btn-eliminar" title="Eliminar">
                                            <a href="<?= BASE_URL ?>/admin/eliminar-veterinaria?action=eliminar&id=<?= $veterinaria['id_veterinaria'] ?>"><i class="bi bi-trash"></i></a>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- SCRIPTS -->
        <!-- 1. jQuery PRIMERO -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

        <!-- 2. Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

        <!-- 3. DataTables JS -->
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        
        <!-- 5. Script de lista de veterinarias -->
        <script src="<?= BASE_URL ?>/public/assets/dashBoard/administrador/js/listaVeterinarias.js"></script>

        <!-- Modo dia  y noche -->
        <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>

        <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>

</body>

</html>