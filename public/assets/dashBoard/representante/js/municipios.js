/**
 * Lista de municipios de Colombia
 * Organizados por departamentos
 */

const municipios = {
    principales: [
        'Bogotá',
        'Medellín',
        'Cali',
        'Barranquilla',
        'Cartagena',
        'Bucaramanga',
        'Cúcuta',
        'Pereira',
        'Manizales',
        'Armenia',
        'Ibagué',
        'Villavicencio',
        'Neiva',
        'Pasto',
        'Popayán',
        'Tunja',
        'Montería',
        'Sincelejo',
        'Valledupar',
        'Riohacha',
        'Santa Marta',
        'San Andrés',
        'Leticia',
        'Mocoa',
        'Yopal',
        'Arauca',
        'Florencia',
        'Quibdó',
        'Inírida',
        'Mitú',
        'Puerto Carreño'
    ],
    
    areaSantafé: [
        'Bogotá',
        'Soacha',
        'Chía',
        'Zipaquirá',
        'Girardot',
        'Facatativá',
        'Fusagasugá'
    ],
    
    areaMedellin: [
        'Medellín',
        'Envigado',
        'Itagüí',
        'Bello',
        'Rionegro'
    ],
    
    areaCali: [
        'Cali',
        'Palmira',
        'Tuluá',
        'Buenaventura'
    ],
    
    areaEje: [
        'Pereira',
        'Dosquebradas',
        'La Dorada'
    ]
};

/**
 * Obtiene la lista completa de municipios sin duplicados y ordenados
 * @returns {Array} Array de municipios ordenados alfabéticamente
 */
function obtenerTodosMunicipios() {
    const todosMunicipios = new Set();
    
    Object.values(municipios).forEach(grupo => {
        grupo.forEach(municipio => todosMunicipios.add(municipio));
    });
    
    return Array.from(todosMunicipios).sort();
}

/**
 * Renderiza los municipios en un select HTML
 * @param {string} selectId - ID del elemento select
 * @param {string} municipioActual - Municipio actualmente seleccionado (opcional)
 */
function renderizarMunicipios(selectId, municipioActual = null) {
    const select = document.getElementById(selectId);
    
    if (!select) {
        console.error(`Select con ID "${selectId}" no encontrado`);
        return;
    }
    
    // Limpiar opciones existentes
    select.innerHTML = '';
    
    // Agregar opción por defecto con el municipio actual
    if (municipioActual) {
        const optionDefault = document.createElement('option');
        optionDefault.value = municipioActual;
        optionDefault.textContent = municipioActual;
        optionDefault.selected = true;
        select.appendChild(optionDefault);
    } else {
        const optionDefault = document.createElement('option');
        optionDefault.value = '';
        optionDefault.textContent = 'Selecciona un municipio...';
        optionDefault.disabled = true;
        select.appendChild(optionDefault);
    }
    
    // Agregar separador visual (optgroup para principales)
    const optgroupPrincipales = document.createElement('optgroup');
    optgroupPrincipales.label = 'Principales ciudades';
    
    municipios.principales.forEach(municipio => {
        if (municipio !== municipioActual) {
            const option = document.createElement('option');
            option.value = municipio;
            option.textContent = municipio;
            optgroupPrincipales.appendChild(option);
        }
    });
    
    select.appendChild(optgroupPrincipales);
    
    // Agregar otras áreas metropolitanas
    const areasMetropolitanas = [
        { label: 'Área Metropolitana de Bogotá', municipios: municipios.areaSantafé },
        { label: 'Área Metropolitana de Medellín', municipios: municipios.areaMedellin },
        { label: 'Área Metropolitana de Cali', municipios: municipios.areaCali },
        { label: 'Área Metropolitana Eje Cafetero', municipios: municipios.areaEje }
    ];
    
    areasMetropolitanas.forEach(area => {
        const optgroup = document.createElement('optgroup');
        optgroup.label = area.label;
        
        area.municipios.forEach(municipio => {
            // Evitar duplicados
            if (municipio !== municipioActual && !municipios.principales.includes(municipio)) {
                const option = document.createElement('option');
                option.value = municipio;
                option.textContent = municipio;
                optgroup.appendChild(option);
            }
        });
        
        if (optgroup.children.length > 0) {
            select.appendChild(optgroup);
        }
    });
}

/**
 * Busca municipios que coincidan con el texto ingresado
 * @param {string} busqueda - Texto a buscar
 * @returns {Array} Array de municipios que coinciden
 */
function buscarMunicipios(busqueda) {
    const busquedaLower = busqueda.toLowerCase();
    const todosMunicipios = obtenerTodosMunicipios();
    
    return todosMunicipios.filter(municipio => 
        municipio.toLowerCase().includes(busquedaLower)
    );
}

// Exportar para uso en módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        municipios,
        obtenerTodosMunicipios,
        renderizarMunicipios,
        buscarMunicipios
    };
}
