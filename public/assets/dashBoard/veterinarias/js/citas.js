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
        function nuevaCita() {
            mostrarNotificacion('Abriendo formulario de nueva cita...', 'info');
            // Aquí se abriría un modal o redirigiría a un formulario
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
