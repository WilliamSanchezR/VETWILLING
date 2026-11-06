-- Devuelve las tablas a su nombre original.
RENAME TABLE
    `_retirar_detalle_servicio`      TO `detalle_servicio`,
    `_retirar_notificaciones_leidas` TO `notificaciones_leidas`,
    `_retirar_rol_permiso`           TO `rol_permiso`,
    `_retirar_usuario_especialidad`  TO `usuario_especialidad`,
    `_retirar_bkp_notificacion_old`  TO `_bkp_notificacion_old`;
