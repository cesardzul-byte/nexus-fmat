<?php
/**
 * Estadísticas (ISS-32): pantalla del administrador con los seis indicadores de RF-17.
 *
 * La página solo arma el esqueleto; los números los pide app.js a public/api/estadisticas.php
 * (ISS-30) con fetch, también al cambiar el filtro desde/hasta, sin recargar.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';

// Solo el administrador entra: el visitante va al login y los demás roles reciben 403
requiereRol('admin');

vista('estadisticas/index', [
    'titulo' => 'Estadísticas',
]);
