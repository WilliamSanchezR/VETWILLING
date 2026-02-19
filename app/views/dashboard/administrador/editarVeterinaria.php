<?php
require_once BASE_PATH . '/app/helpers/session_administrador.php';
//Enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos
require_once BASE_PATH . '/app/controllers/rolController.php';
// Enlazamos la ruta del controlador de veterinarias para listar las veterinarias
require_once BASE_PATH . '/app/controllers/veterinariaController.php';
// Enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos
require_once BASE_PATH . '/app/controllers/veterinariaController.php';

//Llamamos la funcion especifica que existe en dicho controloador
$datosRol = listarRolAdmin();

$datosVeterinaria = listarVeterinariasRegistradas();


$id = $_GET['id'];
$veterinariaData = consultarVeterinariasRegistradas($id);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Veterinaria</title>

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
            <form id="vetForm" action="<?= BASE_URL ?>/admin/actualizar-veterinaria" method="POST" enctype="multipart/form-data">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Veterinaria</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Representante Legal</button>
                    </li>

                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
                        <div class="wizard-header">
                            <i class="bi bi-person-vcard"></i>
                            <h2>Editar veterinaria</h2>
                            <p class="text-muted">Complete todos los campos requeridos para registrar la veterinaria</p>
                        </div>


                        <input type="hidden" name="id_veterinaria" value="<?= $veterinariaData['id_veterinaria'] ?>">
                        <input type="hidden" name="accion" value="actualizar">
                        <input type="hidden" name="foto_actual" value="<?= $veterinariaData['foto'] ?>">

                        <!-- Paso 1: Datos del Veterinaria -->
                        <div class="step active">
                            <h3><i class="bi bi-motherboard"></i>Datos de la Veterinaria</h3>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-hash"></i> Nit *</label>
                                        <input type="text" id="nit" name="nit" required placeholder="000.123.456-7" value="<?= $veterinariaData['nit'] ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-clipboard2-data"></i> Razón Social *</label>
                                        <input type="text" id="nombrePropietario" name="nombre" required placeholder="Ej: Juan Pérez García" value="<?= $veterinariaData['nombre'] ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-envelope"></i> Email *</label>
                                        <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com" value="<?= $veterinariaData['email'] ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-telephone"></i> Teléfono *</label>
                                        <input type="tel" id="telefono" name="telefono" required placeholder="+57 300 123 4567" value="<?= $veterinariaData['telefono'] ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <label><i class="bi bi-envelope"></i> Logo </label>
                                            <input type="file" accept=".jpg, .png, .jpeg" id="foto" name="foto">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-pin-map"></i>Ciudad *</label>
                                        <select name="ciudad" id="ciudad" required>
                                            <option value="<?= $veterinariaData['ciudad'] ?>"><?= $veterinariaData['ciudad'] ?></option>

                                            <!-- Principales ciudades -->
                                            <option value="Bogotá">Bogotá</option>
                                            <option value="Medellín">Medellín</option>
                                            <option value="Cali">Cali</option>
                                            <option value="Barranquilla">Barranquilla</option>
                                            <option value="Cartagena">Cartagena</option>
                                            <option value="Bucaramanga">Bucaramanga</option>
                                            <option value="Cúcuta">Cúcuta</option>
                                            <option value="Pereira">Pereira</option>
                                            <option value="Manizales">Manizales</option>
                                            <option value="Armenia">Armenia</option>
                                            <option value="Ibagué">Ibagué</option>
                                            <option value="Villavicencio">Villavicencio</option>
                                            <option value="Neiva">Neiva</option>
                                            <option value="Pasto">Pasto</option>
                                            <option value="Popayán">Popayán</option>
                                            <option value="Tunja">Tunja</option>
                                            <option value="Montería">Montería</option>
                                            <option value="Sincelejo">Sincelejo</option>
                                            <option value="Valledupar">Valledupar</option>
                                            <option value="Riohacha">Riohacha</option>
                                            <option value="Santa Marta">Santa Marta</option>
                                            <option value="San Andrés">San Andrés</option>
                                            <option value="Leticia">Leticia</option>
                                            <option value="Mocoa">Mocoa</option>
                                            <option value="Yopal">Yopal</option>
                                            <option value="Arauca">Arauca</option>
                                            <option value="Florencia">Florencia</option>
                                            <option value="Quibdó">Quibdó</option>
                                            <option value="Inírida">Inírida</option>
                                            <option value="Mitú">Mitú</option>
                                            <option value="Puerto Carreño">Puerto Carreño</option>

                                            <!-- Área metropolitana y ciudades comunes -->
                                            <option value="Soacha">Soacha</option>
                                            <option value="Chía">Chía</option>
                                            <option value="Zipaquirá">Zipaquirá</option>
                                            <option value="Girardot">Girardot</option>
                                            <option value="Facatativá">Facatativá</option>
                                            <option value="Fusagasugá">Fusagasugá</option>

                                            <option value="Envigado">Envigado</option>
                                            <option value="Itagüí">Itagüí</option>
                                            <option value="Bello">Bello</option>
                                            <option value="Rionegro">Rionegro</option>

                                            <option value="Palmira">Palmira</option>
                                            <option value="Tuluá">Tuluá</option>
                                            <option value="Buenaventura">Buenaventura</option>

                                            <option value="Dosquebradas">Dosquebradas</option>
                                            <option value="La Dorada">La Dorada</option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-geo-alt"></i> Dirección *</label>
                                        <input type="text" id="direccion" name="direccion" required placeholder="Ej: Calle 12 # 34-56" value="<?= $veterinariaData['direccion'] ?>">
                                    </div>
                                </div>

                            </div>


                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">
                        <div class="wizard-header">
                            <i class="bi bi-person-vcard"></i>
                            <h2>Editar Representante Legal</h2>
                            <p class="text-muted">Complete todos los campos requeridos para editar el representante legal</p>
                        </div>


                        <input type="hidden" name="id_usuario" value="<?= $veterinariaData['id_usuario'] ?>">
                        <input type="hidden" name="id_rol" value="<?= $veterinariaData['id_rol'] ?>">
                        <input type="hidden" name="accion" value="actualizar">
                        <!-- Paso 1: Datos del Usuario -->
                        <div class="step active">
                            <h3><i class="bi bi-motherboard"></i>Datos del Usuario</h3>

                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-card-text"></i> Tipo de documento *</label>
                                        <select id="tipoDocumento" name="tipo_documento" required>
                                            <option value="<?= $veterinariaData['tipo_documento'] ?>"><?= $veterinariaData['tipo_documento'] ?></option>
                                            <option value="CC">Cédula de Ciudadanía</option>
                                            <option value="CE">Cédula de Extranjería</option>
                                            <option value="PAS">Pasaporte</option>

                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-hash"></i> Número de documento *</label>
                                        <input type="number" id="documento" name="numero_documento" required placeholder="12345678" value="<?= $veterinariaData['numero_documento'] ?>">
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-person"></i> Nombres *</label>
                                        <input type="text" id="nombres" name="nombres" required placeholder="Ej: Juan Martin" value="<?= $veterinariaData['nombres'] ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-person"></i> Apellidos *</label>
                                        <input type="text" id="apellidos" name="apellidos" required placeholder="Ej: Pérez García" value="<?= $veterinariaData['apellidos'] ?>">
                                    </div>
                                </div>
                            </div>


                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-envelope"></i> Correo electrónico *</label>
                                        <input type="email" id="email_user" name="email_user" required placeholder="ejemplo@correo.com" value="<?= $veterinariaData['email_user'] ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-telephone"></i> Teléfono *</label>
                                        <input type="tel" id="telefono_user" name="telefono_user" placeholder="+57 300 123 4567" required value="<?= $veterinariaData['telefono_user'] ?>">
                                    </div>
                                </div>



                            </div>

                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-geo-alt"></i> Dirección *</label>
                                        <input type="text" id="direccion_user" name="direccion_user" placeholder="Calle 123 #45-67" required value="<?= $veterinariaData['direccion_user'] ?>">
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-person-rolodex"></i> Rol *</label>
                                        <select id="rol" name="rol" required disabled>
                                            <option value="" disabled selected>Seleccione un rol</option>
                                            <?php if (!empty($datosRol)) : ?>
                                                <?php foreach ($datosRol as $rol):  ?>
                                                    <option value="<?= $rol['id_rol'] ?>" <?= $rol['id_rol'] == $veterinariaData['id_rol'] ? 'selected' : '' ?>><?= $rol['nombre'] ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="bi bi-card-text"></i> Estado *</label>
                                        <select id="estado" name="estado_user" required>
                                            <option value="<?= $veterinariaData['estado'] ?>"><?= $veterinariaData['estado_user'] ?></option>
                                            <option value="activo">Activo</option>
                                            <option value="inactivo">Inactivo</option>
                                            <option value="bloqueado">Bloqueado</option>

                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="buttons">
                        <span></span>
                        <button type="submit" class="btn btn-success" id="btnGuardarVeterinaria">
                            Guardar <i class="bi bi-floppy"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>


    </div>
    </div>


    <!-- SweetAlert2 - AGREGAR ANTES DE TUS SCRIPTS -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>

</body>

</html>