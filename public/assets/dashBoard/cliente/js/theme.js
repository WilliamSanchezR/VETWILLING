/**
 * theme.js — VetWilling
 * =============================================================================
 * Módulo autocontenido para la gestión de temas claro/oscuro.
 *
 * Responsabilidades (y SÓLO estas):
 *   1. Aplicar el tema al DOM sin parpadeo (FOUC-safe).
 *   2. Animar el cambio de tema con la clase .theme-transitioning.
 *   3. Persistir la elección en el servidor (API REST) y en localStorage.
 *   4. Mantener sincronizados los botones [data-accion-tema].
 *   5. Exponer una API pública mínima y estable.
 *
 * Dependencias externas: ninguna.
 *   — BASE_URL puede estar en window o no: el módulo lo lee de forma defensiva.
 *   — window.__prefs puede existir o no: el módulo usa fallbacks seguros.
 *
 * Expone: window.Theme (objeto inmutable)
 *
 * API pública:
 *   Theme.aplicar(tema, opciones?)  — aplica 'claro'|'oscuro'
 *   Theme.alternar()                — cambia al opuesto y lo persiste
 *   Theme.obtener()                 — devuelve el tema activo
 *   Theme.init(prefs?)              — re-inicializa desde un objeto de prefs
 *
 * Opciones de aplicar():
 *   { persistir: boolean }   — guarda en servidor (default: false)
 *   { animar: boolean }      — anima el cambio (default: false)
 *
 * Ejemplo de uso:
 *   // Cambiar tema con animación y persistencia:
 *   Theme.aplicar('oscuro', { persistir: true, animar: true });
 *
 *   // Alternar desde un botón toggle:
 *   document.getElementById('btnTema').onclick = () => Theme.alternar();
 *
 * =============================================================================
 */

/* global BASE_URL, window */

const Theme = (() => {
    'use strict';

    // ── Constantes ─────────────────────────────────────────────────────────────
    const TEMAS_VALIDOS    = Object.freeze(['claro', 'oscuro']);
    const LS_KEY           = 'vw_tema';
    const ATTR_HTML        = 'data-tema';
    const CLASS_DARK       = 'dark-theme';
    const CLASS_ANIM       = 'theme-transitioning';
    const TRANS_MS         = 380;   /* ms — debe coincidir con --theme-transition-duration en theme.css */
    const API_ENDPOINT     = '/cliente/api/preferencias/guardar';

    // ── Estado interno ──────────────────────────────────────────────────────────
    let _tema       = 'claro';
    let _guardando  = false;
    let _abortCtrl  = null;     /* AbortController para event listeners */

    // =========================================================================
    // Helpers privados
    // =========================================================================

    /** Lee el tema del localStorage de forma defensiva. */
    function _lsLeer() {
        try { return localStorage.getItem(LS_KEY); } catch (_) { return null; }
    }

    /** Escribe el tema en localStorage de forma defensiva. */
    function _lsEscribir(tema) {
        try { localStorage.setItem(LS_KEY, tema); } catch (_) { /* sin permisos */ }
    }

    /**
     * Aplica el tema directamente al DOM.
     * - Siempre actualiza html[data-tema].
     * - Aplica/quita body.dark-theme (con guard para cuando body aún no existe).
     * - Si `animar` es true, añade la clase de transición y la quita después.
     *
     * @param {string}  tema   - 'claro' | 'oscuro'
     * @param {boolean} animar - ¿activar la transición CSS?
     */
    function _aplicarDOM(tema, animar) {
        // 1. Atributo en <html> (necesario para selectores [data-tema=...])
        document.documentElement.setAttribute(ATTR_HTML, tema);

        // 2. Clase en <body>
        const cb = () => {
            if (!document.body) return;

            if (animar) {
                document.body.classList.add(CLASS_ANIM);
                setTimeout(() => {
                    if (document.body) document.body.classList.remove(CLASS_ANIM);
                }, TRANS_MS + 50);      /* +50ms de margen para que la animación termine */
            }

            document.body.classList.toggle(CLASS_DARK, tema === 'oscuro');
        };

        // Si body ya existe (script al final del body), ejecutar directo.
        // Si no (script en <head>), esperar DOMContentLoaded.
        if (document.body) {
            cb();
        } else {
            document.addEventListener('DOMContentLoaded', cb, { once: true });
        }

        _tema = tema;
    }

    /**
     * Actualiza el estado visual (aria-pressed + clase activa)
     * de todos los botones [data-accion-tema] del documento.
     *
     * @param {string} tema - 'claro' | 'oscuro'
     */
    function _sincronizarBotones(tema) {
        document.querySelectorAll('[data-accion-tema]').forEach(btn => {
            const activo = btn.dataset.accionTema === tema;
            btn.classList.toggle('tema-btn--activo', activo);
            btn.setAttribute('aria-pressed', String(activo));
        });
    }

    /**
     * Guarda el tema en el servidor de forma asíncrona (fire-and-forget).
     * Un fallo de red no afecta la UI (el tema ya está aplicado).
     *
     * @param {string} tema - 'claro' | 'oscuro'
     */
    async function _persistirServidor(tema) {
        if (_guardando) return;
        _guardando = true;

        const base = typeof BASE_URL !== 'undefined' ? BASE_URL : '';

        try {
            const res = await fetch(base + API_ENDPOINT, {
                method  : 'POST',
                headers : { 'Content-Type': 'application/json' },
                body    : JSON.stringify({ tema }),
            });

            if (!res.ok) {
                console.warn('[Theme] El servidor respondió con HTTP', res.status,
                             '— el tema se guardó localmente pero no en el servidor.');
            }
        } catch (err) {
            /* Sin conexión o error de red: el tema queda en localStorage hasta
               la próxima sincronización exitosa. */
            console.warn('[Theme] No se pudo sincronizar con el servidor:', err.message);
        } finally {
            _guardando = false;
        }
    }

    /**
     * Registra los listeners de clic en los botones [data-accion-tema].
     * Limpia los listeners previos antes de añadir nuevos (idempotente).
     */
    function _registrarListeners() {
        /* Limpiar listeners previos para evitar duplicados */
        if (_abortCtrl) {
            _abortCtrl.abort();
        }
        _abortCtrl = new AbortController();
        const { signal } = _abortCtrl;

        document.querySelectorAll('[data-accion-tema]').forEach(btn => {
            btn.addEventListener('click', () => {
                const temaNuevo = btn.dataset.accionTema;
                if (TEMAS_VALIDOS.includes(temaNuevo) && temaNuevo !== _tema) {
                    aplicar(temaNuevo, { persistir: true, animar: true });
                }
            }, { signal });
        });
    }

    // =========================================================================
    // API Pública
    // =========================================================================

    /**
     * Aplica un tema al DOM y opcionalmente lo persiste.
     *
     * @param {string} tema                    - 'claro' | 'oscuro'
     * @param {object} [opts]
     * @param {boolean} [opts.persistir=false] - Guarda en servidor + localStorage
     * @param {boolean} [opts.animar=false]    - Activa la transición CSS
     */
    function aplicar(tema, opts) {
        const opciones  = opts || {};
        const persistir = opciones.persistir === true;
        const animar    = opciones.animar    === true;

        if (!TEMAS_VALIDOS.includes(tema)) {
            console.error('[Theme] Valor inválido:', tema,
                          '— Use "claro" u "oscuro".');
            return;
        }

        _aplicarDOM(tema, animar);
        _sincronizarBotones(tema);
        _lsEscribir(tema);

        if (persistir) {
            _persistirServidor(tema);
        }
    }

    /**
     * Alterna entre claro y oscuro con animación y persistencia.
     *
     * @returns {string} El nuevo tema aplicado ('claro' | 'oscuro')
     */
    function alternar() {
        const nuevo = _tema === 'oscuro' ? 'claro' : 'oscuro';
        aplicar(nuevo, { persistir: true, animar: true });
        return nuevo;
    }

    /**
     * Devuelve el tema actualmente activo.
     *
     * @returns {string} 'claro' | 'oscuro'
     */
    function obtener() {
        return _tema;
    }

    /**
     * (Re)inicializa el módulo con un objeto de preferencias.
     * Útil cuando window.__prefs aún no estaba disponible al cargar el script.
     *
     * @param {object} [prefs={}] - Objeto con la propiedad `tema`
     */
    function init(prefs) {
        const p    = prefs || {};
        const tema = p.tema || _lsLeer() || 'claro';

        /* Sin animación ni persistencia en init: el PHP ya aplica el tema
           en el SSR; solo sincronizamos el estado JS con el DOM real. */
        aplicar(tema, { persistir: false, animar: false });

        /* Re-registrar listeners (útil si el DOM fue modificado) */
        _registrarListeners();
    }

    // =========================================================================
    // Auto-inicialización inmediata (FOUC prevention)
    // =========================================================================
    // Se ejecuta en cuanto el script se parsea — puede estar en <head> o <body>.
    // Aplica el tema SIN animación para que la primera renderización sea correcta.
    (function _autoInit() {
        const tema = (window.__prefs && window.__prefs.tema)
                  || _lsLeer()
                  || 'claro';

        /* Aplicar al DOM directamente (sin animación, sin persistir) */
        _aplicarDOM(tema, false);

        /* Sincronizar botones cuando el DOM esté listo */
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                _sincronizarBotones(_tema);
                _registrarListeners();
            });
        } else {
            /* El script se cargó después de DOMContentLoaded */
            _sincronizarBotones(_tema);
            _registrarListeners();
        }
    })();

    // ── Exponer API pública (objeto sellado) ──────────────────────────────────
    return Object.freeze({ aplicar, alternar, obtener, init });

})();
