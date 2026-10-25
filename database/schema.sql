DROP DATABASE IF EXISTS nexus_fmat;
CREATE DATABASE nexus_fmat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nexus_fmat;

CREATE TABLE licenciaturas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_licenciaturas_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE materias (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  clave VARCHAR(50) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  activa TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_materias_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

