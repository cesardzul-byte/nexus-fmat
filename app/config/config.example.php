<?php
/**
 * Configuración de ejemplo. Se copia a config.php (ignorado por Git) y ahí se
 * ajustan las credenciales de cada computadora. Nunca se suben credenciales reales.
 */
declare(strict_types=1);

// Base de datos (en XAMPP, por defecto: root sin contraseña)
const DB_HOST = 'localhost';
const DB_NAME = 'nexus_fmat';
const DB_USER = 'root';
const DB_PASS = '';

// Ruta pública de la aplicación, sin diagonal final
const BASE_URL = '/nexus-fmat/public';

// Dominio institucional que se acepta en el registro
const DOMINIO_CORREO = 'alumnos.uady.mx';

// true muestra los errores de PHP en pantalla; false en cualquier servidor que no sea de desarrollo
const MODO_DESARROLLO = true;
