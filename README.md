# Nexus FMAT

Sistema de asesorías entre estudiantes de la Facultad de Matemáticas (UADY). Los asesores publican su disponibilidad por materia, los alumnos agendan sesiones y después las califican. El sistema valida que los horarios no se empalmen e incluye un panel de estadísticas con las materias más solicitadas.

Proyecto integrador de Desarrollo de Aplicaciones Web.

## Equipo

| Integrante | Enfoque |
|---|---|
| [@Luis-villanueva-valdivia](https://github.com/Luis-villanueva-valdivia) | Base de datos y modelos |
| [@oswaldo-castillo](https://github.com/oswaldo-castillo) | Autenticación, sesiones y requisitos |
| [@Pao-Parra](https://github.com/Pao-Parra) | Interfaz y pruebas de usuario |
| [@cesardzul-byte](https://github.com/cesardzul-byte) | Coordinación, arquitectura e integración |

## Tecnologías

PHP 8 con PDO, MySQL/MariaDB, HTML5, CSS3 y JavaScript, sin frameworks ni librerías externas.

## Requisitos

- [XAMPP 8.2.12](https://www.apachefriends.org/download.html) (PHP 8.2.12, MariaDB 10.4.32, Apache 2.4.58, phpMyAdmin 5.2.1)
- Git
- Chrome, Firefox o Edge en su versión actual

## Instalación en XAMPP

1. **Clonar** el repositorio dentro de `htdocs`:
   ```bash
   cd C:/xampp/htdocs
   git clone https://github.com/cesardzul-byte/nexus-fmat.git
   ```
2. **Iniciar** Apache y MySQL desde el panel de control de XAMPP.
3. **Importar la base de datos** en phpMyAdmin (`http://localhost/phpmyadmin`), pestaña *Importar*, en este orden:
   1. `database/schema.sql` (crea la base `nexus_fmat` y sus tablas)
   2. `database/seed.sql` (datos demo)
4. **Configurar**: copiar `app/config/config.example.php` a `app/config/config.php` y ajustar las credenciales si es necesario (en XAMPP, por defecto, usuario `root` sin contraseña).
5. **Abrir** la aplicación en <http://localhost/nexus-fmat/public/>

> `config.php` está en `.gitignore` y nunca se sube al repositorio.

## Estructura

```
nexus-fmat/
├── app/          # código que no se sirve al navegador: config, includes, models, views
├── public/       # páginas PHP, endpoints JSON y assets (lo único accesible por URL)
├── database/     # schema.sql y seed.sql
├── docs/         # requisitos, diagramas, manuales y planeación
└── tests/        # verificaciones que se ejecutan con php
```

## Contribuir

Ramas, commits, pull requests, revisión cruzada y estándares de código: [docs/convenciones.md](docs/convenciones.md).

## Licencia

Ver [LICENSE](LICENSE).

<!-- ===== ISS-05 · Cuentas demo (P1) ===== -->

## Cuentas demo

Al importar `database/seed.sql` se crean estas 7 cuentas. Todas usan la contraseña **`Demo1234`**.

| Rol | Nombre | Correo |
|---|---|---|
| Administrador | Ana Martínez | `ana.martinez@demo.uady.mx` |
| Asesor | Luis Hernández | `luis.hernandez@demo.uady.mx` |
| Asesor | Laura Ramírez | `laura.ramirez@demo.uady.mx` |
| Asesor | Pedro Sánchez | `pedro.sanchez@demo.uady.mx` |
| Alumno | Juan Pérez | `juan.perez@demo.uady.mx` |
| Alumno | María González | `maria.gonzalez@demo.uady.mx` |
| Alumno | Carlos Rodríguez | `carlos.rodriguez@demo.uady.mx` |

> Son datos ficticios solo para desarrollo y demostración.
