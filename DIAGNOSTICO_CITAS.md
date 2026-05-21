# Diagnóstico de errores en agendamiento de citas (21-may-2026)

## Resumen del flujo de agendamiento
- El proceso de crear una cita desde el calendario veterinario involucra:
  1. Formulario JS (citas.js) que arma los datos y los envía por fetch a `/calendario/storeEvent`.
  2. El router (index.php) dirige a `calendarioController.php`.
  3. El controlador valida sesión, datos, disponibilidad, conflictos de horario y especialidad.
  4. Si todo es correcto, llama a `Eventos::registrar()` para guardar la cita.
  5. Si algo falla, responde con error y mensaje específico.

## Puntos críticos y posibles fallos
1. **Disponibilidad del profesional**
   - Si el profesional no tiene disponibilidad activa en la veterinaria y especialidad, la cita será rechazada.
   - Verifica que existan registros en la tabla `disponibilidad_usuario` para el profesional, veterinaria y especialidad.
2. **Conflicto de horario**
   - Si ya existe una cita en el rango de fechas/horas, la cita será rechazada.
   - El backend responde con mensaje de conflicto y detalles.
3. **Especialidad**
   - Si no hay especialidad activa para el servicio y veterinaria, la cita no se crea.
   - Revisa la configuración de especialidades activas.
4. **Datos requeridos**
   - Si falta algún campo obligatorio (servicio, subservicio, paciente, fechas), la cita no se crea.
   - El backend responde con mensaje de error específico.
5. **Asignación profesional**
   - Si la tabla `paciente_profesional_asignacion` no existe o falla, la cita se crea pero no se asigna correctamente.
6. **Errores de base de datos**
   - Si hay errores en la conexión o en los datos, se rechaza la inserción y se loguea el error.

## Simulacro de prueba
- El JS arma correctamente el objeto y lo envía a `/calendario/storeEvent`.
- El backend valida sesión, datos, disponibilidad, conflictos y especialidad.
- Si algo falla, responde con error y mensaje específico.
- Los errores y datos recibidos se loguean en `app/logs/citas_error.log` y en el log de PHP.

## Recomendaciones y pasos para depuración
- Verifica que el profesional tenga disponibilidad activa en la veterinaria y especialidad asociada.
- Asegúrate de que los IDs enviados desde el formulario sean válidos y existan en la BD.
- Revisa los mensajes de error devueltos por el backend y los logs.
- Si el error es de conflicto de horario, revisa las citas ya agendadas.
- Si el error es de especialidad, revisa la configuración de especialidades activas para el servicio y veterinaria.
- Si el error es de disponibilidad, revisa la agenda del profesional.
- Si el error es de datos, revisa que todos los campos requeridos estén presentes.
- Si el error es de asignación, revisa la tabla `paciente_profesional_asignacion`.

## Siguiente paso sugerido
- Probar crear una cita con todos los datos correctos y revisar la respuesta JSON y los logs si falla.
- Si el error persiste, revisar la configuración de la BD y los modelos relacionados.
- Consultar los logs en `app/logs/citas_error.log` y el log de errores de PHP para detalles técnicos.
