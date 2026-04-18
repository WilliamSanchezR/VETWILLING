let currentStep = 0;
const steps = document.querySelectorAll('.step');
const bars = document.querySelectorAll('.progress-bar');
const labels = document.querySelectorAll('.progress-labels span');
const btnConfirmarRegistr = document.getElementById('btnConfirmar');
const btnAgregarMascota = document.getElementById('btnAgregarMascota');
const listaMascotasAgregadas = document.getElementById('listaMascotasAgregadas');

const baseUrl = window.BASE_URL || (() => {
    const appBase = window.location.pathname.split('/').filter(Boolean)[0] || '';
    return `${window.location.origin}${appBase ? '/' + appBase : ''}`;
})();

const mascotaFields = {
    nombre: document.getElementById('nombreMascota'),
    especie: document.getElementById('especie'),
    raza: document.getElementById('raza'),
    sexo: document.getElementById('sexo'),
    edad_numero: document.getElementById('edadNumero'),
    edad_unidad: document.getElementById('edadUnidad')
};

const mascotasAgregadas = [];

function showStep(index) {
    steps.forEach((step, i) => step.classList.toggle('active', i === index));
    bars.forEach((bar, i) => bar.classList.toggle('active', i <= index));
    labels.forEach((label, i) => label.classList.toggle('active', i === index));

    document.querySelector('.wizard-container').scrollIntoView({ behavior: 'smooth', block: 'start' });

    if (index === 2) {
        mostrarResumenConfirmacion();
    }
}

function obtenerNombreCampo(input) {
    const label = input.closest('.form-group')?.querySelector('label');
    return (label?.textContent || 'Campo').replace('*', '').trim();
}

function validarCamposPaso(stepIndex) {
    const currentStepElement = steps[stepIndex];
    const requiredInputs = currentStepElement.querySelectorAll('[required]');
    const camposVacios = [];

    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            camposVacios.push(obtenerNombreCampo(input));

            setTimeout(() => {
                input.classList.remove('error');
            }, 3000);
        } else {
            input.classList.remove('error');
        }
    });

    return camposVacios;
}

function obtenerDatosMascotaFormulario() {
    return {
        nombre: mascotaFields.nombre.value.trim(),
        especie: mascotaFields.especie.value.trim(),
        raza: mascotaFields.raza.value.trim(),
        sexo: mascotaFields.sexo.value.trim(),
        edad_numero: mascotaFields.edad_numero.value.trim(),
        edad_unidad: mascotaFields.edad_unidad.value.trim()
    };
}

function limpiarFormularioMascota() {
    mascotaFields.nombre.value = '';
    mascotaFields.especie.value = '';
    mascotaFields.raza.value = '';
    mascotaFields.sexo.value = '';
    mascotaFields.edad_numero.value = '';
    mascotaFields.edad_unidad.value = '';
}

function renderMascotasAgregadas() {
    if (!listaMascotasAgregadas) {
        return;
    }

    if (mascotasAgregadas.length === 0) {
        listaMascotasAgregadas.innerHTML = '<div class="empty-mascotas">Aún no hay mascotas agregadas.</div>';
        return;
    }

    listaMascotasAgregadas.innerHTML = mascotasAgregadas.map((mascota, index) => `
        <div class="item-mascota-agregada">
            <div class="item-mascota-info">
                <strong>${index + 1}. ${mascota.nombre}</strong> · ${mascota.especie} · ${mascota.raza} · ${mascota.edad_numero} ${mascota.edad_unidad} · ${mascota.sexo}
            </div>
            <button type="button" class="btn-eliminar-mascota" data-index="${index}" title="Eliminar mascota">
                <i class="bi bi-trash3"></i>
            </button>
        </div>
    `).join('');
}

function agregarMascotaDesdeFormulario(mostrarAlertaExito = true) {
    const camposVacios = validarCamposPaso(1);

    if (camposVacios.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos incompletos',
            html: `Para agregar la mascota, complete:<br><br><strong>${camposVacios.join('<br>')}</strong>`,
            confirmButtonColor: '#0a932c',
            confirmButtonText: 'Entendido'
        });
        return false;
    }

    const mascota = obtenerDatosMascotaFormulario();
    mascotasAgregadas.push(mascota);
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

function mostrarResumenConfirmacion() {
    const confirmStep = steps[2];
    const buttonsDiv = confirmStep.querySelector('.buttons');
    const existingResumen = confirmStep.querySelector('.resumen-final');

    if (existingResumen) {
        existingResumen.remove();
    }

    const mascotasHTML = mascotasAgregadas.map((mascota, index) => `
        <li><strong>Mascota ${index + 1}:</strong> ${mascota.nombre} (${mascota.especie} · ${mascota.raza} · ${mascota.edad_numero} ${mascota.edad_unidad} · ${mascota.sexo})</li>
    `).join('');

    const resumenHTML = `
        <div class="resumen-final">
            <div class="resumen-card">
                <h4><i class="bi bi-person-check text-success"></i> Datos del Propietario</h4>
                <ul>
                    <li><strong>Nombres:</strong> ${document.getElementById('nombres').value}</li>
                    <li><strong>Apellidos:</strong> ${document.getElementById('apellidos').value}</li>
                    <li><strong>Documento:</strong> ${document.getElementById('tipoDocumento').value} ${document.getElementById('numeroDocumento').value}</li>
                    <li><strong>Teléfono:</strong> ${document.getElementById('telefono').value}</li>
                    <li><strong>Correo:</strong> ${document.getElementById('email').value}</li>
                    <li><strong>Dirección:</strong> ${document.getElementById('direccion').value}</li>
                </ul>
            </div>

            <div class="resumen-card">
                <h4><i class="bi bi-heart text-danger"></i> Mascotas a registrar</h4>
                <ul>
                    ${mascotasHTML}
                </ul>
            </div>
        </div>
    `;

    buttonsDiv.insertAdjacentHTML('beforebegin', resumenHTML);
}

function nextStep() {
    if (currentStep === 0) {
        const camposVacios = validarCamposPaso(0);
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
    }

    if (currentStep === 1) {
        const mascotaEnFormulario = obtenerDatosMascotaFormulario();
        const hayDatosSinAgregar = Object.values(mascotaEnFormulario).some(valor => valor !== '');

        if (hayDatosSinAgregar) {
            const mascotaAgregada = agregarMascotaDesdeFormulario(false);
            if (!mascotaAgregada) {
                return;
            }
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

btnConfirmarRegistr?.addEventListener('click', event => {
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

    const formData = new FormData();

    formData.append('nombres', document.getElementById('nombres').value.trim());
    formData.append('apellidos', document.getElementById('apellidos').value.trim());
    formData.append('tipo_documento', document.getElementById('tipoDocumento').value.trim());
    formData.append('numero_documento', document.getElementById('numeroDocumento').value.trim());
    formData.append('telefono', document.getElementById('telefono').value.trim());
    formData.append('email', document.getElementById('email').value.trim());
    formData.append('direccion', document.getElementById('direccion').value.trim());
    formData.append('mascotas', JSON.stringify(mascotasAgregadas));

    const primeraMascota = mascotasAgregadas[0];
    formData.append('nombre_mascota', primeraMascota.nombre);
    formData.append('especie', primeraMascota.especie);
    formData.append('raza', primeraMascota.raza);
    formData.append('sexo', primeraMascota.sexo);
    formData.append('edad_numero', primeraMascota.edad_numero);
    formData.append('edad_unidad', primeraMascota.edad_unidad);

    Swal.fire({
        title: 'Procesando...',
        text: 'Registrando propietario y mascotas',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`${baseUrl}/veterinario/guardar-paciente`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(result => {
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
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al procesar el registro',
                confirmButtonColor: '#0a932c'
            });
        });
});

document.getElementById('btnVolver')?.addEventListener('click', () => {
    prevStep();
});

btnAgregarMascota?.addEventListener('click', () => {
    agregarMascotaDesdeFormulario(true);
});

listaMascotasAgregadas?.addEventListener('click', event => {
    const button = event.target.closest('.btn-eliminar-mascota');
    if (!button) {
        return;
    }

    const index = Number(button.dataset.index);
    if (!Number.isNaN(index)) {
        mascotasAgregadas.splice(index, 1);
        renderMascotasAgregadas();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    renderMascotasAgregadas();
    showStep(0);
});