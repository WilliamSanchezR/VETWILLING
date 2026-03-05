class DashboardAdmin {
  constructor() {
    this.init();
  }

  init() {
    console.log("DashboardAdmin Inicializado");
    // Aquí puedes agregar cualquier inicialización adicional que necesites
    this.cacheDom();
    this.bindEvents();
    this.dataTableListUsuarios();
    this.dataTableListVeterinarias();
  }

  cacheDom() {
    // Aquí puedes almacenar referencias a elementos del DOM que necesites manipular
    this.tablaUsuarios;
    this.tablaVeterinarias;
    this.tablaVeterinariasAdmin = document.querySelector("#tablaListaVeterinarias");
    this.tablaUsuariosAdmin = document.querySelector("#tbl_user_admin");
    this.tabsListas = document.querySelectorAll(".tab-btn");
    this.contenedorListas = document.querySelector(".contenido-tab");
    this.tabTablaUsuarios = document.querySelector("#tab-usuarios");
    this.tabTablaVeterinarias = document.querySelector("#tab-veterinarias");
  }

  bindEvents() {
    // Aquí puedes agregar cualquier evento que necesites
    if (this.tabsListas) {
      this.tabsListas.forEach((tab) => {
        tab.addEventListener("click", (e) => {
          const target = e.currentTarget;
          this.tabsListas.forEach((t) => t.classList.remove("active"));
          target.classList.add("active");
          // Aquí puedes agregar lógica para mostrar/ocultar contenido según la pestaña seleccionada
          if (this.contenedorListas) {
            const tabName = target.textContent.trim().toLowerCase();
            if (tabName === "usuarios") {
              this.tabTablaUsuarios.style.display = "block";
              this.tabTablaVeterinarias.style.display = "none";
            } else if (tabName === "veterinarias") {
              this.tabTablaUsuarios.style.display = "none";
              this.tabTablaVeterinarias.style.display = "block";
            }
          }
        });
      });
    }
  }

  // Funcion para inicializar DataTable para la lista de laboratorios asociados
  dataTableListUsuarios() {
    try {
      this.tablaUsuarios = $(this.tablaUsuariosAdmin).DataTable({
        // Configuración de idioma en español
        language: {
          decimal: "",
          emptyTable: "No hay Usuarios disponibles",
          info: "Mostrando _START_ a _END_ de _TOTAL_ Usuarios",
          infoEmpty: "Mostrando 0 a 0 de 0 Usuarios",
          infoFiltered: "(filtrado de _MAX_ Usuarios totales)",
          infoPostFix: "",
          thousands: ",",
          lengthMenu: "Mostrar _MENU_ Usuarios",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron Usuarios",
          paginate: {
            first: "Primera",
            last: "Última",
            next: "Siguiente",
            previous: "Anterior",
          },
        },

        // Configuración de paginación
        pageLength: 9,
        lengthMenu: [
          [9, 15, 25, 50, -1],
          [9, 15, 25, 50, "Todas"],
        ],

        // Configuración de ordenamiento
        order: [[1, "desc"]], // Ordenar por fecha por defecto

        // Configuración de columnas
        columnDefs: [
          {
            targets: -1, // Última columna (Operación)
            orderable: false,
            searchable: false,
          },
        ],

        // DOM personalizado
        dom:
          '<"row"<"col-sm-12"tr>>' +
          '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      });

      console.log("✅ Tabla inicializada exitosamente");
    } catch (error) {
      console.error("❌ Error al inicializar DataTables:", error);
      alert(
        "Error al inicializar la tabla. Revisa la consola para más detalles.",
      );
      return;
    }
  }

  dataTableListVeterinarias() {
    try {
      this.tablaVeterinarias = $(this.tablaVeterinariasAdmin).DataTable({
        // Configuración de idioma en español
        language: {
          decimal: "",
          emptyTable: "No hay Veterinarias disponibles",
          info: "Mostrando _START_ a _END_ de _TOTAL_ Veterinarias",
          infoEmpty: "Mostrando 0 a 0 de 0 Veterinarias",
          infoFiltered: "(filtrado de _MAX_ Veterinarias totales)",
          infoPostFix: "",
          thousands: ",",
          lengthMenu: "Mostrar _MENU_ Veterinarias",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron Veterinarias",
          paginate: {
            first: "Primera",
            last: "Última",
            next: "Siguiente",
            previous: "Anterior",
          },
        },

        // Configuración de paginación
        pageLength: 9,
        lengthMenu: [
          [9, 15, 25, 50, -1],
          [9, 15, 25, 50, "Todas"],
        ],

        // Configuración de ordenamiento
        order: [[1, "desc"]], // Ordenar por fecha por defecto

        // Configuración de columnas
        columnDefs: [
          {
            targets: -1, // Última columna (Operación)
            orderable: false,
            searchable: false,
          },
        ],

        // DOM personalizado
        dom:
          '<"row"<"col-sm-12"tr>>' +
          '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      });

      console.log("✅ Tabla inicializada exitosamente");
    } catch (error) {
      console.error("❌ Error al inicializar DataTables:", error);
      alert(
        "Error al inicializar la tabla. Revisa la consola para más detalles.",
      );
      return;
    }
  }

  
}

document.addEventListener("DOMContentLoaded", () => {
  const dashboardAdmin = new DashboardAdmin();
});
