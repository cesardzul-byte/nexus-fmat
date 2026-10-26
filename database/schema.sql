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

CREATE TABLE asesor_materia (
  asesor_id INT UNSIGNED NOT NULL,
  materia_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (asesor_id, materia_id),
  CONSTRAINT fk_asesor_materia_asesor
   FOREIGN KEY (asesor_id)
   REFERENCES usuarios (id)
   ON DELETE RESTRICT,
  CONSTRAINT fk_asesor_materia_materia
   FOREIGN KEY (materia_id)
   REFERENCES materias (id)
   ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE disponibilidad (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  asesor_id INT UNSIGNED NOT NULL,
  materia_id INT UNSIGNED NOT NULL,
  dia_semana TINYINT UNSIGNED NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fin TIME NOT NULL,
  lugar VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_disponibilidad_asesor_materia
   FOREIGN KEY (asesor_id, materia_id)
   REFERENCES asesor_materia (asesor_id, materia_id)
   ON DELETE CASCADE,
  CONSTRAINT chk_disponibilidad_hora CHECK (hora_inicio < hora_fin),
  CONSTRAINT chk_disponibilidad_dia CHECK (dia_semana BETWEEN 1 AND 6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sesiones (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  alumno_id INT UNSIGNED NOT NULL,
  asesor_id INT UNSIGNED NOT NULL,
  materia_id INT UNSIGNED NOT NULL,
  fecha DATE NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fin TIME NOT NULL,
  tema VARCHAR(150) NOT NULL,
  estado ENUM('pendiente', 'confirmada', 'completada', 'cancelada') NOT NULL DEFAULT 'pendiente',
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_sesiones_alumno
   FOREIGN KEY (alumno_id)
   REFERENCES usuarios (id)
   ON DELETE RESTRICT,
  CONSTRAINT fk_sesiones_asesor
   FOREIGN KEY (asesor_id)
   REFERENCES usuarios (id)
   ON DELETE RESTRICT,
  CONSTRAINT fk_sesiones_materia
   FOREIGN KEY (materia_id)
   REFERENCES materias (id)
   ON DELETE RESTRICT,
  CONSTRAINT chk_sesiones_hora CHECK (hora_inicio < hora_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE resenas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  sesion_id INT UNSIGNED NOT NULL,
  calificacion TINYINT UNSIGNED NOT NULL,
  comentario TEXT DEFAULT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_resenas_sesion (sesion_id),
  CONSTRAINT fk_resenas_sesion
   FOREIGN KEY (sesion_id)
   REFERENCES sesiones (id)
   ON DELETE RESTRICT,
  CONSTRAINT chk_resenas_calificacion CHECK (calificacion BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;