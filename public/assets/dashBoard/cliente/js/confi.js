/* ================================================================
   TABS — roving tabindex + navegación por teclado (WAI-ARIA)
   Inicializado en DOMContentLoaded para garantizar que el DOM esté
   completamente disponible antes de consultar los elementos.
================================================================ */
document.addEventListener("DOMContentLoaded", function () {
  var tabBtns = Array.from(document.querySelectorAll(".tab-config"));
  var tabPanels = Array.from(document.querySelectorAll(".tab-content"));

  if (!tabBtns.length) return; // esta vista no tiene tabs personalizados

  function activarTab(btn) {
    tabBtns.forEach(function (b) {
      b.classList.remove("active");
      b.setAttribute("aria-selected", "false");
      b.setAttribute("tabindex", "-1");
    });
    tabPanels.forEach(function (p) {
      p.classList.add("tab-content--hidden");
    });
    btn.classList.add("active");
    btn.setAttribute("aria-selected", "true");
    btn.setAttribute("tabindex", "0");
    var panel = document.getElementById("tab-" + btn.dataset.tab);
    if (panel) panel.classList.remove("tab-content--hidden");
  }

  tabBtns.forEach(function (btn, idx) {
    btn.addEventListener("click", function () {
      activarTab(btn);
      btn.focus();
    });

    btn.addEventListener("keydown", function (e) {
      var next;
      if (e.key === "ArrowRight" || e.key === "ArrowDown") {
        next = tabBtns[(idx + 1) % tabBtns.length];
      } else if (e.key === "ArrowLeft" || e.key === "ArrowUp") {
        next = tabBtns[(idx - 1 + tabBtns.length) % tabBtns.length];
      } else if (e.key === "Home") {
        next = tabBtns[0];
      } else if (e.key === "End") {
        next = tabBtns[tabBtns.length - 1];
      }
      if (next) {
        e.preventDefault();
        activarTab(next);
        next.focus();
      }
    });
  });
});

/* ================================================================
   FAQ — toggle .active (alineado con CSS)
   CORRECCIÓN #5
================================================================ */
function toggleFAQ(btn) {
  var expanded = btn.getAttribute("aria-expanded") === "true";
  btn.setAttribute("aria-expanded", String(!expanded));
  var item = btn.closest(".faq-item");
  if (item) item.classList.toggle("active");

  /* Rotar ícono chevron */
  var icon = btn.querySelector("i.bi-chevron-down, i.bi-chevron-up");
  if (icon) {
    icon.style.transform = !expanded ? "rotate(180deg)" : "";
  }
}

/* ================================================================
   MOSTRAR / OCULTAR CONTRASEÑA
================================================================ */
function togglePass(inputId, btn) {
  var input = document.getElementById(inputId);
  var icon = btn.querySelector("i");
  if (!input) return;
  if (input.type === "password") {
    input.type = "text";
    if (icon) {
      icon.classList.remove("bi-eye");
      icon.classList.add("bi-eye-slash");
    }
    btn.setAttribute(
      "aria-label",
      btn.getAttribute("aria-label").replace("Mostrar", "Ocultar"),
    );
  } else {
    input.type = "password";
    if (icon) {
      icon.classList.remove("bi-eye-slash");
      icon.classList.add("bi-eye");
    }
    btn.setAttribute(
      "aria-label",
      btn.getAttribute("aria-label").replace("Ocultar", "Mostrar"),
    );
  }
}

/* ================================================================
   MEDIDOR DE FORTALEZA DE CONTRASEÑA
================================================================ */
function evaluarPassword(val) {
  var checks = {
    length: val.length >= 8,
    upper: /[A-Z]/.test(val),
    number: /[0-9]/.test(val),
    special: /[^A-Za-z0-9]/.test(val),
  };
  var score = Object.values(checks).filter(Boolean).length;

  /* Actualizar indicadores de requisitos */
  Object.keys(checks).forEach(function (key) {
    var el = document.querySelector('[data-req="' + key + '"]');
    if (!el) return;
    var icon = el.querySelector("i");
    if (checks[key]) {
      el.style.color = "var(--success)";
      if (icon) {
        icon.className = "bi bi-check-circle";
      }
    } else {
      el.style.color = "";
      if (icon) {
        icon.className = "bi bi-x-circle";
      }
    }
  });

  /* Barra de fortaleza */
  var fill = document.getElementById("strengthFill");
  var text = document.getElementById("strengthText");
  var niveles = [
    { label: "", color: "", width: "0%" },
    { label: "Muy débil", color: "var(--error)", width: "25%" },
    { label: "Débil", color: "var(--warning)", width: "50%" },
    { label: "Buena", color: "#3b82f6", width: "75%" },
    { label: "Fuerte", color: "var(--success)", width: "100%" },
  ];
  var nivel = niveles[score];
  if (fill) {
    fill.style.width = nivel.width;
    fill.style.background = nivel.color;
  }
  if (text) {
    text.textContent = nivel.label;
    text.style.color = nivel.color;
  }
}

/* ================================================================
   VALIDACIÓN EN TIEMPO REAL
   CORRECCIÓN #7 — mensajes inline para campos required
================================================================ */
document
  .querySelectorAll(
    ".form-validable input, .form-validable select, .form-validable textarea",
  )
  .forEach(function (field) {
    ["blur", "input"].forEach(function (evt) {
      field.addEventListener(evt, function () {
        validarCampo(field);
      });
    });
  });

function validarCampo(field) {
  var errorId = field.getAttribute("aria-describedby");
  var errorEl = errorId ? document.getElementById(errorId) : null;
  var msg = "";

  if (field.validity.valueMissing) {
    msg = "Este campo es obligatorio.";
  } else if (field.validity.typeMismatch && field.type === "email") {
    msg = "Ingresa un correo electrónico válido.";
  } else if (field.validity.tooShort) {
    msg = "Mínimo " + field.minLength + " caracteres.";
  } else if (field.id === "inp-pass-conf") {
    var nueva = document.getElementById("inp-pass-nueva");
    if (nueva && field.value && field.value !== nueva.value) {
      msg = "Las contraseñas no coinciden.";
    }
  }

  if (errorEl) errorEl.textContent = msg;
  field.classList.toggle("field-invalid", msg !== "");
  field.classList.toggle("field-valid", msg === "" && field.value !== "");
}

/* Validar confirmación al cambiar la nueva contraseña también */
var passNueva = document.getElementById("inp-pass-nueva");
var passConf = document.getElementById("inp-pass-conf");
if (passNueva && passConf) {
  passNueva.addEventListener("input", function () {
    if (passConf.value) validarCampo(passConf);
  });
}

/* ================================================================
   NOTIFICACIONES + HISTORIAL
================================================================ */
document.addEventListener("DOMContentLoaded", function () {
  /* Guardar preferencia de notificación */
  var btnGuardar = document.getElementById("btnGuardarPreferenciaNotificacion");
  if (btnGuardar) {
    btnGuardar.addEventListener("click", function () {
      var sel = document.querySelector(
        'input[name="preferencia_notificacion"]:checked',
      );
      if (!sel) {
        var msg =
          typeof Preferencias !== "undefined" && Preferencias.t
            ? Preferencias.t("notif.selecciona")
            : "Selecciona una preferencia.";
        if (typeof Toast !== "undefined") Toast.mostrar(msg, "warning");
        return;
      }
      btnGuardar.disabled = true;
      var html = btnGuardar.innerHTML;
      var guardando =
        typeof Preferencias !== "undefined" && Preferencias.t
          ? Preferencias.t("general.guardando")
          : "Guardando…";
      btnGuardar.innerHTML =
        '<i class="bi bi-hourglass-split"></i> ' + guardando;

      Preferencias.guardar({ notificaciones: sel.value }).then(function (ok) {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = html;
        if (ok && typeof Toast !== "undefined") {
          Toast.mostrar(Preferencias.t("notif.ok"), "success");
        }
      });
    });
  }

  /* Cargar historial de notificaciones */
  (function cargarHistorial() {
    var loading = document.getElementById("loadingHistorial");
    var sinH = document.getElementById("sinHistorial");
    var content = document.getElementById("historialContent");
    var tbody = document.getElementById("tablaHistorialNotificaciones");

    if (loading) loading.style.display = "block";
    if (sinH) sinH.style.display = "none";
    if (content) content.style.display = "none";

    fetch(
      BASE_URL +
        "/app/controllers/preferenciasNotificacionController.php?accion=historial&limite=10",
    )
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (loading) loading.style.display = "none";
        if (
          data.status !== "success" ||
          !Array.isArray(data.historial) ||
          !data.historial.length
        ) {
          if (sinH) sinH.style.display = "block";
          return;
        }
        if (tbody) tbody.innerHTML = "";
        data.historial.forEach(function (n) {
          var fecha = n.fecha_envio
            ? new Date(n.fecha_envio).toLocaleString("es-CO")
            : "N/A";
          var estado = n.estado_envio === "exitoso" ? "Entregado" : "Fallido";
          var tr = document.createElement("tr");
          tr.innerHTML =
            "<td>" +
            fecha +
            "</td>" +
            "<td>" +
            (n.medio_notificacion || "email") +
            "</td>" +
            "<td>" +
            (n.nombre_mascota || "N/A") +
            "</td>" +
            "<td>" +
            estado +
            "</td>";
          if (tbody) tbody.appendChild(tr);
        });
        if (content) content.style.display = "block";
      })
      .catch(function () {
        if (loading) loading.style.display = "none";
        if (sinH) {
          sinH.style.display = "block";
          sinH.textContent =
            typeof Preferencias !== "undefined" && Preferencias.t
              ? Preferencias.t("notif.historial.error")
              : "Error al cargar el historial.";
        }
      });
  })();

  /* Cerrar sesión individual */
  document.querySelectorAll("[data-cerrar-sesion]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var id = btn.dataset.cerrarSesion;
      if (!id) return;
      btn.disabled = true;
      btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Cerrando…';
      fetch(BASE_URL + "/cliente/cerrar-sesion", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_sesion: id }),
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          if (data.status === "success") {
            var item = btn.closest(".sesion-item");
            if (item) {
              item.style.opacity = "0";
              item.style.transition = "opacity 0.3s";
              setTimeout(function () {
                item.remove();
              }, 300);
            }
            if (typeof Toast !== "undefined")
              Toast.mostrar("Sesión cerrada.", "success");
          } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-right"></i> Cerrar';
            if (typeof Toast !== "undefined")
              Toast.mostrar("No se pudo cerrar la sesión.", "error");
          }
        })
        .catch(function () {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-box-arrow-right"></i> Cerrar';
          if (typeof Toast !== "undefined")
            Toast.mostrar("Error de conexión.", "error");
        });
    });
  });

  /* Cerrar todas las sesiones */
  var btnTodasSesiones = document.getElementById("btnCerrarTodasSesiones");
  if (btnTodasSesiones) {
    btnTodasSesiones.addEventListener("click", function () {
      if (!confirm("¿Cerrar todas las demás sesiones?")) return;
      btnTodasSesiones.disabled = true;
      btnTodasSesiones.innerHTML =
        '<i class="bi bi-hourglass-split"></i> Cerrando…';
      fetch(BASE_URL + "/cliente/cerrar-todas-sesiones", { method: "POST" })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          if (data.status === "success") {
            document
              .querySelectorAll(".sesion-item:not(.sesion-item--actual)")
              .forEach(function (el) {
                el.style.opacity = "0";
                el.style.transition = "opacity 0.3s";
                setTimeout(function () {
                  el.remove();
                }, 300);
              });
            setTimeout(function () {
              var footer = document.querySelector(".sesiones-footer");
              if (footer) footer.remove();
            }, 350);
            if (typeof Toast !== "undefined")
              Toast.mostrar("Todas las sesiones cerradas.", "success");
          } else {
            btnTodasSesiones.disabled = false;
            btnTodasSesiones.innerHTML =
              '<i class="bi bi-box-arrow-right"></i> Cerrar todas las otras sesiones';
            if (typeof Toast !== "undefined")
              Toast.mostrar("No se pudieron cerrar las sesiones.", "error");
          }
        })
        .catch(function () {
          btnTodasSesiones.disabled = false;
          btnTodasSesiones.innerHTML =
            '<i class="bi bi-box-arrow-right"></i> Cerrar todas las otras sesiones';
          if (typeof Toast !== "undefined")
            Toast.mostrar("Error de conexión.", "error");
        });
    });
  }
});
