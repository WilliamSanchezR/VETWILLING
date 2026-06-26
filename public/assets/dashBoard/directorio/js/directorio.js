/* ================================================================
   Directorio de Profesionales — Issue #240
   ================================================================ */

let todosLosProfesionales = [];

/* ── Helpers ─────────────────────────────────────────────────────── */
function estrellas(promedio) {
    const llenas = Math.round(promedio || 0);
    let html = '';
    for (let i = 1; i <= 5; i++) {
        html += `<i class="bi bi-star${i <= llenas ? '-fill' : ''}"></i>`;
    }
    return html;
}

function claseBadge(estado) {
    return 'badge-' + (estado || 'No-disponible').replace(/ /g, '-');
}

function avatarSrc(img) {
    if (!img) return `https://ui-avatars.com/api/?name=VW&background=0a932c&color=fff&size=80`;
    return `${BASE_URL}/public/uploads/profesionales/${img}`;
}

/* ── Renderizar cards ────────────────────────────────────────────── */
function renderCards(lista) {
    const grid  = document.getElementById('gridProfesionales');
    const total = document.getElementById('totalResultados');

    total.textContent = lista.length === 0
        ? ''
        : `${lista.length} profesional${lista.length !== 1 ? 'es' : ''} encontrado${lista.length !== 1 ? 's' : ''}`;

    if (!lista.length) {
        grid.innerHTML = `<div class="empty-state">
            <i class="bi bi-person-x"></i>
            <p>No se encontraron profesionales con los filtros aplicados.</p>
        </div>`;
        return;
    }

    const cols = lista.map(p => {
        const tags = p.especialidades
            ? p.especialidades.split(',').map(e => `<span class="esp-tag">${e.trim()}</span>`).join('')
            : '<span class="text-muted small">Sin especialidad</span>';

        const nombreEnc = encodeURIComponent((p.nombres || 'V') + ' ' + (p.apellidos || 'T'));

        return `<div class="col-xl-3 col-lg-4 col-md-6 col-12">
            <div class="card-prof card">
                <div class="card-body text-center p-4">
                    <div class="avatar-wrap">
                        <img src="${avatarSrc(p.img_perfil)}"
                             alt="${p.nombres}"
                             onerror="this.src='https://ui-avatars.com/api/?name=${nombreEnc}&background=0a932c&color=fff&size=80'">
                    </div>
                    <div class="nombre">${p.nombres} ${p.apellidos}</div>
                    <div class="licencia"><i class="bi bi-patch-check"></i> ${p.registro_medico || 'Sin registro'}</div>
                    <span class="badge-estado ${claseBadge(p.estado_directorio)}">${p.estado_directorio || 'No disponible'}</span>
                    <div class="esp-tags mt-2">${tags}</div>
                    <div class="rating">
                        ${estrellas(p.calificacion_promedio)}
                        <span>${p.calificacion_promedio ? Number(p.calificacion_promedio).toFixed(1) : '—'}</span>
                        <span class="total">(${p.total_resenias || 0})</span>
                    </div>
                    <div class="vet-clinic"><i class="bi bi-hospital"></i> ${p.veterinaria || '—'}</div>
                    <button class="btn-ver-perfil" onclick="verPerfil(${p.id_usuario})">
                        <i class="bi bi-person-lines-fill"></i> Ver Perfil
                    </button>
                </div>
            </div>
        </div>`;
    }).join('');

    grid.innerHTML = `<div class="row g-4">${cols}</div>`;
}

/* ── Cargar directorio desde la API ──────────────────────────────── */
async function cargarDirectorio(filtros = {}) {
    const params = new URLSearchParams();
    if (filtros.busqueda)      params.set('busqueda',      filtros.busqueda);
    if (filtros.especialidad)  params.set('especialidad',  filtros.especialidad);
    if (filtros.disponibilidad) params.set('disponibilidad', filtros.disponibilidad);

    const grid = document.getElementById('gridProfesionales');
    grid.innerHTML = `<div class="spinner-wrap">
        <div class="spinner-border text-success" role="status"></div>
        <p class="mt-2 small text-muted">Cargando…</p>
    </div>`;

    try {
        const res  = await fetch(`${BASE_URL}/directorio/profesionales?${params}`);
        const data = await res.json();
        todosLosProfesionales = data.data || [];
        renderCards(todosLosProfesionales);
        poblarFiltroEsp(todosLosProfesionales);
    } catch {
        grid.innerHTML = `<div class="empty-state">
            <i class="bi bi-exclamation-triangle"></i>
            <p>Error al cargar el directorio. Intenta de nuevo.</p>
        </div>`;
    }
}

/* ── Filtro de especialidades dinámico ───────────────────────────── */
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
        const opt    = document.createElement('option');
        opt.value    = e;
        opt.textContent = e;
        if (e === actual) opt.selected = true;
        sel.appendChild(opt);
    });
}

/* ── Navegar a perfil ────────────────────────────────────────────── */
function verPerfil(id) {
    window.location.href = `${BASE_URL}/directorio/ver-perfil?id_usuario=${id}`;
}

/* ── Guardar estado propio (veterinario) ─────────────────────────── */
async function guardarEstado() {
    const estado = document.getElementById('selectEstadoPropio').value;
    const btn    = document.getElementById('btnGuardarEstado');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const fd = new FormData();
        fd.append('accion', 'estado');
        fd.append('estado', estado);

        const res  = await fetch(`${BASE_URL}/directorio/actualizar-estado`, { method: 'POST', body: fd });
        const data = await res.json();

        btn.innerHTML = data.success
            ? '<i class="bi bi-check-circle-fill"></i> Guardado'
            : '<i class="bi bi-x-circle-fill"></i> Error';

        if (data.success) {
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Guardar';
            }, 2000);
        } else {
            btn.disabled = false;
        }
    } catch {
        btn.innerHTML = '<i class="bi bi-x-circle-fill"></i> Error';
        btn.disabled  = false;
    }
}

/* ── Aplicar filtros ─────────────────────────────────────────────── */
function aplicarFiltros() {
    cargarDirectorio({
        busqueda:       document.getElementById('inputBusqueda').value.trim(),
        especialidad:   document.getElementById('filtroEsp').value,
        disponibilidad: document.getElementById('filtroDisp').value,
    });
}

/* ── Event listeners ─────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {

    let debounce;
    document.getElementById('inputBusqueda').addEventListener('input', function () {
        clearTimeout(debounce);
        debounce = setTimeout(aplicarFiltros, 350);
    });

    document.getElementById('filtroEsp').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroDisp').addEventListener('change', aplicarFiltros);

    document.getElementById('btnLimpiar').addEventListener('click', function () {
        document.getElementById('inputBusqueda').value = '';
        document.getElementById('filtroEsp').value     = '';
        document.getElementById('filtroDisp').value    = '';
        cargarDirectorio();
    });

    const btnGuardar = document.getElementById('btnGuardarEstado');
    if (btnGuardar) btnGuardar.addEventListener('click', guardarEstado);

    cargarDirectorio();
});
