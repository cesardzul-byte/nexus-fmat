<?php
/**
 * Guía de componentes (ISS-06): muestra un ejemplo de cada componente de app.css
 * para que el equipo copie las clases. Se borra antes de la entrega final.
 *
 * Con ?sesion=1 simula un usuario con sesión, solo durante esta petición, para
 * probar la navegación mientras no exista el login (ISS-07).
 */
require __DIR__ . '/../app/includes/bootstrap.php';

const USUARIO_DE_PRUEBA = ['id' => 0, 'nombre' => 'Usuario de prueba', 'rol' => 'alumno'];

$simulaSesion = MODO_DESARROLLO && ($_GET['sesion'] ?? '') === '1';
$usuarioReal = $_SESSION['usuario'] ?? null;

if ($simulaSesion) {
    $_SESSION['usuario'] = USUARIO_DE_PRUEBA;
}

vista('ui', [
    'titulo' => 'Guía de componentes',
    'simulaSesion' => $simulaSesion,
]);

// Se regresa la sesión a como estaba, para que la simulación no se quede guardada
if ($simulaSesion) {
    $_SESSION['usuario'] = $usuarioReal;
    if ($usuarioReal === null) {
        unset($_SESSION['usuario']);
    }
}
