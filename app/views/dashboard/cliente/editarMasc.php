<?php
require_once BASE_PATH . '/app/controllers/mascotasController.php';

$mascotas = listarMascotas();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente - Mascotas VetCare</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/editaMas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">

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
            <div class="container-editar">

                <!-- HEADER -->
                <div class="header-editar">
                    <div class="header-info">
                        <h1>✏️ Editar Mascota</h1>
                        <p>Actualiza la información de </p>
                    </div>
                    <a href="#" class="btn-volver" onclick="history.back()">
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


                <!-- TABS -->
                <div class="form-card">
                    <?php foreach ($mascotas as $m): ?>
                        <div class="tabs-editar">
                            <button class="tab-btn active" onclick="cambiarTab(0)">
                                <i class="bi bi-info-circle"></i>
                                Datos de la Mascota a Editar
                            </button>
                        </div>


                        <!-- TAB 1: INFORMACIÓN BÁSICA -->
                        <div class="tab-content active">


                            <!-- FOTO -->
                            <div class="foto-editar-section">
                                <div class="foto-preview-container">
                                    <img src="<?= BASE_URL ?>/public/uploads/mascotas/<?= $m['img_mascota'] ?>" class="foto-preview-editar"
                                        id="fotoPreviewEditar"
                                        alt="Foto de Max">
                                    <label for="inputFotoEditar" class="btn-cambiar-foto-editar">
                                        <i class="bi bi-camera-fill"></i>
                                    </label>
                                    <input type="file" id="inputFotoEditar" class="input-foto-editar" accept="image/*" onchange="previewFotoEditar(event)">
                                </div>
                                <div class="foto-info-editar">
                                    <h4>Foto de Max</h4>
                                    <p>JPG, PNG o GIF (máx. 5MB)</p>
                                    <div class="foto-btns">
                                        <label for="inputFotoEditar" class="btn-foto btn-foto-primary">
                                            <i class="bi bi-upload"></i>
                                            Cambiar Foto
                                        </label>
                                        <button class="btn-foto btn-foto-danger" onclick="eliminarFotoEditar()">
                                            <i class="bi bi-trash"></i>
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>


                            <form id="formEditarBasico">

                                <!-- NOMBRE -->
                                <div class="form-group">
                                    <label>
                                        Nombre de la Mascota
                                        <span class="required">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="nombre"
                                        value="<?= $m['nombre'] ?>"
                                        placeholder="Nombre de tu mascota"
                                        required>
                                </div>


                                <!-- ESPECIE Y RAZA -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>
                                            Especie
                                            <span class="required">*</span>
                                        </label>
                                        <select id="especie" required>
                                            <option value="perro" selected>🐕 Perro</option>
                                            <option value="gato">🐈 Gato</option>
                                            <option value="ave">🦜 Ave</option>
                                            <option value="conejo">🐰 Conejo</option>
                                            <option value="otro">🐾 Otro</option>
                                        </select>
                                    </div>


                                    <div class="form-group">
                                        <label>
                                            Raza
                                            <span class="required">*</span>
                                        </label>
                                        <select id="raza" required>
                                            <option value="golden" selected>Golden Retriever</option>
                                            <option value="labrador">Labrador</option>
                                            <option value="pastor">Pastor Alemán</option>
                                            <option value="bulldog">Bulldog</option>
                                        </select>
                                    </div>
                                </div>


                                <!-- EDAD Y SEXO -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>
                                            Edad
                                            <span class="required">*</span>
                                        </label>
                                        <div style="display: flex; gap: 10px;">
                                            <input
                                                type="number"
                                                id="edad"
                                                value="<?= $m['edad'] ?>"
                                                min="0"
                                                max="50"
                                                style="flex: 2;"
                                                required>
                                            <select id="edadUnidad" style="flex: 1;">
                                                <option value="meses">Meses</option>
                                                <option value="años" selected>Años</option>
                                            </select>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label>
                                            Sexo
                                            <span class="required">*</span>
                                        </label>
                                        <div class="radio-group">
                                            <div class="radio-option">
                                                <input type="radio" name="<?= $m['sexo'] ?>" id="macho" value="macho" checked>
                                                <label for="macho" class="radio-label">
                                                    <i class="bi bi-gender-male"></i>
                                                    Macho
                                                </label>
                                            </div>
                                            <div class="radio-option">
                                                <input type="radio" name="<?= $m['sexo'] ?>" id="hembra" value="hembra">
                                                <label for="hembra" class="radio-label">
                                                    <i class="bi bi-gender-female"></i>
                                                    Hembra
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- COLOR Y PESO -->
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Color</label>
                                        <input
                                            type="text"
                                            id="color"
                                            value="<?= $m['color'] ?? ''  ?>"
                                            placeholder="Color del pelaje">
                                    </div>


                                    <!-- OBSERVACIONES -->
                                    <div class="form-group">
                                        <label>Observaciones</label>
                                        <textarea
                                            id="observaciones"
                                            placeholder="Información adicional, comportamiento, características especiales...">Mascota muy juguetona y sociable con niños.</textarea>
                                    </div>


                            </form>
                        </div>


                        <!-- BOTONES ACCIÓN -->
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="cancelarEdicion()">
                                <i class="bi bi-x-lg"></i>
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-primary" onclick="guardarCambios()">
                                <i class="bi bi-check-lg"></i>
                                Guardar Cambios
                            </button>
                        </div>

                    <?php endforeach; ?>
                </div>


            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>

    <script>
        // Cambiar Tab
        function cambiarTab(index) {
            // Ocultar todos los tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });


            // Remover active de botones
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });


            // Activar tab seleccionado
            document.querySelectorAll('.tab-content')[index].classList.add('active');
            document.querySelectorAll('.tab-btn')[index].classList.add('active');
        }


        // Preview Foto
        function previewFotoEditar(event) {
            const file = event.target.files[0];
            if (file) {
                // Validar tamaño
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen es demasiado grande. Máximo 5MB');
                    return;
                }


                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('fotoPreviewEditar').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }


        // Eliminar Foto
        function eliminarFotoEditar() {
            if (confirm('¿Estás seguro de eliminar la foto de Max?')) {
                document.getElementById('fotoPreviewEditar').src = 'https://ui-avatars.com/api/?name=Max&background=667eea&color=fff&size=200';
                document.getElementById('inputFotoEditar').value = '';
                alert('Foto eliminada correctamente');
            }
        }


        // Guardar Cambios
        function guardarCambios() {
            // Validar campos requeridos
            const nombre = document.getElementById('nombre').value.trim();
            const especie = document.getElementById('especie').value;
            const raza = document.getElementById('raza').value;
            const edad = document.getElementById('edad').value;


            if (!nombre || !especie || !raza || !edad) {
                alert('Por favor completa todos los campos obligatorios');
                return;
            }


            // Recopilar datos
            const datos = {
                nombre: nombre,
                especie: especie,
                raza: raza,
                edad: edad + ' ' + document.getElementById('edadUnidad').value,
                sexo: document.querySelector('input[name="sexo"]:checked').value,
                color: document.getElementById('color').value,
                peso: document.getElementById('peso').value,
                microchip: document.getElementById('microchip').value,
                observaciones: document.getElementById('observaciones').value,
                // Salud
                estadoSalud: document.getElementById('estadoSalud').value,
                alergias: document.getElementById('alergias').value,
                esterilizado: document.getElementById('esterilizado').checked,
                medicamentos: document.getElementById('medicamentos').value,
                tipoAlimento: document.getElementById('tipoAlimento').value,
                marcaAlimento: document.getElementById('marcaAlimento').value,
                // Config
                estadoRegistro: document.getElementById('estadoRegistro').value
            };


            console.log('Datos actualizados:', datos);


            // Mostrar éxito
            mostrarAlert();


            // Scroll al top
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }


        // Cancelar Edición
        function cancelarEdicion() {
            if (confirm('¿Descartar los cambios realizados?')) {
                window.history.back();
            }
        }


        // Confirmar Eliminar
        function confirmarEliminar() {
            const confirmar = prompt('Esta acción es PERMANENTE. Escribe "ELIMINAR" para confirmar:');
            if (confirmar === 'ELIMINAR') {
                alert('Mascota eliminada permanentemente');
                window.history.back();
            } else if (confirmar) {
                alert('Texto incorrecto. Eliminación cancelada.');
            }
        }


        // Mostrar Alert
        function mostrarAlert() {
            const alert = document.getElementById('alertSuccess');
            alert.classList.add('show');

            setTimeout(() => {
                alert.classList.remove('show');
            }, 4000);
        }


        console.log('✅ Formulario de edición cargado correctamente');
    </script>

</body>

</html>