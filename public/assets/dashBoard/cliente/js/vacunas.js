// Datos de vacunas (vendrían de tu base de datos)
let vacunas = [];

// Elementos del DOM
const vacunasTimeline = document.getElementById('vacunasTimeline');
const vacunasVacio = document.getElementById('vacunasVacio');
const totalVacunas = document.getElementById('totalVacunas');
const proximasVacunas = document.getElementById('proximasVacunas');
const proximasLista = document.getElementById('proximasLista');

// Inicializar
document.addEventListener('DOMContentLoaded', () => {
    cargarVacunas();
});

function cargarVacunas() {
    // Aquí harías la petición a tu servidor PHP
    // fetch('obtener_vacunas.php?mascota_id=' + mascotaId)
    //     .then(response => response.json())
    //     .then(data => {
    //         vacunas = data;
    //         renderizarVacunas();
    //     });
    
    // Datos de ejemplo
    vacunas = [
        {
            nombre: 'Rabia',
            fechaAplicacion: '2024-06-15',
            proximaDosis: '2025-06-15',
            veterinario: 'Dr. García Martínez',
            lote: 'RAB2024-001',
            notas: 'Primera dosis aplicada. Mascota sin reacciones adversas.'
        },
        {
            nombre: 'Parvovirus',
            fechaAplicacion: '2024-08-20',
            proximaDosis: '2025-02-20',
            veterinario: 'Dra. Martínez López',
            lote: 'PARVO-2024-15',
            notas: 'Refuerzo anual aplicado correctamente.'
        },
        {
            nombre: 'Moquillo',
            fechaAplicacion: '2024-07-10',
            proximaDosis: '2025-07-10',
            veterinario: 'Dr. García Martínez',
            lote: 'MOQ-2024-08',
            notas: null
        },
        {
            nombre: 'Leptospirosis',
            fechaAplicacion: '2024-09-05',
            proximaDosis: '2025-03-05',
            veterinario: 'Dra. Martínez López',
            lote: 'LEPTO-2024-22',
            notas: 'Importante mantener al día para perros que salen al campo.'
        }
    ];
    
    renderizarVacunas();
}

function calcularEstado(fechaAplicacion, proximaDosis) {
    if (!proximaDosis) {
        return { clase: 'aplicada', texto: 'Aplicada', tipo: 'aplicada' };
    }
    
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const fechaProxima = new Date(proximaDosis);
    const diasDiferencia = Math.floor((fechaProxima - hoy) / (1000 * 60 * 60 * 24));
    
    if (diasDiferencia < 0) {
        return { clase: 'vencida', texto: 'Refuerzo vencido', tipo: 'vencida' };
    } else if (diasDiferencia <= 30) {
        return { clase: 'pendiente', texto: 'Próximo refuerzo', tipo: 'proxima', dias: diasDiferencia };
    } else {
        return { clase: 'aplicada', texto: 'Vigente', tipo: 'aplicada' };
    }
}

function formatearFecha(fecha) {
    if (!fecha) return 'No especificada';
    const f = new Date(fecha + 'T00:00:00');
    const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
    return f.toLocaleDateString('es-ES', opciones);
}

function obtenerIconoVacuna(nombre) {
    const iconos = {
        'rabia': '🦠',
        'parvovirus': '🛡️',
        'moquillo': '💉',
        'leptospirosis': '🧪',
        'hepatitis': '⚕️',
        'coronavirus': '🔬',
        'tos': '🫁',
        'bordetella': '🌡️'
    };
    
    const nombreLower = nombre.toLowerCase();
    for (let key in iconos) {
        if (nombreLower.includes(key)) {
            return iconos[key];
        }
    }
    return '💊';
}

function renderizarVacunas() {
    if (vacunas.length === 0) {
        vacunasTimeline.innerHTML = '';
        vacunasVacio.style.display = 'block';
        totalVacunas.textContent = '0';
        return;
    }
    
    vacunasVacio.style.display = 'none';
    totalVacunas.textContent = vacunas.length;
    
    // Ordenar por fecha de aplicación (más reciente primero)
    const vacunasOrdenadas = [...vacunas].sort((a, b) => 
        new Date(b.fechaAplicacion) - new Date(a.fechaAplicacion)
    );
    
    // Renderizar timeline
    vacunasTimeline.innerHTML = vacunasOrdenadas.map(vacuna => {
        const estado = calcularEstado(vacuna.fechaAplicacion, vacuna.proximaDosis);
        const icono = obtenerIconoVacuna(vacuna.nombre);
        
        return `
            <div class="vacuna-item ${estado.tipo}">
                <div class="vacuna-header-info">
                    <h3 class="vacuna-nombre">
                        <span class="vacuna-icono">${icono}</span>
                        ${vacuna.nombre}
                    </h3>
                    <span class="vacuna-badge ${estado.clase}">${estado.texto}</span>
                </div>
                
                <div class="vacuna-detalles">
                    <div class="detalle-item">
                        <span class="detalle-icono">📅</span>
                        <div class="detalle-contenido">
                            <span class="detalle-label">Fecha de aplicación</span>
                            <span class="detalle-valor">${formatearFecha(vacuna.fechaAplicacion)}</span>
                        </div>
                    </div>
                    
                    ${vacuna.proximaDosis ? `
                    <div class="detalle-item">
                        <span class="detalle-icono">⏰</span>
                        <div class="detalle-contenido">
                            <span class="detalle-label">Próxima dosis</span>
                            <span class="detalle-valor">${formatearFecha(vacuna.proximaDosis)}</span>
                        </div>
                    </div>
                    ` : ''}
                    
                    ${vacuna.veterinario ? `
                    <div class="detalle-item">
                        <span class="detalle-icono">👨‍⚕️</span>
                        <div class="detalle-contenido">
                            <span class="detalle-label">Veterinario</span>
                            <span class="detalle-valor">${vacuna.veterinario}</span>
                        </div>
                    </div>
                    ` : ''}
                    
                    ${vacuna.lote ? `
                    <div class="detalle-item">
                        <span class="detalle-icono">🏷️</span>
                        <div class="detalle-contenido">
                            <span class="detalle-label">Lote</span>
                            <span class="detalle-valor">${vacuna.lote}</span>
                        </div>
                    </div>
                    ` : ''}
                </div>
                
                ${vacuna.notas ? `
                <div class="vacuna-notas">
                    <div class="vacuna-notas-titulo">
                        📝 Observaciones
                    </div>
                    <p class="vacuna-notas-texto">${vacuna.notas}</p>
                </div>
                ` : ''}
            </div>
        `;
    }).join('');
    
    // Renderizar próximas vacunas
    renderizarProximasVacunas();
}

function renderizarProximasVacunas() {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    const proximas = vacunas
        .filter(v => v.proximaDosis)
        .map(v => {
            const fechaProxima = new Date(v.proximaDosis);
            const diasDiferencia = Math.floor((fechaProxima - hoy) / (1000 * 60 * 60 * 24));
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
                <div class="proxima-nombre">${obtenerIconoVacuna(vacuna.nombre)} ${vacuna.nombre}</div>
                <div class="proxima-fecha">${formatearFecha(vacuna.proximaDosis)}</div>
            </div>
            <div class="proxima-dias">
                ${vacuna.diasDiferencia === 0 ? '¡Hoy!' : 
                  vacuna.diasDiferencia === 1 ? 'Mañana' : 
                  `En ${vacuna.diasDiferencia} días`}
            </div>
        </div>
    `).join('');
}

// Función para recargar datos (puedes llamarla periódicamente si quieres)
function recargarVacunas() {
    cargarVacunas();
}