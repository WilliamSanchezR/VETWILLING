// Variables globales
let listaPacientes = [];
let pacientesSeleccionado = null;

const baseUrl = window.BASE_URL || (() => {
    const appBase = window.location.pathname.split('/').filter(Boolean)[0] || '';
    return `${window.location.origin}${appBase ? '/' + appBase : ''}`;
})();
const API_RECETAS_URL = `${baseUrl}/veterinaria/api/recetas`;

const buscarPaciente   = document.getElementById("buscarPacientes");
const listaSugerencias = document.getElementById("listaSugerencias");
const emptyState       = document.getElementById("emptyState");
const plaqueFilledDiv  = document.getElementById("plaque-paciente");
const plaquePacienteModal = document.getElementById("plaque-paciente-modal");
const textReceta       = document.getElementById("descripcion-receta");
const charCount        = document.getElementById("charCount");
const btnGuardar       = document.getElementById("btn-guardar-receta");
const patientBar       = document.getElementById("receta-pad-patient-bar");
const patientBarName   = document.getElementById("receta-pad-patient-name");
const statRecetas      = document.getElementById("statRecetas");
const statPacientes    = document.getElementById("statPacientes");
const statHoy          = document.getElementById("statHoy");
const statImpresiones  = document.getElementById("statImpresiones");

// ── Fecha en cabecera del taco ──
(function setFechaTaco() {
    const el = document.getElementById("receta-pad-fecha");
    if (!el) return;
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    el.textContent = new Date().toLocaleDateString('es-ES', opciones);
})();

// ── Indicadores de pasos ──
function setStep(n) {
    for (let i = 1; i <= 3; i++) {
        const el = document.getElementById("step" + i);
        if (!el) continue;
        el.classList.remove("active", "done");
        if (i < n)  el.classList.add("done");
        if (i === n) el.classList.add("active");
    }
}

// ── Carga pacientes asignados desde BD ──
function consultarPacientes() {
    fetch(`${API_RECETAS_URL}?action=pacientes`)
        .then(r => r.json())
        .then(payload => {
            if (payload.status !== 'success' || !Array.isArray(payload.data)) {
                throw new Error('Respuesta invalida de pacientes');
            }
            listaPacientes = payload.data;
        })
        .catch(err => console.error('Error al consultar pacientes:', err));
}

// ── Carga estadísticas de mini-stat desde BD ──
function consultarEstadisticas() {
    fetch(`${API_RECETAS_URL}?action=estadisticas`)
        .then(r => r.json())
        .then(payload => {
            if (payload.status !== 'success' || !payload.data) {
                throw new Error('Respuesta invalida de estadisticas');
            }

            const data = payload.data;
            if (statRecetas) statRecetas.textContent = Number(data.total_recetas || 0);
            if (statPacientes) statPacientes.textContent = Number(data.pacientes_atendidos || 0);
            if (statHoy) statHoy.textContent = Number(data.emitidas_hoy || 0);
            if (statImpresiones) statImpresiones.textContent = Number(data.impresiones_hoy || 0);
        })
        .catch(err => {
            console.error('Error al consultar estadisticas:', err);
            if (statRecetas) statRecetas.textContent = '0';
            if (statPacientes) statPacientes.textContent = '0';
            if (statHoy) statHoy.textContent = '0';
            if (statImpresiones) statImpresiones.textContent = '0';
        });
}

// ── Crea la ficha del paciente para el modal ──
function crearPlaquePaciente(paciente) {
    return `
        <div>
            <span><strong>Fecha:</strong> ${paciente.fecha}</span>
            <span><strong>N.º Documento:</strong> ${paciente.documento}</span>
            <span><strong>Propietario:</strong> ${paciente.nombrePropietario}</span>
            <span><strong>Teléfono:</strong> ${paciente.telefono}</span>
        </div>
        <div>
            <span><strong>Paciente:</strong> ${paciente.nombre}</span>
            <span><strong>Especie:</strong> ${paciente.especie}</span>
            <span><strong>Raza:</strong> ${paciente.raza}</span>
            <span><strong>Sexo:</strong> ${paciente.sexo}</span>
        </div>`;
}

// ── Crea la ficha bonita del paciente para el panel lateral ──
function crearFichaLateral(paciente) {
    return `
        <div class="receta-patient-header">
            <div class="receta-patient-header-icon">
                <i class="bi bi-paw"></i>
            </div>
            <div>
                <h6>${paciente.nombre}</h6>
                <small>${paciente.especie} · ${paciente.raza}</small>
            </div>
        </div>
        <div class="receta-patient-fields">
            <div class="receta-field-row">
                <span class="receta-field-label">N.º Documento</span>
                <span class="receta-field-value">${paciente.documento}</span>
            </div>
            <div class="receta-field-row">
                <span class="receta-field-label">Propietario</span>
                <span class="receta-field-value">${paciente.nombrePropietario}</span>
            </div>
            <div class="receta-field-row">
                <span class="receta-field-label">Teléfono</span>
                <span class="receta-field-value">${paciente.telefono}</span>
            </div>
            <div class="receta-field-row">
                <span class="receta-field-label">Sexo</span>
                <span class="receta-field-value">${paciente.sexo}</span>
            </div>
            <div class="receta-field-row">
                <span class="receta-field-label">Fecha</span>
                <span class="receta-field-value">${paciente.fecha}</span>
            </div>
        </div>`;
}

// ── Muestra la info del paciente seleccionado ──
function mostrarContForm() {
    // Panel lateral
    emptyState.style.display   = 'none';
    plaqueFilledDiv.style.display = 'flex';
    plaqueFilledDiv.innerHTML  = crearFichaLateral(pacientesSeleccionado);

    // Barra inline en el editor
    if (patientBar) {
        patientBarName.textContent = `${pacientesSeleccionado.nombre} — ${pacientesSeleccionado.nombrePropietario}`;
        patientBar.style.display = 'flex';
    }

    // Limpiar y preparar textarea
    textReceta.value = 'RP/\n\n';
    actualizarContador();
    actualizarBoton();
    setStep(2);
    textReceta.focus();
}

// ── Deseleccionar paciente ──
function deseleccionarPaciente() {
    pacientesSeleccionado = null;
    buscarPaciente.value  = '';
    emptyState.style.display   = 'flex';
    plaqueFilledDiv.style.display = 'none';
    plaqueFilledDiv.innerHTML  = '';
    if (patientBar) patientBar.style.display = 'none';
    textReceta.value = '';
    actualizarContador();
    actualizarBoton();
    setStep(1);
    buscarPaciente.focus();
}

// ── Contador de caracteres ──
function actualizarContador() {
    if (charCount) charCount.textContent = textReceta.value.length;
}

// ── Habilita / deshabilita el botón de imprimir ──
function actualizarBoton() {
    if (!btnGuardar) return;
    const activo = !!pacientesSeleccionado && textReceta.value.trim().length > 3;
    btnGuardar.disabled = !activo;
}

// ── Búsqueda / filtrado de pacientes ──
function buscarPacientes(valorInput) {
    listaSugerencias.innerHTML = '';

    if (valorInput.trim() === '') {
        listaSugerencias.classList.remove('visible');
        return;
    }

    const q = valorInput.toLowerCase();
    const filtrados = listaPacientes.filter(p => {
        const documento = String(p.documento || '').toLowerCase();
        const propietario = String(p.nombrePropietario || '').toLowerCase();
        return documento.includes(q) || propietario.includes(q);
    });

    if (filtrados.length === 0) {
        listaSugerencias.classList.remove('visible');
        return;
    }

    filtrados.forEach(item => {
        const li = document.createElement("li");
        li.innerHTML = `<i class="bi bi-person-circle"></i> ${item.documento} — ${item.nombrePropietario} (${item.nombre})`;
        li.addEventListener("click", () => {
            buscarPaciente.value = `${item.documento} — ${item.nombrePropietario}`;
            pacientesSeleccionado = item;
            listaSugerencias.innerHTML = '';
            listaSugerencias.classList.remove('visible');
            mostrarContForm();
        });
        listaSugerencias.appendChild(li);
    });

    listaSugerencias.classList.add('visible');
}

// ── Eventos ──
buscarPaciente.addEventListener("input", e => buscarPacientes(e.target.value));

document.addEventListener("click", e => {
    if (!buscarPaciente.contains(e.target) && !listaSugerencias.contains(e.target)) {
        listaSugerencias.classList.remove('visible');
    }
});

textReceta.addEventListener("input", () => {
    actualizarContador();
    actualizarBoton();
});

document.getElementById("btn-limpiar-receta")?.addEventListener("click", () => {
    if (!pacientesSeleccionado) return;
    textReceta.value = 'RP/\n\n';
    actualizarContador();
    actualizarBoton();
    textReceta.focus();
});

document.getElementById("btn-deselect-patient")?.addEventListener("click", deseleccionarPaciente);

// ── Vista previa e imprimir ──
$('#btn-guardar-receta').on('click', function () {
    if (!pacientesSeleccionado) return;
    setStep(3);
    $('#vistaImprimir').modal('show');
    plaquePacienteModal.innerHTML = crearPlaquePaciente(pacientesSeleccionado);
    document.getElementById("receta-paciente").innerText = textReceta.value;

    const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById("fecha_report").innerText = new Date().toLocaleDateString('es-ES', opciones);
});

$('#vistaImprimir').on('hidden.bs.modal', function () {
    if (pacientesSeleccionado) setStep(2);
});

function imprimirDiv(idDiv) {
    const contenidoOriginal = document.body.innerHTML;
    document.title = 'Veterinario - Recetas';
    const divParaImprimir = document.getElementById(idDiv).innerHTML;
    document.body.innerHTML = divParaImprimir;
    window.print();
    document.body.innerHTML = contenidoOriginal;
    window.location.reload();
}

$('#btn-guardar-resultados').on('click', function () {
    imprimirDiv('cont-imprimir');
});

// ── Init ──
document.addEventListener("DOMContentLoaded", () => {
    consultarEstadisticas();
    consultarPacientes();
    setStep(1);
});
