<?php
/**
 * Reglas de horario: qué bloque es válido (RN-03) y cuándo dos intervalos se empalman (RN-04).
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
