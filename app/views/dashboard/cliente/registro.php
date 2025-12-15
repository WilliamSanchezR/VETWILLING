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

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/registro.css">

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
                    <h1>🐾 Registrar Nueva Mascota</h1>
                    <p>Completa la información de tu nueva mascota</p>
                </div>

                <!-- FORMULARIO -->
                <div class="form-card">

                    <!-- ALERT -->
                    <div class="alert alert-success" id="alertSuccess">
                        <i class="bi bi-check-circle-fill" style="font-size: 24px;"></i>
                        <div>
                            <strong>¡Éxito!</strong> Tu mascota ha sido registrada correctamente.
                        </div>
                    </div>

                    <div class="alert alert-error" id="alertError">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 24px;"></i>
                        <div>
                            <strong>Error:</strong> Por favor completa todos los campos obligatorios.
                        </div>
                    </div>

                    <!-- FOTO -->
                    <div class="foto-upload-section">
                        <div class="foto-preview-container">
                            <div class="foto-preview" id="fotoPreview">
                                🐕
                            </div>
                            <label for="inputFotoMascota" class="btn-cambiar-foto">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                            <input type="file" id="inputFotoMascota" class="input-foto" accept="image/*" onchange="previewFoto(event)">
                        </div>
                        <p class="foto-info">
                            <strong>Foto de tu mascota</strong><br>
                            JPG, PNG o GIF (máx. 5MB)
                        </p>
                    </div>

                    <!-- FORMULARIO -->
                    <form id="formRegistroMascota">

                        <!-- NOMBRE -->
                        <div class="form-group">
                            <label>
                                Nombre de la Mascota
                                <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                id="nombre"
                                placeholder="Ej: Max, Luna, Rocky..."
                                required>
                        </div>

                        <!-- ESPECIE Y RAZA -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    Especie
                                    <span class="required">*</span>
                                </label>
                                <select id="especie" required onchange="actualizarRazas()">
                                    <option value="">Selecciona una especie</option>
                                    <option value="perro">🐕 Perro</option>
                                    <option value="gato">🐈 Gato</option>
                                    <option value="ave">🦜 Ave</option>
                                    <option value="conejo">🐰 Conejo</option>
                                    <option value="hamster">🐹 Hámster</option>
                                    <option value="otro">🐾 Otro</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>
                                    Raza
                                    <span class="required">*</span>
                                </label>
                                <select id="raza" required disabled>
                                    <option value="">Selecciona primero una especie</option>
                                </select>
                            </div>
                        </div>

                        <!-- EDAD -->
                        <div class="form-group">
                            <label>
                                Edad
                                <span class="required">*</span>
                            </label>
                            <div class="edad-container">
                                <input
                                    type="number"
                                    id="edadNumero"
                                    class="edad-input"
                                    placeholder="Ej: 3"
                                    min="0"
                                    max="50"
                                    required>
                                <select id="edadUnidad" class="edad-unidad" required>
                                    <option value="meses">Meses</option>
                                    <option value="años" selected>Años</option>
                                </select>
                            </div>
                        </div>

                        <!-- SEXO -->
                        <div class="form-group">
                            <label>
                                Sexo
                                <span class="required">*</span>
                            </label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="sexo" id="macho" value="macho" required>
                                    <label for="macho" class="radio-label">
                                        <i class="bi bi-gender-male"></i>
                                        Macho
                                    </label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="sexo" id="hembra" value="hembra" required>
                                    <label for="hembra" class="radio-label">
                                        <i class="bi bi-gender-female"></i>
                                        Hembra
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- CAMPOS OPCIONALES -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Color</label>
                                <input
                                    type="text"
                                    id="color"
                                    placeholder="Ej: Blanco, Negro, Dorado...">
                            </div>

                            <div class="form-group">
                                <label>Peso (kg)</label>
                                <input
                                    type="number"
                                    id="peso"
                                    placeholder="Ej: 15.5"
                                    step="0.1"
                                    min="0">
                            </div>
                        </div>

                        <!-- INFORMACIÓN ADICIONAL -->
                        <div class="form-group">
                            <label>Información Adicional</label>
                            <input
                                type="text"
                                id="infoAdicional"
                                placeholder="Alergias, comportamiento, características especiales...">
                        </div>

                        <!-- BOTONES -->
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="cancelarRegistro()">
                                <i class="bi bi-x-lg"></i>
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-primary" onclick="guardarMascota()">
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
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>
    <!-- JavaScript -->
    <script>
        // Razas por especie
        const razasPorEspecie = {
            perro: [
                'Labrador Retriever',
                'Golden Retriever',
                'Pastor Alemán',
                'Bulldog',
                'Beagle',
                'Poodle',
                'Chihuahua',
                'Yorkshire Terrier',
                'Boxer',
                'Dachshund',
                'Husky Siberiano',
                'Shih Tzu',
                'Mestizo',
                'Otra raza'
            ],
            gato: [
                'Siamés',
                'Persa',
                'Maine Coon',
                'Bengalí',
                'Ragdoll',
                'Sphynx',
                'British Shorthair',
                'Abisinio',
                'Scottish Fold',
                'Mestizo',
                'Otra raza'
            ],
            ave: [
                'Canario',
                'Periquito',
                'Loro',
                'Cacatúa',
                'Agapornis',
                'Ninfa',
                'Otra especie'
            ],
            conejo: [
                'Mini Rex',
                'Holandés',
                'Cabeza de León',
                'Angora',
                'Belier',
                'Otra raza'
            ],
            hamster: [
                'Sirio',
                'Ruso',
                'Roborovski',
                'Chino',
                'Otra especie'
            ],
            otro: [
                'Especificar'
            ]
        };

        // Actualizar razas según especie
        function actualizarRazas() {
            const especieSelect = document.getElementById('especie');
            const razaSelect = document.getElementById('raza');
            const especie = especieSelect.value;

            // Limpiar opciones
            razaSelect.innerHTML = '<option value="">Selecciona una raza</option>';

            if (especie && razasPorEspecie[especie]) {
                razaSelect.disabled = false;
                razasPorEspecie[especie].forEach(raza => {
                    const option = document.createElement('option');
                    option.value = raza.toLowerCase().replace(/ /g, '-');
                    option.textContent = raza;
                    razaSelect.appendChild(option);
                });
            } else {
                razaSelect.disabled = true;
            }

            // Actualizar emoji de preview
            actualizarEmojiPreview(especie);
        }

        // Actualizar emoji del preview según especie
        function actualizarEmojiPreview(especie) {
            const preview = document.getElementById('fotoPreview');
            const emojis = {
                'perro': '🐕',
                'gato': '🐈',
                'ave': '🦜',
                'conejo': '🐰',
                'hamster': '🐹',
                'otro': '🐾'
            };

            if (!preview.querySelector('img')) {
                preview.textContent = emojis[especie] || '🐾';
            }
        }

        // Preview de foto
        function previewFoto(event) {
            const file = event.target.files[0];
            if (file) {
                // Validar tamaño (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen es demasiado grande. Máximo 5MB');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('fotoPreview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Foto mascota">`;
                }
                reader.readAsDataURL(file);
            }
        }

        // Guardar mascota
        function guardarMascota() {
            const form = document.getElementById('formRegistroMascota');

            // Validar campos requeridos
            const nombre = document.getElementById('nombre').value.trim();
            const especie = document.getElementById('especie').value;
            const raza = document.getElementById('raza').value;
            const edad = document.getElementById('edadNumero').value;
            const sexo = document.querySelector('input[name="sexo"]:checked');

            if (!nombre || !especie || !raza || !edad || !sexo) {
                mostrarAlert('error');
                return;
            }

            // Aquí enviarías los datos al servidor
            const datos = {
                nombre: nombre,
                especie: especie,
                raza: raza,
                edad: edad + ' ' + document.getElementById('edadUnidad').value,
                sexo: sexo.value,
                color: document.getElementById('color').value,
                peso: document.getElementById('peso').value,
                infoAdicional: document.getElementById('infoAdicional').value,
                foto: document.getElementById('inputFotoMascota').files[0]
            };

            console.log('Datos de mascota:', datos);

            // Mostrar éxito
            mostrarAlert('success');

            // Resetear formulario después de 2 segundos
            setTimeout(() => {
                form.reset();
                document.getElementById('fotoPreview').textContent = '🐕';
                document.getElementById('raza').disabled = true;
                ocultarAlerts();
            }, 2000);
        }

        // Cancelar registro
        function cancelarRegistro() {
            if (confirm('¿Estás seguro de cancelar el registro?')) {
                window.history.back();
            }
        }

        // Mostrar alert
        function mostrarAlert(tipo) {
            ocultarAlerts();
            const alert = document.getElementById(tipo === 'success' ? 'alertSuccess' : 'alertError');
            alert.classList.add('show');

            // Auto ocultar después de 5 segundos
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }

        // Ocultar alerts
        function ocultarAlerts() {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.classList.remove('show');
            });
        }

        console.log('✅ Formulario de registro cargado correctamente');
    </script>
</body>

</html>