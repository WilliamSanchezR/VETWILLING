/* ================================================
   SUSCRIPCIÓN — JS v2.0
   Toggle mensual/anual con animación fluida
   ================================================ */

const PRECIOS = {
  mensual: {
    essential: { num: '7.9',  nota: '' },
    procare:   { num: '14.9', nota: '' },
    mastervet: { num: '40.9', nota: '' },
  },
  anual: {
    essential: { num: '6.3',  nota: '💰 Ahorras $19 al año' },
    procare:   { num: '11.9', nota: '💰 Ahorras $36 al año' },
    mastervet: { num: '32.7', nota: '💰 Ahorras $99 al año' },
  }
};

let modoActual = 'mensual';

/* ────────────────────────────────────────
   TOGGLE — Cambio de modo con animación
──────────────────────────────────────── */
function setMode(modo, btnEl) {
  if (modo === modoActual) return;
  modoActual = modo;

  /* 1. Actualizar botones activos */
  document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
  btnEl.classList.add('active');

  /* 2. Mover el slider animado del toggle */
  moverSliderToggle(btnEl);

  /* 3. Animar los precios */
  animarPrecios(modo);
}

function moverSliderToggle(btnActivo) {
  const wrap   = btnActivo.closest('.toggle-wrap');
  const pseudo = wrap; // usamos la variable CSS para controlar el slider

  const rect     = btnActivo.getBoundingClientRect();
  const wrapRect = wrap.getBoundingClientRect();
  const offset   = rect.left - wrapRect.left - 4; // 4px = padding del wrap

  wrap.style.setProperty('--slider-left',  offset + 'px');
  wrap.style.setProperty('--slider-width', rect.width + 'px');
}

function animarPrecios(modo) {
  const planes = ['essential', 'procare', 'mastervet'];

  planes.forEach((plan, i) => {
    const numEl  = document.getElementById('p-' + plan);
    const notaEl = document.getElementById('n-' + plan);
    if (!numEl) return;

    /* Pequeño stagger entre cards */
    setTimeout(() => {

      /* Fase 1: desaparecer */
      numEl.classList.add('changing');
      if (notaEl) {
        notaEl.classList.add('hidden-note');
      }

      /* Fase 2: cambiar valor y reaparecer */
      setTimeout(() => {
        numEl.textContent = PRECIOS[modo][plan].num;
        numEl.classList.remove('changing');

        if (notaEl) {
          notaEl.textContent = PRECIOS[modo][plan].nota;
          if (PRECIOS[modo][plan].nota) {
            notaEl.classList.remove('hidden-note');
          }
        }
      }, 180); // mitad de la transición CSS (0.35s / 2 ≈ 180ms)

    }, i * 60); // stagger: 0ms, 60ms, 120ms
  });
}

/* ────────────────────────────────────────
   FAQ — Acordeón con animación CSS
──────────────────────────────────────── */
function toggleFaq(headerEl) {
  const item     = headerEl.closest('.faq-item');
  const estaOpen = item.classList.contains('open');

  /* Cerrar todos los demás */
  document.querySelectorAll('.faq-item.open').forEach(el => {
    if (el !== item) el.classList.remove('open');
  });

  /* Toggle del actual */
  item.classList.toggle('open', !estaOpen);
}

/* ────────────────────────────────────────
   INIT — Posicionar slider al cargar
──────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  /* Inicializar slider del toggle en el botón activo */
  const btnActivo = document.querySelector('.toggle-btn.active');
  if (btnActivo) {
    /* Pequeño delay para que el layout esté listo */
    requestAnimationFrame(() => moverSliderToggle(btnActivo));
  }

  /* Hacer que el FAQ use la animación CSS (max-height) en vez de display:none */
  document.querySelectorAll('.faq-a').forEach(el => {
    el.style.display = 'block'; // sobreescribir el display:none del CSS antiguo
  });
});