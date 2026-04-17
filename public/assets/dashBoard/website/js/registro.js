// Se declaran variables globales para controlar el paso actual, los elementos del DOM y los botones
const wizardContainer = document.querySelector('.wizard-container');
const steps = document.querySelectorAll('.step');
const bars = document.querySelectorAll('.progress-bar');
const labels = document.querySelectorAll('.progress-labels span');
const formVeterinaria = document.getElementById('vetForm');
const btnConfirmarVeterinaria = document.getElementById('btnConfirmarVeterinaria');
const btnVolver = document.getElementById('btnVolver');
const planSelect = document.getElementById('plan');
const currentStepInput = document.getElementById('current_step');

let currentStep = Number.parseInt(wizardContainer?.dataset.initialStep ?? '0', 10);
if (Number.isNaN(currentStep) || currentStep < 0 || currentStep >= steps.length) {
    currentStep = 0;
}

if (localStorage.getItem('Bawm_Plan_Select') && planSelect) {
    planSelect.value = localStorage.getItem('Bawm_Plan_Select');
}

document.querySelectorAll('select[data-selected]').forEach(select => {
    const selectedValue = select.dataset.selected ?? '';
    if (selectedValue !== '') {
        select.value = selectedValue;
    }
});

// Función para mostrar el paso actual y actualizar las barras de progreso y las etiquetas
function showStep(index) {
    if (!steps.length) {
        return;
    }

    const safeIndex = Math.max(0, Math.min(index, steps.length - 1));
    currentStep = safeIndex;

    steps.forEach((step, i) => step.classList.toggle('active', i === safeIndex));
    bars.forEach((bar, i) => bar.classList.toggle('active', i <= safeIndex));
    labels.forEach((label, i) => label.classList.toggle('active', i === safeIndex));

    if (currentStepInput) {
        currentStepInput.value = String(safeIndex);
    }

    wizardContainer?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Función para obtener el nombre del campo a partir de su etiqueta
function obtenerNombreCampo(input) {
    const label = input.closest('.form-group')?.querySelector('label');
    return (label?.textContent || 'Campo').replace('*', '').trim();
}

// Función para validar los campos requeridos en el paso actual
function validarCamposPaso(stepIndex) {
    const currentStepElement = steps[stepIndex];
    if (!currentStepElement) {
        return [];
    }

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

// Función para mostrar una alerta con los campos vacíos utilizando SweetAlert2
function mostrarAlertaCampos(camposVacios) {
    Swal.fire({
        icon: 'warning',
        title: 'Campos incompletos',
        html: `Por favor complete:<br><br><strong>${camposVacios.join('<br>')}</strong>`,
        confirmButtonColor: '#0a932c',
        confirmButtonText: 'Entendido'
    });
}

// Funciones para avanzar al siguiente paso o retroceder al paso anterior
function nextStep() {
    if (!steps.length) {
        return;
    }

    const camposVacios = validarCamposPaso(currentStep);
    if (camposVacios.length > 0) {
        mostrarAlertaCampos(camposVacios);
        return;
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

showStep(currentStep);

btnVolver?.addEventListener('click', event => {
    event.preventDefault();
    prevStep();
});

btnConfirmarVeterinaria?.addEventListener('click', event => {
    const camposPaso0 = validarCamposPaso(0);
    if (camposPaso0.length > 0) {
        event.preventDefault();
        showStep(0);
        mostrarAlertaCampos(camposPaso0);
        return;
    }

    const camposPaso1 = validarCamposPaso(1);
    if (camposPaso1.length > 0) {
        event.preventDefault();
        showStep(1);
        mostrarAlertaCampos(camposPaso1);
        return;
    }
});

formVeterinaria?.addEventListener('submit', event => {
    const camposPaso0 = validarCamposPaso(0);
    const camposPaso1 = validarCamposPaso(1);
    if (camposPaso0.length > 0 || camposPaso1.length > 0) {
        event.preventDefault();
        if (camposPaso0.length > 0) {
            showStep(0);
            mostrarAlertaCampos(camposPaso0);
        } else {
            showStep(1);
            mostrarAlertaCampos(camposPaso1);
        }
    }
});