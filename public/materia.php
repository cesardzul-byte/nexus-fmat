<?php
/**
 * Detalle de materia (ISS-09): muestra la materia, sus asesores con su
 * licenciatura y los horarios que publicó cada uno. Es pública.
 *
 * Recibe ?id= por GET. Si el id no es un número válido, o la materia no
 * existe o está inactiva, responde 404.
 */
require __DIR__ . '/../app/includes/bootstrap.php';

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
        // DIAS_SEMANA y normalizarHora() vienen de horario.php; la vista muestra 'Lunes' y 'HH:MM'
        $asesores[$i]['bloques'][$j]['dia'] = ucfirst(DIAS_SEMANA[$bloque['dia_semana']] ?? '');
        $asesores[$i]['bloques'][$j]['horario'] = normalizarHora($bloque['hora_inicio'])
            . ' – ' . normalizarHora($bloque['hora_fin']);
    }
}

vista('materia', [
    'titulo' => $materia['nombre'],
    'materia' => $materia,
    'asesores' => $asesores,
    // ISS-18: decide si cada asesor muestra "Agendar" (alumno) o "Inicia sesión para agendar" (visitante)
    'usuario' => usuarioActual(),
    // ISS-24 · Promedio del asesor 
    'promedios' => Resena::promediosPorAsesor(),
]);
