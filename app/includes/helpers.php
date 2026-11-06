<?php
/**
 * Funciones comunes a todas las páginas: escape, rutas, redirección,
 * mensajes flash, usuario en sesión, vistas y páginas de error.
 */
declare(strict_types=1);

const RUTA_VISTAS = __DIR__ . '/../views/';

/**
 * Escapa un valor para imprimirlo en HTML. Toda salida al HTML pasa por aquí.
 *
 * @param string|int|float|null $valor Valor a escapar; null se imprime como cadena vacía.
 * @return string Texto con <, >, &, " y ' convertidos en entidades.
 */
function e(string|int|float|null $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Construye una URL absoluta dentro de la aplicación.
 *
 * @param string $ruta Ruta relativa a public/, por ejemplo 'materia.php?id=3'.
 * @return string URL que empieza con BASE_URL, por ejemplo '/nexus-fmat/public/materia.php?id=3'.
 */
function url(string $ruta = ''): string
{
    return BASE_URL . '/' . ltrim($ruta, '/');
}

/**
 * Redirige a otra página de la aplicación y termina la ejecución.
 *
 * @param string $ruta Ruta relativa a public/.
 * @return never
 */
function redirigir(string $ruta): never
{
    header('Location: ' . url($ruta));
    exit;
}

/**
 * Guarda un mensaje en la sesión para mostrarlo en la siguiente página (POST-Redirect-GET).
 *
 * @param string $tipo Tipo de alerta: 'exito', 'error' o 'info'.
 * @param string $mensaje Texto que verá el usuario.
 * @return void
 */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/**
 * Devuelve los mensajes flash pendientes y los borra, para que se muestren una sola vez.
 *
 * @return array<int, array{tipo: string, mensaje: string}> Mensajes en el orden en que se guardaron.
 */
function obtenerFlash(): array
{
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensajes;
}

/**
 * Devuelve el usuario con sesión iniciada.
 *
 * @return array{id: int, nombre: string, rol: string}|null null si es un visitante.
 */
function usuarioActual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

/**
 * Muestra una vista dentro del layout: header, app/views/$nombreVista.php y footer.
 *
 * Las claves de $datos se convierten en variables dentro de la vista
 * (['titulo' => 'Inicio'] → $titulo). Se usa la constante RUTA_VISTAS en lugar de
 * variables locales para que ninguna clave de $datos choque con ellas.
 *
 * @param string $nombreVista Ruta de la vista dentro de app/views/, sin '.php' (por ejemplo 'asesor/disponibilidad').
 * @param array<string, mixed> $datos Variables que recibe la vista.
 * @return void
 */
function vista(string $nombreVista, array $datos = []): void
{
    extract($datos, EXTR_SKIP);

    // Mientras ISS-06 no cree app/views/layouts/, el header y el footer quedan vacíos
    if (is_file(RUTA_VISTAS . 'layouts/header.php')) {
        require RUTA_VISTAS . 'layouts/header.php';
    }
    require RUTA_VISTAS . $nombreVista . '.php';
    if (is_file(RUTA_VISTAS . 'layouts/footer.php')) {
        require RUTA_VISTAS . 'layouts/footer.php';
    }
}

/**
 * Responde con un código HTTP de error, muestra app/views/error.php y termina la ejecución.
 *
 * @param int $codigo Código HTTP, por ejemplo 403, 404 o 500.
 * @param string $mensaje Mensaje para el usuario; no debe incluir detalles internos.
 * @return never
 */
function abortar(int $codigo, string $mensaje): never
{
    http_response_code($codigo);
    vista('error', ['titulo' => 'Error ' . $codigo, 'codigo' => $codigo, 'mensaje' => $mensaje]);
    exit;
}
