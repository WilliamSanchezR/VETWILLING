class GestionTicket {
  constructor() {
    this.init();
  }

  init() {
    this.listarUsuarios();
    this.cacheDom();
    console.log("GestionTicket Inicializado");
  }

  cacheDom() {
    // Aquí puedes almacenar referencias a elementos del DOM si es necesario
    this.selectUsuario = document.getElementById("usuario_asignado");
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
        option.value = usuario.id;
        option.textContent = `${usuario.nombres} ${usuario.apellidos}`;
        this.selectUsuario.appendChild(option);
      });
    }
  }
}

new GestionTicket();
