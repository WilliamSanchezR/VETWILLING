let currentStep = 0;
const steps = document.querySelectorAll('.step');
const bars = document.querySelectorAll('.progress-bar');
const labels = document.querySelectorAll('.progress-labels span');
let tablaLaboratorios; // Variable global para la tabla DataTables
let listLaboratorios = []; // Lista para almacenar los laboratorios seleccionados
let laboratoriosSeleccionados = []; // Variable para almacenar el laboratorio seleccionado
const buscadorExamenes = document.getElementById('buscarExamenes'); 
const listaSugerencias = document.getElementById('listaSugerencias');
const btnConfirmarRegistr = document.getElementById('btnConfirmar');

// Funcion para mostrar el paso actual del formulario de registro de pacientes de laboratorio
function showStep(index) {
    steps.forEach((s, i) => s.classList.toggle('active', i === index));
    bars.forEach((b, i) => b.classList.toggle('active', i <= index));
    labels.forEach((l, i) => l.classList.toggle('active', i === index));

    // Scroll al inicio del formulario
    document.querySelector('.wizard-container').scrollIntoView({ behavior: 'smooth', block: 'start' });

    // Si estamos en el paso de confirmación, actualizar resumen
    if (index === 2) {
        mostrarResumenConfirmacion();
    }
}

// Función para mostrar resumen en el paso de confirmación
function mostrarResumenConfirmacion() {
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
                    <h4><i class="bi bi-heart text-danger"></i> Datos de la Mascota</h4>
                    <ul>
                        <li><strong>Nombre:</strong> ${document.getElementById('nombreMascota').value}</li>
                        <li><strong>Especie:</strong> ${document.getElementById('especie').value}</li>
                        <li><strong>Raza:</strong> ${document.getElementById('raza').value}</li>
                        <li><strong>Edad:</strong> ${document.getElementById('edadNumero').value} ${document.getElementById('edadUnidad').value}</li>
                        <li><strong>Sexo:</strong> ${document.getElementById('sexo').value}</li>
                    </ul>
                </div>
            </div>
        `;

    // Insertar el resumen antes de los botones
    const confirmStep = steps[2];
    const existingResumen = confirmStep.querySelector('.resumen-final');

    if (!existingResumen) {
        const buttonsDiv = confirmStep.querySelector('.buttons');
        buttonsDiv.insertAdjacentHTML('beforebegin', resumenHTML);
    }
}

// Funcion para avanzar en el formulario de registro de pacientes de laboratorio
function nextStep() {
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
        return; // ← ESTO ES CRÍTICO
    }

    if (currentStep < steps.length - 1) {
        currentStep++;
        showStep(currentStep);
    }
}

// Funcion para retroceder en el formulario de registro de pacientes de laboratorio
function prevStep() {
    if (currentStep > 0) {
        currentStep--;
        showStep(currentStep);
    }
}

// Función para pintar las opciones en un select a partir de una lista
function pintarOptionsSelect(lista, selectId) {
    const elementselect = document.getElementById(selectId);
    lista.forEach(tipo => {
        const option = document.createElement('option');
        option.value = tipo.id;
        option.textContent = tipo.name;

        elementselect.appendChild(option);
    });

}

// Función para consultar el JSON de tipos de documento
function consultaTipoDocumento() {
    fetch("../../assets/data/tipoDoumento.json")
        .then(response => response.json())
        .then(data => {
            pintarOptionsSelect(data, 'tipoDocumento');
        })
        .catch(error => {
            console.error("Error al cargar el JSON:", error);

        });

}

// Función para consultar el JSON de especies
function consultaEspecie() {
    fetch("../../assets/data/especie.json")
        .then(response => response.json())
        .then(data => {
            pintarOptionsSelect(data, 'especie');
        })
        .catch(error => {
            console.error("Error al cargar el JSON:", error);

        });
}

// Función para inicializar DataTables en la tabla de laboratorios
function dataTablesLaboratorios() {

    try {
        tablaLaboratorios = $('#lista-laboratorios').DataTable({
            // Configuración de idioma en español
            language: {
                "decimal": "",
                "emptyTable": "No hay Laboratorios registrados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Laboratorios",
                "infoEmpty": "Mostrando 0 a 0 de 0 Laboratorios",
                "infoFiltered": "(filtrado de _MAX_ Laboratorios totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Laboratorios",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron Laboratorios",
                "paginate": {
                    "first": "Primera",
                    "last": "Última",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },

            // Configuración de paginación
            pageLength: 9,
            lengthMenu: [[9, 15, 25, 50, -1], [9, 15, 25, 50, "Todas"]],

            // Configuración de ordenamiento
            order: [[0, 'desc']], // Ordenar por fecha por defecto

            // Configuración de columnas
            columnDefs: [
                {
                    targets: -1, // Última columna (Operación)
                    orderable: false,
                    searchable: false
                }
            ],

            // DOM personalizado
            dom: '<"row"<"col-sm-12"tr>>' +
                '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',

        });

        console.log('✅ Tabla inicializada exitosamente');

    } catch (error) {
        console.error('❌ Error al inicializar DataTables:', error);
        alert('Error al inicializar la tabla. Revisa la consola para más detalles.');
        return;
    }

}

// Funcion para cargar la lista de laboratorios
function cargarListaLaboratorio() {
    fetch("../../assets/data/examenesLaboratorio.json")
        .then(response => response.json())
        .then(data => {
            listLaboratorios = data;
        })
        .catch(error => {
            console.error("Error al cargar el JSON:", error);

        });

}

// Función para eliminar un registro de laboratorio
function eliminarRegistroLaboratorio(option) {
    if (laboratoriosSeleccionados.some((examen) => examen.id === option)) {
        const listaFiltrada = laboratoriosSeleccionados.filter((examen) => examen.id !== option);
        laboratoriosSeleccionados = listaFiltrada;

        CargarExamenesTabla();
    }
}

// Función para cargar los exámenes seleccionados en la tabla
function CargarExamenesTabla() {
    tablaLaboratorios.clear().draw(); // Limpiar la tabla antes de agregar nuevos datos

    laboratoriosSeleccionados.forEach((examen, i) => {
        tablaLaboratorios.row.add([
            i + 1,
            examen.name,
            `<button class="btn-accion btn-editar btn-delete" title="Eliminar" onclick="eliminarRegistroLaboratorio(${examen.id})"><i class="bi bi-trash3"></i></button>`
        ]).draw();
    });
}

// Función para consultar exámenes según el valor del input
function consultarExamenes(valorInput) {
    listaSugerencias.innerHTML = '';

    if (valorInput.trim() === '') {
        listaSugerencias.style.display = "none";
        return;
    }

    const valorBusqueda = valorInput.toLowerCase();

    const examenesFiltrados = listLaboratorios.filter(examen =>
        examen.name.toLowerCase().includes(valorBusqueda)
    );

    if (examenesFiltrados.length === 0) {
        listaSugerencias.style.display = "none";
        return;
    } else {
        listaSugerencias.style.display = "block";
    }

    examenesFiltrados.forEach(item => {
        const listItem = document.createElement("li");
        listItem.textContent = item.name;
        listItem.addEventListener("click", () => {
            buscadorExamenes.value = '';
            if (!laboratoriosSeleccionados.some(examen => examen.id === item.id)) {
                laboratoriosSeleccionados.push(item);
            }

            listaSugerencias.innerHTML = "";
            listaSugerencias.style.display = "none";
            CargarExamenesTabla(laboratoriosSeleccionados);
        });
        listaSugerencias.appendChild(listItem);
    });
}

// Evento del botón "Confirmar Registro"
btnConfirmarRegistr.addEventListener('click', (e) => {
    e.preventDefault();
    
    // Recopilar datos del formulario
    const formData = new FormData();
    
    // Datos del propietario
    formData.append('nombres', document.getElementById('nombres').value);
    formData.append('apellidos', document.getElementById('apellidos').value);
    formData.append('tipo_documento', document.getElementById('tipoDocumento').value);
    formData.append('numero_documento', document.getElementById('numeroDocumento').value);
    formData.append('telefono', document.getElementById('telefono').value);
    formData.append('email', document.getElementById('email').value);
    formData.append('direccion', document.getElementById('direccion').value);
    
    // Datos de la mascota
    formData.append('nombre_mascota', document.getElementById('nombreMascota').value);
    formData.append('especie', document.getElementById('especie').value);
    formData.append('raza', document.getElementById('raza').value);
    formData.append('sexo', document.getElementById('sexo').value);
    formData.append('edad_numero', document.getElementById('edadNumero').value);
    formData.append('edad_unidad', document.getElementById('edadUnidad').value);
    
    // Log para debug
    console.log('Datos a enviar:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Procesando...',
        text: 'Registrando paciente',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Enviar datos al servidor
    fetch('/vetwilling/veterinario/guardar-paciente', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(result => {
        console.log('Response data:', result);
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Registro exitoso',
                text: result.message,
                confirmButtonColor: '#0a932c',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                // Recargar la página para limpiar el formulario
                window.location.href = '/vetwilling/veterinario/registrar-pacientes';
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
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al procesar el registro',
            confirmButtonColor: '#0a932c'
        });
    });
});

// Evento del botón "Volver a revisar"
document.getElementById('btnVolver')?.addEventListener('click', function () {
    prevStep();
});

// Inicialización al cargar el DOM
document.addEventListener("DOMContentLoaded", () => {
    // Inicialización básica
    showStep(0);
});