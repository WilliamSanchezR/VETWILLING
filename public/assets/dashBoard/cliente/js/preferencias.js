/**
 * preferencias.js – VetWilling
 * Sistema de preferencias de usuario: tema, idioma, zona horaria,
 * formato de fecha y notificaciones.
 *
 * Depende de: BASE_URL (global PHP → JS), window.__prefs (inyectado por confi.php)
 *
 * Flujo:
 *  1. confi.php inyecta window.__prefs con las preferencias del servidor.
 *  2. Este script las aplica inmediatamente al DOM (tema, idioma).
 *  3. Cuando el usuario cambia algo, guarda via API y re-aplica.
 */

/* ================================================================
   MÓDULO DE PREFERENCIAS
================================================================ */
const Preferencias = (() => {
  /* Cache en memoria de las prefs actuales */
  let _prefs = {};

  /* Strings de traducción cargados */
  let _strings = {};

  /* ── Inicializar ── */
  async function init() {
    /* window.__prefs viene inyectado por PHP en confi.php */
    _prefs = window.__prefs || {};

    /* Cargar strings del idioma actual */
    await _cargarStrings(_prefs.idioma || "es");

    /* Aplicar todo al DOM de inmediato */
    _aplicarTema(_prefs.tema || "claro");
    _aplicarIdioma();
    _poblarSelectores();
    _inicializarEventos();
    _inicializarAvatar();
  }

  /* ── Cargar strings de traducción desde el servidor ── */
  async function _cargarStrings(idioma) {
    try {
      const r = await fetch(
        `${BASE_URL}/cliente/api/preferencias/lang?idioma=${idioma}`,
      );
      const data = await r.json();
      _strings = data.strings || {};
    } catch (_) {
      /* Si falla, _strings queda vacío y t() devuelve la clave */
    }
  }

  /* ── Traducir una clave ── */
  function t(clave, n = null) {
    let texto = _strings[clave] || clave;
    if (n !== null) texto = texto.replace("{n}", n);
    return texto;
  }

  /* ── Aplicar tema oscuro/claro ── */
  /* Delega en Theme (theme.js) cuando está disponible. */
  function _aplicarTema(tema) {
    if (typeof Theme !== "undefined" && Theme.aplicar) {
      Theme.aplicar(tema, { persistir: false, animar: false });
    } else {
      /* Fallback: theme.js no cargó */
      document.documentElement.setAttribute("data-tema", tema);
      if (document.body) {
        document.body.classList.toggle("dark-theme", tema === "oscuro");
      }
    }
    _prefs.tema = tema;
  }

  /* ── Aplicar idioma: reemplaza texto de todos los elementos data-i18n ── */
  function _aplicarIdioma() {
    document.querySelectorAll("[data-i18n]").forEach((el) => {
      const clave = el.dataset.i18n;
      const traduccion = t(clave);
      if (traduccion && traduccion !== clave) {
        /* placeholder vs textContent */
        if (el.hasAttribute("placeholder")) {
          el.setAttribute("placeholder", traduccion);
        } else {
          el.textContent = traduccion;
        }
      }
    });

    /* Actualizar atributo lang del <html> */
    const mapa = { es: "es", en: "en", pt: "pt-BR" };
    document.documentElement.lang = mapa[_prefs.idioma] || "es";
  }

  /* ── Poblar selectores con el valor guardado ── */
  function _poblarSelectores() {
    const selectores = {
      selectIdioma: "idioma",
      selectZonaHoraria: "zona_horaria",
      selectFormatoFecha: "formato_fecha",
    };

    Object.entries(selectores).forEach(([id, clave]) => {
      const el = document.getElementById(id);
      if (el && _prefs[clave]) el.value = _prefs[clave];
    });

    /* Botones de tema */
    document.querySelectorAll("[data-accion-tema]").forEach((btn) => {
      btn.classList.toggle(
        "tema-btn--activo",
        btn.dataset.accionTema === (_prefs.tema || "claro"),
      );
    });

    /* Radio de notificaciones */
    const radio = document.querySelector(
      `input[name="preferencia_notificacion"][value="${_prefs.notificaciones || "email"}"]`,
    );
    if (radio) radio.checked = true;
  }

  /* ── Guardar una o varias preferencias ── */
  async function guardar(cambios) {
    try {
      const r = await fetch(`${BASE_URL}/cliente/api/preferencias/guardar`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(cambios),
      });
      const data = await r.json();

      if (data.status === "ok") {
        /* Actualizar cache local */
        Object.assign(_prefs, data.preferencias);

        /* Re-aplicar idioma si cambió.
         * El tema NO se re-aplica aquí: theme.js ya lo aplicó de
         * forma instantánea cuando el usuario hizo clic en el botón.
         * Llamar _aplicarTema de nuevo sería redundante (y quita la
         * animación ya en curso). Sólo sincronizamos _prefs. */
        if (cambios.tema) {
          _prefs.tema = data.preferencias.tema || cambios.tema;
        }
        if (cambios.idioma) {
          await _cargarStrings(cambios.idioma);
          _aplicarIdioma();
        }

        Toast.mostrar(t("toast.prefs.ok"), "success");
        return true;
      } else {
        Toast.mostrar(t("toast.prefs.error"), "error");
        return false;
      }
    } catch (_) {
      Toast.mostrar(t("toast.conexion"), "error");
      return false;
    }
  }

  /* ── Inicializar todos los eventos de preferencias ── */
  function _inicializarEventos() {
    /* Selector de idioma */
    const selIdioma = document.getElementById("selectIdioma");
    if (selIdioma) {
      selIdioma.addEventListener("change", () => {
        guardar({ idioma: selIdioma.value });
      });
    }

    /* Selector de zona horaria */
    const selZona = document.getElementById("selectZonaHoraria");
    if (selZona) {
      selZona.addEventListener("change", () => {
        guardar({ zona_horaria: selZona.value });
      });
    }

    /* Selector de formato de fecha */
    const selFecha = document.getElementById("selectFormatoFecha");
    if (selFecha) {
      selFecha.addEventListener("change", () => {
        guardar({ formato_fecha: selFecha.value });
      });
    }

    /* Botones de tema (claro / oscuro) ──────────────────────────────────
     * Theme.js registra sus propios listeners en los botones [data-accion-tema]
     * y se ocupa de la animación y la persistencia (llamando a la API REST).
     * Preferencias.js ya NO duplica esos listeners para evitar doble guardado.
     * Si theme.js NO está disponible (entorno sin theme.js), añadimos
     * el listener de fallback aquí.
     * ─────────────────────────────────────────────────────────────────── */
    if (typeof Theme === "undefined") {
      document.querySelectorAll("[data-accion-tema]").forEach((btn) => {
        btn.addEventListener("click", () => {
          guardar({ tema: btn.dataset.accionTema });
        });
      });
    }

    /* Radio de notificaciones */
    document
      .querySelectorAll('input[name="preferencia_notificacion"]')
      .forEach((radio) => {
        radio.addEventListener("change", () => {
          guardar({ notificaciones: radio.value });
        });
      });
  }

  /* ── Avatar con iniciales ── */
  function _inicializarAvatar() {
    document.querySelectorAll("img[data-avatar-fallback]").forEach((img) => {
      /* Si ya falló antes de que corriéramos el script */
      if (!img.complete || img.naturalWidth === 0) {
        _reemplazarConIniciales(img);
        return;
      }

      /* Listener para cuando falle en el futuro */
      img.addEventListener("error", () => _reemplazarConIniciales(img), {
        once: true,
      });
    });
  }

  /**
   * Reemplaza la img rota con un <canvas> que muestra las iniciales.
   * No hace ninguna petición HTTP adicional → elimina el bucle de carga.
   */
  function _reemplazarConIniciales(img) {
    const iniciales = (img.dataset.avatarFallback || "?")
      .toUpperCase()
      .slice(0, 2);
    const size = img.width || img.offsetWidth || 80;
    const colores = ["#4f8ef7", "#7c5cbf", "#27ae8f", "#e06c5a", "#d4a017"];
    const color = colores[iniciales.charCodeAt(0) % colores.length];

    const canvas = document.createElement("canvas");
    canvas.width = size;
    canvas.height = size;
    canvas.className = img.className;
    canvas.style.cssText = img.style.cssText;
    canvas.setAttribute("role", "img");
    canvas.setAttribute("aria-label", img.alt || iniciales);

    const ctx = canvas.getContext("2d");

    /* Fondo circular */
    ctx.beginPath();
    ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
    ctx.fillStyle = color;
    ctx.fill();

    /* Texto */
    ctx.fillStyle = "#ffffff";
    ctx.font = `bold ${Math.round(size * 0.38)}px system-ui, sans-serif`;
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(iniciales, size / 2, size / 2 + 1);

    /* Reemplazar en el DOM: img → canvas */
    img.replaceWith(canvas);
  }

  /* ── API pública del módulo ── */
  return { init, guardar, t, aplicarTema: _aplicarTema };
})();

/* ================================================================
   MÓDULO TOAST (centralizado, sin duplicar en confi.php)
================================================================ */
const Toast = (() => {
  function mostrar(mensaje, tipo = "success") {
    const container = document.getElementById("toastContainer");
    if (!container) return;

    const toast = document.createElement("div");
    toast.className = `toast-notification toast-${tipo}`;

    const iconos = {
      success: "bi-check-circle-fill",
      error: "bi-x-circle-fill",
      warning: "bi-exclamation-triangle-fill",
      info: "bi-info-circle-fill",
    };

    toast.innerHTML = `
            <i class="bi ${iconos[tipo] || iconos.info}" aria-hidden="true"></i>
            <span>${mensaje}</span>
        `;

    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add("show"));

    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  }

  return { mostrar };
})();

/* ================================================================
   SESIONES ACTIVAS
================================================================ */
function cerrarSesion(token, btnEl) {
  if (!confirm(Preferencias.t("toast.confirmar.sesion"))) return;

  btnEl.disabled = true;
  btnEl.innerHTML = `<i class="bi bi-hourglass-split"></i> ${Preferencias.t("general.cerrando")}`;

  fetch(`${BASE_URL}/cliente/api/sesiones/cerrar`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ token }),
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.status === "ok") {
        const item = document.querySelector(
          `.sesion-item[data-token="${token}"]`,
        );
        if (item) {
          item.style.transition = "opacity .3s ease, transform .3s ease";
          item.style.opacity = "0";
          item.style.transform = "translateX(20px)";
          setTimeout(() => {
            item.remove();
            const restantes = document.querySelectorAll(
              ".sesion-item:not(.sesion-item--actual)",
            );
            if (!restantes.length) {
              document.querySelector(".sesiones-footer")?.remove();
            }
          }, 300);
        }
        Toast.mostrar(Preferencias.t("seg.sesion.ok"), "success");
      } else {
        btnEl.disabled = false;
        btnEl.innerHTML = `<i class="bi bi-box-arrow-right"></i> ${Preferencias.t("seg.cerrar")}`;
        const mensajes = {
          not_found: Preferencias.t("seg.sesion.not_found"),
          is_current: Preferencias.t("seg.sesion.is_current"),
          write_error: Preferencias.t("seg.sesion.error"),
        };
        Toast.mostrar(
          mensajes[data.code] || Preferencias.t("seg.sesion.error"),
          "error",
        );
      }
    })
    .catch(() => {
      btnEl.disabled = false;
      btnEl.innerHTML = `<i class="bi bi-box-arrow-right"></i> ${Preferencias.t("seg.cerrar")}`;
      Toast.mostrar(Preferencias.t("toast.conexion"), "error");
    });
}

function cerrarTodasSesiones(btnEl) {
  if (!confirm(Preferencias.t("toast.confirmar.todas"))) return;

  btnEl.disabled = true;
  btnEl.innerHTML = `<i class="bi bi-hourglass-split"></i> ${Preferencias.t("general.cerrando")}`;

  fetch(`${BASE_URL}/cliente/api/sesiones/cerrar-todas`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.status === "ok") {
        document
          .querySelectorAll(".sesion-item:not(.sesion-item--actual)")
          .forEach((el) => el.remove());
        document.querySelector(".sesiones-footer")?.remove();
        Toast.mostrar(Preferencias.t("seg.todas.ok"), "success");
      } else {
        btnEl.disabled = false;
        btnEl.innerHTML = `<i class="bi bi-shield-x"></i> ${Preferencias.t("seg.cerrar_todas")}`;
        Toast.mostrar(Preferencias.t("seg.todas.error"), "error");
      }
    })
    .catch(() => {
      btnEl.disabled = false;
      btnEl.innerHTML = `<i class="bi bi-shield-x"></i> ${Preferencias.t("seg.cerrar_todas")}`;
      Toast.mostrar(Preferencias.t("toast.conexion"), "error");
    });
}

/* ================================================================
   FOTO DE PERFIL
================================================================ */
function previewFoto(event) {
  const file = event.target.files[0];
  if (!file) return;

  if (file.size > 2 * 1024 * 1024) {
    Toast.mostrar(Preferencias.t("toast.foto.grande"), "error");
    event.target.value = "";
    return;
  }

  const validTypes = ["image/jpeg", "image/png", "image/gif"];
  if (!validTypes.includes(file.type)) {
    Toast.mostrar(Preferencias.t("toast.foto.tipo"), "error");
    event.target.value = "";
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    /* Puede haber sido reemplazada por canvas. Buscamos tanto img como canvas */
    const avatar = document.querySelector(
      ".avatar-grande img, .avatar-grande canvas",
    );
    if (!avatar) return;

    if (avatar.tagName === "CANVAS") {
      /* Reemplazar el canvas con la img real */
      const img = document.createElement("img");
      img.src = e.target.result;
      img.alt = avatar.getAttribute("aria-label") || "Foto de perfil";
      img.className = avatar.className;
      avatar.replaceWith(img);
    } else {
      avatar.src = e.target.result;
    }
  };
  reader.readAsDataURL(file);
  Toast.mostrar(Preferencias.t("toast.foto.ok"), "success");
}

function eliminarFoto() {
  if (!confirm("¿Eliminar tu foto de perfil?")) return;

  const avatar = document.querySelector(
    ".avatar-grande img, .avatar-grande canvas",
  );
  const input = document.getElementById("inputFoto");

  if (avatar) {
    /* Forzar el avatar de iniciales */
    if (avatar.tagName === "IMG") {
      /* Disparar onerror manualmente: poner src roto */
      avatar.src = "__removed__";
    }
    /* Si ya era canvas, no hay nada que hacer */
  }

  if (input) input.value = "";
  Toast.mostrar(Preferencias.t("toast.foto.eliminar"), "info");
}

/* ================================================================
   CONTRASEÑA — las funciones de validación usan los IDs de confi.php
   (inp-pass-nueva, inp-pass-conf, strengthFill, [data-req=...]).
   confi.js implementa: evaluarPassword(), validarCampo(), togglePass().
================================================================ */

/* ================================================================
   TABS
================================================================ */
function cambiarTab(tab) {
  document
    .querySelectorAll(".tab-content")
    .forEach((el) => el.classList.add("tab-content--hidden"));
  document.querySelectorAll(".tab-config").forEach((btn) => {
    btn.classList.remove("active");
    btn.setAttribute("aria-selected", "false");
  });

  document
    .getElementById(`tab-${tab}`)
    ?.classList.remove("tab-content--hidden");

  const btn = document.querySelector(`[aria-controls="tab-${tab}"]`);
  if (btn) {
    btn.classList.add("active");
    btn.setAttribute("aria-selected", "true");
  }
}

/* toggleFAQ está definido en confi.js (con soporte para aria-expanded) */

/* ================================================================
   VALIDACIÓN DE TELÉFONO / DOCUMENTO / FORMULARIO PRINCIPAL
================================================================ */
document.addEventListener("DOMContentLoaded", () => {
  /* Validación de teléfono */
  document.querySelectorAll('input[type="tel"]').forEach((input) => {
    input.addEventListener("input", () => {
      input.value = input.value.replace(/[^0-9\s+\-]/g, "");
    });
  });

  /* Validación de documento */
  const docInput = document.querySelector('input[name="numero_documento"]');
  if (docInput) {
    docInput.addEventListener("input", () => {
      docInput.value = docInput.value.replace(/\D/g, "");
    });
  }

  /* Botón submit del formulario principal */
  const mainForm = document.querySelector('form[action*="actualizar"]');
  if (mainForm) {
    mainForm.addEventListener("submit", () => {
      const btn = mainForm.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<i class="bi bi-hourglass-split"></i> ${Preferencias.t("general.guardando")}`;
      }
    });
  }

  /* Inicializar el módulo de preferencias */
  Preferencias.init();
});
