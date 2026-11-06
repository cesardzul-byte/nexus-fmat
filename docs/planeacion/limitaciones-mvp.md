# Limitaciones y alcance final (MVP) — Nexus FMAT

Este documento fija hasta dónde llega el proyecto. **Si una función no aparece en la sección 3, no se hace**, aunque suene fácil o sobre tiempo. Para cambiarlo se sigue la sección 9.

El detalle de la revisión 2 está en [`planeacion-segundaEntrega.md`](planeacion-segundaEntrega.md). La entrega final se detalla el 13/11 con este alcance.

**El MVP en una frase:** un alumno encuentra un asesor de una materia y agenda sin empalmes, el asesor confirma y completa la sesión, el alumno la califica y el administrador ve cuánta demanda hay.

---

## 1. Restricciones

| Restricción | Valor |
|---|---|
| Fechas | Revisión 2: jue 12/11/2026 · Entrega final: jue 26/11/2026 |
| Tiempo de trabajo | Revisión 2: 03/11 al 11/11 · Entrega final: 13/11 al 25/11 |
| Horas | Unas 4 h por integrante en cada entrega: **16 h del equipo por entrega, 32 h en total** |
| Equipo | 4 integrantes |
| Tecnología | PHP 8.2 con PDO, MariaDB 10.4 en XAMPP 8.2.12, HTML, CSS y JavaScript sin frameworks, sin librerías y sin Composer |
| Datos | Las 7 tablas actuales de `schema.sql`, sin tablas ni columnas nuevas |
| Dónde corre | En local con XAMPP. La entrega es código y base de datos, no un sitio publicado |

---

## 2. Lo que exige el curso

Es lo único que no se puede recortar.

| Exigencia de la especificación | Cómo se cumple | Cuándo |
|---|---|---|
| Documento de requisitos (3 pts) | `documento_requisitos`; se alinea con el código en ISS-26 | Hecho |
| Modelo relacional | `modelo_relacional` | Hecho |
| Diagrama entidad-relación (2 pts) | Versión final con fondo blanco (ISS-26) | Revisión 2 |
| Base de datos de 5 o más tablas | 7 tablas | Hecho |
| CREATE, READ, UPDATE y DELETE | Sesiones, materias, usuarios y disponibilidad | Revisión 2 |
| Autenticación y sesiones | Inicio y cierre de sesión, roles, CSRF | Hecho |
| Manual de usuario y manual técnico | `docs/manuales/`, en PDF | Entrega final |
| Aplicación web y base de datos | Etiqueta `v1.0.0`, zip y respaldo `.sql` | Entrega final |
| Integración y coevaluación (2 pts) | Revisión cruzada, demo por integrante y coevaluación | Todas |

---

## 3. Qué incluye el MVP

Esta lista está cerrada.

| Rol | Función | Versión que se entrega | RF | Entrega |
|---|---|---|---|---|
| Visitante | Inicio y materias | Tarjetas con número de asesores y filtro por nombre | RF-04 | Hecho |
| Visitante | Detalle de materia | Asesores, bloques y promedio de calificación | RF-04 | Hecho; promedio en revisión 2 |
| Visitante | Registro | Alumno o asesor, con correo institucional, sin aprobación | RF-01 | Hecho |
| Todos | Sesión | Entrar, salir y panel según el rol | RF-02, RF-03 | Hecho |
| Asesor | Materias y disponibilidad | CRUD de bloques con empalmes; no se quita una materia con sesiones vivas | RF-06 | Hecho; regla en revisión 2 |
| Alumno | Buscar | Materia → asesor → tabla semanal con los 4 estados de RF-05 | RF-05, RF-11 | Revisión 2 |
| Alumno | Agendar | Fecha, horas y tema; valida bloque y empalmes en una transacción | RF-12 | Revisión 2 |
| Alumno | Mis sesiones | Próximas e historial; cancelar | RF-14 | Revisión 2 |
| Alumno | Calificar | 1 a 5 y comentario opcional, una vez por sesión completada | RF-16 | Revisión 2 |
| Asesor | Solicitudes | Confirmar, cancelar y marcar como completada | RF-13, RF-14, RF-15 | Revisión 2 |
| Administrador | Materias | Crear, editar, activar y desactivar | RF-07, RF-08, RF-09 | Revisión 2 |
| Administrador | Usuarios | Consultar, filtrar por rol, editar datos, activar y desactivar | RF-10 | Revisión 2 |
| Administrador | Estadísticas | Los 6 indicadores de RF-17 con filtro de fechas | RF-17 | Entrega final |
| Administrador | Supervisar sesiones | Tabla de solo lectura de todas las sesiones | §3 de requisitos | Entrega final |

**Indicadores del panel de estadísticas.** Salen de `public/api/estadisticas.php` con tres consultas, y se muestran en tarjetas, tablas y barras hechas con CSS:

1. Asesorías solicitadas.
2. Sesiones completadas.
3. Sesiones canceladas.
4. Materias más solicitadas.
5. Calificación promedio y número de reseñas.
6. Asesorías por materia en el periodo elegido.

---

## 4. Qué se recorta de lo que ya se había dicho

Estas funciones se mencionan en la planeación de la primera entrega, en los requisitos o en las observaciones de las pruebas, y quedan así:

| Se había dicho | Dónde | En el MVP | Por qué |
|---|---|---|---|
| Editar perfil y cambiar contraseña | Planeación 1, ISS-08 | No se hace. El administrador corrige los datos | No tiene RF y el UPDATE ya se cubre en otras entidades |
| CRUD de licenciaturas | Planeación 1, modelo relacional §3.1 | Catálogo fijo en `seed.sql` | No tiene RF y casi nunca cambia |
| Aprobación de asesores por el administrador | ISS-08 | No se hace. El administrador puede desactivar una cuenta | El flujo de aprobación no cabe en las horas; RF-01 se corrige |
| Asignar roles | RF-10 | El rol se fija al registrarse | Cambiarlo dejaría materias, bloques y sesiones incoherentes |
| Filtrar por asesor, día y horario | RF-11 | Materia → asesor → tabla semanal | La tabla ya muestra día y horario; un buscador aparte duplica la función |
| Historial de cambios de estado | RF-13 | Solo se guarda el estado actual | Una bitácora necesitaría otra tabla |
| Estadísticas en la revisión 2 | Planeación 1 | Todo en la entrega final | RF-17 ya estaba marcado para la entrega final |
| Solicitudes por licenciatura, horarios con más demanda, mejores asesores y tasa de cancelación | Planeación 1 | No se hacen | No están en RF-17 |
| Gráficas | Planeación 1 | Barras con CSS | No se permiten librerías y no agregan indicadores |
| Búsqueda tolerante a errores de dedo | Pruebas, IDX-03 | No se hace | El filtro actual funciona |
| Borrar el error de un campo al corregirlo | Pruebas, REG-09 | No se hace | El error desaparece al volver a enviar |
| Número de sesiones por bloque | Pruebas, SES-02 | No se hace | El asesor las ve en "Solicitudes y sesiones" |

---

## 5. Qué queda fuera

**Funciones**

- Videollamadas, pagos y notificaciones de cualquier tipo. El alumno y el asesor revisan "Mis sesiones".
- Chat o mensajes entre usuarios.
- Exportar a PDF, Excel o calendarios externos.
- Sesiones grupales o recurrentes, lista de espera y reprogramar. Para cambiar una sesión, se cancela y se agenda otra.
- Vencimiento automático de sesiones pendientes, plazo o motivo para cancelar.
- Días festivos, excepciones y vigencia de los bloques de disponibilidad.
- Editar, borrar o responder reseñas.
- Fotos de perfil y cualquier archivo subido por el usuario.

**Cuentas**

- Recuperar contraseña, "recordarme", verificación de correo y doble factor.
- Bloqueo por intentos fallidos.
- Varios roles por usuario, y crear administradores desde la aplicación.

**Técnico**

- Frameworks, librerías de CSS, JS o gráficas, Composer, bundlers y un router.
- Tablas nuevas, triggers, procedimientos almacenados y migraciones.
- Hosting, HTTPS, Docker e integración continua.
- Paginación y caché: los datos son pocos.
- Pruebas automáticas más allá de `tests/*.php`. Lo demás se prueba a mano con una lista escrita.
- Modo oscuro, varios idiomas y aplicación instalable.

---

## 6. Límites que se aceptan

No son errores. Son consecuencias de este alcance y se explican en el manual técnico, para que no se confundan con fallas ni se intenten "arreglar" al final.

| Límite | Consecuencia | Qué se hace |
|---|---|---|
| Sin recuperar contraseña | Quien la olvida no puede entrar | El administrador técnico la cambia en phpMyAdmin |
| Registro de asesor sin aprobación | Cualquier correo institucional puede ser asesor | El administrador lo desactiva |
| Las pendientes no vencen | Una solicitud sin atender se queda pendiente | El asesor o el alumno la cancela |
| Desactivar una cuenta no cancela sus sesiones | Pueden quedar sesiones futuras con una cuenta inactiva | La otra persona las cancela |
| Editar un bloque no revisa sesiones | Una sesión puede quedar fuera del horario nuevo | Se respeta: la sesión ya estaba acordada |
| La tabla semanal no marca choques del alumno | Una celda "Disponible" puede rechazarse | El servidor avisa con cuál sesión choca |
| Fechas fijas en los datos demo | La demo depende de las fechas de 2026 | Sirven hasta diciembre de 2026 |
| Solo HTTP en local | La cookie de sesión no lleva `Secure` | RNF-05 ya lo condiciona a tener HTTPS |

---

## 7. Presupuesto de horas

| Integrante | Revisión 2 (03/11–11/11) | h | Entrega final (13/11–25/11) | h | Total |
|---|---|---|---|---|---|
| P1 | Datos demo, reseñas, regla de materias, ER final, revisión de PR | 4 | Consultas y API de estadísticas (1.5), supervisar sesiones (0.5), manual técnico: BD (1), correcciones (1) | 4 | 8 |
| P2 | Mis sesiones, usuarios, revisión de PR | 4 | Manual de usuario con capturas (2.5), correcciones (1.5) | 4 | 8 |
| P3 | Estilos, tabla semanal, materias, pruebas, revisión de PR | 4 | Vista de estadísticas (1.5), pulido de interfaz (1), pruebas finales (1), correcciones (0.5) | 4 | 8 |
| P4 | Base común, agendar, API de horarios, v0.2.0, coordinación, revisión de PR | 4 | Manual técnico (2), `v1.0.0` y entrega (1), integración y correcciones (1) | 4 | 8 |
| **Equipo** | | **16** | | **16** | **32** |

**Pulido de interfaz** significa estados vacíos, mensajes de error, 360 px y borrar `ui.php`, su sección de `app.css` y los `.gitkeep` que queden. No incluye rediseñar pantallas.

**Si sobra tiempo,** se usa en este orden: bugs, manuales e interfaz. Nunca en funciones nuevas.

---

## 8. Plan de recorte y fin del proyecto

**Si no alcanza el tiempo en la revisión 2,** se sigue el orden del supuesto 11 de la planeación.

**Si no alcanza en la entrega final (se decide el jue 19/11),** se quita en este orden:

1. La tabla de supervisión de sesiones.
2. El filtro de fechas de las estadísticas: se muestran los totales históricos.
3. Las barras con CSS: los indicadores quedan en tablas.

**Nunca se recortan:** el ciclo agendar → confirmar → completar → calificar, el CRUD, los 6 indicadores en alguna forma, los dos manuales, las pruebas y el paquete de entrega.

**El proyecto está terminado cuando:**

- [ ] Importar `schema.sql` y `seed.sql` en un servidor limpio no da errores, y la app abre siguiendo solo el README.
- [ ] El ciclo completo funciona con las cuentas demo de los tres roles.
- [ ] `tests/horario_check.php` y `tests/estados_check.php` pasan.
- [ ] Los casos de prueba están en verde en Chrome, Firefox y Edge, y a 360 px no hay scroll horizontal de la página.
- [ ] No queda `ui.php` ni `.gitkeep` en carpetas con archivos.
- [ ] `main` tiene `v1.0.0` y coincide con `develop`.
- [ ] Están en la plataforma: zip, respaldo `.sql`, requisitos, modelo relacional, diagrama ER, manual de usuario y manual técnico.
- [ ] Cada integrante hizo su coevaluación.

---

## 9. Cómo cambiar este documento

Algo nuevo solo entra si cumple una de estas condiciones:

1. Lo pide el profesor o lo exige la especificación. Entra directo y se anota aquí.
2. El equipo lo aprueba en un PR a este archivo, y **sale algo de las mismas horas**, indicado en el mismo PR.

Un error en algo de la sección 3 no es un cambio de alcance: se registra como `tipo:bug`. Una mejora a algo que ya funciona sí lo es.
