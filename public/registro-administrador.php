<?php
/**
 * Ruta de compatibilidad con enlaces antiguos.
 * El formulario único se procesa en registro.php; no existe un formulario separado.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';
requiereRol('admin');
redirigir('registro.php?modo=admin');
