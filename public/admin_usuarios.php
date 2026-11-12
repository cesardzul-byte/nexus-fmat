<?php
/**
 * Administración de cuentas (ISS-23): permite consultar, editar datos y activar o
 * desactivar alumnos y asesores. Las cuentas de administrador son de solo lectura.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';

requiereRol('admin');

$roles = [
    'alumno' => 'Alumno',
    'asesor' => 'Asesor',
    'admin' => 'Administrador',
];
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metodo, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    abortar(405, 'Método no permitido.');
}

$rolFiltro = null;
if (isset($_GET['rol'])) {
    if (!is_string($_GET['rol'])) {
        abortar(400, 'El filtro de rol no es válido.');
    }
    $rolSolicitado = trim($_GET['rol']);
    if ($rolSolicitado !== '') {
        if (!array_key_exists($rolSolicitado, $roles)) {
            abortar(400, 'El filtro de rol no es válido.');
        }
        $rolFiltro = $rolSolicitado;
    }
}

$rutaListado = 'admin_usuarios.php' . ($rolFiltro !== null ? '?rol=' . rawurlencode($rolFiltro) : '');
$licenciaturas = Licenciatura::todas();
$usuarioEdicion = null;
$errores = [];

if ($metodo === 'POST') {
    verificarCsrf();

    $usuarioId = enteroPost('id');
    if ($usuarioId < 1) {
        abortar(404, 'Esa cuenta no existe.');
    }

    $usuarioExistente = Usuario::buscarPorId($usuarioId);
    if ($usuarioExistente === null) {
        abortar(404, 'Esa cuenta no existe.');
    }
    if ($usuarioExistente['rol'] === 'admin') {
        abortar(403, 'Las cuentas de administrador no se pueden modificar desde aquí.');
    }

    $accion = textoPost('accion');
    if ($accion === 'activar' || $accion === 'desactivar') {
        Usuario::cambiarActivo($usuarioId, $accion === 'activar');
        flash('exito', $accion === 'activar' ? 'La cuenta fue activada.' : 'La cuenta fue desactivada.');
        redirigir($rutaListado);
    }

    if ($accion !== 'guardar') {
        abortar(400, 'La acción solicitada no es válida.');
    }

    // No se leen rol, correo ni matrícula: aunque lleguen manipulados, no forman parte de la actualización.
    $usuarioEdicion = [
        'id' => $usuarioId,
        'nombre' => textoPost('nombre'),
        'apellidos' => textoPost('apellidos'),
        'correo' => (string) $usuarioExistente['correo'],
        'matricula' => (string) ($usuarioExistente['matricula'] ?? ''),
        'rol' => (string) $usuarioExistente['rol'],
        'licenciatura_id' => textoPost('licenciatura_id'),
        'semestre' => textoPost('semestre'),
    ];

    $errores = Usuario::erroresDatosAdministrables($usuarioEdicion);
    if ($errores === []) {
        Usuario::actualizarDatos($usuarioId, $usuarioEdicion);
        flash('exito', 'Los datos de la cuenta fueron actualizados.');
        redirigir($rutaListado);
    }
} elseif (isset($_GET['editar'])) {
    $usuarioId = filter_var($_GET['editar'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!is_int($usuarioId)) {
        abortar(404, 'Esa cuenta no existe.');
    }

    $usuarioEncontrado = Usuario::buscarPorId($usuarioId);
    if ($usuarioEncontrado === null) {
        abortar(404, 'Esa cuenta no existe.');
    }
    if ($usuarioEncontrado['rol'] === 'admin') {
        abortar(403, 'Las cuentas de administrador son de solo lectura.');
    }

    $usuarioEdicion = [
        'id' => (int) $usuarioEncontrado['id'],
        'nombre' => (string) $usuarioEncontrado['nombre'],
        'apellidos' => (string) $usuarioEncontrado['apellidos'],
        'correo' => (string) $usuarioEncontrado['correo'],
        'matricula' => (string) ($usuarioEncontrado['matricula'] ?? ''),
        'rol' => (string) $usuarioEncontrado['rol'],
        'licenciatura_id' => (string) $usuarioEncontrado['licenciatura_id'],
        'semestre' => (string) $usuarioEncontrado['semestre'],
    ];
}

vista('admin/usuarios', [
    'titulo' => 'Usuarios',
    'usuarios' => Usuario::todos($rolFiltro),
    'roles' => $roles,
    'rolFiltro' => $rolFiltro,
    'licenciaturas' => $licenciaturas,
    'usuarioEdicion' => $usuarioEdicion,
    'errores' => $errores,
    'rutaListado' => $rutaListado,
]);
