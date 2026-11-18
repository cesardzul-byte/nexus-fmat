<?php
/**
 * Administración de materias (ISS-22): el administrador crea, edita, desactiva y
 * vuelve a activar materias (RF-07, RF-08, RF-09). Nunca se borra una materia (RN-10).
 *
 * GET ?editar=ID carga esa materia en el formulario. Un id que no existe responde 404.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';

// Solo el administrador entra: el visitante va al login y los demás roles reciben 403
requiereRol('admin');

// Igual que los VARCHAR(50) y VARCHAR(100) de materias.clave y materias.nombre
const LONGITUD_MAX_CLAVE = 50;
const LONGITUD_MAX_NOMBRE = 100;
// Valores del formulario al crear una materia; id 0 significa "nueva"
const MATERIA_NUEVA = ['id' => 0, 'clave' => '', 'nombre' => ''];
// Código SQLSTATE de MariaDB cuando se rompe una llave única
const SQLSTATE_DUPLICADO = '23000';

/**
 * Revisa que un texto no esté vacío, sea UTF-8 válido y no pase del máximo.
 *
 * @param string $texto Texto ya sin espacios a los lados.
 * @param int $maximo Largo máximo en caracteres.
 * @return bool true si el texto se puede guardar.
 */
function textoValido(string $texto, int $maximo): bool
{
    // Un texto que no es UTF-8 (petición modificada) lo rechazaría la BD con una excepción
    return $texto !== '' && mb_check_encoding($texto, 'UTF-8') && mb_strlen($texto) <= $maximo;
}

/**
 * Reúne los errores de la materia, uno por campo, para mostrarlos junto a cada campo.
 * Todo se revisa en el servidor, aunque el HTML se haya modificado.
 *
 * @param array{id: int, clave: string, nombre: string} $materia Materia leída del formulario.
 * @return array<string, string> Mensajes con llave 'clave' o 'nombre'; vacío si se puede guardar.
 */
function erroresDeMateria(array $materia): array
{
    $errores = [];
    if (!textoValido($materia['clave'], LONGITUD_MAX_CLAVE)) {
        $errores['clave'] = 'Escribe la clave, con ' . LONGITUD_MAX_CLAVE . ' caracteres como máximo.';
    } elseif (Materia::existeClave($materia['clave'], $materia['id'])) {
        $errores['clave'] = 'Ya existe una materia con esa clave.';
    }
    if (!textoValido($materia['nombre'], LONGITUD_MAX_NOMBRE)) {
        $errores['nombre'] = 'Escribe el nombre, con ' . LONGITUD_MAX_NOMBRE . ' caracteres como máximo.';
    }
    return $errores;
}

/**
 * Crea o actualiza la materia. Si otra persona guardó la misma clave justo antes,
 * la llave única de la BD lo detiene y se devuelve el mismo error que existeClave().
 *
 * @param array{id: int, clave: string, nombre: string} $materia Materia ya validada.
 * @return array<string, string> Vacío si se guardó; si no, el error de la clave.
 */
function guardarMateria(array $materia): array
{
    try {
        if ($materia['id'] === 0) {
            Materia::crear($materia['clave'], $materia['nombre']);
            flash('exito', 'Materia creada.');
        } else {
            Materia::actualizar($materia['id'], $materia['clave'], $materia['nombre']);
            flash('exito', 'Materia actualizada.');
        }
    } catch (PDOException $excepcion) {
        if ($excepcion->getCode() !== SQLSTATE_DUPLICADO) {
            throw $excepcion;
        }
        return ['clave' => 'Ya existe una materia con esa clave.'];
    }
    return [];
}

$materia = MATERIA_NUEVA;
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    $id = enteroPost('id');
    $accion = textoPost('accion');

    // Con id solo se trabaja sobre una materia que existe
    if ($id !== 0 && Materia::buscarPorId($id) === null) {
        abortar(404, 'Esa materia no existe.');
    }

    if ($accion === 'activar' || $accion === 'desactivar') {
        if ($id === 0) {
            abortar(404, 'Esa materia no existe.');
        }
        Materia::cambiarActiva($id, $accion === 'activar');
        flash('exito', $accion === 'activar' ? 'Materia activada.' : 'Materia desactivada.');
        redirigir('admin_materias.php');
    }

    $materia = [
        'id' => $id,
        'clave' => textoPost('clave'),
        'nombre' => textoPost('nombre'),
    ];
    $errores = erroresDeMateria($materia);
    if ($errores === []) {
        $errores = guardarMateria($materia);
    }
    if ($errores === []) {
        redirigir('admin_materias.php');
    }
    // Con errores no se redirige: el formulario se vuelve a mostrar con lo que escribió el administrador
} elseif (isset($_GET['editar'])) {
    $encontrada = Materia::buscarPorId((int) filter_var($_GET['editar'], FILTER_VALIDATE_INT));
    if ($encontrada === null) {
        abortar(404, 'Esa materia no existe.');
    }
    $materia = [
        'id' => (int) $encontrada['id'],
        'clave' => $encontrada['clave'],
        'nombre' => $encontrada['nombre'],
    ];
}

vista('admin/materias', [
    'titulo' => 'Materias',
    'materias' => Materia::todas(),
    'materia' => $materia,
    'errores' => $errores,
]);
