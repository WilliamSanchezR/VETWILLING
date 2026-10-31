-- Tokens de recuperación de contraseña (un solo uso, con vencimiento).
-- Solo se guarda el hash SHA-256 del token; el token real viaja únicamente en el correo.
CREATE TABLE IF NOT EXISTS `password_reset` (
  `id_reset` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_general_ci NOT NULL,
  `expira` datetime NOT NULL,
  `usado_en` datetime DEFAULT NULL,
  `ip_solicitud` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reset`),
  UNIQUE KEY `uk_password_reset_token` (`token_hash`),
  KEY `idx_password_reset_usuario` (`id_usuario`, `fecha_creacion`),
  KEY `idx_password_reset_ip` (`ip_solicitud`, `fecha_creacion`),
  CONSTRAINT `fk_password_reset_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
