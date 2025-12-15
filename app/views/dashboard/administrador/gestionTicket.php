<?php

// Incluir el helper de sesión para el administrador
require_once BASE_PATH . '/app/helpers/session_administrador.php';
// Enlazamos el controlador de usuario para listar los tickets
require_once BASE_PATH . '/app/controllers/ticketController.php';
require_once BASE_PATH . '/app/controllers/usuarioController.php';

$id = $_GET['id'];
$ticketData = consultarTicket($id);
$usuarioData = consultarUsuarioTicketId($ticketData['id_usuario']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tickets</title>
    <!-- Icono de la página -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- SweetAlert2 - AGREGAR ANTES DE TUS SCRIPTS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Propio -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/administracionStyle.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/formularioAdminStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/gestionTicketStyles.css">


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
    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <!-- Include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_administrador.php'
        ?>

        <div class="wizard-container">
            <div class="wizard-header">
                <i class="bi bi-person-vcard"></i>
                <h2>Ticket</h2>
                <p class="text-muted">Complete todos los campos requeridos para gestinar el ticket</p>
            </div>

            <div class="infoTicket">
                <div class="card">
                    <div class="content-ticket">
                        <div>
                            <span class="label-ticket">Número de Ticket:</span>
                            <span class="label-ticket "><?= $ticketData['id'] ?></span>
                        </div>
                        <div>
                            <span class="label-ticket">Título:</span>
                            <span><?= $ticketData['titulo'] ?></span>
                        </div>
                        <div>
                            <span class="label-ticket">Fecha de Creación:</span>
                            <span><?= $ticketData['fecha_creacion'] ?></span>
                        </div>
                        <div>
                            <span class="label-ticket">Categoría:</span>
                            <span><?= $ticketData['categoria'] ?></span>
                        </div>
                        <div>
                            <span class="label-ticket">Prioridad:</span>
                            <span><?= $ticketData['prioridad'] ?></span>
                        </div>
                        <div>
                            <span class="label-ticket">Estado:</span>
                            <span><?= $ticketData['estado'] ?></span>
                        </div>
                        <div>
                            <span class="label-ticket">Asignado a:</span>
                            <span><?= $ticketData['nombre_asignado'] . ' ' . $ticketData['apellido_asignado'] ?></span>
                        </div>

                        <div class="ticket-description">
                            <span class="label-ticket">Descripción:</span>
                            <p><?= $ticketData['descripcion'] ?></p>
                        </div>

                    </div>
                </div>
                <div class="card ticket-user-info">
                    <h2>Información del usuario:</h2>
                    <div class="content-ticket">
                        <div>
                            <span class="label-ticket">Nombre del Usuario:</span>
                            <span><?= $usuarioData['nombres'] . ' ' . $usuarioData['apellidos'] ?></span>
                        </div>
                        <div>
                            <span class="label-ticket">Correo Electrónico:</span>
                            <span><?= $usuarioData['email'] ?></span>
                        </div>
                        <div>
                            <span class="label-ticket">Teléfono de Contacto:</span>
                            <span><?= $usuarioData['telefono'] ?></span>
                        </div>
                        <div>
                            <span class="label-ticket">Rol del Usuario:</span>
                            <span><?= $usuarioData['rol_name'] ?></span>
                        </div>
                        <div>
                            <span class="label-ticket">Veterinaria:</span>
                            <span><?= $usuarioData['nombre_veterinaria'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <form id="vetForm" action="<?= BASE_URL ?>/admin/actualizar-ticket" method="POST">

                <input type="hidden" name="accion" value="actualizar">
                <div class="step active">
                    <h3><i class="bi bi-motherboard"></i>Datos del ticket</h3>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-card-text"></i> Titulo*</label>
                                <input type="text" id="titulo" name="titulo" required placeholder="Ej: Problema con la cuenta" value="<?= $ticketData['titulo'] ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-hash"></i> Descripción *</label>
                            <input type="text" id="descripcion" name="descripcion" required placeholder="Descripción del problema" value="<?= $ticketData['descripcion'] ?>">
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-person"></i> Categoria*</label>
                            <input type="text" id="categoria" name="categoria" required placeholder="Ej: Soporte Técnico" value="<?= $ticketData['categoria'] ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-person"></i> Prioridad *</label>
                            <input type="text" id="prioridad" name="prioridad" required placeholder="Ej: Alta" value="<?= $ticketData['prioridad'] ?>">
                        </div>
                    </div>
                </div>


                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-envelope"></i> Estado *</label>
                            <input type="text" id="estado" name="estado" required placeholder="Estado del ticket" value="<?= $ticketData['estado'] ?>">
                        </div>
                    </div>

                </div>

                <div class="row">


                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-person-rolodex"></i> Rol *</label>
                            <select id="rol" name="rol" required disabled>
                                <option value="" disabled selected>Seleccione un rol</option>
                                <?php if (!empty($datosRol)) : ?>
                                    <?php foreach ($datosRol as $rol):  ?>
                                        <option value="<?= $rol['id_rol'] ?>" <?= $rol['id_rol'] == $usuarioData['id_rol'] ? 'selected' : '' ?>><?= $rol['nombre'] ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-card-text"></i> Estado *</label>
                            <select id="estado" name="estado" required>
                                <option value="<?= $ticketData['estado'] ?>"><?= $ticketData['estado'] ?></option>
                                <option value="Activo">Abierto</option>
                                <option value="Inactivo">En proceso</option>
                                <option value="Bloqueado">En espera</option>
                                <option value="Cerrado">Cerrado</option>

                            </select>
                        </div>
                    </div>

                    <?php if ($ticketData['id_rol'] !== 1 && $usuarioData['id_rol'] !== '1') : ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Ticket </label>
                                <select id="ticket" name="ticket" required>
                                    <option value="" disabled selected>Seleccione una veterinaria</option>
                                    <?php if (!empty($datosVeterinaria)) : ?>
                                        <?php foreach ($datosVeterinaria as $veterinaria):  ?>
                                            <option value="<?= $veterinaria['id_veterinaria'] ?>" <?= $veterinaria['id_veterinaria'] == $usuarioData['id_veterinaria'] ? 'selected' : '' ?>><?= $veterinaria['nombre'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>

                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="buttons">
                    <span></span>
                    <button type="submit" class="btn btn-success" id="btnGuardarVeterinaria">
                        Guardar <i class="bi bi-floppy"></i>
                    </button>
                </div>
        </div>
        </form> -->
        </div>
    </div>

</body>

<!-- SweetAlert2 - AGREGAR ANTES DE TUS SCRIPTS -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>

<!-- Propio -->
<script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>

</html>