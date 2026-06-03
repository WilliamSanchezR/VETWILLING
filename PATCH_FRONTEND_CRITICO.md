# PATCH_FRONTEND_CRITICO — VETWILLING

Fecha: 2026-06-03
Alcance: Aplicación de parches de severidad Crítica/Alta según AUDITORIA_FRONTEND_CRITICA.md.

Resumen de acciones: se aplicaron parches mínimos, no invasivos, para mitigar errores que causaban TypeError, fallos por selectores inconsistentes, `onclick` inline sin stubs y discrepancias de visibilidad entre scripts. No se tocaron backend, base de datos, SSE, ni se refactorizó arquitectura ni diseño.

---

**Cambios aplicados (por archivo)**

- Archivo modificado: [public/assets/global/js/menu.js](public/assets/global/js/menu.js#L1-L200)
  - Líneas afectadas: aproximado [L12-L60] y [L90-L140] (se añadieron guards en `bindEvents`, `toggleSidebar`, `restoreSidebarState`).
  - Problema corregido: Evita `TypeError` al ejecutar `addEventListener` sobre `null` y protege accesos a `this.sidebar` cuando el DOM no contiene elementos esperados.
  - Riesgo introducido: mínima — añade comprobaciones que silencian errores de inicialización; si un elemento realmente falta por un bug en la plantilla, el menú dejará de tener comportamiento esperado en lugar de lanzar excepción (esto es deseado para estabilidad).

- Archivo modificado: [public/assets/dashBoard/veterinarias/js/navbar-superior.js](public/assets/dashBoard/veterinarias/js/navbar-superior.js#L1-L240)
  - Líneas afectadas: aproximado [L8-L28] (helpers de visibilidad) y [L60-L120] (selectores y toggleDropdown).
  - Problema corregido: Añadido adaptador de visibilidad (`mostrar`/`ocultar`/`estaOculto`) que considera `is-hidden`, `show` y `style.display`. Se añadieron selectores de fallback para tolerar variantes de template (IDs, `data-*`, clases). `toggleDropdown` ahora sincroniza `classList` y `style.display`.
  - Riesgo introducido: baja — cambios afectan únicamente la lógica de visibilidad (no el CSS) y podrían ocultar incompatibilidades subtis entre plantillas diferentes; se intentó mantener comportamiento previo al preferir `is-hidden`/`show`/`style.display` conjuntamente.

- Archivo modificado: [public/assets/dashBoard/cliente/js/nav.js](public/assets/dashBoard/cliente/js/nav.js#L1-L120)
  - Líneas afectadas: aproximado [L6-L28] (mapeo de `this.elements` con selectores fallback).
  - Problema corregido: Evita fallos por selectores faltantes (mapeos de dropdowns y botones ahora prueban variantes comunes: `#id`, `data-*`, `.clase`).
  - Riesgo introducido: muy baja — solo amplía selectores probados; si hay múltiples elementos coincidentes inesperados, el script elegirá el primero (comportamiento ya presente en `querySelector`).

- Archivo modificado: [public/assets/dashBoard/representante/js/panelSuperiorRepresentante.js](public/assets/dashBoard/representante/js/panelSuperiorRepresentante.js#L1-L120)
  - Líneas afectadas: aproximado [L1-L40] (stubs globales), [L140-L170] (synchronización `is-hidden` en toggles).
  - Problema corregido: Evita `ReferenceError` cuando HTML contiene `onclick="toggleNotificaciones()"` u otros `onclick` que hagan referencia a funciones globales no inicializadas. Se añadieron stubs seguros y se sincronizó `style.display` con la clase `is-hidden` para compatibilidad entre scripts.
  - Riesgo introducido: baja — los stubs delegan a `click()` sobre elementos existentes; si las plantillas no contienen dichos botones, las llamadas quedan como no-ops en lugar de lanzar errores.

---

**Errores corregidos (prioridad solicitada)**

- Error 1 (TypeError por elementos DOM inexistentes): CORREGIDO (menu.js)
- Error 2 (selectores inconsistentes): MITIGADO (navbar-superior.js, nav.js)
- Error 3 (onclick globales): MITIGADO (panelSuperiorRepresentante.js stubs)
- Error 4 (visibilidad inconsistente): MITIGADO (navbar-superior.js, panelSuperiorRepresentante.js)

> Nota: "mitigado" indica que se agregó tolerancia y adaptadores para evitar fallos y mantener compatibilidad; una normalización completa (migrar a único contrato `data-*` y remover código duplicado) queda pendiente.

---

**Cambios pendientes / recomendaciones (no aplicados por restricción)**

- Normalizar selectores en todas las plantillas y scripts a `data-dropdown` / `data-panel` (recomendado como paso medio a medio plazo).
- Reemplazar `onclick` inline por listeners JS y eliminar stubs una vez migradas las plantillas.
- Homogeneizar el mecanismo de visibilidad (usar `is-open` o `data-open="true"`) y refactorizar scripts para leer/alterar esa única fuente de verdad.
- Añadir pruebas E2E que validen: login, apertura de dropdowns, notificaciones y navegación principal.

---

Si deseas, aplico ahora un segundo pase que:
- Reemplaza inline `onclick` en plantillas por listeners (requiere tocar vistas HTML).
- Realiza una migración parcial a `data-*` selectors en un conjunto de plantillas + scripts.

Confírmame si quieres que ejecute ese segundo pase (recomendado solo si estás de acuerdo en modificar plantillas).