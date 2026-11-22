<?php
/**
 * API de estadísticas (ISS-30): indicadores de demanda de RF-17 para el administrador.
 * Alimenta la pantalla de estadísticas (ISS-32) con el contrato de §4 de la planeación final.
 *
 * GET ?desde=AAAA-MM-DD&hasta=AAAA-MM-DD, ambos opcionales e inclusivos sobre sesiones.fecha.
 * Sin ninguno cuenta el histórico; con uno solo, el otro extremo queda abierto. Responde JSON
 * también en los errores (401, 403 y 400), con el mismo patrón que api/horarios.php.
 * Solo lee, por eso no lleva CSRF.
 */
declare(strict_types=1);

require __DIR__ . '/../../app/includes/bootstrap.php';

// Como en api/horarios.php, se vuelve a leer el usuario para no atender cuentas desactivadas
$sesion = usuarioActual();
$usuario = $sesion === null ? null : Usuario::buscarPorId((int) $sesion['id']);
if ($usuario === null || (int) $usuario['activo'] !== 1) {
    responderJson(['error' => 'Inicia sesión para ver las estadísticas.'], 401);
}
if ($usuario['rol'] !== 'admin') {
    responderJson(['error' => 'Solo los administradores pueden consultar las estadísticas.'], 403);
}

$desde = filter_input(INPUT_GET, 'desde');
$hasta = filter_input(INPUT_GET, 'hasta');
// Si el parámetro no viene o viene vacío, ese extremo queda abierto (null)
$desde = is_string($desde) && $desde !== '' ? $desde : null;
$hasta = is_string($hasta) && $hasta !== '' ? $hasta : null;

// Como en errorDeFecha(): volver a darle formato detecta las fechas que PHP "corrige", como 2026-02-30
foreach ([$desde, $hasta] as $fecha) {
    if ($fecha === null) {
        continue;
    }
    $dia = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
    if ($dia === false || $dia->format('Y-m-d') !== $fecha) {
        responderJson(['error' => 'Envía desde y hasta como fechas válidas AAAA-MM-DD.'], 400);
    }
}
// 'AAAA-MM-DD' ordena como texto igual que en el tiempo
if ($desde !== null && $hasta !== null && $desde > $hasta) {
    responderJson(['error' => 'La fecha desde no puede ser posterior a la fecha hasta.'], 400);
}

$resumen = Sesion::resumenEntre($desde, $hasta);
$resenas = Resena::resumenEntre($desde, $hasta);

responderJson([
    'desde' => $desde,
    'hasta' => $hasta,
    'solicitadas' => $resumen['solicitadas'],
    'completadas' => $resumen['completadas'],
    'canceladas' => $resumen['canceladas'],
    'resenas' => [
        // Un decimal, como pide el contrato; null se queda en null cuando no hay reseñas
        'promedio' => $resenas['promedio'] === null ? null : round($resenas['promedio'], 1),
        'total' => $resenas['total'],
    ],
    'por_materia' => Sesion::demandaPorMateria($desde, $hasta),
]);