/**
 * vacunas.js — Timeline de vacunas del cliente
 * Apunta a los elementos: vacunasTimeline, vacunasVacio, totalVacunas, proximasVacunas, proximasLista
 * Si esos elementos no existen en la página actual, el script no hace nada.
 */

let vacunas = [];

const vacunasTimeline = document.getElementById('vacunasTimeline');
const vacunasVacio    = document.getElementById('vacunasVacio');
const totalVacunas    = document.getElementById('totalVacunas');
const proximasVacunas = document.getElementById('proximasVacunas');
const proximasLista   = document.getElementById('proximasLista');

document.addEventListener('DOMContentLoaded', () => {
    /* Solo actuar si los elementos de timeline existen */
    if (!vacunasTimeline && !totalVacunas) return;
    cargarVacunas();
});

async function cargarVacunas() {
    const idMascota = window.MASCOTA_ID;
    const apiUrl    = window.API_VACUNAS;

    if (!idMascota || !apiUrl) return;

    try {
        const res  = await fetch(`${apiUrl}?id_mascota=${idMascota}`);
        const data = await res.json();

        if (!res.ok || data.status !== 'ok') {
            throw new Error(data.message || 'Error al obtener vacunas');
        }

        /* Convertir formato de la API al formato del timeline */
        vacunas = (data.aplicadas || []).map(v => ({
            nombre          : v.nombre,
            fechaAplicacion : v.fecha,
            proximaDosis    : null,
            veterinario     : v.veterinario,
            lote            : null,
            notas           : v.observaciones || null,
        }));

        renderizarVacunas();

    } catch (err) {
        console.error('vacunas.js cargarVacunas:', err);
        if (vacunasTimeline) {
            vacunasTimeline.innerHTML =
                '<p class="text-muted text-center py-4">No se pudieron cargar las vacunas.</p>';
        }
        if (vacunasVacio) vacunasVacio.style.display = 'none';
    }
}

function calcularEstado(fechaAplicacion, proximaDosis) {
    if (!proximaDosis) {
        return { clase: 'aplicada', texto: 'Aplicada', tipo: 'aplicada' };
    }

    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const fechaProxima     = new Date(proximaDosis);
    const diasDiferencia   = Math.floor((fechaProxima - hoy) / (1000 * 60 * 60 * 24));

    if (diasDiferencia < 0)   return { clase: 'vencida',  texto: 'Refuerzo vencido', tipo: 'vencida' };
    if (diasDiferencia <= 30) return { clase: 'pendiente', texto: 'Próximo refuerzo', tipo: 'proxima', dias: diasDiferencia };
    return { clase: 'aplicada', texto: 'Vigente', tipo: 'aplicada' };
}

function formatearFecha(fecha) {
    if (!fecha) return 'No especificada';
    const f = new Date(fecha + 'T00:00:00');
    return f.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
}

function obtenerIconoVacuna(nombre) {
    const iconos = {
        rabia        : '🦠',
        parvovirus   : '🛡️',
        moquillo     : '💉',
        leptospirosis: '🧪',
        hepatitis    : '⚕️',
        coronavirus  : '🔬',
        tos          : '🫁',
        bordetella   : '🌡️',
    };

    const nombreLower = (nombre || '').toLowerCase();
    for (const key in iconos) {
        if (nombreLower.includes(key)) return iconos[key];
    }
    return '💊';
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function renderizarVacunas() {
    if (!vacunasTimeline) return;

    if (vacunas.length === 0) {
        vacunasTimeline.innerHTML = '';
        if (vacunasVacio)  vacunasVacio.style.display  = 'block';
        if (totalVacunas)  totalVacunas.textContent    = '0';
        return;
    }

    if (vacunasVacio)  vacunasVacio.style.display  = 'none';
    if (totalVacunas)  totalVacunas.textContent    = vacunas.length;

    const vacunasOrdenadas = [...vacunas].sort(
        (a, b) => new Date(b.fechaAplicacion) - new Date(a.fechaAplicacion)
    );

    vacunasTimeline.innerHTML = vacunasOrdenadas.map(vacuna => {
        const estado = calcularEstado(vacuna.fechaAplicacion, vacuna.proximaDosis);
        const icono  = obtenerIconoVacuna(vacuna.nombre);

        return `
            <div class="vacuna-item ${estado.tipo}">
                <div class="vacuna-header-info">
                    <h3 class="vacuna-nombre">
                        <span class="vacuna-icono">${icono}</span>
                        ${escHtml(vacuna.nombre)}
                    </h3>
                    <span class="vacuna-badge ${estado.clase}">${escHtml(estado.texto)}</span>
                </div>

                <div class="vacuna-detalles">
                    <div class="detalle-item">
                        <span class="detalle-icono">📅</span>
                        <div class="detalle-contenido">
                            <span class="detalle-label">Fecha de aplicación</span>
                            <span class="detalle-valor">${formatearFecha(vacuna.fechaAplicacion)}</span>
                        </div>
                    </div>

                    ${vacuna.veterinario ? `
                    <div class="detalle-item">
                        <span class="detalle-icono">👨‍⚕️</span>
                        <div class="detalle-contenido">
                            <span class="detalle-label">Veterinario</span>
                            <span class="detalle-valor">${escHtml(vacuna.veterinario)}</span>
                        </div>
                    </div>
                    ` : ''}
                </div>

                ${vacuna.notas ? `
                <div class="vacuna-notas">
                    <div class="vacuna-notas-titulo">📝 Observaciones</div>
                    <p class="vacuna-notas-texto">${escHtml(vacuna.notas)}</p>
                </div>
                ` : ''}
            </div>
        `;
    }).join('');

    renderizarProximasVacunas();
}

function renderizarProximasVacunas() {
    if (!proximasVacunas || !proximasLista) return;

    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    const proximas = vacunas
        .filter(v => v.proximaDosis)
        .map(v => {
            const fechaProxima    = new Date(v.proximaDosis);
            const diasDiferencia  = Math.floor((fechaProxima - hoy) / (1000 * 60 * 60 * 24));
            return { ...v, diasDiferencia };
        })
        .filter(v => v.diasDiferencia >= 0 && v.diasDiferencia <= 60)
        .sort((a, b) => a.diasDiferencia - b.diasDiferencia);

    if (proximas.length === 0) {
        proximasVacunas.style.display = 'none';
        return;
    }

    proximasVacunas.style.display = 'block';
    proximasLista.innerHTML = proximas.map(vacuna => `
        <div class="proxima-card">
            <div class="proxima-info">
                <div class="proxima-nombre">${obtenerIconoVacuna(vacuna.nombre)} ${escHtml(vacuna.nombre)}</div>
                <div class="proxima-fecha">${formatearFecha(vacuna.proximaDosis)}</div>
            </div>
            <div class="proxima-dias">
                ${vacuna.diasDiferencia === 0 ? '¡Hoy!'
                  : vacuna.diasDiferencia === 1 ? 'Mañana'
                  : `En ${vacuna.diasDiferencia} días`}
            </div>
        </div>
    `).join('');
}

function recargarVacunas() {
    cargarVacunas();
}
