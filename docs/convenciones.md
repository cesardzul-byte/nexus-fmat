# Convenciones del proyecto — Nexus FMAT

Reglas con las que trabaja el equipo. Si algo no está aquí, se decide en el issue correspondiente y se agrega a este archivo por PR.

---

## 1. Entorno y versiones

Las cuatro computadoras usan **la misma versión de XAMPP**. Es la más reciente que publica Apache Friends para Windows.

| Componente | Versión | Notas |
|---|---|---|
| XAMPP | **8.2.12** | Instalador de Windows de [apachefriends.org](https://www.apachefriends.org/download.html) |
| PHP | **8.2.12** | Incluido en XAMPP |
| MariaDB | **10.4.32** | Incluido en XAMPP; es compatible con MySQL. En el código y los documentos se le llama "MySQL" |
| Apache | **2.4.58** | Incluido en XAMPP |
| phpMyAdmin | **5.2.1** | Incluido en XAMPP; se usa para importar los scripts |

Lenguajes y estándares:

| Lenguaje | Estándar |
|---|---|
| HTML | HTML5 (*HTML Living Standard*) |
| CSS | CSS3 con variables (`:root`), Flexbox y Grid. Sin Bootstrap ni Tailwind |
| JavaScript | ES2020 en el navegador, sin librerías ni bundlers |
| PHP | 8.2 con PDO. Sin frameworks ni Composer |
| SQL | MariaDB 10.4 / MySQL, motor InnoDB y `utf8mb4` |

Navegadores de prueba: versiones actuales de Chrome, Firefox y Edge.

**Cómo verificarlo:** en el panel de XAMPP, *Shell* → `php -v` y `mysql --version`. Cada integrante comenta el resultado en el issue #1.

---

## 2. Ramas

```
main      ← solo recibe develop al cerrar cada revisión (con etiqueta v0.1.0, v0.2.0, v1.0.0)
develop   ← rama de integración y predeterminada
  └── tipo/iss-XX-descripcion   ← una rama por issue
```

- **Todo issue tiene su propia rama**, que sale de `develop` actualizado.
- **Nunca se hacen cambios directamente en `develop` ni en `main`.** Ambas están protegidas: no aceptan push directo y piden 1 aprobación para fusionar.
- Formato: `tipo/iss-XX-descripcion`, con `XX` a dos dígitos y la descripción en minúsculas, sin acentos, con guiones.

| Tipo | Uso | Ejemplo |
|---|---|---|
| `feature` | Funcionalidad nueva | `feature/iss-07-login` |
| `fix` | Corrección de un error | `fix/iss-15-empalme-borde` |
| `docs` | Documentación | `docs/iss-03-requisitos` |
| `db` | Esquema, datos demo o modelos de BD | `db/iss-05-schema-seed` |
| `ui` | Vistas y estilos | `ui/iss-06-layout` |
| `test` | Pruebas | `test/iss-13-pruebas-rev1` |
| `chore` | Configuración, estructura, mantenimiento | `chore/iss-02-bootstrap` |

```bash
git switch develop
git pull
git switch -c feature/iss-07-login
```

---

## 3. Commits

Formato: **`tipo: descripción (iss-XX)`**

- `tipo` en minúsculas, de la lista de abajo.
- Descripción en español, en presente e imperativo, sin punto final, de 72 caracteres o menos.
- Siempre termina con el issue entre paréntesis.

| Tipo | Uso |
|---|---|
| `feat` | Funcionalidad nueva |
| `fix` | Corrección de un error |
| `docs` | Solo documentación |
| `db` | `schema.sql`, `seed.sql` o cambios de estructura de la BD |
| `style` | Estilos (CSS) o formato del código sin cambiar su comportamiento |
| `refactor` | Cambio interno sin cambiar el comportamiento |
| `test` | Pruebas |
| `chore` | Configuración, estructura de carpetas, `.gitignore`, etc. |

Ejemplos:

```
feat: valida empalmes al crear un bloque de disponibilidad (iss-12)
fix: conserva el correo escrito al fallar el login (iss-07)
docs: agrega las convenciones del equipo (iss-01)
db: agrega CHECK de calificacion 1 a 5 en resenas (iss-05)
```

**Squash merge.** Los PR se fusionan con *Squash and merge*, así cada issue queda como **un solo commit** en `develop`. Por eso el **título del PR** sigue el mismo formato, porque se convierte en el mensaje del commit final. Los commits dentro de la rama pueden ser pequeños, pero también siguen el formato.

---

## 4. Flujo de trabajo y pull requests

1. Tomar el issue en el tablero de GitHub Projects y moverlo a *En progreso*.
2. Crear la rama desde `develop` actualizado (§2).
3. Trabajar con commits pequeños (§3). Antes de abrir el PR, traer lo último de `develop`:
   ```bash
   git fetch origin
   git merge origin/develop
   ```
4. Abrir el PR **hacia `develop`** con la plantilla (`.github/pull_request_template.md`), incluido `Closes #XX`. Asignar al revisor (§5).
5. El revisor prueba la rama en su computadora y **aprueba** o **pide cambios**.
   - Si pide cambios, el autor corrige en la misma rama, contesta los comentarios y vuelve a solicitar revisión (*Re-request review*).
6. Con la aprobación, **el autor fusiona su propio PR** con *Squash and merge* (así su trabajo queda visible para la coevaluación).
7. **La rama se borra** al fusionar (botón *Delete branch*, y localmente `git branch -d tipo/iss-XX-...`).
8. El issue se cierra solo por el `Closes #XX`.

`main` solo recibe un PR desde `develop` al cerrar cada revisión (ISS-14 y equivalentes), con su etiqueta.

---

## 5. Revisión cruzada (supuesto 8)

| PR de | Lo revisa |
|---|---|
| P2 (@oswaldo-castillo) | P1 (@Luis-villanueva-valdivia) |
| P3 (@Pao-Parra) | P2 (@oswaldo-castillo) |
| P4 (@cesardzul-byte) | P3 (@Pao-Parra) |
| P1 (@Luis-villanueva-valdivia) | P4 (@cesardzul-byte) |

- El administrador del repositorio (P4) puede aprobar cualquier PR.
- Si el revisor asignado no contesta en **24 h**, puede aprobar cualquier otro integrante.
- Nadie aprueba su propio PR.
- El revisor revisa que funcione, que cumpla los criterios de aceptación del issue y el checklist de la plantilla.

---

## 6. Dueños de archivo (supuesto 9)

Cada archivo tiene un dueño: quien lo crea. Los demás **solo agregan funciones o secciones al final**, con un comentario de sección, y no modifican lo que ya existe sin avisar en el issue al dueño.

| Archivo | Dueño |
|---|---|
| `README.md` | P4 |
| `public/assets/css/app.css` y `public/assets/js/app.js` | P3 |
| `app/models/Usuario.php` | P2 |
| `app/models/Materia.php` | P1 |

Comentario de sección al agregar algo a un archivo ajeno:

```php
// ===== ISS-10 · Panel por rol (P3) =====
```
```css
/* ===== ISS-12 · Disponibilidad (P4) ===== */
```

---

## 7. Nombres

| Elemento | Convención | Ejemplo |
|---|---|---|
| Tablas y columnas | `snake_case`, español, plural en tablas, **sin acentos ni ñ** | `resenas`, `asesor_materia`, `hora_inicio` |
| Archivos PHP de `public/` y vistas | `snake_case` | `mis_materias.php`, `asesor/disponibilidad.php` |
| Modelos (clases) | `PascalCase`, singular, un archivo por clase con el mismo nombre | `Usuario.php` → `class Usuario` |
| Métodos y funciones | `camelCase`, empiezan con verbo | `buscarPorCorreo()`, `requiereRol()` |
| Variables | `camelCase`, descriptivas | `$asesorId`, `$bloquesDelDia` |
| Constantes | `MAYUSCULAS_CON_GUION_BAJO` | `DB_HOST`, `HORA_APERTURA` |
| Clases CSS | `kebab-case` | `.tarjeta-materia`, `.alerta-error` |
| JS (variables y funciones) | `camelCase`; constantes globales en `MAYUSCULAS` | `filtrarTarjetas()` |
| Atributos `data-*` | `kebab-case` | `data-confirmar` |
| Archivos CSS, JS e imágenes | `snake_case` o `kebab-case`, minúsculas | `app.css`, `logo.svg` |

Los identificadores van en español y sin acentos (`resenas`, `anio`), porque así se nombra el dominio. Palabras técnicas comunes se dejan en inglés cuando no tienen buena traducción (`$pdo`, `$stmt`, `$id`).

---

## 8. Estándares de código

### 8.1 Generales (todos los lenguajes)

- **Indentación de 4 espacios** (2 en HTML/CSS/JS se acepta si el archivo ya lo usa; no se mezclan). Sin tabuladores. UTF-8 sin BOM, salto de línea final.
- **Sin números mágicos.** Todo valor con significado va en una constante con nombre: `HORA_APERTURA = '07:00'`, `MINUTOS_BLOQUE = 30`, `LONGITUD_MIN_PASSWORD = 8`. Excepciones obvias: `0`, `1`, `-1`, `''`.
- **Funciones pequeñas con una sola responsabilidad.** Si una función pasa de unas 30 líneas, probablemente hace demasiado.
- **Retorno temprano** en lugar de `if` anidados.
- **No repetir código (DRY):** si algo se usa en dos lugares, va en un helper o en el modelo.
- **Nombres que expliquen la intención.** Nada de `$x`, `$tmp`, `$data2`; los booleanos se leen como pregunta (`$esValido`, `imparte()`).
- **No dejar código comentado ni `var_dump`/`console.log`** en lo que se fusiona.

### 8.2 Comentarios y documentación

- **Todo archivo** empieza con un bloque que dice qué hace.
- **Toda función o método** lleva su bloque de documentación (PHPDoc o JSDoc) con descripción, parámetros, retorno y excepciones.
- Dentro del código se comenta el **porqué**, no el qué: reglas de negocio, decisiones no obvias, casos límite.
- Los comentarios van en español.

### 8.3 PHP

Base: **PSR-1 y PSR-12**, con la excepción de que los nombres van en español (§7).

- `declare(strict_types=1);` al inicio de los archivos de `app/` (modelos, includes).
- Tipos en parámetros y retornos (`function buscarPorId(int $id): ?array`).
- Llaves de clases y métodos en su propia línea; las de `if`/`foreach` en la misma línea.
- Comparaciones estrictas (`===`, `!==`).
- **Siempre consultas preparadas** (`prepare` + `execute`). Nunca se concatenan datos del usuario en SQL.
- **Toda salida al HTML pasa por `e()`.**
- Contraseñas solo con `password_hash` / `password_verify`.
- Formularios POST con `csrfCampo()` y `verificarCsrf()`; después de un POST se redirige (POST-Redirect-GET).
- Las vistas (`app/views/`) solo muestran datos: sin consultas ni lógica de negocio. Usan la sintaxis alternativa (`<?php foreach (...): ?> … <?php endforeach; ?>`).

```php
<?php
/**
 * Modelo de la tabla `disponibilidad`: bloques semanales que publica cada asesor.
 */
declare(strict_types=1);

class Disponibilidad
{
    /**
     * Devuelve los bloques de un asesor ordenados por día y hora.
     *
     * @param int $asesorId ID del asesor (sale de usuarioActual(), nunca del formulario).
     * @return array<int, array<string, mixed>> Bloques con el nombre de su materia.
     */
    public static function deAsesor(int $asesorId): array
    {
        $stmt = db()->prepare(
            'SELECT d.*, m.nombre AS materia
               FROM disponibilidad d
               JOIN materias m ON m.id = d.materia_id
              WHERE d.asesor_id = ?
              ORDER BY d.dia_semana, d.hora_inicio'
        );
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll();
    }
}
```

### 8.4 JavaScript

- `const` por defecto y `let` si cambia; nunca `var`.
- `===` siempre; punto y coma al final de cada sentencia.
- Eventos con `addEventListener`, nunca atributos `onclick` en el HTML.
- Se selecciona por clases o atributos `data-*`, no por estructura del DOM.
- `fetch` con `async`/`await` y manejo de errores (`try`/`catch` y revisar `response.ok`).
- La validación en JS es solo ayuda: **la que manda es la del servidor**.
- Documentación con JSDoc:

```js
/**
 * Pide confirmación antes de enviar cualquier formulario cuyo botón tenga data-confirmar.
 * @param {SubmitEvent} evento Evento de envío del formulario.
 */
function confirmarEnvio(evento) { /* … */ }
```

### 8.5 HTML

- HTML semántico (`header`, `nav`, `main`, `section`, `article`, `footer`), `lang="es"`.
- Cada campo tiene su `<label for>`; cada imagen, su `alt`.
- Validación HTML5 (`required`, `type`, `pattern`, `min`, `max`, `step`) además de la del servidor.
- Sin estilos en línea (`style="…"`); todo va en `app.css`.

### 8.6 CSS

- Un solo archivo, `app.css`. Colores, tipografía y espaciados como variables en `:root`; nada de colores sueltos.
- *Mobile first*: estilos base para 360 px y `@media (min-width: …)` para pantallas grandes.
- Organizado por secciones con comentario de encabezado. Lo específico de una página va al final, en su sección.
- Sin `!important` salvo que se justifique en un comentario.
- Contraste de texto de al menos 4.5:1.

### 8.7 SQL

- Palabras reservadas en MAYÚSCULAS y nombres en `snake_case`.
- Toda tabla con `id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY` (salvo tablas puente con PK compuesta), `ENGINE=InnoDB` y `utf8mb4`.
- Las reglas que la BD puede garantizar van como restricción (`UNIQUE`, `CHECK`, `FOREIGN KEY` con su `ON DELETE`).
- Usuarios y materias se desactivan (`activo = 0`), no se borran.

---

## 9. Estructura del proyecto (§4 de la planeación)

Cada página de `public/` hace de controlador: valida, llama al modelo y muestra una vista. No hay router ni framework.

| Carpeta | Qué va ahí |
|---|---|
| `app/` | Código que **no** se sirve al navegador (`app/.htaccess` lo bloquea) |
| `app/config/` | `config.example.php` (se sube) · `config.php` (ignorado por Git, con las credenciales) |
| `app/includes/` | Arranque (`bootstrap.php`), conexión (`db.php`), helpers, autenticación, reglas de horario |
| `app/models/` | Una clase por tabla, con métodos estáticos que usan PDO |
| `app/views/` | HTML de cada página, agrupado por módulo; `layouts/` tiene el header y el footer |
| `public/` | Lo único que abre el navegador (`BASE_URL`); un archivo `.php` = una página |
| `public/api/` | Endpoints JSON para `fetch` (desde la revisión 2) |
| `public/assets/` | `css/`, `js/` e `img/` |
| `database/` | `schema.sql` y `seed.sql` |
| `docs/` | `requisitos/`, `diagramas/`, `manuales/`, `planeacion/` |
| `tests/` | Verificaciones que se ejecutan con `php` (desde ISS-12) |

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

## 10. Otras reglas

- **`.gitkeep`:** al agregar el primer archivo real a una carpeta, se borra su `.gitkeep` en el mismo commit.
- **Credenciales:** nunca se suben. `app/config/config.php` está en `.gitignore`; se sube solo `config.example.php` con valores de ejemplo.
- **Datos demo:** solo ficticios.
- **Cambios en la BD:** se edita `schema.sql` y se reimporta (no hay migraciones). Quien cambie el esquema avisa en el issue, porque los demás tienen que reimportar.
- **Protección de ramas:** `main` y `develop` no aceptan push directo y piden 1 aprobación. Si GitHub dejara de permitirlo, la regla se sigue cumpliendo a mano y el revisor rechaza cualquier PR que no la respete.
