<?php
/**
 * Reglas de horario: qué bloque es válido (RN-03), cuándo dos intervalos se empalman (RN-04),
 * si una sesión cabe en un bloque (RN-05), si su fecha se puede agendar y qué estado muestra
 * un intervalo en la tabla semanal (RF-05).
 *
 * Son funciones puras, sin BD ni sesión: las prueba tests/horario_check.php y las
 * reutilizan las sesiones de la revisión 2. Las horas se comparan como texto 'HH:MM',
 * que con ceros a la izquierda ordena igual que el tiempo.
 */
declare(strict_types=1);

// Días válidos; la clave es dia_semana (1 = lunes … 6 = sábado, igual que date('N') en PHP)
const DIAS_SEMANA = [
    1 => 'lunes',
    2 => 'martes',
    3 => 'miércoles',
    4 => 'jueves',
    5 => 'viernes',
    6 => 'sábado',
];
const HORA_APERTURA = '07:00';
const HORA_CIERRE = '21:00';
const MINUTOS_BLOQUE = 30;
// 'HH:MM' son los primeros caracteres de 'HH:MM:SS'; los minutos empiezan después de 'HH:'
const LONGITUD_HORA = 5;
const POSICION_MINUTOS = 3;
// HH:MM con segundos opcionales solo en :00 (así llegan de la BD); rechaza '7:00', '24:00' o '10:00:15'
const PATRON_HORA = '/^([01]\d|2[0-3]):[0-5]\d(:00)?$/';

/**
 * Recorta una hora a 'HH:MM'. La BD devuelve 'HH:MM:SS' y el formulario envía 'HH:MM'.
 *
 * @param string $hora Hora en 'HH:MM' o 'HH:MM:SS'.
 * @return string La misma hora en 'HH:MM'.
 */
function normalizarHora(string $hora): string
{
    return substr($hora, 0, LONGITUD_HORA);
}

/**
 * Indica si dos intervalos del mismo día se empalman.
 *
 * Se usa < y no <= para que dos intervalos que solo se tocan (10:00–11:00 y
 * 11:00–12:00) no cuenten como empalme. Acepta 'HH:MM' y 'HH:MM:SS' mezclados.
 *
 * @param string $ini1 Inicio del primer intervalo.
 * @param string $fin1 Fin del primer intervalo.
 * @param string $ini2 Inicio del segundo intervalo.
 * @param string $fin2 Fin del segundo intervalo.
 * @return bool true si comparten al menos un minuto.
 */
function seEmpalman(string $ini1, string $fin1, string $ini2, string $fin2): bool
{
    return normalizarHora($ini1) < normalizarHora($fin2)
        && normalizarHora($fin1) > normalizarHora($ini2);
}

/**
 * Revisa el día y las horas de un bloque: de lunes a sábado, inicio antes que fin,
 * dentro de 07:00–21:00 y con minutos en :00 o :30.
 *
 * @param int $dia Día de la semana (1 = lunes … 6 = sábado).
 * @param string $inicio Hora de inicio en 'HH:MM' o 'HH:MM:SS'.
 * @param string $fin Hora de fin en 'HH:MM' o 'HH:MM:SS'.
 * @return array<int, string> Mensajes de error; vacío si el bloque es válido.
 */
function validarBloque(int $dia, string $inicio, string $fin): array
{
    $errores = [];
    if (!isset(DIAS_SEMANA[$dia])) {
        $errores[] = 'Elige un día de lunes a sábado.';
    }

    // Sin un formato válido no tiene sentido comparar las horas
    if (preg_match(PATRON_HORA, $inicio) !== 1 || preg_match(PATRON_HORA, $fin) !== 1) {
        $errores[] = 'Escribe las horas con el formato HH:MM.';
        return $errores;
    }

    $inicio = normalizarHora($inicio);
    $fin = normalizarHora($fin);
    if ($inicio >= $fin) {
        $errores[] = 'La hora de inicio debe ser antes que la hora de fin.';
    }
    if ($inicio < HORA_APERTURA || $fin > HORA_CIERRE) {
        $errores[] = 'El bloque debe quedar entre las ' . HORA_APERTURA . ' y las ' . HORA_CIERRE . '.';
    }

    $minutosInicio = (int) substr($inicio, POSICION_MINUTOS);
    $minutosFin = (int) substr($fin, POSICION_MINUTOS);
    if ($minutosInicio % MINUTOS_BLOQUE !== 0 || $minutosFin % MINUTOS_BLOQUE !== 0) {
        $errores[] = 'Las horas deben terminar en :00 o :30.';
    }

    return $errores;
}

// ===== ISS-18 · Agendar una sesión (P4) =====

/**
 * Indica si un intervalo cabe completo dentro de alguno de los bloques (RN-05).
 *
 * Tiene que caber en un solo bloque: con 09:00–11:00 y 11:00–13:00, la sesión
 * 10:00–12:00 no se acepta aunque entre los dos la cubran.
 *
 * @param string $inicio Inicio de la sesión en 'HH:MM' o 'HH:MM:SS'.
 * @param string $fin Fin de la sesión en 'HH:MM' o 'HH:MM:SS'.
 * @param array<int, array{hora_inicio: string, hora_fin: string}> $bloques Bloques del asesor en esa materia y día.
 * @return bool true si inicio ≥ inicio del bloque y fin ≤ fin del bloque en alguno de ellos.
 */
function dentroDeBloque(string $inicio, string $fin, array $bloques): bool
{
    foreach ($bloques as $bloque) {
        if (
            normalizarHora($inicio) >= normalizarHora($bloque['hora_inicio'])
            && normalizarHora($fin) <= normalizarHora($bloque['hora_fin'])
        ) {
            return true;
        }
    }
    return false;
}

/**
 * Revisa que la fecha de una sesión exista, no sea domingo y no haya pasado.
 *
 * @param string $fecha Fecha en 'AAAA-MM-DD', como la envía <input type="date">.
 * @param string $inicio Hora de inicio en 'HH:MM'; con la fecha de hoy decide si ya pasó.
 * @param DateTimeImmutable $ahora Momento actual; se recibe para poder probarla con una hora fija.
 * @return string|null Mensaje de error, o null si la fecha se puede agendar.
 */
function errorDeFecha(string $fecha, string $inicio, DateTimeImmutable $ahora): ?string
{
    // PHP "corrige" 2026-02-30 a 2026-03-02; volver a darle formato y comparar lo detecta
    $dia = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
    if ($dia === false || $dia->format('Y-m-d') !== $fecha) {
        return 'Elige una fecha válida.';
    }
    if (!isset(DIAS_SEMANA[(int) $dia->format('N')])) {
        return 'No hay asesorías en domingo. Elige un día de lunes a sábado.';
    }
    // 'AAAA-MM-DD HH:MM' ordena como texto igual que en el tiempo. Una sesión que empieza este minuto ya pasó
    if ($fecha . ' ' . normalizarHora($inicio) <= $ahora->format('Y-m-d H:i')) {
        return 'Esa fecha y hora ya pasaron.';
    }
    return null;
}

// ===== ISS-20 · API de horarios (P4) =====

/**
 * Decide el estado de un intervalo de la tabla semanal (RF-05).
 *
 * Fuera de los bloques de la materia es 'sin_bloque', aunque haya sesiones. Dentro, una sesión
 * confirmada que lo toque lo deja 'ocupado' y gana sobre una pendiente, que lo deja 'reservado'.
 * Las sesiones canceladas o completadas no cuentan (RN-06). Que el intervalo ya haya pasado lo
 * revisa quien la llama con errorDeFecha().
 *
 * @param string $inicio Inicio del intervalo en 'HH:MM' o 'HH:MM:SS'.
 * @param string $fin Fin del intervalo en 'HH:MM' o 'HH:MM:SS'.
 * @param array<int, array{hora_inicio: string, hora_fin: string}> $bloques Bloques del asesor en esa materia y día.
 * @param array<int, array{hora_inicio: string, hora_fin: string, estado: string}> $sesiones Sesiones del asesor en esa fecha, de cualquier materia.
 * @return string 'disponible', 'reservado', 'ocupado' o 'sin_bloque'.
 */
function estadoIntervalo(string $inicio, string $fin, array $bloques, array $sesiones): string
{
    if (!dentroDeBloque($inicio, $fin, $bloques)) {
        return 'sin_bloque';
    }

    $estado = 'disponible';
    foreach ($sesiones as $sesion) {
        if (!seEmpalman($inicio, $fin, $sesion['hora_inicio'], $sesion['hora_fin'])) {
            continue;
        }
        if ($sesion['estado'] === 'confirmada') {
            return 'ocupado';
        }
        if ($sesion['estado'] === 'pendiente') {
            $estado = 'reservado';
        }
    }
    return $estado;
}
