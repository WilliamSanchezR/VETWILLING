// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', function () {
    inicializarDashboard();
    configurarEventListeners();
    sincronizarConSidebar();
});

// ========== FUNCIÓN PRINCIPAL DE INICIALIZACIÓN ==========
function inicializarDashboard() {
    // Animar tarjetas de estadísticas
    animarEstadisticas();

    // Configurar filtros
    configurarFiltros();

    // Configurar checkbox maestro
    configurarCheckboxMaestro();

    // Cargar datos (si es necesario)
    // cargarDatosPacientes();
}

// ========== SINCRONIZACIÓN CON SIDEBAR ==========
function sincronizarConSidebar() {
    const sidebar = document.getElementById('barraLateralIzquierda');
    const areaContenido = document.querySelector('.area-contenido');

    if (!sidebar || !areaContenido) return;

    // Escuchar cambios en el sidebar
    window.addEventListener('sidebarToggled', function (e) {
        // Agregar clase al body para mejor control
        if (e.detail.colapsado) {
            document.body.classList.add('sidebar-collapsed');
        } else {
            document.body.classList.remove('sidebar-collapsed');
        }

        // Opcional: ajustar gráficos o tablas si es necesario
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 300);
    });

    // Verificar estado inicial
    if (sidebar.classList.contains('collapsed')) {
        document.body.classList.add('sidebar-collapsed');
    }
}

// ========== ANIMACIÓN DE ESTADÍSTICAS ==========
function animarEstadisticas() {
    const tarjetas = document.querySelectorAll('.tarjeta-estadistica');

    tarjetas.forEach((tarjeta, index) => {
        const h3 = tarjeta.querySelector('h3');
        if (!h3) return;

        const valorFinal = parseInt(h3.textContent);
        let valorActual = 0;
        const duracion = 1500; // ms
        const incremento = Math.ceil(valorFinal / (duracion / 16));

        // Iniciar en 0
        h3.textContent = '0';

        // Animar después de un delay
        setTimeout(() => {
            const intervalo = setInterval(() => {
                valorActual += incremento;

                if (valorActual >= valorFinal) {
                    valorActual = valorFinal;
                    clearInterval(intervalo);
                }

                h3.textContent = valorActual.toLocaleString();
            }, 16);
        }, index * 100);
    });
}

// ========== CONFIGURAR EVENT LISTENERS ==========
function configurarEventListeners() {
    // Botones de acción
    const btnNuevoPaciente = document.querySelector('[onclick*="abrirModalNuevoPaciente"]');
    if (btnNuevoPaciente) {
        btnNuevoPaciente.addEventListener('click', function (e) {
            e.preventDefault();
            abrirModalNuevoPaciente();
        });
    }

    // Botón exportar
    const btnExportar = document.querySelector('.boton-accion-secundario');
    if (btnExportar) {
        btnExportar.addEventListener('click', exportarDatos);
    }

    // Checkboxes de selección
    configurarCheckboxes();

    // Botones de acciones en tabla
    configurarBotonesTabla();

    // Paginación
    configurarPaginacion();
}

// ========== FILTROS ==========
function configurarFiltros() {
    const filtroEspecie = document.getElementById('filtroEspecie');
    const filtroEstado = document.getElementById('filtroEstado');

    if (filtroEspecie) {
        filtroEspecie.addEventListener('change', function () {
            aplicarFiltros();
        });
    }

    if (filtroEstado) {
        filtroEstado.addEventListener('change', function () {
            aplicarFiltros();
        });
    }
}

function aplicarFiltros() {
    const filtroEspecie = document.getElementById('filtroEspecie')?.value.toLowerCase();
    const filtroEstado = document.getElementById('filtroEstado')?.value.toLowerCase();
    const filas = document.querySelectorAll('.tabla-pacientes tbody tr');

    let visibles = 0;

    filas.forEach(fila => {
        const especieTexto = fila.querySelector('.badge-especie')?.textContent.toLowerCase().trim() || '';
        const estadoTexto = fila.querySelector('.badge-estado')?.textContent.toLowerCase().trim() || '';

        let mostrar = true;

        if (filtroEspecie && !especieTexto.includes(filtroEspecie)) {
            mostrar = false;
        }

        if (filtroEstado) {
            if (filtroEstado === 'activo' && !estadoTexto.includes('activo')) {
                mostrar = false;
            } else if (filtroEstado === 'tratamiento' && !estadoTexto.includes('tratamiento')) {
                mostrar = false;
            } else if (filtroEstado === 'alta' && !estadoTexto.includes('alta')) {
                mostrar = false;
            }
        }

        if (mostrar) {
            fila.style.display = '';
            visibles++;
        } else {
            fila.style.display = 'none';
        }
    });

    // Actualizar contador de paginación
    actualizarContadorPaginacion(visibles, filas.length);

    // Mostrar mensaje si no hay resultados
    mostrarMensajeNoResultados(visibles);
}

// ========== CHECKBOX MAESTRO ==========
function configurarCheckboxMaestro() {
    const checkboxMaestro = document.querySelector('.tabla-pacientes thead input[type="checkbox"]');

    if (checkboxMaestro) {
        checkboxMaestro.addEventListener('change', function () {
            const checkboxesFilas = document.querySelectorAll('.tabla-pacientes tbody input[type="checkbox"]');

            checkboxesFilas.forEach(checkbox => {
                checkbox.checked = this.checked;
                actualizarFilaSeleccionada(checkbox);
            });

            actualizarBarraAccionesMasivas();
        });
    }
}

function configurarCheckboxes() {
    const checkboxes = document.querySelectorAll('.tabla-pacientes tbody input[type="checkbox"]');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            actualizarFilaSeleccionada(this);
            actualizarCheckboxMaestro();
            actualizarBarraAccionesMasivas();
        });
    });
}

function actualizarFilaSeleccionada(checkbox) {
    const fila = checkbox.closest('tr');
    if (checkbox.checked) {
        fila.style.background = 'rgba(10, 147, 44, 0.05)';
    } else {
        fila.style.background = '';
    }
}

function actualizarCheckboxMaestro() {
    const checkboxMaestro = document.querySelector('.tabla-pacientes thead input[type="checkbox"]');
    const checkboxesFilas = document.querySelectorAll('.tabla-pacientes tbody input[type="checkbox"]');
    const checkboxesMarcados = document.querySelectorAll('.tabla-pacientes tbody input[type="checkbox"]:checked');

    if (checkboxMaestro) {
        checkboxMaestro.checked = checkboxesFilas.length === checkboxesMarcados.length && checkboxesFilas.length > 0;
        checkboxMaestro.indeterminate = checkboxesMarcados.length > 0 && checkboxesFilas.length !== checkboxesMarcados.length;
    }
}

function actualizarBarraAccionesMasivas() {
    const seleccionados = document.querySelectorAll('.tabla-pacientes tbody input[type="checkbox"]:checked').length;

    // Aquí podrías mostrar una barra de acciones masivas
    if (seleccionados > 0) {
        console.log(`${seleccionados} paciente(s) seleccionado(s)`);
        // Mostrar barra de acciones masivas
    }
}

// ========== BOTONES DE TABLA ==========
function configurarBotonesTabla() {
    const botonesVer = document.querySelectorAll('.boton-accion-tabla[title="Ver detalles"]');
    const botonesEditar = document.querySelectorAll('.boton-accion-tabla[title="Editar"]');
    const botonesHistoria = document.querySelectorAll('.boton-accion-tabla[title="Historia clínica"]');

    botonesVer.forEach(btn => {
        btn.addEventListener('click', function () {
            const fila = this.closest('tr');
            verDetallesPaciente(fila);
        });
    });

    botonesEditar.forEach(btn => {
        btn.addEventListener('click', function () {
            const fila = this.closest('tr');
            editarPaciente(fila);
        });
    });

    botonesHistoria.forEach(btn => {
        btn.addEventListener('click', function () {
            const fila = this.closest('tr');
            verHistoriaClinica(fila);
        });
    });
}

// ========== ACCIONES DE PACIENTES ==========
function abrirModalNuevoPaciente() {
    // Implementar modal de nuevo paciente
    console.log('Abrir modal nuevo paciente');

    // Ejemplo: mostrar alerta
    mostrarNotificacion('Abriendo formulario de nuevo paciente...', 'info');
}

function verDetallesPaciente(fila) {
    const nombre = fila.querySelector('.nombre-paciente')?.textContent;
    console.log('Ver detalles de:', nombre);
    mostrarNotificacion(`Viendo detalles de ${nombre}`, 'info');
}

function editarPaciente(fila) {
    const nombre = fila.querySelector('.nombre-paciente')?.textContent;
    console.log('Editar paciente:', nombre);
    mostrarNotificacion(`Editando ${nombre}`, 'info');
}

function verHistoriaClinica(fila) {
    const nombre = fila.querySelector('.nombre-paciente')?.textContent;
    console.log('Ver historia clínica de:', nombre);
    mostrarNotificacion(`Abriendo historia clínica de ${nombre}`, 'info');
}

// ========== EXPORTAR DATOS ==========
function exportarDatos() {
    mostrarNotificacion('Exportando datos...', 'info');

    // Simular exportación
    setTimeout(() => {
        mostrarNotificacion('Datos exportados exitosamente', 'success');
    }, 1000);
}

// ========== PAGINACIÓN ==========
function configurarPaginacion() {
    const enlaces = document.querySelectorAll('.page-link');

    enlaces.forEach(enlace => {
        enlace.addEventListener('click', function (e) {
            e.preventDefault();

            if (this.closest('.page-item').classList.contains('disabled') ||
                this.closest('.page-item').classList.contains('active')) {
                return;
            }

            const pagina = this.textContent.trim();
            cambiarPagina(pagina);
        });
    });
}

function cambiarPagina(pagina) {
    console.log('Cambiar a página:', pagina);

    // Añadir efecto de carga
    const tabla = document.querySelector('.contenedor-tabla-pacientes');
    tabla.classList.add('loading-state');

    setTimeout(() => {
        tabla.classList.remove('loading-state');

        // Actualizar clase active en paginación
        document.querySelectorAll('.page-item').forEach(item => {
            item.classList.remove('active');
        });

        // Scroll al inicio de la tabla
        tabla.scrollIntoView({ behavior: 'smooth', block: 'start' });

        mostrarNotificacion(`Página ${pagina} cargada`, 'success');
    }, 500);
}

// ========== UTILIDADES ==========
function actualizarContadorPaginacion(visibles, total) {
    const infoPaginacion = document.querySelector('.info-paginacion');
    if (infoPaginacion) {
        infoPaginacion.innerHTML = `Mostrando <strong>1-${visibles}</strong> de <strong>${total}</strong> pacientes`;
    }
}

function mostrarMensajeNoResultados(visibles) {
    let mensajeExistente = document.querySelector('.mensaje-no-resultados');

    if (visibles === 0) {
        if (!mensajeExistente) {
            const tbody = document.querySelector('.tabla-pacientes tbody');
            const mensaje = document.createElement('tr');
            mensaje.className = 'mensaje-no-resultados';
            mensaje.innerHTML = `
                <td colspan="9" style="text-align: center; padding: 40px; color: var(--color-texto-secondary);">
                    <i class="bi bi-search" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                    <strong>No se encontraron resultados</strong>
                    <p style="margin-top: 8px;">Intenta cambiar los filtros</p>
                </td>
            `;
            tbody.appendChild(mensaje);
        }
    } else {
        if (mensajeExistente) {
            mensajeExistente.remove();
        }
    }
}

function mostrarNotificacion(mensaje, tipo = 'info') {
    // Crear notificación
    const notificacion = document.createElement('div');
    notificacion.className = `notificacion notificacion-${tipo}`;
    notificacion.style.cssText = `
        position: fixed;
        top: 24px;
        right: 24px;
        background: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 300px;
        animation: slideInRight 0.3s ease;
    `;

    const icono = tipo === 'success' ? 'check-circle-fill' : tipo === 'error' ? 'x-circle-fill' : 'info-circle-fill';
    const color = tipo === 'success' ? '#0A932C' : tipo === 'error' ? '#dc2626' : '#3b82f6';

    notificacion.innerHTML = `
        <i class="bi bi-${icono}" style="font-size: 24px; color: ${color};"></i>
        <span style="flex: 1;">${mensaje}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; color: #6b7280;">
            <i class="bi bi-x-lg"></i>
        </button>
    `;

    document.body.appendChild(notificacion);

    // Auto-remover después de 3 segundos
    setTimeout(() => {
        notificacion.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notificacion.remove(), 300);
    }, 3000);
}

// ========== ANIMACIONES CSS ADICIONALES ==========
const estilosAnimacion = document.createElement('style');
estilosAnimacion.textContent = `
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
document.head.appendChild(estilosAnimacion);