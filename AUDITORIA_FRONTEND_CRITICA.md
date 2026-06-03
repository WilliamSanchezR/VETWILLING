# AUDITORIA FRONTEND CRÍTICA — VETWILLING

Fecha: 2026-06-03
Alcance: Análisis exclusivo del flujo Frontend (login, navbar, dropdowns, notificaciones, navegación).
Prioridad: se priorizan únicamente errores que impidan:
  1) Iniciar sesión
  2) Abrir dropdowns
  3) Navegar
  4) Usar notificaciones

---

Resumen breve
--------------
Se han identificado varias fallas de integración entre scripts y el DOM que causan comportamientos inconsistentes en la barra superior (navbar), dropdowns de perfil/notificaciones y navegación. Los problemas más críticos son: suposiciones inválidas sobre elementos DOM (provocando TypeError), selectores inconsistentes entre scripts y plantillas HTML, colisiones de funciones globales y mezcla de estrategias de visibilidad (clases vs style.display). Estas fallas impiden la apertura de dropdowns, el funcionamiento de la campana de notificaciones y la navegación confiable.

Hallazgos priorizados (criticos → medios)
-----------------------------------------
- Error 1 — Script rompe al iniciarse por elementos DOM faltantes (TypeError)
  - Error encontrado: uso de `addEventListener` sobre `null` (no hay guard extern).
  - Archivo: `public/assets/global/js/menu.js`
  - Línea aproximada: ~L39 (línea donde aparece `this.sidebarToggle.addEventListener('click', ...)`).
  - Severidad: Crítica
  - Impacto: Si la página no contiene `#sidebarToggle` (o el DOM aún no lo ha cargado), el script lanza una excepción que aborta `menu.js` y puede impedir inicializaciones y handlers dependientes del menú; provoca que la navegación lateral y toggles no respondan. Afecta directamente la navegación y apertura de menús.
  - Solución propuesta: Añadir comprobaciones defensivas antes de usar nodos (p.ej. `if (this.sidebarToggle) this.sidebarToggle.addEventListener(...);`). Encapsular `bindEvents()` para tolerar ausencia de elementos. Garantizar `DOMContentLoaded` antes de cachear.

- Error 2 — Selectores inconsistentes entre scripts y plantillas (IDs vs data-attributes vs clases)
  - Error encontrado: varios scripts usan distintos conjuntos de selectores para los mismos controles (ej. `#btnNotificaciones` / `[data-dropdown="notificaciones"]` / `.btn-notificaciones`). Cuando se carga el script "equivocado" para la plantilla actual, los event listeners no se registran.
  - Archivos implicados: `public/assets/dashBoard/veterinarias/js/navbar-superior.js` (usa IDs como `btnNotificaciones`, `perfilDropdown`), `public/assets/dashBoard/cliente/js/nav.js` (usa `data-dropdown`, `#dropdownNotificaciones`), `public/assets/dashBoard/representante/js/panelSuperiorRepresentante.js` (usa clases `.btn-notificaciones`, expone funciones globales para onclick). 
  - Líneas aproximadas: `navbar-superior.js` ~L1-L80 (declaración de IDs), `nav.js` ~L1-L60 (configuración inicial), `panelSuperiorRepresentante.js` ~L1-L40 (caché de selectores / `initDropdowns`).
  - Severidad: Crítica (alto)
  - Impacto: La campana de notificaciones, el dropdown de perfil y controles de navbar no reciben handlers; resultado: botones no responden y no se puede abrir dropdown ni ver notificaciones.
  - Solución propuesta: Normalizar un único contrato DOM (recomiendo `data-*` attributes: `data-dropdown="..."` y `data-action`), y actualizar los scripts a una API compartida. Alternativa de mitigación rápida: hacer que cada script pruebe múltiples selectores en cascada (ID → data-attr → clase) y registre handlers en el primero que exista.

- Error 3 — Uso de `onclick` inline que llama funciones globales no garantizadas (race / indefinición)
  - Error encontrado: Templates (p.ej. `panel_superior_representante.php`) usan `onclick="toggleNotificaciones()"` y similares. Estos nombres globales se definen más tarde por scripts que pueden fallar o cargarse con `defer`, provocando `ReferenceError` en el momento del click si la función no existe.
  - Archivos implicados: `app/views/layouts/panel_superior_representante.php` (HTML con `onclick`), `public/assets/dashBoard/representante/js/panelSuperiorRepresentante.js` (define `window.toggleNotificaciones` en init), `public/assets/dashBoard/cliente/js/nav.js` (usa `data-dropdown` approach).
  - Líneas aproximadas: HTML: `panel_superior_representante.php` (botones con `onclick`) alrededor de las secciones de Notificaciones/Perfil; JS: `panelSuperiorRepresentante.js` ~L110-L120 (exposición global).
  - Severidad: Alta
  - Impacto: Si el script que expone la función no se ejecuta por error o se carga después del primer uso, los clicks fallarán con error; impide abrir dropdowns y usar notificaciones.
  - Solución propuesta: Remover `onclick` inline y registrar listeners en JS (event delegation) o proveer stubs globales seguros inmediatamente antes del HTML (p.ej. `window.toggleNotificaciones = window.toggleNotificaciones || function(){}`) hasta migrar a listeners JS.

- Error 4 — Mezcla de mecanismos de visibilidad (clases `is-hidden` / `show` / `style.display`) → inconsistencia de estado
  - Error encontrado: `navbar-superior.js` usa la clase `is-hidden`; `nav.js` usa `.classList.toggle('show')`; otros scripts usan `element.style.display = 'block' / 'none'`. Cerrar/abrir desde un script no garantiza que otro reconozca el estado.
  - Archivos implicados: `public/assets/dashBoard/veterinarias/js/navbar-superior.js` (usa `is-hidden`), `public/assets/dashBoard/cliente/js/nav.js` (usa `show`), `public/assets/dashBoard/representante/js/panelSuperiorRepresentante.js` (usa `style.display`).
  - Líneas aproximadas: `navbar-superior.js` funciones `toggleDropdown` ~L30-L60; `nav.js` `toggleDropdown` ~L60-L100; `panelSuperiorRepresentante.js` `toggleNotificaciones` ~L120-L140.
  - Severidad: Alta
  - Impacto: Un dropdown puede aparentar estar abierto para un script y cerrado para otro; provoca elementos que no responden a closers o que quedan visibles/invisibles confusamente.
  - Solución propuesta: Establecer una convención única (recomiendo una clase `is-open` o atributo `data-open="true"`) y actualizar todos los scripts y estilos para leer/alterar esa única fuente de verdad. Como mitigación rápida, añadir adaptadores en los scripts que traduzcan entre `is-hidden` / `show` / `style.display`.

Error 5 — Colisiones de funciones globales (sobreescritura / ambigüedad)
  - Error encontrado: múltiples scripts exportan funciones globales con los mismos nombres (`window.toggleTheme`, `window.abrirSidebarMovil`, `window.togglePerfilMenu`, etc.). La implementación que quede activa depende del orden de carga y puede sobrescribir la otra.
  - Archivos implicados: `public/assets/dashBoard/veterinarias/js/master-handler.js` (~L199), `public/assets/dashBoard/representante/js/panelSuperiorRepresentante.js` (~L283), `public/assets/dashBoard/administrador/js/panelSuperiorAdmin.js` (~L101).
  - Severidad: Media - Alta
  - Impacto: Comportamientos impredecibles (tema, sidebar, toggles) y errores cuando una función esperada ya fue sobrescrita o no inicializada.
  - Solución propuesta: Encapsular la API UI en un único namespace (p.ej. `window.VW = window.VW || { ui: { ... } }`) y referenciar `VW.ui.toggleTheme()` desde HTML/script. Evitar exponer múltiples funciones top-level; usar eventos custom (`document.dispatchEvent(new CustomEvent('vw:toggleTheme'))`) si se desea desacoplar.

Error 6 — Botones sin `type="button"` dentro del DOM pueden provocar submits accidentales
  - Error encontrado: varios botones en la barra superior no especifican `type="button"` (p.ej. `#btnMenuMobile` en [app/views/layouts/panel_superior_veterinario.php](app/views/layouts/panel_superior_veterinario.php)). En formularios anidados o por estructura HTML esto puede provocar envíos no deseados.
  - Archivos implicados: `app/views/layouts/panel_superior_veterinario.php` (botón `id="btnMenuMobile"`) y otras plantillas de navbar.
  - Severidad: Media
  - Impacto: Clicks en botones pueden desencadenar submit de formularios (navegación inesperada o recarga) y, en combinación con problemas del backend, dar la impresión de "carga indefinida" al usuario.
  - Solución propuesta: Asegurar `type="button"` en todos los botones que no realizan submit. Auditar plantillas y corregir los botones reutilizados en los navbars.

Pruebas y pasos rápidos de verificación
------------------------------------
1. Abrir DevTools → Console y Network.
2. Cargar una página del dashboard y observar excepciones en Console (buscar `TypeError` relacionados con `addEventListener` y `undefined is not a function`).
3. Verificar que no haya errores al hacer click en: campana de notificaciones, avatar de perfil, botones del navbar. Si un `ReferenceError` aparece al hacer click en un `onclick` inline, eso confirma Error 3.
4. Comprobar que no existen solicitudes bloqueadas/sin respuesta en Network que correspondan a endpoints de notificaciones (esto indica problemas de conectividad o servidor — fuera de este alcance).

Remediación priorizada (acciones sugeridas, ordenadas)
----------------------------------------------------
1. Patch rápido (10-30 min):
   - Añadir guards en `menu.js` y otros scripts antes de usar elementos DOM (evitar `null.addEventListener`).
   - Actualizar scripts del navbar para intentar múltiples selectores en cascada (ID → data-attr → clase).
   - Añadir `type="button"` a botones del navbar.

2. Media (1-2 días):
   - Normalizar contrato DOM: migrar a `data-dropdown` y `data-action` y actualizar plantillas + scripts.
   - Reemplazar `onclick` inline por event listeners (o exponer stubs seguros hasta la migración completa).

3. Mejora (2-5 días):
   - Refactorizar globales a `window.VW` o eventos custom.
   - Homogeneizar visibilidad (usar `is-open` o `data-open`) y ajustar CSS.
   - Añadir tests E2E (puppeteer/playwright) que validen apertura de dropdowns y clicks principales.

¿Quieres que aplique los cambios rápidos ahora (patch defensivo en `menu.js`, adaptadores de selectores en los scripts de navbar y `type="button"` en plantillas)? Puedo preparar y aplicar los parches pequeños inmediatamente y ejecutar comprobaciones sintácticas.

Fin del documento.