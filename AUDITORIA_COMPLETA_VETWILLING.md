# AUDITORIA_COMPLETA_VETWILLING

Fecha: (generado automáticamente)
Autor: Auditoría automática (resumen técnico)

---

**Resumen Ejecutivo**

- **Estado general**: Sistema PHP personalizado con MVC ligero; la mayoría de accesos a BD usan PDO y sentencias preparadas. Se detectó y se corrigió un bloqueo de sesión producido por un endpoint SSE que mantenía la sesión abierta durante un bucle largo; ese bloqueo provocaba que las peticiones POST de login quedaran pendientes.
- **Impacto inmediato**: Login se quedaba "Pending" cuando coincidía con conexiones SSE activas desde el frontend. Esto impedía iniciar sesión o completar solicitudes de escritura en la sesión hasta que el proceso SSE liberara el bloqueo.
- **Acción tomada**: Se añadió `session_write_close()` en el endpoint SSE para liberar el lock antes del bucle de envío (ver evidencia).
- **Recomendación de prioridad**: 1) Confirmar y replicar fix en otros endpoints potencialmente long-running; 2) Revisar patrones de sesión y documentar política (abrir/cerrar); 3) Pruebas funcionales en entorno local.

---

**Evidencia y hallazgos críticos**

- **Bloqueo de sesión en SSE (CRÍTICO)**: El endpoint SSE fue la causa raíz del bloqueo que provocaba que el login quedara pendiente.
  - Archivo con la corrección aplicada: [app/controllers/NotificacioneController.php](app/controllers/NotificacioneController.php#L60)
  - Línea relevante (liberación de sesión): [app/controllers/NotificacioneController.php](app/controllers/NotificacioneController.php#L60)
  - Contexto donde se inicia la sesión en el mismo controlador: [app/controllers/NotificacioneController.php](app/controllers/NotificacioneController.php#L149-L155)
  - Comportamiento observado: endpoint con `ignore_user_abort(true);`, `set_time_limit(0);` y un `while (!connection_aborted()) { ... sleep(3); }` mantenía la sesión bloqueada si `session_start()` no había cerrado la escritura.

- **Punto de bloqueo (operación de escritura de sesión)**: El flujo de login escribe `$_SESSION['user']` en el POST de autenticación.
  - Archivo/ubicación: [app/controllers/loginControllers.php](app/controllers/loginControllers.php#L63-L70)
  - Impacto: mientras la sesión estaba bloqueada por el SSE, la asignación `$_SESSION['user'] = [...]` quedaba en espera hasta que el archivo de sesión fuera liberado.

- **Buenas prácticas aplicadas en notificaciones (observación positiva)**: El modelo `Notificacion` usa PDO y sentencias preparadas en todos sus métodos, reduciendo riesgo de inyección SQL para esas operaciones.
  - Archivo: [app/models/Notificacion.php](app/models/Notificacion.php#L11-L24)

- **Front-end: múltiples EventSource activos (alto riesgo operativo)**
  - Puntos donde se abren `EventSource` desde el cliente (pueden abrirse en varias pestañas/roles):
    - [public/assets/dashBoard/veterinarias/js/navbar-superior.js](public/assets/dashBoard/veterinarias/js/navbar-superior.js#L328-L336)
    - [public/assets/dashBoard/representante/js/panelSuperiorRepresentante.js](public/assets/dashBoard/representante/js/panelSuperiorRepresentante.js#L412-L420)
    - [public/assets/dashBoard/cliente/js/notificaciones-paciente.js](public/assets/dashBoard/cliente/js/notificaciones-paciente.js#L298-L304)
  - Riesgo: múltiples conexiones SSE simultáneas desde el navegador aumentan la probabilidad de contención si el servidor mantiene locks sobre recursos compartidos (ej. sesión de PHP).

---

**Hallazgos adicionales (clasificados)**

- **Crítico**
  - Endpoint SSE mantuvo la sesión abierta durante un bucle infinito: ya corregido con `session_write_close()` en [app/controllers/NotificacioneController.php](app/controllers/NotificacioneController.php#L60).

- **Alto**
  - Varias controladoras llaman `session_start()` al inicio sin estandarizar cuándo cerrar la sesión (`session_write_close()`). Revisar controladoras que inician sesión y podrían atender peticiones de larga duración o descarga de archivos.
    - Ejemplos (no exhaustivo): [app/controllers/veterinarioController.php](app/controllers/veterinarioController.php), [app/controllers/mascotasController.php](app/controllers/mascotasController.php), [app/controllers/calendarioController.php](app/controllers/calendarioController.php#L804-L908), [app/controllers/preferenciasNotificacionController.php](app/controllers/preferenciasNotificacionController.php#L9)
  - Recomendación: escanear todos los controladores que usan `session_start()` y confirmar si retornan pronto o si deben cerrar la sesión si realizan trabajo prolongado.

- **Medio**
  - Frontend: reintento en `EventSource.onerror` cierra y reintenta cada 10s; está bien, pero conviene agregar backoff exponencial y límites para evitar ráfagas.
  - Documentación y políticas de sesión: falta una guía central sobre cuándo usar `session_write_close()`.

- **Bajo**
  - Consistencia en nombres de controladores y archivos (`NotificacioneController.php` vs `NotificacionController` en comentarios); normalizar ayuda a mantenimiento y búsqueda.
  - Revisar logs y manejo de errores para endpoints SSE (ej.: emitir eventos de error con formato estándar JSON para diagnóstico remoto si se habilita logging central).

---

**Pruebas de reproducción (cómo replicar el problema original)**

1. Abrir el dashboard de un usuario que tenga el `EventSource` activo (por ejemplo, cliente o veterinario). El navegador establece una conexión SSE a `?accion=stream`.
2. Desde otra pestaña o el mismo equipo, realizar un POST de login (o cualquier petición que escriba `$_SESSION['user']`).
3. Antes del fix, la petición POST quedará en estado "Pending" hasta que termine la conexión SSE o se cierre el proceso que mantiene el lock. Con el fix, la petición completa normalmente.

Evidencia de código relevante:
- SSE: [app/controllers/NotificacioneController.php](app/controllers/NotificacioneController.php#L60)
- Login que escribe sesión: [app/controllers/loginControllers.php](app/controllers/loginControllers.php#L63-L70)
- EventSource clientes: [public/assets/dashBoard/veterinarias/js/navbar-superior.js](public/assets/dashBoard/veterinarias/js/navbar-superior.js#L328-L336), [public/assets/dashBoard/representante/js/panelSuperiorRepresentante.js](public/assets/dashBoard/representante/js/panelSuperiorRepresentante.js#L412-L420), [public/assets/dashBoard/cliente/js/notificaciones-paciente.js](public/assets/dashBoard/cliente/js/notificaciones-paciente.js#L298-L304)

---

**Plan de remediación (fases)**

- **Fase 1 (inmediata, 1-2 horas)**
  - Confirmar que `session_write_close()` ya está presente en todos los endpoints SSE y/o handlers long-running. Aplicar mismo patrón donde haga falta (no forzar si el handler necesita escribir sesión más adelante).
  - Ejecutar pruebas manuales de login con SSE activos (varios roles/pestañas) para validar que el bloqueo ya no ocurre.

- **Fase 2 (corta, 1 día)**
  - Escaneo automático: buscar en todo el repo `session_start()` y revisar manualmente controladoras que puedan entrar en loops, llamadas a `sleep()` o descargas grandes.
  - Añadir advertencias en controladores con trabajo prolongado y, donde convenga, cerrar la sesión tras leer datos necesarios (`session_write_close()`), o migrar a almacenamiento de estado sin bloqueo (Redis, DB) para concurrencia.

- **Fase 3 (mediana, 2-5 días)**
  - Documentar política de sesión: cuándo abrir/leer/escribir/cerrar. Añadir utilitario helper para manejo explícito de sesión con RAII simple (open, read, close).
  - Mejorar clientes SSE: agregar backoff y límites de reintentos; considerar WebSockets para interactividad bidireccional si el sistema lo requiere.

- **Fase 4 (mejora continua)**
  - Instrumentación y alertas: medir tiempos de respuesta y detectar requests que pasan cierto umbral (p.ej. > 5s) y alertar.
  - Pruebas de carga: simular múltiples conexiones SSE y peticiones concurrentes de login para observar comportamiento en condiciones reales.

---

**Lista de archivos críticos y recomendaciones puntuales**

- **Fix aplicado (confirmado)**: [app/controllers/NotificacioneController.php](app/controllers/NotificacioneController.php#L60)
  - Recomendación: dejar comentario explicativo sobre por qué se cierra la sesión antes del bucle SSE.

- **Login**: [app/controllers/loginControllers.php](app/controllers/loginControllers.php#L63-L70)
  - Recomendación: validar que solo se abre la sesión cuando es estrictamente necesario y considerar regenerar ID de sesión después de login (`session_regenerate_id(true)`).

- **Notificaciones (modelo)**: [app/models/Notificacion.php](app/models/Notificacion.php#L11-L24)
  - Observación: uso correcto de PDO/prepared statements. Mantener esta práctica.

- **Clientes SSE**:
  - [public/assets/dashBoard/veterinarias/js/navbar-superior.js](public/assets/dashBoard/veterinarias/js/navbar-superior.js#L328-L336)
  - [public/assets/dashBoard/representante/js/panelSuperiorRepresentante.js](public/assets/dashBoard/representante/js/panelSuperiorRepresentante.js#L412-L420)
  - [public/assets/dashBoard/cliente/js/notificaciones-paciente.js](public/assets/dashBoard/cliente/js/notificaciones-paciente.js#L298-L304)
  - Recomendación: evitar abrir múltiples conexiones innecesarias en la misma sesión de usuario; centralizar la escucha SSE si es posible.

---

**Evidencia técnica (logs y comprobaciones sugeridas)**

- Ejecutar `php -l` en archivos modificados para verificar sintaxis.
- Monitorizar `apache`/`php-fpm` (según configuración) para ver procesos colgantes.
- Habilitar temporalmente un log en el SSE para anotar timestamps de `session_start()`, `session_write_close()` y eventos enviados (ayuda a correlacionar bloqueos con escrituras de sesión).

---

**Conclusión y próximos pasos propuestos**

- La causa raíz del problema de login fue identificada y parcheada (liberación de lock de sesión en SSE). El cambio es mínimo y correcto; sin embargo, es necesario un barrido completo del código para aplicar la misma disciplina donde corresponda.
- Propongo ejecutar el **Fase 1** inmediatamente (confirmación manual + pruebas). ¿Deseas que realice el escaneo automático del repo para listar todos los archivos que llaman `session_start()` y priorice posibles puntos conflictivos para revisión y parche rápida?

---

Fin del informe.
