# Planeación del proyecto — Nexus FMAT (primera entrega)

Sistema de asesorías entre estudiantes de la FMAT: los asesores publican su disponibilidad por materia, los alumnos agendan sesiones y luego las califican. Lo que lo distingue es la validación de empalmes de horario y un panel de estadísticas con las materias más solicitadas.

Equipo:

| Clave | Usuario | Enfoque |
|---|---|---|
| P1 | @Luis-villanueva-valdivia | Base de datos y modelos |
| P2 | @oswaldo-castillo | Autenticación, sesiones y documento de requisitos |
| P3 | @Pao-Parra | Interfaz (HTML, CSS, JS) y pruebas de usuario |
| P4 | @cesardzul-byte | Coordinación, arquitectura, empalmes e integración |

Revisiones:

| Revisión | Fecha | Qué se entrega |
|---|---|---|
| Revisión 1 | jue 29/10/2026 | Requisitos del software, diagrama entidad-relación y primer avance de código: al menos el index, la autenticación y una funcionalidad |
| Revisión 2 | jue 12/11/2026 | Código de las demás funcionalidades y componentes |
| Entrega final | jue 26/11/2026 | Aplicación integrada, base de datos y manuales de usuario y técnico |

---

## 1. Supuestos

1. **Fechas.** El trabajo de la revisión 1 va del vie 16/10 al mié 28/10. El jueves solo se presenta.
2. **Diagrama ER.** Las fechas de la especificación lo piden en la revisión 1, pero la tabla de puntos lo pone en la segunda. Se entrega en la revisión 1 (ISS-04) y en la revisión 2 se entrega su versión final con los cambios que haya.
3. **Tecnología.** PHP 8 con PDO, MySQL/MariaDB (XAMPP), HTML, CSS y JavaScript sin frameworks, sin librerías de CSS y sin Composer. Así se cubren los temas del curso, que valen el 20 % de la calificación de código.
4. **La funcionalidad de la revisión 1** es **publicar la disponibilidad por materia con validación de empalmes** (ISS-11 e ISS-12) y su consulta pública (ISS-09). Es la base del agendado de la revisión 2 y adelanta lo que distingue al proyecto.
5. **Roles.** Hay alumno, asesor y administrador, y cada usuario tiene un solo rol. Al registrarse solo se puede elegir alumno o asesor. El administrador se crea con los datos demo.
6. **Disponibilidad semanal.** Un bloque es día de la semana (lunes a sábado), hora de inicio, hora de fin y lugar, ligado a una materia que el asesor imparte. En la revisión 2 las sesiones se agendan en una fecha concreta dentro de un bloque. Las horas van de 07:00 a 21:00 en múltiplos de 30 minutos.
7. **Ramas.** `develop` es la rama de integración (y la predeterminada). Cada issue se trabaja en su propia rama y entra a `develop` por PR con una aprobación. `main` solo recibe `develop` al cerrar cada revisión, con etiqueta (`v0.1.0`, `v0.2.0`, `v1.0.0`).
8. **Revisión cruzada.** P1 revisa los PR de P2, P2 los de P3, P3 los de P4 y P4 los de P1. Si el revisor asignado no contesta en 24 h, puede aprobar cualquier otro. Cada persona fusiona sus propios PR, así su trabajo queda visible para la coevaluación.
9. **Archivos compartidos.** Cada archivo tiene un dueño, que es quien lo crea. Los demás solo agregan funciones o secciones al final, con un comentario de sección. Dueños: `README.md` → P4, `app.css` y `app.js` → P3, `Usuario.php` → P2, `Materia.php` → P1.
10. **IDs de issues.** Los issues se crean en orden (ISS-01 … ISS-14) antes de abrir el primer PR. Si el repositorio no tiene issues ni PR previos, ISS-NN = #NN. Si no, se suma el desplazamiento al copiar las dependencias.
11. **Carga.** Son entre 11 y 13 h por persona en 13 días (tabla de §3). P4 además coordina y revisa PR.
12. **Plan de recorte.** Si el lun 26/10 algo va atrasado, se quita en este orden: (1) el filtro de búsqueda del index y los conteos del panel de administrador; (2) la página de detalle de materia (ISS-09 se queda solo con el index). Nunca se recortan ISS-03, ISS-04, ISS-07 ni ISS-12, porque son justo lo que pide la revisión.

---

## 2. Fases e hitos de todo el proyecto

Cada revisión es un milestone de GitHub. Los hitos son puntos de control internos.

| Fase (milestone) | Periodo (vence) | Objetivo | Hitos |
|---|---|---|---|
| **Revisión 1 — Requisitos, ER y primer avance** | 16/10 al 28/10 (vence 2026-10-29) | El documento de requisitos y el ER están listos. La app instala desde el README, tiene index, registro, inicio y cierre de sesión, panel por rol, y el asesor publica disponibilidad con validación de empalmes. | **H1.1 Base lista** (mar 20/10) · **H1.2 Funcionalidades completas** (lun 26/10) · **H1.3 Revisión lista, v0.1.0** (mié 28/10) |
| **Revisión 2 — Funcionalidades** | 30/10 al 11/11 (vence 2026-11-12) | Funciona el ciclo completo publicar → agendar → realizar → calificar, con CRUD en todas las entidades y empalmes de sesiones. | **H2.1 Agendar con empalmes** (jue 05/11) · **H2.2 Congelamiento** (mar 10/11) · **H2.3 v0.2.0** (mié 11/11) |
| **Entrega final — Integración y documentación** | 13/11 al 25/11 (vence 2026-11-26) | Panel de estadísticas completo, interfaz pulida, pruebas completas y manuales de usuario y técnico. | **H3.1 Congelamiento de funciones** (jue 19/11) · **H3.2 Manuales listos** (lun 23/11) · **H3.3 v1.0.0 entregada** (mié 25/11) |

### Alcance de la revisión 2 (se detalla el 30/10 en `planeacion_segundaEntrega.md`)

| Componente | Responsable tentativo |
|---|---|
| Agendar sesión: el alumno elige materia, asesor, fecha y hora dentro de un bloque. Se valida que no choque con sesiones del asesor ni del alumno en esa fecha, en una transacción. Los horarios libres llegan por `fetch` desde `public/api/horarios.php` (JSON). | P4 |
| Mis sesiones (alumno y asesor): confirmar, rechazar, cancelar y marcar como completada (UPDATE de estado; cancelar en vez de borrar). Perfil: editar datos y cambiar contraseña. | P2 |
| Reseñas: calificar de 1 a 5, con comentario, una sesión completada (una sola vez por sesión). El promedio del asesor aparece en el detalle de materia. ER y modelo relacional actualizados. Datos demo de sesiones y reseñas. | P1 |
| Administración: CRUD de materias y licenciaturas, y activar o desactivar usuarios. Primera versión de estadísticas: tabla de materias más solicitadas. Vistas y estilos de los flujos nuevos. | P3 |

### Alcance de la entrega final (se detalla el 13/11 en `planeacion_entregaFinal.md`)

| Componente | Responsable tentativo |
|---|---|
| Panel de estadísticas: materias más solicitadas, solicitudes por licenciatura, horarios con más demanda, asesores mejor calificados y tasa de cancelación, con filtro por periodo y gráficas. Los datos salen de `public/api/estadisticas.php`. | P3 (vista) · P1 (consultas) |
| Pulido de la interfaz: estados vacíos, mensajes de error, responsivo y accesibilidad. | P3 |
| Pruebas completas (CRUD, autenticación, sesiones y empalmes) y corrección de errores. | Todos |
| Manual de usuario por rol con capturas (`docs/manuales/`). | P2 · P3 |
| Manual técnico: instalación, arquitectura, base de datos, estructura y endpoints (`docs/manuales/`). | P4 · P1 |
| Entrega: etiqueta `v1.0.0`, zip, respaldo `.sql` y PDFs en la plataforma. Coevaluación. | P4 |

### Cobertura de la rúbrica

| Rubro | Dónde se cubre |
|---|---|
| Documento de requisitos (3 pts) | ISS-03 |
| Diagramas entidad-relación (2 pts) | ISS-04; versión final en la revisión 2 |
| Codificación (3 pts y los 20 del desglose) | ISS-02 e ISS-05 a ISS-12, más las revisiones 2 y final |
| Trabajo en equipo (2 pts) | Ramas por persona, revisión cruzada (supuesto 8) y una parte de la demo para cada integrante (ISS-14) |
| Funcionalidad (40 %) | CRUD de disponibilidad (rev. 1); CRUD de sesiones, reseñas y catálogos (rev. 2) |
| Temas del curso (20 %) | Formularios y sesiones en PHP, PDO, validación con HTML5 y JS, CSS responsivo, `fetch` con JSON (rev. 2) |
| Modularidad (20 %) | Arranque común, helpers, un modelo por tabla, vistas con layout (§4) |
| Interfaz (20 %) | ISS-06 (layout y estilos comunes) |

---

## 3. Calendario de la revisión 1

| Issue | Actividad | Resp. | Depende de | Fecha objetivo | Est. |
|---|---|---|---|---|---|
| ISS-01 | Convenciones, ramas y tablero | P4 | — | sáb 17/10 | 2 h |
| ISS-02 | Arranque de PHP | P4 | 01 | lun 19/10 | 3 h |
| ISS-04 | Diagrama ER y modelo relacional | P1 | — | lun 19/10 | 4 h |
| ISS-03 | Documento de requisitos | P2 | 04 | borrador mar 20/10 · final lun 26/10 | 6 h |
| ISS-05 | Script de BD, datos demo y catálogo | P1 | 02, 04 | mar 20/10 | 4 h |
| ISS-06 | Layout y hoja de estilos | P3 | 02 | mar 20/10 | 5 h |
| ISS-07 | Inicio/cierre de sesión, guardias y CSRF | P2 | 05, 06 | jue 22/10 | 4 h |
| ISS-09 | Index y detalle de materia | P3 | 05, 06 | vie 23/10 | 4 h |
| ISS-10 | Panel por rol | P3 | 07 | vie 23/10 | 2 h |
| ISS-08 | Registro | P2 | 07 | sáb 24/10 | 3 h |
| ISS-11 | Materias del asesor | P1 | 05, 06, 07 | sáb 24/10 | 3 h |
| ISS-12 | Disponibilidad con empalmes | P4 | 05, 06, 07 | lun 26/10 | 6 h |
| ISS-13 | Pruebas de la revisión 1 | P3 | 07–12 | mar 27/10 | 2 h |
| ISS-14 | Versión 0.1.0 y entrega | P4 | 03, 04, 13 | mié 28/10 | 2 h |

Carga: P1 11 h · P2 13 h · P3 13 h · P4 13 h. Desde el cierre del lun 26/10 solo entran correcciones.

- **H1.1 Base lista (mar 20/10):** ISS-01, 02, 04, 05 y 06 fusionados, más el borrador de ISS-03. Cualquiera clona, importa la BD y ve el layout en `localhost`.
- **H1.2 Funcionalidades completas (lun 26/10):** ISS-03 e ISS-07 a 12 fusionados. Las cuentas demo entran, el asesor publica bloques, los empalmes se rechazan y el alumno ve los horarios.
- **H1.3 Revisión lista (mié 28/10):** ISS-13 e ISS-14. `v0.1.0` está en `main` y los PDFs están en la plataforma.

---

## 4. Arquitectura y estructura

Se usa la estructura del commit `2dd9cf4`. Cada página de `public/` hace de controlador: valida, llama al modelo y muestra una vista. No hay router ni framework.

```
nexus-fmat/
├── app/                  # no se sirve al navegador (app/.htaccess lo bloquea)
│   ├── config/           # config.example.php (se sube) · config.php (ignorado)
│   ├── includes/         # bootstrap, conexión, helpers, auth, reglas de horario
│   ├── models/           # una clase por tabla con métodos estáticos (PDO)
│   └── views/            # HTML de cada página; layouts/ = header y footer
├── public/               # lo único que abre el navegador (BASE_URL)
│   ├── *.php             # una página = un controlador
│   ├── api/              # endpoints JSON para fetch (desde la revisión 2)
│   └── assets/css|js|img
├── database/             # schema.sql y seed.sql
├── docs/                 # requisitos/, diagramas/, manuales/, planeacion/
└── tests/                # verificaciones que se ejecutan con php (ISS-12)
```

Forma de toda página de `public/`:

```php
<?php
require __DIR__ . '/../app/includes/bootstrap.php';
requiereRol('asesor');                      // o requiereLogin(), o nada si es pública

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    // validar → modelo → flash() → redirigir()  (POST-Redirect-GET)
}
vista('asesor/disponibilidad', ['titulo' => 'Mi disponibilidad', 'bloques' => $bloques]);
```

La sesión guarda `$_SESSION['usuario'] = ['id' => int, 'nombre' => string, 'rol' => 'alumno'|'asesor'|'admin']` y se lee con `usuarioActual()`.

---

## 5. Issues de la revisión 1

**Milestone:** `Revisión 1 — Requisitos, ER y primer avance` (vence 2026-10-29)

Labels:

| Grupo | Labels |
|---|---|
| Tipo | `tipo:feature` `tipo:ui` `tipo:bd` `tipo:docs` `tipo:config` `tipo:test` `tipo:bug` |
| Área | `area:repo` `area:auth` `area:catalogo` `area:asesor` `area:sesiones` `area:resenas` `area:estadisticas` `area:admin` `area:ui` |
| Prioridad | `prioridad:bloqueante` (otros issues esperan a este) · `prioridad:alta` (lo exige la revisión) · `prioridad:media` (se puede recortar) |

---

#### ISS-01 · Definir convenciones, ramas y tablero del proyecto

- **Responsable:** P4 (@cesardzul-byte)
- **Dependencias:** ninguna
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:config` `area:repo` `prioridad:bloqueante`

**Descripción.** Fija cómo trabaja el equipo antes de escribir código: versiones, ramas, PR, nombres y qué va en cada carpeta. También crea en GitHub los labels, los milestones y los 14 issues de este plan, para que desde el segundo día cada quien trabaje en su rama sin pisar a nadie.

**Entrega esperada.**
- `docs/convenciones.md` con:
  - versión exacta de XAMPP, PHP y MariaDB que usarán las cuatro computadoras;
  - ramas `tipo/NN-descripcion` (por ejemplo, `feature/07-login`) que salen de `develop`;
  - commits `tipo: descripción` (`feat`, `fix`, `docs`, `db`, `style`, `chore`);
  - nombres: tablas y columnas en `snake_case` y en español sin acentos ni ñ (`resenas`), archivos en `snake_case`, modelos en `PascalCase` con métodos en `camelCase`;
  - la tabla de §4 y la forma de página;
  - las reglas de revisión cruzada y de dueños de archivo (supuestos 8 y 9);
  - la regla de borrar el `.gitkeep` de una carpeta al agregarle su primer archivo.
- `.github/pull_request_template.md` con el issue que cierra (`Closes #NN`), cómo probarlo, capturas si hay interfaz y un checklist: sin credenciales, solo consultas preparadas y salida escapada con `e()`.
- `README.md` con descripción, requisitos, instalación en XAMPP (clonar en `htdocs`, importar `schema.sql` y `seed.sql`, copiar `config.example.php` a `config.php`) y la URL local `http://localhost/nexus-fmat/public/`.
- En GitHub:
  - los labels de §5 y los 3 milestones con su fecha;
  - ISS-01 a ISS-14 creados en orden, cada uno con responsable, milestone y labels;
  - `main` y `develop` protegidas: sin push directo y con 1 aprobación para fusionar.

**Limitaciones.**
- No escribe PHP; eso es ISS-02. La sección de cuentas demo del README la agrega P1 en ISS-05.
- Si el repositorio es privado y el plan gratuito no permite proteger ramas, la regla queda escrita en `convenciones.md` y se cumple a mano.
- Tiempo máximo: 2 h, porque todo depende de este issue.

**Criterios de aceptación.**
- [ ] `docs/convenciones.md` indica las versiones exactas, el formato de ramas y el de commits.
- [ ] Un push directo a `develop` o a `main` se rechaza, y un PR sin aprobación no se puede fusionar (o está documentada la excepción).
- [ ] Existen los 3 milestones con su fecha y todos los labels de §5.
- [ ] Existen ISS-01 a ISS-14, cada uno con responsable, milestone y labels.
- [ ] Los cuatro integrantes comentaron en el issue que clonaron el repo y tienen la misma versión de XAMPP.

---

#### ISS-02 · Crear el arranque de PHP: configuración, conexión y helpers

- **Responsable:** P4 (@cesardzul-byte)
- **Dependencias:** ISS-01
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:config` `area:repo` `prioridad:bloqueante`

**Descripción.** Es un solo archivo que cada página incluye para tener configuración, conexión, sesión segura y funciones comunes. Así ninguna página repite la conexión ni el manejo de la sesión, que es lo primero que se mira en modularidad.

**Entrega esperada.**
- `app/config/config.example.php` (se sube) con `DB_HOST`, `DB_NAME` (`nexus_fmat`), `DB_USER`, `DB_PASS`, `BASE_URL` (`/nexus-fmat/public`) y `DOMINIO_CORREO` (`alumnos.uady.mx`). `config.php` ya está en `.gitignore`.
- `app/includes/bootstrap.php`:
  - carga la configuración;
  - abre la sesión con cookie `httponly` y `SameSite=Lax`;
  - muestra errores solo en desarrollo;
  - incluye los helpers y los modelos.
- `app/includes/db.php`: `db(): PDO` con una sola conexión (`utf8mb4`, `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES = false`).
- `app/includes/helpers.php`:
  - `e()` (escapa con `htmlspecialchars`), `url($ruta)` y `redirigir($ruta)`;
  - `flash($tipo, $mensaje)` y `obtenerFlash()`;
  - `usuarioActual(): ?array`;
  - `vista($nombre, $datos)`: incluye el header, `app/views/$nombre.php` y el footer;
  - `abortar($codigo, $mensaje)`: manda el código HTTP y muestra `app/views/error.php`.
- `app/views/error.php`, que es una vista mínima.
- `app/.htaccess` con `Require all denied`.
- `public/index.php` temporal que ejecuta `SELECT 1` y muestra "Conexión OK". Lo reemplaza ISS-09.

**Limitaciones.**
- No incluye autenticación (ISS-07) ni estilos (ISS-06). Mientras ISS-06 no exista, `vista()` usa un header y un footer vacíos.
- Sin router, sin framework y sin Composer: cada archivo de `public/` es su propio controlador.

**Criterios de aceptación.**
- [ ] Con `config.php` copiado del ejemplo y la BD creada, `public/index.php` muestra "Conexión OK" en las cuatro computadoras.
- [ ] Con credenciales incorrectas aparece un mensaje genérico, sin la contraseña de la BD.
- [ ] `git status` no muestra `config.php` después de crearlo.
- [ ] `http://localhost/nexus-fmat/app/config/config.php` responde 403.
- [ ] `e('<script>')` devuelve `&lt;script&gt;`, y `abortar(404, 'No existe')` responde con código 404.

---

#### ISS-03 · Redactar el documento de requisitos

- **Responsable:** P2 (@oswaldo-castillo)
- **Dependencias:** ISS-04 (solo para la sección de base de datos; lo demás empieza el primer día)
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:docs` `prioridad:alta`

**Descripción.** Es el documento que vale 3 puntos y que se sube a la plataforma. Además fija las reglas de negocio (empalmes, reseñas, cancelaciones) que implementa la revisión 2, así que es la referencia cuando haya dudas.

**Entrega esperada.**
- `docs/requisitos/documento_requisitos.md` y su PDF, con estas secciones:
  1. **Portada:** nombre de la aplicación (Nexus FMAT), equipo, materia y fecha.
  2. **Descripción del problema:** muchos alumnos batallan con Cálculo, Álgebra o Programación, y las asesorías se organizan por WhatsApp. Consecuencias: horarios que chocan, no queda registro, no hay retroalimentación y la facultad no sabe qué materias se piden más.
  3. **Objetivo y alcance:** qué incluye y qué no (sin videollamadas, pagos ni notificaciones por correo).
  4. **Usuarios y roles:** visitante, alumno, asesor y administrador, y qué puede hacer cada uno.
  5. **Funcionalidad principal:** el flujo publicar disponibilidad → buscar → agendar → realizar → calificar, más el panel de estadísticas.
  6. **Requisitos funcionales (RF-01…):** cada uno con descripción, rol, operación (C, R, U, D) y revisión en que se entrega (1, 2 o final).
  7. **Reglas de negocio (RN-01…):**
     - empalme de disponibilidad: mismo asesor, mismo día;
     - empalme de sesiones: asesor y alumno, misma fecha;
     - una sesión cae dentro de un bloque del asesor;
     - horario de 07:00 a 21:00 en múltiplos de 30 minutos, de lunes a sábado;
     - una reseña por sesión, solo para sesiones completadas, con calificación de 1 a 5;
     - una sesión se cancela cambiando su estado, no se borra;
     - usuarios y materias se desactivan en vez de borrarse.
  8. **Requisitos no funcionales:** seguridad (`password_hash`, consultas preparadas, escape de salida, CSRF, sesiones), versiones actuales de Chrome, Firefox y Edge, diseño responsivo desde 360 px y todo en español.
  9. **Integración FrontEnd–BackEnd:** un diagrama de navegador ↔ páginas PHP (formularios POST con redirección) ↔ modelos ↔ MySQL. A partir de la revisión 2 se suman endpoints JSON en `public/api/` que se consumen con `fetch` (horarios libres y estadísticas).
  10. **Base de datos:** las 7 tablas con su propósito y una referencia al diagrama de ISS-04.
  11. **Panel de estadísticas:** qué indicadores tiene y para qué le sirven a la facultad.

**Limitaciones.**
- No incluye el modelo relacional detallado (ISS-04) ni los manuales (entrega final).
- Máximo unas 8 páginas.
- Los requisitos de las revisiones 2 y final se describen, pero sus pantallas no se diseñan.
- El dominio de correo (`alumnos.uady.mx`) se confirma aquí. Si cambia, solo se edita `DOMINIO_CORREO`.

**Criterios de aceptación.**
- [ ] Tiene las cinco partes que pide la especificación: nombre, problema, funcionalidad, integración front/back y BD con cinco o más tablas.
- [ ] Cada RF indica rol, operación y revisión, y hay al menos un RF de crear, leer, actualizar y eliminar, además de los de autenticación y sesiones.
- [ ] La regla de empalme incluye la condición exacta (`inicio < otro_fin` y `fin > otro_inicio`) y aclara que dos bloques que solo se tocan (10–11 y 11–12) no se empalman.
- [ ] El borrador está en un PR el mar 20/10, y P1, P3 y P4 dejaron comentarios antes del vie 23/10.
- [ ] El PDF se generó a partir del `.md` final y dice lo mismo.

---

#### ISS-04 · Diseñar el diagrama entidad-relación y el modelo relacional

- **Responsable:** P1 (@Luis-villanueva-valdivia)
- **Dependencias:** ninguna
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:bd` `tipo:docs` `prioridad:bloqueante`

**Descripción.** Define las 7 tablas con sus atributos, llaves y relaciones. El script SQL, los modelos y la sección de BD de los requisitos salen de aquí, por eso es lo primero.

**Entrega esperada.**
- `docs/diagramas/diagrama_er.png` y su archivo fuente editable (draw.io, MySQL Workbench o dbdiagram.io).
- `docs/requisitos/modelo_relacional.md` y su PDF. Por cada tabla: columnas con tipo, si aceptan nulo, llave, restricciones (UNIQUE, CHECK, FK con su `ON DELETE`) y descripción. También las cardinalidades.
- Contenido mínimo. P1 puede ajustar nombres y avisa en el issue si cambia una tabla:
  - `licenciaturas` (id, nombre UNIQUE)
  - `usuarios` (id, nombre, apellidos, matricula UNIQUE, correo UNIQUE, password_hash, rol ENUM alumno/asesor/admin, licenciatura_id FK NULL, semestre, activo, creado_en)
  - `materias` (id, clave UNIQUE, nombre, activa)
  - `asesor_materia` (asesor_id FK, materia_id FK; PK compuesta)
  - `disponibilidad` (id, asesor_id, materia_id, dia_semana 1–6 [1 = lunes, como `date('N')` en PHP], hora_inicio, hora_fin, lugar)
    - FK compuesta (asesor_id, materia_id) → `asesor_materia` con `ON DELETE CASCADE`
    - `CHECK hora_inicio < hora_fin`
    - se borra de verdad, porque ninguna tabla la referencia
  - `sesiones` (id, alumno_id FK, asesor_id FK, materia_id FK, fecha, hora_inicio, hora_fin, tema, estado ENUM pendiente/confirmada/completada/cancelada, creado_en)
  - `resenas` (id, sesion_id FK UNIQUE, calificacion CHECK 1–5, comentario, creado_en)
- Justificación breve de tres decisiones: desactivar en vez de borrar usuarios y materias, disponibilidad semanal, y por qué `sesiones` guarda asesor y materia en lugar de apuntar a un bloque (si el asesor cambia sus bloques, las sesiones ya agendadas se conservan).

**Limitaciones.**
- Solo las 7 tablas de la propuesta. No se agregan tablas como notificaciones o mensajes.
- Sin triggers ni procedimientos almacenados. Los empalmes se validan en PHP, porque MySQL no tiene restricciones de exclusión por rangos de tiempo.
- La versión final del ER se entrega en la revisión 2.

**Criterios de aceptación.**
- [ ] El diagrama muestra las 7 tablas con PK, FK y cardinalidades, y se lee impreso en tamaño carta.
- [ ] Cada FK del diagrama aparece en el modelo relacional con su `ON DELETE`.
- [ ] Las reglas que la BD sí puede garantizar están como restricciones: correo y matrícula únicos, calificación de 1 a 5, una reseña por sesión y `hora_inicio < hora_fin`.
- [ ] No hay datos repetidos entre tablas, salvo las llaves.
- [ ] P4 aprobó el PR antes de que empiece ISS-05.

---

#### ISS-05 · Crear el script de la base de datos, los datos demo y los modelos de catálogo

- **Responsable:** P1 (@Luis-villanueva-valdivia)
- **Dependencias:** ISS-02, ISS-04
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:bd` `area:catalogo` `prioridad:bloqueante`

**Descripción.** Convierte el modelo en scripts que cualquiera importa en phpMyAdmin en un paso, con datos demo para desarrollar y presentar. También crea los modelos de solo lectura de licenciaturas y materias, que usan el registro, el index y el asesor.

**Entrega esperada.**
- `database/schema.sql`: `DROP DATABASE IF EXISTS` y `CREATE DATABASE nexus_fmat` en `utf8mb4`, y las 7 tablas InnoDB con todas las restricciones de ISS-04.
- `database/seed.sql` con:
  - las licenciaturas de la FMAT (Actuaría, Ciencias de la Computación, Enseñanza de las Matemáticas, Ingeniería de Software, Ingeniería en Computación y Matemáticas; confirmar con la oferta vigente);
  - al menos 10 materias (Cálculo Diferencial, Cálculo Integral, Álgebra Superior, Álgebra Lineal, Programación Estructurada, Programación Orientada a Objetos, Estructuras de Datos, Matemáticas Discretas, Probabilidad, etc.);
  - usuarios demo (1 administrador, 3 asesores y 3 alumnos) con la contraseña `Demo1234`, guardada con un hash generado con `password_hash`;
  - en `asesor_materia` y `disponibilidad`, al menos 2 materias y 2 bloques por asesor, para que ISS-09 tenga datos sin esperar a ISS-11 ni ISS-12.
- `app/models/Licenciatura.php` con `todas()`.
- `app/models/Materia.php` con `activas()`, `buscarPorId($id)` y `conNumeroAsesores()`.
- Sección "Cuentas demo" en el `README.md`.

**Limitaciones.**
- No crea los modelos de usuarios (ISS-07), materias del asesor (ISS-11) ni disponibilidad (ISS-12).
- Sin migraciones: si cambia el esquema, se edita `schema.sql` y se reimporta. Funciona mientras no haya datos reales.
- Solo datos ficticios. Los datos demo de sesiones y reseñas son de la revisión 2.

**Criterios de aceptación.**
- [ ] Importar `schema.sql` y luego `seed.sql` en un servidor limpio no da errores, y reimportarlos tampoco.
- [ ] Insertar a mano una reseña con calificación 6, un correo repetido o un bloque con inicio ≥ fin falla por restricción.
- [ ] `password_verify('Demo1234', $hash)` da verdadero para las 7 cuentas demo.
- [ ] `Materia::conNumeroAsesores()` devuelve, en una sola consulta, todas las materias activas con su número de asesores, incluidas las que tienen 0.
- [ ] Toda consulta que recibe parámetros es preparada.

---

#### ISS-06 · Crear el layout base y la hoja de estilos

- **Responsable:** P3 (@Pao-Parra)
- **Dependencias:** ISS-02 (para conectar el header y el footer; el CSS empieza el primer día)
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:ui` `area:ui` `prioridad:bloqueante`

**Descripción.** El encabezado, la navegación, el pie y los estilos comunes (formularios, tablas, botones, tarjetas y alertas) que usan todas las vistas. Así la app se ve igual en todas partes (la interfaz vale el 20 % del código) y nadie escribe CSS por su cuenta.

**Entrega esperada.**
- `app/views/layouts/header.php`:
  - `<!doctype html>`, `lang="es"`, viewport y título por página (`$titulo`);
  - enlace a la hoja de estilos y nombre "Nexus FMAT" con su logo;
  - navegación según la sesión: el visitante ve Inicio, Iniciar sesión y Registrarse; el usuario con sesión ve Inicio, Panel, su nombre y Cerrar sesión (un formulario POST que se conecta en ISS-07);
  - mensajes flash.
- `app/views/layouts/footer.php`: pie de página e inclusión de `app.js`.
- `public/assets/css/app.css`, un solo archivo con:
  - variables de color y tipografía en `:root` y un reset mínimo;
  - contenedor y navegación responsiva (menú móvil con `<details>`, sin JS);
  - formularios con errores por campo, botones, tablas, tarjetas y alertas de éxito, error e información.
- `public/assets/js/app.js`: confirmación antes de enviar en cualquier botón con `data-confirmar="mensaje"`.
- Logo en SVG o texto en `public/assets/img/`.
- `public/ui.php`: guía con un ejemplo de cada componente, para que los demás copien las clases. Se borra antes de la entrega final.

**Limitaciones.**
- Sin frameworks de CSS (Bootstrap, Tailwind) ni librerías de JS: se evalúa CSS y JS propios.
- No incluye las vistas de páginas concretas (ISS-07 a ISS-12).
- Los estilos de una página concreta solo se agregan si no alcanza con los componentes, y van al final de `app.css` en una sección comentada.

**Criterios de aceptación.**
- [ ] `public/ui.php` muestra un formulario con un error, los botones, una tabla, una tarjeta y las 3 alertas, todos con estilo.
- [ ] A 360 px de ancho no hay scroll horizontal y la navegación se puede usar.
- [ ] La navegación cambia entre visitante y usuario con sesión según `usuarioActual()`. Se prueba llenando `$_SESSION['usuario']` a mano mientras no exista ISS-07.
- [ ] Cada campo de formulario tiene su `<label>` y el texto tiene un contraste de al menos 4.5:1.
- [ ] Ninguna vista necesita escribir `<html>`, `<head>` ni `<nav>`: los pone `vista()`.

---

#### ISS-07 · Implementar inicio y cierre de sesión, protección de páginas y CSRF

- **Responsable:** P2 (@oswaldo-castillo)
- **Dependencias:** ISS-05, ISS-06
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:feature` `area:auth` `prioridad:bloqueante`

**Descripción.** Es la autenticación y el manejo de sesiones que pide la especificación. El usuario entra con correo y contraseña, la sesión recuerda quién es y qué rol tiene, y cada página protegida solo necesita una línea. Incluye la protección CSRF que usarán todos los formularios.

**Entrega esperada.**
- `app/models/Usuario.php` con `buscarPorCorreo($correo)` y `buscarPorId($id)`.
- `app/includes/auth.php`:
  - `iniciarSesion($usuario)`: regenera el ID de sesión y guarda id, nombre y rol;
  - `cerrarSesion()`;
  - `requiereLogin()`: sin sesión, manda al login con un aviso;
  - `requiereRol(...$roles)`: si el rol no corresponde, responde 403;
  - `csrfCampo()`, que imprime el campo oculto, y `verificarCsrf()`, que compara con `hash_equals` y responde 400 si falla.
- `public/login.php` y `app/views/auth/login.php`:
  - formulario de correo y contraseña;
  - un solo mensaje para cualquier error: "Correo o contraseña incorrectos";
  - los usuarios inactivos no entran;
  - conserva el correo escrito;
  - si ya hay sesión, manda a `panel.php`.
- `public/logout.php`: solo acepta POST con CSRF, cierra la sesión y manda al index con un aviso.

**Limitaciones.**
- Sin "recordarme", sin recuperación de contraseña y sin bloqueo por intentos. Se anotan como mejoras futuras en ISS-03.
- El registro es ISS-08.
- Las contraseñas solo se manejan con `password_hash` y `password_verify`.

**Criterios de aceptación.**
- [ ] Las cuentas demo de los tres roles entran y llegan a `panel.php`. Un correo o una contraseña incorrectos muestran el mismo mensaje.
- [ ] Un usuario con `activo = 0` no entra.
- [ ] El valor de la cookie `PHPSESSID` cambia al iniciar sesión.
- [ ] Una página con `requiereLogin()` manda al login si no hay sesión, y una con `requiereRol('asesor')` responde 403 a un alumno.
- [ ] Un POST sin token CSRF, o con el token alterado, se rechaza.
- [ ] Después de cerrar sesión, regresar con "atrás" y recargar no muestra el panel.
- [ ] Ninguna consulta concatena datos que vienen del usuario.

---

#### ISS-08 · Implementar el registro de alumnos y asesores

- **Responsable:** P2 (@oswaldo-castillo)
- **Dependencias:** ISS-07
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:feature` `area:auth` `prioridad:alta`

**Descripción.** El alta de cuenta (CREATE de usuarios), con validación en el servidor y en el navegador. El usuario elige si es alumno o asesor y cuál es su licenciatura. Al terminar, queda con la sesión iniciada.

**Entrega esperada.**
- En `Usuario.php`: `crear($datos): int` y `existeCorreoOMatricula($correo, $matricula)`.
- `public/registro.php` y `app/views/auth/registro.php` con nombre, apellidos, matrícula, correo, licenciatura (lista de `Licenciatura::todas()`), semestre, rol (alumno o asesor), contraseña y confirmación.
- Validación en el servidor, que es la que manda:
  - campos obligatorios;
  - correo con dominio `DOMINIO_CORREO` y matrícula solo con dígitos;
  - contraseña de 8 caracteres o más, igual a su confirmación;
  - correo y matrícula que no existan ya;
  - licenciatura existente;
  - rol solo alumno o asesor, aunque alguien modifique el formulario.
- Validación en el navegador: con atributos HTML5 (`required`, `type="email"`, `pattern`, `minlength`) y, para comparar las contraseñas, con `setCustomValidity` en `app.js`.
- Los errores aparecen junto a su campo y se conserva lo escrito, excepto las contraseñas.
- Al registrarse, `iniciarSesion()` y redirección al panel con un mensaje de bienvenida.

**Limitaciones.**
- Sin verificación de correo.
- La aprobación de asesores por el administrador se decide para la revisión 2.
- Editar el perfil (UPDATE) es de la revisión 2.

**Criterios de aceptación.**
- [ ] Un registro válido crea el usuario con la contraseña hasheada (nunca en texto plano) y deja la sesión iniciada.
- [ ] Un correo o una matrícula repetidos muestran el error en ese campo y no se crea nada.
- [ ] Enviar `rol=admin` editando el HTML se rechaza.
- [ ] Un correo de otro dominio se rechaza.
- [ ] Con JavaScript desactivado, la validación del servidor sigue funcionando.
- [ ] El formulario usa `csrfCampo()` y la página llama a `verificarCsrf()`.

---

#### ISS-09 · Crear la página de inicio y el detalle de materia

- **Responsable:** P3 (@Pao-Parra)
- **Dependencias:** ISS-05, ISS-06
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:feature` `tipo:ui` `area:catalogo` `prioridad:alta`

**Descripción.** Es el index que pide la revisión. Presenta Nexus FMAT y lista las materias que tienen asesores. Al abrir una materia se ven sus asesores y los horarios que publicaron. Es la parte pública de la funcionalidad de la revisión 1 (READ), y ahí aparecerá el botón "Agendar" en la revisión 2.

**Entrega esperada.**
- `public/index.php` y `app/views/index.php`, que reemplazan el index temporal de ISS-02, con:
  - una presentación: qué es y cómo funciona en tres pasos;
  - botones Registrarse e Iniciar sesión, o Ir al panel si ya hay sesión;
  - tarjetas de materias con su número de asesores (`Materia::conNumeroAsesores()`);
  - un filtro por nombre que oculta tarjetas con JS, sin recargar la página.
- `public/materia.php?id=` y `app/views/materia.php` con:
  - nombre y clave de la materia;
  - sus asesores con su licenciatura;
  - los bloques de cada asesor, ordenados por día y hora.
  - La consulta se agrega como `Materia::asesoresConDisponibilidad($id)`.
- Si la materia no existe o está inactiva: `abortar(404, ...)`.

**Limitaciones.**
- Solo lectura; agendar es de la revisión 2.
- Sin paginación: se esperan menos de 50 materias.

**Criterios de aceptación.**
- [ ] Con los datos demo, el index muestra todas las materias activas con el número correcto de asesores.
- [ ] Al escribir en el filtro, las tarjetas se filtran sin recargar.
- [ ] `materia.php` de una materia demo lista a sus asesores y sus bloques. Con `id=abc` o `id=9999` responde 404 sin errores de PHP.
- [ ] Un bloque creado en ISS-12 aparece aquí sin tocar esta página.
- [ ] Se ve bien a 360 px y a 1280 px.

---

#### ISS-10 · Crear el panel por rol

- **Responsable:** P3 (@Pao-Parra)
- **Dependencias:** ISS-07
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:ui` `area:ui` `area:auth` `prioridad:alta`

**Descripción.** Es la página a la que llega cada usuario al iniciar sesión, con accesos según su rol. Demuestra el uso de la sesión (nombre y rol) y es la entrada a las funcionalidades de las siguientes revisiones.

**Entrega esperada.**
- `public/panel.php` y `app/views/panel.php`, protegida con `requiereLogin()`.
- Un saludo con el nombre y el rol del usuario.
- Para el asesor: tarjetas "Mis materias" (`mis_materias.php`) y "Mi disponibilidad" (`disponibilidad.php`).
- Para el alumno: la tarjeta "Buscar asesoría por materia", que lleva al index.
- Para el administrador: usuarios por rol y total de materias. Se agrega `Usuario::conteoPorRol()`, una sola consulta con `GROUP BY`.

**Limitaciones.**
- No implementa las funciones a las que enlaza.
- No muestra tarjetas de funciones que todavía no existen.

**Criterios de aceptación.**
- [ ] Cada cuenta demo ve solo las opciones de su rol.
- [ ] Sin sesión, `panel.php` manda al login.
- [ ] Un nombre registrado como `<b>x</b>` se muestra como texto, no en negritas.
- [ ] El administrador ve los conteos correctos de los datos demo.

---

#### ISS-11 · Gestionar las materias que imparte el asesor

- **Responsable:** P1 (@Luis-villanueva-valdivia)
- **Dependencias:** ISS-05, ISS-06, ISS-07
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:feature` `area:asesor` `prioridad:alta`

**Descripción.** Es el primer paso de la funcionalidad de la revisión 1: el asesor elige de qué materias da asesoría (crear, leer y borrar en `asesor_materia`). Sin esto no puede publicar disponibilidad.

**Entrega esperada.**
- `app/models/AsesorMateria.php` con `materiasDe($asesorId)`, `agregar($asesorId, $materiaId)`, `quitar($asesorId, $materiaId)` e `imparte($asesorId, $materiaId)`.
- `public/mis_materias.php` y `app/views/asesor/materias.php`, protegidas con `requiereRol('asesor')`:
  - lista de sus materias, cada una con un botón Quitar (POST con `data-confirmar`);
  - formulario para agregar una materia activa que todavía no tenga.
- Cada POST redirige con un mensaje flash (POST-Redirect-GET).
- El `asesor_id` siempre sale de `usuarioActual()`, nunca del formulario.

**Limitaciones.**
- Al quitar una materia también se borran sus bloques de disponibilidad (`ON DELETE CASCADE`), y la confirmación lo avisa.
- La regla de no quitar una materia con sesiones pendientes es de la revisión 2.

**Criterios de aceptación.**
- [ ] El asesor demo agrega una materia, la ve en su lista y el contador del index sube.
- [ ] No puede agregar dos veces la misma materia, ni una inactiva o inexistente. Se valida en el servidor; la lista del formulario es solo ayuda.
- [ ] Quitar una materia pide confirmación y borra también sus bloques.
- [ ] Un alumno que abre `mis_materias.php` recibe 403, y un visitante va al login.
- [ ] Un asesor no puede quitar materias de otro asesor modificando el formulario.

---

#### ISS-12 · Publicar disponibilidad por materia con validación de empalmes

- **Responsable:** P4 (@cesardzul-byte)
- **Dependencias:** ISS-05, ISS-06, ISS-07 (los datos demo traen materias asignadas, así que no espera a ISS-11)
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:feature` `area:asesor` `prioridad:alta`

**Descripción.** Es la funcionalidad de la revisión 1 y la base de la validación de empalmes, lo que distingue al proyecto. El asesor publica bloques semanales (día, inicio, fin y lugar) para cada materia que imparte. El sistema rechaza un bloque que choca con otro suyo, aunque sea de otra materia. Es un CRUD completo sobre `disponibilidad`.

**Entrega esperada.**
- `app/includes/horario.php` con funciones puras que reutiliza la revisión 2 para las sesiones:
  - `seEmpalman($ini1, $fin1, $ini2, $fin2): bool`: normaliza las horas a `HH:MM` (la BD devuelve `HH:MM:SS`) y aplica `ini1 < fin2 && fin1 > ini2`;
  - `validarBloque($dia, $inicio, $fin): array`: devuelve los errores (día del 1 al 6, inicio antes que fin, dentro de 07:00–21:00, minutos en :00 o :30).
- `tests/horario_check.php`: `assert`s sobre las dos funciones, con choque parcial, bloque contenido, bloques idénticos, bloques que solo se tocan, formatos `HH:MM` y `HH:MM:SS` mezclados, y entradas inválidas.
- `app/models/Disponibilidad.php` con `deAsesor($asesorId)` (incluye el nombre de la materia y ordena por día y hora), `delDia($asesorId, $dia)`, `buscar($id, $asesorId)`, `crear()`, `actualizar()` y `eliminar($id, $asesorId)`.
- El empalme se calcula en PHP con `seEmpalman()` sobre los bloques del asesor en ese día (son pocas filas). Así la regla vive en un solo lugar y se prueba sin BD.
- `public/disponibilidad.php` y `app/views/asesor/disponibilidad.php`, protegidas con `requiereRol('asesor')`:
  - sus bloques agrupados por día;
  - formulario para crear y editar con materia (solo las que imparte), día (lunes a sábado), `<input type="time" step="1800" min="07:00" max="21:00">` y lugar;
  - botones Editar y Eliminar (POST con confirmación).
- El mensaje de empalme dice con qué bloque choca, por ejemplo: "Se empalma con Cálculo Integral, lunes 10:00–12:00".
- Si el asesor no imparte ninguna materia, se muestra un aviso con un enlace a `mis_materias.php`.

**Limitaciones.**
- Solo valida empalmes entre bloques del mismo asesor. Los empalmes de sesiones (asesor y alumno, por fecha) son de la revisión 2 y usan `seEmpalman()`.
- `ponytail:` la validación revisa y luego inserta, sin bloqueo, porque solo el propio asesor escribe sus bloques. Las sesiones (revisión 2) sí necesitan transacción.
- Sin fechas de vigencia ni excepciones como días festivos.

**Criterios de aceptación.**
- [ ] `php -d zend.assertions=1 tests/horario_check.php` termina sin errores, y deja de pasar si se cambia `<` por `<=` en `seEmpalman()`.
- [ ] Si existe el bloque lunes 10:00–12:00, se rechazan 11:00–13:00, 09:00–10:30, 10:30–11:30 y 10:00–12:00 aunque sean de otra materia; se aceptan 12:00–13:00, 08:00–10:00 y el martes de 10:00 a 12:00.
- [ ] Editar un bloque sin cambiar sus horas no choca consigo mismo.
- [ ] El servidor rechaza, aunque se modifique el HTML: inicio ≥ fin, horas fuera de 07:00–21:00, minutos distintos de :00 o :30, día 7 y materias que el asesor no imparte.
- [ ] Un asesor no puede ver, editar ni borrar bloques de otro: si cambia el id, recibe 404.
- [ ] Un bloque nuevo aparece en `materia.php` (ISS-09).

---

#### ISS-13 · Ejecutar las pruebas de la revisión 1

- **Responsable:** P3 (@Pao-Parra)
- **Dependencias:** ISS-07, ISS-08, ISS-09, ISS-10, ISS-11, ISS-12
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:test` `prioridad:alta`

**Descripción.** Alguien que no escribió el backend prueba la app como usuario, en una instalación limpia y con una lista escrita. Cada falla se registra y se corrige antes del miércoles.

**Entrega esperada.**
- `docs/planeacion/pruebas_revision1.md` con una tabla de casos: resultado esperado, obtenido y estado. Casos mínimos:
  - instalación desde cero siguiendo el README;
  - inicio de sesión con los 3 roles;
  - registro válido e inválido;
  - permisos por rol;
  - materias del asesor;
  - disponibilidad y los empalmes de ISS-12;
  - index y detalle de materia;
  - cierre de sesión;
  - vista a 360 px.
- Por cada falla, un issue `tipo:bug` enlazado a este, asignado al responsable del issue original.

**Limitaciones.**
- No corrige errores: los corrige el responsable original.
- Las pruebas son manuales. La única automática es `tests/horario_check.php`.

**Criterios de aceptación.**
- [ ] La instalación se hizo siguiendo solo el README, en una computadora distinta a la de P4, sin pedir ayuda.
- [ ] Todos los casos tienen resultado, y los bugs que impiden la demo están cerrados antes de ISS-14.
- [ ] Se probó en Chrome y en Firefox.

---

#### ISS-14 · Integrar, publicar la versión 0.1.0 y preparar la revisión 1

- **Responsable:** P4 (@cesardzul-byte)
- **Dependencias:** ISS-03, ISS-04, ISS-13
- **Milestone:** Revisión 1 — Requisitos, ER y primer avance
- **Labels:** `tipo:config` `area:repo` `prioridad:alta`

**Descripción.** Cierra la revisión: todo queda fusionado en `main`, los documentos se suben en PDF a la plataforma y hay un guion para que los cuatro presenten su parte, lo que cuenta para trabajo en equipo.

**Entrega esperada.**
- PR de `develop` a `main` y etiqueta `v0.1.0`.
- En la plataforma del curso: el documento de requisitos, el diagrama ER y el modelo relacional en PDF, más el enlace al repositorio (y el zip de la etiqueta, si lo piden).
- `docs/planeacion/guion_revision1.md`: demo de unos 5 minutos que dice quién presenta qué y con qué cuenta demo:
  - P2: problema y requisitos;
  - P1: base de datos y ER;
  - P3: interfaz e index;
  - P4: inicio de sesión y disponibilidad con un empalme rechazado en vivo.
- La computadora de la presentación tiene la BD reimportada y la app probada el mismo día.
- Al final del guion, una lista de pendientes que pasan a la planeación de la revisión 2.

**Limitaciones.**
- No agrega funciones. Desde el cierre del lun 26/10 solo entran correcciones.

**Criterios de aceptación.**
- [ ] `main` tiene la etiqueta `v0.1.0` y coincide con `develop`.
- [ ] Los PDFs se subieron a la plataforma antes de la hora límite del 29/10.
- [ ] El guion le da una parte a cada integrante y se ensayó una vez, en 7 minutos o menos.
- [ ] Los issues del milestone Revisión 1 están cerrados, o movidos a Revisión 2 con una justificación.
