<?php


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">


    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/website/css/registro.css">
</head>

<body>


    <div class="wizard-container">
        <div class="wizard-header">
            <i class="bi bi-building-check"></i>
            <h2>Registro de la Veterinaria y Representante Legal</h2>
            <p class="text-muted">Complete todos los campos requeridos para registrar la veterinaria y su representante legal</p>
        </div>



        <div class="progress-wrapper">
            <div class="progress">
                <div id="bar1" class="progress-bar active"></div>
                <div id="bar2" class="progress-bar"></div>
                <div id="bar3" class="progress-bar"></div>
            </div>
            <div class="progress-labels">
                <span class="active">Representante Legal</span>
                <span>Veterinaria</span>
                <span>Confirmar</span>
            </div>
        </div>




        <form id="vetForm" action="<?= BASE_URL ?>/registro/veterinaria" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="plan" id="plan" value="">
            <!-- Paso 1: Datos de la Veterinaria -->
            <div class="step ">
                <h3><i class="bi bi-motherboard"></i>Datos de la Veterinaria</h3>

                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-hash"></i> Nit *</label>
                            <input type="text" id="nit" name="nit" required placeholder="000.123.456-7">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-clipboard2-data"></i> Razón Social *</label>
                            <input type="text" id="nombreVeterinaria" name="nombreVeterinaria" required placeholder="Ej: Mundo Patitas S.A.S">
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-envelope"></i> Email *</label>
                            <input type="email" id="emailVeterinaria" name="emailVeterinaria" required placeholder="ejemplo@correo.com">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-telephone"></i> Teléfono *</label>
                            <input type="tel" id="telefonoVeterinaria" name="telefonoVeterinaria" required placeholder="+57 300 123 4567">
                        </div>
                    </div>

                </div>


                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-group">
                                <label><i class="bi bi-card-image"></i> Logo </label>
                                <input type="file" accept=".jpg, .png, .jpeg" id="fotoVeterinaria" name="fotoVeterinaria">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-pin-map"></i> Ciudad *</label>
                            <select name="ciudad" id="ciudad" required>
                                <option value="">Seleccione una ciudad</option>

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

                    <div class="col-md-12">
                        <div class="form-group">
                            <label><i class="bi bi-geo-alt"></i> Dirección *</label>
                            <input type="text" id="direccionVeterinaria" name="direccionVeterinaria" required placeholder="Calle 12 # 34-56, Barrio Centro">
                        </div>
                    </div>

                </div>

                <div class="buttons">
                    <span></span>
                    <button type="button" class="btn-next" onclick="nextStep()">
                        Siguiente <i class="bi bi-arrow-right"></i>
                    </button>
                </div>


            </div>

            <div class="step active">
                <h3><i class="bi bi-person-badge"></i>Datos del Representante Legal</h3>

                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-card-text"></i> Tipo de documento *</label>
                            <select id="tipoDocumento" name="tipo_documento" required>
                                <option value="">Seleccione...</option>
                                <option value="CC">Cédula de Ciudadanía</option>
                                <option value="CE">Cédula de Extranjería</option>
                                <option value="PAS">Pasaporte</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-hash"></i> Número de documento *</label>
                            <input type="number" id="documento" name="numero_documento" required placeholder="12345678">
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-person"></i> Nombres *</label>
                            <input type="text" id="nombres" name="nombres" required placeholder="Ej: Juan Martin">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-person"></i> Apellidos *</label>
                            <input type="text" id="apellidos" name="apellidos" required placeholder="Ej: Pérez García">
                        </div>
                    </div>
                </div>


                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-envelope"></i> Correo electrónico *</label>
                            <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-telephone"></i> Teléfono *</label>
                            <input type="tel" id="telefono" name="telefono" placeholder="+57 300 123 4567">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-camera"></i>Foto *</label>
                            <input type="file" accept=".jpg," id="img_perfil" name="img_perfil" placeholder="ejemplo@correo.com">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="bi bi-geo-alt"></i> Dirección *</label>
                            <input type="text" id="direccion" name="direccion" placeholder="Calle 123 #45-67">
                        </div>
                    </div>
                </div>

                <div class="buttons">
                    <button type="button" class="btn-prev" onclick="prevStep()">
                        <i class="bi bi-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn-next" onclick="nextStep()">
                        Siguiente <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

            </div>

            <div class="step">
                <h1>¿Deseas confirmar el envío del formulario?</h1>
                <p>Por favor, revisa que toda la información sea correcta antes de continuar.</p>

                <div class="buttons">
                    <button type="button" class="btn btn-secondary" id="btnVolver">Volver a revisar</button>
                    <button type="submit" class="btn btn-success" id="btnConfirmarVeterinaria">Confirmar y enviar</button>
                </div>
            </div>

        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script del registro -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/website/js/registro.js"></script>
</body>

</html>