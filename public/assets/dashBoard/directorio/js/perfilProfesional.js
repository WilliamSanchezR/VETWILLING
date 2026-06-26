/* ================================================================
   Perfil de Profesional — Issue #240
   ================================================================ */

let profesionalData = null;

/* ── Helpers ─────────────────────────────────────────────────────── */
function estrellas(promedio) {
    const llenas = Math.round(promedio || 0);
    let html = '';
    for (let i = 1; i <= 5; i++) {
        const color = i <= llenas ? '#f59e0b' : '#ddd';
        html += `<i class="bi bi-star${i <= llenas ? '-fill' : ''}" style="font-size:.85rem;color:${color}"></i>`;
    }
    return html;
}

function badgeClass(estado) {
    return 'badge-' + (estado || 'No-disponible').replace(/ /g, '-');
}

function avatarSrc(img, nombre, apellido) {
    if (img) return `${BASE_URL}/public/uploads/profesionales/${img}`;
    return `https://ui-avatars.com/api/?name=${encodeURIComponent((nombre || 'V') + ' ' + (apellido || 'T'))}&background=0a932c&color=fff&size=110`;
}

/* ── Render del perfil completo ──────────────────────────────────── */
function renderPerfil(p, resenias) {
    const esPropioVet   = (ID_ROL === 2 && ID_USUARIO === p.id_usuario);
    const esPropietario = ID_ROL === 3;

    const tags = p.especialidades
        ? p.especialidades.split(',').map(e => `<span class="esp-tag">${e.trim()}</span>`).join('')
        : '';

    // Banner estado propio
    const bannerEstado = esPropioVet ? `
    <div class="estado-propio">
        <i class="bi bi-person-badge-fill text-warning fs-5"></i>
        <strong>Tu estado visible:</strong>
        <select id="selectEstado">
            <option value="Activo"         ${p.estado_directorio === 'Activo'        ? 'selected' : ''}>🟢 Activo</option>
            <option value="En consulta"    ${p.estado_directorio === 'En consulta'   ? 'selected' : ''}>🟡 En consulta</option>
            <option value="De vacaciones"  ${p.estado_directorio === 'De vacaciones' ? 'selected' : ''}>🔵 De vacaciones</option>
            <option value="No disponible"  ${p.estado_directorio === 'No disponible' ? 'selected' : ''}>⚫ No disponible</option>
        </select>
        <button class="btn-guardar-estado" id="btnGuardarEstado">
            <i class="bi bi-check-lg"></i> Guardar
        </button>
        <span id="msgEstado" class="small"></span>
    </div>` : '';

    // Horarios
    let horariosHtml = '';
    if (p.horarios && p.horarios.length) {
        horariosHtml = p.horarios.map(h => `
            <div class="horario-row">
                <span class="horario-dia"><i class="bi bi-calendar3 me-1"></i>${h.dia}</span>
                <span class="horario-hora">${h.hora_inicio.substring(0, 5)} – ${h.hora_fin.substring(0, 5)}</span>
                ${h.especialidad ? `<span class="horario-esp">${h.especialidad}</span>` : ''}
            </div>`).join('');
    } else {
        horariosHtml = `<div class="empty-horario">
            <i class="bi bi-calendar-x d-block mb-2" style="font-size:2rem;"></i>Sin horarios registrados
        </div>`;
    }

    // Reseñas
    let reseniasHtml = '';
    if (resenias && resenias.length) {
        reseniasHtml = resenias.map(r => `
            <div class="resenia-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="autor"><i class="bi bi-person-circle me-1"></i>${r.autor_nombre || 'Usuario'}</span>
                    <span class="fecha">${r.created_at ? r.created_at.substring(0, 10) : ''}</span>
                </div>
                <div class="stars mt-1">${estrellas(r.calificacion)}</div>
                ${r.comentario ? `<p class="texto">${r.comentario}</p>` : ''}
            </div>`).join('');
    } else {
        reseniasHtml = `<div class="empty-res">
            <i class="bi bi-chat-square-dots d-block mb-2" style="font-size:2rem;color:#ddd;"></i>Sin reseñas todavía
        </div>`;
    }

    const btnNuevaResenia = esPropietario ? `
    <button type="button" class="btn btn-outline-success btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#modalResenia">
        <i class="bi bi-star-fill"></i> Dejar mi reseña
    </button>` : '';

    const nombreEnc = encodeURIComponent((p.nombres || 'V') + ' ' + (p.apellidos || 'T'));

    document.getElementById('contenidoPerfil').innerHTML = `
    ${bannerEstado}

    <div class="perfil-header">
        <img class="avatar"
             src="${avatarSrc(p.img_perfil, p.nombres, p.apellidos)}"
             alt="${p.nombres}"
             onerror="this.src='https://ui-avatars.com/api/?name=${nombreEnc}&background=0a932c&color=fff&size=110'">
        <div>
            <div class="nombre-prof">${p.nombres} ${p.apellidos}</div>
            <div class="licencia"><i class="bi bi-patch-check-fill me-1"></i>${p.registro_medico || 'Sin registro médico'}</div>
            <div>${tags}</div>
            <div class="rating-row">
                <span class="stars">${estrellas(p.calificacion_promedio)}</span>
                <span>${p.calificacion_promedio ? Number(p.calificacion_promedio).toFixed(1) : 'Sin calificación'}</span>
                <span style="opacity:.7;font-size:.82rem;">(${p.total_resenias || 0} reseña${p.total_resenias !== 1 ? 's' : ''})</span>
            </div>
            <span class="badge-estado-hdr ${badgeClass(p.estado_directorio)}">${p.estado_directorio || 'No disponible'}</span>
        </div>
    </div>

    <div class="tab-panel">
        <ul class="nav nav-tabs" id="perfilTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabInfo" type="button">
                    <i class="bi bi-info-circle me-1"></i>Información
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabHorarios" type="button">
                    <i class="bi bi-clock me-1"></i>Horarios
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabResenias" type="button">
                    <i class="bi bi-star me-1"></i>Reseñas
                    <span class="badge bg-secondary ms-1">${resenias ? resenias.length : 0}</span>
                </button>
            </li>
        </ul>
        <div class="tab-content" id="perfilTabContent">

            <div class="tab-pane fade show active" id="tabInfo">
                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-telephone-fill"></i></div>
                    <div class="info-body"><div class="lbl">Teléfono</div><div class="val">${p.telefono || '—'}</div></div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-envelope-fill"></i></div>
                    <div class="info-body"><div class="lbl">Correo</div><div class="val">${p.email || '—'}</div></div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-hospital-fill"></i></div>
                    <div class="info-body"><div class="lbl">Clínica veterinaria</div><div class="val">${p.veterinaria || '—'}</div></div>
                </div>
                <div class="info-row">
                    <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <div class="info-body"><div class="lbl">Dirección</div><div class="val">${p.direccion || '—'}</div></div>
                </div>
            </div>

            <div class="tab-pane fade" id="tabHorarios">${horariosHtml}</div>

            <div class="tab-pane fade" id="tabResenias">
                ${reseniasHtml}
                ${btnNuevaResenia}
            </div>
        </div>
    </div>`;

    // Re-enlazar handler de guardar estado (se creó dinámicamente)
    const btnGuardar = document.getElementById('btnGuardarEstado');
    if (btnGuardar) btnGuardar.addEventListener('click', guardarEstado);
}

/* ── Cargar perfil desde la API ──────────────────────────────────── */
async function cargarPerfil() {
    try {
        const res  = await fetch(`${BASE_URL}/directorio/perfil?accion=perfil&id_usuario=${ID_PERFIL}`);
        const data = await res.json();

        if (!data.success) {
            document.getElementById('contenidoPerfil').innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-person-x d-block mb-2" style="font-size:3rem;"></i>
                    Profesional no encontrado.
                    <div class="mt-3">
                        <a href="${BASE_URL}/directorio" class="btn btn-outline-success btn-sm">Volver al directorio</a>
                    </div>
                </div>`;
            return;
        }
        profesionalData = data.data;
        renderPerfil(data.data, data.data.resenias || []);
    } catch {
        document.getElementById('contenidoPerfil').innerHTML = `
            <div class="text-center py-5 text-danger">
                <i class="bi bi-exclamation-triangle d-block mb-2" style="font-size:2rem;"></i>
                Error al cargar el perfil.
            </div>`;
    }
}

/* ── Guardar estado propio ───────────────────────────────────────── */
async function guardarEstado() {
    const estado = document.getElementById('selectEstado').value;
    const btn    = document.getElementById('btnGuardarEstado');
    const msg    = document.getElementById('msgEstado');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const fd = new FormData();
        fd.append('accion', 'estado');
        fd.append('estado', estado);

        const res  = await fetch(`${BASE_URL}/directorio/actualizar-estado`, { method: 'POST', body: fd });
        const data = await res.json();

        msg.innerHTML = data.success
            ? '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Estado actualizado</span>'
            : '<span class="text-danger">No se pudo actualizar</span>';
    } catch {
        msg.innerHTML = '<span class="text-danger">Error de conexión</span>';
    } finally {
        btn.disabled  = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Guardar';
    }
}

/* ── Selector de estrellas interactivo ───────────────────────────── */
function initStarSelector() {
    const stars = document.querySelectorAll('#starSelector i');

    stars.forEach(s => {
        s.addEventListener('mouseover', function () {
            const v = +this.dataset.val;
            stars.forEach(x => x.style.color = +x.dataset.val <= v ? '#f59e0b' : '#ddd');
        });

        s.addEventListener('mouseleave', function () {
            const sel = +document.getElementById('calificacionSeleccionada').value;
            stars.forEach(x => x.style.color = +x.dataset.val <= sel ? '#f59e0b' : '#ddd');
        });

        s.addEventListener('click', function () {
            document.getElementById('calificacionSeleccionada').value = this.dataset.val;
            const v = +this.dataset.val;
            stars.forEach(x => x.style.color = +x.dataset.val <= v ? '#f59e0b' : '#ddd');
        });
    });
}

/* ── Enviar reseña ───────────────────────────────────────────────── */
function initEnviarResenia() {
    document.getElementById('btnEnviarResenia').addEventListener('click', async function () {
        const cal = +document.getElementById('calificacionSeleccionada').value;
        const msg = document.getElementById('msgResenia');

        if (cal < 1 || cal > 5) {
            msg.innerHTML = '<span class="text-danger">Selecciona una calificación (1–5 estrellas)</span>';
            return;
        }

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando…';

        try {
            const fd = new FormData();
            fd.append('accion',                 'resenia');
            fd.append('id_usuario_profesional', ID_PERFIL);
            fd.append('calificacion',           cal);
            fd.append('comentario',             document.getElementById('comentarioResenia').value.trim());

            const res  = await fetch(`${BASE_URL}/directorio/resenia`, { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                msg.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Reseña enviada</span>';
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('modalResenia')).hide();
                    cargarPerfil();
                }, 1200);
            } else {
                msg.innerHTML = '<span class="text-danger">No se pudo enviar. Intenta de nuevo.</span>';
            }
        } catch {
            msg.innerHTML = '<span class="text-danger">Error de conexión</span>';
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-send-fill"></i> Enviar reseña';
        }
    });
}

/* ── Reset modal al cerrarse ─────────────────────────────────────── */
function initResetModal() {
    document.getElementById('modalResenia').addEventListener('hidden.bs.modal', function () {
        document.getElementById('calificacionSeleccionada').value = 0;
        document.getElementById('comentarioResenia').value        = '';
        document.getElementById('msgResenia').innerHTML           = '';
        document.querySelectorAll('#starSelector i').forEach(s => s.style.color = '#ddd');
    });
}

/* ── Init ────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    initStarSelector();
    initEnviarResenia();
    initResetModal();
    cargarPerfil();
});
