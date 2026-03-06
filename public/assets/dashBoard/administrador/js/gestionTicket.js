class GestionTicket {
  constructor() {
    this.init();
  }

  init() {
    this.listarUsuarios();
    this.cacheDom();
    this.bindEvents();
    this.configurarEstadoFormulario();
    console.log("GestionTicket Inicializado");
  }

  cacheDom() {
    // Formulario de asignación
    this.selectUsuario = document.getElementById("usuario_asignado");
    this.ticketId = document.getElementById("id_ticket");
    this.formAsignarTicket = document.getElementById("asignarTicketForm");

    // Formulario de actualización
    this.formActualizarEstado = document.getElementById("actualizarEstadoForm");
    this.selectEstado = document.getElementById("estado_ticket");
    this.selectReasignar = document.getElementById("reasignar_ticket");
    this.textareaSolucion = document.getElementById("solucion_ticket");
    this.btnActualizarEstado = document.getElementById("btn-actualizar-estado");

    // Variables de estado
    this.estadoActual = document.getElementById("estado_actual")?.value;
    this.puedeEditar = document.getElementById("puede_editar")?.value === "1";
    this.ticketCerrado =
      document.getElementById("ticket_cerrado")?.value === "1";
    this.solucionRequired = document.getElementById("solucion_required");
  }

  bindEvents() {
    // Evento para asignar ticket
    if (this.formAsignarTicket) {
      this.formAsignarTicket.addEventListener("submit", (event) => {
        event.preventDefault();
        this.asignarTicket();
      });
    }

    // Evento para actualizar estado
    if (this.formActualizarEstado) {
      this.formActualizarEstado.addEventListener("submit", (event) => {
        event.preventDefault();
        this.actualizarEstadoTicket();
      });
    }

    // Evento para cambio de estado - controla reasignación y solución obligatoria
    if (this.selectEstado) {
      this.selectEstado.addEventListener("change", () => {
        this.manejarCambioEstado();
      });
    }
  }

  configurarEstadoFormulario() {
    // Si el ticket está cerrado o el usuario no puede editar, deshabilitar todo
    if (this.ticketCerrado || !this.puedeEditar) {
      this.deshabilitarFormulario();
    }

    // Configurar estado inicial
    this.manejarCambioEstado();
  }

  manejarCambioEstado() {
    if (!this.selectEstado) return;

    const estadoSeleccionado = this.selectEstado.value;

    // Lógica de reasignación
    if (estadoSeleccionado === "en_espera") {
      // No se puede reasignar en estado "en_espera"
      if (this.selectReasignar) {
        this.selectReasignar.disabled = true;
        this.selectReasignar.value = "";
      }
    } else if (estadoSeleccionado === "cerrado") {
      // No se puede reasignar si está cerrado
      if (this.selectReasignar) {
        this.selectReasignar.disabled = true;
        this.selectReasignar.value = "";
      }
    } else {
      // Permitir reasignación en otros estados (si puede editar)
      if (this.selectReasignar && this.puedeEditar) {
        this.selectReasignar.disabled = false;
      }
    }

    // Lógica de solución obligatoria
    if (
      estadoSeleccionado === "en_espera" ||
      estadoSeleccionado === "cerrado"
    ) {
      // Solución es obligatoria
      if (this.textareaSolucion) {
        this.textareaSolucion.required = true;
        this.textareaSolucion.classList.add("required-field");
      }
      if (this.solucionRequired) {
        this.solucionRequired.style.display = "inline";
      }
    } else {
      // Solución no es obligatoria
      if (this.textareaSolucion) {
        this.textareaSolucion.required = false;
        this.textareaSolucion.classList.remove("required-field");
      }
      if (this.solucionRequired) {
        this.solucionRequired.style.display = "none";
      }
    }
  }

  deshabilitarFormulario() {
    // Deshabilitar todos los campos del formulario de actualización
    if (this.selectEstado) this.selectEstado.disabled = true;
    if (this.selectReasignar) this.selectReasignar.disabled = true;
    if (this.textareaSolucion) this.textareaSolucion.disabled = true;
    if (this.btnActualizarEstado) this.btnActualizarEstado.disabled = true;
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
    // Renderizar en el select de asignación inicial
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

    // Renderizar en el select de reasignación
    if (this.selectReasignar) {
      this.selectReasignar.innerHTML =
        '<option value="" disabled selected>Seleccione un usuario para reasignar</option>';
      usuarios.forEach((usuario) => {
        const option = document.createElement("option");
        option.value = usuario.id_usuario;
        option.textContent = `${usuario.nombres} ${usuario.apellidos}`;
        this.selectReasignar.appendChild(option);
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
          }).then(() => {
            window.location.reload();
          });
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

  actualizarEstadoTicket() {
    const estadoSeleccionado = this.selectEstado.value;
    const solucion = this.textareaSolucion.value.trim();

    // Validar solución obligatoria
    if (
      (estadoSeleccionado === "en_espera" ||
        estadoSeleccionado === "cerrado") &&
      !solucion
    ) {
      this.mostrarMensajeError(
        "Campo Requerido",
        "Debe proporcionar una solución para cambiar el estado a 'En Espera' o 'Cerrado'.",
      );
      this.textareaSolucion.focus();
      this.textareaSolucion.classList.add("error");
      return;
    }

    const ticketId = this.ticketId.value;
    const reasignarA = this.selectReasignar.value;

    const datos = {
      id_ticket: ticketId,
      estado: estadoSeleccionado,
      solucion: solucion,
    };

    // Solo incluir reasignación si se seleccionó un usuario
    if (reasignarA) {
      datos.id_usuario_reasignado = reasignarA;
    }

    console.log(datos);

    fetch("/vetwilling/soporte/api/ticket", {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(datos),
    })
      .then((response) => response.json())
      .then((data) => {
        console.log("Respuesta del servidor:", data);
        if (data.status === "success") {
          Swal.fire({
            icon: "success",
            title: "Actualización Exitosa",
            text: "El ticket ha sido actualizado exitosamente.",
          }).then(() => {
            window.location.reload();
          });
        } else {
          this.mostrarMensajeError(
            "Error",
            "Error al actualizar el ticket: " + data.message,
          );
        }
      })
      .catch((error) => {
        console.error("Error al actualizar el ticket:", error);
        this.mostrarMensajeError(
          "Error",
          "Error al actualizar el ticket. Por favor, inténtalo de nuevo.",
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
