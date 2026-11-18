# Pruebas de la revisión 2 — Nexus FMAT

Issue: ISS-27 · Responsable: P3 (@Pao-Parra)

Pruebas manuales de la revisión 2, hechas como usuario con una instalación limpia y siguiendo esta lista. Cubren el ciclo completo: agendar, cambiar el estado de una sesión, calificarla, la tabla semanal y la administración. No repiten los 75 casos de la revisión 1, solo la regresión mínima. Cada falla se registra como un issue `tipo:bug` enlazado a ISS-27 y asignado al responsable del issue original.

## Datos de la prueba

| Dato | Valor |
|---|---|
| Fecha | 17/11/2026 |
| Computadora | La de P3 (@Pao-Parra), distinta a la de P4 |
| Rama / commit probado | `develop` @ `fc68677` (probado desde la rama `test/iss-27-pruebas-rev2`) |
| Navegadores | Google Chrome 157 · Firefox · Microsoft Edge |
| XAMPP / PHP / MariaDB | 8.2.12 / 8.2.12 / 10.4.32 |
| Instalación | Solo con el README, sin pedir ayuda |
| Aseguramiento | INS-01, AGE-06, EST-03, EST-05, TAB-01, ADM-08, RGR-02, VIS-01 y VIS-02 se replicaron el 17/11/2026 en una segunda computadora (la de P4), con una instalación nueva desde el README y en Google Chrome 157, con fines de aseguramiento: comprobar que dan el mismo resultado en distintas computadoras |

**Cuentas demo** (contraseña `Demo1234`):

| Rol | Correo | Datos útiles |
|---|---|---|
| Administrador | `ana.martinez@demo.uady.mx` | |
| Asesor | `luis.hernandez@demo.uady.mx` | Cálculo Diferencial lunes 09:00–11:00 · Cálculo Integral miércoles 14:00–16:00 |
| Asesor | `laura.ramirez@demo.uady.mx` | Álgebra Superior martes 10:00–12:00 · Álgebra Lineal jueves 15:00–17:00 |
| Asesor | `pedro.sanchez@demo.uady.mx` | Cálculo Diferencial lunes 13:00–15:00 · POO miércoles 11:00–13:00 · Programación Estructurada viernes 08:00–10:00 |
| Alumno | `juan.perez@demo.uady.mx` | Sesiones demo 4 (completada sin reseña), 6 (confirmada, ya pasó) y 7 (pendiente, 07/12) |
| Alumno | `maria.gonzalez@demo.uady.mx` | Sesión demo 8 (confirmada con Pedro, 09/12 11:00–12:00) |
| Alumno | `carlos.rodriguez@demo.uady.mx` | |

**Estados:** ✅ Pasa · ❌ Falla (con enlace al issue del bug) · ⏳ Pendiente

---

## 1. Instalación y pruebas automáticas

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| INS-01 | Base limpia y reimportación | Importar `schema.sql` y después `seed.sql` en phpMyAdmin. Volver a importar los dos encima | Sin errores las dos veces; `sesiones` tiene 8 filas y `resenas` 3 | schema.sql y seed.sql se importan sin errores; al reimportarlos encima tampoco hay errores y siguen 8 filas en sesiones, 3 en resenas y 7 tablas | ✅ |
| AUT-01 | Horarios | `C:\xampp\php\php.exe -d zend.assertions=1 tests/horario_check.php` | Termina sin errores | OK: horario_check pasó todos los casos | ✅ |
| AUT-02 | Estados | `C:\xampp\php\php.exe -d zend.assertions=1 tests/estados_check.php` | Termina sin errores | OK: estados_check pasó los 17 casos | ✅ |

## 2. Agendar (ISS-18)

Se usa el lunes **14/12/2026**, que no tiene sesiones demo, con el bloque de Luis de Cálculo Diferencial (09:00–11:00).

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| AGE-01 | Dentro del bloque | Como Juan, agendar con Luis Cálculo Diferencial el 14/12 de 09:00 a 10:00 | Se crea en `pendiente` y lleva a "Mis sesiones" con un aviso | Se crea la sesión en pendiente y lleva a Mis sesiones con aviso | ✅ |
| AGE-02 | Fuera del bloque | Como Juan, intentar 08:30–09:30 y 10:30–11:30 el 14/12 | Los dos se rechazan: la sesión debe quedar dentro del bloque | Los dos horarios fuera del bloque se rechazan | ✅ |
| AGE-03 | Empalme del asesor | Como María, intentar con Luis el 14/12 de 09:30 a 10:30 | Rechazado: "El asesor ya tiene una sesión de 09:00 a 10:00", sin decir con quién | Rechazado por empalme con la sesión del asesor de 09:00 a 10:00 | ✅ |
| AGE-04 | Solo se tocan | Como María, agendar con Luis el 14/12 de 10:00 a 11:00 | Se acepta | Se acepta porque solo se toca con la sesión de 09:00 a 10:00 | ✅ |
| AGE-05 | Empalme del alumno | Antes: como Luis, crear un bloque de Cálculo Integral el lunes 13:00–15:00. Como Juan, agendar con Pedro Cálculo Diferencial el 14/12 de 13:00 a 14:00 y después con Luis Cálculo Integral a la misma hora | La segunda se rechaza: "Ya tienes Cálculo Diferencial de 13:00 a 14:00" | Con la sesión de Juan con Pedro de 13:00 a 14:00, la de Luis a la misma hora se rechaza por empalme del alumno | ✅ |
| AGE-06 | Datos inválidos | Con F12 (quitando `min`, `max`, `step` y `required`), enviar: una fecha pasada, un domingo, `2026-02-30`, 06:30, 09:15, inicio igual al fin y un tema vacío | El servidor rechaza cada uno con un mensaje | Los siete se rechazan y no se crea ninguna sesión: fecha pasada "Esa fecha y hora ya pasaron."; domingo "No hay asesorías en domingo…"; `2026-02-30` "Elige una fecha válida."; 06:30 "El bloque debe quedar entre las 07:00 y las 21:00."; 09:15 "Las horas deben terminar en :00 o :30."; inicio igual al fin "La hora de inicio debe ser antes que la hora de fin."; tema vacío "Escribe el tema…" | ✅ |
| AGE-07 | Permisos | Abrir `agendar.php?materia=1&asesor=2` como Luis, como Ana y sin sesión | Luis y Ana reciben 403; sin sesión manda al login | Luis y Ana reciben 403; sin sesión manda al login | ✅ |
| AGE-08 | Sin JavaScript | Desactivar JavaScript y agendar como Carlos con Laura Álgebra Lineal el jueves 17/12 de 15:00 a 16:00 | La tabla semanal no aparece y el formulario funciona igual | Sin JavaScript la tabla no aparece y el formulario agenda la sesión | ✅ |

## 3. Estados de una sesión (ISS-19)

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| EST-01 | Confirmar | Como Luis, en "Solicitudes y sesiones", confirmar la sesión 7 (07/12 09:00). Luego verla como Juan | Luis la ve confirmada y Juan también, con su etiqueta azul | Luis confirma la sesión 7 y Juan la ve confirmada con la etiqueta azul | ✅ |
| EST-02 | Completar una pasada | Como Luis, completar la sesión 6 (28/10, Cálculo Integral) | Pasa a "Historial" como completada | Luis completa la sesión 6 y pasa al historial como completada | ✅ |
| EST-03 | Completar una futura | Como Pedro, intentar completar la sesión 8 (09/12) | No aparece el botón, y con F12 el servidor lo rechaza porque aún no termina | Pedro no ve el botón de completar en la sesión 8; al forzar el envío, el servidor responde "Solo puedes completar una sesión después de que termine su horario." y la sesión sigue confirmada | ✅ |
| EST-04 | Cancelar | Como Juan, cancelar la sesión de AGE-01 | Pide confirmación; la fila sigue en `sesiones` como `cancelada` y el 14/12 09:00 vuelve a estar disponible | Pide confirmación; queda cancelada en el historial y el horario vuelve a estar disponible | ✅ |
| EST-05 | Transiciones prohibidas | Con F12, como Juan intentar confirmar o completar una sesión, y como cualquiera cambiar una `completada` o `cancelada` | Todo se rechaza y nada cambia | OK: alumno confirma, alumno completa, cambiar una completada (sesión 1) y confirmar, completar o cancelar una cancelada (sesión 5) responden 403; las sesiones 1, 5 y 7 conservan su estado | ✅ |
| EST-06 | Sesión ajena | Como Carlos, cambiar con F12 el id del formulario por el de la sesión 8 (de María) | Responde 404 | OK: Carlos con sesion_id 8 recibió 404 | ✅ |
| EST-07 | Permisos y CSRF | Abrir `mis_sesiones.php` como Ana, y enviar un cambio de estado con el token alterado | Ana recibe 403; el token alterado se rechaza (400) | OK: Ana recibió 403 en mis_sesiones.php; el token alterado se rechazó con 400 y la sesión 7 no cambió | ✅ |

## 4. Reseñas (ISS-24)

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| RES-01 | Reseña válida | Como Juan, calificar con 5 la sesión 4 (con Pedro) | Se guarda, desaparece el botón "Calificar" y cambia el promedio de Pedro en el detalle de Cálculo Diferencial | OK: se guardó la reseña de 5 sin comentario, desapareció Calificar y el promedio de Pedro en Cálculo Diferencial pasó a 4.0 (antes 3.0, con una reseña de 3) | ✅ |
| RES-02 | Reseña repetida | Justo después, presionar "atrás" y volver a enviar, o abrir `resena.php?sesion=4` de nuevo | No crea otra reseña ni muestra error de PHP: "Esta sesión ya tiene reseña" | OK: al volver atrás y reenviar, o abrir resena.php?sesion=4, se muestra "Esta sesión ya tiene reseña" sin errores de PHP | ✅ |
| RES-03 | Sesión no completada | Como Juan, abrir `resena.php?sesion=7` | Aviso de que solo se califican sesiones completadas | OK: resena.php?sesion=7 muestra el aviso de que solo se califican sesiones completadas, sin formulario | ✅ |
| RES-04 | Sesión de otro alumno | Como Juan, abrir `resena.php?sesion=8` (de María) | Responde 404 | OK: resena.php?sesion=8 (de María) responde 404 | ✅ |
| RES-05 | Calificación inválida | Con F12, enviar una calificación de 0, de 6 y de `abc` en una sesión completada sin reseña | El servidor rechaza los tres | OK: calificación 0, 6 y abc se rechazaron con mensaje de error; no se guardó reseña y la sesión 4 conserva su botón Calificar | ✅ |
| RES-06 | Comentario | Calificar con el comentario `<script>alert(1)</script>`, y otra con el comentario vacío | El primero se ve como texto en el historial; el vacío se acepta | OK: el comentario `<script>alert(1)</script>` en la sesión 6 se ve como texto y no se ejecuta; el comentario vacío de RES-01 se aceptó | ✅ |

## 5. Tabla semanal (ISS-20 e ISS-21)

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| TAB-01 | Cuatro estados | Como Juan, abrir `agendar.php?materia=1&asesor=2` (Luis) y avanzar a la semana del 07/12. Después `agendar.php?materia=6&asesor=4` (Pedro, POO) en la misma semana | Luis: lunes 09:00–10:00 "Reservado" (u "Ocupado" si ya se confirmó en EST-01), 10:00–11:00 "Disponible", lo demás "Sin bloque". Pedro: miércoles 11:00–12:00 "Ocupado". Cada estado con su texto | OK: los cuatro estados. Luis, semana del 07/12: lunes 09:00–10:00 "Reservado" (sesión 7 pendiente; después de EST-01 sale "Ocupado"), 10:00–11:00 "Disponible", lo demás "Sin bloque". Pedro POO: miércoles 09/12 11:00–12:00 "Ocupado" | ✅ |
| TAB-02 | Cambio de semana | Usar "Semana anterior" y "Semana siguiente" | Cambia la tabla sin recargar la página | OK: "Semana anterior" y "Semana siguiente" cambian la tabla sin recargar la página | ✅ |
| TAB-03 | Elegir una celda | Tocar un horario disponible, escribir el tema y enviar. Volver a abrir la página | Se llenan fecha, inicio y fin (30 minutos después); se crea la sesión y la celda sale "Reservado" | OK: lunes 14/12 09:00 con Luis llenó fecha, inicio 09:00 y fin 09:30; se creó la sesión y al volver la celda sale "Reservado" | ✅ |
| TAB-04 | API detenida | Renombrar `public/api/horarios.php` y recargar la página de agendar. Al terminar, devolverle su nombre | Aparece "No se pudieron cargar los horarios…" y no hay errores sin manejar en la consola | OK: con horarios.php renombrado aparece "No se pudieron cargar los horarios…"; en consola solo el 404 de red, sin `Uncaught`. Se le regresó su nombre | ✅ |

## 6. Administración (ISS-22, ISS-23 e ISS-25)

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| ADM-01 | Crear materia | Como Ana, en "Materias" crear `MAT-999 · Topología General` | Aparece en el inicio con "Sin asesores aún" | OK: MAT-999 · Topología General se creó y aparece en el inicio con "Sin asesores aún" | ✅ |
| ADM-02 | Clave repetida | Crear otra con la clave `MAT101`, y editar `MAT-999` poniéndole `MAT101` | Los dos se rechazan con un mensaje junto a la clave. Editar sin cambiar la clave sí se guarda | OK: crear con MAT101 y editar MAT-999 a MAT101 se rechazan con el mensaje junto a la clave; editar solo el nombre sin cambiar la clave sí se guarda | ✅ |
| ADM-03 | Desactivar materia | Desactivar Cálculo Diferencial; abrir el inicio, `materia.php?id=1` y `agendar.php?materia=1&asesor=2`. Después activarla | Desaparece del inicio y las dos páginas responden 404. Al activarla regresan sus asesores, bloques y sesiones | OK: desactivada desaparece del inicio, materia.php?id=1 y agendar.php?materia=1&asesor=2 dan 404; al activarla regresan Luis y Pedro con sus horarios | ✅ |
| ADM-04 | Desactivar alumno | Con Juan con sesión abierta en otro navegador, Ana lo desactiva en "Usuarios" | En su siguiente clic Juan sale de la sesión, ya no puede entrar y sus sesiones siguen en la BD. Al activarlo, entra | OK: con Juan abierto en ventana InPrivate, al desactivarlo sale de la sesión en su siguiente clic y no puede entrar; al activarlo entra y conserva sus sesiones | ✅ |
| ADM-05 | Desactivar asesor | Desactivar a Pedro y abrir el detalle de Cálculo Diferencial y `agendar.php?materia=1&asesor=4` | Pedro desaparece del detalle y agendar responde 404. Los conteos del panel cambian | OK: Pedro desactivado desaparece del detalle de Cálculo Diferencial, agendar.php?materia=1&asesor=4 da 404 y Asesores baja de 3 a 2 en el panel. Se volvió a activar | ✅ |
| ADM-06 | Cuentas protegidas | Con F12, intentar desactivar a Ana y enviar `rol=admin` al editar a un alumno. Revisar el código fuente de la lista de usuarios | No cambia nada y el HTML no contiene ningún `$2y$` | OK: desactivar a Ana (id 1) responde 403 y sigue activa; con `rol=admin` agregado, Juan se guarda pero sigue como Alumno; el código fuente no contiene `$2y$` | ✅ |
| ADM-07 | Permisos | Abrir `admin_materias.php` y `admin_usuarios.php` como Juan, como Luis y sin sesión | Juan y Luis reciben 403; sin sesión manda al login | OK: Juan y Luis reciben 403 en admin_materias.php y admin_usuarios.php; sin sesión mandan al login | ✅ |
| ADM-08 | Materia con sesiones vivas | Como Luis, intentar quitar Cálculo Diferencial en "Mis materias" mientras existe la sesión 7. Luego cancelarla e intentar de nuevo | Primero no se quita y dice cuántas sesiones debe atender; después de cancelar, sí se quita | OK: con sesiones vivas no se quita ("tienes 2 sesiones pendientes o confirmadas…"); después de cancelarlas sí se quita, junto con sus bloques. Se volvió a agregar y se creó otra vez el bloque lunes 09:00–11:00 antes de RGR-02 | ✅ |

## 7. Regresión

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| RGR-01 | Inicio de sesión | Entrar con Ana, Luis y Juan | Cada uno llega a su panel con sus tarjetas (incluidas "Materias" y "Usuarios" para Ana, "Mis sesiones" para Juan y "Solicitudes y sesiones" para Luis) | OK: Ana ve "Materias" y "Usuarios", Luis "Solicitudes y sesiones" y Juan "Mis sesiones" | ✅ |
| RGR-02 | Empalme de bloques (DIS-02) | Como Luis, crear un bloque lunes 10:00–12:00 de Cálculo Diferencial | Rechazado: "Se empalma con Cálculo Diferencial, lunes 09:00–11:00" | OK: el bloque lunes 10:00–12:00 se rechaza con "Se empalma con Cálculo Diferencial, lunes 09:00–11:00" | ✅ |

## 8. Vista a 360 px y navegadores

En cada página: F12 → `Ctrl + Shift + M` → ancho 360.

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| VIS-01 | Pantallas nuevas a 360 px | Revisar agendar (con la tabla), mis sesiones, reseña, materias y usuarios | Sin scroll horizontal de la página; la tabla semanal se desliza dentro de su recuadro | OK en la primera prueba y en Chrome: a 360 px agendar, mis sesiones, reseña, materias y usuarios no tienen scroll horizontal de la página; la tabla semanal (640 px) se desliza dentro de su recuadro | ✅ |
| VIS-02 | Chrome y Firefox | Repetir AGE-01, EST-01 y TAB-01 en los dos navegadores | Igual que lo esperado | OK en Chrome y Firefox (y en Edge): agendar el 21/12 09:00–10:00 con Luis queda pendiente con aviso, Luis confirma la sesión 7 y Juan la ve con la etiqueta azul, y la tabla de la semana del 07/12 muestra el lunes 09:00 "Ocupado" | ✅ |

---

## Resumen

Todos los casos dieron el resultado esperado y no se encontraron bugs. Los casos replicados con fines de aseguramiento dieron el mismo resultado en las dos computadoras.

| Sección | Casos | ✅ | ❌ | ⏳ |
|---|---|---|---|---|
| 1. Instalación y automáticas | 3 | 3 | 0 | 0 |
| 2. Agendar | 8 | 8 | 0 | 0 |
| 3. Estados | 7 | 7 | 0 | 0 |
| 4. Reseñas | 6 | 6 | 0 | 0 |
| 5. Tabla semanal | 4 | 4 | 0 | 0 |
| 6. Administración | 8 | 8 | 0 | 0 |
| 7. Regresión | 2 | 2 | 0 | 0 |
| 8. Vista y navegadores | 2 | 2 | 0 | 0 |
| **Total** | **40** | **40** | **0** | **0** |

## Observaciones y mejoras

| Caso | Observación | Sugerencia |
|---|---|---|
| Aseguramiento | La réplica en la segunda computadora se hizo en Google Chrome manejado por un script local, que no forma parte del repositorio. Las únicas pruebas automáticas del proyecto siguen siendo `horario_check.php` y `estados_check.php` | Ninguna |
| ADM-08 | Quitar una materia borra los bloques del asesor por el `ON DELETE CASCADE`; volver a agregarla no los recupera. Si ADM-08 se hace antes de RGR-02 o VIS-02, hay que volver a crear el bloque lunes 09:00–11:00 | Es el comportamiento esperado: el aviso "Quitaste la materia y sus bloques de disponibilidad" lo dice |
| TAB-04 | Con la API detenida, la consola muestra la línea roja del 404 de red; es el aviso normal del navegador y no hay ningún error `Uncaught` | Ninguna |
| EST-05 · EST-06 · RES-05 · ADM-06 | Los envíos alterados se hicieron desde la consola, armando el formulario con los valores modificados | Ninguna |

## Bugs encontrados

| Issue | Caso | Descripción | Responsable | Estado |
|---|---|---|---|---|
| — | — | No se encontraron bugs en esta revisión | — | — |
