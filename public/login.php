<?php
/**
 * Controlador de inicio de sesión (ISS-07).
 * Verifica siempre una contraseña para reducir diferencias de tiempo entre
 * un correo inexistente y un correo existente.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';

// Hash bcrypt de relleno (costo 10, igual que las cuentas demo de seed.sql).
// Su resultado nunca autentica a nadie: solo se usa cuando el correo no existe
// o la fila no contiene un hash válido.
$hashRelleno = '$2y$10$O40mCxcfcXoY.GOejBWmpOBkU/O2eYWuWh9UnKBpf0whKEiOlRD9q';

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
    $hashVerificar = $usuario !== null
        && is_string($usuario['password_hash'] ?? null)
        && $usuario['password_hash'] !== ''
            ? $usuario['password_hash']
            : $hashRelleno;

    // Se ejecuta incluso cuando el correo no existe o la cuenta está inactiva.
    $contrasenaCorrecta = password_verify($contrasena, $hashVerificar);

    $credencialesCorrectas = $usuario !== null
        && (int) ($usuario['activo'] ?? 0) === 1
        && $contrasenaCorrecta;

    if ($credencialesCorrectas) {
        iniciarSesion($usuario);
        flash('exito', 'Has iniciado sesión correctamente.');
        redirigir('panel.php');
    }

    // Mismo mensaje para correo desconocido, contraseña incorrecta y cuenta inactiva.
    $error = 'Correo o contraseña incorrecta';
}

vista('auth/login', [
    'titulo' => 'Iniciar sesión',
    'correo' => $correo,
    'error' => $error,
]);
