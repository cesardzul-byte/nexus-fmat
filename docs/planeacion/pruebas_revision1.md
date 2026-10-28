# Pruebas de la revisión 1 — Nexus FMAT

Issue: ISS-13 · Responsable: P3 (@Pao-Parra)

Pruebas manuales de la revisión 1, hechas como usuario y siguiendo esta lista. Cada caso tiene el resultado esperado, el obtenido y su estado. Cada falla se registra como un issue `tipo:bug` enlazado a ISS-13 y asignado al responsable del issue original.

## Datos de la prueba

| Dato | Valor |
|---|---|
| Fecha | _pendiente_ |
| Computadora | _pendiente_ (distinta a la de P4) |
| Rama / commit probado | `develop` @ _pendiente_ |
| Navegadores | Chrome _versión_ · Firefox _versión_ |
| XAMPP / PHP / MariaDB | 8.2.12 / 8.2.12 / 10.4.32 |

**Cuentas demo** (contraseña `Demo1234`):

| Rol | Correo |
|---|---|
| Administrador | `ana.martinez@demo.uady.mx` |
| Asesor | `luis.hernandez@demo.uady.mx` (Cálculo Diferencial e Integral) |
| Asesor | `laura.ramirez@demo.uady.mx` |
| Asesor | `pedro.sanchez@demo.uady.mx` |
| Alumno | `juan.perez@demo.uady.mx` |

**Estados:** ✅ Pasa · ❌ Falla (con enlace al issue del bug) · ⏳ Pendiente

---

## 1. Instalación desde cero (README)

Se hace en una computadora distinta a la de P4, siguiendo solo el README y sin pedir ayuda.

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| INS-01 | Clonar | Clonar el repo en `C:/xampp/htdocs` como dice el README | Se crea `nexus-fmat` con todas las carpetas | | ⏳ |
| INS-02 | Importar BD | Importar `schema.sql` y después `seed.sql` en phpMyAdmin | Sin errores; base `nexus_fmat` con 7 tablas | | ⏳ |
| INS-03 | Reimportar | Volver a importar `schema.sql` y `seed.sql` | Sin errores (el script borra y crea de nuevo) | | ⏳ |
| INS-04 | Configurar | Copiar `config.example.php` a `config.php` | `git status` no muestra `config.php` | | ⏳ |
| INS-05 | Abrir la app | Abrir `http://localhost/nexus-fmat/public/` | Se ve la página de inicio, sin errores de PHP | | ⏳ |
| INS-06 | Carpeta `app/` protegida | Abrir `http://localhost/nexus-fmat/app/config/config.php` | Responde 403 | | ⏳ |

## 2. Inicio y cierre de sesión (ISS-07)

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| SES-01 | Entrar como admin | Iniciar sesión con la cuenta de Ana | Llega a `panel.php` | | ⏳ |
| SES-02 | Entrar como asesor | Iniciar sesión con la cuenta de Luis | Llega a `panel.php` | | ⏳ |
| SES-03 | Entrar como alumno | Iniciar sesión con la cuenta de Juan | Llega a `panel.php` | | ⏳ |
| SES-04 | Contraseña incorrecta | Correo de Juan con contraseña `otra1234` | "Correo o contraseña incorrectos" y se conserva el correo escrito | | ⏳ |
| SES-05 | Correo inexistente | `nadie@demo.uady.mx` con `Demo1234` | El mismo mensaje que SES-04 | | ⏳ |
| SES-06 | Usuario inactivo | Poner `activo = 0` a Juan en phpMyAdmin e intentar entrar | No entra; mismo mensaje. Regresar `activo = 1` al terminar | | ⏳ |
| SES-07 | Cambia la sesión | Ver la cookie `PHPSESSID` (F12 → Aplicación) antes y después de entrar | El valor cambia al iniciar sesión | | ⏳ |
| SES-08 | Login con sesión abierta | Ya con sesión, abrir `login.php` | Manda a `panel.php` | | ⏳ |
| SES-09 | Cerrar sesión | Clic en "Cerrar sesión" | Va al inicio con un aviso; el menú vuelve a mostrar "Iniciar sesión" | | ⏳ |
| SES-10 | Atrás después de salir | Después de SES-09, presionar "atrás" y recargar | No muestra el panel; manda al login | | ⏳ |
| SES-11 | CSRF en logout | Con F12, borrar o cambiar el valor del campo oculto del token y enviar "Cerrar sesión" | Se rechaza (400) y la sesión sigue abierta | | ⏳ |

## 3. Registro (ISS-08)

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| REG-01 | Registro válido | Llenar todo con un correo `@alumnos.uady.mx` nuevo, matrícula nueva, contraseña de 8+ | Crea la cuenta, inicia sesión y llega al panel con mensaje de bienvenida | | ⏳ |
| REG-02 | Contraseña hasheada | Revisar el usuario de REG-01 en phpMyAdmin | `password_hash` empieza con `$2y$`, nunca es la contraseña en texto | | ⏳ |
| REG-03 | Correo repetido | Registrarse de nuevo con el correo de REG-01 | Error junto al campo correo; no se crea nada | | ⏳ |
| REG-04 | Matrícula repetida | Usar la matrícula `12345678` | Error junto al campo matrícula; no se crea nada | | ⏳ |
| REG-05 | Otro dominio | Correo `ana@gmail.com` | Error de dominio junto al campo | | ⏳ |
| REG-06 | Matrícula con letras | Matrícula `12AB5678` | Error junto al campo | | ⏳ |
| REG-07 | Contraseña corta | Contraseña `abc123` | Error: mínimo 8 caracteres | | ⏳ |
| REG-08 | Contraseñas distintas | Confirmación diferente | El navegador avisa; con JS desactivado, el servidor también | | ⏳ |
| REG-09 | Se conserva lo escrito | Provocar cualquier error | Los campos se conservan, excepto las contraseñas | | ⏳ |
| REG-10 | Rol admin forzado | Con F12, cambiar el valor de una opción de rol a `admin` y enviar | Se rechaza; no se crea un admin | | ⏳ |
| REG-11 | Sin JavaScript | Desactivar JS (F12 → Configuración) y repetir REG-05 y REG-07 | El servidor muestra los mismos errores | | ⏳ |

## 4. Permisos por rol (ISS-07, ISS-10, ISS-11, ISS-12)

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| PER-01 | Panel sin sesión | Sin sesión, abrir `panel.php` | Manda al login con un aviso | | ⏳ |
| PER-02 | Materias del asesor sin sesión | Sin sesión, abrir `mis_materias.php` | Manda al login | | ⏳ |
| PER-03 | Alumno en página de asesor | Como Juan, abrir `mis_materias.php` y `disponibilidad.php` | Responden 403 | | ⏳ |
| PER-04 | Panel del alumno | Entrar como Juan | Saludo con nombre y rol; solo la tarjeta "Buscar asesoría por materia" | | ⏳ |
| PER-05 | Panel del asesor | Entrar como Luis | Saludo; tarjetas "Mis materias" y "Mi disponibilidad" | | ⏳ |
| PER-06 | Panel del admin | Entrar como Ana | Conteos: 1 admin, 3 asesores, 3 alumnos, 10 materias (datos demo sin cambios) | | ⏳ |
| PER-07 | Nombre con HTML | Registrar a alguien con nombre `<b>x</b>` y ver su panel y el menú | Se ve el texto `<b>x</b>`, no en negritas | | ⏳ |

## 5. Materias del asesor (ISS-11)

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| MAT-01 | Ver mis materias | Como Luis, abrir "Mis materias" | Lista Cálculo Diferencial y Cálculo Integral | | ⏳ |
| MAT-02 | Agregar materia | Agregar Probabilidad | Aparece en la lista con mensaje de éxito; en el inicio, Probabilidad sube a 1 asesor | | ⏳ |
| MAT-03 | Agregar repetida | Con F12, forzar el id de una materia que ya tiene | Se rechaza con un mensaje | | ⏳ |
| MAT-04 | Materia inexistente | Con F12, cambiar el id a `9999` | Se rechaza | | ⏳ |
| MAT-05 | Quitar materia | Quitar Probabilidad | Pide confirmación (avisa que borra sus bloques); al aceptar desaparece | | ⏳ |
| MAT-06 | Quitar borra bloques | Quitar una materia con bloques y revisar "Mi disponibilidad" y su detalle | Sus bloques ya no existen | | ⏳ |
| MAT-07 | Quitar de otro asesor | Como Luis, con F12 cambiar el id para quitar una materia de Laura | No se quita nada de Laura | | ⏳ |

## 6. Disponibilidad y empalmes (ISS-12)

Antes: como Luis, crear el bloque **lunes 10:00–12:00** de Cálculo Diferencial.

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| DIS-01 | Prueba automática | `php -d zend.assertions=1 tests/horario_check.php` | Termina sin errores | | ⏳ |
| DIS-02 | Choque parcial (después) | Lunes 11:00–13:00 | Rechazado: "Se empalma con Cálculo Diferencial, lunes 10:00–12:00" | | ⏳ |
| DIS-03 | Choque parcial (antes) | Lunes 09:00–10:30 | Rechazado | | ⏳ |
| DIS-04 | Bloque contenido | Lunes 10:30–11:30 | Rechazado | | ⏳ |
| DIS-05 | Bloque idéntico, otra materia | Lunes 10:00–12:00 de Cálculo Integral | Rechazado | | ⏳ |
| DIS-06 | Solo se tocan (después) | Lunes 12:00–13:00 | Aceptado | | ⏳ |
| DIS-07 | Solo se tocan (antes) | Lunes 08:00–10:00 | Aceptado | | ⏳ |
| DIS-08 | Otro día | Martes 10:00–12:00 | Aceptado | | ⏳ |
| DIS-09 | Editar sin cambiar horas | Editar el lugar del bloque lunes 10:00–12:00 | Se guarda; no choca consigo mismo | | ⏳ |
| DIS-10 | Inicio ≥ fin | Con F12, enviar 12:00–10:00 | Rechazado por el servidor | | ⏳ |
| DIS-11 | Fuera de horario | Con F12, enviar 06:00–08:00 y 20:30–22:00 | Rechazados (07:00–21:00) | | ⏳ |
| DIS-12 | Minutos inválidos | Con F12, enviar 10:15–11:00 | Rechazado (solo :00 o :30) | | ⏳ |
| DIS-13 | Día 7 | Con F12, cambiar el día a `7` | Rechazado | | ⏳ |
| DIS-14 | Materia que no imparte | Con F12, poner el id de Álgebra Lineal | Rechazado | | ⏳ |
| DIS-15 | Bloque de otro asesor | Como Luis, cambiar el id de editar/eliminar por uno de Laura | Responde 404 | | ⏳ |
| DIS-16 | Eliminar bloque | Eliminar el martes 10:00–12:00 | Pide confirmación y desaparece | | ⏳ |
| DIS-17 | Asesor sin materias | Quitarle todas sus materias a un asesor y abrir "Mi disponibilidad" | Aviso con enlace a "Mis materias" | | ⏳ |

## 7. Inicio y detalle de materia (ISS-09)

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| IDX-01 | Materias del inicio | Abrir el inicio sin sesión | 10 tarjetas; Cálculo Diferencial con 2 asesores; las que no tienen dicen "Sin asesores aún" | | ⏳ |
| IDX-02 | Botones según sesión | Ver el inicio sin sesión y con sesión | Sin sesión: Registrarse e Iniciar sesión. Con sesión: Ir al panel | | ⏳ |
| IDX-03 | Filtro | Escribir `algebra` | Solo Álgebra Lineal y Álgebra Superior, sin recargar | | ⏳ |
| IDX-04 | Filtro sin resultados | Escribir `zzz` | "No encontramos materias con ese nombre" | | ⏳ |
| IDX-05 | Detalle de materia | "Ver asesores" de Cálculo Diferencial | Luis y Pedro con su licenciatura y bloques por día y hora | | ⏳ |
| IDX-06 | Id inválido | `materia.php?id=abc` | 404 sin errores de PHP | | ⏳ |
| IDX-07 | Id inexistente | `materia.php?id=9999` | 404 sin errores de PHP | | ⏳ |
| IDX-08 | Bloque nuevo aparece | Después de DIS-08, abrir el detalle de Cálculo Diferencial | Aparece el martes 10:00–12:00 | | ⏳ |

## 8. Vista a 360 px y navegadores

En cada página: F12 → modo dispositivo → ancho 360.

| ID | Caso | Pasos | Esperado | Obtenido | Estado |
|---|---|---|---|---|---|
| VIS-01 | Inicio a 360 px | Abrir el inicio | Sin scroll horizontal; botón "Menú" funciona | | ⏳ |
| VIS-02 | Detalle a 360 px | Abrir el detalle de una materia | Sin scroll horizontal; la tabla se desliza sola | | ⏳ |
| VIS-03 | Login y registro a 360 px | Abrir ambos | Formularios usables, sin scroll horizontal | | ⏳ |
| VIS-04 | Panel a 360 px | Entrar con cada rol | Tarjetas una debajo de otra | | ⏳ |
| VIS-05 | Páginas del asesor a 360 px | "Mis materias" y "Mi disponibilidad" | Usables, sin scroll horizontal | | ⏳ |
| VIS-06 | Chrome | Repetir IDX-01, SES-01 y DIS-02 | Igual que lo esperado | | ⏳ |
| VIS-07 | Firefox | Repetir IDX-01, SES-01 y DIS-02 | Igual que lo esperado | | ⏳ |

---

## Resumen

| Sección | Casos | ✅ | ❌ | ⏳ |
|---|---|---|---|---|
| 1. Instalación | 6 | | | 6 |
| 2. Sesión | 11 | | | 11 |
| 3. Registro | 11 | | | 11 |
| 4. Permisos | 7 | | | 7 |
| 5. Materias del asesor | 7 | | | 7 |
| 6. Disponibilidad | 17 | | | 17 |
| 7. Inicio y detalle | 8 | | | 8 |
| 8. Vista y navegadores | 7 | | | 7 |
| **Total** | **74** | | | **74** |

## Bugs encontrados

| Issue | Caso | Descripción | Responsable | Estado |
|---|---|---|---|---|
| | | | | |
