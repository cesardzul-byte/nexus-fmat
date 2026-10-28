<?php
/**
 * Controlador de inicio de sesión.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';

// Si ya hay una sesión, se evita volver a mostrar el formulario de acceso.
if (usuarioActual() !== null) {
    $sesionActual = usuarioActual();
    $usuarioActual = is_array($sesionActual)
        ? Usuario::buscarPorId((int) ($sesionActual['id'] ?? 0))
        : null;

    if ($usuarioActual !== null && (int) ($usuarioActual['activo'] ?? 0) === 1) {
        redirigir('panel.php');
    }

    cerrarSesion();
}

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metodo, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    abortar(405, 'Método no permitido.');
}

$correo = '';
$error = null;

if ($metodo === 'POST') {
    verificarCsrf();

    $correoEnviado = $_POST['correo'] ?? '';
    $contrasenaEnviada = $_POST['contrasena'] ?? '';
    $correo = is_string($correoEnviado) ? trim($correoEnviado) : '';
    $contrasena = is_string($contrasenaEnviada) ? $contrasenaEnviada : '';

    $usuario = Usuario::buscarPorCorreo($correo);
    $credencialesCorrectas = $usuario !== null
        && (int) ($usuario['activo'] ?? 0) === 1
        && is_string($usuario['password_hash'] ?? null)
        && password_verify($contrasena, $usuario['password_hash']);

    if ($credencialesCorrectas) {
        iniciarSesion($usuario);
        flash('exito', 'Has iniciado sesión correctamente.');
        redirigir('panel.php');
    }

    // Se usa el mismo mensaje para correo desconocido, contraseña incorrecta y cuenta inactiva.
    $error = 'Correo o contraseña incorrectos';
}

vista('auth/login', [
    'titulo' => 'Iniciar sesión',
    'correo' => $correo,
    'error' => $error,
]);
