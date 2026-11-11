<?php
/**
 * Mis sesiones (ISS-19): alumno consulta sus asesorías y asesor atiende solicitudes.
 * Las acciones se validan en el servidor y usan POST-Redirect-GET con protección CSRF.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';

requiereRol('alumno', 'asesor');

$usuario = usuarioActual();
$usuarioId = (int) $usuario['id'];
$rol = (string) $usuario['rol'];
$zonaHoraria = new DateTimeZone('America/Merida');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $sesionId = enteroPost('sesion_id');
    $estadoNuevo = textoPost('estado_nuevo');
    if ($sesionId < 1) {
        abortar(404, 'Esa sesión no existe.');
    }

    $sesion = Sesion::buscar($sesionId);
    if ($sesion === null) {
        abortar(404, 'Esa sesión no existe.');
    }

    // No se revela si existe una sesión perteneciente a otra persona.
    $columnaParticipante = $rol === 'alumno' ? 'alumno_id' : 'asesor_id';
    if ((int) $sesion[$columnaParticipante] !== $usuarioId) {
        abortar(404, 'Esa sesión no existe.');
    }

    $estadoActual = (string) $sesion['estado'];
    if (!Sesion::transicionPermitida($rol, $estadoActual, $estadoNuevo)) {
        abortar(403, 'No tienes permiso para realizar ese cambio de estado.');
    }

    if ($estadoNuevo === 'completada') {
        $fechaHoraFin = new DateTimeImmutable(
            (string) $sesion['fecha'] . ' ' . (string) $sesion['hora_fin'],
            $zonaHoraria
        );
        if ($fechaHoraFin > new DateTimeImmutable('now', $zonaHoraria)) {
            flash('error', 'Solo puedes completar una sesión después de que termine su horario.');
            redirigir('mis_sesiones.php');
        }
    }

    if (!Sesion::cambiarEstado($sesionId, $usuarioId, $rol, $estadoActual, $estadoNuevo)) {
        flash('error', 'No se pudo actualizar la sesión. Actualiza la página e inténtalo de nuevo.');
        redirigir('mis_sesiones.php');
    }

    $mensajesExito = [
        'confirmada' => 'La sesión quedó confirmada.',
        'cancelada' => 'La sesión fue cancelada.',
        'completada' => 'La sesión quedó marcada como completada.',
    ];
    flash('exito', $mensajesExito[$estadoNuevo]);
    redirigir('mis_sesiones.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    abortar(405, 'Método no permitido.');
}

$estadosProximos = ['pendiente', 'confirmada'];
$estadosTexto = [
    'pendiente' => 'Pendiente',
    'confirmada' => 'Confirmada',
    'completada' => 'Completada',
    'cancelada' => 'Cancelada',
];
$definicionAcciones = [
    'confirmada' => [
        'texto' => 'Confirmar',
        'clase' => 'boton boton-destacado',
    ],
    'cancelada' => [
        'texto' => 'Cancelar',
        'clase' => 'boton boton-peligro',
        'confirmacion' => '¿Cancelar esta sesión? La acción no se puede deshacer.',
    ],
    'completada' => [
        'texto' => 'Marcar como completada',
        'clase' => 'boton boton-secundario',
    ],
];

$proximas = [];
$historial = [];
$ahora = new DateTimeImmutable('now', $zonaHoraria);

foreach (Sesion::deUsuario($usuarioId, $rol) as $sesion) {
    $sesion['fecha_formato'] = (new DateTimeImmutable((string) $sesion['fecha'], $zonaHoraria))->format('d/m/Y');
    $sesion['horario'] = normalizarHora((string) $sesion['hora_inicio'])
        . ' – ' . normalizarHora((string) $sesion['hora_fin']);
    $sesion['lugar'] = is_string($sesion['lugar']) && trim($sesion['lugar']) !== ''
        ? $sesion['lugar']
        : 'Por acordar';
    $sesion['estado_texto'] = $estadosTexto[$sesion['estado']] ?? 'Desconocido';
    $sesion['estado_clase'] = 'etiqueta-' . (string) $sesion['estado'];

    $fechaHoraFin = new DateTimeImmutable(
        (string) $sesion['fecha'] . ' ' . (string) $sesion['hora_fin'],
        $zonaHoraria
    );
    $sesion['puede_completar'] = $rol === 'asesor'
        && $sesion['estado'] === 'confirmada'
        && $fechaHoraFin <= $ahora;

    $sesion['acciones'] = [];
    foreach ($definicionAcciones as $estadoNuevo => $accion) {
        if (!Sesion::transicionPermitida($rol, (string) $sesion['estado'], $estadoNuevo)) {
            continue;
        }
        if ($estadoNuevo === 'completada' && !$sesion['puede_completar']) {
            continue;
        }
        $sesion['acciones'][] = [
            'estado' => $estadoNuevo,
            'texto' => $accion['texto'],
            'clase' => $accion['clase'],
            'confirmacion' => $accion['confirmacion'] ?? null,
        ];
    }

    if (in_array($sesion['estado'], $estadosProximos, true)) {
        $proximas[] = $sesion;
    } else {
        $historial[] = $sesion;
    }
}

// Las próximas van de la más cercana a la más lejana; el historial de la más reciente a la más antigua.
usort($proximas, static fn(array $a, array $b): int => [$a['fecha'], $a['hora_inicio']] <=> [$b['fecha'], $b['hora_inicio']]);
usort($historial, static fn(array $a, array $b): int => [$b['fecha'], $b['hora_inicio']] <=> [$a['fecha'], $a['hora_inicio']]);

vista('sesiones/mis_sesiones', [
    'titulo' => $rol === 'alumno' ? 'Mis sesiones' : 'Solicitudes y sesiones',
    'rol' => $rol,
    'proximas' => $proximas,
    'historial' => $historial,
]);
