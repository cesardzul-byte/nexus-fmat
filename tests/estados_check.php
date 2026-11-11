<?php
/**
 * Verifica las transiciones de estado de Sesion::transicionPermitida(). No usa la BD.
 * Uso: php -d zend.assertions=1 tests/estados_check.php
 */
declare(strict_types=1);

require __DIR__ . '/../app/models/Sesion.php';

// Sin zend.assertions=1, PHP omite los assert() y la prueba no comprobaría nada.
if ((int) ini_get('zend.assertions') !== 1) {
    fwrite(STDERR, "Ejecuta: php -d zend.assertions=1 tests/estados_check.php\n");
    exit(1);
}

// Cada fila es [rol, estado actual, estado nuevo, resultado esperado].
$casos = [
    'alumno cancela una pendiente' => ['alumno', 'pendiente', 'cancelada', true],
    'alumno no confirma una pendiente' => ['alumno', 'pendiente', 'confirmada', false],
    'alumno no completa una pendiente' => ['alumno', 'pendiente', 'completada', false],
    'alumno cancela una confirmada' => ['alumno', 'confirmada', 'cancelada', true],
    'alumno no completa una confirmada' => ['alumno', 'confirmada', 'completada', false],
    'asesor confirma una pendiente' => ['asesor', 'pendiente', 'confirmada', true],
    'asesor cancela una pendiente' => ['asesor', 'pendiente', 'cancelada', true],
    'asesor no completa una pendiente' => ['asesor', 'pendiente', 'completada', false],
    'asesor cancela una confirmada' => ['asesor', 'confirmada', 'cancelada', true],
    'asesor completa una confirmada' => ['asesor', 'confirmada', 'completada', true],
    'asesor no vuelve a confirmar una confirmada' => ['asesor', 'confirmada', 'confirmada', false],
    'no se reactiva una cancelada' => ['asesor', 'cancelada', 'pendiente', false],
    'no se modifica una completada' => ['asesor', 'completada', 'cancelada', false],
    'administrador no cambia estados' => ['admin', 'pendiente', 'cancelada', false],
    'rol inválido no cambia estados' => ['visitante', 'pendiente', 'cancelada', false],
    'estado de origen inválido' => ['asesor', 'desconocido', 'confirmada', false],
    'estado de destino inválido' => ['asesor', 'pendiente', 'desconocido', false],
];

foreach ($casos as $nombre => [$rol, $estadoActual, $estadoNuevo, $esperado]) {
    assert(
        Sesion::transicionPermitida($rol, $estadoActual, $estadoNuevo) === $esperado,
        $nombre
    );
}

echo 'OK: estados_check pasó los ' . count($casos) . " casos.\n";
