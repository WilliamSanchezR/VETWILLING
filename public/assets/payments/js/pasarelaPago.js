let pasoActual = 1;
let metodoSeleccionado = '';
let checkoutUrl = '';
let verificarUrl = '';
let intervaloSeguimiento = null;

const baseUrl = window.BASE_URL || (() => {
    const appBase = window.location.pathname.split('/').filter(Boolean)[0] || '';
    return `${window.location.origin}${appBase ? '/' + appBase : ''}`;
})();

// ← NUEVO: mapa de rutas
const rutasVolver = {
    'suscripcion': `${baseUrl}/veterinario/suscripcion`,
    'tienda': `${baseUrl}/cliente/tienda`,
    'inicio': `${baseUrl}/`,
};

function abrirModal() {
    const overlay = document.getElementById('overlay');
    const modalFooter = document.getElementById('modal-footer');

    if (!overlay) return;

    overlay.classList.add('active');
    overlay.setAttribute('aria-hidden', 'false');

    if (modalFooter) {
        modalFooter.style.display = 'flex';
    }

    document.querySelectorAll('.metodo-item').forEach(m => m.classList.remove('selected'));
    metodoSeleccionado = '';
    pasoActual = 1;
    irPaso(1);
}

function cerrarModal() {
    const overlay = document.getElementById('overlay');

    if (!overlay) return;

    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden', 'true');
}

function seleccionarMetodo(el, nombre) {
    document.querySelectorAll('.metodo-item').forEach(m => m.classList.remove('selected'));
    el.classList.add('selected');
    metodoSeleccionado = nombre;
    document.getElementById('btn-next').disabled = false;
}

function irPaso(paso) {
    const modalFooter = document.getElementById('modal-footer');
    const btnNext = document.getElementById('btn-next');
    const btnBack = document.getElementById('btn-back');

    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    const pasoElement = document.getElementById('step-' + paso);
    if (pasoElement) {
        pasoElement.classList.add('active');
    }

    for (let i = 1; i <= 3; i++) {
        const dot = document.getElementById('dot-' + i);
        if (!dot) continue;
        dot.classList.remove('active', 'done');
        if (i < paso) dot.classList.add('done');
        if (i === paso) dot.classList.add('active');
    }

    if (btnBack) {
        btnBack.style.display = (paso > 1 && paso < 3) ? 'block' : 'none';
    }

    if (btnNext) {
        if (paso === 1) {
            btnNext.textContent = 'Continuar →';
            btnNext.disabled = true;
        }

        if (paso === 2) {
            btnNext.textContent = 'Ir a Mercado Pago →';
            btnNext.disabled = false;
        }

        if (paso === 3) {
            btnNext.textContent = 'Procesando...';
            btnNext.disabled = true;
        }
    }

    if (modalFooter) {
        modalFooter.style.display = paso === 3 ? 'none' : 'flex';
    }

    pasoActual = paso;
}

function pasoContinuar() {
    if (pasoActual === 1) {
        irPaso(2);
    } else if (pasoActual === 2) {
        irPaso(3);
        setTimeout(finalizarPago, 1400);
    }
}

function pasoAnterior() {
    if (pasoActual === 2) irPaso(1);
}

function finalizarPago() {
    cerrarModal();

    if (checkoutUrl) {
        window.open(checkoutUrl, '_blank', 'noopener,noreferrer');
    }

    iniciarSeguimiento();
}

// ← FUNCIÓN MODIFICADA
function volverInicio() {
    const params = new URLSearchParams(window.location.search);
    const origen = params.get('origen') || 'inicio';
    const ruta = rutasVolver[origen] || '/';

    // Demo: muestra a dónde iría
    // alert('✅ Redirigiendo a: ' + ruta);

    // PHP real: descomenta esta línea y borra el alert
    window.location.href = ruta;
}

function formatCard(input) {
    let val = input.value.replace(/\D/g, '').substring(0, 16);
    let formatted = val.match(/.{1,4}/g)?.join(' ') || val;
    input.value = formatted;
    const preview = val.padEnd(16, '•').match(/.{1,4}/g).join(' ');
    document.getElementById('preview-numero').textContent = preview;
}

function formatExp(input) {
    let val = input.value.replace(/\D/g, '').substring(0, 4);
    if (val.length >= 2) val = val.substring(0, 2) + '/' + val.substring(2);
    input.value = val;
    document.getElementById('preview-exp').textContent = val || 'MM/AA';
}

function iniciarSeguimiento() {
    if (!verificarUrl || intervaloSeguimiento) {
        return;
    }

    const revisarEstado = async () => {
        try {
            const separador = verificarUrl.includes('?') ? '&' : '?';
            const respuesta = await fetch(verificarUrl + separador + 'format=json', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store'
            });

            if (!respuesta.ok) {
                return;
            }

            const data = await respuesta.json();
            if (data && (data.estado === 'success' || data.estado === 'failure') && data.redirect_url) {
                clearInterval(intervaloSeguimiento);
                intervaloSeguimiento = null;
                window.location.href = data.redirect_url;
            }
        } catch (error) {
            console.debug('No fue posible verificar el pago automáticamente.', error);
        }
    };

    revisarEstado();
    intervaloSeguimiento = setInterval(revisarEstado, 8000);
}

document.addEventListener('DOMContentLoaded', () => {
    const botonPagar = document.getElementById('btnAbrirPasarela');

    if (!botonPagar) {
        return;
    }

    checkoutUrl = botonPagar.dataset.checkoutUrl || '';
    verificarUrl = botonPagar.dataset.verificarUrl || '';

    botonPagar.addEventListener('click', (event) => {
        event.preventDefault();
        abrirModal();
    });
});