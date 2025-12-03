<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
require_once BASE_PATH . '/app/helpers/session_representante.php';

// enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos
require_once BASE_PATH . '/app/controllers/veterinarioController.php';

// asignamos el valor id del registro según la tabla
$id = $_GET['id'];

// Llamamos la funcion del controlador
$veterinario = listarVeterinario($id);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modulo edicion datos veterinario</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/formulario.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPacientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/nodoNoche.css">
</head>

<body>

    <?php
    include_once __DIR__ . '/../../layouts/sidebar_veterinario.php';
    include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php';
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php'; ?>

        <!-- ÁREA DE CONTENIDO -->
        <div class="wizard-container">
            <div class="wizard-header">
                <i class="bi bi-feather"></i>
                <h2>Edición datos Veterinario</h2>
                <p class="text-muted">Actualice los datos del veterinario</p>
            </div>

            <form id="vetForm" action="<?= BASE_URL ?>/veterinario/actualizar-veterinario" method="POST">

                <input type="hidden" name="id_usuario" value="<?= $veterinario['id_usuario'] ?>">
                <input type="hidden" name="accion" value="actualizar">

                <div class="step active">
                    <h3><i class="bi bi-person-badge me-2"></i>Datos del veterinario</h3>

                    <div class="row">
                        <!-- Nombres -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-person"></i> Nombres</label>
                                <input type="text" name="nombres"
                                    value="<?= $veterinario['nombres'] ?>"
                                    placeholder="Ej: Juan Pérez García">
                            </div>
                        </div>

                        <!-- Apellidos -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-person"></i> Apellidos</label>
                                <input type="text" id="apellidoPropietario" name="apellidos" value="<?= $veterinario['apellidos'] ?>" placeholder="Ej: García">
                            </div>
                        </div>

                        <!-- Tipo documento -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-card-text"></i> Tipo de documento</label>
                                <select name="tipo_documento">
                                    <option value="<?= $veterinario['tipo_documento'] ?>">
                                        <?= $veterinario['tipo_documento'] ?>
                                    </option>
                                    <option value="CC">Cédula de Ciudadanía</option>
                                    <option value="CE">Cédula de Extranjería</option>
                                    <option value="PAS">Pasaporte</option>
                                </select>
                            </div>
                        </div>

                        <!-- Rol -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Rol</label>
                                <select id="rol" name="id_rol">
                                    <option value="<?= $usuario['rol'] ?>"><?= $usuario['rol'] ?></option>
                                    <option value="2">Administrador</option>
                                    <option value="1">Veterinario</option>
                                </select>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Estado</label>
                                <select name="estado">
                                    <option value="<?= $veterinario['estado'] ?>">
                                        <?= $veterinario['estado'] ?>
                                    </option>
                                    <option value="Activo">Activo</option>
                                    <option value="Bloqueado">Bloqueado</option>
                                </select>
                            </div>
                        </div>

                        <!-- Num documento -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-hash"></i> Número de documento *</label>
                                <input type="number"
                                    name="numero_documento"
                                    value="<?= $veterinario['numero_documento'] ?>">
                            </div>
                        </div>

                        <!-- Teléfono -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-telephone"></i> Teléfono *</label>
                                <input type="tel" id="telefono" name="telefono" value="<?= $veterinario['telefono'] ?>" placeholder="+57 300 123 4567">
                            </div>
                        </div>

                        <!-- Correo -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-envelope"></i> Correo electrónico *</label>
                                <input type="email"
                                    name="email"
                                    value="<?= $veterinario['email'] ?>">
                            </div>
                        </div>

                    </div>

                    <div class="buttons">
                        <button type="submit" class="btn-next">
                            Guardar cambios <i class="bi bi-save"></i>
                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>

</body>

</html>