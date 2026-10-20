<?php
/**
 * Arranque común: toda página de public/ lo incluye en su primera línea.
 * Carga la configuración, configura errores y sesión, e incluye conexión, helpers y modelos.
 */
declare(strict_types=1);

require __DIR__ . '/../config/config.php';

// Los errores solo se muestran en desarrollo; en producción se quedan en el log de Apache
error_reporting(E_ALL);
ini_set('display_errors', MODO_DESARROLLO ? '1' : '0');

// httponly: JavaScript no puede leer la cookie. Lax: no viaja en POST desde otros sitios
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';

// glob() puede devolver false en algunos sistemas cuando no hay coincidencias
foreach (glob(__DIR__ . '/../models/*.php') ?: [] as $modelo) {
    require $modelo;
}
