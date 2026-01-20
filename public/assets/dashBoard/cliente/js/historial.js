    // Script para generar PDF
// Meti este script plano para tener la informacion para que se pueda descargar ya solo es poner el php para que traiga los datos de la base de datos y poder asi tener todo mas moderado y ordenado

function generarPDF() {
    const {
        jsPDF
    } = window.jspdf;
    const doc = new jsPDF();

    // Colores corporativos
    const colorPrimario = [41, 128, 185];
    const colorSecundario = [52, 73, 94];
    const colorExito = [39, 174, 96];
    const colorAlerta = [230, 126, 34];
    const colorPeligro = [231, 76, 60];

    let yPos = 20;

    // ENCABEZADO
    doc.setFillColor(...colorPrimario);
    doc.rect(0, 0, 210, 40, 'F');

    doc.setTextColor(255, 255, 255);
    doc.setFontSize(24);
    doc.setFont(undefined, 'bold');
    doc.text('HISTORIAL MÉDICO VETERINARIO', 105, 20, {
        align: 'center'
    });

    doc.setFontSize(12);
    doc.setFont(undefined, 'normal');
    doc.text('VetCare - Sistema de Gestión Veterinaria', 105, 30, {
        align: 'center'
    });

    yPos = 50;

    // INFORMACIÓN DE LA MASCOTA
    doc.setFillColor(240, 240, 240);
    doc.rect(10, yPos, 190, 8, 'F');
    doc.setTextColor(...colorSecundario);
    doc.setFontSize(14);
    doc.setFont(undefined, 'bold');
    doc.text('INFORMACIÓN DEL PACIENTE', 15, yPos + 6);

    yPos += 15;
    doc.setFontSize(10);
    doc.setTextColor(0, 0, 0);

    // Datos de la mascota
    const nombre = document.getElementById('nombre').textContent;
    const especie = document.getElementById('especie').textContent;
    const raza = document.getElementById('raza').textContent;
    const edad = document.getElementById('edad').textContent;
    const sexo = document.getElementById('sexo').textContent;
    const microchip = document.getElementById('microchip').textContent;

    doc.setFont(undefined, 'bold');
    doc.text('Nombre:', 15, yPos);
    doc.setFont(undefined, 'normal');
    doc.text(nombre, 45, yPos);

    doc.setFont(undefined, 'bold');
    doc.text('Especie:', 110, yPos);
    doc.setFont(undefined, 'normal');
    doc.text(especie, 135, yPos);

    yPos += 7;
    doc.setFont(undefined, 'bold');
    doc.text('Raza:', 15, yPos);
    doc.setFont(undefined, 'normal');
    doc.text(raza, 45, yPos);

    doc.setFont(undefined, 'bold');
    doc.text('Edad:', 110, yPos);
    doc.setFont(undefined, 'normal');
    doc.text(edad, 135, yPos);

    yPos += 7;
    doc.setFont(undefined, 'bold');
    doc.text('Sexo:', 15, yPos);
    doc.setFont(undefined, 'normal');
    doc.text(sexo, 45, yPos);

    doc.setFont(undefined, 'bold');
    doc.text('Microchip:', 110, yPos);
    doc.setFont(undefined, 'normal');
    doc.text(microchip, 135, yPos);

    yPos += 15;

    // ALERGIAS (INFORMACIÓN CRÍTICA)
    doc.setFillColor(...colorPeligro);
    doc.rect(10, yPos, 190, 8, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.text('⚠ ALERGIAS Y CONDICIONES IMPORTANTES', 15, yPos + 6);

    yPos += 15;
    doc.setTextColor(...colorPeligro);
    doc.setFontSize(10);
    doc.text('• Penicilina - Reacción severa (Urticaria, dificultad respiratoria)', 15, yPos);
    yPos += 7;
    doc.setTextColor(0, 0, 0);
    doc.text('• No se han registrado alergias alimentarias', 15, yPos);

    yPos += 15;

    // CONSULTAS MÉDICAS
    doc.setFillColor(240, 240, 240);
    doc.rect(10, yPos, 190, 8, 'F');
    doc.setTextColor(...colorSecundario);
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.text('CONSULTAS MÉDICAS', 15, yPos + 6);

    yPos += 12;

    const consultasData = [];
    const consultasRows = document.querySelectorAll('#tablaConsultas tbody tr');
    consultasRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        consultasData.push([
            cells[0].textContent,
            cells[1].textContent,
            cells[2].textContent,
            cells[3].textContent
        ]);
    });

    doc.autoTable({
        startY: yPos,
        head: [
            ['Fecha', 'Motivo', 'Veterinario', 'Diagnóstico']
        ],
        body: consultasData,
        theme: 'striped',
        headStyles: {
            fillColor: colorPrimario,
            textColor: 255
        },
        styles: {
            fontSize: 9
        },
        margin: {
            left: 15,
            right: 15
        }
    });

    yPos = doc.lastAutoTable.finalY + 15;

    // Nueva página si es necesario
    if (yPos > 250) {
        doc.addPage();
        yPos = 20;
    }

    // VACUNAS
    doc.setFillColor(240, 240, 240);
    doc.rect(10, yPos, 190, 8, 'F');
    doc.setTextColor(...colorSecundario);
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.text('REGISTRO DE VACUNACIÓN', 15, yPos + 6);

    yPos += 12;

    const vacunasData = [
        ['Rabia', '20/09/2024', '20/09/2025', 'Al día'],
        ['Múltiple (DHPP)', '20/09/2024', '20/09/2025', 'Al día'],
        ['Tos de las perreras', '15/03/2024', '15/03/2025', 'Próxima'],
        ['Leptospirosis', '20/09/2024', '20/09/2025', 'Al día']
    ];

    doc.autoTable({
        startY: yPos,
        head: [
            ['Vacuna', 'Última Dosis', 'Próxima Dosis', 'Estado']
        ],
        body: vacunasData,
        theme: 'striped',
        headStyles: {
            fillColor: colorExito,
            textColor: 255
        },
        styles: {
            fontSize: 9
        },
        margin: {
            left: 15,
            right: 15
        }
    });

    yPos = doc.lastAutoTable.finalY + 15;

    // Nueva página si es necesario
    if (yPos > 250) {
        doc.addPage();
        yPos = 20;
    }

    // MEDICAMENTOS
    doc.setFillColor(240, 240, 240);
    doc.rect(10, yPos, 190, 8, 'F');
    doc.setTextColor(...colorSecundario);
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.text('HISTORIAL DE MEDICAMENTOS', 15, yPos + 6);

    yPos += 12;

    const medicamentosData = [];
    const medicamentosRows = document.querySelectorAll('#tablaMedicamentos tbody tr');
    medicamentosRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        medicamentosData.push([
            cells[0].textContent,
            cells[1].textContent,
            cells[2].textContent,
            cells[3].textContent + ' - ' + cells[4].textContent
        ]);
    });

    doc.autoTable({
        startY: yPos,
        head: [
            ['Medicamento', 'Dosis', 'Frecuencia', 'Período']
        ],
        body: medicamentosData,
        theme: 'striped',
        headStyles: {
            fillColor: colorPrimario,
            textColor: 255
        },
        styles: {
            fontSize: 9
        },
        margin: {
            left: 15,
            right: 15
        }
    });

    yPos = doc.lastAutoTable.finalY + 15;

    // Nueva página si es necesario
    if (yPos > 250) {
        doc.addPage();
        yPos = 20;
    }

    // EXÁMENES
    doc.setFillColor(240, 240, 240);
    doc.rect(10, yPos, 190, 8, 'F');
    doc.setTextColor(...colorSecundario);
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.text('EXÁMENES Y ESTUDIOS', 15, yPos + 6);

    yPos += 12;

    const examenesData = [];
    const examenesRows = document.querySelectorAll('#tablaExamenes tbody tr');
    examenesRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        examenesData.push([
            cells[0].textContent,
            cells[1].textContent,
            cells[2].textContent,
            cells[3].textContent
        ]);
    });

    doc.autoTable({
        startY: yPos,
        head: [
            ['Fecha', 'Tipo de Examen', 'Resultados', 'Veterinario']
        ],
        body: examenesData,
        theme: 'striped',
        headStyles: {
            fillColor: colorPrimario,
            textColor: 255
        },
        styles: {
            fontSize: 9
        },
        margin: {
            left: 15,
            right: 15
        }
    });

    yPos = doc.lastAutoTable.finalY + 15;

    // Nueva página si es necesario
    if (yPos > 250) {
        doc.addPage();
        yPos = 20;
    }

    // CIRUGÍAS
    doc.setFillColor(240, 240, 240);
    doc.rect(10, yPos, 190, 8, 'F');
    doc.setTextColor(...colorSecundario);
    doc.setFontSize(12);
    doc.setFont(undefined, 'bold');
    doc.text('HISTORIAL QUIRÚRGICO', 15, yPos + 6);

    yPos += 12;

    const cirugiasData = [];
    const cirugiasRows = document.querySelectorAll('#tablaCirugias tbody tr');
    cirugiasRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        cirugiasData.push([
            cells[0].textContent,
            cells[1].textContent,
            cells[2].textContent,
            cells[3].textContent
        ]);
    });

    doc.autoTable({
        startY: yPos,
        head: [
            ['Fecha', 'Procedimiento', 'Veterinario', 'Notas']
        ],
        body: cirugiasData,
        theme: 'striped',
        headStyles: {
            fillColor: colorAlerta,
            textColor: 255
        },
        styles: {
            fontSize: 9
        },
        margin: {
            left: 15,
            right: 15
        }
    });

    // PIE DE PÁGINA
    const totalPages = doc.internal.getNumberOfPages();
    for (let i = 1; i <= totalPages; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(128, 128, 128);
        doc.text(
            'Documento generado el ' + new Date().toLocaleDateString('es-ES') + ' - VetCare © 2024',
            105,
            285, {
            align: 'center'
        }
        );
        doc.text(
            'Página ' + i + ' de ' + totalPages,
            190,
            285, {
            align: 'right'
        }
        );
    }

    // Guardar el PDF
    doc.save('Historial_Medico_' + nombre.replace(/\s+/g, '_') + '.pdf');
}