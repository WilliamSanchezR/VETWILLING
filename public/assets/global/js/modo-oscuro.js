// Modo oscuro común de los paneles del administrador, el representante y el veterinario.
//
// Una sola preferencia en localStorage ("vw_tema": "claro" | "oscuro", la misma clave y valores
// que usa el panel del propietario) y todas las convenciones que ya usa el CSS del proyecto:
//   html[data-theme="dark"], html[data-tema="oscuro"], html[data-bs-theme="dark"] (Bootstrap 5.3),
//   body.dark-theme y body.dark-mode.
// La primera vez recupera la preferencia guardada con las claves antiguas ("theme", "navbar_tema").
//
// API: window.vwTema.obtener() → "claro" | "oscuro"; .cambiar(tema) aplica y guarda; .alternar().
// Emite el evento "vw:tema" en window con { detail: { tema } }.
(function () {
    'use strict';

    const CLAVE = 'vw_tema';
    let tema = 'claro';

    function leerGuardado() {
        try {
            const valor = localStorage.getItem(CLAVE);
            if (valor === 'oscuro' || valor === 'claro') return valor;
            const antiguo = localStorage.getItem('theme') || localStorage.getItem('navbar_tema');
            return antiguo === 'dark' ? 'oscuro' : 'claro';
        } catch (e) {
            return 'claro';
        }
    }

    function guardar(valor) {
        try {
            localStorage.setItem(CLAVE, valor);
            localStorage.removeItem('theme');
            localStorage.removeItem('navbar_tema');
        } catch (e) { /* sin acceso a localStorage: el tema dura lo que la página */ }
    }

    function aplicar(valor) {
        tema = valor === 'oscuro' ? 'oscuro' : 'claro';
        const oscuro = tema === 'oscuro';
        const html = document.documentElement;
        html.setAttribute('data-theme', oscuro ? 'dark' : 'light');
        html.setAttribute('data-tema', tema);
        html.setAttribute('data-bs-theme', oscuro ? 'dark' : 'light');
        if (document.body) {
            document.body.classList.toggle('dark-theme', oscuro);
            document.body.classList.toggle('dark-mode', oscuro);
        }
    }

    function cambiar(valor) {
        aplicar(valor);
        guardar(tema);
        window.dispatchEvent(new CustomEvent('vw:tema', { detail: { tema } }));
        return tema;
    }

    window.vwTema = Object.freeze({
        obtener: () => tema,
        cambiar,
        alternar: () => cambiar(tema === 'oscuro' ? 'claro' : 'oscuro'),
    });

    aplicar(leerGuardado());
    if (!document.body) {
        document.addEventListener('DOMContentLoaded', () => aplicar(tema));
    }
})();
