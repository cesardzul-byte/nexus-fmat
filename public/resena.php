<?php
/**
 * Calificar una sesión (ISS-24): el alumno deja una reseña de 1 a 5 y un comentario opcional
 * en una sesión suya que ya está completada. Cada sesión se califica una sola vez (RF-16, RN-08).
 *
 * Recibe ?sesion= por GET (también al enviar el formulario). Responde 404 si la sesión no existe
 * o no es del alumno; si no está completada o ya tiene reseña, muestra un aviso en lugar del formulario.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';

// Solo los alumnos califican: el visitante va al login y los demás roles reciben 403
requiereRol('alumno');

$sesionId = (int) filter_input(INPUT_GET, 'sesion', FILTER_VALIDATE_INT);
$sesion = $sesionId > 0 ? Sesion::buscar($sesionId) : null;
// La sesión de otro alumno responde igual que una que no existe, para no revelar que existe
if ($sesion === null || (int) $sesion['alumno_id'] !== (int) usuarioActual()['id']) {
    abortar(404, 'Esa sesión no existe.');
}

$avisoSesion = null;
if ($sesion['estado'] !== 'completada') {
    $avisoSesion = 'Solo puedes calificar una sesión cuando el asesor la marca como completada.';
} elseif (Resena::existeDeSesion($sesionId)) {
    $avisoSesion = 'Esta sesión ya tiene reseña.';
}

$calificacion = 0;
$comentario = '';
$errores = [];

// Con aviso no se procesa el POST: así se rechaza calificar una sesión no completada o ya calificada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $avisoSesion === null) {
    verificarCsrf();
    $calificacion = enteroPost('calificacion');
    $comentario = textoPost('comentario');

    $errores = Resena::validar($calificacion, $comentario);
    if ($errores === []) {
        if (Resena::crear($sesionId, $calificacion, $comentario)) {
            flash('exito', 'Gracias, tu reseña quedó guardada.');
            redirigir('mis_sesiones.php');
        }
        // Otra pestaña la guardó entre el aviso de arriba y este INSERT: lo frenó la UNIQUE
        $avisoSesion = 'Esta sesión ya tiene reseña.';
    }
}

$materia = Materia::buscarPorId((int) $sesion['materia_id']);
$asesor = Usuario::buscarPorId((int) $sesion['asesor_id']);

vista('resenas/crear', [
    'titulo' => 'Calificar sesión',
    'sesion' => $sesion,
    'materia' => $materia['nombre'] ?? 'Materia',
    'asesor' => $asesor === null ? 'Asesor' : $asesor['nombre'] . ' ' . $asesor['apellidos'],
    'fecha' => date('d/m/Y', strtotime((string) $sesion['fecha'])),
    'avisoSesion' => $avisoSesion,
    'errores' => $errores,
    'calificacion' => $calificacion,
    'comentario' => $comentario,
    'rutaFormulario' => 'resena.php?sesion=' . $sesionId,
]);