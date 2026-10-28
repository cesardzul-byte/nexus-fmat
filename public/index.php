<?php
/**
 * Página de inicio (ISS-09): presenta Nexus FMAT, explica cómo funciona y
 * lista las materias activas con su número de asesores. Es pública.
 */
require __DIR__ . '/../app/includes/bootstrap.php';

vista('index', [
    'titulo' => 'Inicio',
    'usuario' => usuarioActual(),
    'materias' => Materia::conNumeroAsesores(),
]);
