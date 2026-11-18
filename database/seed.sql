USE nexus_fmat;

INSERT INTO licenciaturas (nombre) VALUES
  ('Actuaría'),
  ('Ciencias de la Computación'),
  ('Enseñanza de las Matemáticas'),
  ('Ingeniería de Software'),
  ('Ingeniería en Computación'),
  ('Matemáticas');

INSERT INTO materias (clave, nombre) VALUES
  ('MAT101', 'Cálculo Diferencial'),
  ('MAT102', 'Cálculo Integral'),
  ('MAT103', 'Álgebra Superior'),
  ('MAT201', 'Álgebra Lineal'),
  ('PRG202', 'Programación Estructurada'),
  ('PRG203', 'Programación Orientada a Objetos'),
  ('PRG301', 'Estructuras de Datos'),
  ('MAT302', 'Matemáticas Discretas'),
  ('MAT303', 'Probabilidad'),
  ('MAT401', 'Geometría Analítica');

INSERT INTO usuarios (nombre, apellidos, correo, password_hash, rol, matricula, licenciatura_id, semestre) VALUES
  ('Ana', 'Martínez', 'ana.martinez@demo.uady.mx', '$2y$10$P2iGEP/i1uyOnn9enaYNFeoAl1G65EHf.Z7ADQJCN2uydDxAqQ8F2', 'admin', NULL, NULL, NULL),
  ('Luis', 'Hernández', 'luis.hernandez@demo.uady.mx', '$2y$10$P2iGEP/i1uyOnn9enaYNFeoAl1G65EHf.Z7ADQJCN2uydDxAqQ8F2', 'asesor', '22222222', 4, 6),
  ('Laura', 'Ramírez', 'laura.ramirez@demo.uady.mx', '$2y$10$P2iGEP/i1uyOnn9enaYNFeoAl1G65EHf.Z7ADQJCN2uydDxAqQ8F2', 'asesor', '33333333', 1, 9),
  ('Pedro', 'Sánchez', 'pedro.sanchez@demo.uady.mx', '$2y$10$P2iGEP/i1uyOnn9enaYNFeoAl1G65EHf.Z7ADQJCN2uydDxAqQ8F2', 'asesor', '44444444', 2, 7),
  ('Juan', 'Pérez', 'juan.perez@demo.uady.mx', '$2y$10$P2iGEP/i1uyOnn9enaYNFeoAl1G65EHf.Z7ADQJCN2uydDxAqQ8F2', 'alumno', '12345678', 1, 3),
  ('María', 'González', 'maria.gonzalez@demo.uady.mx', '$2y$10$P2iGEP/i1uyOnn9enaYNFeoAl1G65EHf.Z7ADQJCN2uydDxAqQ8F2', 'alumno', '98765432', 2, 4),
  ('Carlos', 'Rodríguez', 'carlos.rodriguez@demo.uady.mx', '$2y$10$P2iGEP/i1uyOnn9enaYNFeoAl1G65EHf.Z7ADQJCN2uydDxAqQ8F2', 'alumno', '11111111', 3, 2);

INSERT INTO asesor_materia (asesor_id, materia_id) VALUES
  (2, 1),
  (2, 2),
  (3, 3),
  (3, 4),
  (4, 5),
  (4, 1),
  (4, 6);

INSERT INTO disponibilidad (asesor_id,materia_id,dia_semana, hora_inicio, hora_fin,lugar) VALUES
  (2, 1, 1, '09:00:00', '11:00:00', 'Aula 101'),
  (2, 2, 3, '14:00:00', '16:00:00', 'Aula 102'),
  (3, 3, 2, '10:00:00', '12:00:00', 'Aula 201'),
  (3, 4, 4, '15:00:00', '17:00:00', 'Aula 202'),
  (4, 5, 5, '08:00:00', '10:00:00', 'Aula 301'),
  (4, 1, 1, '13:00:00', '15:00:00', 'Aula 302'),
  (4, 6, 3, '11:00:00', '13:00:00', 'Aula 303');


-- ====== ISS 16 Datos demo de sesiones y reseñas ======

-- Fechas fijas (supuesto 8): pasadas en octubre y futuras en diciembre 2026.
-- Cada sesión cae dentro de un bloque de 'disponibilidad' del mismo asesor correspondiente  y materia.
-- Los IDs de las sesiones salen en el orden del INSERT (1-8) porque schema.sql recrea la base. 

INSERT INTO sesiones (alumno_id, asesor_id, materia_id, fecha, hora_inicio, hora_fin, tema, estado) VALUES
  (5, 2, 1, '2026-10-19', '09:00:00', '10:00:00', 'Límites y continuidad', 'completada'),            -- 1: con reseña de 5
  (6, 3, 3, '2026-10-20', '10:00:00', '11:00:00', 'Inducción matemática', 'completada'),             -- 2: con reseña de 4
  (7, 4, 5, '2026-10-23', '08:00:00', '09:00:00', 'Ciclos y arreglos en C', 'completada'),           -- 3: con reseña de 3 sin comentario
  (5, 4, 1, '2026-10-26', '13:00:00', '14:00:00', 'Regla de la cadena', 'completada'),               -- 4: sin reseña, se califica en la demo
  (6, 2, 1, '2026-10-26', '09:00:00', '10:00:00', 'Derivadas implícitas', 'cancelada'),              -- 5: historial
  (5, 2, 2, '2026-10-28', '14:00:00', '15:00:00', 'Integración por partes', 'confirmada'),           -- 6: ya pasó, se completa en la demo
  (5, 2, 1, '2026-12-07', '09:00:00', '10:00:00', 'Repaso para el ordinario', 'pendiente'),          -- 7: se confirma en la demo
  (6, 4, 6, '2026-12-09', '11:00:00', '12:00:00', 'Herencia y polimorfismo', 'confirmada');          -- 8: "Ocupado" en la tabla

INSERT INTO resenas (sesion_id, calificacion, comentario) VALUES
  (1, 5, 'Excelente asesoría, muy clara y útil.'),
  (2, 4, 'Buena explicación, pero podría mejorar la claridad en algunos puntos.'),
  (3, 3, NULL); -- Sin comentario
  