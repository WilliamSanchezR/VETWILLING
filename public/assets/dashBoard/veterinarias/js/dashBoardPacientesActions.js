$(document).ready(function () {
    const tablaPacientesDashboard = $('#tablaPacientesDashboard').DataTable({
        language: {
            decimal: '',
            emptyTable: 'No hay pacientes registrados',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ pacientes',
            infoEmpty: 'Mostrando 0 a 0 de 0 pacientes',
            infoFiltered: '(filtrado de _MAX_ pacientes totales)',
            lengthMenu: 'Mostrar _MENU_ pacientes',
            loadingRecords: 'Cargando...',
            processing: 'Procesando...',
            search: 'Buscar:',
            zeroRecords: 'No se encontraron pacientes',
            paginate: {
                first: 'Primera',
                last: 'Última',
                next: 'Siguiente',
                previous: 'Anterior'
            }
        },
        pageLength: 9,
        lengthMenu: [
            [9, 15, 25, 50, -1],
            [9, 15, 25, 50, 'Todas']
        ],
        order: [[0, 'asc']],
        columnDefs: [
            {
                targets: -1,
                orderable: false,
                searchable: false
            }
        ],
        dom: '<"row"<"col-sm-12"tr>>' + '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });

    $('#filtroEspecie').on('change', function () {
        tablaPacientesDashboard.column(1).search(this.value).draw();
    });

    $('#filtroEstado').on('change', function () {
        tablaPacientesDashboard.column(6).search(this.value).draw();
    });

    const pacientesEndpoint = document.body.dataset.pacientesEndpoint || '';
    if (!pacientesEndpoint) {
        return;
    }

    const asegurarEstilosModalPaciente = () => {
        const styleId = 'swal-paciente-styles';
        if (document.getElementById(styleId)) return;

        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            .swal2-popup .form-paciente {
                text-align: left;
            }

            .swal2-popup .form-grid-2 {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .swal2-popup .form-group-paciente {
                margin-bottom: 12px;
            }

            .swal2-popup .form-label-paciente {
                font-weight: 600;
                font-size: 13px;
                color: #00304d;
                margin-bottom: 6px;
                display: block;
            }

            .swal2-popup .form-control-paciente {
                width: 100%;
                padding: 10px 12px;
                border-radius: 8px;
                border: 1px solid #e0e0e0;
                font-size: 14px;
                outline: none;
                background: #ffffff;
            }

            .swal2-popup .form-control-paciente:focus {
                border-color: #0a932c;
                box-shadow: 0 0 0 3px rgba(10, 147, 44, 0.12);
            }

            .swal2-popup .form-divider-paciente {
                height: 1px;
                background: linear-gradient(to right, transparent, #e0e0e0, transparent);
                margin: 16px 0;
            }

            .popup-paciente {
                border-radius: 16px !important;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
            }

            .title-paciente {
                font-size: 24px !important;
                font-weight: 700 !important;
                color: #00304d !important;
                padding: 20px 20px 10px 20px !important;
            }

            .swal2-actions {
                gap: 12px !important;
                margin-top: 20px !important;
            }

            .btn-confirmar-paciente,
            .btn-cancelar-paciente {
                padding: 12px 30px !important;
                border-radius: 10px !important;
                font-weight: 600 !important;
                font-size: 15px !important;
                transition: all 0.3s ease !important;
                border: none !important;
                cursor: pointer !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 8px !important;
            }

            .btn-confirmar-paciente {
                background: linear-gradient(135deg, #0a932c 0%, #0a932c 100%) !important;
                color: #ffffff !important;
            }

            .btn-confirmar-paciente:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 8px 20px rgba(10, 147, 44, 0.35) !important;
            }

            .btn-cancelar-paciente {
                background: #f0f0f0 !important;
                color: #666666 !important;
            }

            .btn-cancelar-paciente:hover {
                background: #e0e0e0 !important;
            }
        `;
        document.head.appendChild(style);
    };

    const construirModalPaciente = (opciones = {}) => ({
        width: '700px',
        padding: '24px',
        background: '#ffffff',
        customClass: {
            popup: 'popup-paciente',
            title: 'title-paciente',
            confirmButton: 'btn-confirmar-paciente',
            cancelButton: 'btn-cancelar-paciente'
        },
        buttonsStyling: false,
        didOpen: asegurarEstilosModalPaciente,
        ...opciones
    });

    const escapeHtml = (value) => {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const obtenerIdPaciente = (boton) => {
        const idBoton = Number(boton.dataset.idPaciente || 0);
        if (idBoton > 0) return idBoton;
        const fila = boton.closest('tr');
        return Number(fila?.dataset.idPaciente || 0);
    };

    const consultarPaciente = async (idPaciente) => {
        const response = await fetch(pacientesEndpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                accion: 'consultar',
                id_paciente: idPaciente
            })
        });

        const payload = await response.json();
        if (!response.ok || payload.status !== 'success') {
            throw new Error(payload.message || 'No se pudo consultar el paciente.');
        }

        return payload.data;
    };

    $('#tablaPacientesDashboard tbody').on('click', '.btn-ver-detalle', async function () {
        const idPaciente = obtenerIdPaciente(this);
        if (!idPaciente) return;

        try {
            const data = await consultarPaciente(idPaciente);
            const paciente = data.paciente || {};
            const historial = Array.isArray(data.historial) ? data.historial : [];

            const historialHtml = historial.length > 0
                ? historial.map((item) => {
                    const fecha = item.fecha_hora ? new Date(item.fecha_hora).toLocaleString('es-CO') : 'Sin fecha';
                    const tipo = escapeHtml(item.tipo || 'Consulta');
                    const estado = escapeHtml(item.estado || 'Sin estado');
                    return `<li><strong>${tipo}</strong> · ${fecha} · ${estado}</li>`;
                }).join('')
                : '<li>Sin registros clínicos recientes</li>';

            Swal.fire(construirModalPaciente({
                title: `Paciente: ${escapeHtml(paciente.nombre || 'Sin nombre')}`,
                html: `
                    <div class="form-paciente">
                        <div class="form-group-paciente">
                            <label class="form-label-paciente">Especie</label>
                            <div class="form-control-paciente">${escapeHtml(paciente.especie || 'N/A')}</div>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group-paciente">
                                <label class="form-label-paciente">Raza</label>
                                <div class="form-control-paciente">${escapeHtml(paciente.raza || 'N/A')}</div>
                            </div>
                            <div class="form-group-paciente">
                                <label class="form-label-paciente">Edad</label>
                                <div class="form-control-paciente">${escapeHtml(paciente.edad_numero || '0')} ${escapeHtml(paciente.edad_unidad || '')}</div>
                            </div>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group-paciente">
                                <label class="form-label-paciente">Sexo</label>
                                <div class="form-control-paciente">${escapeHtml(paciente.sexo || 'N/A')}</div>
                            </div>
                            <div class="form-group-paciente">
                                <label class="form-label-paciente">Propietario</label>
                                <div class="form-control-paciente">${escapeHtml(paciente.propietario_nombre || 'N/A')}</div>
                            </div>
                        </div>
                        <div class="form-divider-paciente"></div>
                        <div class="form-group-paciente">
                            <label class="form-label-paciente">Resumen clínico reciente</label>
                            <ul style="padding-left:18px; margin:0; font-size:14px;">${historialHtml}</ul>
                        </div>
                    </div>
                `,
                confirmButtonText: 'Cerrar'
            }));
        } catch (error) {
            Swal.fire('Error', error.message || 'No se pudo cargar la información.', 'error');
        }
    });

    $('#tablaPacientesDashboard tbody').on('click', '.btn-editar', async function () {
        const idPaciente = obtenerIdPaciente(this);
        if (!idPaciente) return;

        try {
            const data = await consultarPaciente(idPaciente);
            const paciente = data.paciente || {};

            const result = await Swal.fire(construirModalPaciente({
                title: 'Editar paciente',
                html: `
                    <div class="form-paciente">
                        <div class="form-group-paciente">
                            <label class="form-label-paciente">Nombre</label>
                            <input id="swal-nombre" class="form-control-paciente" value="${escapeHtml(paciente.nombre || '')}">
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group-paciente">
                                <label class="form-label-paciente">Especie</label>
                                <select id="swal-especie" class="form-control-paciente">
                                <option value="Perro" ${(paciente.especie === 'Perro') ? 'selected' : ''}>Perro</option>
                                <option value="Gato" ${(paciente.especie === 'Gato') ? 'selected' : ''}>Gato</option>
                                <option value="Ave" ${(paciente.especie === 'Ave') ? 'selected' : ''}>Ave</option>
                                <option value="Conejo" ${(paciente.especie === 'Conejo') ? 'selected' : ''}>Conejo</option>
                                <option value="Hamster" ${(paciente.especie === 'Hamster') ? 'selected' : ''}>Hamster</option>
                                <option value="Otro" ${(paciente.especie === 'Otro') ? 'selected' : ''}>Otro</option>
                                </select>
                            </div>
                            <div class="form-group-paciente">
                                <label class="form-label-paciente">Raza</label>
                                <input id="swal-raza" class="form-control-paciente" value="${escapeHtml(paciente.raza || '')}">
                            </div>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group-paciente">
                                <label class="form-label-paciente">Edad número</label>
                                <input id="swal-edad-numero" type="number" min="1" class="form-control-paciente" value="${escapeHtml(paciente.edad_numero || '')}">
                            </div>
                            <div class="form-group-paciente">
                                <label class="form-label-paciente">Edad unidad</label>
                                <select id="swal-edad-unidad" class="form-control-paciente">
                                <option value="meses" ${(paciente.edad_unidad === 'meses') ? 'selected' : ''}>meses</option>
                                <option value="años" ${(paciente.edad_unidad === 'años') ? 'selected' : ''}>años</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group-paciente">
                            <label class="form-label-paciente">Sexo</label>
                            <select id="swal-sexo" class="form-control-paciente">
                                <option value="Macho" ${(paciente.sexo === 'Macho') ? 'selected' : ''}>Macho</option>
                                <option value="Hembra" ${(paciente.sexo === 'Hembra') ? 'selected' : ''}>Hembra</option>
                            </select>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar cambios',
                cancelButtonText: 'Cancelar',
                preConfirm: async () => {
                    const payload = {
                        accion: 'actualizar',
                        id_paciente: idPaciente,
                        nombre: document.getElementById('swal-nombre').value.trim(),
                        especie: document.getElementById('swal-especie').value,
                        raza: document.getElementById('swal-raza').value.trim(),
                        edad_numero: Number(document.getElementById('swal-edad-numero').value || 0),
                        edad_unidad: document.getElementById('swal-edad-unidad').value,
                        sexo: document.getElementById('swal-sexo').value
                    };

                    if (!payload.nombre || !payload.especie || !payload.raza || payload.edad_numero <= 0 || !payload.edad_unidad || !payload.sexo) {
                        Swal.showValidationMessage('Completa todos los campos obligatorios.');
                        return false;
                    }

                    try {
                        const response = await fetch(pacientesEndpoint, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const resultResponse = await response.json();
                        if (!response.ok || resultResponse.status !== 'success') {
                            throw new Error(resultResponse.message || 'No se pudo actualizar.');
                        }

                        return payload;
                    } catch (error) {
                        Swal.showValidationMessage(error.message || 'Error al actualizar.');
                        return false;
                    }
                }
            }));

            if (result.isConfirmed && result.value) {
                const fila = $(this).closest('tr');
                tablaPacientesDashboard.cell(fila, 0).data(escapeHtml(result.value.nombre));
                tablaPacientesDashboard.cell(fila, 1).data(escapeHtml(result.value.especie));
                tablaPacientesDashboard.cell(fila, 2).data(escapeHtml(result.value.raza));
                tablaPacientesDashboard.cell(fila, 3).data(`${result.value.edad_numero} ${escapeHtml(result.value.edad_unidad)}`);
                tablaPacientesDashboard.draw(false);

                Swal.fire('Actualizado', 'Los datos de la mascota fueron actualizados.', 'success');
            }
        } catch (error) {
            Swal.fire('Error', error.message || 'No se pudo cargar el formulario de edición.', 'error');
        }
    });

    $('#tablaPacientesDashboard tbody').on('click', '.btn-eliminar', async function () {
        const idPaciente = obtenerIdPaciente(this);
        if (!idPaciente) return;

        const confirmacion = await Swal.fire(construirModalPaciente({
            icon: 'warning',
            title: 'Desactivar paciente',
            text: 'Esta acción no elimina la mascota. Solo la dejará inactiva para este profesional.',
            showCancelButton: true,
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }));

        if (!confirmacion.isConfirmed) return;

        try {
            const response = await fetch(pacientesEndpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    accion: 'desactivar',
                    id_paciente: idPaciente
                })
            });

            const result = await response.json();
            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'No se pudo desactivar el paciente.');
            }

            const fila = $(this).closest('tr');
            tablaPacientesDashboard.row(fila).remove().draw(false);

            Swal.fire('Desactivado', 'La mascota quedó inactiva para este profesional.', 'success');
        } catch (error) {
            Swal.fire('Error', error.message || 'No se pudo completar la acción.', 'error');
        }
    });
});
