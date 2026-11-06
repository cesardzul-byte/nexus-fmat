<!--
Título del PR (se convierte en el commit del squash merge):
  tipo: descripción (iss-XX)
  Ej.: feat: valida empalmes al crear un bloque de disponibilidad (iss-12)
-->

Closes #XX

## Descripción

<!-- Qué resuelve este PR y por qué. Una o dos frases. -->

## Cambios

<!-- Lista de lo que se agregó, modificó o eliminó. -->
-
-

## Cómo probarlo

<!-- Pasos para que el revisor lo compruebe en su computadora. -->
1. `git fetch && git switch tipo/iss-XX-descripcion`
2. <!-- ¿Hay que reimportar schema.sql / seed.sql? ¿Copiar algo a config.php? -->
3. Abrir `http://localhost/nexus-fmat/public/...`
4. Resultado esperado:

## Criterios de aceptación

<!-- Copiados del issue. Marcar los que se cumplen. -->
- [ ]
- [ ]

## Capturas

<!-- Obligatorias si hay cambios de interfaz (360 px y escritorio). Si no aplica, escribir "No aplica". -->

## Checklist

- [ ] La rama sale de `develop` y sigue el formato `tipo/iss-XX-descripcion`.
- [ ] El título y los commits siguen el formato `tipo: descripción (iss-XX)`.
- [ ] Sin credenciales ni `config.php` en el PR.
- [ ] Todas las consultas con parámetros son preparadas (nada de SQL concatenado).
- [ ] Toda salida al HTML está escapada con `e()`.
- [ ] Los formularios POST usan `csrfCampo()` y la página llama a `verificarCsrf()`.
- [ ] Funciones documentadas (PHPDoc / JSDoc) y código comentado donde no es obvio.
- [ ] Sin números mágicos, sin código comentado ni `var_dump`/`console.log`.
- [ ] Solo se agregó al final de archivos ajenos, con comentario de sección (supuesto 9).
- [ ] Se borró el `.gitkeep` de las carpetas que recibieron su primer archivo.
- [ ] Probado en Chrome y Firefox (si hay interfaz).
- [ ] Revisor asignado según la revisión cruzada de `docs/convenciones.md`.

## Notas para el revisor

<!-- Dudas, decisiones tomadas, pendientes o cosas fuera de alcance. Borrar si no hay. -->
