-- Tarjetas del equipo que se muestran en el directorio. Cada administrador edita solo la suya.
-- Para deshacerlo: database/equipo_sistema_rollback.sql
CREATE TABLE IF NOT EXISTS `equipo_sistema` (
  `id_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `rol_equipo` varchar(120) NOT NULL,
  `habilidades` varchar(500) DEFAULT NULL,
  `foto` varchar(250) DEFAULT NULL,
  `github` varchar(250) DEFAULT NULL,
  `linkedin` varchar(250) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 100,
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_equipo`),
  UNIQUE KEY `uq_equipo_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Mismos datos de la landing page, enlazados a la cuenta de cada administrador
INSERT IGNORE INTO `equipo_sistema` (`id_usuario`,`nombre`,`rol_equipo`,`habilidades`,`foto`,`github`,`linkedin`,`correo`,`orden`)
SELECT `id_usuario`,'Brayan Andrade','Backend · Frontend','React, Vue.js, JavaScript, 3+ años','public/assets/webSite/img/brian.webp','https://github.com/brayandrad3','https://www.linkedin.com/in/brayan-andrad3/','brayandrade33@gmail.com',1 FROM `usuario` WHERE `email`='brayandrade33@gmail.com' AND `id_rol`=1;
INSERT IGNORE INTO `equipo_sistema` (`id_usuario`,`nombre`,`rol_equipo`,`habilidades`,`foto`,`github`,`linkedin`,`correo`,`orden`)
SELECT `id_usuario`,'Adriana Castillo','Backend · Frontend','PHP, Laravel, MySQL, REST API','public/assets/webSite/img/adri.webp','https://github.com/adricas35','https://www.linkedin.com/in/adriana-castillo-05aa10368/','adriana@vetwilling.com',2 FROM `usuario` WHERE `email`='adricasra1993@gmail.com' AND `id_rol`=1;
INSERT IGNORE INTO `equipo_sistema` (`id_usuario`,`nombre`,`rol_equipo`,`habilidades`,`foto`,`github`,`linkedin`,`correo`,`orden`)
SELECT `id_usuario`,'Maicol Bernal','Backend Developer','Node.js, Python, MySQL, GraphQL','public/assets/webSite/img/maic.webp','https://github.com/MaicBernal11','https://www.linkedin.com/in/maicol-bernal-62138641b','maicol@vetwilling.com',3 FROM `usuario` WHERE `email`='bernalmaicolw@gmail.com' AND `id_rol`=1;
INSERT IGNORE INTO `equipo_sistema` (`id_usuario`,`nombre`,`rol_equipo`,`habilidades`,`foto`,`github`,`linkedin`,`correo`,`orden`)
SELECT `id_usuario`,'Yair Vergara','Full Stack Developer','React, Node.js, Docker, CI/CD','public/assets/webSite/img/yahir.webp','https://github.com/yairV1','https://www.linkedin.com/in/yair-vergara-163043309/','yair@vetwilling.com',4 FROM `usuario` WHERE `email`='yandrey2007@gmail.com' AND `id_rol`=1;
INSERT IGNORE INTO `equipo_sistema` (`id_usuario`,`nombre`,`rol_equipo`,`habilidades`,`foto`,`github`,`linkedin`,`correo`,`orden`)
SELECT `id_usuario`,'William Sánchez','Project Manager','Scrum, Liderazgo, Planeación, 7+ años','public/assets/webSite/img/wiii.webp','https://github.com/WilliamSanchezR','https://www.linkedin.com/in/william-andres-sanchez-rojas-1a3096264/','william@vetwilling.com',5 FROM `usuario` WHERE `email`='sanchezwill596@gmail.com' AND `id_rol`=1;
