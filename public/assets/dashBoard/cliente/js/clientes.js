/**
 * dashboard-cliente.js
 * Versión : 2.0
 * Scope   : Solo el dashboard del propietario.
 */

/* ============================================================
   PANEL DE NOTIFICACIONES
   ============================================================ */
class PanelNotificaciones {
    constructor() {
        this.overlay   = document.getElementById('notifOverlay');
        this.panel     = document.getElementById('notifPanel');
        this.btnAbrir  = document.getElementById('btnNotificaciones');
        this.btnCerrar = document.getElementById('btnCerrarNotif');

        if (!this.panel) return;

        this._bindEvents();
    }

    _bindEvents() {
        this.btnAbrir?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.abrir();
        });

        this.btnCerrar?.addEventListener('click', () => this.cerrar());
        this.overlay?.addEventListener('click', () => this.cerrar());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.panel.classList.contains('activo')) {
                this.cerrar();
            }
        });
    }

    abrir() {
        this.panel.classList.add('activo');
        this.overlay?.classList.add('activo');
        document.body.style.overflow = 'hidden';
        this.btnCerrar?.focus();

        // Marcar como leídas después de 2 seg
        // TODO: cuando el backend esté listo, hacer fetch aquí también:
        // fetch('/notificaciones/marcar-leidas', { method: 'POST' })
        setTimeout(() => this._marcarLeidas(), 2000);
    }

    cerrar() {
        this.panel.classList.remove('activo');
        this.overlay?.classList.remove('activo');
        document.body.style.overflow = '';
        this.btnAbrir?.focus();
    }

    _marcarLeidas() {
        document.querySelectorAll('.notif-item.no-leida').forEach(item => {
            item.classList.remove('no-leida');
            item.querySelector('.notif-punto')?.remove();
        });

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

    const texto = new Date().toLocaleDateString('es-CO', {
        weekday : 'long',
        year    : 'numeric',
        month   : 'long',
        day     : 'numeric',
    });

    el.textContent = texto.charAt(0).toUpperCase() + texto.slice(1);
}


/* ============================================================
   ANIMACIONES DE ENTRADA
   ============================================================ */
function iniciarAnimacionesEntrada() {
    const elementos = document.querySelectorAll('.dash-fade-in');

    // Sin animación si el usuario lo prefiere
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        elementos.forEach(el => el.classList.add('visible'));
        return;
    }

    const MAX_DELAY = 300; // ms — evita que elementos tardíos entren demasiado tarde

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (!entry.isIntersecting) return;

            const delay = Math.min(i * 80, MAX_DELAY);
            setTimeout(() => entry.target.classList.add('visible'), delay);
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.08 });

    elementos.forEach(el => observer.observe(el));
}


/* ============================================================
   TOOLTIPS ACCESIBLES EN CHIPS (teclado + mouse)
   ============================================================ */
function iniciarTooltipsChips() {
    document.querySelectorAll('.chip-recordatorio').forEach(chip => {
        const tooltip = chip.querySelector('.chip-tooltip');
        if (!tooltip) return;

        // Vincular tooltip al chip para lectores de pantalla
        const id = 'tt-' + Math.random().toString(36).slice(2, 7);
        tooltip.id = id;
        chip.setAttribute('aria-describedby', id);

        const mostrar = () => {
            tooltip.style.opacity   = '1';
            tooltip.style.transform = 'translateX(-50%) scale(1)';
        };

        const ocultar = () => {
            tooltip.style.opacity   = '';
            tooltip.style.transform = '';
        };

        chip.addEventListener('focus', mostrar);
        chip.addEventListener('blur',  ocultar);

        chip.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                chip.click();
            }
        });
    });
}


/* ============================================================
   INIT
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    new PanelNotificaciones();
    inyectarFecha();
    iniciarAnimacionesEntrada();
    iniciarTooltipsChips();
});