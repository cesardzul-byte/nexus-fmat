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

echo "OK: horario_check pasó todos los casos.\n";
