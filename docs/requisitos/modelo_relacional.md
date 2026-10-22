# Modelo relacional — Nexus FMAT

Base de datos `nexus_fmat` (MariaDB 10.4 / MySQL, motor InnoDB, `utf8mb4`). Tiene 7 tablas. El diagrama entidad-relación está en [`docs/diagramas/diagrama_er.png`](../diagramas/diagrama_er.png) y su fuente editable en `docs/diagramas/diagrama_er.drawio`.

Responsable: P1 (@Luis-villanueva-valdivia) · ISS-04

---

## 1. Resumen de tablas

| Tabla | Propósito |
|---|---|
| `licenciaturas` | Catálogo de carreras de la FMAT |
| `usuarios` | Cuentas de alumnos, asesores y administradores |
| `materias` | Catálogo de materias en las que se ofrecen asesorías |
| `asesor_materia` | Qué materias imparte cada asesor (tabla intermedia N:M) |
| `disponibilidad` | Bloques semanales en los que un asesor da una materia |
| `sesiones` | Asesorías agendadas por un alumno en una fecha concreta |
| `resenas` | Calificación que el alumno da a una sesión completada |

## 2. Relaciones y cardinalidades

| Relación | Cardinalidad | FK | Lectura |
|---|---|---|---|
| `licenciaturas` – `usuarios` | 0..1 : N | `usuarios.licenciatura_id` | Una licenciatura tiene muchos usuarios; un usuario tiene cero o una licenciatura (el admin no tiene) |
| `usuarios` – `materias` | N : M | `asesor_materia` | Un asesor imparte muchas materias y una materia la imparten muchos asesores |
| `asesor_materia` – `disponibilidad` | 1 : N | `disponibilidad.(asesor_id, materia_id)` | Una materia de un asesor tiene muchos bloques de horario |
| `usuarios` (alumno) – `sesiones` | 1 : N | `sesiones.alumno_id` | Un alumno agenda muchas sesiones; cada sesión es de un alumno |
| `usuarios` (asesor) – `sesiones` | 1 : N | `sesiones.asesor_id` | Un asesor imparte muchas sesiones; cada sesión es de un asesor |
| `materias` – `sesiones` | 1 : N | `sesiones.materia_id` | Una materia tiene muchas sesiones; cada sesión es de una materia |
| `sesiones` – `resenas` | 1 : 0..1 | `resenas.sesion_id` (UNIQUE) | Una sesión tiene cero o una reseña; cada reseña es de exactamente una sesión |

La relación N:M entre alumnos y asesores no necesita tabla propia: `sesiones` ya une a un alumno con un asesor y además guarda datos propios (fecha, horario, tema y estado).

---

## 3. Tablas

En todas las tablas: `ENGINE=InnoDB`, `DEFAULT CHARSET=utf8mb4`. Las columnas son `NOT NULL` salvo las marcadas con "Sí" en la columna *Nulo*.

### 3.1 `licenciaturas`

Catálogo de carreras. Es una tabla (y no un `ENUM` en `usuarios`) porque el administrador puede agregar o editar carreras desde la aplicación (revisión 2) sin cambiar la estructura de la BD.

| Columna | Tipo | Nulo | Llave | Restricciones | Descripción |
|---|---|---|---|---|---|
| `id` | INT UNSIGNED | No | PK | AUTO_INCREMENT | Identificador |
| `nombre` | VARCHAR(100) | No | | UNIQUE | Nombre de la licenciatura |

### 3.2 `usuarios`

Alumnos, asesores y administradores en una sola tabla, porque comparten los mismos atributos y el mismo inicio de sesión; se distinguen por `rol`.

| Columna | Tipo | Nulo | Llave | Restricciones | Descripción |
|---|---|---|---|---|---|
| `id` | INT UNSIGNED | No | PK | AUTO_INCREMENT | Identificador |
| `nombre` | VARCHAR(100) | No | | | Nombre(s) |
| `apellidos` | VARCHAR(100) | No | | | Apellidos |
| `correo` | VARCHAR(150) | No | | UNIQUE | Correo institucional; se usa para iniciar sesión |
| `password_hash` | VARCHAR(255) | No | | | Hash de `password_hash()`; nunca la contraseña en texto plano |
| `rol` | ENUM('alumno','asesor','admin') | No | | | Rol único del usuario |
| `matricula` | CHAR(8) | Sí | | UNIQUE | Matrícula UADY; es texto porque no se opera con ella. NULL solo para el admin |
| `licenciatura_id` | INT UNSIGNED | Sí | FK → `licenciaturas.id` | ON DELETE RESTRICT | Carrera; NULL solo para el admin |
| `semestre` | TINYINT UNSIGNED | Sí | | CHECK 1–9 | Semestre actual; NULL solo para el admin |
| `activo` | TINYINT(1) | No | | DEFAULT 1 | 1 = activo, 0 = desactivado |
| `creado_en` | DATETIME | No | | DEFAULT CURRENT_TIMESTAMP | Fecha y hora del registro |

**Restricciones de tabla:**

- `CHECK (rol = 'admin' OR matricula IS NOT NULL)`
- `CHECK (rol = 'admin' OR licenciatura_id IS NOT NULL)`
- `CHECK (rol = 'admin' OR semestre IS NOT NULL)`
- `CHECK (semestre BETWEEN 1 AND 9)`

`matricula`, `licenciatura_id` y `semestre` aceptan NULL porque el administrador no es estudiante, pero los tres `CHECK` con `rol` impiden que un alumno o asesor quede sin esos datos. `UNIQUE` permite varios NULL, así que puede haber más de un admin sin matrícula. El semestre llega como máximo a 9 porque en la UADY no avanza hasta aprobar: un alumno irregular repite el mismo semestre, no pasa al décimo.

### 3.3 `materias`

| Columna | Tipo | Nulo | Llave | Restricciones | Descripción |
|---|---|---|---|---|---|
| `id` | INT UNSIGNED | No | PK | AUTO_INCREMENT | Identificador |
| `clave` | VARCHAR(50) | No | | UNIQUE | Clave de la materia en el plan de estudios; es texto porque no se opera con ella |
| `nombre` | VARCHAR(100) | No | | | Nombre de la materia |
| `activa` | TINYINT(1) | No | | DEFAULT 1 | 1 = activa, 0 = desactivada |

### 3.4 `asesor_materia`

Tabla intermedia de la relación N:M entre asesores y materias.

| Columna | Tipo | Nulo | Llave | Restricciones | Descripción |
|---|---|---|---|---|---|
| `asesor_id` | INT UNSIGNED | No | PK, FK → `usuarios.id` | ON DELETE RESTRICT | Asesor |
| `materia_id` | INT UNSIGNED | No | PK, FK → `materias.id` | ON DELETE RESTRICT | Materia que imparte |

**Restricciones de tabla:**

- `PRIMARY KEY (asesor_id, materia_id)`: PK compuesta; la misma pareja no puede repetirse, así que un asesor no puede registrar dos veces la misma materia. No necesita columna `id`.

### 3.5 `disponibilidad`

Bloques semanales que publica cada asesor. Se guarda el día de la semana y no una fecha porque el bloque se repite cada semana ("los lunes de 10:00 a 12:00" es una sola fila). La fecha concreta va en `sesiones`, cuando el alumno agenda.

| Columna | Tipo | Nulo | Llave | Restricciones | Descripción |
|---|---|---|---|---|---|
| `id` | INT UNSIGNED | No | PK | AUTO_INCREMENT | Identificador |
| `asesor_id` | INT UNSIGNED | No | FK (compuesta) | | Asesor del bloque |
| `materia_id` | INT UNSIGNED | No | FK (compuesta) | | Materia del bloque |
| `dia_semana` | TINYINT UNSIGNED | No | | CHECK 1–6 | 1 = lunes … 6 = sábado (igual que `date('N')` en PHP) |
| `hora_inicio` | TIME | No | | | Hora de inicio |
| `hora_fin` | TIME | No | | | Hora de fin |
| `lugar` | VARCHAR(100) | No | | | Dónde se da la asesoría |

**Restricciones de tabla:**

- `FOREIGN KEY (asesor_id, materia_id) REFERENCES asesor_materia (asesor_id, materia_id) ON DELETE CASCADE`
- `CHECK (dia_semana BETWEEN 1 AND 6)`
- `CHECK (hora_inicio < hora_fin)`

La FK compuesta apunta a la pareja de `asesor_materia` y no a `usuarios` y `materias` por separado. Así la BD garantiza que un asesor solo publique horarios de materias que imparte.

### 3.6 `sesiones`

| Columna | Tipo | Nulo | Llave | Restricciones | Descripción |
|---|---|---|---|---|---|
| `id` | INT UNSIGNED | No | PK | AUTO_INCREMENT | Identificador |
| `alumno_id` | INT UNSIGNED | No | FK → `usuarios.id` | ON DELETE RESTRICT | Alumno que agenda |
| `asesor_id` | INT UNSIGNED | No | FK → `usuarios.id` | ON DELETE RESTRICT | Asesor que imparte |
| `materia_id` | INT UNSIGNED | No | FK → `materias.id` | ON DELETE RESTRICT | Materia de la sesión |
| `fecha` | DATE | No | | | Día concreto de la sesión |
| `hora_inicio` | TIME | No | | | Hora de inicio |
| `hora_fin` | TIME | No | | | Hora de fin |
| `tema` | VARCHAR(150) | No | | | Qué quiere repasar el alumno; obligatorio para que el asesor sepa qué preparar |
| `estado` | ENUM('pendiente','confirmada','completada','cancelada') | No | | DEFAULT 'pendiente' | Estado de la sesión |
| `creado_en` | DATETIME | No | | DEFAULT CURRENT_TIMESTAMP | Cuándo se agendó |

**Restricciones de tabla:**

- `CHECK (hora_inicio < hora_fin)`

### 3.7 `resenas`

| Columna | Tipo | Nulo | Llave | Restricciones | Descripción |
|---|---|---|---|---|---|
| `id` | INT UNSIGNED | No | PK | AUTO_INCREMENT | Identificador |
| `sesion_id` | INT UNSIGNED | No | FK → `sesiones.id` | UNIQUE, ON DELETE RESTRICT | Sesión calificada; UNIQUE = una reseña por sesión |
| `calificacion` | TINYINT UNSIGNED | No | | CHECK 1–5 | Calificación de 1 a 5 |
| `comentario` | TEXT | Sí | | | Opcional: se puede calificar sin comentar |
| `creado_en` | DATETIME | No | | DEFAULT CURRENT_TIMESTAMP | Fecha y hora de la reseña |

**Restricciones de tabla:**

- `CHECK (calificacion BETWEEN 1 AND 5)`

La FK va en `resenas` (y no en `sesiones`) porque una sesión puede existir sin reseña, pero una reseña no puede existir sin sesión.

---

## 4. Llaves foráneas y `ON DELETE`

| FK | Apunta a | ON DELETE | Razón |
|---|---|---|---|
| `usuarios.licenciatura_id` | `licenciaturas.id` | RESTRICT | No se borra una carrera que tiene usuarios |
| `asesor_materia.asesor_id` | `usuarios.id` | RESTRICT | Los usuarios se desactivan, no se borran |
| `asesor_materia.materia_id` | `materias.id` | RESTRICT | Las materias se desactivan, no se borran |
| `disponibilidad.(asesor_id, materia_id)` | `asesor_materia` | CASCADE | Quitar una materia es una acción normal del asesor y sus horarios ya no sirven |
| `sesiones.alumno_id` | `usuarios.id` | RESTRICT | Las sesiones son historia |
| `sesiones.asesor_id` | `usuarios.id` | RESTRICT | Las sesiones son historia |
| `sesiones.materia_id` | `materias.id` | RESTRICT | Las sesiones son historia |
| `resenas.sesion_id` | `sesiones.id` | RESTRICT | Las reseñas alimentan el promedio de los asesores y las estadísticas |

Criterio: `CASCADE` solo donde borrar al padre es parte normal de la aplicación y los hijos no tienen sentido sin él. En los demás casos, borrar al padre sería un error, y `RESTRICT` lo frena en lugar de borrar datos en silencio.

---

## 5. Decisiones de diseño

1. **Desactivar en vez de borrar usuarios y materias.** Un usuario o una materia tienen sesiones y reseñas que forman la historia del sistema y alimentan las estadísticas. Si se borraran, esa historia se perdería o quedaría huérfana. Por eso tienen la columna `activo` / `activa` y sus FK usan `ON DELETE RESTRICT`.
2. **Disponibilidad semanal.** Los asesores dan asesoría en un horario que se repite cada semana. Guardar `dia_semana` (1–6) permite registrar "los lunes de 10:00 a 12:00" en una sola fila en lugar de una fila por cada lunes del semestre. La fecha concreta se guarda en `sesiones`.
3. **`sesiones` guarda asesor y materia en lugar de apuntar a un bloque de disponibilidad.** Si el asesor cambia o quita sus bloques, las sesiones ya agendadas se conservan con su fecha y su horario. Además, quitar una materia borra sus bloques en cascada; si las sesiones dependieran de los bloques, esa cascada alcanzaría a la historia.

## 6. Reglas que se validan en PHP

La BD no puede garantizar estas reglas con restricciones, así que se validan en el código:

| Regla | Dónde |
|---|---|
| `asesor_materia.asesor_id` y `sesiones.asesor_id` deben ser usuarios con rol `asesor`, y `sesiones.alumno_id` uno con rol `alumno` (un `CHECK` no puede consultar otra tabla) | ISS-11, revisión 2 |
| Dos bloques de disponibilidad del mismo asesor y el mismo día no se empalman (`inicio < otro_fin` y `fin > otro_inicio`); MySQL no tiene restricciones de exclusión por rangos de tiempo | ISS-12 (`seEmpalman()`) |
| Horario entre 07:00 y 21:00 en múltiplos de 30 minutos | ISS-12 (`validarBloque()`) |
| Una sesión cae dentro de un bloque del asesor y no se empalma con otras sesiones del asesor ni del alumno en esa fecha | Revisión 2 |
| Solo se reseñan sesiones con estado `completada` | Revisión 2 |
