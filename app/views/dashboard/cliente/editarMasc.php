<?php
require_once BASE_PATH . '/app/controllers/mascotasController.php';


$id_mascota = $_GET['id'];
$mascota = consultarMascotaId($id_mascota);



?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mascota - VetCare</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/editaMas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/noche.css">
</head>

<body>
    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <!-- ÁREA DE CONTENIDO -->
        <div class="container-editar">

            <!-- HEADER -->
            <div class="header-editar">
                <div class="header-info">
                    <h1><i class="fa-solid fa-pencil"></i> Editar Mascota</h1>
                    <p>Actualiza la información de <strong><?= ($mascota['nombre']) ?></strong></p>
                </div>
                <a href="<?= BASE_URL ?>/cliente/mascotas" class="btn-volver">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            </div>

            <!-- ALERT -->
            <div class="alert alert-success" id="alertSuccess">
                <i class="bi bi-check-circle-fill" style="font-size: 24px;"></i>
                <div>
                    <strong>¡Actualizado!</strong> Los cambios se han guardado correctamente.
                </div>
            </div>

            <!-- FORMULARIO -->
            <div class="form-card">
                <div class="tabs-editar">
                    <button class="tab-btn active">
                        <i class="bi bi-info-circle"></i>
                        Datos de <?= ($mascota['nombre']) ?>
                    </button>
                </div>

                <!-- TAB: INFORMACIÓN -->
                <div class="tab-content active">



                    <form id="formEditarMascota"
                        action="<?= BASE_URL ?>/cliente/actualizar-mascota"
                        method="POST"
                        enctype="multipart/form-data">

                        <input type="hidden" name="id_mascota" value="<?= $mascota['id_paciente'] ?>">
                        <input type="hidden" name="accion" value="actualizar">



                        <!-- FOTO -->
                        <div class="foto-editar-section">
                            <div class="foto-preview-container">
                                <img src="<?= BASE_URL ?>/public/uploads/mascotas/<?= $mascota['img_mascota'] ?? 'default_pet.jpg' ?>"
                                    class="foto-preview-editar"
                                    id="fotoPreviewEditar"
                                    alt="Foto de <?= ($mascota['nombre']) ?>">
                                <label for="inputFotoEditar" class="btn-cambiar-foto-editar">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                                <input type="file" name="img_mascota" id="inputFotoEditar" class="input-foto-editar"
                                    accept="image/jpeg,image/jpg,image/png"
                                    onchange="previewFotoEditar(event)">
                            </div>
                            <div class="foto-info-editar">
                                <h4>Foto de <?= ($mascota['nombre']) ?></h4>
                                <p>JPG, PNG (máx. 5MB)</p>
                                <div class="foto-btns">
                                    <label for="inputFotoEditar" class="btn-foto btn-foto-primary">
                                        <i class="bi bi-upload"></i>
                                        Cambiar Foto
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- NOMBRE -->
                        <div class="form-group">
                            <label>
                                Nombre de la Mascota
                                <span class="required">*</span>
                            </label>
                            <input type="text"
                                id="nombre"
                                name="nombre"
                                value="<?= ($mascota['nombre']) ?>"
                                placeholder="Nombre de tu mascota"
                                required>
                        </div>

                        <!-- ESPECIE Y RAZA -->
                        <div class="form-row">
                            <!-- ESPECIE -->
                            <div class="form-group">
                                <label>
                                    Especie
                                    <span class="required">*</span>
                                </label>
                                <select id="especie" name="especie" required>
                                    <option value="Perro" <?= ($mascota['especie'] ?? '') == 'Perro' ? 'selected' : '' ?>> Perro</option>
                                    <option value="Gato" <?= ($mascota['especie'] ?? '') == 'Gato' ? 'selected' : '' ?>> Gato</option>
                                    <option value="Ave" <?= ($mascota['especie'] ?? '') == 'Ave' ? 'selected' : '' ?>> Ave</option>
                                    <option value="Conejo" <?= ($mascota['especie'] ?? '') == 'Conejo' ? 'selected' : '' ?>> Conejo</option>
                                    <option value="Hamster" <?= ($mascota['especie'] ?? '') == 'Hamster' ? 'selected' : '' ?>> Hámster</option>
                                    <option value="Otro" <?= ($mascota['especie'] ?? '') == 'Otro' ? 'selected' : '' ?>> Otro</option>
                                </select>
                            </div>

                            <!-- RAZA -->
                            <div class="form-group">
                                <label>
                                    Raza
                                    <span class="required">*</span>
                                </label>
                                <select id="raza" name="raza" required>
                                    <option value="">Selecciona una raza</option>
                                </select>
                            </div>
                        </div>
                </div>

                <script>
                    // 🎯 SISTEMA DE RAZAS DINÁMICAS PARA EDITAR
                    document.addEventListener('DOMContentLoaded', function() {
                        const especieSelect = document.getElementById('especie');
                        const razaSelect = document.getElementById('raza');

                        // Guardar la raza actual de la mascota
                        const razaActual = '<?= ($mascota['raza'] ?? '') ?>';
                        const especieActual = '<?= ($mascota['especie'] ?? '') ?>';

                        // 📋 Base de datos de razas por especie (igual que en registrar)
                        const razasPorEspecie = {
                            'Perro': [
                                'Labrador Retriever',
                                'Golden Retriever',
                                'Pastor Alemán',
                                'Bulldog Francés',
                                'Bulldog Inglés',
                                'Beagle',
                                'Poodle',
                                'Chihuahua',
                                'Yorkshire Terrier',
                                'Boxer',
                                'Dachshund (Salchicha)',
                                'Husky Siberiano',
                                'Shih Tzu',
                                'Rottweiler',
                                'Doberman',
                                'Pomerania',
                                'Border Collie',
                                'Cocker Spaniel',
                                'Schnauzer',
                                'Pitbull',
                                'Mestizo'
                            ],
                            'Gato': [
                                'Siamés',
                                'Persa',
                                'Maine Coon',
                                'Bengalí',
                                'Ragdoll',
                                'Sphynx (Sin pelo)',
                                'British Shorthair',
                                'Abisinio',
                                'Scottish Fold',
                                'Angora',
                                'Exótico',
                                'Birmano',
                                'Mestizo'
                            ],
                            'Ave': [
                                'Canario',
                                'Periquito',
                                'Loro',
                                'Cacatúa',
                                'Agapornis (Inseparable)',
                                'Ninfa (Cockatiel)',
                                'Guacamayo',
                                'Papagayo',
                                'Diamante mandarín',
                                'Perico australiano'
                            ],
                            'Conejo': [
                                'Mini Rex',
                                'Holandés',
                                'Cabeza de León',
                                'Angora',
                                'Belier',
                                'Toy',
                                'Californiano',
                                'Gigante de Flandes',
                                'Mini Lop'
                            ],
                            'Hamster': [
                                'Sirio (Dorado)',
                                'Ruso',
                                'Roborovski',
                                'Chino',
                                'Campbell'
                            ],
                            'Otro': [
                                'No aplica',
                                'Otra raza'
                            ]
                        };

                        // Función para cargar razas según especie
                        function cargarRazas(especie, razaPreseleccionada = null) {
                            razaSelect.innerHTML = '<option value="">Selecciona una raza</option>';

                            if (especie && razasPorEspecie[especie]) {
                                razasPorEspecie[especie].forEach(function(raza) {
                                    const option = document.createElement('option');
                                    option.value = raza;
                                    option.textContent = raza;

                                    // Pre-seleccionar la raza actual
                                    if (razaPreseleccionada && raza === razaPreseleccionada) {
                                        option.selected = true;
                                    }

                                    razaSelect.appendChild(option);
                                });

                                // Si la raza actual no está en la lista, agregarla
                                if (razaPreseleccionada && !razasPorEspecie[especie].includes(razaPreseleccionada)) {
                                    const option = document.createElement('option');
                                    option.value = razaPreseleccionada;
                                    option.textContent = razaPreseleccionada + ' (Personalizada)';
                                    option.selected = true;
                                    razaSelect.appendChild(option);
                                }
                            }
                        }

                        // 🎨 Cargar razas al iniciar con la especie actual
                        if (especieActual) {
                            cargarRazas(especieActual, razaActual);
                        }

                        // 🎨 Evento cuando se cambia la especie
                        especieSelect.addEventListener('change', function() {
                            const especieSeleccionada = this.value;
                            cargarRazas(especieSeleccionada);

                            // Animación suave
                            razaSelect.style.opacity = '0.5';
                            setTimeout(() => {
                                razaSelect.style.opacity = '1';
                            }, 100);
                        });
                    });
                </script>

                <style>
                    #raza {
                        transition: opacity 0.3s ease;
                    }
                </style>

                <!-- EDAD Y SEXO -->
                
                    <!-- Reemplaza el campo de EDAD en tu formulario con esto: -->

                    <!-- EDAD CON UNIDAD DE TIEMPO -->
                    <div class="form-group">
                        <label>
                            Edad de la Mascota
                            <span class="required">*</span>
                        </label>

                        <div class="edad-container" style="display: flex; gap: 10px; align-items: flex-start;">
                            <!-- Campo numérico -->
                            <div style="flex: 1;">
                                <input type="number"
                                    id="edad_numero"
                                    name="edad_numero"
                                    value="<?= ($mascota['edad_numero']) ?>"
                                    placeholder="Ej: 3"
                                    min="1"
                                    max="999"
                                    class="form-control"
                                    style="height: 50px; border: 2px solid #e0e0e0; border-radius: 8px;"
                                    >
                            </div>

                            <!-- Selector de unidad -->
                            <div style="flex: 1;">
                                <select id="edad_unidad"
                                    name="edad_unidad"
                                    class="form-control"
                                    style="height: 50px; border: 2px solid #e0e0e0; border-radius: 8px;"
                                    >
                                    <option value="<?= ($mascota['edad_unidad']) ?>"><?= ($mascota['edad_unidad']) ?></option>
                                    <option value="Dias">Días</option>
                                    <option value="Semanas">Semanas</option>
                                    <option value="Meses">Meses</option>
                                    <option value="Años">Años</option>
                                </select>
                            </div>
                        </div>

                        <!-- Mensaje de ayuda -->
                        <small class="text-muted" style="display: block; margin-top: 8px;">
                            <i class="bi bi-info-circle"></i>
                            Ejemplo: Para un cachorro de 3 meses, ingresa "3" y selecciona "Meses"
                        </small>
                    </div>

                    <style>
                        .edad-container .form-control:focus {
                            border-color: #1b8f72;
                            box-shadow: 0 0 0 0.2rem rgba(27, 143, 114, 0.25);
                            outline: none;
                        }
                    </style>

                    <script>
                        // Agregar este script al final de tu página (después del script de preview de imagen)
                        document.addEventListener('DOMContentLoaded', function() {
                            const edadNumero = document.getElementById('edad_numero');
                            const edadUnidad = document.getElementById('edad_unidad');

                            // Validación al cambiar la unidad
                            if (edadUnidad) {
                                edadUnidad.addEventListener('change', function() {
                                    const unidad = this.value;
                                    const numero = parseInt(edadNumero.value) || 0;

                                    // Ajustar el máximo según la unidad
                                    switch (unidad) {
                                        case 'dias':
                                            edadNumero.max = 365;
                                            edadNumero.placeholder = "Máx. 365 días";
                                            if (numero > 365) edadNumero.value = 365;
                                            break;
                                        case 'semanas':
                                            edadNumero.max = 208;
                                            edadNumero.placeholder = "Máx. 208 semanas";
                                            if (numero > 208) edadNumero.value = 208;
                                            break;
                                        case 'meses':
                                            edadNumero.max = 240;
                                            edadNumero.placeholder = "Máx. 240 meses";
                                            if (numero > 240) edadNumero.value = 240;
                                            break;
                                        case 'años':
                                            edadNumero.max = 30;
                                            edadNumero.placeholder = "Máx. 30 años";
                                            if (numero > 30) edadNumero.value = 30;
                                            break;
                                    }
                                });
                            }

                            // Validación en tiempo real del número
                            if (edadNumero) {
                                edadNumero.addEventListener('input', function() {
                                    const valor = parseInt(this.value);
                                    const max = parseInt(this.max);

                                    if (valor < 1) {
                                        this.value = 1;
                                    } else if (valor > max) {
                                        this.value = max;
                                    }
                                });
                            }
                        });
                    </script>

                    <div class="form-group">
                        <label>
                            Sexo
                            <span class="required">*</span>
                        </label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio"
                                    name="sexo"
                                    id="macho"
                                    value="Macho"
                                    <?= $mascota['sexo'] == 'Macho' ? 'checked' : '' ?>
                                    required>
                                <label for="macho" class="radio-label">
                                    <i class="bi bi-gender-male"></i>
                                    Macho
                                </label>
                            </div>
                            <div class="radio-option">
                                <input type="radio"
                                    name="sexo"
                                    id="hembra"
                                    value="Hembra"
                                    <?= $mascota['sexo'] == 'Hembra' ? 'checked' : '' ?>
                                    required>
                                <label for="hembra" class="radio-label">
                                    <i class="bi bi-gender-female"></i>
                                    Hembra
                                </label>
                            </div>
                        </div>
                    </div>
                

                <!-- BOTONES ACCIÓN -->
                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/cliente/mascotas" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary" onclick="guardarCambios()">
                        <i class="bi bi-check-lg"></i>
                        Guardar Cambios
                    </button>


                </div>

                </form>
            </div>



        </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/cliente/js/clientes.js"></script>

    <script>
        // Preview Foto
        function previewFotoEditar(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen es demasiado grande. Máximo 5MB');
                    event.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('fotoPreviewEditar').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }

        // Guardar Cambios


        console.log('✅ Formulario de edición cargado correctamente');
        console.log('Editando mascota ID:', <?= $mascota['id_paciente'] ?>);
    </script>

</body>

</html>