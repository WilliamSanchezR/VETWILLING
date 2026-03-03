<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pacientes | Historial Clínico</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleGestionClinica.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleGestionPacientesHistorial.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_veterinario.php' ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php' ?>

        <div class="area-contenido">
            <section class="historial-shell">
                <div class="encabezado-seccion encabezado-historial mb-0">
                    <div>
                        <h4>Gestión de Pacientes</h4>
                        <p class="subtitulo-historial mb-0">Historial clínico, control de acceso y trazabilidad por atención</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn-secundario" type="button">
                            <i class="bi bi-download me-1"></i> Exportar reporte
                        </button>
                        <button class="boton-agregar" type="button">
                            <i class="bi bi-plus-circle"></i> Nueva atención
                        </button>
                    </div>
                </div>

                <div class="historial-card mt-3">
                    <div class="bloque-titulo">
                        <h5><i class="bi bi-funnel me-2"></i>Filtros y búsqueda</h5>
                    </div>
                    <div class="filtros-grid">
                        <div>
                            <label class="label-historial">Paciente</label>
                            <input id="filtroPaciente" class="input-filtro" type="text" placeholder="Buscar por nombre del paciente...">
                        </div>
                        <div>
                            <label class="label-historial">Fecha</label>
                            <input id="filtroFecha" class="input-filtro" type="date">
                        </div>
                        <div>
                            <label class="label-historial">Veterinario</label>
                            <select id="filtroVeterinario" class="select-filtro">
                                <option value="">Todos</option>
                                <option value="Dra. María García">Dra. María García</option>
                                <option value="Dr. Juan Pérez">Dr. Juan Pérez</option>
                                <option value="Dra. Laura Gómez">Dra. Laura Gómez</option>
                            </select>
                        </div>
                        <div>
                            <label class="label-historial">Acceso</label>
                            <select id="filtroAcceso" class="select-filtro">
                                <option value="">Todos</option>
                                <option value="Autorizado">Autorizado</option>
                                <option value="Restringido">Restringido</option>
                            </select>
                        </div>
                        <div class="acciones-filtro">
                            <button id="btnFiltrar" class="boton-agregar btn-sm-fit" type="button"><i class="bi bi-search"></i> Filtrar</button>
                            <button id="btnLimpiar" class="btn-secundario btn-sm-fit" type="button"><i class="bi bi-arrow-counterclockwise"></i> Limpiar</button>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-xl-7">
                        <div class="historial-card h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Listado de atenciones</h5>
                                <span class="badge-sync"><i class="bi bi-arrow-repeat"></i> Actualización automática</span>
                            </div>
                            <div class="table-responsive tabla-wrapper">
                                <table class="tabla-historial" id="tablaHistoriales">
                                    <thead>
                                        <tr>
                                            <th>Paciente</th>
                                            <th>Fecha</th>
                                            <th>Veterinario</th>
                                            <th>Motivo</th>
                                            <th>Acceso</th>
                                            <th>Versión</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="active" data-paciente="Luna" data-especie="Canino" data-raza="Labrador"
                                            data-fecha="2026-03-02" data-vet="Dra. María García"
                                            data-motivo="Control postoperatorio" data-diagnostico="Evolución favorable"
                                            data-tratamiento="Curación, control de sutura" data-medicacion="Amoxicilina 250mg"
                                            data-observaciones="Próximo control en 7 días" data-acceso="Autorizado" data-version="v3.2">
                                            <td class="paciente-cell"><strong>Luna</strong><small>Canino · Labrador</small></td>
                                            <td>2026-03-02</td>
                                            <td>Dra. María García</td>
                                            <td>Control postoperatorio</td>
                                            <td><span class="badge-acceso permitido"><i class="bi bi-check-circle"></i> Autorizado</span></td>
                                            <td><span class="badge-version">v3.2</span></td>
                                            <td>
                                                <div class="acciones-registro">
                                                    <button class="btn-mini" title="Ver"><i class="bi bi-eye"></i></button>
                                                    <button class="btn-mini btn-editar" title="Editar"><i class="bi bi-pencil"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr data-paciente="Milo" data-especie="Felino" data-raza="Criollo"
                                            data-fecha="2026-03-01" data-vet="Dr. Juan Pérez"
                                            data-motivo="Vómito persistente" data-diagnostico="Gastroenteritis leve"
                                            data-tratamiento="Fluidoterapia y dieta blanda" data-medicacion="Omeprazol 5mg"
                                            data-observaciones="Monitoreo por 48 horas" data-acceso="Autorizado" data-version="v1.6">
                                            <td class="paciente-cell"><strong>Milo</strong><small>Felino · Criollo</small></td>
                                            <td>2026-03-01</td>
                                            <td>Dr. Juan Pérez</td>
                                            <td>Vómito persistente</td>
                                            <td><span class="badge-acceso permitido"><i class="bi bi-check-circle"></i> Autorizado</span></td>
                                            <td><span class="badge-version">v1.6</span></td>
                                            <td>
                                                <div class="acciones-registro">
                                                    <button class="btn-mini" title="Ver"><i class="bi bi-eye"></i></button>
                                                    <button class="btn-mini btn-editar" title="Editar"><i class="bi bi-pencil"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr data-paciente="Nina" data-especie="Canino" data-raza="Pug"
                                            data-fecha="2026-02-28" data-vet="Dra. Laura Gómez"
                                            data-motivo="Dificultad respiratoria" data-diagnostico="Síndrome braquicefálico"
                                            data-tratamiento="Nebulización y observación" data-medicacion="Prednisolona 10mg"
                                            data-observaciones="Solo personal autorizado puede modificar" data-acceso="Restringido" data-version="v4.1">
                                            <td class="paciente-cell"><strong>Nina</strong><small>Canino · Pug</small></td>
                                            <td>2026-02-28</td>
                                            <td>Dra. Laura Gómez</td>
                                            <td>Dificultad respiratoria</td>
                                            <td><span class="badge-acceso restringido"><i class="bi bi-lock"></i> Restringido</span></td>
                                            <td><span class="badge-version">v4.1</span></td>
                                            <td>
                                                <div class="acciones-registro">
                                                    <button class="btn-mini" title="Ver"><i class="bi bi-eye"></i></button>
                                                    <button class="btn-mini btn-editar" title="Editar"><i class="bi bi-pencil"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="historial-card h-100">
                            <h5><i class="bi bi-file-medical me-2"></i>Detalle y edición del historial</h5>

                            <div class="panel-seguridad">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <span class="badge-sync"><i class="bi bi-link-45deg"></i> Integrado con consultas</span>
                                    <span class="badge-version" id="detalleVersion">v3.2</span>
                                </div>
                                <div class="small text-muted mt-2">Paciente registrado: <strong id="detallePacienteRegistro">Sí</strong></div>
                                <div class="small text-muted">Acceso de edición: <strong id="detalleAcceso">Autorizado</strong></div>
                                <div class="small text-muted">Última actualización: <strong id="detalleActualizacion">03/03/2026 09:42</strong></div>
                            </div>

                            <form id="formHistorial" class="d-flex flex-column gap-2">
                                <div class="campos-grid">
                                    <div>
                                        <label class="label-historial">Nombre del paciente</label>
                                        <input id="campoPaciente" class="input-historial" type="text" value="Luna">
                                    </div>
                                    <div>
                                        <label class="label-historial">Fecha de atención</label>
                                        <input id="campoFecha" class="input-historial" type="date" value="2026-03-02">
                                    </div>
                                </div>

                                <div class="campos-grid">
                                    <div>
                                        <label class="label-historial">Especie</label>
                                        <input id="campoEspecie" class="input-historial" type="text" value="Canino">
                                    </div>
                                    <div>
                                        <label class="label-historial">Raza</label>
                                        <input id="campoRaza" class="input-historial" type="text" value="Labrador">
                                    </div>
                                </div>

                                <div>
                                    <label class="label-historial">Veterinario responsable</label>
                                    <input id="campoVeterinario" class="input-historial" type="text" value="Dra. María García">
                                </div>

                                <div>
                                    <label class="label-historial">Motivo de la consulta</label>
                                    <textarea id="campoMotivo" class="textarea-historial">Control postoperatorio</textarea>
                                </div>

                                <div>
                                    <label class="label-historial">Diagnóstico</label>
                                    <textarea id="campoDiagnostico" class="textarea-historial">Evolución favorable</textarea>
                                </div>

                                <div>
                                    <label class="label-historial">Tratamientos aplicados</label>
                                    <textarea id="campoTratamiento" class="textarea-historial">Curación, control de sutura</textarea>
                                </div>

                                <div>
                                    <label class="label-historial">Medicación recetada</label>
                                    <textarea id="campoMedicacion" class="textarea-historial">Amoxicilina 250mg</textarea>
                                </div>

                                <div>
                                    <label class="label-historial">Observaciones adicionales</label>
                                    <textarea id="campoObservaciones" class="textarea-historial">Próximo control en 7 días</textarea>
                                </div>

                                <div class="acciones-panel">
                                    <button id="btnGuardar" type="button" class="boton-agregar">
                                        <i class="bi bi-check2-circle"></i> Guardar cambios
                                    </button>
                                    <button type="button" class="btn-secundario">
                                        <i class="bi bi-file-earmark-text"></i> Generar resumen
                                    </button>
                                </div>
                            </form>

                            <div class="bloque-versionado">
                                <strong class="small d-block mb-2">Trazabilidad y versionado</strong>
                                <div class="item-version">
                                    <i class="bi bi-clock-history"></i>
                                    <div><strong>v3.2</strong> · 02/03/2026 18:20 · Actualización tras consulta</div>
                                </div>
                                <div class="item-version">
                                    <i class="bi bi-person-check"></i>
                                    <div><strong>v3.1</strong> · 27/02/2026 09:11 · Editado por Dra. María García</div>
                                </div>
                                <div class="item-version mb-0">
                                    <i class="bi bi-journal-plus"></i>
                                    <div><strong>v3.0</strong> · 25/02/2026 14:02 · Registro inicial en módulo de consultas</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabla = document.getElementById('tablaHistoriales');
            const filas = tabla ? tabla.querySelectorAll('tbody tr') : [];
            const btnFiltrar = document.getElementById('btnFiltrar');
            const btnLimpiar = document.getElementById('btnLimpiar');
            const btnGuardar = document.getElementById('btnGuardar');

            const campos = {
                paciente: document.getElementById('campoPaciente'),
                especie: document.getElementById('campoEspecie'),
                raza: document.getElementById('campoRaza'),
                fecha: document.getElementById('campoFecha'),
                vet: document.getElementById('campoVeterinario'),
                motivo: document.getElementById('campoMotivo'),
                diagnostico: document.getElementById('campoDiagnostico'),
                tratamiento: document.getElementById('campoTratamiento'),
                medicacion: document.getElementById('campoMedicacion'),
                observaciones: document.getElementById('campoObservaciones')
            };

            const detalleAcceso = document.getElementById('detalleAcceso');
            const detalleVersion = document.getElementById('detalleVersion');
            const detalleActualizacion = document.getElementById('detalleActualizacion');

            function cargarDetalle(fila) {
                if (!fila) return;

                filas.forEach(function(item) {
                    item.classList.remove('active');
                });
                fila.classList.add('active');

                campos.paciente.value = fila.dataset.paciente || '';
                campos.especie.value = fila.dataset.especie || '';
                campos.raza.value = fila.dataset.raza || '';
                campos.fecha.value = fila.dataset.fecha || '';
                campos.vet.value = fila.dataset.vet || '';
                campos.motivo.value = fila.dataset.motivo || '';
                campos.diagnostico.value = fila.dataset.diagnostico || '';
                campos.tratamiento.value = fila.dataset.tratamiento || '';
                campos.medicacion.value = fila.dataset.medicacion || '';
                campos.observaciones.value = fila.dataset.observaciones || '';

                detalleAcceso.textContent = fila.dataset.acceso || 'Autorizado';
                detalleVersion.textContent = fila.dataset.version || 'v1.0';
            }

            filas.forEach(function(fila) {
                fila.addEventListener('click', function() {
                    cargarDetalle(this);
                });
            });

            if (btnFiltrar) {
                btnFiltrar.addEventListener('click', function() {
                    const paciente = (document.getElementById('filtroPaciente').value || '').toLowerCase().trim();
                    const fecha = document.getElementById('filtroFecha').value || '';
                    const vet = document.getElementById('filtroVeterinario').value || '';
                    const acceso = document.getElementById('filtroAcceso').value || '';

                    filas.forEach(function(fila) {
                        const coincidePaciente = !paciente || (fila.dataset.paciente || '').toLowerCase().includes(paciente);
                        const coincideFecha = !fecha || fila.dataset.fecha === fecha;
                        const coincideVet = !vet || fila.dataset.vet === vet;
                        const coincideAcceso = !acceso || fila.dataset.acceso === acceso;

                        fila.style.display = coincidePaciente && coincideFecha && coincideVet && coincideAcceso ? '' : 'none';
                    });
                });
            }

            if (btnLimpiar) {
                btnLimpiar.addEventListener('click', function() {
                    document.getElementById('filtroPaciente').value = '';
                    document.getElementById('filtroFecha').value = '';
                    document.getElementById('filtroVeterinario').value = '';
                    document.getElementById('filtroAcceso').value = '';
                    filas.forEach(function(fila) {
                        fila.style.display = '';
                    });
                });
            }

            if (btnGuardar) {
                btnGuardar.addEventListener('click', function() {
                    const filaActiva = tabla.querySelector('tbody tr.active');
                    if (!filaActiva) return;

                    if ((filaActiva.dataset.acceso || '') === 'Restringido') {
                        alert('Registro restringido: solo personal autorizado puede modificar este historial.');
                        return;
                    }

                    const ahora = new Date();
                    const fechaHora = ahora.toLocaleDateString('es-CO') + ' ' + ahora.toLocaleTimeString('es-CO', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    detalleActualizacion.textContent = fechaHora;
                    alert('Cambios guardados (modo visual). El historial se marcaría como actualizado automáticamente.');
                });
            }

            const primeraFila = tabla ? tabla.querySelector('tbody tr.active') : null;
            if (primeraFila) {
                cargarDetalle(primeraFila);
            }
        });
    </script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>
</body>

</html>
