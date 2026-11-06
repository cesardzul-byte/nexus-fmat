# Planeación del proyecto — Nexus FMAT (segunda entrega)

Detalla la revisión 2 sobre lo que ya existe en `develop`. Al terminarla funciona el ciclo completo: el asesor publica, el alumno agenda sin empalmes, el asesor confirma y completa, y el alumno califica. El alcance final del proyecto y lo que queda fuera están en [`limitaciones-mvp.md`](limitaciones-mvp.md).

Equipo:

| Clave | Usuario | Enfoque |
|---|---|---|
| P1 | @Luis-villanueva-valdivia | Base de datos, modelos y reseñas |
| P2 | @oswaldo-castillo | Sesiones del alumno y del asesor, administración de usuarios |
| P3 | @Pao-Parra | Interfaz (HTML, CSS, JS), administración de materias y pruebas de usuario |
| P4 | @cesardzul-byte | Coordinación, agendado con empalmes e integración |

Revisiones:

| Revisión | Fecha | Qué se entrega |
|---|---|---|
| Revisión 1 | jue 29/10/2026 | Presentada: requisitos, diagrama entidad-relación e index, autenticación y disponibilidad |
| **Revisión 2** | **jue 12/11/2026** | **Código de las demás funcionalidades y componentes, y diagrama ER final (2 puntos)** |
| Entrega final | jue 26/11/2026 | Aplicación integrada, base de datos y manuales de usuario y técnico |

---

## 1. Supuestos

1. **Fechas.** El trabajo va del mar 03/11 al mié 11/11. El jueves 12 solo se presenta.
2. **Horas.** Cada integrante tiene unas 4 h para esta revisión (16 h del equipo), incluida la revisión de PR. La entrega final tiene otras 4 h por persona. Es cerca de un tercio de lo que se presupuestó para la revisión 1 (11–13 h), por eso el alcance se redujo. Las estimaciones no tienen holgura y suponen copiar los patrones que ya existen: si algo se atrasa se aplica el plan de recorte (supuesto 11), no se agregan horas.
3. **Tecnología.** No cambia: PHP 8.2 con PDO, MariaDB 10.4 (XAMPP 8.2.12), HTML, CSS y JavaScript sin frameworks, sin librerías y sin Composer.
4. **La base de datos no cambia.** `schema.sql` ya tiene `sesiones` y `resenas`. Esta revisión no agrega tablas ni columnas; solo crece `seed.sql` (ISS-16).
5. **Registro.** Se queda como está desde ISS-08: el usuario elige alumno o asesor, sin aprobación del administrador. ISS-08 dejó esta decisión para la revisión 2. Es lo más barato y ya está probado, pero contradice RF-01, que se corrige en ISS-26.
6. **Estados de una sesión.** Solo estas transiciones. `completada` y `cancelada` son finales: una sesión cancelada no se reactiva, se agenda otra.

   | Estado actual | Alumno de la sesión | Asesor de la sesión |
   |---|---|---|
   | `pendiente` | cancelar | confirmar, cancelar |
   | `confirmada` | cancelar | cancelar, completar (solo si ya pasó la hora de fin) |

7. **Zona horaria.** El `php.ini` de XAMPP trae `Europe/Berlin` (7 h adelante de Mérida en noviembre): desde las 17:00 de Mérida, `date('Y-m-d')` ya devuelve el día siguiente. Por eso `bootstrap.php` fija `America/Merida` (ISS-15).
8. **Fechas de los datos demo.** Son fijas, sin `CURDATE()`: las pasadas en octubre y las futuras en diciembre de 2026. Así sirven igual en las dos presentaciones que faltan.
9. **Ramas, revisión cruzada y dueños.** Siguen `docs/convenciones.md`. Dueños nuevos: `Sesion.php` y `horario.php` → P4, `Resena.php` → P1. Los demás agregan al final con comentario de sección.
10. **IDs de issues.** Se continúa en ISS-15. El último número usado en GitHub es #32, así que si los issues se crean en orden antes de abrir otro PR, ISS-NN = #(NN + 18): ISS-15 = #33 … ISS-28 = #46. Si se abre un PR antes, se suma ese desplazamiento al copiar las dependencias.
11. **Plan de recorte.** Si el vie 06/11 algo va atrasado, se quita en este orden: (1) el promedio del asesor en `materia.php` (ISS-24) y el filtro por rol de usuarios (ISS-23); (2) la edición de datos de usuarios (ISS-23 se queda en listar, activar y desactivar); (3) la tabla semanal (ISS-20 e ISS-21 pasan a la entrega final, porque el formulario de ISS-18 funciona sin ella). Nunca se recortan ISS-18, ISS-19, ISS-22, el registro de la reseña de ISS-24 ni ISS-26, porque sin ellos no hay ciclo completo, CRUD ni los 2 puntos del ER.

---

## 2. Fases e hitos de todo el proyecto

Cada revisión es un milestone de GitHub. Los hitos son puntos de control internos. Fechas y nombres son los de la planeación de la primera entrega.

| Fase (milestone) | Periodo (vence) | Objetivo | Hitos |
|---|---|---|---|
| **Revisión 1 — Requisitos, ER y primer avance** | 16/10 al 28/10 (vence 2026-10-29) | Presentada. Falta cerrar ISS-14. | H1.1 · H1.2 · H1.3 |
| **Revisión 2 — Funcionalidades** | 03/11 al 11/11 (vence 2026-11-12) | Funciona el ciclo publicar → agendar → realizar → calificar, con CRUD en todas las entidades y empalmes de sesiones. | **H2.1 Agendar con empalmes** (jue 05/11) · **H2.2 Congelamiento** (mar 10/11) · **H2.3 v0.2.0** (mié 11/11) |
| **Entrega final — Integración y documentación** | 13/11 al 25/11 (vence 2026-11-26) | Panel de estadísticas, interfaz pulida, pruebas completas y manuales. Alcance en `limitaciones-mvp.md`. | **H3.1 Congelamiento de funciones** (jue 19/11) · **H3.2 Manuales listos** (lun 23/11) · **H3.3 v1.0.0 entregada** (mié 25/11) |

### Estado al empezar (lun 02/11)

Revisado en el repositorio y en GitHub:

| Tema | Situación | Qué se hace |
|---|---|---|
| ISS-14 | Sigue abierto. `main` solo tiene el commit inicial (26 detrás de `develop`), no existe `v0.1.0` y no se escribió `guion_revision1.md` | P4 fusiona `develop` a `main`, crea la etiqueta y cierra el issue. El guion ya no hace falta porque la revisión se presentó |
| Milestones | Solo existe el de la Revisión 1 | P4 crea los otros dos con su fecha |
| Labels | Faltan `area:sesiones`, `area:resenas`, `area:estadisticas` y `area:admin` | P4 los crea |
| RF-05 (tabla semanal) | El documento lo marca para la revisión 1, pero `materia.php` solo lista bloques | Se entrega en esta revisión (ISS-20 e ISS-21) |
| RF-01 (registro) | El documento dice que el rol de asesor lo asigna una cuenta autorizada; el código deja elegirlo | Se corrige el documento (supuesto 5, ISS-26) |
| Diagrama ER | Las 7 tablas coinciden con `schema.sql`, pero el PNG tiene fondo oscuro y no se lee bien impreso | Se exporta con fondo blanco (ISS-26) |

### Antes de empezar (P4, mar 03/11)

1. Cerrar ISS-14 como dice la tabla anterior.
2. Crear los milestones `Revisión 2 — Funcionalidades` (2026-11-12) y `Entrega final — Integración y documentación` (2026-11-26).
3. Crear los 4 labels que faltan.
4. Crear ISS-15 a ISS-28 en orden, cada uno con responsable, milestone y labels (§5).

### Cambios respecto al alcance tentativo de la primera entrega

| Componente tentativo | Decisión |
|---|---|
| Agendar con `fetch` desde `public/api/horarios.php` (P4) | Se mantiene. La vista de la tabla la hace P3 (ISS-21) |
| Mis sesiones (P2) | Se mantiene (ISS-19) |
| Perfil: editar datos y cambiar contraseña (P2) | **Sale del MVP.** No tiene RF y el UPDATE ya se cubre en otras entidades |
| Reseñas, promedio, ER y datos demo (P1) | Se mantienen (ISS-16, ISS-24 e ISS-26) |
| CRUD de materias (P3) | Se mantiene (ISS-22) |
| CRUD de licenciaturas (P3) | **Sale del MVP.** No tiene RF; el catálogo queda en `seed.sql` |
| Activar y desactivar usuarios (P3) | Pasa a P2 (ISS-23), que es dueño de `Usuario.php` |
| Primera versión de estadísticas (P3) | **Pasa a la entrega final**, junto con el panel completo (RF-17) |

### Requisitos que cubre esta revisión

| Requisito | Issue |
|---|---|
| RF-05 Tabla de horarios | ISS-20, ISS-21 |
| RF-07, RF-08, RF-09 Materias | ISS-22 |
| RF-10 Usuarios | ISS-23 |
| RF-11 Buscar asesorías (versión reducida) | ISS-18, ISS-21 |
| RF-12 Agendar | ISS-18 |
| RF-13, RF-14, RF-15 Solicitudes, cancelar y completar | ISS-19 |
| RF-16 Reseñas | ISS-24 |

RF-17 (estadísticas) es de la entrega final.

### CRUD por entidad al cerrar la revisión 2

| Entidad | Crear | Leer | Actualizar | Eliminar o desactivar |
|---|---|---|---|---|
| `usuarios` | ISS-08 | ISS-23 | ISS-23 | ISS-23 (desactivar) |
| `materias` | ISS-22 | ISS-09, ISS-22 | ISS-22 | ISS-22 (desactivar) |
| `disponibilidad` | ISS-12 | ISS-12 | ISS-12 | ISS-12 |
| `asesor_materia` | ISS-11 | ISS-11 | — | ISS-11, ISS-25 |
| `sesiones` | ISS-18 | ISS-19 | ISS-19 (estado) | ISS-19 (cancelar) |
| `resenas` | ISS-24 | ISS-19, ISS-24 | — | — |
| `licenciaturas` | `seed.sql` | ISS-08 | — | — |

### Cobertura de la rúbrica

| Rubro | Dónde se cubre |
|---|---|
| Diagramas entidad-relación (2 pts, segunda revisión) | ISS-26 |
| Codificación (3 pts y los 20 del desglose) | ISS-15 a ISS-25 |
| Trabajo en equipo (2 pts) | Cada quien fusiona sus PR, revisión cruzada y una parte de la demo para cada integrante (ISS-28) |
| Funcionalidad (40 %) | Ciclo completo y CRUD de la tabla anterior |
| Temas del curso (20 %) | Formularios y sesiones en PHP, transacciones con PDO (ISS-18), `fetch` con JSON (ISS-20, ISS-21), validación con HTML5 y JS, CSS responsivo |
| Modularidad (20 %) | Base común (ISS-15), un modelo por tabla, reglas en funciones puras con su prueba (ISS-18, ISS-19, ISS-20) |
| Interfaz (20 %) | Estilos de los flujos nuevos (ISS-17) y tabla semanal (ISS-21) |

---

## 3. Calendario de la revisión 2

| Issue | Actividad | Resp. | Depende de | Fecha objetivo | Est. |
|---|---|---|---|---|---|
| ISS-15 | Base común de la revisión 2 | P4 | — | mar 03/11 | 0.5 h |
| ISS-16 | Datos demo de sesiones y reseñas | P1 | — | mar 03/11 | 0.5 h |
| ISS-17 | Estilos de los flujos nuevos | P3 | — | mié 04/11 | 0.5 h |
| ISS-18 | Agendar con validación de empalmes | P4 | 15, 16 | jue 05/11 | 1.5 h |
| ISS-19 | Mis sesiones y cambios de estado | P2 | 15, 16, 17 | vie 06/11 | 2 h |
| ISS-20 | API de horarios | P4 | 15, 18 | vie 06/11 | 0.5 h |
| ISS-25 | Materias con sesiones vivas | P1 | 15, 16 | vie 06/11 | 0.5 h |
| ISS-22 | Administración de materias | P3 | 15, 17 | sáb 07/11 | 1 h |
| ISS-21 | Tabla semanal con `fetch` | P3 | 17, 18, 20 | lun 09/11 | 1 h |
| ISS-23 | Administración de usuarios | P2 | 15, 17 | lun 09/11 | 1.5 h |
| ISS-24 | Reseñas y promedio del asesor | P1 | 16, 19 | lun 09/11 | 1.5 h |
| ISS-26 | ER final y documentos alineados | P1 | — | lun 09/11 | 1 h |
| ISS-27 | Pruebas de la revisión 2 | P3 | 18–25 | mar 10/11 | 1 h |
| ISS-28 | Versión 0.2.0 y entrega | P4 | 26, 27 | mié 11/11 | 0.5 h |

Carga:

| Integrante | Issues | Desarrollo | Revisión de PR | Coordinación | Total |
|---|---|---|---|---|---|
| P1 | ISS-16, 24, 25, 26 | 3.5 h | 0.5 h | — | **4 h** |
| P2 | ISS-19, 23 | 3.5 h | 0.5 h | — | **4 h** |
| P3 | ISS-17, 21, 22, 27 | 3.5 h | 0.5 h | — | **4 h** |
| P4 | ISS-15, 18, 20, 28 | 3 h | 0.5 h | 0.5 h | **4 h** |

Cada PR se revisa en unos 10 minutos: se prueba la ruta feliz y un caso de rechazo. La coordinación de P4 es la sección "Antes de empezar".

- **H2.1 Agendar con empalmes (jue 05/11):** ISS-15, 16, 17 y 18 fusionados. Un alumno demo agenda una sesión y los empalmes se rechazan. Sin esto no se puede demostrar lo que distingue al proyecto, así que si no llega, P4 avisa ese mismo día.
- **H2.2 Congelamiento (mar 10/11):** ISS-19 a 26 fusionados a más tardar el lun 09/11 en la noche. El martes se prueba y desde ahí solo entran correcciones.
- **H2.3 v0.2.0 (mié 11/11):** ISS-27 e ISS-28. `v0.2.0` está en `main` y los PDFs están en la plataforma.

---

## 4. Arquitectura y estructura

La estructura y la forma de las páginas son las de `docs/convenciones.md`. Solo se agregan estos archivos:

```
nexus-fmat/
├── app/
│   ├── models/
│   │   ├── Sesion.php            # ISS-15 lo crea; ISS-18, 19, 20 y 25 agregan métodos
│   │   └── Resena.php            # ISS-24
│   └── views/
│       ├── sesiones/             # agendar.php, mis_sesiones.php
│       ├── resenas/              # crear.php
│       └── admin/                # materias.php, usuarios.php
├── public/
│   ├── agendar.php · mis_sesiones.php · resena.php
│   ├── admin_materias.php · admin_usuarios.php
│   └── api/horarios.php          # único endpoint JSON de esta revisión
└── tests/estados_check.php       # transiciones de estado, sin BD
```

**Agendar es la única escritura que necesita transacción.** Dos personas pueden pedir el mismo horario a la vez y MySQL no tiene restricciones de exclusión por rangos de tiempo. Se bloquean las filas de los dos participantes, de modo que cualquier otro agendado de alguno de ellos espera su turno:

```php
// Sesion::agendar(): revisar empalmes y guardar son una sola operación (RN-06)
$pdo = db();
$pdo->beginTransaction();
try {
    // ORDER BY: dos peticiones toman los bloqueos en el mismo orden y no se traban entre sí
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE id IN (?, ?) ORDER BY id FOR UPDATE');
    $stmt->execute([$alumnoId, $asesorId]);
    // leer las sesiones que reservan en esa fecha → seEmpalman() → INSERT, o rollBack() con el error
    $pdo->commit();
} catch (Throwable $ex) {
    $pdo->rollBack();
    throw $ex;
}
```

**Cambiar de estado no necesita transacción.** El `UPDATE` lleva el estado esperado en el `WHERE`, así que un doble clic o dos pestañas no pueden aplicar la misma transición dos veces, y `rowCount()` dice si se aplicó:

```sql
UPDATE sesiones SET estado = ? WHERE id = ? AND asesor_id = ? AND estado = ?
```

**La API solo lee y responde JSON también en los errores.** `requiereRol()` redirige al login y `fetch` seguiría esa redirección hasta recibir HTML, por eso `public/api/` usa `responderJson()`.

```
GET public/api/horarios.php?asesor=2&materia=1&lunes=2026-11-09
```
```json
{
  "lunes": "2026-11-09",
  "dias": [
    {
      "fecha": "2026-11-09",
      "intervalos": [
        { "inicio": "09:00", "fin": "09:30", "estado": "disponible" }
      ]
    }
  ]
}
```

`estado` es `disponible`, `reservado` (hay una sesión pendiente), `ocupado` (hay una confirmada) o `sin_bloque`.

---

## 5. Issues de la revisión 2

**Milestone:** `Revisión 2 — Funcionalidades` (vence 2026-11-12)

Labels:

| Grupo | Labels |
|---|---|
| Tipo | `tipo:feature` `tipo:ui` `tipo:bd` `tipo:docs` `tipo:config` `tipo:test` `tipo:bug` |
| Área | `area:repo` `area:auth` `area:catalogo` `area:asesor` `area:sesiones` `area:resenas` `area:estadisticas` `area:admin` `area:ui` |
| Prioridad | `prioridad:bloqueante` (otros issues esperan a este) · `prioridad:alta` (lo exige la revisión) · `prioridad:media` (se puede recortar) |

---

#### ISS-15 · Preparar la base común de la revisión 2

- **Responsable:** P4 (@cesardzul-byte)
- **Dependencias:** ninguna
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.1 Agendar con empalmes
- **Labels:** `tipo:config` `area:repo` `prioridad:bloqueante`
- **Estimación:** 0.5 h

**Descripción.** Las páginas nuevas necesitan leer formularios, responder JSON y comparar contra la fecha de hoy. Este issue lo deja en un solo lugar antes de que cuatro personas lo copien, que es lo primero que se mira en modularidad. También corrige la zona horaria (supuesto 7) y crea el modelo `Sesion`, para que P2 y P4 agreguen métodos en paralelo sin chocar.

**Entrega esperada.**
- `app/includes/helpers.php`:
  - `textoPost()` y `enteroPost()`, movidas desde `public/disponibilidad.php`;
  - `responderJson(array $datos, int $codigo = 200): never`, que manda el código HTTP, el encabezado `application/json; charset=utf-8` y el JSON.
- `app/includes/bootstrap.php`:
  - `date_default_timezone_set('America/Merida')`;
  - incluye `horario.php`.
- `public/disponibilidad.php`: sin las dos funciones movidas y sin su `require` de `horario.php` (si se queda, PHP falla por función redeclarada).
- `public/materia.php`: usa `DIAS_SEMANA` y `normalizarHora()` en lugar de `NOMBRES_DIA` y `LONGITUD_HORA_CORTA`.
- `app/models/Sesion.php` con `ESTADOS_QUE_RESERVAN = ['pendiente', 'confirmada']` y `buscar(int $id): ?array`.
- Se borra `app/views/auth/.gitkeep`, que ya no hace falta.

**Limitaciones.**
- No cambia el comportamiento de ninguna página.
- La zona horaria va en `bootstrap.php` y no en `config.php`: ese archivo no está en Git y una constante nueva rompería a quien no lo vuelva a copiar.

**Criterios de aceptación.**
- [ ] `php -d zend.assertions=1 tests/horario_check.php` sigue pasando.
- [ ] Se repiten DIS-02 e IDX-05 de `pruebas_revision1.md` con el mismo resultado.
- [ ] Con el `php.ini` de XAMPP sin tocar, `date('P')` en una página devuelve `-06:00`.
- [ ] `responderJson(['ok' => true], 401)` responde 401 con JSON válido.
- [ ] `textoPost` y `enteroPost` solo están definidas en `helpers.php`.

---

#### ISS-16 · Agregar los datos demo de sesiones y reseñas

- **Responsable:** P1 (@Luis-villanueva-valdivia)
- **Dependencias:** ninguna
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.1 Agendar con empalmes
- **Labels:** `tipo:bd` `area:sesiones` `area:resenas` `prioridad:bloqueante`
- **Estimación:** 0.5 h

**Descripción.** Mis sesiones, reseñas y la demo necesitan sesiones en los cuatro estados sin esperar a que exista el agendado. También deja preparados los pasos que se hacen en vivo en la presentación.

**Entrega esperada.**
- `database/seed.sql`, al final, con estas 8 sesiones. Todas caen dentro de un bloque demo del asesor:

  | # | Alumno | Asesor | Materia | Fecha | Horario | Estado | Para qué sirve |
  |---|---|---|---|---|---|---|---|
  | 1 | Juan (5) | Luis (2) | Cálculo Diferencial (1) | lun 19/10 | 09:00–10:00 | completada | Con reseña de 5 y comentario |
  | 2 | María (6) | Laura (3) | Álgebra Superior (3) | mar 20/10 | 10:00–11:00 | completada | Con reseña de 4 y comentario |
  | 3 | Carlos (7) | Pedro (4) | Programación Estructurada (5) | vie 23/10 | 08:00–09:00 | completada | Con reseña de 3 sin comentario |
  | 4 | Juan (5) | Pedro (4) | Cálculo Diferencial (1) | lun 26/10 | 13:00–14:00 | completada | Sin reseña: se califica en la demo |
  | 5 | María (6) | Luis (2) | Cálculo Diferencial (1) | lun 26/10 | 09:00–10:00 | cancelada | Historial |
  | 6 | Juan (5) | Luis (2) | Cálculo Integral (2) | mié 28/10 | 14:00–15:00 | confirmada | Ya pasó: se completa en la demo |
  | 7 | Juan (5) | Luis (2) | Cálculo Diferencial (1) | lun 07/12 | 09:00–10:00 | pendiente | Se confirma en la demo; "Reservado" en la tabla |
  | 8 | María (6) | Pedro (4) | Programación Orientada a Objetos (6) | mié 09/12 | 11:00–12:00 | confirmada | "Ocupado" en la tabla |

**Limitaciones.**
- `schema.sql` no cambia.
- Fechas fijas de 2026 (supuesto 8) y solo datos ficticios.
- Las estadísticas de la entrega final pueden agregar más filas; aquí solo van las que usa esta revisión.

**Criterios de aceptación.**
- [ ] Importar `schema.sql` y luego `seed.sql` en un servidor limpio no da errores, y reimportarlos tampoco.
- [ ] `SELECT estado, COUNT(*) FROM sesiones GROUP BY estado` da pendiente 1, confirmada 2, completada 4 y cancelada 1.
- [ ] `WEEKDAY(fecha) + 1` de cada sesión coincide con el `dia_semana` de un bloque del mismo asesor y materia, y sus horas caen dentro de ese bloque.
- [ ] Solo hay reseñas de sesiones completadas.

---

#### ISS-17 · Agregar los estilos de los flujos nuevos

- **Responsable:** P3 (@Pao-Parra)
- **Dependencias:** ninguna
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.1 Agendar con empalmes
- **Labels:** `tipo:ui` `area:ui` `prioridad:bloqueante`
- **Estimación:** 0.5 h

**Descripción.** Los componentes que usan varias pantallas nuevas, listos desde el miércoles para que nadie escriba CSS por su cuenta. Lo demás (formularios, botones, tablas, alertas) ya existe en `app.css`.

**Entrega esperada.**
- En `public/assets/css/app.css`, una sección nueva al final, con las variables de color que ya existen:
  - `.etiqueta-pendiente`, `.etiqueta-confirmada`, `.etiqueta-completada` y `.etiqueta-cancelada`;
  - `.tabla-semanal` con `.celda-disponible`, `.celda-reservado`, `.celda-ocupado` y `.celda-sin-bloque`;
  - `.calificacion`, para mostrar "★ 4.5 · 3 reseñas".
- Un ejemplo de cada una en `public/ui.php`.

**Limitaciones.**
- No crea páginas ni lógica. La tabla semanal se arma en ISS-21.
- Sin librerías. El estado nunca se indica solo con color: cada celda lleva su texto.

**Criterios de aceptación.**
- [ ] `ui.php` muestra las cuatro etiquetas, las cuatro celdas y la calificación.
- [ ] El texto tiene un contraste de al menos 4.5:1 en todas ellas.
- [ ] No hay colores sueltos: todo sale de variables de `:root`.

---

#### ISS-18 · Agendar una sesión con validación de empalmes

- **Responsable:** P4 (@cesardzul-byte)
- **Dependencias:** ISS-15, ISS-16
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.1 Agendar con empalmes
- **Labels:** `tipo:feature` `area:sesiones` `prioridad:alta`
- **Estimación:** 1.5 h

**Descripción.** Es la funcionalidad central y lo que distingue al proyecto (RF-12, RN-03, RN-05 y RN-06). El alumno pide una sesión con un asesor, en una fecha concreta y dentro de uno de sus bloques. El sistema la rechaza si choca con otra sesión del asesor o del propio alumno, aunque dos personas la pidan al mismo tiempo.

**Entrega esperada.**
- `app/includes/horario.php`, con funciones puras:
  - `dentroDeBloque($inicio, $fin, array $bloques): bool`;
  - `errorDeFecha(string $fecha, string $inicio, DateTimeImmutable $ahora): ?string`: la fecha existe, no es domingo y no ha pasado.
- `tests/horario_check.php`: casos nuevos para las dos funciones, incluidos `2026-02-30`, un domingo, una hora de hoy que ya pasó y una sesión que sale del bloque por 30 minutos.
- `Sesion::agendar(array $datos): array`, con la transacción de §4. Reutiliza `validarBloque()` para el horario permitido y `seEmpalman()` para los choques. Devuelve los errores, o un arreglo vacío si guardó. Crea la sesión en `pendiente`.
- `public/agendar.php` y `app/views/sesiones/agendar.php`, protegidas con `requiereRol('alumno')`:
  - reciben `?materia=&asesor=` y responden 404 si la materia o el asesor están inactivos, o si el asesor no la imparte;
  - muestran los bloques del asesor en esa materia, con su lugar (`Disponibilidad::deAsesor()` y `delDia()` ya devuelven lo necesario; no hace falta otra consulta);
  - formulario con `<input type="date">`, dos `<input type="time" step="1800">` y tema (obligatorio, 150 caracteres como máximo);
  - al guardar, un mensaje flash y redirección a `panel.php`. ISS-19 cambia ese destino a `mis_sesiones.php`.
- En `materia.php`, un botón "Agendar" por cada asesor con bloques, solo para alumnos. El visitante ve "Inicia sesión para agendar".
- El mensaje de empalme dice con qué choca, sin revelar datos de otros: "Ya tienes Cálculo Integral de 14:00 a 15:00" o "El asesor ya tiene una sesión de 09:00 a 10:00".
- `alumno_id` siempre sale de `usuarioActual()`, nunca del formulario.

**Limitaciones.**
- Solo crea sesiones. Confirmarlas, cancelarlas y completarlas es ISS-19.
- Sin reprogramar (se cancela y se agenda otra), sin sesiones recurrentes ni grupales, y sin límite de anticipación ni de duración aparte de caber en el bloque.
- El formulario debe funcionar solo. La tabla semanal (ISS-21) es una ayuda encima de él.
- `ponytail:` se bloquean las filas de los dos participantes en lugar de usar índices de rango o tablas de bloqueo. Alcanza de sobra para el volumen de este proyecto.

**Criterios de aceptación.**
- [ ] `php -d zend.assertions=1 tests/horario_check.php` pasa, y deja de pasar si se invierte una comparación de `dentroDeBloque()`.
- [ ] Con el bloque de Luis (lunes 09:00–11:00, Cálculo Diferencial), el próximo lunes se acepta 09:00–10:00. Se rechazan 08:30–09:30, 10:30–11:30, otro día de la semana y otra materia suya.
- [ ] Con esa sesión guardada, otro alumno recibe rechazo en 09:30–10:30 y se le acepta 10:00–11:00, porque solo se tocan.
- [ ] Un alumno no puede agendar con otro asesor en un horario que choca con una sesión suya de esa fecha.
- [ ] Al cancelar la sesión (a mano en phpMyAdmin hasta que exista ISS-19), su horario queda libre.
- [ ] El servidor rechaza, aunque se modifique el HTML: fecha pasada, domingo, fecha inexistente, horas fuera de 07:00–21:00, minutos distintos de :00 o :30, inicio ≥ fin y tema vacío o de más de 150 caracteres.
- [ ] Dos pestañas del mismo alumno que envían el mismo horario casi a la vez crean una sola sesión.
- [ ] Un asesor o administrador recibe 403 y un visitante va al login.

---

#### ISS-19 · Gestionar mis sesiones y sus cambios de estado

- **Responsable:** P2 (@oswaldo-castillo)
- **Dependencias:** ISS-15, ISS-16, ISS-17
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.2 Congelamiento
- **Labels:** `tipo:feature` `area:sesiones` `prioridad:alta`
- **Estimación:** 2 h

**Descripción.** Es la pantalla donde el alumno ve sus sesiones y el asesor atiende sus solicitudes (RF-13, RF-14, RF-15, RN-07 y RN-09). Cubre el UPDATE y el DELETE lógico de `sesiones`, y es donde después aparece el botón "Calificar".

**Entrega esperada.**
- En `Sesion.php`, con comentario de sección:
  - `TRANSICIONES`, una constante con la tabla del supuesto 6, y `transicionPermitida($rol, $estadoActual, $estadoNuevo): bool`, sin BD;
  - `deUsuario($usuarioId, $rol)`: sesiones con materia, nombre de la otra persona, y `calificacion` y `comentario` con `LEFT JOIN resenas`;
  - `cambiarEstado($id, $usuarioId, $rol, $estadoActual, $estadoNuevo): bool` con el `UPDATE` de §4.
- `tests/estados_check.php`: `assert`s sobre `transicionPermitida()` para cada rol y estado.
- El lugar se obtiene con un `LEFT JOIN disponibilidad` por asesor, materia, `WEEKDAY(fecha) + 1` y horas, porque `sesiones` no lo guarda. Si el asesor ya quitó ese bloque, se muestra "Por acordar".
- `public/mis_sesiones.php` y `app/views/sesiones/mis_sesiones.php`, protegidas con `requiereRol('alumno', 'asesor')`:
  - "Próximas" (`pendiente` y `confirmada`) e "Historial" (`completada` y `cancelada`);
  - fecha, horario, materia, otra persona, tema, lugar y etiqueta de estado;
  - solo los botones que permite su rol, y cancelar pide confirmación con `data-confirmar`.
- Completar exige, en el servidor, que ya haya pasado la hora de fin (RF-15).
- En `panel.php`, la tarjeta "Mis sesiones" para el alumno y "Solicitudes y sesiones" para el asesor. `agendar.php` ahora redirige a `mis_sesiones.php`.

**Limitaciones.**
- Nunca se borra una fila. Una sesión cancelada no se reactiva.
- `estado` guarda solo el estado actual; no hay bitácora de quién cambió qué ni cuándo.
- Sin motivo de cancelación y sin plazo para cancelar.
- Una sesión `pendiente` cuya fecha ya pasó no vence sola: el asesor la cancela.

**Criterios de aceptación.**
- [ ] `php -d zend.assertions=1 tests/estados_check.php` pasa, y deja de pasar si se le da "confirmar" al alumno.
- [ ] Luis ve la sesión 7 de los datos demo y la confirma; Juan la ve confirmada.
- [ ] Luis completa la sesión 6, que ya pasó. Una sesión confirmada futura (como la 8, con la cuenta de Pedro) no se puede completar.
- [ ] Al cancelar, la fila sigue en `sesiones` con estado `cancelada` y el horario se puede volver a agendar.
- [ ] Con F12, un alumno no puede confirmar ni completar, y nadie puede cambiar una sesión `completada` o `cancelada`.
- [ ] Cambiar el id por el de una sesión de otra persona responde 404.
- [ ] Un POST sin token CSRF se rechaza, y un administrador recibe 403.
- [ ] Se usa a 360 px sin scroll horizontal de la página.

---

#### ISS-20 · Crear la API de horarios

- **Responsable:** P4 (@cesardzul-byte)
- **Dependencias:** ISS-15, ISS-18
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.2 Congelamiento
- **Labels:** `tipo:feature` `area:sesiones` `prioridad:media`
- **Estimación:** 0.5 h

**Descripción.** El endpoint JSON del que se alimenta la tabla semanal de RF-05. Cubre `fetch` con JSON, que es tema del curso.

**Entrega esperada.**
- `estadoIntervalo($inicio, $fin, array $bloques, array $sesiones): string` en `horario.php`, con sus casos en `tests/horario_check.php`.
- `Sesion::deAsesorEntre($asesorId, $desde, $hasta)`: sesiones que reservan en ese rango, de cualquier materia.
- `public/api/horarios.php` con la respuesta de §4: lunes a sábado, de 07:00 a 21:00 cada 30 minutos.
  - 401 sin sesión y 403 si no es alumno, siempre en JSON;
  - 400 si falta un parámetro o `lunes` no es un lunes válido;
  - un intervalo que ya pasó se envía como `sin_bloque`, porque no se puede reservar.
- Se borra `public/api/.gitkeep`.

**Limitaciones.**
- Solo lectura con GET, así que no lleva CSRF. La tabla es informativa: quien decide es `Sesion::agendar()` (RF-05).
- No expone nombres, temas ni ningún dato de otras personas, solo el estado.
- No marca los choques del propio alumno; esos los rechaza `agendar()`.

**Criterios de aceptación.**
- [ ] `php -d zend.assertions=1 tests/horario_check.php` pasa con los casos de `estadoIntervalo()`.
- [ ] Con `asesor=2&materia=1&lunes=2026-12-07`, 09:00–09:30 es `reservado` y 10:00–10:30 es `disponible`. El martes completo es `sin_bloque`.
- [ ] Con `asesor=4&materia=6&lunes=2026-12-07`, el miércoles a las 11:00 es `ocupado`.
- [ ] `lunes=2026-12-08` responde 400 y `asesor=abc` responde 400, con JSON.
- [ ] Sin sesión responde 401 en JSON, sin redirigir.

---

#### ISS-21 · Mostrar la tabla semanal de horarios con fetch

- **Responsable:** P3 (@Pao-Parra)
- **Dependencias:** ISS-17, ISS-18, ISS-20
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.2 Congelamiento
- **Labels:** `tipo:ui` `area:sesiones` `prioridad:media`
- **Estimación:** 1 h

**Descripción.** La tabla que describe RF-05, dentro de la página de agendar. Es también la versión del MVP de RF-11: el alumno llega por materia y asesor, y aquí ve qué días y horas puede reservar.

**Entrega esperada.**
- En `app/views/sesiones/agendar.php`, un contenedor con `data-tabla-horarios` y los ids de asesor y materia, botones "Semana anterior" y "Semana siguiente", y la leyenda de los cuatro estados.
- En `public/assets/js/app.js`, con comentario de sección:
  - `cargarSemana(lunes)`, con `async`/`await`, `try`/`catch` y revisión de `response.ok`;
  - arma la tabla con `textContent`, nunca con `innerHTML`.
- Las celdas `disponible` son `<button>`, para que funcionen con teclado. Al elegir una, se llenan fecha, inicio y fin (30 minutos después) en el formulario.
- Si la petición falla, un aviso: "No se pudieron cargar los horarios. Puedes escribir la fecha y la hora en el formulario".

**Limitaciones.**
- Sin librerías de calendario, sin arrastrar para elegir y sin vista de mes.
- Es solo una ayuda: sin JavaScript sigue funcionando el formulario de ISS-18.

**Criterios de aceptación.**
- [ ] En la semana del 07/12 con Luis se ven los estados que indica ISS-20, cada uno con su texto.
- [ ] Cambiar de semana no recarga la página.
- [ ] Elegir una celda disponible llena el formulario, y al enviarlo se crea la sesión. La tabla la muestra como "Reservado" al volver a cargarla.
- [ ] Con la API detenida a mano (nombre de archivo cambiado) aparece el aviso, y no un error en consola sin manejar.
- [ ] A 360 px la tabla se desliza dentro de `.tabla-envoltura`, sin mover la página.
- [ ] Con JavaScript desactivado se puede agendar.

---

#### ISS-22 · Administrar las materias

- **Responsable:** P3 (@Pao-Parra)
- **Dependencias:** ISS-15, ISS-17
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.2 Congelamiento
- **Labels:** `tipo:feature` `area:admin` `area:catalogo` `prioridad:alta`
- **Estimación:** 1 h

**Descripción.** El administrador crea, edita y desactiva materias (RF-07, RF-08, RF-09 y RN-10). Tiene la misma forma que `public/disponibilidad.php`, que se usa como plantilla, por eso se estima en 1 h.

**Entrega esperada.**
- En `Materia.php`, con comentario de sección: `todas()`, `existeClave($clave, $excluirId = 0)`, `crear()`, `actualizar()` y `cambiarActiva()`.
- `public/admin_materias.php` y `app/views/admin/materias.php`, protegidas con `requiereRol('admin')`:
  - tabla con clave, nombre, estado y botones Editar y Desactivar o Activar;
  - formulario para crear y editar: clave (obligatoria, única, 50 caracteres como máximo) y nombre (obligatorio, 100 como máximo).
- Una tarjeta "Materias" en el panel del administrador.
- Se borra `app/views/admin/.gitkeep`.

**Limitaciones.**
- Nunca se borra una materia.
- Sin CRUD de licenciaturas y sin asignar asesores desde aquí.

**Criterios de aceptación.**
- [ ] Una materia nueva aparece en el inicio con "Sin asesores aún".
- [ ] Una clave repetida se rechaza con un mensaje junto al campo, también al editar. Editar sin cambiar la clave no choca consigo misma.
- [ ] Editar el nombre no altera las sesiones que ya la usan: siguen visibles con el nombre nuevo.
- [ ] Al desactivarla, sale del inicio, `materia.php` y `agendar.php` responden 404, y sus sesiones siguen en el historial. Al activarla, regresan sus asesores y bloques.
- [ ] Un alumno o asesor recibe 403 y un visitante va al login.
- [ ] Todo formulario usa `csrfCampo()` y la página llama a `verificarCsrf()`.

---

#### ISS-23 · Administrar los usuarios

- **Responsable:** P2 (@oswaldo-castillo)
- **Dependencias:** ISS-15, ISS-17
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.2 Congelamiento
- **Labels:** `tipo:feature` `area:admin` `area:auth` `prioridad:alta`
- **Estimación:** 1.5 h

**Descripción.** El administrador consulta las cuentas, corrige sus datos y desactiva las que no deben usarse (RF-10 y RN-10). Es además el control sobre las cuentas de asesor, ya que el registro no pide aprobación (supuesto 5).

**Entrega esperada.**
- En `Usuario.php`, con comentario de sección: `todos(?string $rol)`, sin `password_hash` en el `SELECT`; `actualizarDatos($id, $datos)` y `cambiarActivo($id, bool $activo)`.
- `public/admin_usuarios.php` y `app/views/admin/usuarios.php`, protegidas con `requiereRol('admin')`:
  - tabla con nombre, correo, matrícula, rol, licenciatura, semestre y estado;
  - filtro por rol con `?rol=`;
  - formulario para editar nombre, apellidos, licenciatura y semestre, con los mismos límites del registro (`Usuario::LONGITUD_MAXIMA_NOMBRE` y demás);
  - botones Desactivar o Activar, con confirmación.
- Una tarjeta "Usuarios" en el panel del administrador.

**Limitaciones.**
- El rol no se cambia: quitarle el rol a un asesor dejaría materias, bloques y sesiones incoherentes. RF-10 se corrige en ISS-26.
- Las cuentas de administrador se ven, pero no se editan ni se desactivan aquí. Eso evita que el único administrador se quede fuera y no choca con los `CHECK` que dejan en NULL su licenciatura y semestre.
- Correo y matrícula no se editan. Sin restablecer contraseñas y sin crear cuentas desde aquí.
- Desactivar no toca sus sesiones: se conservan y la otra persona las puede cancelar.

**Criterios de aceptación.**
- [ ] Al desactivar a Juan, su sesión abierta termina en la siguiente petición (ya lo hace `requiereLogin()`), no puede entrar, y sus sesiones siguen en la BD.
- [ ] Al desactivar a un asesor, desaparece del inicio y de `materia.php`, y `agendar.php` con su id responde 404.
- [ ] Al activar la cuenta, todo regresa.
- [ ] Con F12, intentar desactivar a un administrador o enviar `rol=admin` no cambia nada.
- [ ] El HTML de la lista no contiene ningún hash.
- [ ] Un alumno o asesor recibe 403.
- [ ] Los conteos del panel del administrador cambian al desactivar una cuenta.

---

#### ISS-24 · Registrar reseñas y mostrar el promedio del asesor

- **Responsable:** P1 (@Luis-villanueva-valdivia)
- **Dependencias:** ISS-16, ISS-19
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.2 Congelamiento
- **Labels:** `tipo:feature` `area:resenas` `prioridad:alta`
- **Estimación:** 1.5 h

**Descripción.** Cierra el ciclo: el alumno califica una sesión completada una sola vez (RF-16 y RN-08), y el promedio del asesor se ve al elegir con quién agendar.

**Entrega esperada.**
- `app/models/Resena.php`:
  - `crear($sesionId, $calificacion, $comentario)`;
  - `promediosPorAsesor(): array`, en una sola consulta con `GROUP BY`, indexado por `asesor_id`, con promedio y total.
- `public/resena.php?sesion=ID` y `app/views/resenas/crear.php`, protegidas con `requiereRol('alumno')`:
  - calificación del 1 al 5 con `<select>` o radios, y comentario opcional de 500 caracteres como máximo, en una constante;
  - 404 si la sesión no existe o no es del alumno;
  - un aviso si no está `completada` o si ya tiene reseña;
  - la `UNIQUE` de `sesion_id` frena un doble envío: se atrapa la excepción y se muestra "Esta sesión ya tiene reseña".
- En `mis_sesiones.php`, el botón "Calificar" en las sesiones completadas del alumno que no tienen reseña.
- En `materia.php`, por asesor: "★ 4.5 · 3 reseñas" o "Sin reseñas".
- Se borra `app/views/resenas/.gitkeep`.

**Limitaciones.**
- Una reseña no se edita ni se borra, y el asesor no la responde.
- Los comentarios los ven solo el alumno y el asesor de esa sesión, en su historial. En público solo aparece el promedio.

**Criterios de aceptación.**
- [ ] Juan califica la sesión 4 de los datos demo; el botón desaparece y el promedio de Pedro cambia en `materia.php`.
- [ ] Volver a enviar el formulario, con "atrás" o en otra pestaña, no crea otra reseña ni muestra un error de PHP.
- [ ] Calificar una sesión pendiente, confirmada o cancelada se rechaza.
- [ ] Calificar una sesión de otro alumno responde 404.
- [ ] Una calificación de 0, de 6 o de `abc` se rechaza en el servidor, y un comentario vacío se acepta.
- [ ] Un comentario con `<script>` se muestra como texto.

---

#### ISS-25 · Proteger las materias con sesiones vivas

- **Responsable:** P1 (@Luis-villanueva-valdivia)
- **Dependencias:** ISS-15, ISS-16
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.2 Congelamiento
- **Labels:** `tipo:feature` `area:asesor` `prioridad:media`
- **Estimación:** 0.5 h

**Descripción.** Es la regla que ISS-11 dejó para esta revisión: un asesor no debe poder quitar una materia mientras tenga sesiones futuras de ella. También evita que publique horarios de una materia que el administrador ya desactivó.

**Entrega esperada.**
- `Sesion::hayVivasDeMateria($asesorId, $materiaId): bool`: sesiones `pendiente` o `confirmada` con fecha de hoy en adelante.
- `public/mis_materias.php`:
  - si hay sesiones vivas, no quita la materia y muestra cuántas debe atender antes;
  - lee el POST con `textoPost()` y `enteroPost()`.
- `AsesorMateria::materiasDe()` filtra `m.activa = 1`.

**Limitaciones.**
- Editar o borrar un bloque no revisa sesiones: las ya agendadas se conservan (decisión 3 de `modelo_relacional.md`).
- Quitar una materia sin sesiones vivas sigue borrando sus bloques en cascada.

**Criterios de aceptación.**
- [ ] Luis no puede quitar Cálculo Diferencial mientras exista la sesión 7. Si la cancela, sí puede.
- [ ] Sesiones pasadas, completadas o canceladas no impiden quitarla.
- [ ] Una materia desactivada no aparece en "Mis materias" ni en el formulario de "Mi disponibilidad".
- [ ] Enviar `materia_id[]=1` editando el HTML no quita ninguna materia.

---

#### ISS-26 · Entregar el diagrama ER final y alinear los documentos

- **Responsable:** P1 (@Luis-villanueva-valdivia)
- **Dependencias:** ninguna (las correcciones ya están decididas en este plan)
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.2 Congelamiento
- **Labels:** `tipo:docs` `tipo:bd` `prioridad:alta`
- **Estimación:** 1 h

**Descripción.** El diagrama vale 2 puntos en esta revisión. El esquema no cambió, así que el trabajo es dejarlo presentable y hacer que los documentos digan lo mismo que el código.

**Entrega esperada.**
- `docs/diagramas/diagrama_er.png` y `.drawio`: exportado con fondo blanco, y una última comparación contra `schema.sql` columna por columna.
- `docs/requisitos/modelo_relacional.md` y su PDF:
  - §3.1: quitar que el administrador edita carreras desde la aplicación;
  - §6: cambiar "revisión 2" por los issues reales (ISS-18 e ISS-24).
- `docs/requisitos/documento_requisitos.md` y su PDF. P2 es dueño del archivo y se le avisa en el issue:
  - §3 y RF-01: el registro público crea cuentas de alumno o asesor, nunca de administrador;
  - RN-01 y RNF-06: el dominio aplica a todo el registro público, no solo a alumnos;
  - RF-05: revisión 2;
  - RF-10: quitar "asignar roles";
  - RF-11: se busca por materia y asesor, y la tabla semanal muestra día y horario;
  - RF-13: el historial es el estado actual de cada sesión;
  - fecha de la portada.

**Limitaciones.**
- No agrega tablas ni columnas.
- Los manuales son de la entrega final.
- Solo se corrigen las diferencias con el código; el documento no se reescribe.

**Criterios de aceptación.**
- [ ] El diagrama muestra las 7 tablas con PK, FK y cardinalidades, y se lee impreso en tamaño carta.
- [ ] Toda columna de `schema.sql` aparece en el diagrama y en el modelo relacional, y viceversa.
- [ ] Cada PDF se generó del `.md` final y dice lo mismo.
- [ ] RF-01, RF-05, RF-10, RF-11 y RF-13 coinciden con lo que hace la aplicación.
- [ ] Las `(revisión 2)` que quedan en el modelo relacional ya no prometen funciones fuera del MVP.

---

#### ISS-27 · Ejecutar las pruebas de la revisión 2

- **Responsable:** P3 (@Pao-Parra)
- **Dependencias:** ISS-18, ISS-19, ISS-20, ISS-21, ISS-22, ISS-23, ISS-24, ISS-25
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.3 v0.2.0
- **Labels:** `tipo:test` `prioridad:alta`
- **Estimación:** 1 h

**Descripción.** Alguien que no escribió el backend prueba el ciclo completo, con una instalación limpia y una lista escrita. Las fallas se corrigen antes del miércoles.

**Entrega esperada.**
- `docs/planeacion/pruebas_revision2.md`, con el formato de `pruebas_revision1.md`. Entre 30 y 40 casos:
  - reimportar `schema.sql` y `seed.sql`;
  - agendar: dentro y fuera del bloque, empalmes de asesor y de alumno, horarios que solo se tocan, fecha pasada, domingo;
  - estados: confirmar, cancelar, completar, transiciones prohibidas, sesiones de otra persona;
  - reseñas: válida, repetida, de una sesión no completada, de otro alumno, calificación fuera de rango;
  - tabla semanal: los cuatro estados, cambio de semana y elegir una celda;
  - administración: materias, usuarios y permisos;
  - regresión: inicio de sesión con los 3 roles y DIS-02;
  - vista a 360 px de las pantallas nuevas.
- Por cada falla, un issue `tipo:bug` enlazado a este, asignado al responsable del issue original.

**Limitaciones.**
- No corrige errores y no repite los 75 casos de la revisión 1.
- Las únicas pruebas automáticas son `tests/horario_check.php` y `tests/estados_check.php`, que también se ejecutan.

**Criterios de aceptación.**
- [ ] Se hizo en una computadora distinta a la de P4, siguiendo solo el README.
- [ ] Todos los casos tienen resultado, y los bugs que impiden la demo están cerrados antes de ISS-28.
- [ ] Se probó en Chrome y en Firefox.

---

#### ISS-28 · Integrar, publicar la versión 0.2.0 y preparar la revisión 2

- **Responsable:** P4 (@cesardzul-byte)
- **Dependencias:** ISS-26, ISS-27
- **Milestone:** Revisión 2 — Funcionalidades
- **Hito:** H2.3 v0.2.0
- **Labels:** `tipo:config` `area:repo` `prioridad:alta`
- **Estimación:** 0.5 h

**Descripción.** Cierra la revisión: todo queda en `main`, el ER y los documentos se suben a la plataforma, y hay un guion para que los cuatro presenten su parte, lo que cuenta para trabajo en equipo.

**Entrega esperada.**
- PR de `develop` a `main` y etiqueta `v0.2.0`.
- En la plataforma del curso: el diagrama ER, el modelo relacional y el documento de requisitos en PDF, más el enlace al repositorio.
- `docs/planeacion/guion_revision2.md`: demo de unos 5 minutos que dice quién presenta qué y con qué cuenta demo:
  - P1: diagrama ER final; Juan califica la sesión 4;
  - P2: Luis confirma la sesión 7 y completa la 6; Ana desactiva un usuario;
  - P3: tabla semanal; Ana crea y desactiva una materia;
  - P4: Juan agenda una sesión y se rechaza un empalme en vivo.
- La computadora de la presentación tiene la BD reimportada y la app probada el mismo día, porque la demo cambia los datos.
- Al final del guion, una lista de pendientes para la planeación de la entrega final.

**Limitaciones.**
- No agrega funciones. Desde el mar 10/11 solo entran correcciones.

**Criterios de aceptación.**
- [ ] `main` tiene la etiqueta `v0.2.0` y coincide con `develop`.
- [ ] Los PDFs se subieron a la plataforma antes de la hora límite del 12/11.
- [ ] El guion le da una parte a cada integrante y se ensayó una vez, en 7 minutos o menos.
- [ ] Los issues del milestone Revisión 2 están cerrados, o movidos a Entrega final con una justificación.
