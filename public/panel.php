<?php
/**
 * Panel (ISS-10): la página a la que llega cada usuario al iniciar sesión.
 * Saluda con su nombre y rol, y muestra solo los accesos que le corresponden.
 * Sin sesión, requiereLogin() manda al login.
 */
require __DIR__ . '/../app/includes/bootstrap.php';
requiereLogin();

// Cómo se muestra cada rol en el saludo
const NOMBRES_ROL = [
    'alumno' => 'Alumno',
    'asesor' => 'Asesor',
    'admin' => 'Administrador',
];

$usuario = usuarioActual();

// Los conteos solo se consultan para el administrador, que es el único que los ve
$conteoRoles = null;
$totalMaterias = null;
if ($usuario['rol'] === 'admin') {
    $conteoRoles = Usuario::conteoPorRol();
    $totalMaterias = count(Materia::activas());
}

vista('panel', [
    'titulo' => 'Panel',
    'usuario' => $usuario,
    'nombreRol' => NOMBRES_ROL[$usuario['rol']] ?? '',
    'conteoRoles' => $conteoRoles,
    'totalMaterias' => $totalMaterias,
]);
