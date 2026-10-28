<?php
/**
 * Controlador de cierre de sesión. Solo permite solicitudes POST protegidas con CSRF.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    abortar(405, 'Método no permitido.');
}

verificarCsrf();
cerrarSesion();
flash('info', 'Has cerrado sesión correctamente.');
redirigir('');
