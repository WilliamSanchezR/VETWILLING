/**
 * dashboard-cliente.js
 * Versión : 1.0
 * Scope   : Solo el dashboard del propietario.
 *           El JS global del módulo (sidebar, tema, búsqueda)
 *           vive en clientes.js y se carga antes que este.
 *
 * Responsabilidades:
 *   1. Panel lateral de notificaciones (abrir / cerrar)
 *   2. Animaciones de entrada (IntersectionObserver)
 *   3. Fecha dinámica en el banner de bienvenida
 *   4. Tooltip accesible en chips (teclado + mouse)
 */

/* ============================================================
   PANEL DE NOTIFICACIONES
   ============================================================ */
class PanelNotificaciones {
    constructor() {
        // Referencias DOM — pueden no existir en otras vistas, se guarda null
        this.overlay  = document.getElementById('notifOverlay');
        this.panel    = document.getElementById('notifPanel');
        this.btnAbrir = document.getElementById('btnNotificaciones');
        this.btnCerrar = document.getElementById('btnCerrarNotif');

        if (!this.panel) return; // guard: no estamos en el dashboard

        this._bindEvents();
    }

    _bindEvents() {
        // Abrir al click en la campanita
        this.btnAbrir?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.abrir();
        });

        // Cerrar con botón X
        this.btnCerrar?.addEventListener('click', () => this.cerrar());

        // Cerrar al click en el overlay
        this.overlay?.addEventListener('click', () => this.cerrar());

        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.panel.classList.contains('activo')) {
                this.cerrar();
            }
        });
    }

    abrir() {
        this.panel.classList.add('activo');
        this.overlay.classList.add('activo');
        document.body.style.overflow = 'hidden'; // bloquea scroll del body
        this.btnCerrar?.focus();                  // foco para accesibilidad

        // Marcar como leídas después de 2 seg (UX: el usuario ya las vio)
        setTimeout(() => this._marcarLeidas(), 2000);
    }

    cerrar() {
        this.panel.classList.remove('activo');
        this.overlay.classList.remove('activo');
        document.body.style.overflow = '';
        this.btnAbrir?.focus(); // devolver foco al botón original
    }

    _marcarLeidas() {
        document.querySelectorAll('.notif-item.no-leida').forEach(item => {
            item.classList.remove('no-leida');
            item.querySelector('.notif-punto')?.remove();
        });

        // Limpiar badge del botón
        const badge = document.querySelector('.notif-badge-count');
        if (badge) badge.textContent = '0';

        const btnBadge = document.querySelector('#btnNotificaciones .notification-badge');
        if (btnBadge) btnBadge.style.display = 'none';
    }
}


/* ============================================================
   FECHA DINÁMICA EN EL BANNER
   ============================================================ */
function inyectarFecha() {
    const el = document.getElementById('dashFecha');
    if (!el) return;

    const ahora = new Date();
    const opciones = {
        weekday : 'long',
        year    : 'numeric',
        month   : 'long',
        day     : 'numeric',
    };

    // Capitalizar primera letra
    const texto = ahora.toLocaleDateString('es-CO', opciones);
    el.textContent = texto.charAt(0).toUpperCase() + texto.slice(1);
}


/* ============================================================
   ANIMACIONES DE ENTRADA
   Usa IntersectionObserver para animar elementos al entrar
   en el viewport, respetando prefers-reduced-motion.
   ============================================================ */
function iniciarAnimacionesEntrada() {
    // Respeto por prefers-reduced-motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('.dash-fade-in').forEach(el => {
            el.classList.add('visible');
        });
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                // Delay escalonado para que no entren todos a la vez
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, i * 80);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });

    document.querySelectorAll('.dash-fade-in').forEach(el => observer.observe(el));
}


/* ============================================================
   TOOLTIP ACCESIBLE EN CHIPS
   Mouse: se gestiona con CSS (:hover en .chip-tooltip).
   Teclado: focus/blur añaden clase .tooltip-visible al chip.
   ============================================================ */
function iniciarTooltipsChips() {
    document.querySelectorAll('.chip-recordatorio').forEach(chip => {
        chip.setAttribute('tabindex', '0');
        chip.setAttribute('role', 'button');

        const tooltip = chip.querySelector('.chip-tooltip');
        if (!tooltip) return;

        // Accesibilidad: aria-describedby apunta al tooltip
        const tooltipId = 'tt-' + Math.random().toString(36).slice(2, 7);
        tooltip.id = tooltipId;
        chip.setAttribute('aria-describedby', tooltipId);

        chip.addEventListener('focus', () => {
            tooltip.style.opacity = '1';
            tooltip.style.transform = 'translateX(-50%) scale(1)';
        });

        chip.addEventListener('blur', () => {
            tooltip.style.opacity = '';
            tooltip.style.transform = '';
        });

        // Enter / Space activan el chip como botón
        chip.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                chip.click();
            }
        });
    });
}


/* ============================================================
   INIT — punto de entrada único
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    new PanelNotificaciones();
    inyectarFecha();
    iniciarAnimacionesEntrada();
    iniciarTooltipsChips();

    console.log('✅ Dashboard cliente VetWilling — listo');
});