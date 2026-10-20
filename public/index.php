<?php
/**
 * Página temporal (ISS-02): comprueba que el arranque y la conexión a la BD funcionan.
 * La reemplaza el index real en ISS-09.
 */
require __DIR__ . '/../app/includes/bootstrap.php';

db()->query('SELECT 1');
echo 'Conexión OK';
