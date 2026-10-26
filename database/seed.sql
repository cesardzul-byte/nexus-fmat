USE nexus_fmat;

INSERT INTO licenciaturas (nombre) VALUES
  ('Actuaría'),
  ('Ciencias de la Computación'),
  ('Enseñanza de las Matemáticas'),
  ('Ingeniería en Software'),
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
