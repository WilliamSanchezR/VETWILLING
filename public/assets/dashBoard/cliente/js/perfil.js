/**
 * perfil.js – VetCare Dashboard v3.0
 * Maneja: tabs, foto de perfil, contraseña, sesiones activas (AJAX)
 */

(function () {
  'use strict';

  const BASE = window.BASE_URL || '';

  /* ══════════════════════════════════════════════════════════
     CAMBIO DE FOTO DE PERFIL
  ══════════════════════════════════════════════════════════ */
  const btnCamera  = document.getElementById('btn-camera');
  const uploadLogo = document.getElementById('upload-logo');
  const formFoto   = document.getElementById('form_cambio_imagen');

  if (btnCamera && uploadLogo && formFoto) {
    btnCamera.addEventListener('click', (e) => {
      e.preventDefault();
      uploadLogo.click();
    });

    uploadLogo.addEventListener('change', function () {
      if (!this.files?.length) return;

      // Preview rápido
      const reader = new FileReader();
      reader.onload = (e) => {
        const img = formFoto.querySelector('.fotito');
        if (img) img.src = e.target.result;
      };
      reader.readAsDataURL(this.files[0]);

      formFoto.submit();
    });
  }


  /* ══════════════════════════════════════════════════════════
     TABS
  ══════════════════════════════════════════════════════════ */
  const tabs      = document.querySelectorAll('.tab-perfil');
  const tabPanels = document.querySelectorAll('.tab-content');

  tabs.forEach(tab => {
    tab.addEventListener('click', function () {
      const target = this.dataset.tab;

      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');

      tabPanels.forEach(panel => {
        const isTarget = panel.id === `tab-${target}`;

        if (isTarget) {
          panel.classList.remove('d-none');
          panel.style.opacity = '0';
          panel.style.transform = 'translateY(10px)';

          requestAnimationFrame(() => {
            panel.style.transition = 'opacity .28s ease, transform .28s ease';
            panel.style.opacity = '1';
            panel.style.transform = 'translateY(0)';
          });
        } else {
          panel.classList.add('d-none');
          panel.style.opacity  = '';
          panel.style.transform = '';
        }
      });
    });
  });


  /* ══════════════════════════════════════════════════════════
     MOSTRAR / OCULTAR CONTRASEÑA
  ══════════════════════════════════════════════════════════ */
  document.querySelectorAll('.toggle-pass').forEach(btn => {
    btn.addEventListener('click', function () {
      const input = document.getElementById(this.dataset.target);
      if (!input) return;
      const isPass = input.type === 'password';
      input.type = isPass ? 'text' : 'password';
      const icon = this.querySelector('i');
      icon.className = isPass ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
    });
  });


  /* ══════════════════════════════════════════════════════════
     MEDIDOR DE FORTALEZA DE CONTRASEÑA
  ══════════════════════════════════════════════════════════ */
  const passNueva   = document.getElementById('pass-nueva');
  const segments    = [1, 2, 3, 4].map(n => document.getElementById(`seg-${n}`));
  const strengthLbl = document.getElementById('strength-label');

  if (passNueva && segments[0] && strengthLbl) {
    passNueva.addEventListener('input', function () {
      const val = this.value;
      let score = 0;

      if (val.length >= 8)           score++;
      if (/[A-Z]/.test(val))         score++;
      if (/[0-9]/.test(val))         score++;
      if (/[^A-Za-z0-9]/.test(val))  score++;

      const cls    = ['', 'active-weak', 'active-medium', 'active-medium', 'active-strong'];
      const labels = [
        'Ingresa una contraseña',
        'Muy débil',
        'Débil',
        'Aceptable',
        '¡Contraseña segura!',
      ];

      segments.forEach((seg, i) => {
        seg.className = 'strength-segment';
        if (i < score) seg.classList.add(cls[score]);
      });

      strengthLbl.textContent = val.length === 0 ? labels[0] : labels[score];
    });
  }


  /* ══════════════════════════════════════════════════════════
     SESIONES ACTIVAS — AJAX
  ══════════════════════════════════════════════════════════ */

  /**
   * Envía una acción al servidor vía POST y devuelve la respuesta JSON.
   */
  async function postSesion(payload) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(payload)) fd.append(k, v);

    const res = await fetch(window.location.href, { method: 'POST', body: fd });
    return res.json();
  }

  /**
   * Cierra una sesión individual al hacer click en su botón.
   */
  document.querySelectorAll('.btn-cerrar-sesion').forEach(btn => {
    btn.addEventListener('click', async function () {
      const card  = this.closest('.session-item');
      const token = this.dataset.token || card?.dataset.token;
      if (!token) return;

      // Feedback inmediato
      card?.classList.add('removing');
      this.disabled = true;

      try {
        const data = await postSesion({ accion: 'cerrar_sesion', token });

        if (data.ok) {
          card?.remove();
          actualizarContadorSesiones();
        } else {
          card?.classList.remove('removing');
          this.disabled = false;
          mostrarToast('No se pudo cerrar la sesión.', 'error');
        }
      } catch {
        card?.classList.remove('removing');
        this.disabled = false;
        mostrarToast('Error de conexión.', 'error');
      }
    });
  });

  /**
   * Cierra TODAS las sesiones excepto la actual.
   */
  const btnCerrarTodas = document.getElementById('btn-cerrar-todas');

  if (btnCerrarTodas) {
    btnCerrarTodas.addEventListener('click', async function () {
      if (!confirm('¿Cerrar todas las demás sesiones activas?')) return;

      this.disabled = true;
      this.innerHTML = '<i class="bi bi-hourglass-split"></i> Cerrando...';

      try {
        const data = await postSesion({ accion: 'cerrar_todas' });

        if (data.ok) {
          // Eliminar todas las tarjetas excepto la sesión actual
          document.querySelectorAll('.session-item:not(.current)').forEach(el => el.remove());
          this.remove();
          mostrarToast(`${data.cerradas ?? 'Las'} sesiones cerradas correctamente.`, 'success');
        } else {
          mostrarToast('Ocurrió un error.', 'error');
          this.disabled = false;
          this.innerHTML = '<i class="bi bi-shield-x"></i> Cerrar todas las otras sesiones';
        }
      } catch {
        mostrarToast('Error de conexión.', 'error');
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-shield-x"></i> Cerrar todas las otras sesiones';
      }
    });
  }

  /**
   * Si no quedan sesiones extra, quita el botón "cerrar todas".
   */
  function actualizarContadorSesiones() {
    const restantes = document.querySelectorAll('.session-item:not(.current)').length;
    if (restantes === 0) btnCerrarTodas?.remove();
  }


  /* ══════════════════════════════════════════════════════════
     TOAST DE NOTIFICACIÓN LIGERO
  ══════════════════════════════════════════════════════════ */
  function mostrarToast(mensaje, tipo = 'success') {
    const existente = document.getElementById('perfil-toast');
    if (existente) existente.remove();

    const colores = {
      success: { bg: '#E8F9E8', color: '#0A7A24', border: 'rgba(10,147,44,.25)', icon: 'bi-check-circle-fill' },
      error:   { bg: '#FEF2F2', color: '#991B1B', border: 'rgba(239,68,68,.25)',  icon: 'bi-x-circle-fill'    },
    };
    const c = colores[tipo] || colores.success;

    const toast = document.createElement('div');
    toast.id = 'perfil-toast';
    toast.style.cssText = `
      position: fixed;
      bottom: 24px;
      right: 24px;
      background: ${c.bg};
      color: ${c.color};
      border: 1px solid ${c.border};
      border-radius: 12px;
      padding: 13px 20px;
      font-size: 14px;
      font-weight: 600;
      font-family: 'Plus Jakarta Sans', sans-serif;
      box-shadow: 0 8px 24px rgba(0,0,0,.12);
      display: flex;
      align-items: center;
      gap: 9px;
      z-index: 9999;
      max-width: 340px;
      animation: toastIn .3s ease-out;
    `;

    toast.innerHTML = `<i class="bi ${c.icon}" style="font-size:18px;flex-shrink:0"></i> ${mensaje}`;
    document.body.appendChild(toast);

    // Agregar keyframe si no existe
    if (!document.getElementById('toast-style')) {
      const style = document.createElement('style');
      style.id = 'toast-style';
      style.textContent = `
        @keyframes toastIn  { from { opacity:0; transform:translateY(14px) } to { opacity:1; transform:translateY(0) } }
        @keyframes toastOut { from { opacity:1; transform:translateY(0)    } to { opacity:0; transform:translateY(14px) } }
      `;
      document.head.appendChild(style);
    }

    setTimeout(() => {
      toast.style.animation = 'toastOut .3s ease-in forwards';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  }


  /* ══════════════════════════════════════════════════════════
     ANIMACIÓN ESCALONADA — CARDS
  ══════════════════════════════════════════════════════════ */
  document.querySelectorAll('.card-perfil').forEach((card, i) => {
    card.style.animationDelay = `${i * 0.07}s`;
  });


  /* ══════════════════════════════════════════════════════════
     STAT CARDS — CONTADOR ANIMADO
  ══════════════════════════════════════════════════════════ */
  const animarContador = (el) => {
    const target = parseFloat(el.textContent);
    if (isNaN(target)) return;

    const isDecimal = el.textContent.includes('.');
    const dur = 800;
    const start = performance.now();

    const step = (now) => {
      const p = Math.min((now - start) / dur, 1);
      const e = 1 - Math.pow(1 - p, 3);
      el.textContent = isDecimal ? (target * e).toFixed(1) : Math.floor(target * e);
      if (p < 1) requestAnimationFrame(step);
    };

    requestAnimationFrame(step);
  };

  if ('IntersectionObserver' in window) {
    const obs = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animarContador(entry.target);
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    document.querySelectorAll('.stat-card-number').forEach(el => obs.observe(el));
  } else {
    document.querySelectorAll('.stat-card-number').forEach(animarContador);
  }

})();