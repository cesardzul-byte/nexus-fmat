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

CREATE TABLE usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  correo VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('alumno', 'asesor', 'admin') NOT NULL,
  matricula CHAR(8) DEFAULT NULL,
  licenciatura_id INT UNSIGNED DEFAULT NULL,
  semestre TINYINT UNSIGNED DEFAULT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_correo (correo),
  UNIQUE KEY uq_usuarios_matricula (matricula),
  CONSTRAINT fk_usuarios_licenciatura
   FOREIGN KEY (licenciatura_id)
   REFERENCES licenciaturas (id)
   ON DELETE RESTRICT,
  CONSTRAINT chk_usuarios_semestre CHECK (semestre BETWEEN 1 AND 9),
  CONSTRAINT chk_usuarios_matricula CHECK (rol = 'admin' OR matricula IS NOT NULL),
  CONSTRAINT chk_usuarios_licenciatura CHECK (rol = 'admin' OR licenciatura_id IS NOT NULL),
  CONSTRAINT chk_usuarios_semestre_rol CHECK (rol = 'admin' OR semestre IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
