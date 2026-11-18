<?php
/**
 * Materias del asesor (ISS-11): el asesor ve las materias que imparte,
 * agrega una materia activa que todavía no tenga o quita una de las suyas.
 */
require __DIR__ . '/../app/includes/bootstrap.php';

// Solo los asesores entran: el visitante va al login y los demás roles reciben 403
requiereRol('asesor');
$usuario = usuarioActual();

//El asesor SIEMPRE sale de la sesión, nunca del formulario
$asesorId = (int) $usuario['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    
    // textoPost() y enteroPost() descartan arreglos: materia_id[]=1 llega como 0 (ISS-25)
    $accion = textoPost('accion');
    $materiaId = enteroPost('materia_id');

    if ($accion === 'agregar') {
        $materia = Materia::buscarPorId($materiaId);

        if ($materia === null ){
            flash('error', 'Esa materia no existe.');
        } else if (!$materia['activa']) {
            flash('error', 'Esa materia no está activa.');
        } else if (AsesorMateria::imparte($asesorId, $materiaId)) {
            flash('error', 'Ya impartes esa materia.');
        } else {
            AsesorMateria::agregar($asesorId, $materiaId);
            flash('exito', 'Materia agregada correctamente.');
        }
    } else if ($accion === 'quitar') {
        // ISS-25: no se quita una materia con sesiones pendientes o confirmadas de hoy en adelante
        $vivas = Sesion::contarVivasDeMateria($asesorId, $materiaId);

        if ($vivas > 0) {
            flash('error', 'No puedes quitar esta materia: tienes ' . $vivas
                . ($vivas === 1 ? ' sesión pendiente o confirmada' : ' sesiones pendientes o confirmadas')
                . '. Atiéndelas o cancélalas antes.');
        } else if (AsesorMateria::quitar($asesorId, $materiaId)) {
            flash('exito', 'Quitaste la materia y sus bloques de disponibilidad.');
        } else {
            flash('error', 'Esa materia no está en tu lista.');
        }
    }
    //POST-Redirect-GET: despues de un POST siempre se redirige
    redirigir('mis_materias.php');
}

//GET: armar los datos para la vista
$materias = AsesorMateria::materiasDe($asesorId);
$idsMias = array_column($materias, 'id');

$disponibles = array_filter(
    Materia::activas(),
    fn($m) => !in_array($m['id'], $idsMias)
);

vista('asesor/materias', [
    'titulo' => 'Mis materias',
    'materias' => $materias,
    'disponibles' => $disponibles,
]);