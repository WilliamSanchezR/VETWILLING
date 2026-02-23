/* ================================================
   SUSCRIPCIÓN — JavaScript
   Archivo: suscripcion.js
   ================================================ */


// ── PRECIOS BASE (mensual) ──────────────────────
const PRICES = {
    essential: 7.9,
    procare:   14.9,
    mastervet: 40.9
};

let modoActual = 'mensual';


// ── TOGGLE MENSUAL / ANUAL ──────────────────────
/**
 * Cambia entre facturación mensual y anual.
 * @param {string} modo  - 'mensual' o 'anual'
 * @param {HTMLElement} btn - El botón que se hizo click
 */
function setMode(modo, btn) {
    modoActual = modo;

    // Quitar clase activa de todos y ponerla en el clickeado
    document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    actualizarPrecios();
}


// ── ACTUALIZAR PRECIOS ──────────────────────────
/**
 * Recalcula y muestra los precios según el modo actual.
 * Si es anual aplica un descuento del 20%.
 */
function actualizarPrecios() {
    const esAnual = modoActual === 'anual';
    const planes  = ['essential', 'procare', 'mastervet'];

    planes.forEach(plan => {
        const base    = PRICES[plan];
        const mostrar = esAnual ? +(base * 0.8).toFixed(1) : base;

        // Actualizar número del precio
        document.getElementById('p-' + plan).textContent = mostrar;

        // Mostrar u ocultar el ahorro anual
        const nota = document.getElementById('n-' + plan);
        if (esAnual) {
            const ahorroTotal = ((base - mostrar) * 12).toFixed(0);
            nota.textContent = `Ahorras $${ahorroTotal} al año`;
        } else {
            nota.textContent = '';
        }
    });
}


// ── FAQ ACORDEÓN ────────────────────────────────
/**
 * Abre o cierra un ítem del FAQ.
 * Solo puede haber uno abierto a la vez.
 * @param {HTMLElement} el - El elemento .faq-q clickeado
 */
function toggleFaq(el) {
    const item   = el.parentElement;
    const abierto = item.classList.contains('open');

    // Cerrar todos
    document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));

    // Si estaba cerrado, abrirlo
    if (!abierto) {
        item.classList.add('open');
    }
}