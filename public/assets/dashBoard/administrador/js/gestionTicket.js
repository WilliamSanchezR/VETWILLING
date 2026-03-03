class GestionTicket {
  constructor() {
    this.init();
  }

  init() {
    this.listarUsuarios();
    this.cacheDom();
    this.bindEvents();
    console.log("GestionTicket Inicializado");
  }

  cacheDom() {
    // Aquí puedes almacenar referencias a elementos del DOM si es necesario
    this.selectUsuario = document.getElementById("usuario_asignado");
    this.ticketId = document.getElementById("id_ticket");
    this.formAsignarTicket = document.getElementById("asignarTicketForm");
  }

  bindEvents() {
    if (this.formAsignarTicket) {
      this.formAsignarTicket.addEventListener("submit", (event) => {
        event.preventDefault();
        this.asignarTicket();
      });
    }
  }

  listarUsuarios() {
    fetch("/vetwilling/admin/lista-profesionales?accion=listarAdmin")
      .then((response) => response.json())
      .then((data) => {
        this.renderListaUsuarios(data);
      })
      .catch((error) => {
        console.error("Error al obtener usuarios:", error);
      });
  }

  renderListaUsuarios(usuarios) {
    if (this.selectUsuario) {
      this.selectUsuario.innerHTML =
        '<option value="" disabled selected>Seleccione un usuario</option>';
      usuarios.forEach((usuario) => {
        const option = document.createElement("option");
        option.value = usuario.id_usuario;
        option.textContent = `${usuario.nombres} ${usuario.apellidos}`;
        this.selectUsuario.appendChild(option);
      });
    }
  }

  asignarTicket() {
    const ticketId = this.ticketId.value;
    const usuarioId = this.selectUsuario.value;

    fetch("/vetwilling/soporte/api/ticket?action=asignar", {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        id_ticket: ticketId,
        id_usuario: usuarioId,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        console.log("Respuesta del servidor:", data);
        if (data.status === "success") {
          Swal.fire({
            icon: "success",
            title: "Asignación Exitosa",
            text: "El ticket ha sido asignado exitosamente.",
          });
          // Recargar la página después de cerrar el mensaje
          setTimeout(() => {
            window.location.reload();
          }, 1500);
        } else {
          this.mostrarMensajeError(
            "Error",
            "Error al asignar el ticket: " + data.message,
          );
        }
      })
      .catch((error) => {
        console.error("Error al asignar el ticket:", error);
        this.mostrarMensajeError(
          "Error",
          "Error al asignar el ticket. Por favor, inténtalo de nuevo.",
        );
      });
  }

  mostrarMensajeError(title, mensaje) {
    Swal.fire({
      icon: "error",
      title: title,
      text: mensaje,
    });
  }
}

new GestionTicket();
