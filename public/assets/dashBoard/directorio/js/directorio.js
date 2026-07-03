/* ================================================================
   Directorio de Profesionales — VetWilling
   Versión : 2.0
   ================================================================ */

let todosLosProfesionales = [];

/* ── Helpers ──────────────────────────────────────────────────── */
function estrellas(promedio) {
    const llenas = Math.round(promedio || 0);
    let html = '';
    for (let i = 1; i <= 5; i++) {
        html += `<i class="bi bi-star${i <= llenas ? '-fill' : ''}" aria-hidden="true"></i>`;
    }
    return html;
}

function claseBadge(estado) {
    return 'badge-' + (estado || 'No-disponible').replace(/ /g, '-');
}

// Genera iniciales para el avatar (máx. 2 letras)
function iniciales(nombres, apellidos) {
    const n = (nombres || '').trim().charAt(0).toUpperCase();
    const a = (apellidos || '').trim().charAt(0).toUpperCase();
    return n + a || 'VW';
}

// Devuelve el HTML del avatar: foto real si existe, iniciales si no
function avatarHTML(p) {
    const inis = iniciales(p.nombres, p.apellidos);

    if (p.img_perfil) {
        return `<div class="avatar-wrap">
            <img
                src="${BASE_URL}/public/uploads/profesionales/${p.img_perfil}"
                alt="${p.nombres || ''}"
                onerror="this.parentElement.innerHTML='<span>${inis}</span>'"
            >
        </div>`;
    }

    // Sin foto: iniciales con color generado desde el id para que
    // cada profesional tenga su propio color consistente
    const colores = [
        'linear-gradient(135deg,#0a932c,#077522)',
        'linear-gradient(135deg,#0369a1,#075985)',
        'linear-gradient(135deg,#7c3aed,#5b21b6)',
        'linear-gradient(135deg,#b45309,#92400e)',
        'linear-gradient(135deg,#0891b2,#0e7490)',
        'linear-gradient(135deg,#dc2626,#b91c1c)',
    ];
    const bg = colores[(p.id_usuario || 0) % colores.length];

    return `<div class="avatar-wrap" style="background:${bg};">
        <span>${inis}</span>
    </div>`;
}

/* ── Renderizar cards ─────────────────────────────────────────── */
function renderCards(lista) {
    const grid  = document.getElementById('gridProfesionales');
    const total = document.getElementById('totalResultados');

    if (lista.length === 0) {
        total.innerHTML = '';
        grid.innerHTML  = `<div class="empty-state col-12">
            <i class="bi bi-person-x" aria-hidden="true"></i>
            <p>No se encontraron profesionales con los filtros aplicados.</p>
        </div>`;
        return;
    }

    const plural = lista.length !== 1;
    total.innerHTML = `<strong>${lista.length}</strong> profesional${plural ? 'es' : ''} encontrado${plural ? 's' : ''}`;

    grid.innerHTML = lista.map(p => {
        const tags = p.especialidades
            ? p.especialidades.split(',').map(e =>
                `<span class="esp-tag">${e.trim()}</span>`
              ).join('')
            : '<span style="font-size:11px;color:var(--txt2)">Sin especialidad</span>';

        const calificacion = p.calificacion_promedio
            ? Number(p.calificacion_promedio).toFixed(1)
            : '—';

        return `
        <div class="card-prof col-6 col-sm-4 col-md-3 col-lg-2">
            ${avatarHTML(p)}
            <div class="nombre">${p.nombres || ''} ${p.apellidos || ''}</div>
            <div class="licencia">
                <i class="bi bi-patch-check" aria-hidden="true"></i>
                ${p.registro_medico || 'Sin registro'}
            </div>
            <div class="esp-tags">${tags}</div>
            <div class="rating">
                ${estrellas(p.calificacion_promedio)}
                <span>${calificacion}</span>
                <span class="total">(${p.total_resenias || 0})</span>
            </div>
            <div class="vet-clinic">
                <i class="bi bi-hospital" aria-hidden="true"></i>
                ${p.veterinaria || '—'}
            </div>
            <span class="badge-estado ${claseBadge(p.estado_directorio)}">
                ${p.estado_directorio || 'No disponible'}
            </span>
            <hr class="card-sep">
            <button class="btn-ver-perfil" onclick="verPerfil(${p.id_usuario})">
                <i class="bi bi-person-lines-fill" aria-hidden="true"></i>
                Ver perfil
            </button>
        </div>`;
    }).join('');
}

/* ── Cargar directorio desde la API ──────────────────────────── */
async function cargarDirectorio(filtros = {}) {
    const params = new URLSearchParams();
    if (filtros.busqueda)       params.set('busqueda',       filtros.busqueda);
    if (filtros.especialidad)   params.set('especialidad',   filtros.especialidad);
    if (filtros.disponibilidad) params.set('disponibilidad', filtros.disponibilidad);

    document.getElementById('gridProfesionales').innerHTML = `
        <div class="spinner-wrap">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Cargando…</span>
            </div>
            <p class="mt-2 small">Cargando directorio…</p>
        </div>`;

    try {
        const res  = await fetch(`${BASE_URL}/directorio/profesionales?${params}`);
        const data = await res.json();
        todosLosProfesionales = data.data || [];
        renderCards(todosLosProfesionales);
        poblarFiltroEsp(todosLosProfesionales);
    } catch {
        document.getElementById('gridProfesionales').innerHTML = `
            <div class="empty-state col-12">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                <p>Error al cargar el directorio. Intenta de nuevo.</p>
            </div>`;
    }
}

/* ── Filtro de especialidades dinámico ───────────────────────── */
function poblarFiltroEsp(lista) {
    const sel    = document.getElementById('filtroEsp');
    const actual = sel.value;
    const espSet = new Set();

    lista.forEach(p => {
        if (p.especialidades) {
            p.especialidades.split(',').forEach(e => espSet.add(e.trim()));
        }
    });

    sel.innerHTML = '<option value="">Todas las especialidades</option>';
    [...espSet].sort().forEach(e => {
        const opt       = document.createElement('option');
        opt.value       = e;
        opt.textContent = e;
        if (e === actual) opt.selected = true;
        sel.appendChild(opt);
    });
}

/* ── Navegar a perfil ────────────────────────────────────────── */
function verPerfil(id) {
    window.location.href = `${BASE_URL}/directorio/ver-perfil?id_usuario=${id}`;
}

/* ── Guardar estado propio (solo veterinario) ────────────────── */
async function guardarEstado() {
    const estado = document.getElementById('selectEstadoPropio').value;
    const btn    = document.getElementById('btnGuardarEstado');

    btn.disabled    = true;
    btn.innerHTML   = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Guardando…';

    try {
        const fd = new FormData();
        fd.append('accion', 'estado');
        fd.append('estado', estado);

        const res  = await fetch(`${BASE_URL}/directorio/actualizar-estado`, { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            btn.innerHTML = '<i class="bi bi-check-circle-fill" aria-hidden="true"></i> Guardado';
            setTimeout(() => {
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-check-lg" aria-hidden="true"></i> Guardar';
            }, 2000);
        } else {
            btn.innerHTML = '<i class="bi bi-x-circle-fill" aria-hidden="true"></i> Error';
            btn.disabled  = false;
        }
    } catch {
        btn.innerHTML = '<i class="bi bi-x-circle-fill" aria-hidden="true"></i> Error';
        btn.disabled  = false;
    }
}

/* ── Aplicar filtros ─────────────────────────────────────────── */
function aplicarFiltros() {
    cargarDirectorio({
        busqueda:       document.getElementById('inputBusqueda').value.trim(),
        especialidad:   document.getElementById('filtroEsp').value,
        disponibilidad: document.getElementById('filtroDisp').value,
    });
}

/* ── Init ────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {

    // Búsqueda con debounce de 350 ms
    let debounce;
    document.getElementById('inputBusqueda').addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(aplicarFiltros, 350);
    });

    document.getElementById('filtroEsp').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroDisp').addEventListener('change', aplicarFiltros);

    document.getElementById('btnLimpiar').addEventListener('click', () => {
        document.getElementById('inputBusqueda').value = '';
        document.getElementById('filtroEsp').value     = '';
        document.getElementById('filtroDisp').value    = '';
        cargarDirectorio();
    });

    // Botón guardar estado (solo existe en rol veterinario)
    document.getElementById('btnGuardarEstado')?.addEventListener('click', guardarEstado);

    // Carga inicial
    cargarDirectorio();
});