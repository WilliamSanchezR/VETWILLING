// FUNCIONALIDAD DE TABS
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remover active de todos
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filtro = this.dataset.tab;
                filtrarCitas(filtro);
            });
        });

        function filtrarCitas(filtro) {
            const citas = document.querySelectorAll('.cita-card');
            
            citas.forEach(cita => {
                const estado = cita.dataset.estado;
                
                if (filtro === 'todas') {
                    cita.style.display = 'grid';
                } else if (filtro === 'pendientes' && estado === 'pendiente') {
                    cita.style.display = 'grid';
                } else if (filtro === 'confirmadas' && estado === 'confirmada') {
                    cita.style.display = 'grid';
                } else if (filtro === 'completadas' && estado === 'completada') {
                    cita.style.display = 'grid';
                } else {
                    cita.style.display = 'none';
                }
            });

            verificarCitasVisibles();
        }

        function verificarCitasVisibles() {
            const citasVisibles = document.querySelectorAll('.cita-card[style="display: grid;"]').length;
            const citasLista = document.getElementById('citasLista');

            if (citasVisibles === 0) {
                if (!document.querySelector('.empty-state')) {
                    citasLista.innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <h3>No hay citas</h3>
                            <p>No se encontraron citas con los filtros aplicados</p>
                        </div>
                    `;
                }
            }
        }

        // BÚSQUEDA
        document.getElementById('buscarCita').addEventListener('input', function(e) {
            const termino = e.target.value.toLowerCase();
            const citas = document.querySelectorAll('.cita-card');

            citas.forEach(cita => {
                const texto = cita.textContent.toLowerCase();
                cita.style.display = texto.includes(termino) ? 'grid' : 'none';
            });

            verificarCitasVisibles();
        });

        // ACCIONES DE BOTONES
        document.querySelectorAll('.btn-confirmar').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.cita-card');
                const badge = card.querySelector('.estado-badge');
                
                badge.textContent = 'Confirmada';
                badge.className = 'estado-badge confirmada';
                card.dataset.estado = 'confirmada';

                mostrarNotificacion('Cita confirmada exitosamente', 'success');
            });
        });

        document.querySelectorAll('.btn-completar').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.cita-card');
                const badge = card.querySelector('.estado-badge');
                
                badge.textContent = 'Completada';
                badge.className = 'estado-badge completada';
                card.dataset.estado = 'completada';

                mostrarNotificacion('Cita marcada como completada', 'success');
            });
        });

        document.querySelectorAll('.btn-cancelar').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('¿Está seguro de cancelar esta cita?')) {
                    const card = this.closest('.cita-card');
                    const badge = card.querySelector('.estado-badge');
                    
                    badge.textContent = 'Cancelada';
                    badge.className = 'estado-badge cancelada';
                    card.dataset.estado = 'cancelada';

                    mostrarNotificacion('Cita cancelada', 'warning');
                }
            });
        });

        document.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', function() {
                mostrarNotificacion('Función de edición en desarrollo', 'info');
            });
        });

        // NUEVA CITA
        window.nuevaCita = async function() {
            // Cargar script de SweetAlert2 si no está cargado
            if (typeof Swal === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                document.head.appendChild(script);
                
                await new Promise((resolve) => {
                    script.onload = resolve;
                });
            }

            // Obtener fecha y hora actual
            const ahora = new Date();
            
            // Establecer hora de inicio (redondear a la próxima hora)
            const fechaInicio = new Date(ahora);
            fechaInicio.setMinutes(0, 0, 0);
            if (ahora.getMinutes() > 0) {
                fechaInicio.setHours(fechaInicio.getHours() + 1);
            }
            
            // Establecer hora de fin (1 hora después)
            const fechaFin = new Date(fechaInicio);
            fechaFin.setHours(fechaFin.getHours() + 1);
            
            // Formatear fechas para los inputs
            const fechaInicioStr = formatDateForInput(fechaInicio);
            const fechaFinStr = formatDateForInput(fechaFin);
            
            try {
                // Cargar datos desde el servidor
                console.log('Cargando propietarios...');
                const propietarios = await cargarPropietarios();
                console.log('Propietarios cargados:', propietarios);
                
                console.log('Cargando subservicios...');
                const subservicios = await cargarSubservicios();
                console.log('Subservicios cargados:', subservicios);
                
                // Crear opciones HTML para propietarios
                let propietariosOptions = '<option value="">Selecciona un propietario...</option>';
                propietarios.forEach(prop => {
                    propietariosOptions += `<option value="${prop.id_propietario}">${prop.nombres} ${prop.apellidos}</option>`;
                });
                
                // Crear opciones HTML para subservicios
                let serviciosOptions = '<option value="">Selecciona un servicio...</option>';
                subservicios.forEach(sub => {
                    const precio = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(sub.costo);
                    serviciosOptions += `<option value="${sub.id_subservicio}">${sub.nombre_subservicio} - ${precio}</option>`;
                });
                
                // Mostrar SweetAlert con formulario
                const result = await Swal.fire({
                    title: '<i class="bi bi-calendar-plus" style="color: #0a932c;"></i> Nueva Cita',
                    html: `
                        <style>
                            .form-agendamiento {
                                max-height: 500px;
                                overflow-y: auto;
                                padding: 0 5px;
                            }
                            
                            .form-agendamiento::-webkit-scrollbar {
                                width: 8px;
                            }
                            
                            .form-agendamiento::-webkit-scrollbar-track {
                                background: #f1f1f1;
                                border-radius: 10px;
                            }
                            
                            .form-agendamiento::-webkit-scrollbar-thumb {
                                background: #888;
                                border-radius: 10px;
                            }
                            
                            .form-agendamiento::-webkit-scrollbar-thumb:hover {
                                background: #555;
                            }
                            
                            .form-group-ag {
                                margin-bottom: 24px;
                            }
                            
                            .form-label-ag {
                                display: flex;
                                align-items: center;
                                gap: 8px;
                                margin-bottom: 10px;
                                font-weight: 600;
                                color: #00304D;
                                font-size: 14px;
                            }
                            
                            .form-label-ag i {
                                font-size: 16px;
                                color: #0a932c;
                            }
                            
                            .form-label-ag .required {
                                color: #e74c3c;
                                margin-left: 2px;
                            }
                            
                            .form-control-ag {
                                width: 100%;
                                padding: 12px 16px;
                                border: 2px solid #e0e0e0;
                                border-radius: 10px;
                                font-size: 14px;
                                font-family: inherit;
                                transition: all 0.3s ease;
                                background: #ffffff;
                                box-sizing: border-box;
                            }
                            
                            .form-control-ag:focus {
                                outline: none;
                                border-color: #0a932c;
                                box-shadow: 0 0 0 4px rgba(10, 147, 44, 0.1);
                                background: #ffffff;
                            }
                            
                            .form-control-ag:disabled {
                                background: #f5f5f5;
                                cursor: not-allowed;
                                color: #999;
                            }
                            
                            textarea.form-control-ag {
                                resize: vertical;
                                min-height: 100px;
                                font-family: inherit;
                                line-height: 1.5;
                            }
                            
                            .form-helper {
                                font-size: 12px;
                                color: #666;
                                margin-top: 6px;
                                display: flex;
                                align-items: center;
                                gap: 5px;
                            }
                            
                            .form-helper i {
                                font-size: 11px;
                            }
                            
                            .form-divider {
                                height: 1px;
                                background: linear-gradient(to right, transparent, #e0e0e0, transparent);
                                margin: 25px 0;
                            }
                        </style>
                        
                        <div class="form-agendamiento">
                            <div class="form-group-ag">
                                <label class="form-label-ag">
                                    <i class="bi bi-person-fill"></i>
                                    Propietario
                                    <span class="required">*</span>
                                </label>
                                <select id="swal-propietario" class="form-control-ag">
                                    ${propietariosOptions}
                                </select>
                                <div class="form-helper">
                                    <i class="bi bi-info-circle"></i>
                                    Selecciona el propietario de la mascota
                                </div>
                            </div>
                            
                            <div class="form-group-ag">
                                <label class="form-label-ag">
                                    <i class="bi bi-heart-fill"></i>
                                    Mascota
                                    <span class="required">*</span>
                                </label>
                                <select id="swal-mascota" class="form-control-ag" disabled>
                                    <option value="">Primero selecciona un propietario</option>
                                </select>
                                <div class="form-helper">
                                    <i class="bi bi-info-circle"></i>
                                    Selecciona la mascota del propietario
                                </div>
                            </div>
                            
                            <div class="form-divider"></div>
                            
                            <div class="form-group-ag">
                                <label class="form-label-ag">
                                    <i class="bi bi-clipboard2-pulse-fill"></i>
                                    Servicio
                                    <span class="required">*</span>
                                </label>
                                <select id="swal-servicio" class="form-control-ag">
                                    ${serviciosOptions}
                                </select>
                                <div class="form-helper">
                                    <i class="bi bi-info-circle"></i>
                                    Selecciona el servicio específico
                                </div>
                            </div>
                            
                            <div class="form-divider"></div>
                            
                            <div class="form-group-ag">
                                <label class="form-label-ag">
                                    <i class="bi bi-person-badge-fill"></i>
                                    Veterinario
                                </label>
                                <select id="swal-veterinario" class="form-control-ag">
                                    <option value="">Cualquier veterinario disponible</option>
                                </select>
                            </div>
                            
                            <div class="form-divider"></div>
                            
                            <div class="form-group-ag">
                                <label class="form-label-ag">
                                    <i class="bi bi-clock-fill"></i>
                                    Fecha y Hora Inicio
                                    <span class="required">*</span>
                                </label>
                                <input type="datetime-local" id="swal-fecha-inicio" 
                                       class="form-control-ag" value="${fechaInicioStr}">
                            </div>
                            
                            <div class="form-group-ag">
                                <label class="form-label-ag">
                                    <i class="bi bi-clock-history"></i>
                                    Fecha y Hora Fin
                                    <span class="required">*</span>
                                </label>
                                <input type="datetime-local" id="swal-fecha-fin" 
                                       class="form-control-ag" value="${fechaFinStr}">
                            </div>
                            
                            <div class="form-group-ag">
                                <label class="form-label-ag">
                                    <i class="bi bi-chat-left-text-fill"></i>
                                    Motivo / Observaciones
                                </label>
                                <textarea id="swal-motivo" class="form-control-ag" 
                                          placeholder="Describe el motivo de la cita o cualquier observación importante..."></textarea>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-check-lg"></i> Agendar Cita',
                    cancelButtonText: '<i class="bi bi-x-lg"></i> Cancelar',
                    width: '700px',
                    customClass: {
                        confirmButton: 'btn btn-success px-4',
                        cancelButton: 'btn btn-secondary px-4'
                    },
                    buttonsStyling: false,
                    didOpen: () => {
                        setupFormHandlers();
                    },
                    preConfirm: () => {
                        return validateAndGetFormData();
                    }
                });

                if (result.isConfirmed && result.value) {
                    await enviarCita(result.value);
                }
                
            } catch (error) {
                console.error('Error al abrir formulario de nueva cita:', error);
                console.error('Stack trace:', error.stack);
                mostrarNotificacion('Error al cargar el formulario: ' + error.message, 'error');
            }
        }

        // Función auxiliar para formatear fechas para datetime-local
        function formatDateForInput(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        // Función auxiliar para formatear fechas para MySQL
        function formatDateForMySQL(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }

        // Configurar los handlers del formulario
        function setupFormHandlers() {
            // Handler para cambio de propietario
            document.getElementById('swal-propietario').addEventListener('change', async function() {
                const idPropietario = this.value;
                const mascotaSelect = document.getElementById('swal-mascota');
                
                if (idPropietario) {
                    mascotaSelect.disabled = true;
                    mascotaSelect.innerHTML = '<option value="">Cargando mascotas...</option>';
                    
                    try {
                        const mascotas = await cargarMascotasPorPropietario(idPropietario);
                        let options = '<option value="">Selecciona una mascota...</option>';
                        mascotas.forEach(m => {
                            options += `<option value="${m.id_paciente}">${m.nombre} - ${m.especie} (${m.raza})</option>`;
                        });
                        mascotaSelect.innerHTML = options;
                        mascotaSelect.disabled = false;
                    } catch (error) {
                        mascotaSelect.innerHTML = '<option value="">Error al cargar mascotas</option>';
                        console.error('Error al cargar mascotas:', error);
                    }
                } else {
                    mascotaSelect.disabled = true;
                    mascotaSelect.innerHTML = '<option value="">Primero selecciona un propietario</option>';
                }
            });

            // Cargar veterinarios
            cargarVeterinarios();
        }

        // Validar y obtener datos del formulario
        function validateAndGetFormData() {
            const propietario = document.getElementById('swal-propietario').value;
            const mascota = document.getElementById('swal-mascota').value;
            const subservicio = document.getElementById('swal-servicio').value; // Ahora contiene id_subservicio
            const veterinario = document.getElementById('swal-veterinario').value || null;
            const fechaInicio = document.getElementById('swal-fecha-inicio').value;
            const fechaFin = document.getElementById('swal-fecha-fin').value;
            const motivo = document.getElementById('swal-motivo').value;

            // Validaciones
            if (!propietario) {
                Swal.showValidationMessage('Por favor selecciona un propietario');
                return false;
            }
            if (!mascota) {
                Swal.showValidationMessage('Por favor selecciona una mascota');
                return false;
            }
            if (!subservicio) {
                Swal.showValidationMessage('Por favor selecciona un servicio');
                return false;
            }
            if (!fechaInicio || !fechaFin) {
                Swal.showValidationMessage('Por favor completa las fechas');
                return false;
            }

            const inicio = new Date(fechaInicio);
            const fin = new Date(fechaFin);

            if (fin <= inicio) {
                Swal.showValidationMessage('La fecha de fin debe ser posterior a la de inicio');
                return false;
            }

            return {
                id_mascota: mascota,
                id_subservicio: subservicio,
                id_veterinario: veterinario,
                fecha_hora_inicio: formatDateForMySQL(inicio),
                fecha_hora_fin: formatDateForMySQL(fin),
                motivo: motivo,
                estado: 'pendiente'
            };
        }

        // Enviar cita al servidor
        async function enviarCita(datos) {
            try {
                const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
                
                const response = await fetch(baseUrl + '/calendario/storeEvent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datos)
                });

                const result = await response.json();

                if (result.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: '¡Cita Agendada!',
                        text: result.message || 'La cita ha sido agendada exitosamente',
                        confirmButtonText: 'Aceptar',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        },
                        buttonsStyling: false
                    });
                    
                    // Recargar la página para mostrar la nueva cita
                    location.reload();
                } else {
                    throw new Error(result.message || 'Error al agendar la cita');
                }
            } catch (error) {
                console.error('Error:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo agendar la cita',
                    confirmButtonText: 'Aceptar',
                    customClass: {
                        confirmButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                });
            }
        }

        // Funciones para cargar datos desde el servidor
        async function cargarPropietarios() {
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
            const response = await fetch(baseUrl + '/calendario/getPropietarios');
            if (!response.ok) throw new Error('Error al cargar propietarios');
            const result = await response.json();
            return result.status === 'success' ? result.data : [];
        }

        async function cargarServicios() {
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
            const response = await fetch(baseUrl + '/calendario/getServicios');
            if (!response.ok) throw new Error('Error al cargar servicios');
            const result = await response.json();
            return result.status === 'success' ? result.data : [];
        }

        async function cargarSubserviciosPorServicio(idServicio) {
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
            const response = await fetch(baseUrl + `/calendario/getSubservicios?id_servicio=${idServicio}`);
            if (!response.ok) throw new Error('Error al cargar subservicios');
            const result = await response.json();
            return result.status === 'success' ? result.data : [];
        }

        async function cargarMascotasPorPropietario(idPropietario) {
            const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
            const response = await fetch(baseUrl + `/calendario/getMascotas?id_propietario=${idPropietario}`);
            if (!response.ok) throw new Error('Error al cargar mascotas');
            const result = await response.json();
            return result.status === 'success' ? result.data : [];
        }

        async function cargarVeterinarios() {
            try {
                const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
                const response = await fetch(baseUrl + '/calendario/getVeterinarios');
                if (!response.ok) throw new Error('Error al cargar veterinarios');
                
                const result = await response.json();
                const veterinarios = result.status === 'success' ? result.data : [];
                const select = document.getElementById('swal-veterinario');
                
                let options = '<option value="">Cualquier veterinario disponible</option>';
                veterinarios.forEach(vet => {
                    options += `<option value="${vet.id_usuario}">Dr. ${vet.nombres} ${vet.apellidos}</option>`;
                });
                
                select.innerHTML = options;
            } catch (error) {
                console.error('Error al cargar veterinarios:', error);
            }
        }

        // SISTEMA DE NOTIFICACIONES
        function mostrarNotificacion(mensaje, tipo = 'info') {
            const colores = {
                success: '#22c55e',
                warning: '#f59e0b',
                error: '#ef4444',
                info: '#3b82f6'
            };

            const iconos = {
                success: 'bi-check-circle-fill',
                warning: 'bi-exclamation-triangle-fill',
                error: 'bi-x-circle-fill',
                info: 'bi-info-circle-fill'
            };

            const notificacion = document.createElement('div');
            notificacion.style.cssText = `
                position: fixed;
                top: 30px;
                right: 30px;
                background: white;
                padding: 20px 25px;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                z-index: 10000;
                display: flex;
                align-items: center;
                gap: 15px;
                min-width: 300px;
                animation: slideInRight 0.4s ease;
                border-left: 5px solid ${colores[tipo]};
            `;

            notificacion.innerHTML = `
                <i class="bi ${iconos[tipo]}" style="font-size: 28px; color: ${colores[tipo]};"></i>
                <span style="flex: 1; font-weight: 600; color: var(--color-gris);">${mensaje}</span>
                <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; color: #6c757d; font-size: 20px;">
                    <i class="bi bi-x-lg"></i>
                </button>
            `;

            document.body.appendChild(notificacion);

            setTimeout(() => {
                notificacion.style.animation = 'slideOutRight 0.4s ease';
                setTimeout(() => notificacion.remove(), 400);
            }, 4000);
        }

        // ANIMACIONES CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // FILTRO POR VETERINARIO
        document.getElementById('filtroVeterinario').addEventListener('change', function() {
            const veterinario = this.value;
            const citas = document.querySelectorAll('.cita-card');

            citas.forEach(cita => {
                const vetText = cita.textContent;
                if (veterinario === '' || vetText.includes(this.options[this.selectedIndex].text)) {
                    cita.style.display = 'grid';
                } else {
                    cita.style.display = 'none';
                }
            });

            verificarCitasVisibles();
        });

        // ACTUALIZAR ESTADÍSTICAS
        function actualizarEstadisticas() {
            const citas = document.querySelectorAll('.cita-card');
            const stats = {
                pendiente: 0,
                confirmada: 0,
                completada: 0,
                cancelada: 0
            };

            citas.forEach(cita => {
                const estado = cita.dataset.estado;
                if (stats.hasOwnProperty(estado)) {
                    stats[estado]++;
                }
            });

            // Actualizar los números en las tarjetas de estadísticas
            const statCards = document.querySelectorAll('.stat-card');
            statCards[0].querySelector('h3').textContent = stats.pendiente;
            statCards[1].querySelector('h3').textContent = stats.confirmada;
            statCards[2].querySelector('h3').textContent = stats.completada;
            statCards[3].querySelector('h3').textContent = stats.cancelada;
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            actualizarEstadisticas();
            console.log('Sistema de Gestión de Citas cargado exitosamente');
        });
