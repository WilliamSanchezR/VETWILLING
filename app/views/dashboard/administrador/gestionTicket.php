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
                        <div>
                            <span class="label-ticket">Especialidad:</span>
                            <span><?= $usuarioData['especialidad'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="infoTicket">
                <div class="card">
                    <!-- si el ticket no tiene asignado muestre el combo de asignación -->
                    <?php if ($ticketData['id_asignado'] === null) : ?>
                        <div class="asignar-ticket">
                            <h2>Asignar Ticket</h2>
                            <form id="asignarTicketForm">
                                <input type="hidden" name="id_ticket" id="id_ticket" value="<?= $ticketData['id'] ?>">
                                <div class="content-ticket">
                                    <div>
                                        <label for="usuario_asignado" class="form-label">Seleccionar Usuario a asignar:</label>
                                        <select class="form-select" id="usuario_asignado" name="usuario_asignado" required>
                                            <option value="" disabled selected>Seleccione un usuario</option>
                                        </select>
                                    </div>
                                    <div class="btn-asignar">
                                        <button type="submit" id="btn-asignar-ticket" class="btn btn-success">Asignar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
<script src="<?= BASE_URL ?>/public/assets/dashBoard/administrador/js/gestionTicket.js"></script>

</html>