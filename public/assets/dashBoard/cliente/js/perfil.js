/**
 * perfil.js – VetCare Dashboard
 * Lógica de la vista de perfil del cliente
 */

(function () {
  'use strict';

  /* ── Cambio de foto de perfil ─────────────────────────── */
  const btnCamera  = document.getElementById('btn-camera');
  const uploadLogo = document.getElementById('upload-logo');
  const formFoto   = document.getElementById('form_cambio_imagen');

  if (btnCamera && uploadLogo && formFoto) {
    btnCamera.addEventListener('click', (e) => {
      e.preventDefault();
      uploadLogo.click();
    });

    uploadLogo.addEventListener('change', function () {
      if (this.files && this.files.length > 0) {
        // Preview rápido antes de enviar (opcional pero elegante)
        const reader = new FileReader();
        reader.onload = (e) => {
          const img = formFoto.querySelector('.fotito');
          if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);

        // Submit
        formFoto.submit();
      }
    });
  }

  /* ── Tabs ────────────────────────────────────────────── */
  const tabs    = document.querySelectorAll('.tab-perfil');
  const tabPanels = document.querySelectorAll('.tab-content');

  tabs.forEach(tab => {
    tab.addEventListener('click', function () {
      const target = this.dataset.tab;

      // Activar tab
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');

      // Mostrar panel correspondiente con fade
      tabPanels.forEach(panel => {
        const isTarget = panel.id === `tab-${target}`;
        if (isTarget) {
          panel.classList.remove('d-none');
          panel.style.opacity = '0';
          panel.style.transform = 'translateY(10px)';
          requestAnimationFrame(() => {
            panel.style.transition = 'opacity .3s ease, transform .3s ease';
            panel.style.opacity = '1';
            panel.style.transform = 'translateY(0)';
          });
        } else {
          panel.classList.add('d-none');
          panel.style.opacity = '';
          panel.style.transform = '';
        }
      });
    });
  });

  /* ── Toggle mostrar/ocultar contraseña ───────────────── */
  document.querySelectorAll('.toggle-pass').forEach(btn => {
    btn.addEventListener('click', function () {
      const targetId = this.dataset.target;
      const input    = document.getElementById(targetId);
      if (!input) return;
      const isPass = input.type === 'password';
      input.type   = isPass ? 'text' : 'password';
      const icon   = this.querySelector('i');
      icon.className = isPass ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
    });
  });

  /* ── Medidor de fortaleza de contraseña ──────────────── */
  const passNueva   = document.getElementById('pass-nueva');
  const segments    = [1,2,3,4].map(n => document.getElementById(`seg-${n}`));
  const strengthLbl = document.getElementById('strength-label');

  if (passNueva && segments[0] && strengthLbl) {
    passNueva.addEventListener('input', function () {
      const val = this.value;
      let score = 0;

      if (val.length >= 8)            score++;
      if (/[A-Z]/.test(val))          score++;
      if (/[0-9]/.test(val))          score++;
      if (/[^A-Za-z0-9]/.test(val))   score++;

      const classes = ['', 'active-weak', 'active-medium', 'active-medium', 'active-strong'];
      const labels  = ['Ingresa una contraseña', 'Muy débil', 'Débil', 'Aceptable', 'Segura'];

      segments.forEach((seg, i) => {
        seg.className = 'strength-segment';
        if (i < score) seg.classList.add(classes[score]);
      });

      strengthLbl.textContent = val.length === 0 ? labels[0] : labels[score];
    });
  }

  /* ── Stagger animation para cards ────────────────────── */
  document.querySelectorAll('.card-perfil').forEach((card, i) => {
    card.style.animationDelay = `${i * 0.08}s`;
  });

  /* ── Stat cards: contador animado ────────────────────── */
  const statNumbers = document.querySelectorAll('.stat-card-number');

  const animateCounter = (el) => {
    const target = parseFloat(el.textContent);
    if (isNaN(target)) return;

    const isDecimal = el.textContent.includes('.');
    const duration  = 900;
    const start     = performance.now();

    const step = (now) => {
      const progress = Math.min((now - start) / duration, 1);
      const eased    = 1 - Math.pow(1 - progress, 3); // ease-out cubic
      const current  = target * eased;
      el.textContent = isDecimal ? current.toFixed(1) : Math.floor(current);
      if (progress < 1) requestAnimationFrame(step);
    };

    requestAnimationFrame(step);
  };

  // Disparar cuando sean visibles
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    statNumbers.forEach(el => observer.observe(el));
  } else {
    // Fallback: animar de inmediato
    statNumbers.forEach(animateCounter);
  }

  console.log('✅ perfil.js cargado correctamente');
})();