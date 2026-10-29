<?php
/**
 * Disponibilidad del asesor (ISS-12): el asesor publica, edita y elimina sus bloques
 * semanales (materia, día, inicio, fin y lugar). Un bloque se rechaza si sale del
 * horario permitido o si se empalma con otro bloque suyo, aunque sea de otra materia.
 *
 * GET ?editar=ID carga ese bloque en el formulario. Un id que no es del asesor responde 404.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';
require __DIR__ . '/../app/includes/horario.php';

// Solo los asesores entran: el visitante va al login y los demás roles reciben 403
requiereRol('asesor');

// Igual que el VARCHAR(100) de disponibilidad.lugar
const LONGITUD_MAX_LUGAR = 100;
// Valores del formulario al publicar un bloque nuevo; id 0 significa "nuevo"
const BLOQUE_NUEVO = [
    'id' => 0,
    'materia_id' => 0,
    'dia_semana' => 0,
    'hora_inicio' => '',
    'hora_fin' => '',
    'lugar' => '',
];

/**
 * Lee un campo de texto del POST. Un arreglo (HTML modificado) cuenta como vacío.
 *
 * @param string $campo Nombre del campo.
 * @return string Valor sin espacios a los lados, o '' si no llegó.
 */
function textoPost(string $campo): string
{
    $valor = $_POST[$campo] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

/**
 * Lee un campo entero del POST.
 *
 * @param string $campo Nombre del campo.
 * @return int El entero, o 0 si no llegó o no es un entero válido.
 */
function enteroPost(string $campo): int
{
    return (int) filter_var($_POST[$campo] ?? null, FILTER_VALIDATE_INT);
}

/**
 * Busca los bloques del mismo asesor y día que chocan con el bloque, de cualquier materia.
 *
 * @param int $asesorId Id del asesor en sesión.
 * @param array<string, mixed> $bloque Bloque con día y horas ya validados por validarBloque().
 * @return array<int, string> Un mensaje por cada bloque con el que se empalma.
 */
function mensajesDeEmpalme(int $asesorId, array $bloque): array
{
    $mensajes = [];
    foreach (Disponibilidad::delDia($asesorId, $bloque['dia_semana']) as $otro) {
        // Al editar, el bloque no se compara consigo mismo
        if ((int) $otro['id'] === $bloque['id']) {
            continue;
        }
        if (seEmpalman($bloque['hora_inicio'], $bloque['hora_fin'], $otro['hora_inicio'], $otro['hora_fin'])) {
            $mensajes[] = 'Se empalma con ' . $otro['materia'] . ', ' . DIAS_SEMANA[$bloque['dia_semana']] . ' '
                . normalizarHora($otro['hora_inicio']) . '–' . normalizarHora($otro['hora_fin']) . '.';
        }
    }
    return $mensajes;
}

/**
 * Reúne todos los errores del bloque: horario permitido, empalmes, materia y lugar.
 * Todo se revisa en el servidor, aunque el HTML se haya modificado.
 *
 * @param int $asesorId Id del asesor en sesión.
 * @param array<string, mixed> $bloque Bloque leído del formulario.
 * @return array<int, string> Mensajes de error; vacío si se puede guardar.
 */
function erroresDelBloque(int $asesorId, array $bloque): array
{
    $errores = validarBloque($bloque['dia_semana'], $bloque['hora_inicio'], $bloque['hora_fin']);
    // Los empalmes solo se buscan con un día y unas horas válidos
    if ($errores === []) {
        $errores = mensajesDeEmpalme($asesorId, $bloque);
    }
    if (!AsesorMateria::imparte($asesorId, $bloque['materia_id'])) {
        $errores[] = 'Elige una de las materias que impartes.';
    }
    // Un texto que no es UTF-8 (petición modificada) lo rechazaría la BD con una excepción
    $lugar = $bloque['lugar'];
    if ($lugar === '' || !mb_check_encoding($lugar, 'UTF-8') || mb_strlen($lugar) > LONGITUD_MAX_LUGAR) {
        $errores[] = 'Escribe el lugar, con ' . LONGITUD_MAX_LUGAR . ' caracteres como máximo.';
    }
    return $errores;
}

// El asesor SIEMPRE sale de la sesión, nunca del formulario
$asesorId = (int) usuarioActual()['id'];
$bloque = BLOQUE_NUEVO;
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    $id = enteroPost('id');

    if (textoPost('accion') === 'eliminar') {
        if (!Disponibilidad::eliminar($id, $asesorId)) {
            abortar(404, 'Ese bloque no existe.');
        }
        flash('exito', 'Bloque eliminado.');
        redirigir('disponibilidad.php');
    }

    // Guardar: con id edita, pero solo si el bloque es del asesor; sin id publica uno nuevo
    if ($id !== 0 && Disponibilidad::buscar($id, $asesorId) === null) {
        abortar(404, 'Ese bloque no existe.');
    }
    $bloque = [
        'id' => $id,
        'materia_id' => enteroPost('materia_id'),
        'dia_semana' => enteroPost('dia_semana'),
        'hora_inicio' => textoPost('hora_inicio'),
        'hora_fin' => textoPost('hora_fin'),
        'lugar' => textoPost('lugar'),
    ];
    $errores = erroresDelBloque($asesorId, $bloque);

    // ponytail: se revisa y luego se guarda, sin bloqueo, porque solo el propio asesor escribe sus bloques.
    // Las sesiones (revisión 2) sí necesitan transacción, porque las escriben alumno y asesor a la vez.
    if ($errores === []) {
        if ($id === 0) {
            Disponibilidad::crear($asesorId, $bloque);
            flash('exito', 'Bloque publicado.');
        } else {
            Disponibilidad::actualizar($id, $asesorId, $bloque);
            flash('exito', 'Bloque actualizado.');
        }
        redirigir('disponibilidad.php');
    }
    // Con errores no se redirige: el formulario se vuelve a mostrar con lo que escribió el asesor
} elseif (isset($_GET['editar'])) {
    $encontrado = Disponibilidad::buscar((int) filter_var($_GET['editar'], FILTER_VALIDATE_INT), $asesorId);
    if ($encontrado === null) {
        abortar(404, 'Ese bloque no existe.');
    }
    // El input type="time" se llena con 'HH:MM', no con el 'HH:MM:SS' de la BD
    $bloque = array_merge($encontrado, [
        'id' => (int) $encontrado['id'],
        'hora_inicio' => normalizarHora($encontrado['hora_inicio']),
        'hora_fin' => normalizarHora($encontrado['hora_fin']),
    ]);
}

// Se agrupan por día (ya vienen ordenados por día y hora) y se arma el texto del horario para la vista
$bloquesPorDia = [];
foreach (Disponibilidad::deAsesor($asesorId) as $fila) {
    $fila['horario'] = normalizarHora($fila['hora_inicio']) . ' – ' . normalizarHora($fila['hora_fin']);
    $bloquesPorDia[ucfirst(DIAS_SEMANA[$fila['dia_semana']])][] = $fila;
}

vista('asesor/disponibilidad', [
    'titulo' => 'Mi disponibilidad',
    'materias' => AsesorMateria::materiasDe($asesorId),
    'nombresDia' => array_map('ucfirst', DIAS_SEMANA),
    'bloquesPorDia' => $bloquesPorDia,
    'bloque' => $bloque,
    'errores' => $errores,
]);
