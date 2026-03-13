class ListaTickets {
  constructor() {
    document.addEventListener("DOMContentLoaded", () => this.init());
  }

  init() {
    console.log("Lista Agenda Disponible Init");
    this.cacheDom();
    this.bindEvents();
    this.dataTableListTickets();
  }

  cacheDom() {
    this.gridTickets;
    this.tablaTickets = $("#tablaListaTickets");
    this.ordebarBtn = document.getElementById("btnOrdenar");
    this.btnExportarCSV = document.getElementById("btnExport");
    this.inputBuscarTickets = document.getElementById("buscarTickets");
    this.limpiarBusqueda = document.querySelector(".campo-buscar i");
  }

  bindEvents() {
    if (this.ordebarBtn) {
      this.ordebarBtn.onclick = () => this.ordenarTickets();
    }
    if (this.btnExportarCSV) {
      this.btnExportarCSV.onclick = () => this.exportarCSV();
    }

    if (this.inputBuscarTickets) {
      this.inputBuscarTickets.oninput = () => this.buscarTickets();
    }

    if (this.limpiarBusqueda) {
      this.limpiarBusqueda.onclick = () => {
        this.inputBuscarTickets.value = "";
        this.buscarTickets();
      };
    }
  }

  // Funcion para inicializar DataTable para la lista de laboratorios asociados
  dataTableListTickets() {
    try {
      this.gridTickets = this.tablaTickets.DataTable({
        // Configuración de idioma en español
        language: {
          decimal: "",
          emptyTable: "No hay Tickets disponibles",
          info: "Mostrando _START_ a _END_ de _TOTAL_ Tickets",
          infoEmpty: "Mostrando 0 a 0 de 0 Tickets",
          infoFiltered: "(filtrado de _MAX_ Tickets totales)",
          infoPostFix: "",
          thousands: ",",
          lengthMenu: "Mostrar _MENU_ Tickets",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron Tickets",
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
        order: [[2, "desc"]], // Ordenar por fecha por defecto

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

  // Función para ordenar la lista de tickets
  ordenarTickets() {
    const opciones = [
      "Titulo (A-Z)",
      "Titulo (Z-A)",
      "Categoría (A-Z)",
      "Categoría (Z-A)",
      "Asignado a (A-Z)",
      "Asignado a (Z-A)",
    ];

    const mensaje =
      "⬆️⬇️ Selecciona el ordenamiento:\n\n" +
      opciones.map((o, i) => `${i + 1} - ${o}`).join("\n");

    const opcion = prompt(mensaje);

    switch (opcion) {
      case "1":
        this.gridTickets.order([1, "asc"]).draw();
        console.log("Ordenado por Titulo ascendente");
        break;
      case "2":
        this.gridTickets.order([1, "desc"]).draw();
        console.log("Ordenado por Titulo descendente");
        break;
      case "3":
        this.gridTickets.order([2, "asc"]).draw();
        console.log("Ordenado por Categoría ascendente");
        break;
      case "4":
        this.gridTickets.order([2, "desc"]).draw();
        console.log("Ordenado por Categoría descendente");
        break;
      case "5":
        this.gridTickets.order([6, "asc"]).draw();
        console.log("Ordenado por Asignado a ascendente");
        break;
      case "6":
        this.gridTickets.order([6, "desc"]).draw();
        console.log("Ordenado por Asignado a descendente");
        break;

      default:
        if (opcion !== null) {
          alert("❌ Opción no válida");
        }
    }
  }

  // Función para exportar la lista de tickets a CSV
  exportarCSV() {
    try {
      const data = this.gridTickets.rows({ search: "applied" }).data();
      let csv =
        "Id, Titulo, Categoria, Prioridad, Estado, Usuario, Asignado a, Fecha de Creación\n";

      data.each(function (fila) {
        const filaLimpia = [];
        for (let i = 0; i < fila.length - 1; i++) {
          let valor = fila[i].toString().replace(/<[^>]*>/g, "");
          valor = valor.replace(/"/g, '""');
          filaLimpia.push(`"${valor}"`);
        }
        csv += filaLimpia.join(",") + "\n";
      });

      const blob = new Blob(["\ufeff" + csv], {
        type: "text/csv;charset=utf-8;",
      });
      const link = document.createElement("a");
      const url = URL.createObjectURL(blob);
      const fecha = new Date().toISOString().split("T")[0];

      link.setAttribute("href", url);
      link.setAttribute("download", `tickets_${fecha}.csv`);
      link.style.visibility = "hidden";

      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      alert("✅ Archivo CSV descargado correctamente");
      console.log("✅ CSV exportado:", `tickets_${fecha}.csv`);
    } catch (error) {
      console.error("❌ Error al exportar CSV:", error);
      alert("Error al exportar CSV. Revisa la consola.");
    }
  }

  // Función para buscar tickets en la tabla
  buscarTickets() {
    const valorBusqueda = this.inputBuscarTickets.value;
    console.log("🔍 Buscando:", valorBusqueda);
    this.gridTickets.search(valorBusqueda).draw();
  }
}

new ListaTickets();
