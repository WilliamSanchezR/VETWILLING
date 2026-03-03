let currentStep = 0;
const steps = document.querySelectorAll('.step');
const bars = document.querySelectorAll('.progress-bar');
const labels = document.querySelectorAll('.progress-labels span');
const formVeterinaria = document.getElementById('vetForm');
const btnConfirmarVeterinaria = document.getElementById('btnConfirmarVeterinaria');
const btnVolver = document.getElementById('btnVolver');

function showStep(index) {
    steps.forEach((step, i) => step.classList.toggle('active', i === index));
    bars.forEach((bar, i) => bar.classList.toggle('active', i <= index));
    labels.forEach((label, i) => label.classList.toggle('active', i === index));

    document.querySelector('.wizard-container')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function obtenerNombreCampo(input) {
    const label = input.closest('.form-group')?.querySelector('label');
    return (label?.textContent || 'Campo').replace('*', '').trim();
}

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

function mostrarAlertaCampos(camposVacios) {
    Swal.fire({
        icon: 'warning',
        title: 'Campos incompletos',
        html: `Por favor complete:<br><br><strong>${camposVacios.join('<br>')}</strong>`,
        confirmButtonColor: '#0a932c',
        confirmButtonText: 'Entendido'
    });
}

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