# Guion de la revisión 2 — Nexus FMAT

Issue: ISS-28 · Presentación: jue 12/11/2026 · Duración: unos 5 min (máximo 7)

Cada integrante presenta lo que construyó. El orden sigue el ciclo del sistema: los datos, el asesor atiende, el alumno elige un horario y agenda sin empalmes.

## Antes de presentar (el mismo día, en la computadora de la presentación)

La demo cambia los datos, así que esto se hace el mismo día y **otra vez después de cualquier ensayo**.

1. Encender Apache y MySQL en XAMPP.
2. Reimportar en phpMyAdmin `database/schema.sql` y después `database/seed.sql`.
3. Correr `php -d zend.assertions=1 tests/horario_check.php` y `php -d zend.assertions=1 tests/estados_check.php`, y confirmar que los dos terminan con `OK`.
4. Abrir tres ventanas, cada una con su sesión iniciada en <http://localhost/nexus-fmat/public/>:
   - **Ventana 1** (Chrome normal): Juan, `juan.perez@demo.uady.mx`.
   - **Ventana 2** (Chrome incógnito): Luis, `luis.hernandez@demo.uady.mx`.
   - **Ventana 3** (Firefox o Edge): Ana, `ana.martinez@demo.uady.mx`.
5. Dejar abierto `docs/diagramas/diagrama_er.png`.

Todas las cuentas demo usan la contraseña `Demo1234`.

## Reparto

| # | Quién | Tiempo | Tema | Cuenta |
|---|---|---|---|---|
| 1 | P1 (@Luis-villanueva-valdivia) | 0:00–1:15 | Diagrama ER final y reseñas | Juan (ventana 1) |
| 2 | P2 (@oswaldo-castillo) | 1:15–2:30 | Solicitudes y sesiones, administración de usuarios | Luis (ventana 2), Ana (ventana 3) |
| 3 | P3 (@Pao-Parra) | 2:30–3:45 | Tabla semanal y administración de materias | Juan (ventana 1), Ana (ventana 3) |
| 4 | P4 (@cesardzul-byte) | 3:45–5:00 | Agendar con validación de empalmes | Juan (ventana 1) |

## 1. Diagrama ER final y reseñas (P1)

En `diagrama_er.png`:

- Siete tablas con PK, FK y cardinalidades, con fondo blanco para que se lea impreso. Coincide columna por columna con `schema.sql`, el modelo relacional y el documento de requisitos.
- `sesiones` une a un alumno y a un asesor (los dos en `usuarios`) con una materia. `resenas` es 1:1 con `sesiones`: la `UNIQUE` de `sesion_id` impide calificar dos veces.

En la ventana 1 (Juan):

1. "Mis sesiones" → "Historial": la sesión 4 (Cálculo Diferencial con Pedro, 26/10) está completada y tiene el botón "Calificar".
2. Calificar con 5 y "Enviar reseña". El botón desaparece.
3. Abrir el detalle de Cálculo Diferencial: Pedro pasa de "★ 3.0 · 1 reseña" a "★ 4.0 · 2 reseñas".

## 2. Solicitudes, sesiones y usuarios (P2)

En la ventana 2 (Luis), "Solicitudes y sesiones":

1. En "Próximas", la sesión 7 (07/12, 09:00–10:00, con Juan) está "Pendiente". "Confirmar": cambia a "Confirmada".
2. La sesión 6 (28/10, Cálculo Integral) ya pasó: "Marcar como completada" la lleva al "Historial". Una sesión futura no se puede completar; el servidor lo rechaza aunque se edite el HTML.
3. Nunca se borra una fila: cancelar solo cambia el estado.

En la ventana 3 (Ana):

4. En el panel, el resumen dice 3 alumnos. "Usuarios" → Carlos Rodríguez → "Desactivar" y aceptar la confirmación.
5. De vuelta en el panel, ahora son 2 alumnos. Carlos ya no puede entrar y sus sesiones se conservan.

## 3. Tabla semanal y materias (P3)

En la ventana 1 (Juan):

1. Detalle de Cálculo Diferencial → "Agendar" con Luis.
2. "Semana siguiente" hasta la del 07/12, sin recargar la página. El lunes 09:00–10:00 sale "Ocupado" (la sesión que Luis acaba de confirmar), 10:00–11:00 "Disponible" y lo demás "Sin bloque". Cada celda dice su estado con texto, no solo con color, y la tabla se arma con `fetch` desde `public/api/horarios.php`.
3. "Reservado" (una sesión pendiente) aparece cuando P4 agende.

En la ventana 3 (Ana):

4. "Materias" → "Nueva materia": clave `MAT-999`, nombre `Topología General` → "Crear materia". En el inicio aparece con "Sin asesores aún" y el resumen del panel sube a 11 materias activas.
5. "Desactivar" `MAT-999` y aceptar: sale del inicio. No se borra, se puede volver a activar.

## 4. Agendar con empalmes (P4)

En la ventana 1 (Juan), en la página de agendar con Luis:

1. En la tabla, avanzar a la semana del 14/12 y tocar el lunes 09:00. Se llenan la fecha y las horas; cambiar el fin a 10:00, escribir el tema `Derivadas de orden superior` y "Solicitar sesión". Lleva a "Mis sesiones" con un aviso y la sesión queda "Pendiente".
2. **Empalme en vivo:** volver a agendar con Luis el 14/12 de 09:30 a 10:30. Se rechaza con "Ya tienes Cálculo Diferencial de 09:00 a 10:00." Si el choque fuera con otro alumno, solo diría "El asesor ya tiene una sesión de 09:00 a 10:00", sin revelar con quién.
3. Agendar 10:00–11:00: se acepta, porque solo se toca con la anterior.
4. En la tabla del 14/12, 09:00–11:00 ya sale "Reservado".
5. Revisar el empalme y guardar se hacen en una sola transacción que bloquea al alumno y al asesor, así que dos peticiones al mismo tiempo no pueden crear sesiones que choquen.

Si la app falla durante la demo, P4 corre `tests/horario_check.php` y `tests/estados_check.php` para mostrar que las reglas de empalmes y de estados funcionan.

## Ensayo

| Fecha | Duración | Quién asistió | Notas |
|---|---|---|---|
| | | | |

Máximo 7 minutos. Después de ensayar, se repite el paso 2 de "Antes de presentar".

## Pendientes para la planeación de la entrega final

El alcance está cerrado en `limitaciones-mvp.md` (§3, §7 y §8). Congelamiento de funciones: jue 19/11.

| Pendiente | Origen | Responsable |
|---|---|---|
| Escribir la planeación de la entrega final con issues, fechas y milestone | `limitaciones-mvp.md` | P4 |
| Consultas y API de estadísticas (`public/api/estadisticas.php`) con los 6 indicadores de RF-17 y filtro de fechas | RF-17 | P1 |
| Vista de estadísticas con tarjetas, tablas y barras con CSS | RF-17 | P3 |
| Tabla de solo lectura para que el administrador supervise las sesiones | §3 de requisitos | P1 |
| Manual de usuario con capturas, en PDF | Especificación del curso | P2 |
| Manual técnico, en PDF: instalación, arquitectura, BD (P1) y los límites aceptados de `limitaciones-mvp.md` §6 | Especificación del curso | P4 y P1 |
| Pulido de interfaz: estados vacíos, mensajes de error y 360 px; borrar `public/ui.php`, `app/views/ui.php`, su sección de `app.css` y los `.gitkeep` que queden | `limitaciones-mvp.md` §7 | P3 |
| Pruebas finales en Chrome, Firefox y Edge, y a 360 px | `limitaciones-mvp.md` §8 | P3 |
| Etiqueta `v1.0.0`, zip, respaldo `.sql` y subir todo a la plataforma | `limitaciones-mvp.md` §2 | P4 |
| Coevaluación de cada integrante | Especificación del curso | Todos |
| Observaciones del profesor en esta revisión | Revisión 2 | Se agregan aquí después de presentar |
