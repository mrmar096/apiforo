-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.1.10-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win32
-- HeidiSQL Versión:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Volcando estructura de base de datos para apiforo
CREATE DATABASE IF NOT EXISTS `apiforo` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `apiforo`;


-- Volcando estructura para tabla apiforo.comentarios
CREATE TABLE IF NOT EXISTS `comentarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comentario` text NOT NULL,
  `usuario` int(11) NOT NULL,
  `tema` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario` (`usuario`),
  KEY `tema` (`tema`),
  CONSTRAINT `fkcomtema_temas` FOREIGN KEY (`tema`) REFERENCES `temas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkcomusuario_usuarios` FOREIGN KEY (`usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='En esta tabla almacenamos los comentarios que relacionan a un usuario y a un tema';

-- Volcando datos para la tabla apiforo.comentarios: ~0 rows (aproximadamente)
DELETE FROM `comentarios`;
/*!40000 ALTER TABLE `comentarios` DISABLE KEYS */;
INSERT INTO `comentarios` (`id`, `comentario`, `usuario`, `tema`) VALUES
	(1, 'Comienza el tema', 1, 1),
	(2, 'Comienza el tema', 1, 2);
/*!40000 ALTER TABLE `comentarios` ENABLE KEYS */;


-- Volcando estructura para tabla apiforo.temas
CREATE TABLE IF NOT EXISTS `temas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL DEFAULT '0',
  `detalle` text,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='En esta tabla almacenamos la variedad de temas que hay en el foro';

-- Volcando datos para la tabla apiforo.temas: ~0 rows (aproximadamente)
DELETE FROM `temas`;
/*!40000 ALTER TABLE `temas` DISABLE KEYS */;
INSERT INTO `temas` (`id`, `nombre`, `detalle`, `fecha`) VALUES
	(1, 'S2DAM', 'Vamos a hablar de RESTFUL', '2017-01-16 22:56:09'),
	(2, 'S2ASIR', 'Los ASIRES', '2017-01-16 22:56:09');
/*!40000 ALTER TABLE `temas` ENABLE KEYS */;


-- Volcando estructura para tabla apiforo.userapi
CREATE TABLE IF NOT EXISTS `userapi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `pass` varchar(250) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `key` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla apiforo.userapi: ~0 rows (aproximadamente)
DELETE FROM `userapi`;
/*!40000 ALTER TABLE `userapi` DISABLE KEYS */;
/*!40000 ALTER TABLE `userapi` ENABLE KEYS */;


-- Volcando estructura para tabla apiforo.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `pass` varchar(12) NOT NULL,
  `avatar` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='En esta tabla almacenamos los usuarios registrados en el foro';

-- Volcando datos para la tabla apiforo.usuarios: ~0 rows (aproximadamente)
DELETE FROM `usuarios`;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` (`id`, `username`, `nombre`, `email`, `pass`, `avatar`) VALUES
	(1, 's2dam', 's2dam', 's2dam@ies-azarquiel.es', '', '');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
