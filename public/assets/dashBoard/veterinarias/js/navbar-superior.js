/* ======================================================
   NAVBAR SUPERIOR - JS GENERAL
   ====================================================== */

document.addEventListener("DOMContentLoaded", () => {
  iniciarReloj();
  iniciarSaludo();
  inicializarEventosGlobales();
});

const baseUrl = window.BASE_URL || (() => {
  const appBase = window.location.pathname.split('/').filter(Boolean)[0] || '';
  return `${window.location.origin}${appBase ? '/' + appBase : ''}`;
})();

/* ======================================================
   RELOJ Y SALUDO
   ====================================================== */

function iniciarReloj() {
  const horaActual = document.getElementById("horaActual");

  setInterval(() => {
    const ahora = new Date();
    horaActual.textContent = ahora.toLocaleTimeString("es-CO");
  }, 1000);
}

function iniciarSaludo() {
  const saludoTexto = document.getElementById("saludoTexto");
  const saludoEmoji = document.getElementById("saludoEmoji");

  const hora = new Date().getHours();

  if (hora < 12) {
    saludoTexto.textContent = "Buenos días";
    saludoEmoji.textContent = "🌅";
  } else if (hora < 18) {
    saludoTexto.textContent = "Buenas tardes";
    saludoEmoji.textContent = "☀️";
  } else {
    saludoTexto.textContent = "Buenas noches";
    saludoEmoji.textContent = "🌙";
  }
}

/* ======================================================
   MENÚ PERFIL
   ====================================================== */

function togglePerfilMenu() {
  const menu = document.getElementById("perfilDropdown");
  cerrarOtrosDropdowns(menu);
  menu.style.display = menu.style.display === "block" ? "none" : "block";
}

/* ======================================================
   NOTIFICACIONES
   ====================================================== */

function toggleNotificaciones() {
  const panel = document.getElementById("notificationsPanel");
  cerrarOtrosDropdowns(panel);
  panel.style.display = panel.style.display === "block" ? "none" : "block";
}

function eliminarNotificacion(btn) {
  btn.closest(".notification-item").remove();
}

function marcarTodasLeidas() {
  document
    .querySelectorAll(".notification-item.unread")
    .forEach((item) => item.classList.remove("unread"));
}

/* ======================================================
   MODAL SOPORTE
   ====================================================== */

const btnAbrirSoporte = document.getElementById("btnAbrirSoporte");
const modalSoporte = document.getElementById("modalSoporte");
const btnCerrarModal = document.getElementById("btnCerrarModal");
const btnCancelar = document.getElementById("btnCancelar");

if (btnAbrirSoporte) {
  btnAbrirSoporte.addEventListener("click", (e) => {
    e.preventDefault();
    cerrarTodos();
    modalSoporte.classList.add("active");
  });
}

if (btnCerrarModal) {
  btnCerrarModal.addEventListener("click", cerrarModalSoporte);
}

if (btnCancelar) {
  btnCancelar.addEventListener("click", cerrarModalSoporte);
}

function cerrarModalSoporte() {
  modalSoporte.classList.remove("active");
}

/* ======================================================
   FORMULARIO SOPORTE
   ====================================================== */

const formularioSoporte = document.getElementById("formularioSoporte");

if (formularioSoporte) {
  formularioSoporte.addEventListener("submit", (e) => {
    e.preventDefault();

    const descripcion = document.getElementById("descripcionProblema").value;

    if (descripcion.length > 250) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "La descripción no debe exceder los 250 caracteres.",
      });
      return;
    }

    // Validamos que el usuario haya seleccionado una categoría
    const tipoProblema = document.getElementById("tipoProblema").value;
    const asunto = document.getElementById("asunto").value.trim();
    if (!tipoProblema || !asunto) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Por favor, Ingrese los campos obligatorios",
      });
      return;
    }

    // Enviamos el formulario (aquí iría la lógica real de envío, como una petición AJAX)
    const formData = new FormData(formularioSoporte);

    fetch(`${baseUrl}/soporte/api/crear-ticket`, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "error") {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: data.message,
          });
        } else {
          Swal.fire({
            icon: "success",
            title: "Éxito",
            text: `${data.message}`,
          });
          formularioSoporte.reset();
          cerrarModalSoporte();
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Ocurrió un error al enviar la solicitud.",
        });
      });
  });
}

/* ======================================================
   TEMA OSCURO / CLARO
   ====================================================== */

function toggleTheme() {
  document.body.classList.toggle("dark-theme");
}

/* ======================================================
   SIDEBAR MÓVIL
   ====================================================== */

function abrirSidebarMovil() {
  document.body.classList.toggle("sidebar-open");
}

/* ======================================================
   UTILIDADES
   ====================================================== */

function cerrarOtrosDropdowns(excepto) {
  document.querySelectorAll(".dropdown-panel").forEach((panel) => {
    if (panel !== excepto) {
      panel.style.display = "none";
    }
  });
}

function cerrarTodos() {
  document.querySelectorAll(".dropdown-panel").forEach((panel) => {
    panel.style.display = "none";
  });
}

/* ======================================================
   CIERRE AL HACER CLICK FUERA
   ====================================================== */

function inicializarEventosGlobales() {
  document.addEventListener("click", (e) => {
    if (
      !e.target.closest(".navbar-action") &&
      !e.target.closest(".dropdown-panel") &&
      !e.target.closest(".modal-container")
    ) {
      cerrarTodos();
    }
  });
}
const btnProfile = document.querySelector(".btn-profile");

btnProfile.addEventListener("click", () => {
  // btnProfile.classList.toggle('active');
});
