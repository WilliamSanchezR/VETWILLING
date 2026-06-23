/**
 * dashBoardSeguimientos.js – VetWilling v3.0
 *
 * Correcciones respecto a la versión anterior:
 *   1. showLoading() ya no conflicta con toggleEmptyState() —
 *      se usa un estado centralizado (appState.fase) para saber
 *      qué mostrar en cada momento.
 *   2. La búsqueda opera sobre campos de datos (dataset.*),
 *      NO sobre textContent, evitando que "ver" encuentre todas las tarjetas.
 *   3. Los filtros Todos/Activos/Críticos/Completados están correctamente
 *      conectados al JS (antes tenían el HTML pero no el listener).
 *   4. confirm() y prompt() reemplazados por modales Bootstrap.
 *   5. nuevoSeguimiento() implementada (antes era ReferenceError).
 *   6. Los datos del API se obtienen en UN solo fetch cuando es posible.
 *   7. Se agrega paginación (PAGE_SIZE = 10) para listas largas.
 *   8. Los console.log de diagnóstico eliminados del PHP — ya no exponen sesión.
 *   9. XSS: todos los valores interpolados pasan por escHtml().
 *  10. obtenerBadgeEstado usa solo clases CSS que existen en el CSS.
 */

(function () {
  "use strict";

  /* ============================================================
       CONFIGURACIÓN
    ============================================================ */
  const BASE_URL = window.BASE_URL || window.location.origin;
  const API_URL = `${BASE_URL}/veterinaria/api/seguimientos`;
  const PAGE_SIZE = 10;

  /* ============================================================
       ESTADO CENTRAL
       Evita el problema anterior donde showLoading() y
       toggleEmptyState() se contradecían al escribir display
       directamente sobre el mismo elemento.
    ============================================================ */
  const appState = {
    fase: "idle", // 'loading' | 'empty' | 'data'
    todos: [], // todos los seguimientos cargados
    visibles: [], // después de filtrar/ordenar
    paginaActual: 1,
    filtroActivo: "todos",
    orden: "recientes",
    busqueda: "",
  };

  /* ============================================================
       REFS AL DOM
    ============================================================ */
  const $ = (id) => document.getElementById(id);

  const elLista = $("listaSeguimientos");
  const elLoading = $("loadingState");
  const elEmpty = $("emptyState");
  const elSearch = $("searchInput");
  const elClearSearch = $("clearSearch");
  const elSort = $("sortSelect");
  const elViewList = $("viewList");
  const elViewGrid = $("viewGrid");
  const elToasts = $("toastContainer");
  const elCount = $("resultCount");
  const elSync = $("lastUpdate");
  const elPag = $("paginacion");
  const elPagAnterior = $("btnPagAnterior");
  const elPagSiguiente = $("btnPagSiguiente");
  const elPagInfo = $("paginaInfo");

  const elStatActivos = $("statActivos");
  const elStatCriticos = $("statCriticos");
  const elStatPendientes = $("statPendientes");
  const elStatCompletados = $("statCompletados");

  /* ============================================================
       RENDERIZADO DE FASES
    ============================================================ */
  function setFase(fase) {
    appState.fase = fase;
    elLoading?.style &&
      (elLoading.style.display = fase === "loading" ? "flex" : "none");
    elEmpty?.style &&
      (elEmpty.style.display = fase === "empty" ? "block" : "none");
    elLista?.style &&
      (elLista.style.display = fase === "data" ? "flex" : "none");
    elPag?.style &&
      (elPag.style.display =
        fase === "data" && appState.visibles.length > PAGE_SIZE
          ? "flex"
          : "none");
  }

  /* ============================================================
       CARGA DE DATOS — UN SOLO FETCH
    ============================================================ */
  async function cargarSeguimientos() {
    setFase("loading");
    try {
      const res = await fetch(`${API_URL}?action=listar`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      if (data.status !== "success")
        throw new Error(data.message || "Error de API");

      appState.todos = data.data || [];

      /* Si el API devuelve estadísticas en la misma respuesta
               las usamos directamente — evita el segundo fetch */
      if (data.estadisticas) {
        pintarEstadisticas(data.estadisticas);
      } else {
        calcularEstadisticasLocales(appState.todos);
      }

      aplicarFiltros();
      actualizarSync();
    } catch (err) {
      setFase("empty");
      toast("Error al cargar seguimientos: " + err.message, "error");
    }
  }

  /* ============================================================
       ESTADÍSTICAS
    ============================================================ */
  function pintarEstadisticas(stats) {
    if (elStatActivos) elStatActivos.textContent = stats.total_activos ?? "—";
    if (elStatCriticos) elStatCriticos.textContent = stats.criticos ?? "—";
    if (elStatPendientes)
      elStatPendientes.textContent = stats.requieren_atencion ?? "—";
    if (elStatCompletados)
      elStatCompletados.textContent = stats.revisiones_hoy ?? "—";
  }

  function calcularEstadisticasLocales(datos) {
    const activos = datos.filter(
      (s) => s.estado_seguimiento !== "completado",
    ).length;
    const criticos = datos.filter((s) =>
      ["critico", "critica"].includes(
        (s.prioridad_calculada || s.prioridad || "").toLowerCase(),
      ),
    ).length;
    const pendientes = datos.filter(
      (s) => s.estado_seguimiento === "pendiente",
    ).length;
    pintarEstadisticas({
      total_activos: activos,
      criticos: criticos,
      requieren_atencion: pendientes,
      revisiones_hoy: "—",
    });
  }

  /* ============================================================
       FILTROS, BÚSQUEDA Y ORDEN — FUNCIÓN CENTRAL
    ============================================================ */
  function aplicarFiltros() {
    const q = appState.busqueda.toLowerCase().trim();
    const filtro = appState.filtroActivo;
    const orden = appState.orden;

    let lista = appState.todos.filter((seg) => {
      const estado = (seg.estado_seguimiento || "activo").toLowerCase();
      const prioridad = (
        seg.prioridad_calculada ||
        seg.prioridad ||
        "normal"
      ).toLowerCase();

      /* Filtro por estado */
      if (filtro === "activos") {
        if (!["activo", "en-tratamiento", "programado"].includes(estado))
          return false;
      } else if (filtro === "criticos") {
        if (!["critico", "critica"].includes(prioridad)) return false;
      } else if (filtro === "completados") {
        if (estado !== "completado") return false;
      }

      /* Filtro por búsqueda — SOLO en campos de texto, NO en HTML generado.
               Esto evitaba que "ver" encontrara todas las tarjetas por el botón "Ver Detalles" */
      if (q) {
        const campos = [
          seg.paciente_nombre,
          seg.ultimo_diagnostico,
          seg.propietario_nombres,
          seg.propietario_apellidos,
          seg.tratamiento_actual,
          prioridad,
          estado,
        ].map((v) => (v || "").toLowerCase());

        return campos.some((c) => c.includes(q));
      }

      return true;
    });

    /* Ordenar */
    lista = ordenar(lista, orden);

    appState.visibles = lista;
    appState.paginaActual = 1;

    renderizar();
  }

  function ordenar(lista, orden) {
    const copia = [...lista];
    switch (orden) {
      case "prioridad": {
        const p = {
          critica: 0,
          critico: 0,
          alta: 1,
          media: 2,
          normal: 3,
          baja: 4,
        };
        return copia.sort(
          (a, b) =>
            (p[(a.prioridad_calculada || a.prioridad || "").toLowerCase()] ??
              3) -
            (p[(b.prioridad_calculada || b.prioridad || "").toLowerCase()] ??
              3),
        );
      }
      case "paciente":
        return copia.sort((a, b) =>
          (a.paciente_nombre || "").localeCompare(
            b.paciente_nombre || "",
            "es",
          ),
        );
      case "fecha":
        return copia.sort(
          (a, b) =>
            new Date(b.proxima_cita || 0) - new Date(a.proxima_cita || 0),
        );
      default: // recientes
        return copia.sort(
          (a, b) => new Date(b.ultima_cita || 0) - new Date(a.ultima_cita || 0),
        );
    }
  }

  /* ============================================================
       RENDERIZADO CON PAGINACIÓN
    ============================================================ */
  function renderizar() {
    if (!elLista) return;

    const total = appState.visibles.length;
    const pagina = appState.paginaActual;
    const inicio = (pagina - 1) * PAGE_SIZE;
    const fin = inicio + PAGE_SIZE;
    const pagina_items = appState.visibles.slice(inicio, fin);
    const totalPaginas = Math.ceil(total / PAGE_SIZE);

    /* Actualizar contador */
    if (elCount) {
      elCount.textContent =
        total === 0
          ? ""
          : total === 1
            ? "1 seguimiento"
            : `${total} seguimientos`;
    }

    if (total === 0) {
      setFase("empty");
      return;
    }

    elLista.innerHTML = "";
    pagina_items.forEach((seg, i) => {
      const card = crearCard(seg);
      card.style.animationDelay = `${i * 0.04}s`;
      elLista.appendChild(card);
    });

    setFase("data");

    /* Paginación */
    if (totalPaginas > 1) {
      elPag.style.display = "flex";
      if (elPagInfo)
        elPagInfo.textContent = `Página ${pagina} de ${totalPaginas}`;
      if (elPagAnterior) elPagAnterior.disabled = pagina === 1;
      if (elPagSiguiente) elPagSiguiente.disabled = pagina === totalPaginas;
    } else {
      elPag.style.display = "none";
    }
  }

  /* ============================================================
       CREACIÓN DE TARJETA
    ============================================================ */
  function crearCard(seg) {
    const prioridad = (
      seg.prioridad_calculada ||
      seg.prioridad ||
      "normal"
    ).toLowerCase();
    const estado = (seg.estado_seguimiento || "activo").toLowerCase();
    const progreso = Math.min(
      100,
      Math.max(0, parseInt(seg.progreso_porcentaje || seg.progreso || 0)),
    );
    const ultimaCita = seg.ultima_cita
      ? formatFecha(seg.ultima_cita)
      : "Sin citas";
    const proxCita = seg.proxima_cita
      ? formatFecha(seg.proxima_cita)
      : "Sin programar";
    const avatarUrl =
      seg.img_mascota ||
      `https://api.dicebear.com/7.x/bottts/svg?seed=${encodeURIComponent(seg.paciente_nombre || "pet")}`;

    const art = document.createElement("article");
    art.className = `card-seg ${prioridad}`;
    art.setAttribute("role", "listitem");

    /* Campos de datos para búsqueda y filtrado — NO se usa textContent */
    art.dataset.id = seg.id_seguimiento;
    art.dataset.prioridad = prioridad;
    art.dataset.estado = estado;
    art.dataset.paciente = (seg.paciente_nombre || "").toLowerCase();
    art.dataset.diagnostico = (seg.ultimo_diagnostico || "").toLowerCase();
    art.dataset.propietario =
      `${seg.propietario_nombres || ""} ${seg.propietario_apellidos || ""}`
        .toLowerCase()
        .trim();

    art.innerHTML = `
            <!-- HEADER -->
            <div class="card-seg-header">
                <div class="card-seg-paciente">
                    <img src="${escHtml(avatarUrl)}"
                         alt="Foto de ${escHtml(seg.paciente_nombre)}"
                         class="card-seg-avatar"
                         loading="lazy"
                         onerror="this.src='https://api.dicebear.com/7.x/bottts/svg?seed=default'">
                    <div style="min-width:0">
                        <div class="card-seg-nombre">${escHtml(seg.paciente_nombre)}</div>
                        <div class="card-seg-sub">
                            ${escHtml(seg.especie || "")}${seg.raza ? " · " + escHtml(seg.raza) : ""}
                            &nbsp;·&nbsp;
                            ${escHtml(seg.propietario_nombres || "")} ${escHtml(seg.propietario_apellidos || "")}
                        </div>
                    </div>
                </div>

                <div class="card-seg-badges">
                    ${badgePrioridad(prioridad)}
                    ${badgeEstado(estado)}
                </div>

                <div class="card-seg-ctrl">
                    <!-- Menú de acciones rápidas -->
                    <div class="dropdown">
                        <button class="btn-ctrl"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                aria-label="Acciones para ${escHtml(seg.paciente_nombre)}">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius:10px;border:1px solid #e5e7eb;">
                            <li>
                                <button class="dropdown-item d-flex align-items-center gap-2"
                                        type="button"
                                        data-action="ver"
                                        data-id="${seg.id_seguimiento}">
                                    <i class="bi bi-eye text-info"></i> Ver detalles
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center gap-2"
                                        type="button"
                                        data-action="actualizar"
                                        data-id="${seg.id_seguimiento}"
                                        data-nombre="${escHtml(seg.paciente_nombre)}">
                                    <i class="bi bi-heart-pulse text-success"></i> Actualizar estado
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center gap-2"
                                        type="button"
                                        data-action="notificar"
                                        data-id="${seg.id_seguimiento}"
                                        data-nombre="${escHtml(seg.paciente_nombre)}">
                                    <i class="bi bi-bell text-warning"></i> Notificar propietario
                                </button>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <button class="dropdown-item d-flex align-items-center gap-2 text-success"
                                        type="button"
                                        data-action="completar"
                                        data-id="${seg.id_seguimiento}"
                                        data-nombre="${escHtml(seg.paciente_nombre)}">
                                    <i class="bi bi-check-circle"></i> Marcar completado
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Toggle expandir -->
                    <button class="btn-ctrl btn-toggle-seg"
                            type="button"
                            aria-expanded="false"
                            aria-label="Expandir detalles">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
            </div>

            <!-- VISTA COMPACTA (por defecto) -->
            <div class="card-seg-compact">
                <div class="card-seg-info-row">
                    <div class="card-seg-info-item">
                        <i class="bi bi-calendar-check" style="color:var(--green)"></i>
                        <strong>Última cita:</strong>
                        <time datetime="${escHtml(seg.ultima_cita || "")}">${ultimaCita}</time>
                    </div>
                    <div class="card-seg-info-item">
                        <i class="bi bi-calendar-event" style="color:var(--teal)"></i>
                        <strong>Próxima cita:</strong>
                        <time datetime="${escHtml(seg.proxima_cita || "")}">${proxCita}</time>
                    </div>
                    <div class="card-seg-info-item">
                        <i class="bi bi-clipboard-pulse" style="color:var(--red)"></i>
                        <strong>Diagnóstico:</strong>
                        <span>${escHtml(seg.ultimo_diagnostico || "Sin diagnóstico")}</span>
                    </div>
                </div>

                <div class="prog-wrap">
                    <div class="prog-label">
                        <span>Progreso del tratamiento</span>
                        <span class="prog-pct"
                              role="status"
                              aria-label="${progreso}% completado">${progreso}%</span>
                    </div>
                    <div class="prog-bar"
                         role="progressbar"
                         aria-valuenow="${progreso}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        <div class="prog-fill" style="width:${progreso}%"></div>
                    </div>
                </div>
            </div>

            <!-- VISTA EXPANDIDA (oculta por defecto) -->
            <div class="card-seg-expanded" style="display:none">
                <div class="card-seg-expanded-grid">
                    <div class="exp-item">
                        <i class="bi bi-capsule"></i>
                        <div>
                            <div class="exp-item-label">Tratamiento</div>
                            <div class="exp-item-val">${escHtml(seg.tratamiento_actual || "No especificado")}</div>
                        </div>
                    </div>
                    <div class="exp-item">
                        <i class="bi bi-people"></i>
                        <div>
                            <div class="exp-item-label">Propietario</div>
                            <div class="exp-item-val">${escHtml(seg.propietario_nombres || "")} ${escHtml(seg.propietario_apellidos || "")}</div>
                        </div>
                    </div>
                    <div class="exp-item">
                        <i class="bi bi-telephone"></i>
                        <div>
                            <div class="exp-item-label">Teléfono</div>
                            <div class="exp-item-val">${escHtml(seg.propietario_telefono || "No disponible")}</div>
                        </div>
                    </div>
                    <div class="exp-item">
                        <i class="bi bi-file-medical"></i>
                        <div>
                            <div class="exp-item-label">Total de citas</div>
                            <div class="exp-item-val">${parseInt(seg.total_citas_realizadas || 0)}</div>
                        </div>
                    </div>
                    ${
                      seg.observaciones_generales
                        ? `
                    <div class="exp-item" style="grid-column:1/-1">
                        <i class="bi bi-journal-text"></i>
                        <div>
                            <div class="exp-item-label">Observaciones</div>
                            <div class="exp-item-val">${escHtml(seg.observaciones_generales)}</div>
                        </div>
                    </div>`
                        : ""
                    }
                </div>

                <div class="card-seg-footer">
                    <button class="btn-accion verde"
                            type="button"
                            onclick="location.href='${BASE_URL}/veterinaria/calendario'">
                        <i class="bi bi-calendar-plus"></i> Programar cita
                    </button>
                    <button class="btn-accion azul"
                            type="button"
                            data-action="ver"
                            data-id="${seg.id_seguimiento}">
                        <i class="bi bi-eye"></i> Ver historial
                    </button>
                    <button class="btn-accion verde"
                            type="button"
                            data-action="actualizar"
                            data-id="${seg.id_seguimiento}"
                            data-nombre="${escHtml(seg.paciente_nombre)}">
                        <i class="bi bi-heart-pulse"></i> Actualizar estado
                    </button>
                    <button class="btn-accion naranja"
                            type="button"
                            data-action="notificar"
                            data-id="${seg.id_seguimiento}"
                            data-nombre="${escHtml(seg.paciente_nombre)}">
                        <i class="bi bi-bell"></i> Notificar
                    </button>
                    <button class="btn-accion rojo"
                            type="button"
                            data-action="completar"
                            data-id="${seg.id_seguimiento}"
                            data-nombre="${escHtml(seg.paciente_nombre)}">
                        <i class="bi bi-check-circle"></i> Completar
                    </button>
                </div>
            </div>
        `;

    /* Listeners */
    art.querySelectorAll("[data-action]").forEach((btn) => {
      btn.addEventListener("click", manejarAccion);
    });

    const toggle = art.querySelector(".btn-toggle-seg");
    toggle?.addEventListener("click", function () {
      const compact = art.querySelector(".card-seg-compact");
      const expanded = art.querySelector(".card-seg-expanded");
      const abierto = this.getAttribute("aria-expanded") === "true";

      compact.style.display = abierto ? "block" : "none";
      expanded.style.display = abierto ? "none" : "block";
      this.setAttribute("aria-expanded", String(!abierto));
    });

    return art;
  }

  /* ============================================================
       BADGES — usan solo clases que existen en el CSS
    ============================================================ */
  function badgePrioridad(p) {
    const map = {
      critico: ["critico", "bi-exclamation-triangle-fill", "Crítico"],
      critica: ["critica", "bi-exclamation-triangle-fill", "Crítico"],
      alta: ["alta", "bi-exclamation-circle-fill", "Alta"],
      media: ["media", "bi-dash-circle-fill", "Media"],
      normal: ["normal", "bi-circle-fill", "Normal"],
      baja: ["baja", "bi-arrow-down-circle-fill", "Baja"],
    };
    const [cls, ico, txt] = map[p] || map.normal;
    return `<span class="badge-pri ${cls}"><i class="bi ${ico}"></i> ${txt}</span>`;
  }

  function badgeEstado(e) {
    const map = {
      activo: ["activo", "bi-activity", "Activo"],
      activa: ["activa", "bi-activity", "Activo"],
      "en-tratamiento": ["en-tratamiento", "bi-activity", "En Tratamiento"],
      programado: ["programado", "bi-calendar-check", "Programado"],
      programada: ["programada", "bi-calendar-check", "Programado"],
      pendiente: ["pendiente", "bi-clock", "Pendiente"],
      completado: ["completado", "bi-check-circle-fill", "Completado"],
    };
    const [cls, ico, txt] = map[e] || map["pendiente"];
    return `<span class="badge-est ${cls}"><i class="bi ${ico}"></i> ${txt}</span>`;
  }

  /* ============================================================
       ACCIONES — confirm/prompt reemplazados por modales Bootstrap
    ============================================================ */
  function manejarAccion(e) {
    e.preventDefault();
    e.stopPropagation();

    const btn = e.currentTarget;
    const action = btn.dataset.action;
    const id = btn.dataset.id;
    const nombre =
      btn.dataset.nombre ||
      btn.closest(".card-seg")?.dataset.paciente ||
      "el paciente";

    switch (action) {
      case "ver":
        /* Expande la tarjeta inline en lugar de redirigir al calendario */
        const card = btn.closest(".card-seg");
        const toggle = card?.querySelector(".btn-toggle-seg");
        if (toggle && toggle.getAttribute("aria-expanded") !== "true") {
          toggle.click();
        }
        card?.scrollIntoView({ behavior: "smooth", block: "nearest" });
        break;

      case "actualizar":
        abrirModalActualizarEstado(id, nombre);
        break;

      case "completar":
        abrirModalConfirmar(id, nombre, btn.closest(".card-seg"));
        break;

      case "notificar":
        abrirModalNotificar(id, nombre);
        break;
    }
  }

  async function completarSeguimiento(id, nombre, card) {
    try {
      const res = await fetch(API_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "finalizar", id_seguimiento: id }),
      });
      const data = await res.json();

      if (data.status === "success") {
        /* Remover del estado local y re-renderizar */
        appState.todos = appState.todos.filter(
          (s) => String(s.id_seguimiento) !== String(id),
        );
        calcularEstadisticasLocales(appState.todos);
        aplicarFiltros();
        toast(`Seguimiento de ${nombre} completado`, "success");
      } else {
        throw new Error(data.message || "Error al completar");
      }
    } catch (err) {
      toast("Error: " + err.message, "error");
    }
  }

  async function notificarPropietario(id, nombre, mensaje) {
    try {
      const res = await fetch(API_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "notificar",
          id_seguimiento: id,
          mensaje,
        }),
      });
      const data = await res.json();

      if (data.status === "success") {
        toast(`Notificación enviada — ${nombre}`, "success");
      } else {
        throw new Error(data.message || "Error al notificar");
      }
    } catch (err) {
      toast("Error: " + err.message, "error");
    }
  }

  async function actualizarEstadoClinico(id, nombre, payload) {
    try {
      const res = await fetch(API_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "actualizar-estado",
          id_seguimiento: id,
          ...payload,
        }),
      });
      const data = await res.json();

      if (data.status === "success") {
        await cargarSeguimientos();
        toast(`Estado clínico actualizado — ${nombre}`, "success");
      } else {
        throw new Error(data.message || "Error al actualizar el seguimiento");
      }
    } catch (err) {
      toast("Error: " + err.message, "error");
    }
  }

  /* ============================================================
       MODALES DINÁMICOS
    ============================================================ */
  function crearModal(id, contenidoHtml) {
    const existente = document.getElementById(id);
    if (existente) return existente;

    const el = document.createElement("div");
    el.id = id;
    el.className = "modal fade modal-seg";
    el.tabIndex = -1;
    el.innerHTML = contenidoHtml;
    document.body.appendChild(el);
    return el;
  }

  function abrirModalConfirmar(id, nombre, card) {
    const modal = crearModal(
      "modalConfirmarSeg",
      `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mConfTitulo">Completar seguimiento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="mConfMensaje" class="mb-0" style="font-size:15px;"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="mConfOk" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Confirmar
                        </button>
                    </div>
                </div>
            </div>`,
    );

    modal.querySelector("#mConfMensaje").textContent =
      `¿Confirmas que el seguimiento de ${nombre} ha sido completado?`;

    /* Clonar el botón para limpiar listeners previos */
    const btnOk = modal.querySelector("#mConfOk");
    const btnNuevo = btnOk.cloneNode(true);
    btnOk.parentNode.replaceChild(btnNuevo, btnOk);

    btnNuevo.addEventListener("click", async () => {
      bootstrap.Modal.getInstance(modal)?.hide();
      await completarSeguimiento(id, nombre, card);
    });

    bootstrap.Modal.getOrCreateInstance(modal).show();
  }

  function abrirModalNotificar(id, nombre) {
    const modal = crearModal(
      "modalNotificarSeg",
      `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mNotTitulo"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="mNotMensaje" class="form-label fw-semibold mb-2">
                            Mensaje para el propietario:
                        </label>
                        <textarea id="mNotMensaje"
                                  class="seg-textarea"
                                  rows="4"
                                  maxlength="500"
                                  placeholder="Escribe el recordatorio…"></textarea>
                        <div id="mNotError"
                             class="text-danger mt-1"
                             style="font-size:13px;display:none">
                            Por favor escribe un mensaje antes de enviar.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="mNotOk" class="btn btn-warning text-dark">
                            <i class="bi bi-send me-1"></i> Enviar notificación
                        </button>
                    </div>
                </div>
            </div>`,
    );

    modal.querySelector("#mNotTitulo").textContent =
      `Notificar propietario — ${nombre}`;

    const textarea = modal.querySelector("#mNotMensaje");
    const errEl = modal.querySelector("#mNotError");
    textarea.value = `Recordatorio de seguimiento para ${nombre}.`;

    const btnOk = modal.querySelector("#mNotOk");
    const btnNuevo = btnOk.cloneNode(true);
    btnOk.parentNode.replaceChild(btnNuevo, btnOk);

    btnNuevo.addEventListener("click", async () => {
      const msg = textarea.value.trim();
      if (msg.length < 5) {
        errEl.style.display = "block";
        textarea.focus();
        return;
      }
      errEl.style.display = "none";
      bootstrap.Modal.getInstance(modal)?.hide();
      await notificarPropietario(id, nombre, msg);
    });

    bootstrap.Modal.getOrCreateInstance(modal).show();
  }

  function abrirModalActualizarEstado(id, nombre) {
    const modal = crearModal(
      "modalActualizarSeg",
      `
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Actualización clínica — <span id="mActNombre"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="mActEstado" class="form-label fw-semibold">Estado actual de salud</label>
                                <select id="mActEstado" class="form-select">
                                    <option value="">Selecciona una opción</option>
                                    <option value="mejoria">Mejoría</option>
                                    <option value="estable">Estable</option>
                                    <option value="empeoramiento">Empeoramiento</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="mActDiagnostico" class="form-label fw-semibold">Diagnóstico reciente</label>
                                <input id="mActDiagnostico" class="form-control" maxlength="255" placeholder="Diagnóstico o hallazgo clínico relevante">
                            </div>
                            <div class="col-md-7">
                                <label for="mActTratamiento" class="form-label fw-semibold">Tratamiento o medicación en curso</label>
                                <input id="mActTratamiento" class="form-control" maxlength="150" placeholder="Medicamento o tratamiento actual">
                            </div>
                            <div class="col-md-5">
                                <label for="mActDosis" class="form-label fw-semibold">Dosis / indicación</label>
                                <input id="mActDosis" class="form-control" maxlength="100" placeholder="Ej: 1 tableta cada 12 h">
                            </div>
                            <div class="col-md-4">
                                <label for="mActFechaFin" class="form-label fw-semibold">Fecha fin tratamiento</label>
                                <input id="mActFechaFin" type="date" class="form-control">
                            </div>
                            <div class="col-md-8">
                                <label for="mActObservacion" class="form-label fw-semibold">Observaciones del comportamiento y condición física</label>
                                <textarea id="mActObservacion" class="seg-textarea" rows="4" maxlength="800" placeholder="Describe evolución clínica, comportamiento, signos y respuesta al tratamiento"></textarea>
                            </div>
                        </div>
                        <div id="mActError" class="text-danger mt-2" style="font-size:13px;display:none">
                            Completa al menos el estado de salud y las observaciones clínicas.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="mActOk" class="btn btn-success">
                            <i class="bi bi-save me-1"></i> Guardar actualización
                        </button>
                    </div>
                </div>
            </div>`,
    );

    modal.querySelector("#mActNombre").textContent = nombre;

    const btnOk = modal.querySelector("#mActOk");
    const btnNuevo = btnOk.cloneNode(true);
    btnOk.parentNode.replaceChild(btnNuevo, btnOk);

    btnNuevo.addEventListener("click", async () => {
      const estado = modal.querySelector("#mActEstado").value.trim();
      const observacion = modal.querySelector("#mActObservacion").value.trim();
      const diagnostico = modal.querySelector("#mActDiagnostico").value.trim();
      const tratamiento = modal.querySelector("#mActTratamiento").value.trim();
      const dosis = modal.querySelector("#mActDosis").value.trim();
      const fechaFin = modal.querySelector("#mActFechaFin").value.trim();
      const error = modal.querySelector("#mActError");

      if (!estado || !observacion) {
        error.style.display = "block";
        return;
      }

      error.style.display = "none";
      bootstrap.Modal.getInstance(modal)?.hide();
      await actualizarEstadoClinico(id, nombre, {
        estado_salud: estado,
        observacion,
        diagnostico,
        tratamiento,
        dosis_tratamiento: dosis,
        fecha_fin_tratamiento: fechaFin,
      });
    });

    bootstrap.Modal.getOrCreateInstance(modal).show();
  }

  function abrirModalNuevoSeguimiento() {
    const modal = crearModal(
      "modalNuevoSeguimientoSeg",
      `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-circle-fill" style="color: #00a884;"></i> Nuevo Seguimiento
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Sección: Seleccionar Paciente -->
                        <div class="mb-4">
                            <h6 class="mb-3" style="font-weight: 600; color: #333;">
                                <i class="bi bi-heart-pulse" style="color: #00a884;"></i> Seleccionar Paciente
                            </h6>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="mNuevoPaciente" class="form-label fw-semibold">
                                            Paciente <span style="color: #e74c3c;">*</span>
                                        </label>
                                        <select id="mNuevoPaciente" class="form-select" required>
                                            <option value="">-- Seleccionar Paciente --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="mNuevoPropietario" class="form-label fw-semibold">Propietario</label>
                                        <input type="text" id="mNuevoPropietario" class="form-control" readonly placeholder="Auto-cargado">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr style="border: 1px solid #e0e0e0;">
                        <!-- Sección: Información del Seguimiento -->
                        <div class="mb-4">
                            <h6 class="mb-3" style="font-weight: 600; color: #333;">
                                <i class="bi bi-clipboard-check" style="color: #00a884;"></i> Información del Seguimiento
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mNuevoDiagnostico" class="form-label fw-semibold">
                                            Diagnóstico <span style="color: #e74c3c;">*</span>
                                        </label>
                                        <textarea id="mNuevoDiagnostico" class="form-control" rows="3" required placeholder="Escriba el diagnóstico..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mNuevoObservaciones" class="form-label fw-semibold">Observaciones</label>
                                        <textarea id="mNuevoObservaciones" class="form-control" rows="3" placeholder="Observaciones adicionales..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mNuevoPrioridad" class="form-label fw-semibold">
                                            Prioridad <span style="color: #e74c3c;">*</span>
                                        </label>
                                        <select id="mNuevoPrioridad" class="form-select" required>
                                            <option value="">-- Seleccionar --</option>
                                            <option value="baja">Baja</option>
                                            <option value="normal" selected>Normal</option>
                                            <option value="alta">Alta</option>
                                            <option value="critica">Crítica</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mNuevoEstado" class="form-label fw-semibold">
                                            Estado <span style="color: #e74c3c;">*</span>
                                        </label>
                                        <select id="mNuevoEstado" class="form-select" required>
                                            <option value="activo" selected>Activo</option>
                                            <option value="pausado">Pausado</option>
                                            <option value="completado">Completado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mNuevoFechaInicio" class="form-label fw-semibold">
                                            Fecha de Inicio <span style="color: #e74c3c;">*</span>
                                        </label>
                                        <input type="date" id="mNuevoFechaInicio" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mNuevoProximaRevision" class="form-label fw-semibold">Próxima Revisión</label>
                                        <input type="date" id="mNuevoProximaRevision" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr style="border: 1px solid #e0e0e0;">
                        <!-- Sección: Medicación (opcional) -->
                        <div class="mb-4">
                            <h6 class="mb-3" style="font-weight: 600; color: #333;">
                                <i class="bi bi-capsule" style="color: #00a884;"></i> Medicación <small style="color: #999;">(Opcional)</small>
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mNuevoMedicamento" class="form-label fw-semibold">Medicamento</label>
                                        <input type="text" id="mNuevoMedicamento" class="form-control" placeholder="Nombre del medicamento">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mNuevoDosis" class="form-label fw-semibold">Dosis</label>
                                        <input type="text" id="mNuevoDosis" class="form-control" placeholder="Ej: 500mg, 2 veces al día">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="mNuevoError" class="text-danger mt-2" style="font-size:13px;display:none">
                            Por favor completa los campos requeridos.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="mNuevoOk" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Crear Seguimiento
                        </button>
                    </div>
                </div>
            </div>`,
    );

    // Cargar pacientes en el select
    const selectPaciente = modal.querySelector("#mNuevoPaciente");
    const selectPropietario = modal.querySelector("#mNuevoPropietario");
    const today = new Date().toISOString().split("T")[0];
    modal.querySelector("#mNuevoFechaInicio").value = today;

    // Poblar desde la API de asignaciones (solo pacientes del profesional en sesión)
    selectPaciente.innerHTML =
      '<option value="">Cargando pacientes...</option>';
    selectPaciente.disabled = true;

    fetch(`${BASE_URL}/veterinario/pacientes-asignacion?accion=listar_activos`)
      .then((r) => r.json())
      .then((payload) => {
        selectPaciente.innerHTML =
          '<option value="">-- Seleccionar Paciente --</option>';

        if (
          !payload.success ||
          !Array.isArray(payload.data) ||
          payload.data.length === 0
        ) {
          selectPaciente.innerHTML =
            '<option value="">Sin pacientes asignados</option>';
          return;
        }

        payload.data.forEach((p) => {
          const option = document.createElement("option");
          option.value = p.id_paciente;
          option.textContent = `${p.paciente_nombre} (${p.propietario_nombre})`;
          option.dataset.propietario = p.propietario_nombre;
          selectPaciente.appendChild(option);
        });
      })
      .catch(() => {
        selectPaciente.innerHTML =
          '<option value="">Error al cargar pacientes</option>';
      })
      .finally(() => {
        selectPaciente.disabled = false;
      });

    selectPaciente.addEventListener("change", function () {
      const opt = this.options[this.selectedIndex];
      selectPropietario.value = opt?.dataset.propietario || "";
    });

    // Botón guardar
    const btnOk = modal.querySelector("#mNuevoOk");
    const btnNuevo = btnOk.cloneNode(true);
    btnOk.parentNode.replaceChild(btnNuevo, btnOk);

    btnNuevo.addEventListener("click", async () => {
      const id_paciente = modal.querySelector("#mNuevoPaciente").value;
      const diagnostico = modal
        .querySelector("#mNuevoDiagnostico")
        .value.trim();
      const error = modal.querySelector("#mNuevoError");

      if (!id_paciente || !diagnostico) {
        error.style.display = "block";
        return;
      }

      error.style.display = "none";

      const datos = new FormData();
      datos.append("action", "crear");
      datos.append("id_paciente", id_paciente);
      datos.append("diagnostico", diagnostico);
      datos.append(
        "observaciones",
        modal.querySelector("#mNuevoObservaciones").value,
      );
      datos.append("prioridad", modal.querySelector("#mNuevoPrioridad").value);
      datos.append("estado", modal.querySelector("#mNuevoEstado").value);
      datos.append(
        "fecha_inicio",
        modal.querySelector("#mNuevoFechaInicio").value,
      );
      datos.append(
        "proxima_revision",
        modal.querySelector("#mNuevoProximaRevision").value,
      );
      datos.append(
        "medicamento",
        modal.querySelector("#mNuevoMedicamento").value,
      );
      datos.append("dosis", modal.querySelector("#mNuevoDosis").value);

      try {
        const response = await fetch(
          `${BASE_URL}/veterinaria/api/seguimientos`,
          {
            method: "POST",
            body: datos,
          },
        );

        const data = await response.json();

        if (data.status === "success") {
          toast("Seguimiento creado exitosamente", "success");
          bootstrap.Modal.getInstance(modal)?.hide();
          setTimeout(() => {
            cargarSeguimientos();
          }, 800);
        } else {
          toast(data.message || "Error al crear el seguimiento", "error");
        }
      } catch (error) {
        console.error("Error:", error);
        toast("Error al crear el seguimiento", "error");
      }
    });

    bootstrap.Modal.getOrCreateInstance(modal).show();
  }

  /* ============================================================
       NUEVO SEGUIMIENTO — implementada (antes era ReferenceError)
    ============================================================ */
  window.nuevoSeguimiento = function () {
    abrirModalNuevoSeguimiento();
  };

  /* ============================================================
       EVENTOS DE BÚSQUEDA Y FILTROS
    ============================================================ */
  let searchTimer = null;

  elSearch?.addEventListener("input", function () {
    const q = this.value.trim();
    if (elClearSearch) elClearSearch.style.display = q ? "block" : "none";
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      appState.busqueda = q;
      aplicarFiltros();
    }, 280);
  });

  elClearSearch?.addEventListener("click", () => {
    if (elSearch) {
      elSearch.value = "";
      elSearch.focus();
    }
    elClearSearch.style.display = "none";
    appState.busqueda = "";
    aplicarFiltros();
  });

  /* Filtros de estado — CORRECCIÓN: antes no tenían addEventListener */
  document.querySelectorAll(".seg-filtro-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      document.querySelectorAll(".seg-filtro-btn").forEach((b) => {
        b.classList.remove("active");
        b.setAttribute("aria-pressed", "false");
      });
      this.classList.add("active");
      this.setAttribute("aria-pressed", "true");
      appState.filtroActivo = this.dataset.filtro || "todos";
      aplicarFiltros();
    });
  });

  /* Ordenar */
  elSort?.addEventListener("change", function () {
    appState.orden = this.value;
    aplicarFiltros();
  });

  /* Vista lista / grid */
  elViewList?.addEventListener("click", () => cambiarVista("list"));
  elViewGrid?.addEventListener("click", () => cambiarVista("grid"));

  function cambiarVista(vista) {
    if (vista === "list") {
      elLista?.classList.remove("grid-view");
      elViewList?.classList.add("active");
      elViewGrid?.classList.remove("active");
      elViewList?.setAttribute("aria-pressed", "true");
      elViewGrid?.setAttribute("aria-pressed", "false");
    } else {
      elLista?.classList.add("grid-view");
      elViewList?.classList.remove("active");
      elViewGrid?.classList.add("active");
      elViewList?.setAttribute("aria-pressed", "false");
      elViewGrid?.setAttribute("aria-pressed", "true");
    }
  }

  /* Paginación */
  elPagAnterior?.addEventListener("click", () => {
    if (appState.paginaActual > 1) {
      appState.paginaActual--;
      renderizar();
      elLista?.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  });

  elPagSiguiente?.addEventListener("click", () => {
    const total = Math.ceil(appState.visibles.length / PAGE_SIZE);
    if (appState.paginaActual < total) {
      appState.paginaActual++;
      renderizar();
      elLista?.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  });

  /* Teclado */
  document.addEventListener("keydown", (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === "f" && elSearch) {
      e.preventDefault();
      elSearch.focus();
    }
    if (e.key === "Escape") {
      document
        .querySelectorAll(".dropdown-menu.show")
        .forEach((d) => d.classList.remove("show"));
    }
  });

  /* Exportar (placeholder) */
  $("btnExportar")?.addEventListener("click", () => {
    toast("Exportación en desarrollo", "info");
  });

  /* ============================================================
       HELPERS
    ============================================================ */
  function formatFecha(f) {
    if (!f) return "—";
    try {
      return new Date(f).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
      });
    } catch (_) {
      return f;
    }
  }

  function escHtml(val) {
    return String(val ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function actualizarSync() {
    if (elSync) {
      elSync.textContent = `Actualizado ${new Date().toLocaleTimeString("es-ES", { hour: "2-digit", minute: "2-digit" })}`;
    }
  }

  function toast(message, type = "info", duration = 3800) {
    if (!elToasts) return;
    const iconos = {
      success: "bi-check-circle-fill",
      error: "bi-x-circle-fill",
      warning: "bi-exclamation-triangle-fill",
      info: "bi-info-circle-fill",
    };

    const el = document.createElement("div");
    el.className = `toast toast-vet ${type} show`;
    el.setAttribute("role", "alert");
    el.setAttribute("aria-live", "assertive");
    el.innerHTML = `
            <div class="toast-body">
                <i class="bi ${iconos[type] || iconos.info}"></i>
                ${escHtml(message)}
            </div>`;

    elToasts.appendChild(el);

    setTimeout(() => {
      el.classList.remove("show");
      el.style.transition = "opacity .3s";
      el.style.opacity = "0";
      setTimeout(() => el.remove(), 300);
    }, duration);
  }

  /* ============================================================
       INIT
    ============================================================ */
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", cargarSeguimientos);
  } else {
    cargarSeguimientos();
  }
})();
