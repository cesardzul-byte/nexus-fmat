# Planeación del proyecto — Nexus FMAT (tercera entrega)

El jueves **26 de noviembre de 2026** el equipo presenta la aplicación de asesorías y entrega una copia instalable con su base de datos. El administrador debe poder explicar la demanda por materia con los mismos registros que el alumno y el asesor ven en sus sesiones.

| Integrante | Usuario | Responsabilidad final |
|---|---|---|
| P1 | @Luis-villanueva-valdivia | Consultas, supervisión y documentación de la BD |
| P2 | @oswaldo-castillo | Manual de usuario y correcciones de los flujos por rol |
| P3 | @Pao-Parra | Estadísticas en pantalla, interfaz y pruebas |
| P4 | @cesardzul-byte | Planeación, manual técnico, integración y entrega |

## 1. Supuestos

1. **Referencias.** La referencia académica es [Especificaciones del proyecto](../especificaciones-proyecto.md). El alcance acordado está en [Limitaciones del MVP](limitaciones-mvp.md), en particular §§3, 6–8. La primera y la segunda planeación aportan los hitos y la secuencia de issues.
2. **Estado al empezar.** La ventana original es del 13 al 25/11. Al corte del viernes 20/11, `develop` contiene las páginas de agendado, solicitudes, reseñas y administración, los dos scripts de pruebas, requisitos, modelo relacional y ER, y `main` tiene `v0.2.0`. El [informe de revisión 2](pruebas_revision2.md) registra 40/40 casos aprobados en esa versión.
3. **Pendientes.** El panel actual cuenta alumnos, asesores, administradores y materias activas. La API de horarios ya existe. Quedan por integrar la API y vista de RF-17, la supervisión global y los PDF de los manuales.
4. **Horas.** Se presupuestan 16 horas-persona en toda la entrega. Al iniciar el 20/11 cada integrante descuenta sus horas ya usadas y comunica lo que tiene en su rama. Las revisiones cruzadas, correcciones y coordinación se cargan a las partidas de su responsable.
5. **Plan de recorte.** El 19/11 era el punto de decisión original. El 20/11 P4 coteja ramas y horas restantes. Si hay atraso, registra el recorte mínimo en este orden: (1) supervisión global, (2) filtro de fechas, conservando totales históricos, (3) barras CSS, conservando tablas. Se actualizan manuales, pruebas y dependencias afectadas. El ciclo de asesoría, CRUD, seis indicadores, manuales, pruebas y paquete son obligatorios.

## 2. Fases e hitos de todo el proyecto

| Milestone | Vencimiento | Producto |
|---|---|---|
| Revisión 1 — Requisitos, ER y primer avance | 29/10 | Requisitos, ER, autenticación y disponibilidad |
| Revisión 2 — Funcionalidades | 12/11 | Agendado, estados, reseñas, administración y ER final |
| **Entrega final — Integración y documentación** | **26/11** | Aplicación integrada, BD restaurable, manuales, demostración y coevaluación |

| Hito interno | Fecha | Evidencia para cerrarlo |
|---|---|---|
| H3.1 Congelamiento de funciones | 19/11 original · comprobar el 20/11 | P4 registra funciones presentes y pendientes, recortes y contrato de datos. Desde ese corte solo se completa el alcance acordado. |
| H3.2 Manuales listos | 23/11 | Ambos PDF abren y coinciden con las pantallas integradas y la instalación real. |
| H3.3 v1.0.0 entregada | 25/11 | Pruebas aprobadas, versión identificada y paquete comprobado en plataforma. El 26/11 se presenta y se completa la coevaluación según indique el profesor. |

**Cobertura de la rúbrica.** La funcionalidad (40 %) se demuestra con una asesoría y sus indicadores. Los temas del curso (20 %) se muestran con autenticación, sesión PHP, formularios, PDO y JSON. La modularidad (20 %) se revisa siguiendo una consulta desde el controlador al modelo y la vista. La interfaz (20 %) se comprueba en escritorio y a 360 px. Los requisitos, el ER y el modelo acompañan el paquete. La revisión por pares y una intervención por integrante respaldan el trabajo en equipo.

## 3. Calendario de la entrega final

Todas las fechas son de noviembre de 2026. La dependencia indica qué debe estar integrado para cerrar la actividad. Los manuales pueden redactarse desde el 20 y completar las capturas al integrar la interfaz.

| Issue | Actividad | Responsable | Depende de | Fecha | Horas |
|---|---|---|---|---|---:|
| ISS-29 | Acordar el alcance de cierre | P4 | — | vie 20/11 | 0.25 |
| ISS-30 | Calcular RF-17 y exponer el JSON | P1 | 29 | sáb 21/11 | 1.5 |
| ISS-31 | Supervisar las sesiones | P1 | 29 | sáb 21/11 | 0.5 |
| ISS-32 | Mostrar los seis indicadores | P3 | 30 | dom 22/11 | 1.5 |
| ISS-33 | Pulir y limpiar la interfaz final | P3 | 31*, 32 | dom 22/11 | 1 |
| ISS-34 | Documentar la base de datos | P1 | 30 | dom 22/11 | 1 |
| ISS-35 | Entregar el manual de usuario | P2 | 31*, 32, 33 | lun 23/11 | 2.5 |
| ISS-36 | Entregar el manual técnico | P4 | 30, 32, 34 | lun 23/11 | 2 |
| ISS-37 | Probar la versión integrada y corregir hallazgos | P3 y equipo | 31*, 32, 33, 35, 36 | mar 24/11 | 4.75 |
| ISS-38 | Cerrar v1.0.0 y entregar en plataforma | P4 | 35, 36, 37 | mié 25/11 | 1 |

\* Si se recorta ISS-31, P4 registra la decisión y retira esa dependencia del manual y las pruebas.

| Integrante | Distribución en horas | Total |
|---|---|---:|
| P1 | API 1.5 + supervisión 0.5 + manual técnico (BD) 1 + verificación y correcciones 1 | 4 |
| P2 | Manual de usuario 2.5 + verificación y correcciones 1.5 | 4 |
| P3 | Vista 1.5 + pulido 1 + pruebas 1 + correcciones 0.5 | 4 |
| P4 | Planeación 0.25 + manual técnico 2 + integración y correcciones 0.75 + entrega 1 | 4 |
| **Equipo** | Incluye revisión por pares | **16** |

## 4. Arquitectura y estructura

XAMPP 8.2.12 reúne Apache 2.4.58, PHP 8.2.12 con PDO y MariaDB 10.4.32. El navegador usa HTML5, CSS y JS ES2020 nativos. Los controladores de `public/` cargan `app/includes/bootstrap.php`, consultan modelos de `app/models/` y cargan vistas de `app/views/`. La API devuelve JSON con el patrón de `public/api/horarios.php`. La pantalla nueva utiliza `app/views/estadisticas/` y los recursos compartidos de `public/assets/`. El servidor comprueba el rol también en accesos directos. Solo se agregan o retiran estos archivos:

```
nexus-fmat/
├── app/
│   ├── models/
│   │   ├── Sesion.php                # ISS-30: resumenEntre(), demandaPorMateria() · ISS-31: todas()
│   │   └── Resena.php                # ISS-30: resumenEntre()
│   └── views/
│       ├── admin/sesiones.php        # ISS-31
│       ├── estadisticas/index.php    # ISS-32 (borra el .gitkeep)
│       └── ui.php                    # ISS-33 lo retira
├── public/
│   ├── admin_sesiones.php            # ISS-31
│   ├── estadisticas.php              # ISS-32
│   ├── api/estadisticas.php          # ISS-30
│   └── ui.php                        # ISS-33 lo retira
└── docs/
    ├── manuales/                     # manual-usuario.pdf (ISS-35) y manual-tecnico.pdf (ISS-36)
    └── planeacion/                   # pruebas_revision3.md (ISS-37) y guion_revision3.md (ISS-38)
```

**Contrato de la API de estadísticas.** Lo fija ISS-29 para que ISS-32 e ISS-36 no esperen a ISS-30. Los errores responden `{ "error": "..." }` con 400, 401 o 403, como `horarios.php`.

```
GET public/api/estadisticas.php?desde=2026-10-01&hasta=2026-10-31
```
```json
{
  "desde": "2026-10-01",
  "hasta": "2026-10-31",
  "solicitadas": 6,
  "completadas": 4,
  "canceladas": 1,
  "resenas": { "promedio": 4.0, "total": 3 },
  "por_materia": [
    { "clave": "MAT101", "nombre": "Cálculo Diferencial", "solicitadas": 3 },
    { "clave": "MAT103", "nombre": "Álgebra Superior", "solicitadas": 1 },
    { "clave": "MAT102", "nombre": "Cálculo Integral", "solicitadas": 1 },
    { "clave": "PRG202", "nombre": "Programación Estructurada", "solicitadas": 1 }
  ]
}
```

Sin `desde` ni `hasta`, ambos van en `null` y se cuenta el histórico. `promedio` lleva un decimal, o `null` si no hay reseñas. `por_materia` solo incluye materias con al menos una solicitud, ordenadas de más a menos solicitadas y por nombre en empate: sus primeros lugares son el ranking y la lista completa es el desglose.

### Matriz mínima de cierre

| Recorrido | Resultado comprobable |
|---|---|
| Instalación y reglas | README reproducible. Ejecutar `php tests/horario_check.php` y `php tests/estados_check.php` y registrar la salida. |
| Autenticación, sesión y roles | Registro, entrada, navegación, salida y rechazo posterior de una página protegida. Cada rol ve sus operaciones. |
| CRUD | Crear, consultar y editar materia. Desactivarla y comprobar la visibilidad. Repetir alta, edición y baja de un bloque libre de prueba. Editar, desactivar y reactivar un usuario. |
| Ciclo y empalmes | Agendar una fecha futura dentro del bloque, rechazar cruce de alumno o asesor y confirmar. Con la sesión demo 6, cuya hora de fin ya pasó, completar y calificar una vez. Verificar cancelación y rechazo de segunda reseña. |
| Estadísticas y supervisión | Antes de modificar el seed, contrastar los totales de ISS-30 y las filas de ISS-31. Verificar rango vacío, acceso por rol y actualización tras una operación conocida. |
| Interfaz | Registrar por navegador y a 360 px la navegación, mensajes, tablas y formularios. Restaurar la BD de prueba antes de repetir recorridos que cambien el estado. |

## 5. Issues de la entrega final

**Milestone común:** Entrega final — Integración y documentación (vence el 26/11/2026). Una rama por issue desde `develop`, un PR con aprobación y revisión cruzada según [Convenciones](../convenciones.md). P4 revisa a P1, P1 a P2, P2 a P3 y P3 a P4. Los hitos indicados abajo son el siguiente punto de control al que contribuye cada issue.

Labels:

| Grupo | Labels |
|---|---|
| Tipo | `tipo:feature` `tipo:ui` `tipo:bd` `tipo:docs` `tipo:config` `tipo:test` `tipo:bug` |
| Área | `area:repo` `area:auth` `area:catalogo` `area:asesor` `area:sesiones` `area:resenas` `area:estadisticas` `area:admin` `area:ui` |
| Prioridad | `prioridad:bloqueante` (otros issues esperan a este) · `prioridad:alta` (lo exige la entrega) · `prioridad:media` (se puede recortar) |

#### ISS-29 · Acordar el alcance de cierre

- **Responsable:** P4 (@cesardzul-byte)
- **Dependencias:** ninguna
- **Milestone:** Entrega final — Integración y documentación
- **Hito:** H3.1 Congelamiento de funciones
- **Labels:** `tipo:docs` `area:repo` `prioridad:bloqueante`
- **Fecha objetivo:** 20/11/2026
- **Estimación:** 0.25 h

**Descripción.** Contrastar esta planeación con las ramas del equipo y el tiempo usado.

**Entrega esperada.** Este archivo, relación de issues, contrato de la API de estadísticas (§4) y decisión sobre los tres recortes.

**Limitaciones.** Usar ISS-29 en adelante como claves internas. Al crear los issues, copiar el número real en las dependencias y los PR.

**Criterios de aceptación.**

- [ ] Cada actividad tiene dueño y fecha.
- [ ] El alcance decidido cabe en las horas restantes.

#### ISS-30 · Calcular RF-17 y exponer el JSON

- **Responsable:** P1 (@Luis-villanueva-valdivia)
- **Dependencias:** ISS-29
- **Milestone:** Entrega final — Integración y documentación
- **Hito:** H3.2 Manuales listos
- **Labels:** `tipo:feature` `area:estadisticas` `prioridad:bloqueante`
- **Fecha objetivo:** 21/11/2026
- **Estimación:** 1.5 h

**Descripción.** Contar solicitudes de todos los estados, completadas y canceladas. Agrupar por materia para el ranking y el desglose. Calcular promedio y cantidad de reseñas de sesiones del periodo.

**Entrega esperada.** Tres consultas preparadas: resumen de sesiones (`Sesion::resumenEntre()`), demanda agrupada por materia (`Sesion::demandaPorMateria()`) y reseñas unidas a sesiones (`Resena::resumenEntre()`). Se agregan con comentario de sección. GET public/api/estadisticas.php responde el contrato de §4 y admite desde y hasta inclusivos sobre sesiones.fecha.

**Limitaciones.** Solo administradores activos. Sin fechas da el histórico; con una sola, el otro extremo queda abierto. Conservar las siete tablas.

**Criterios de aceptación.**

- [ ] Con el seed limpio, el histórico devuelve 8 solicitudes, 4 completadas, 1 cancelada, 3 reseñas y promedio 4.
- [ ] MAT101 encabeza con 4.
- [ ] Octubre da 6 solicitudes.
- [ ] Rango invertido o fecha inexistente (2026-02-30) da 400, visitante 401 y otro rol 403 en JSON.

#### ISS-31 · Supervisar las sesiones

- **Responsable:** P1 (@Luis-villanueva-valdivia)
- **Dependencias:** ISS-29
- **Milestone:** Entrega final — Integración y documentación
- **Hito:** H3.2 Manuales listos
- **Labels:** `tipo:feature` `area:admin` `prioridad:media`
- **Fecha objetivo:** 21/11/2026
- **Estimación:** 0.5 h

**Descripción.** Permitir que el administrador consulte la agenda completa desde su panel.

**Entrega esperada.** `Sesion::todas()`, `public/admin_sesiones.php` y `app/views/admin/sesiones.php`, protegidas con `requiereRol('admin')`, con alumno, asesor, materia, fecha, intervalo y estado. Tarjeta "Sesiones" en el panel del administrador.

**Limitaciones.** Lectura únicamente. Primer elemento recortable si las horas son insuficientes.

**Criterios de aceptación.**

- [ ] La cuenta admin visualiza las ocho sesiones demo.
- [ ] Alumno y asesor carecen de acceso directo.
- [ ] La tabla conserva el historial cancelado.

#### ISS-32 · Mostrar los seis indicadores

- **Responsable:** P3 (@Pao-Parra)
- **Dependencias:** ISS-30
- **Milestone:** Entrega final — Integración y documentación
- **Hito:** H3.2 Manuales listos
- **Labels:** `tipo:ui` `area:estadisticas` `prioridad:alta`
- **Fecha objetivo:** 22/11/2026
- **Estimación:** 1.5 h

**Descripción.** Crear la pantalla de estadísticas del administrador y conectarla al JSON. Mostrar solicitadas, completadas, canceladas, materias más solicitadas, promedio con número de reseñas y asesorías por materia.

**Entrega esperada.** `public/estadisticas.php` con `requiereRol('admin')` y `app/views/estadisticas/index.php` con tarjetas, tabla por materia, barras CSS y filtro desde/hasta. El `fetch` va en una sección nueva de `app.js`. Tarjeta "Estadísticas" en el panel del administrador. Se borra `app/views/estadisticas/.gitkeep`.

**Limitaciones.** Reutilizar layout y fetch nativo. Aplicar los recortes registrados en ISS-29.

**Criterios de aceptación.**

- [ ] Los valores coinciden con ISS-30.
- [ ] Diciembre da 2 solicitudes y 0 reseñas: el promedio indica «Sin reseñas».
- [ ] Un intervalo vacío muestra cero solicitudes.
- [ ] Un error de consulta deja un mensaje visible.

#### ISS-33 · Pulir y limpiar la interfaz final

- **Responsable:** P3 (@Pao-Parra)
- **Dependencias:** ISS-31*, ISS-32
- **Milestone:** Entrega final — Integración y documentación
- **Hito:** H3.2 Manuales listos
- **Labels:** `tipo:ui` `area:ui` `prioridad:alta`
- **Fecha objetivo:** 22/11/2026
- **Estimación:** 1 h

**Descripción.** Revisar estados vacíos, avisos y tablas en pantallas estrechas.

**Entrega esperada.** Retirar public/ui.php, app/views/ui.php, la sección 11 de app.css y la variable `--ancho-muestra-color` de `:root`, que solo usa esa sección.

**Limitaciones.** Conservar los estilos compartidos usados por las vistas.

**Criterios de aceptación.**

- [ ] Las vistas abren desde su menú.
- [ ] A 360 px solo se desplazan dentro de su contenedor las tablas que lo necesitan.
- [ ] La guía retirada carece de referencias activas.

#### ISS-34 · Documentar la base de datos

- **Responsable:** P1 (@Luis-villanueva-valdivia)
- **Dependencias:** ISS-30
- **Milestone:** Entrega final — Integración y documentación
- **Hito:** H3.2 Manuales listos
- **Labels:** `tipo:docs` `area:repo` `prioridad:alta`
- **Fecha objetivo:** 22/11/2026
- **Estimación:** 1 h

**Descripción.** Preparar la sección de BD que P4 incorporará al manual técnico.

**Entrega esperada.** Instrucciones para importar schema.sql y después seed.sql en una instancia de prueba, relaciones de las siete tablas y restauración del respaldo.

**Limitaciones.** Usar los nombres, claves y restricciones del esquema actual. Advertir que el script recrea la BD.

**Criterios de aceptación.**

- [ ] La instalación produce las cuentas demo y las ocho sesiones.
- [ ] Se explica la relación única entre sesión y reseña y cómo contrastar RF-17 con el seed.

#### ISS-35 · Entregar el manual de usuario

- **Responsable:** P2 (@oswaldo-castillo)
- **Dependencias:** ISS-31*, ISS-32, ISS-33
- **Milestone:** Entrega final — Integración y documentación
- **Hito:** H3.2 Manuales listos
- **Labels:** `tipo:docs` `area:repo` `prioridad:alta`
- **Fecha objetivo:** 23/11/2026
- **Estimación:** 2.5 h

**Descripción.** Guiar al visitante y a los tres roles con capturas de las pantallas finales.

**Entrega esperada.** docs/manuales/manual-usuario.pdf con registro y acceso, búsqueda, disponibilidad, agendado, cancelación, confirmación, finalización, reseña y administración de materias, usuarios y estadísticas.

**Limitaciones.** Incluir supervisión solo si se conservó. Explicar qué hacer si el horario se ocupa antes de confirmar la reserva. El primer manual que llegue a `docs/manuales/` borra su `.gitkeep`.

**Criterios de aceptación.**

- [ ] Una persona puede seguir el recorrido con las cuentas demo.
- [ ] Las capturas coinciden con el menú, las fechas, los estados y los filtros de la versión a entregar.

#### ISS-36 · Entregar el manual técnico

- **Responsable:** P4 (@cesardzul-byte)
- **Dependencias:** ISS-30, ISS-32, ISS-34
- **Milestone:** Entrega final — Integración y documentación
- **Hito:** H3.2 Manuales listos
- **Labels:** `tipo:docs` `area:repo` `prioridad:alta`
- **Fecha objetivo:** 23/11/2026
- **Estimación:** 2 h

**Descripción.** Explicar cómo instalar, mantener y comprobar la aplicación en otra computadora.

**Entrega esperada.** docs/manuales/manual-tecnico.pdf con instalación XAMPP, config.example.php, zona America/Merida, arquitectura, roles, CSRF, endpoints, sección de BD y ejecución de los dos scripts. Actualizar el README para reproducir la instalación.

**Limitaciones.** Documentar los ocho límites aceptados de §6 del MVP, incluidas fechas demo y sesiones pendientes. Si llega antes que ISS-35, borra `docs/manuales/.gitkeep`.

**Criterios de aceptación.**

- [ ] Siguiendo el README en una instalación limpia se abre el index.
- [ ] El manual describe parámetros y errores de ambas API y cómo restaurar los datos de demo.

#### ISS-37 · Probar la versión integrada y corregir hallazgos

- **Responsable:** P3 (@Pao-Parra) con P1, P2 y P4
- **Dependencias:** ISS-31*, ISS-32, ISS-33, ISS-35, ISS-36
- **Milestone:** Entrega final — Integración y documentación
- **Hito:** H3.3 v1.0.0 entregada
- **Labels:** `tipo:test` `area:repo` `prioridad:bloqueante`
- **Fecha objetivo:** 24/11/2026
- **Estimación:** 4.75 h

**Descripción.** P3 coordina pruebas. P1 contrasta datos y corrige consultas, P2 verifica recorridos y manuales, P4 integra correcciones y revisiones.

**Entrega esperada.** docs/planeacion/pruebas_revision3.md con caso, navegador, esperado, observado, responsable y evidencia. Registrar defectos como tipo:bug y volver a probarlos.

**Limitaciones.** Carga compartida: P1 1 h, P2 1.5 h, P3 1.5 h (1 de pruebas y 0.5 de ajustes), P4 0.75 h. Preparar casos antes del cierre de los manuales.

**Criterios de aceptación.**

- [ ] Completar la matriz de §4 en Chrome, Firefox y Edge y verificar 360 px.
- [ ] Cada fallo tiene corrección comprobada.
- [ ] Repetir los casos afectados si cambia el paquete.

#### ISS-38 · Cerrar v1.0.0 y entregar en plataforma

- **Responsable:** P4 (@cesardzul-byte)
- **Dependencias:** ISS-35, ISS-36, ISS-37
- **Milestone:** Entrega final — Integración y documentación
- **Hito:** H3.3 v1.0.0 entregada
- **Labels:** `tipo:config` `area:repo` `prioridad:alta`
- **Fecha objetivo:** 25/11/2026
- **Estimación:** 1 h

**Descripción.** Cerrar la versión probada y preparar una demostración entre los cuatro integrantes.

**Entrega esperada.** Integrar develop en main, comprobar igualdad del contenido y etiquetar v1.0.0. Generar ZIP de esa versión y respaldo SQL de estructura y datos demo. `docs/planeacion/guion_revision3.md` con quién presenta qué y con qué cuenta demo, como en las revisiones anteriores.

**Limitaciones.** Usar configuración de ejemplo y datos demo. Reservar el 26 para presentar.

**Criterios de aceptación.**

- [ ] Restaurar el SQL en una BD limpia.
- [ ] Abrir el ZIP y verificar aplicación, README y scripts.
- [ ] Comprobar en plataforma ZIP, SQL, requisitos PDF, modelo relacional PDF, ER y ambos manuales.
- [ ] El guion da una parte a cada integrante (P1 datos, P2 el ciclo, P3 el panel y P4 la instalación) y se ensayó una vez, en 7 minutos o menos.
- [ ] Cada integrante completa su coevaluación.
