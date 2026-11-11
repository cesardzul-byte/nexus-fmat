<?php
/**
 * Verificación de las reglas de horario (app/includes/horario.php). No usa la BD.
 *
 * Uso: php -d zend.assertions=1 tests/horario_check.php
 * Si un caso falla, PHP lanza AssertionError con el nombre del caso y termina con código distinto de 0.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/horario.php';

// Sin zend.assertions=1 los assert() no se evalúan y el archivo "pasaría" sin revisar nada
if ((int) ini_get('zend.assertions') !== 1) {
    fwrite(STDERR, "Ejecuta: php -d zend.assertions=1 tests/horario_check.php\n");
    exit(1);
}

// ----- seEmpalman(): cada caso contra el bloque existente lunes 10:00–12:00 -----

// nombre del caso => [inicio, fin, ¿se empalma?]
$casosEmpalme = [
    'choque parcial al final (11:00–13:00)' => ['11:00', '13:00', true],
    'choque parcial al inicio (09:00–10:30)' => ['09:00', '10:30', true],
    'bloque contenido (10:30–11:30)' => ['10:30', '11:30', true],
    'bloque que lo contiene (09:00–13:00)' => ['09:00', '13:00', true],
    'bloques idénticos (10:00–12:00)' => ['10:00', '12:00', true],
    'solo se tocan al final (12:00–13:00)' => ['12:00', '13:00', false],
    'solo se tocan al inicio (08:00–10:00)' => ['08:00', '10:00', false],
    'separados (14:00–15:00)' => ['14:00', '15:00', false],
];

// El bloque existente va en 'HH:MM' y en 'HH:MM:SS' (como lo devuelve la BD), mezclado con el 'HH:MM' del formulario
foreach ([['10:00', '12:00'], ['10:00:00', '12:00:00']] as [$inicioExistente, $finExistente]) {
    foreach ($casosEmpalme as $caso => [$inicio, $fin, $esperado]) {
        // En los dos órdenes: el resultado no depende de cuál bloque va primero
        assert(seEmpalman($inicio, $fin, $inicioExistente, $finExistente) === $esperado, "$caso contra $inicioExistente");
        assert(seEmpalman($inicioExistente, $finExistente, $inicio, $fin) === $esperado, "$caso contra $inicioExistente, orden inverso");
    }
}

// ----- validarBloque() -----

assert(validarBloque(1, '10:00', '12:00') === [], 'bloque válido');
assert(validarBloque(6, '07:00', '21:00') === [], 'los límites 07:00 y 21:00 se aceptan');
assert(validarBloque(3, '10:30:00', '11:00:00') === [], 'acepta HH:MM:SS');

// nombre del caso => [día, inicio, fin]
$bloquesInvalidos = [
    'día 0' => [0, '10:00', '12:00'],
    'día 7' => [7, '10:00', '12:00'],
    'inicio igual a fin' => [1, '10:00', '10:00'],
    'inicio después de fin' => [1, '12:00', '10:00'],
    'empieza antes de las 07:00' => [1, '06:30', '08:00'],
    'termina después de las 21:00' => [1, '20:00', '21:30'],
    'minutos en :15' => [1, '10:15', '12:00'],
    'minutos en :45' => [1, '10:00', '11:45'],
    'hora vacía' => [1, '', '12:00'],
    'texto que no es hora' => [1, 'abc', '12:00'],
    'hora sin cero a la izquierda' => [1, '7:00', '09:00'],
    'hora 24:00' => [1, '20:00', '24:00'],
    'segundos distintos de :00' => [1, '10:00:15', '12:00'],
];
foreach ($bloquesInvalidos as $caso => [$dia, $inicio, $fin]) {
    assert(validarBloque($dia, $inicio, $fin) !== [], $caso);
}

// ----- dentroDeBloque(): el bloque de Luis, lunes 09:00–11:00, como lo devuelve la BD -----

$bloqueLuis = [['hora_inicio' => '09:00:00', 'hora_fin' => '11:00:00']];
// Dos bloques que solo se tocan (RN-04 los permite)
$bloquesContiguos = [
    ['hora_inicio' => '09:00:00', 'hora_fin' => '11:00:00'],
    ['hora_inicio' => '11:00:00', 'hora_fin' => '13:00:00'],
];

// nombre del caso => [inicio, fin, bloques, ¿cabe?]
$casosBloque = [
    'al inicio del bloque (09:00–10:00)' => ['09:00', '10:00', $bloqueLuis, true],
    'al final del bloque (10:00–11:00)' => ['10:00', '11:00', $bloqueLuis, true],
    'en medio del bloque (09:30–10:30)' => ['09:30', '10:30', $bloqueLuis, true],
    'el bloque completo (09:00–11:00)' => ['09:00', '11:00', $bloqueLuis, true],
    'sale 30 minutos antes (08:30–09:30)' => ['08:30', '09:30', $bloqueLuis, false],
    'sale 30 minutos después (10:30–11:30)' => ['10:30', '11:30', $bloqueLuis, false],
    'contiene al bloque (08:00–12:00)' => ['08:00', '12:00', $bloqueLuis, false],
    'solo toca el final del bloque (11:00–12:00)' => ['11:00', '12:00', $bloqueLuis, false],
    'sin bloques ese día' => ['09:00', '10:00', [], false],
    'en el segundo bloque (11:00–12:00)' => ['11:00', '12:00', $bloquesContiguos, true],
    'entre dos bloques contiguos (10:00–12:00)' => ['10:00', '12:00', $bloquesContiguos, false],
];
foreach ($casosBloque as $caso => [$inicio, $fin, $bloques, $esperado]) {
    assert(dentroDeBloque($inicio, $fin, $bloques) === $esperado, $caso);
}

// ----- errorDeFecha(): "ahora" fijo en el martes 10/11/2026 a las 12:00 -----

$ahora = new DateTimeImmutable('2026-11-10 12:00');

// nombre del caso => [fecha, inicio]
$fechasValidas = [
    'el próximo lunes' => ['2026-11-16', '09:00'],
    'un sábado' => ['2026-11-14', '09:00'],
    'hoy, en una hora que no ha llegado' => ['2026-11-10', '12:30'],
    'acepta HH:MM:SS' => ['2026-11-16', '09:00:00'],
];
foreach ($fechasValidas as $caso => [$fecha, $inicio]) {
    assert(errorDeFecha($fecha, $inicio, $ahora) === null, $caso);
}

$fechasInvalidas = [
    'fecha inexistente (2026-02-30)' => ['2026-02-30', '09:00'],
    'mes 13' => ['2026-13-01', '09:00'],
    'un domingo' => ['2026-11-15', '09:00'],
    'hoy, en una hora que ya pasó' => ['2026-11-10', '11:30'],
    'hoy, a la hora actual' => ['2026-11-10', '12:00'],
    'ayer' => ['2026-11-09', '13:00'],
    'fecha vacía' => ['', '09:00'],
    'texto que no es fecha' => ['abc', '09:00'],
    'formato dd/mm/aaaa' => ['16/11/2026', '09:00'],
    'sin ceros a la izquierda' => ['2026-11-6', '09:00'],
];
foreach ($fechasInvalidas as $caso => [$fecha, $inicio]) {
    assert(errorDeFecha($fecha, $inicio, $ahora) !== null, $caso);
}

echo "OK: horario_check pasó todos los casos.\n";
