-- Retira tablas sin uso cambiándoles el nombre. No borra datos.
-- Para deshacerlo: database/retirar_tablas_sin_uso_rollback.sql
RENAME TABLE
    `detalle_servicio`      TO `_retirar_detalle_servicio`,
    `notificaciones_leidas` TO `_retirar_notificaciones_leidas`,
    `rol_permiso`           TO `_retirar_rol_permiso`,
    `usuario_especialidad`  TO `_retirar_usuario_especialidad`,
    `_bkp_notificacion_old` TO `_retirar_bkp_notificacion_old`;
