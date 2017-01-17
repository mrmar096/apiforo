-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.1.16-MariaDB - mariadb.org binary distribution
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
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario` (`usuario`),
  KEY `tema` (`tema`),
  CONSTRAINT `fkcomtema_temas` FOREIGN KEY (`tema`) REFERENCES `temas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fkcomusuario_usuarios` FOREIGN KEY (`usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='En esta tabla almacenamos los comentarios que relacionan a un usuario y a un tema';

-- La exportación de datos fue deseleccionada.


-- Volcando estructura para tabla apiforo.temas
CREATE TABLE IF NOT EXISTS `temas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL DEFAULT '0',
  `detalle` text,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `autor` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `autor` (`autor`),
  CONSTRAINT `fkautor_usuarios` FOREIGN KEY (`autor`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='En esta tabla almacenamos la variedad de temas que hay en el foro';

-- La exportación de datos fue deseleccionada.


-- Volcando estructura para tabla apiforo.userapi
CREATE TABLE IF NOT EXISTS `userapi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `pass` varchar(250) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `key` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.


-- Volcando estructura para tabla apiforo.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `pass` varchar(250) NOT NULL,
  `avatar` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='En esta tabla almacenamos los usuarios registrados en el foro';

-- La exportación de datos fue deseleccionada.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
