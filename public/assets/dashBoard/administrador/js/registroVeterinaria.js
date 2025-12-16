let currentStep = 0;
const steps = document.querySelectorAll('.step');

const btnGuardarVeterinaria = document.getElementById('btnGuardarVeterinaria');

function validateForm() {
    // Validar campos requeridos del paso actual
    const currentStepElement = steps[currentStep];
    const requiredInputs = currentStepElement.querySelectorAll('[required]');
    let isValid = true;
    let camposVacios = [];

    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');

            // Obtener el nombre del campo
            const label = input.previousElementSibling?.textContent || 'Campo';
            camposVacios.push(label);

            // Remover clase de error después de 3 segundos
            setTimeout(() => {
                input.classList.remove('error');
            }, 3000);
        } else {
            input.classList.remove('error');
        }
    });

    if (!isValid) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos incompletos',
            html: `Por favor complete los siguientes campos:<br><br><strong>${camposVacios.join('<br>')}</strong>`,
            confirmButtonColor: '#0a932c',
            confirmButtonText: 'Entendido'
        });
        return false;
    }

    return true;
}

// Evento del botón "Guardar"
btnGuardarVeterinaria.addEventListener('click', (e) => {
    e.preventDefault();
    const validacion = validateForm();
    if (validacion) {
        Swal.fire({
            icon: 'success',
            title: 'Veterinaria Creada',
            html: `Se ha registrado la veterinaria con éxito`,
            confirmButtonColor: '#0a932c',
            confirmButtonText: 'Entendido'
        }).then(() => {
            setTimeout(() => {
                document.location.href = '../../dashBoard/veterinarias/dashBoardVeterinaria.html';
            }, 500);
        });
    }
});