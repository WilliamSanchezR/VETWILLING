<?php

// Incluir el helper de sesión para el administrador
require_once BASE_PATH . '/app/helpers/session_administrador.php';
// Enlazamos el controlador de usuario para listar los tickets
require_once BASE_PATH . '/app/controllers/ticketController.php';
require_once BASE_PATH . '/app/controllers/usuarioController.php';

$id = $_GET['id'];
$ticketData = consultarTicket($id);
$usuarioData = consultarUsuarioTicketId($ticketData['id_usuario']);

// Obtener el ID del usuario logueado
$usuarioLogueadoId = $_SESSION['user']['id_usuario'];

// Verificar si el usuario logueado es el asignado
$esAsignado = ($ticketData['id_asignado'] == $usuarioLogueadoId);
$esAdmin = ($_SESSION['user']['id_rol'] == 1);
// Solo el usuario asignado puede editar el ticket (incluso si no es admin)
$puedeEditar = $esAsignado && ($ticketData['estado'] != 'cerrado');
$ticketCerrado = ($ticketData['estado'] == 'cerrado');

// Relacionar estados internos con etiquetas legibles para UI
$estadoOpciones = [
    'abierto' => 'Abierto',
    'en_proceso' => 'En Proceso',
    'en_espera' => 'En Espera',
    'cerrado' => 'Cerrado',
];
$estadoActual = $ticketData['estado'] ?? '';
$estadoLabel = $estadoOpciones[$estadoActual] ?? ucfirst(str_replace('_', ' ', $estadoActual));

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
            <input type="hidden" name="id_usuario_auth" id="id_usuario_auth" value="<?= $usuarioLogueadoId ?>">

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
                            <span class="badge rounded-pill estado-badge estado-<?= htmlspecialchars($estadoActual) ?>"><?= htmlspecialchars($estadoLabel) ?></span>
                        </div>
                        <div>
                            <span class="label-ticket">Asignado a:</span>
                            <span><?= $ticketData['nombre_asignado'] . ' ' . $ticketData['apellido_asignado'] ?></span>
                        </div>

                        <div class="ticket-description">
                            <span class="label-ticket">Descripción:</span>
                            <p><?= $ticketData['descripcion'] ?></p>
                        </div>

                        <?php if (!empty($ticketData['archivo'])) : ?>
                            <div class="ticket-attachment">
                                <span class="label-ticket">Archivo Adjunto:</span>
                                <a href="<?= BASE_URL ?>/public/uploads/tickets/<?= $ticketData['archivo'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-paperclip"></i> Ver Archivo
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card ticket-user-info">
                    <h2>Información del usuario:</h2>
                    <div class="content-ticket">
                        <div>
                            <span class="label-ticket">Nombre del Usuario:</span>
                            <span id="nombre_usuario_creado"><?= $usuarioData['nombres'] . ' ' . $usuarioData['apellidos'] ?></span>
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

            <!-- TABS PARA GESTIONAR TICKET E HISTÓRICO -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="gestion-tab" data-bs-toggle="tab" data-bs-target="#gestion-content" type="button" role="tab">
                                <i class="bi bi-tools"></i> Gestionar Ticket
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="historico-tab" data-bs-toggle="tab" data-bs-target="#historico-content" type="button" role="tab">
                                <i class="bi bi-clock-history"></i> Histórico de Cambios
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content">
                    <!-- TAB 1: GESTIONAR TICKET -->
                    <div class="tab-pane fade show active" id="gestion-content" role="tabpanel">
                        <!-- si el ticket no tiene asignado muestre el combo de asignación (solo para administradores) -->
                        <?php if ($ticketData['id_asignado'] === null && $esAdmin) : ?>
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
                        <?php elseif ($ticketData['id_asignado'] === null && !$esAdmin) : ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                Este ticket aún no ha sido asignado. Espere a que un administrador lo asigne.
                            </div>
                        <?php endif; ?>

                        <!-- Si el ticket tiene asignado muestre el formulario de actualización -->
                        <?php if ($ticketData['id_asignado'] !== null) : ?>
                            <div class="container">
                                <form id="actualizarEstadoForm">
                                    <input type="hidden" name="id_ticket" id="id_ticket" value="<?= $ticketData['id'] ?>">
                                    <input type="hidden" id="estado_actual" value="<?= $ticketData['estado'] ?>">
                                    <input type="hidden" id="puede_editar" value="<?= $puedeEditar ? '1' : '0' ?>">
                                    <input type="hidden" id="ticket_cerrado" value="<?= $ticketCerrado ? '1' : '0' ?>">

                                    <div class="row title">
                                        <h2>Actualizar Estado del Ticket</h2>
                                        <?php if (!$puedeEditar): ?>
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle"></i>
                                                <?php if ($ticketCerrado): ?>
                                                    Este ticket está cerrado y no puede ser modificado.
                                                <?php elseif (!$esAsignado): ?>
                                                    Este ticket solo puede ser editado por el usuario asignado: <strong><?= $ticketData['nombre_asignado'] . ' ' . $ticketData['apellido_asignado'] ?></strong>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="estado_ticket" class="form-label">Seleccionar nuevo estado:</label>
                                            <select class="form-select" id="estado_ticket" name="estado_ticket" required <?= !$puedeEditar ? 'disabled' : '' ?>>
                                                <option value="" disabled selected>Seleccione un estado</option>
                                                <option value="abierto" <?= $ticketData['estado'] === 'abierto' ? 'selected' : '' ?>>Abierto</option>
                                                <option value="en_proceso" <?= $ticketData['estado'] === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                                <option value="en_espera" <?= $ticketData['estado'] === 'en_espera' ? 'selected' : '' ?>>En Espera</option>
                                                <option value="cerrado" <?= $ticketData['estado'] === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="reasignar_ticket" class="form-label">Reasignar Ticket:</label>
                                            <select class="form-select" id="reasignar_ticket" name="reasignar_ticket" <?= (!$puedeEditar || $ticketData['id_asignado'] === null) ? 'disabled' : '' ?>>
                                                <option value="" disabled selected>Seleccione un usuario para reasignar</option>
                                            </select>
                                            <?php if ($ticketData['estado'] === 'en_espera' || $ticketData['estado'] === 'cerrado'): ?>
                                                <small class="text-muted">No disponible durante "En Espera" o "Cerrado"</small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="solucion_ticket" class="form-label">
                                                Solución:
                                                <span class="text-danger" id="solucion_required" style="display: none;">*</span>
                                            </label>
                                            <textarea class="form-control" id="solucion_ticket" name="solucion_ticket" rows="3" placeholder="Ingrese la solución" <?= !$puedeEditar ? 'disabled' : '' ?>><?= htmlspecialchars($ticketData['resultado'] ?? '') ?></textarea>
                                            <?php if ($ticketData['estado'] === 'en_espera' || $ticketData['estado'] === 'cerrado'): ?>
                                                <small class="text-muted">Obligatorio</small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="row btn-actualizar">
                                            <button type="submit" id="btn-actualizar-estado" class="btn btn-primary" <?= !$puedeEditar ? 'disabled' : '' ?>>
                                                <i class="bi bi-check-circle"></i> Actualizar Ticket
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB 2: HISTÓRICO DE CAMBIOS -->
                    <div class="tab-pane fade" id="historico-content" role="tabpanel">
                        <div class="historico-container">
                            <h2 class="mb-4">Histórico de Cambios</h2>
                            <div class="timeline">

                                <!-- Aquí van los cambios dinámicos del histórico -->
                                <!-- Ejemplo de estructura para cambios -->
                                <div id="historicoItems"></div>

                                <!-- TEMPLATES para agregar dinámicamente con JavaScript -->
                                <template id="cambio-estado-template">
                                    <div class="timeline-item">
                                        <div class="timeline-marker estado">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h5>Cambio de Estado</h5>
                                            <p class="text-muted"><small>Fecha: <span class="fecha"></span></small></p>
                                            <p>Usuario: <strong><span class="usuario"></span></strong></p>
                                            <p>Estado anterior: <span class="badge bg-secondary estado-anterior"></span> → Estado nuevo: <span class="badge bg-success estado-nuevo"></span></p>
                                        </div>
                                    </div>
                                </template>

                                <template id="reasignacion-template">
                                    <div class="timeline-item">
                                        <div class="timeline-marker reasignacion">
                                            <i class="bi bi-person-check"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h5>Reasignación de Usuario</h5>
                                            <p class="text-muted"><small>Fecha: <span class="fecha"></span></small></p>
                                            <p>Realizado por: <strong><span class="usuario-cambio"></span></strong></p>
                                            <p>Usuario anterior: <span class="usuario-anterior"></span> → Nuevo usuario: <span class="badge bg-info usuario-nuevo"></span></p>
                                        </div>
                                    </div>
                                </template>

                                <template id="modificacion-template">
                                    <div class="timeline-item">
                                        <div class="timeline-marker modificacion">
                                            <i class="bi bi-pencil-square"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h5>Modificación de Descripción</h5>
                                            <p class="text-muted"><small>Fecha: <span class="fecha"></span></small></p>
                                            <p>Usuario: <strong><span class="usuario"></span></strong></p>
                                            <p><strong>Cambios:</strong></p>
                                            <p class="descripcion-cambio">
                                                <em>Anterior:</em><br>
                                                <span class="descripcion-anterior"></span><br><br>
                                                <em>Nuevo:</em><br>
                                                <span class="descripcion-nueva"></span>
                                            </p>
                                        </div>
                                    </div>
                                </template>

                                <template id="creacion-template">
                                    <div class="timeline-item">
                                        <div class="timeline-marker">
                                            <i class="bi bi-plus-circle"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h5>Ticket Creado</h5>
                                            <p class="text-muted"><small>Fecha: <span class="fecha"></span></small></p>
                                            <p>Por: <strong><span class="usuario"></span></strong></p>
                                        </div>
                                    </div>
                                </template>

                                <template id="asignacion-template">
                                    <div class="timeline-item">
                                        <div class="timeline-marker asignacion">
                                            <i class="bi bi-person-check"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h5>Asignación de Ticket</h5>
                                            <p class="text-muted"><small>Fecha: <span class="fecha"></span></small></p>
                                            <p>Realizado por: <strong><span class="usuario-cambio"></span></strong></p>
                                            <p>Usuario asignado: <span class="badge bg-info usuario-nuevo"></span></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
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