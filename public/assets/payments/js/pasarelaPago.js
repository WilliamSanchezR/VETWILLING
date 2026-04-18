let pasoActual = 1;
let metodoSeleccionado = '';

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
    document.getElementById('overlay').classList.add('active');
    pasoActual = 1;
    irPaso(1);
}

function cerrarModal() {
    document.getElementById('overlay').classList.remove('active');
}

function seleccionarMetodo(el, nombre) {
    document.querySelectorAll('.metodo-item').forEach(m => m.classList.remove('selected'));
    el.classList.add('selected');
    metodoSeleccionado = nombre;
    document.getElementById('btn-next').disabled = false;
}

function irPaso(paso) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById('step-' + paso).classList.add('active');

    for (let i = 1; i <= 3; i++) {
        const dot = document.getElementById('dot-' + i);
        dot.classList.remove('active', 'done');
        if (i < paso) dot.classList.add('done');
        if (i === paso) dot.classList.add('active');
    }

    document.getElementById('btn-back').style.display = (paso > 1 && paso < 3) ? 'block' : 'none';

    const btnNext = document.getElementById('btn-next');
    if (paso === 1) { btnNext.textContent = 'Continuar →'; btnNext.disabled = true; }
    if (paso === 2) { btnNext.textContent = 'Pagar $ 150.000 →'; btnNext.disabled = false; }
    if (paso === 3) { document.getElementById('modal-footer').style.display = 'none'; }

    pasoActual = paso;
}

function pasoContinuar() {
    if (pasoActual === 1) {
        if (metodoSeleccionado === 'Tarjeta de crédito/débito') {
            irPaso(2);
        } else {
            irPaso(3);
            setTimeout(finalizarPago, 2800);
        }
    } else if (pasoActual === 2) {
        irPaso(3);
        setTimeout(finalizarPago, 2800);
    }
}

function pasoAnterior() {
    if (pasoActual === 2) irPaso(1);
}

function finalizarPago() {
    cerrarModal();

    const txId = 'WMP-' + Math.random().toString(36).substr(2, 9).toUpperCase();
    const ahora = new Date().toLocaleString('es-CO', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });

    document.getElementById('conf-id').textContent = txId;
    document.getElementById('conf-metodo').textContent = metodoSeleccionado;
    document.getElementById('conf-fecha').textContent = ahora;

    document.getElementById('pagina-checkout').style.display = 'none';
    document.getElementById('pagina-confirmacion').style.display = 'flex';
    document.getElementById('pagina-confirmacion').style.alignItems = 'center';
    document.getElementById('pagina-confirmacion').style.justifyContent = 'center';
    document.getElementById('pagina-confirmacion').style.minHeight = '100vh';
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