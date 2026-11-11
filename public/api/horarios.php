<?php
/**
 * API de horarios (ISS-20): estado de cada intervalo de 30 minutos, de lunes a sábado y de
 * 07:00 a 21:00, para un asesor y una materia. Alimenta la tabla semanal de agendar (RF-05).
 *
 * GET ?asesor=&materia=&lunes=AAAA-MM-DD. Responde JSON también en los errores (401, 403 y 400):
 * fetch no debe recibir la página HTML de abortar() ni seguir la redirección de requiereRol().
 * Solo lee, por eso no lleva CSRF. Es informativa: quien decide es Sesion::agendar(), así que no
 * marca los choques del propio alumno. No envía nombres ni temas de nadie, solo el estado.
 */
declare(strict_types=1);

require __DIR__ . '/../../app/includes/bootstrap.php';

// Como en requiereLogin(), se vuelve a leer el usuario para no atender cuentas desactivadas
$sesion = usuarioActual();
$usuario = $sesion === null ? null : Usuario::buscarPorId((int) $sesion['id']);
if ($usuario === null || (int) $usuario['activo'] !== 1) {
    responderJson(['error' => 'Inicia sesión para ver los horarios.'], 401);
}
if ($usuario['rol'] !== 'alumno') {
    responderJson(['error' => 'Solo los alumnos pueden consultar los horarios.'], 403);
}

$asesorId = filter_input(INPUT_GET, 'asesor', FILTER_VALIDATE_INT);
$materiaId = filter_input(INPUT_GET, 'materia', FILTER_VALIDATE_INT);
$lunesTexto = filter_input(INPUT_GET, 'lunes');
// Como en errorDeFecha(): volver a darle formato detecta las fechas que PHP "corrige", como 2026-02-30.
// format('N') da 1 para el lunes
$lunes = is_string($lunesTexto) ? DateTimeImmutable::createFromFormat('!Y-m-d', $lunesTexto) : false;
if (
    !is_int($asesorId) || !is_int($materiaId)
    || $lunes === false || $lunes->format('Y-m-d') !== $lunesTexto || $lunes->format('N') !== '1'
) {
    responderJson(['error' => 'Envía asesor y materia como números, y lunes como la fecha AAAA-MM-DD de un lunes.'], 400);
}

// Fecha de cada día de la semana, con la misma clave que dia_semana (1 = lunes … 6 = sábado)
$fechas = [];
foreach (array_keys(DIAS_SEMANA) as $dia) {
    $fechas[$dia] = $lunes->modify('+' . ($dia - 1) . ' days')->format('Y-m-d');
}

// Una consulta de bloques y una de sesiones para toda la semana, agrupadas para buscarlas por día
$bloquesPorDia = [];
foreach (Disponibilidad::deAsesor($asesorId) as $bloque) {
    if ((int) $bloque['materia_id'] === $materiaId) {
        $bloquesPorDia[(int) $bloque['dia_semana']][] = $bloque;
    }
}
$sesionesPorFecha = [];
foreach (Sesion::deAsesorEntre($asesorId, $lunesTexto, end($fechas)) as $sesionAsesor) {
    $sesionesPorFecha[$sesionAsesor['fecha']][] = $sesionAsesor;
}

// Intervalos de 07:00–07:30 a 20:30–21:00: DatePeriod no incluye la hora de cierre como inicio
$paso = new DateInterval('PT' . MINUTOS_BLOQUE . 'M');
$horas = [];
foreach (new DatePeriod(new DateTimeImmutable(HORA_APERTURA), $paso, new DateTimeImmutable(HORA_CIERRE)) as $hora) {
    $horas[] = [$hora->format('H:i'), $hora->add($paso)->format('H:i')];
}

$ahora = new DateTimeImmutable();
$dias = [];
foreach ($fechas as $dia => $fecha) {
    $intervalos = [];
    foreach ($horas as [$inicio, $fin]) {
        // Un intervalo que ya empezó no se puede reservar: misma regla que usa agendar()
        $estado = errorDeFecha($fecha, $inicio, $ahora) !== null
            ? 'sin_bloque'
            : estadoIntervalo($inicio, $fin, $bloquesPorDia[$dia] ?? [], $sesionesPorFecha[$fecha] ?? []);
        $intervalos[] = ['inicio' => $inicio, 'fin' => $fin, 'estado' => $estado];
    }
    $dias[] = ['fecha' => $fecha, 'intervalos' => $intervalos];
}

responderJson(['lunes' => $lunesTexto, 'dias' => $dias]);
