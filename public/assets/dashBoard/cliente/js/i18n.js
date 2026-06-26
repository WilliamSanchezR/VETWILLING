/**
 * i18n.js — VetWilling · Cliente
 * Módulo compartido de internacionalización para todas las vistas del cliente.
 * Requiere que el layout inyecte window.__prefs y window.__lang_all.
 */
(function () {
    "use strict";

    var _prefs   = window.__prefs    || { idioma: "es", tema: "claro" };
    var _langAll = window.__lang_all || {};
    var _strings = _langAll[_prefs.idioma] || _langAll["es"] || {};

    function _resolve(clave, n) {
        var val = _strings[clave] || ((_langAll["es"] || {})[clave]) || clave;
        if (n !== undefined && n !== null) {
            val = val.replace("{n}", n);
        }
        return val;
    }

    function _aplicarIdioma() {
        document.querySelectorAll("[data-i18n]").forEach(function (el) {
            var clave = el.getAttribute("data-i18n");
            if (!clave) return;
            var n = el.getAttribute("data-i18n-n");
            el.textContent = _resolve(clave, n != null ? Number(n) : undefined);
        });
        var lang = _prefs.idioma === "pt" ? "pt-BR" : _prefs.idioma;
        document.documentElement.lang = lang;
    }

    function _cambiarIdioma(idioma) {
        if (!_langAll[idioma]) return;
        _prefs.idioma = idioma;
        _strings = _langAll[idioma];
        _aplicarIdioma();
    }

    window.VW = {
        t: _resolve,
        lang: function () { return _prefs.idioma; },
        aplicarIdioma: _aplicarIdioma,
        cambiarIdioma: _cambiarIdioma
    };

    document.addEventListener("DOMContentLoaded", _aplicarIdioma);
}());