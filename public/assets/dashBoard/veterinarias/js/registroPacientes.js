/* ============================================================
   Registro de Paciente — wizard de 3 pasos
   Validación por formato + envío al backend
   ============================================================ */

let currentStep = 0;
const steps = document.querySelectorAll('.step');

/* baseUrl robusto: usa window.BASE_URL aunque sea cadena vacía (app en raíz).
   Solo cae al fallback si de verdad no existe. */
const baseUrl = (typeof window.BASE_URL === 'string')
    ? window.BASE_URL
    : `${window.location.origin}`;

const btnConfirmarRegistr   = document.getElementById('btnConfirmar');
const btnAgregarMascota     = document.getElementById('btnAgregarMascota');
const listaMascotasAgregadas = document.getElementById('listaMascotasAgregadas');

const mascotaFields = {
    nombre:      document.getElementById('nombreMascota'),
    especie:     document.getElementById('especie'),
    raza:        document.getElementById('raza'),
    sexo:        document.getElementById('sexo'),
    edad_numero: document.getElementById('edadNumero'),
    edad_unidad: document.getElementById('edadUnidad')
};

const mascotasAgregadas = [];

/* ── Utilidades ─────────────────────────────────────────────── */

/* Escapa texto antes de meterlo en innerHTML */
const esc = (valor) => String(valor ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
}[c]));

function marcarError(input) {
    input.classList.add('error');
    setTimeout(() => input.classList.remove('error'), 3000);
}

function obtenerNombreCampo(input) {
    const label = input.closest('.form-group')?.querySelector('label');
    return (label?.textContent || 'Campo').replace('*', '').trim();
}

/* ── Filtro de escritura: solo dígitos en documento y teléfono ── */
function activarFiltrosNumericos() {
    const docInput = document.getElementById('numeroDocumento');
    const telInput = document.getElementById('telefono');

    docInput?.addEventListener('input', () => {
        docInput.value = docInput.value.replace(/\D/g, '');
    });

    telInput?.addEventListener('input', () => {
        // Teléfono: permite dígitos, "+" y espacios
        telInput.value = telInput.value.replace(/[^\d+\s]/g, '');
    });
}

/* ── Validación por paso (vacíos + formato) ──────────────────── */
function validarCamposPaso(stepIndex) {
    const requiredInputs = steps[stepIndex].querySelectorAll('[required]');
    const camposVacios = [];
    const camposInvalidos = [];

    requiredInputs.forEach((input) => {
        const valor = input.value.trim();

        // 1) Vacío
        if (!valor) {
            marcarError(input);
            camposVacios.push(obtenerNombreCampo(input));
            return;
        }

        // 2) Formato según el campo (default: válido)
        let valido = true;

        switch (input.id) {
            case 'numeroDocumento':
                valido = /^\d{6,12}$/.test(valor);
                break;
            case 'telefono':
                valido = /^[\d+\s]{7,}$/.test(valor) && valor.replace(/\D/g, '').length >= 7;
                break;
            case 'email':
                valido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);
                break;
            case 'nombres':
            case 'apellidos':
                valido = /^[a-záéíóúñ\s]+$/i.test(valor);
                break;
            case 'edadNumero':
                valido = /^\d{1,2}$/.test(valor) && Number(valor) >= 0;
                break;
        }

        if (valido) {
            input.classList.remove('error');
        } else {
            marcarError(input);
            camposInvalidos.push(obtenerNombreCampo(input));
        }
    });

    return { camposVacios, camposInvalidos };
}

/* ── Mascotas ────────────────────────────────────────────────── */
function obtenerDatosMascotaFormulario() {
    return {
        nombre:      mascotaFields.nombre.value.trim(),
        especie:     mascotaFields.especie.value.trim(),
        raza:        mascotaFields.raza.value.trim(),
        sexo:        mascotaFields.sexo.value.trim(),
        edad_numero: mascotaFields.edad_numero.value.trim(),
        edad_unidad: mascotaFields.edad_unidad.value.trim()
    };
}

function limpiarFormularioMascota() {
    Object.values(mascotaFields).forEach((campo) => { campo.value = ''; });
}

function renderMascotasAgregadas() {
    if (!listaMascotasAgregadas) return;

    if (mascotasAgregadas.length === 0) {
        listaMascotasAgregadas.innerHTML =
            '<div class="empty-mascotas"><i class="bi bi-clipboard-heart"></i> Aún no hay mascotas agregadas.</div>';
        return;
    }

    listaMascotasAgregadas.innerHTML = mascotasAgregadas.map((m, i) => `
        <div class="item-mascota-agregada">
            <span class="item-mascota-orden">${i + 1}</span>
            <div class="item-mascota-info">
                <strong>${esc(m.nombre)}</strong>
                <span>${esc(m.especie)} · ${esc(m.raza)} · ${esc(m.edad_numero)} ${esc(m.edad_unidad)} · ${esc(m.sexo)}</span>
            </div>
            <button type="button" class="btn-eliminar-mascota" data-index="${i}" title="Eliminar mascota" aria-label="Eliminar ${esc(m.nombre)}">
                <i class="bi bi-trash3"></i>
            </button>
        </div>
    `).join('');
}

function agregarMascotaDesdeFormulario(mostrarAlertaExito = true) {
    const { camposVacios, camposInvalidos } = validarCamposPaso(1);

    if (camposVacios.length > 0 || camposInvalidos.length > 0) {
        const lista = [...camposVacios, ...camposInvalidos];
        Swal.fire({
            icon: 'warning',
            title: 'Revisa los datos de la mascota',
            html: `Verifica:<br><br><strong>${lista.join('<br>')}</strong>`,
            confirmButtonColor: '#0a932c',
            confirmButtonText: 'Entendido'
        });
        return false;
    }

    mascotasAgregadas.push(obtenerDatosMascotaFormulario());
    renderMascotasAgregadas();
    limpiarFormularioMascota();

    if (mostrarAlertaExito) {
        Swal.fire({
            icon: 'success',
            title: 'Mascota agregada',
            text: 'Puedes agregar otra mascota o continuar con el registro.',
            timer: 1400,
            showConfirmButton: false
        });
    }
    return true;
}

/* ── Navegación del wizard ───────────────────────────────────── */
function showStep(index) {
    steps.forEach((step, i) => step.classList.toggle('active', i === index));

    // Stepper numerado
    document.querySelectorAll('.wz-step').forEach((el, i) => {
        el.classList.toggle('is-active', i === index);
        el.classList.toggle('is-done', i < index);
    });
    const fill = document.getElementById('wzFill');
    if (fill && steps.length > 1) {
        fill.style.width = `${(index / (steps.length - 1)) * 100}%`;
    }

    document.querySelector('.wizard-container')
        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });

    if (index === 2) mostrarResumenConfirmacion();
}

function nextStep() {
    if (currentStep === 0) {
        const { camposVacios, camposInvalidos } = validarCamposPaso(0);

        if (camposVacios.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                html: `Por favor complete:<br><br><strong>${camposVacios.join('<br>')}</strong>`,
                confirmButtonColor: '#0a932c',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        if (camposInvalidos.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Datos inválidos',
                html: `Revisa el formato de:<br><br><strong>${camposInvalidos.join('<br>')}</strong>`,
                confirmButtonColor: '#0a932c',
                confirmButtonText: 'Entendido'
            });
            return;
        }
    }

    if (currentStep === 1) {
        const enFormulario = obtenerDatosMascotaFormulario();
        const hayDatosSinAgregar = Object.values(enFormulario).some((v) => v !== '');

        if (hayDatosSinAgregar) {
            if (!agregarMascotaDesdeFormulario(false)) return;
        }

        if (mascotasAgregadas.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Falta agregar mascota',
                text: 'Agrega al menos una mascota para continuar.',
                confirmButtonColor: '#0a932c',
                confirmButtonText: 'Entendido'
            });
            return;
        }
    }

    if (currentStep < steps.length - 1) {
        currentStep++;
        showStep(currentStep);
    }
}

function prevStep() {
    if (currentStep > 0) {
        currentStep--;
        showStep(currentStep);
    }
}

/* ── Resumen de confirmación ─────────────────────────────────── */
function mostrarResumenConfirmacion() {
    const confirmStep = steps[2];
    const buttonsDiv = confirmStep.querySelector('.buttons');
    confirmStep.querySelector('.resumen-final')?.remove();

    const mascotasHTML = mascotasAgregadas.map((m, i) => `
        <li>
            <strong>Mascota ${i + 1}:</strong>
            ${esc(m.nombre)} (${esc(m.especie)} · ${esc(m.raza)} · ${esc(m.edad_numero)} ${esc(m.edad_unidad)} · ${esc(m.sexo)})
        </li>
    `).join('');

    const val = (id) => esc(document.getElementById(id).value);

    const resumenHTML = `
        <div class="resumen-final row g-3">
            <div class="resumen-card col-12 col-md-6">
                <h4><i class="bi bi-person-check"></i> Datos del propietario</h4>
                <ul>
                    <li><strong>Nombres:</strong> ${val('nombres')}</li>
                    <li><strong>Apellidos:</strong> ${val('apellidos')}</li>
                    <li><strong>Documento:</strong> ${val('tipoDocumento')} ${val('numeroDocumento')}</li>
                    <li><strong>Teléfono:</strong> ${val('telefono')}</li>
                    <li><strong>Correo:</strong> ${val('email')}</li>
                    <li><strong>Dirección:</strong> ${val('direccion')}</li>
                </ul>
            </div>
            <div class="resumen-card col-12 col-md-6">
                <h4><i class="bi bi-heart-pulse"></i> Mascotas a registrar</h4>
                <ul>${mascotasHTML}</ul>
            </div>
        </div>
    `;
    buttonsDiv.insertAdjacentHTML('beforebegin', resumenHTML);
}

/* ── Envío ───────────────────────────────────────────────────── */
btnConfirmarRegistr?.addEventListener('click', (event) => {
    event.preventDefault();

    if (mascotasAgregadas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay mascotas',
            text: 'Debes agregar al menos una mascota para registrar.',
            confirmButtonColor: '#0a932c'
        });
        return;
    }

    // Guard contra doble envío
    btnConfirmarRegistr.disabled = true;

    const formData = new FormData();
    formData.append('nombres',          document.getElementById('nombres').value.trim());
    formData.append('apellidos',        document.getElementById('apellidos').value.trim());
    formData.append('tipo_documento',   document.getElementById('tipoDocumento').value.trim());
    formData.append('numero_documento', document.getElementById('numeroDocumento').value.trim());
    formData.append('telefono',         document.getElementById('telefono').value.trim());
    formData.append('email',            document.getElementById('email').value.trim());
    formData.append('direccion',        document.getElementById('direccion').value.trim());
    formData.append('mascotas',         JSON.stringify(mascotasAgregadas));

    // Compatibilidad: primera mascota como campos sueltos
    const primera = mascotasAgregadas[0];
    formData.append('nombre_mascota', primera.nombre);
    formData.append('especie',        primera.especie);
    formData.append('raza',           primera.raza);
    formData.append('sexo',           primera.sexo);
    formData.append('edad_numero',    primera.edad_numero);
    formData.append('edad_unidad',    primera.edad_unidad);

    Swal.fire({
        title: 'Procesando...',
        text: 'Registrando propietario y mascotas',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch(`${baseUrl}/veterinario/guardar-paciente`, { method: 'POST', body: formData })
        .then((response) => response.json())
        .then((result) => {
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Registro exitoso',
                    text: result.message,
                    confirmButtonColor: '#0a932c',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = `${baseUrl}/veterinario/registrar-pacientes`;
                });
            } else {
                btnConfirmarRegistr.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error al registrar',
                    text: result.message,
                    confirmButtonColor: '#0a932c',
                    confirmButtonText: 'Aceptar'
                });
            }
        })
        .catch(() => {
            btnConfirmarRegistr.disabled = false;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al procesar el registro',
                confirmButtonColor: '#0a932c'
            });
        });
});

document.getElementById('btnVolver')?.addEventListener('click', prevStep);

btnAgregarMascota?.addEventListener('click', () => agregarMascotaDesdeFormulario(true));

listaMascotasAgregadas?.addEventListener('click', (event) => {
    const button = event.target.closest('.btn-eliminar-mascota');
    if (!button) return;

    const index = Number(button.dataset.index);
    if (!Number.isNaN(index)) {
        mascotasAgregadas.splice(index, 1);
        renderMascotasAgregadas();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    activarFiltrosNumericos();
    renderMascotasAgregadas();
    showStep(0);
});