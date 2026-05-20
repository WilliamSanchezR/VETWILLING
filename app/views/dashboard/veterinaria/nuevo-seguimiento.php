<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';

// Seguridad
$usuarioNombre = htmlspecialchars($_SESSION['user']['nombre'] ?? 'Veterinario');
$usuarioId     = (int)($_SESSION['user']['id_usuario'] ?? 0);
$veterinariaId = (int)($_SESSION['user']['id_veterinaria'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Seguimiento | Dashboard Veterinario – VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleGestionPacientesHistorial.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardSeguimientos.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/formulario.css">

    <style>
        .nuevo-seg-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }

        .nuevo-seg-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #e0e0e0;
        }

        .nuevo-seg-header i {
            font-size: 2rem;
            color: #00a884;
        }

        .nuevo-seg-header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: #1a1a1a;
        }

        .form-section {
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section h3 i {
            color: #00a884;
        }

        .form-group label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #00a884;
            box-shadow: 0 0 0 0.2rem rgba(0, 168, 132, 0.25);
        }

        .btn-seg-primario {
            background: #00a884;
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-seg-primario:hover {
            background: #008b6b;
            color: white;
        }

        .btn-secondary-seg {
            background: #f0f0f0;
            border: 1px solid #d0d0d0;
            color: #333;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-secondary-seg:hover {
            background: #e0e0e0;
            color: #333;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .required::after {
            content: " *";
            color: #e74c3c;
        }
    </style>
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar_veterinario.php' ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php' ?>

        <div class="area-contenido">
            <section class="nuevo-seg-container" aria-label="Nuevo seguimiento">

                <!-- Encabezado -->
                <div class="nuevo-seg-header">
                    <i class="bi bi-plus-circle-fill"></i>
                    <div>
                        <h1>Nuevo Seguimiento</h1>
                        <p style="margin: 0; color: #666; font-size: 0.9rem;">Crear un nuevo seguimiento de paciente</p>
                    </div>
                </div>

                <!-- Formulario -->
                <form id="formNuevoSeguimiento" method="POST" action="<?= BASE_URL ?>/veterinaria/api/seguimientos">
                    <input type="hidden" name="action" value="crear">

                    <!-- Sección: Seleccionar Paciente -->
                    <div class="form-section">
                        <h3><i class="bi bi-heart-pulse"></i> Seleccionar Paciente</h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="selectPaciente" class="required">Paciente</label>
                                    <select id="selectPaciente" name="id_paciente" class="form-select" required>
                                        <option value="">-- Seleccionar Paciente --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="selectPropietario">Propietario</label>
                                    <input type="text" id="selectPropietario" class="form-control" readonly placeholder="Se cargará automáticamente">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Información del Seguimiento -->
                    <div class="form-section">
                        <h3><i class="bi bi-clipboard-check"></i> Información del Seguimiento</h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="diagnostico" class="required">Diagnóstico</label>
                                    <textarea id="diagnostico" name="diagnostico" class="form-control" rows="3" required placeholder="Escriba el diagnóstico..."></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="observaciones">Observaciones</label>
                                    <textarea id="observaciones" name="observaciones" class="form-control" rows="3" placeholder="Observaciones adicionales..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prioridad" class="required">Prioridad</label>
                                    <select id="prioridad" name="prioridad" class="form-select" required>
                                        <option value="">-- Seleccionar --</option>
                                        <option value="baja">Baja</option>
                                        <option value="normal">Normal</option>
                                        <option value="alta">Alta</option>
                                        <option value="critica">Crítica</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado" class="required">Estado</label>
                                    <select id="estado" name="estado" class="form-select" required>
                                        <option value="">-- Seleccionar --</option>
                                        <option value="activo" selected>Activo</option>
                                        <option value="pausado">Pausado</option>
                                        <option value="completado">Completado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fechaInicio" class="required">Fecha de Inicio</label>
                                    <input type="date" id="fechaInicio" name="fecha_inicio" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proximaRevision">Próxima Revisión</label>
                                    <input type="date" id="proximaRevision" name="proxima_revision" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Medicación (opcional) -->
                    <div class="form-section">
                        <h3><i class="bi bi-capsule"></i> Medicación (Opcional)</h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="medicamento">Medicamento</label>
                                    <input type="text" id="medicamento" name="medicamento" class="form-control" placeholder="Nombre del medicamento">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dosis">Dosis</label>
                                    <input type="text" id="dosis" name="dosis" class="form-control" placeholder="Ej: 500mg, 2 veces al día">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="form-actions">
                        <a href="<?= BASE_URL ?>/veterinaria/seguimientos" class="btn-secondary-seg">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-seg-primario">
                            <i class="bi bi-check-lg"></i> Crear Seguimiento
                        </button>
                    </div>
                </form>

            </section>
        </div>
    </div>

    <!-- Toast container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer" style="z-index:11000;" aria-live="assertive" aria-atomic="true"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.BASE_URL = "<?= BASE_URL ?>";
        window.USUARIO_ID = <?= $usuarioId ?>;
        window.VETERINARIA_ID = <?= $veterinariaId ?>;
    </script>

    <script>
        // Script para cargar pacientes y manejar el formulario
        document.addEventListener('DOMContentLoaded', function () {
            const selectPaciente = document.getElementById('selectPaciente');
            const selectPropietario = document.getElementById('selectPropietario');
            const formNuevoSeguimiento = document.getElementById('formNuevoSeguimiento');
            const fechaInicio = document.getElementById('fechaInicio');

            // Establecer fecha actual como predeterminada
            const today = new Date().toISOString().split('T')[0];
            fechaInicio.value = today;

            // Cargar pacientes del veterinario
            async function cargarPacientes() {
                try {
                    const response = await fetch(`${window.BASE_URL}/veterinaria/api/seguimientos?action=listar`);
                    const data = await response.json();

                    if (data.status === 'success' && data.data) {
                        const pacientesUnicos = {};
                        
                        data.data.forEach(seg => {
                            const key = seg.id_paciente;
                            if (!pacientesUnicos[key]) {
                                pacientesUnicos[key] = {
                                    id: seg.id_paciente,
                                    nombre: seg.nombre_mascota,
                                    propietario: seg.nombre_propietario
                                };
                            }
                        });

                        // Llenar select de pacientes
                        Object.values(pacientesUnicos).forEach(paciente => {
                            const option = document.createElement('option');
                            option.value = paciente.id;
                            option.textContent = `${paciente.nombre} (${paciente.propietario})`;
                            selectPaciente.appendChild(option);
                        });
                    }
                } catch (error) {
                    console.error('Error al cargar pacientes:', error);
                }
            }

            // Actualizar propietario cuando se selecciona un paciente
            selectPaciente.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                const propietario = selectedOption.text.split('(')[1]?.replace(')', '') || '';
                selectPropietario.value = propietario;
            });

            // Manejar envío del formulario
            formNuevoSeguimiento.addEventListener('submit', async function (e) {
                e.preventDefault();

                const formData = new FormData(this);

                try {
                    const response = await fetch(`${window.BASE_URL}/veterinaria/api/seguimientos`, {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.status === 'success') {
                        showToast('Seguimiento creado exitosamente', 'success');
                        setTimeout(() => {
                            window.location.href = `${window.BASE_URL}/veterinaria/seguimientos`;
                        }, 1500);
                    } else {
                        showToast(data.message || 'Error al crear el seguimiento', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Error al crear el seguimiento', 'error');
                }
            });

            // Cargar pacientes al iniciar
            cargarPacientes();
        });

        // Función para mostrar toasts
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
</body>
</html>
