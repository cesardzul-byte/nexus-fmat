<?php
/**
 * Funciones comunes de autenticación, autorización, sesión y protección CSRF.
 * Este archivo se carga desde bootstrap.php después de los helpers y modelos.
 */
declare(strict_types=1);

/**
 * Inicia una sesión autenticada y reemplaza el identificador previo.
 * Solo guarda los datos mínimos necesarios para identificar al usuario y su rol.
 *
 * @param array<string, mixed> $usuario Fila de usuarios obtenida de la base de datos.
 */
function iniciarSesion(array $usuario): void
{
    $id = (int) ($usuario['id'] ?? 0);
    $nombre = trim((string) ($usuario['nombre'] ?? '') . ' ' . (string) ($usuario['apellidos'] ?? ''));
    $rol = (string) ($usuario['rol'] ?? '');

    if ($id < 1 || $nombre === '' || !in_array($rol, ['alumno', 'asesor', 'admin'], true)) {
        throw new InvalidArgumentException('No se pueden iniciar sesiones con datos de usuario inválidos.');
    }

    if (!session_regenerate_id(true)) {
        throw new RuntimeException('No fue posible renovar el identificador de sesión.');
    }

    $_SESSION = [];
    $_SESSION['usuario'] = [
        'id' => $id,
        'nombre' => $nombre,
        'rol' => $rol,
    ];

    // La sesión autenticada recibe un token CSRF nuevo, distinto al del formulario de login.
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Invalida los datos de autenticación, elimina el identificador anterior y crea uno nuevo vacío.
 * Mantener una sesión anónima permite mostrar el mensaje flash después de la redirección.
 */
function cerrarSesion(): void
{
    $_SESSION = [];

    if (session_status() === PHP_SESSION_ACTIVE && !session_regenerate_id(true)) {
        // Si no se pudo rotar el ID, se destruye la sesión para no conservar credenciales.
        session_destroy();
        return;
    }
}

/**
 * Exige que el usuario tenga una sesión válida y que su cuenta siga activa.
 * Redirige al login si no hay autenticación o si la cuenta fue desactivada.
 */
function requiereLogin(): void
{
    // Evita que páginas protegidas se reutilicen desde la caché del navegador tras cerrar sesión.
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    $sesion = usuarioActual();
    $id = is_array($sesion) ? (int) ($sesion['id'] ?? 0) : 0;

    if ($id < 1) {
        flash('info', 'Inicia sesión para continuar.');
        redirigir('login.php');
    }

    // Se vuelve a consultar el usuario para revocar sesiones de cuentas desactivadas o roles modificados.
    $usuario = Usuario::buscarPorId($id);
    if ($usuario === null || (int) ($usuario['activo'] ?? 0) !== 1) {
        cerrarSesion();
        flash('info', 'Tu sesión ya no está activa. Inicia sesión nuevamente.');
        redirigir('login.php');
    }

    $rol = (string) ($usuario['rol'] ?? '');
    if (!in_array($rol, ['alumno', 'asesor', 'admin'], true)) {
        cerrarSesion();
        flash('info', 'Inicia sesión para continuar.');
        redirigir('login.php');
    }

    $_SESSION['usuario'] = [
        'id' => (int) $usuario['id'],
        'nombre' => trim((string) $usuario['nombre'] . ' ' . (string) $usuario['apellidos']),
        'rol' => $rol,
    ];
}

/**
 * Exige autenticación y uno de los roles permitidos.
 * Responde con HTTP 403 cuando la sesión pertenece a otro rol.
 *
 * @param string ...$roles Roles autorizados, por ejemplo 'asesor' o 'admin'.
 */
function requiereRol(string ...$roles): void
{
    requiereLogin();

    $usuario = usuarioActual();
    $rolActual = is_array($usuario) ? (string) ($usuario['rol'] ?? '') : '';

    if ($roles === [] || !in_array($rolActual, $roles, true)) {
        abortar(403, 'No tienes permisos para acceder a esta sección.');
    }
}

/**
 * Imprime el campo oculto CSRF del formulario. El token se mantiene en la sesión.
 */
function csrfCampo(): void
{
    $token = $_SESSION['_csrf_token'] ?? null;
    if (!is_string($token) || $token === '') {
        $token = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token'] = $token;
    }

    echo '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
}

/**
 * Comprueba el token CSRF recibido por POST. Una ausencia o diferencia responde HTTP 400.
 */
function verificarCsrf(): void
{
    $tokenRecibido = $_POST['_csrf_token'] ?? null;
    $tokenSesion = $_SESSION['_csrf_token'] ?? null;

    if (
        !is_string($tokenRecibido)
        || $tokenRecibido === ''
        || !is_string($tokenSesion)
        || $tokenSesion === ''
        || !hash_equals($tokenSesion, $tokenRecibido)
    ) {
        abortar(400, 'La solicitud no es válida o ha expirado. Actualiza la página e inténtalo de nuevo.');
    }
}
