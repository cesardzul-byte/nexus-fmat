# Guion de la revisión 1 — Nexus FMAT

Issue: ISS-14 · Presentación: jue 29/10/2026 · Duración: unos 5 min (máximo 7)

Cada integrante presenta lo que construyó. El orden sigue el recorrido del sistema: el problema, los datos, lo que ve el visitante y lo que hace el asesor.

## Antes de presentar (el mismo día, en la computadora de la presentación)

1. Encender Apache y MySQL en XAMPP.
2. Reimportar en phpMyAdmin `database/schema.sql` y después `database/seed.sql`, para empezar con los datos demo limpios.
3. Correr `php -d zend.assertions=1 tests/horario_check.php` y confirmar que termina con `OK`.
4. Entrar y salir una vez con Ana, Luis y Juan.
5. Dejar abiertas estas pestañas: `docs/requisitos/documento_requisitos.pdf`, `docs/diagramas/diagrama_er.png`, phpMyAdmin con la base `nexus_fmat` y <http://localhost/nexus-fmat/public/> sin sesión.

Todas las cuentas demo usan la contraseña `Demo1234`.

## Reparto

| # | Quién | Tiempo | Tema | Cuenta |
|---|---|---|---|---|
| 1 | P2 (@oswaldo-castillo) | 0:00–1:15 | Problema y requisitos | — |
| 2 | P1 (@Luis-villanueva-valdivia) | 1:15–2:30 | Base de datos y ER | phpMyAdmin (`root`) |
| 3 | P3 (@Pao-Parra) | 2:30–3:30 | Interfaz e index | Sin sesión |
| 4 | P4 (@cesardzul-byte) | 3:30–5:00 | Inicio de sesión y disponibilidad con empalme | `juan.perez@demo.uady.mx`, `luis.hernandez@demo.uady.mx` |

## 1. Problema y requisitos (P2)

En `documento_requisitos.pdf`:

- **Problema:** las asesorías entre estudiantes se organizan de manera informal, y eso provoca choques de horario, sesiones sin registro, poca retroalimentación y una facultad que no sabe en qué materias se necesita más apoyo.
- **Roles:** alumno, asesor y administrador. Cada usuario tiene un solo rol.
- **Lo que cubre esta revisión:** RF-01 (crear cuenta), RF-02 y RF-03 (iniciar y cerrar sesión), RF-04 (consultar materias), RF-06 (publicar disponibilidad) y las reglas RN-03 (horario permitido) y RN-04 (empalmes).
- **Lo que sigue:** agendar, gestionar sesiones, reseñas, administración y estadísticas (RF-05 y de RF-07 a RF-17).

## 2. Base de datos y ER (P1)

En `diagrama_er.png` y en phpMyAdmin:

- Siete tablas: `licenciaturas`, `materias`, `usuarios`, `asesor_materia`, `disponibilidad`, `sesiones` y `resenas`. Las dos últimas ya existen para la revisión 2.
- `asesor_materia` resuelve la relación N:M entre asesores y materias. Cada bloque de `disponibilidad` pertenece a un asesor y a una materia que imparte.
- En phpMyAdmin se muestran los datos demo (7 usuarios y 10 materias) y se señala que `password_hash` empieza con `$2y$`, es decir, que la contraseña nunca se guarda en texto.
- Restricciones: el correo y la matrícula son únicos, y al quitarle una materia a un asesor se borran sus bloques en cascada.

## 3. Interfaz e index (P3)

En <http://localhost/nexus-fmat/public/>, sin sesión:

- El inicio muestra 10 tarjetas con el número de asesores de cada materia.
- Al escribir `algebra` en el filtro quedan solo Álgebra Lineal y Álgebra Superior, sin recargar y sin importar las tildes.
- "Ver asesores" de Cálculo Diferencial muestra a Luis y a Pedro con su licenciatura y sus bloques.
- Con F12 a 360 px no hay scroll horizontal y funciona el botón "Menú".

## 4. Inicio de sesión y disponibilidad (P4)

1. Entrar con `juan.perez@demo.uady.mx` y la contraseña `otra1234`. Aparece "Correo o contraseña incorrectos", sin decir cuál de los dos falló, para que nadie pueda averiguar qué correos tienen cuenta.
2. Entrar con `luis.hernandez@demo.uady.mx` y `Demo1234`. Llega al panel de asesor, con las tarjetas "Mis materias" y "Mi disponibilidad".
3. **Empalme en vivo:** en "Mi disponibilidad", crear el bloque lunes 10:00–12:00 de Cálculo Diferencial. Se rechaza con "Se empalma con Cálculo Diferencial, lunes 09:00–11:00".
4. Crear el bloque lunes 11:00–13:00. Se acepta, porque solo toca el bloque anterior sin empalmarse.
5. Abrir el detalle de Cálculo Diferencial, donde ya aparece el bloque nuevo. Cerrar sesión.

Si la app falla durante la demo, P4 corre `tests/horario_check.php` para mostrar que la regla de empalmes funciona.

## Pendientes que pasan a la revisión 2

| Pendiente | Origen | Dónde se atiende |
|---|---|---|
| Fusionar `develop` a `main` y crear la etiqueta `v0.1.0` | ISS-14 | ISS-14 (P4), antes de ISS-15 |
| Tabla semanal de horarios: `materia.php` solo lista bloques | RF-05 | ISS-20 e ISS-21 |
| RF-01 dice que el rol de asesor lo asigna una cuenta autorizada, pero el registro deja elegirlo | ISS-08 | ISS-26 corrige el documento |
| El diagrama ER tiene fondo oscuro y no se lee impreso | Revisión del ER | ISS-26 |
| Agendar, mis sesiones, reseñas y administración de materias y usuarios | Alcance tentativo de la planeación 1 | ISS-18 a ISS-24 |
| Crear los milestones y los labels que faltan | Tablero | P4, antes de ISS-15 |
| Estilos de los errores y botones de login y registro (B-01, #31) | ISS-13 | Corregido en #32 |
| Filtro tolerante a errores de dedo, borrar el error al corregir el campo, número de sesiones por bloque | Observaciones de ISS-13 | No se hacen (`limitaciones-mvp.md`, §4) |
