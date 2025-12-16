<?php
require_once BASE_PATH . '/app/helpers/session_propietario.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente - Dashboard VetCare</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


    <!-- CSS -->
<<<<<<< HEAD
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/noche.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/cliente/css/registro.css">
=======
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/registro.css">

>>>>>>> 16633142215270c74cab9e0c178b4c442d37b5eb
</head>

<body>

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="contenido-principal">
            <!-- DASHBOARD CONTENT -->
            <div class="container-registro">

                <!-- HEADER -->
                <div class="header-registro">
                    <h1><i class="fa-solid fa-shield-dog"></i> Registrar Nueva Mascota</h1>
                    <p>Completa la información de tu nueva mascota</p>
                </div>

                <!-- FORMULARIO -->
                <div class="form-card">

                    <!-- FORMULARIO -->
                    <form id="formRegistroMascota"
                        action="<?= BASE_URL ?>/cliente/guardar-mascota"
                        method="POST"
                        enctype="multipart/form-data">

                        <!-- FOTO -->
                        <div class="foto-upload-section">
                            <div class="foto-preview-container">
                                <div class="foto-preview" id="fotoPreview">
                                    <i class="fa-solid fa-dog"></i>
                                </div>
                                <label for="inputFotoMascota" class="btn-cambiar-foto">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                                <input type="file"
                                    id="inputFotoMascota"
                                    class="input-foto"
                                    name="img_mascota"
                                    accept="image/jpeg,image/jpg,image/png">
                            </div>
                            <p class="foto-info">
                                <strong>Foto de tu mascota (opcional)</strong><br>
                                JPG, PNG (máx. 5MB)
                            </p>
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
                                placeholder="Ej: Max, Luna, Rocky..."
                                required>
                        </div>

                        <!-- ESPECIE Y RAZA -->
                        <!-- ESPECIE Y RAZA DINÁMICOS -->
                        <div class="form-row">
                            <!-- ESPECIE -->
                            <div class="form-group">
                                <label>
                                    Especie
                                    <span class="required">*</span>
                                </label>
                                <select id="especie" name="especie" class="form-control" required>
                                    <option value="">Selecciona una especie</option>
                                    <option value="Perro" data-icon="bi-dog">Perro</option>
                                    <option value="Gato" data-icon="bi-cat">Gato</option>
                                    <option value="Ave" data-icon="bi-feather">Ave</option>
                                    <option value="Conejo" data-icon="bi-rabbit">Conejo</option>
                                    <option value="Hamster" data-icon="bi-circle">Hámster</option>
                                    <option value="Otro" data-icon="bi-paw">Otro</option>
                                </select>
                            </div>

                            <!-- RAZA (SE FILTRA SEGÚN ESPECIE) -->
                            <div class="form-group">
                                <label>
                                    Raza
                                    <span class="required">*</span>
                                </label>
                                <select id="raza" name="raza" required disabled>
                                    <option value="">Primero selecciona una especie</option>
                                </select>
                            </div>
                        </div>

                        <script>
                            // 🎯 SISTEMA DE RAZAS DINÁMICAS
                            document.addEventListener('DOMContentLoaded', function() {
                                const especieSelect = document.getElementById('especie');
                                const razaSelect = document.getElementById('raza');

                                // 📋 Base de datos de razas por especie
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

                                // 🎨 Evento cuando se cambia la especie
                                especieSelect.addEventListener('change', function() {
                                    const especieSeleccionada = this.value;

                                    // Limpiar opciones anteriores
                                    razaSelect.innerHTML = '<option value="">Selecciona una raza</option>';

                                    if (especieSeleccionada && razasPorEspecie[especieSeleccionada]) {
                                        // Habilitar el select de raza
                                        razaSelect.disabled = false;

                                        // Agregar las razas correspondientes
                                        razasPorEspecie[especieSeleccionada].forEach(function(raza) {
                                            const option = document.createElement('option');
                                            option.value = raza;
                                            option.textContent = raza;
                                            razaSelect.appendChild(option);
                                        });

                                        // Animación suave
                                        razaSelect.style.opacity = '0.5';
                                        setTimeout(() => {
                                            razaSelect.style.opacity = '1';
                                        }, 100);

                                    } else {
                                        // Deshabilitar si no hay especie seleccionada
                                        razaSelect.disabled = true;
                                        razaSelect.innerHTML = '<option value="">Primero selecciona una especie</option>';
                                    }
                                });

                                // 💡 Mensaje de ayuda cuando el campo está deshabilitado
                                razaSelect.addEventListener('click', function() {
                                    if (this.disabled) {
                                        const especieInput = document.getElementById('especie');
                                        especieInput.focus();
                                        especieInput.style.borderColor = '#ff6b6b';
                                        setTimeout(() => {
                                            especieInput.style.borderColor = '';
                                        }, 1000);
                                    }
                                });
                            });
                        </script>

                        <style>
                            /* Estilos para transición suave */
                            #raza {
                                transition: opacity 0.3s ease, border-color 0.3s ease;
                            }

                            #raza:disabled {
                                background-color: #f5f5f5;
                                cursor: not-allowed;
                                opacity: 0.6;
                            }

                            #especie:focus {
                                border-color: #1b8f72 !important;
                                box-shadow: 0 0 0 0.2rem rgba(27, 143, 114, 0.25);
                            }
                        </style>

                        <!-- EDAD -->
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
                                        placeholder="Ej: 3"
                                        min="1"
                                        max="999"
                                        class="form-control"
                                        style="height: 54px; border: 2px solid #e0e0e0; border-radius: 8px;"
                                        required>
                                </div>

                                <!-- Selector de unidad -->
                                <div style="flex: 1;">
                                    <select id="edad_unidad"
                                        name="edad_unidad"
                                        class="form-control"
                                        style=" border: 2px solid #e0e0e0; border-radius: 8px;"
                                        required>
                                        <option value="">Seleccionar...</option>
                                        <option value="Dias">Días</option>
                                        <option value="Semanas">Semanas</option>
                                        <option value="Meses">Meses</option>
                                        <option value="Años" selected>Años</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Mensaje de ayuda -->
                            <small class="text-muted" style="display: block; margin-top: 8px;">
                                <i class="bi bi-info-circle"></i>
                                Ejemplo: Para un cachorro de 3 meses, ingresa "3" y selecciona "Meses"
                            </small>
                        </div>

                        <!-- <style>
                            .edad-container .form-control:focus {
                                border-color: #1b8f72;
                                box-shadow: 0 0 0 0.2rem rgba(17, 137, 107, 0.25);
                            }
                        </style> -->

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

                        <!-- SEXO -->
                        <div class="form-group">
                            <label>
                                Sexo
                                <span class="required">*</span>
                            </label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="sexo" id="macho" value="Macho" required>
                                    <label for="macho" class="radio-label">
                                        <i class="bi bi-gender-male"></i>
                                        Macho
                                    </label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="sexo" id="hembra" value="Hembra" required>
                                    <label for="hembra" class="radio-label">
                                        <i class="bi bi-gender-female"></i>
                                        Hembra
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- BOTONES -->
                        <div class="form-actions">
                            <a href="<?= BASE_URL ?>/cliente/perfil" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i>
                                Registrar Mascota
                            </button>
                        </div>

                    </form>

                    <!-- INFO CARDS -->
                    <div class="info-cards">
                        <div class="info-card">
                            <i class="bi bi-shield-check"></i>
                            <h4>Datos Seguros</h4>
                            <p>Tu información está protegida</p>
                        </div>
                        <div class="info-card">
                            <i class="bi bi-heart-pulse"></i>
                            <h4>Historial Médico</h4>
                            <p>Registro completo de salud</p>
                        </div>
                        <div class="info-card">
                            <i class="bi bi-bell"></i>
                            <h4>Recordatorios</h4>
                            <p>Alertas de vacunas y citas</p>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<<<<<<< HEAD
    <script src="<?= BASE_URL ?>/public/assets/dashboard/cliente/js/clientes.js"></script>

    <!-- Preview de imagen -->
=======
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>
    <!-- JavaScript -->
>>>>>>> 16633142215270c74cab9e0c178b4c442d37b5eb
    <script>
        // Preview de foto
        document.getElementById('inputFotoMascota').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamaño (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen es demasiado grande. Máximo 5MB');
                    this.value = '';
                    return;
                }

                // Validar tipo
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Solo se permiten archivos JPG, JPEG y PNG');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('fotoPreview').innerHTML =
                        `<img src="${event.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>