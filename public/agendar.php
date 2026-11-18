<?php
/**
 * Agendar una sesión (ISS-18): el alumno pide una sesión con un asesor en una fecha
 * y dentro de uno de sus bloques de esa materia. Sesion::agendar() valida todo en el
 * servidor y la rechaza si choca con otra sesión del asesor o del alumno.
 *
 * Recibe ?materia=&asesor= por GET (también al enviar el formulario). Responde 404 si la
 * materia o el asesor están inactivos, o si el asesor no imparte la materia.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';

// Solo los alumnos agendan: el visitante va al login y los demás roles reciben 403
requiereRol('alumno');

$materiaId = (int) filter_input(INPUT_GET, 'materia', FILTER_VALIDATE_INT);
$asesorId = (int) filter_input(INPUT_GET, 'asesor', FILTER_VALIDATE_INT);
$materia = Materia::buscarPorId($materiaId);
$asesor = Usuario::buscarPorId($asesorId);
if (
    $materia === null || (int) $materia['activa'] !== 1
    || $asesor === null || (int) $asesor['activo'] !== 1
    || !AsesorMateria::imparte($asesorId, $materiaId)
) {
    abortar(404, 'Ese asesor no da esa materia o ya no está disponible.');
}

$solicitud = ['fecha' => '', 'hora_inicio' => '', 'hora_fin' => '', 'tema' => ''];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    $solicitud = [
        'fecha' => textoPost('fecha'),
        'hora_inicio' => textoPost('hora_inicio'),
        'hora_fin' => textoPost('hora_fin'),
        'tema' => textoPost('tema'),
    ];
    // El alumno SIEMPRE sale de la sesión, nunca del formulario
    $errores = Sesion::agendar(array_merge($solicitud, [
        'alumno_id' => (int) usuarioActual()['id'],
        'asesor_id' => $asesorId,
        'materia_id' => $materiaId,
    ]));

    if ($errores === []) {
        flash('exito', 'Sesión solicitada. Queda pendiente hasta que el asesor la confirme.');
        // ===== ISS-19 · Después de agendar, consultar Mis sesiones (P2) =====
        redirigir('mis_sesiones.php');
    }
    // Con errores no se redirige: el formulario se vuelve a mostrar con lo que escribió el alumno
}

// Solo los bloques de esta materia, con el texto del día y del horario listo para la vista
$bloques = [];
foreach (Disponibilidad::deAsesor($asesorId) as $bloque) {
    if ((int) $bloque['materia_id'] !== $materiaId) {
        continue;
    }
    $bloque['dia'] = ucfirst(DIAS_SEMANA[$bloque['dia_semana']]);
    $bloque['horario'] = normalizarHora($bloque['hora_inicio']) . ' – ' . normalizarHora($bloque['hora_fin']);
    $bloques[] = $bloque;
}

vista('sesiones/agendar', [
    'titulo' => 'Agendar sesión',
    'materia' => $materia,
    'asesor' => $asesor,
    'bloques' => $bloques,
    'solicitud' => $solicitud,
    'errores' => $errores,
    'hoy' => date('Y-m-d'),
    'rutaFormulario' => 'agendar.php?materia=' . $materiaId . '&asesor=' . $asesorId,
]);
