# Análisis de Integración de Notificaciones

## 1. Módulos principales de notificaciones

### a) Modelo de notificaciones
- Archivo: `app/models/Notificacion.php`
- Función: acceso a datos de la tabla `notificaciones`.
- Operaciones principales:
  - `crear(...)`: crea notificaciones internas para un usuario.
  - `obtenerPorUsuario(...)`: carga notificaciones para un usuario.
  - `contarNoLeidas(...)`: cuenta notificaciones no leídas.
  - `marcarLeida(...)`: marca una notificación como leída.
  - `marcarTodasLeidas(...)`: marca todas como leídas.

### b) Controlador de notificaciones internas
- Archivo: `app/controllers/NotificacioneController.php`
- Función: atender solicitudes HTTP para la UI de notificaciones.
- Rutas esperadas:
  - `GET /notificaciones` → obtener notificaciones listadas.
  - `POST /notificaciones/leida` → marcar una notificación como leída.
  - `POST /notificaciones/todas` → marcar todas como leídas.

### c) Preferencias y historial
- Archivo: `app/controllers/preferenciasNotificacionController.php`
- Función:
  - obtener/actualizar preferencia de notificación del usuario.
  - obtener historial de notificaciones enviadas.
- Lógica de preferencia en: `app/models/usuario.php` con:
  - `obtenerPreferenciaNotificacion($id_usuario)`
  - `actualizarPreferenciaNotificacion($id_usuario, $preferencia)`
  - `obtenerHistorialNotificaciones($id_usuario, $limite)`

### d) Auditoría y envío de email
- Archivo: `app/helpers/notificacion_helper.php`
- Función: registrar y actualizar el historial de envíos en `notificaciones_enviadas`.
- Usa funciones como:
  - `registrarNotificacionEnviada($datos)`
  - `actualizarEstadoNotificacion(... )`
  - `marcarNotificacionRecibida(...)`
  - `obtenerHistorialNotificacionesCita(...)`

## 2. Dónde se generan notificaciones hoy

### a) Al crear una cita
- Archivo: `app/controllers/citasClienteController.php`
- Lado: back-end de creación de citas.
- Comportamiento:
  - crea la cita en la base de datos.
  - envía email de confirmación al propietario.
  - registra el resultado en `notificaciones_enviadas`.
- Conexión: `enviarNotificacionCitaCreada(...)` + `registrarNotificacionEnviada(...)`.

### b) Recordatorios automáticos
- Archivo: `app/helpers/cron_recordatorios.php`
- Lado: script cron / back-end batch.
- Función: enviar recordatorios de citas 24h antes.
- Conexión:
  - lee citas programadas.
  - verifica preferencia de notificación del usuario.
  - envía email de recordatorio.
  - registra éxito/fallo en `notificaciones_enviadas`.

### c) Notificaciones internas de seguimiento
- Archivo: `app/models/Seguimientos.php`
- Lado: back-end de seguimiento médico.
- Función: crea notificaciones para:
  - propietario del paciente.
  - profesional que registra el seguimiento.
- Conexión: usa `new Notificacion()` y `crear(...)`.

## 3. Dónde se muestran las notificaciones en el front-end

### a) Panel superior de usuarios
- `app/views/layouts/panel_superio_paciente.php`
- `app/views/layouts/panel_superior_veterinario.php`
- `app/views/layouts/panel_superior_representante.php`
- `app/views/layouts/panel_superior_administrador.php`

Estas vistas contienen:
- icono/badge de notificaciones.
- lista desplegable de notificaciones.
- botón "Ver todas".
- elementos HTML con ids como `badgeNotificaciones`, `listaNotificaciones`.

### b) Barra lateral / widget adicional
- `app/views/layouts/sidebar_notifi_pasiente.php`
- `app/views/layouts/sidebar_notifi_veterinario.php`

### c) Configuración de notificaciones del cliente
- `app/views/dashboard/cliente/confi.php`
- Contiene:
  - pestaña de preferencias.
  - pestaña de historial de notificaciones.
  - llamadas JS para cargar y mostrar el historial.

## 4. ¿Dónde hay que unir los lados?

### 1) Unir el backend de notificaciones con la UI
- Las vistas del panel superior deben hacer peticiones al controlador `NotificacioneController`.
- Los botones `marcarLeida()` y `marcarTodasLeidas()` del front-end deben enviar `POST` a:
  - `/notificaciones/leida`
  - `/notificaciones/todas`
- La lista desplegable debe cargarse con `GET /notificaciones`.

### 2) Unir las preferencias con el sistema de envío
- En la configuración de usuario (`dashboard/cliente/confi.php`):
  - solicitar la preferencia actual con `preferenciasNotificacionController.php?accion=obtener`
  - permitir cambiarla con `POST` `accion=actualizar`
- En el cron de recordatorios:
  - respetar `preferencia_notificacion` del usuario.

### 3) Unir notificaciones internas con los eventos relevantes
- Donde se crea un seguimiento, debe llamar a `Notificacion::crear(...)`.
- Donde se crea una cita o se confirma una acción importante, también puede crearse una notificación interna.
- Ejemplos claros:
  - `Seguimientos.php` ya crea notificaciones internas.
  - `CitasClienteController.php` debe crear notificaciones o avisos si quiere mostrarlas en la app.

### 4) Unir el envío de email con el historial de auditoría
- Todo email enviado desde citas o cron debe registrar su estado en `notificaciones_enviadas`.
- El helper `notificacion_helper.php` es el punto central para esa auditoría.

## 5. Resumen de las piezas que debes conectar

| Componente | Archivo | Qué hace | Debe unirse a... |
|---|---|---|---|
| Modelo de notificaciones | `app/models/Notificacion.php` | CRUD de notificaciones internas | front-end de panel superior + controladores |
| Controlador de UI | `app/controllers/NotificacioneController.php` | API JSON de notificaciones | vistas, dropdowns, JS |
| Preferencias | `app/controllers/preferenciasNotificacionController.php` | obtiene y actualiza preferencia | configuración de cliente |
| Historial usuario | `app/models/usuario.php` | obtiene historial de envíos | configuración + dashboard |
| Email audit | `app/helpers/notificacion_helper.php` | guarda envíos en `notificaciones_enviadas` | cron + citas |
| Recordatorios | `app/helpers/cron_recordatorios.php` | envía emails 24h antes | tabla de citas + preferencias |
| Seguimientos | `app/models/Seguimientos.php` | notificaciones internas por seguimiento | crear notificaciones en app |
| UI notificaciones | `app/views/layouts/*.php` | muestra notificaciones | debe consumir `NotificacioneController` |
| Configuración cliente | `app/views/dashboard/cliente/confi.php` | preferencias + historial | debe usar `preferenciasNotificacionController.php` |

## 6. Recomendación práctica

1. Verifica primero que el front-end realmente llama al controlador `NotificacioneController`.
2. Asegura que las rutas estén bien definidas y que el servidor las recibe.
3. Comprueba la tabla `notificaciones` y `notificaciones_enviadas` para ver datos reales.
4. Si quieres que el usuario vea notificaciones de cita dentro de la app, debes crear una notificación interna con `Notificacion::crear(...)` al momento de generar o modificar la cita.

---

**Archivo creado:** `NOTIFICACIONES_ANALISIS.md`

Este documento describe dónde está cada parte de notificaciones y en qué lados debes unirlas.