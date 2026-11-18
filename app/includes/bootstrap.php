<?php
/**
 * Arranque común: toda página de public/ lo incluye en su primera línea.
 * Carga la configuración, configura errores, zona horaria y sesión, e incluye conexión,
 * helpers, reglas de horario y modelos.
 */
declare(strict_types=1);

require __DIR__ . '/../config/config.php';

// Los errores solo se muestran en desarrollo; en producción se quedan en el log de Apache
error_reporting(E_ALL);
ini_set('display_errors', MODO_DESARROLLO ? '1' : '0');

// El php.ini de XAMPP trae Europe/Berlin: desde las 17:00 de Mérida, date('Y-m-d') ya daría el día
// siguiente (supuesto 7). Va aquí y no en config.php porque ese archivo no está en Git
date_default_timezone_set('America/Merida');

// httponly: JavaScript no puede leer la cookie. Lax: no viaja en POST desde otros sitios
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
// Reglas de horario (RN-03, RN-04): las usan disponibilidad, materia y las sesiones de la revisión 2
require __DIR__ . '/horario.php';

// glob() puede devolver false en algunos sistemas cuando no hay coincidencias
foreach (glob(__DIR__ . '/../models/*.php') ?: [] as $modelo) {
    require $modelo;
}
// Hace disponibles las funciones de autenticación y CSRF en todas las páginas.
require_once __DIR__ . '/auth.php';
