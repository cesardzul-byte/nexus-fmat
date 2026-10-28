<?php
/**
 * Detalle de materia (ISS-09): muestra la materia, sus asesores con su
 * licenciatura y los horarios que publicó cada uno. Es pública.
 *
 * Recibe ?id= por GET. Si el id no es un número válido, o la materia no
 * existe o está inactiva, responde 404.
 */
require __DIR__ . '/../app/includes/bootstrap.php';

// dia_semana se guarda como 1 = lunes … 6 = sábado, igual que date('N') en PHP
const NOMBRES_DIA = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
];
// Las horas llegan como 'HH:MM:SS'; para mostrarlas solo se usan horas y minutos
const LONGITUD_HORA_CORTA = 5;

$materiaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!is_int($materiaId)) {
    abortar(404, 'Esa materia no existe.');
}

$materia = Materia::buscarPorId($materiaId);
if ($materia === null || (int) $materia['activa'] !== 1) {
    abortar(404, 'Esa materia no existe o ya no está disponible.');
}

// Se preparan los textos de cada bloque aquí para que la vista solo los imprima
$asesores = Materia::asesoresConDisponibilidad($materiaId);
foreach ($asesores as $i => $asesor) {
    foreach ($asesor['bloques'] as $j => $bloque) {
        $asesores[$i]['bloques'][$j]['dia'] = NOMBRES_DIA[$bloque['dia_semana']] ?? '';
        $asesores[$i]['bloques'][$j]['horario'] = substr($bloque['hora_inicio'], 0, LONGITUD_HORA_CORTA)
            . ' – ' . substr($bloque['hora_fin'], 0, LONGITUD_HORA_CORTA);
    }
}

vista('materia', [
    'titulo' => $materia['nombre'],
    'materia' => $materia,
    'asesores' => $asesores,
]);
