<?php
/**
 * Modelo de la tabla `sesiones`: asesorías que un alumno agenda con un asesor.
 *
 * ISS-15 lo crea con lo común; ISS-18, 19, 20 y 25 agregan sus métodos al final,
 * cada uno con su comentario de sección (supuesto 9).
 */
declare(strict_types=1);

class Sesion
{
    // Estados que ocupan el horario al revisar empalmes (RN-06); cancelada y completada no lo bloquean
    public const ESTADOS_QUE_RESERVAN = ['pendiente', 'confirmada'];

    /**
     * Busca una sesión por su id, sin filtrar por usuario.
     *
     * Quien la llama comprueba que alumno_id o asesor_id sea el usuario en sesión
     * antes de mostrarla o cambiarla.
     *
     * @param int $id Id de la sesión.
     * @return array<string, mixed>|null La fila completa de `sesiones`, o null si no existe.
     */
    public static function buscar(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT *
               FROM sesiones
              WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // ===== ISS-18 · Agendar una sesión (P4) =====

    // Igual que el VARCHAR(150) de sesiones.tema
    public const LONGITUD_MAX_TEMA = 150;

    /**
     * Agenda una sesión en estado pendiente si cumple RN-03, RN-05 y RN-06 (RF-12).
     *
     * Revisar empalmes y guardar son una sola transacción: se bloquean las filas de los dos
     * participantes, así que dos peticiones que comparten alumno o asesor no pueden pasar
     * la revisión al mismo tiempo y guardar las dos.
     *
     * @param array{alumno_id: int, asesor_id: int, materia_id: int, fecha: string, hora_inicio: string, hora_fin: string, tema: string} $datos
     *        Datos de la sesión; alumno_id sale de usuarioActual(), nunca del formulario.
     * @return array<int, string> Mensajes de error; vacío si se guardó.
     * @throws Throwable Si falla la BD; la transacción se deshace antes de relanzarla.
     */
    public static function agendar(array $datos): array
    {
        $errores = self::erroresDeHorario($datos);
        // Un texto que no es UTF-8 (petición modificada) lo rechazaría la BD con una excepción
        $tema = $datos['tema'];
        if ($tema === '' || !mb_check_encoding($tema, 'UTF-8') || mb_strlen($tema) > self::LONGITUD_MAX_TEMA) {
            $errores[] = 'Escribe el tema, con ' . self::LONGITUD_MAX_TEMA . ' caracteres como máximo.';
        }
        if ($errores !== []) {
            return $errores;
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            // ponytail: se bloquean las filas de los dos participantes en lugar de índices de rango o tablas de
            // bloqueo; alcanza para el volumen del proyecto. Tiene que ser la primera lectura de la transacción:
            // REPEATABLE READ toma su foto en la primera lectura normal, y así ya incluye lo que guardó la petición
            // que tenía el bloqueo. ORDER BY: todas toman los bloqueos en el mismo orden y no se traban entre sí
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE id IN (?, ?) ORDER BY id FOR UPDATE');
            $stmt->execute([$datos['alumno_id'], $datos['asesor_id']]);

            $errores = self::empalmes($datos);
            if ($errores === []) {
                $stmt = $pdo->prepare(
                    "INSERT INTO sesiones (alumno_id, asesor_id, materia_id, fecha, hora_inicio, hora_fin, tema, estado)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')"
                );
                $stmt->execute([
                    $datos['alumno_id'],
                    $datos['asesor_id'],
                    $datos['materia_id'],
                    $datos['fecha'],
                    $datos['hora_inicio'],
                    $datos['hora_fin'],
                    $tema,
                ]);
            }
            // Con empalmes no se escribió nada: commit() solo libera los bloqueos
            $pdo->commit();
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
        return $errores;
    }

    /**
     * Revisa la fecha (existe, no es domingo, no pasó), el horario permitido (RN-03) y que la
     * sesión quepa en un bloque del asesor en esa materia y ese día de la semana (RN-05).
     *
     * @param array<string, mixed> $datos Los mismos datos que recibe agendar().
     * @return array<int, string> Mensajes de error; vacío si el horario se puede pedir.
     */
    private static function erroresDeHorario(array $datos): array
    {
        $errorFecha = errorDeFecha($datos['fecha'], $datos['hora_inicio'], new DateTimeImmutable());
        // Sin una fecha válida no hay día de la semana con el cual revisar las horas ni los bloques
        if ($errorFecha !== null) {
            return [$errorFecha];
        }

        $dia = (int) date('N', strtotime($datos['fecha']));
        $errores = validarBloque($dia, $datos['hora_inicio'], $datos['hora_fin']);
        if ($errores !== []) {
            return $errores;
        }

        // Un bloque de otra materia del mismo asesor no sirve, aunque sea el mismo día
        $bloques = array_filter(
            Disponibilidad::delDia($datos['asesor_id'], $dia),
            fn(array $bloque): bool => (int) $bloque['materia_id'] === $datos['materia_id']
        );
        if (!dentroDeBloque($datos['hora_inicio'], $datos['hora_fin'], $bloques)) {
            return ['El horario debe quedar dentro de uno de los bloques del asesor para esta materia.'];
        }
        return [];
    }

    /**
     * Busca las sesiones del alumno o del asesor en esa fecha que reservan el horario y chocan (RN-06).
     * Se llama dentro de la transacción de agendar(), con los dos participantes ya bloqueados.
     *
     * @param array<string, mixed> $datos Los mismos datos que recibe agendar(), ya validados.
     * @return array<int, string> Un mensaje por cada sesión con la que choca.
     */
    private static function empalmes(array $datos): array
    {
        $stmt = db()->prepare(
            'SELECT s.alumno_id, s.hora_inicio, s.hora_fin, s.estado, m.nombre AS materia
               FROM sesiones s
               JOIN materias m ON m.id = s.materia_id
              WHERE s.fecha = ?
                AND (s.alumno_id = ? OR s.asesor_id = ?)
              ORDER BY s.hora_inicio'
        );
        $stmt->execute([$datos['fecha'], $datos['alumno_id'], $datos['asesor_id']]);

        $mensajes = [];
        foreach ($stmt->fetchAll() as $otra) {
            // Una sesión cancelada o completada ya no ocupa el horario
            if (
                !in_array($otra['estado'], self::ESTADOS_QUE_RESERVAN, true)
                || !seEmpalman($datos['hora_inicio'], $datos['hora_fin'], $otra['hora_inicio'], $otra['hora_fin'])
            ) {
                continue;
            }
            $horario = normalizarHora($otra['hora_inicio']) . ' a ' . normalizarHora($otra['hora_fin']);
            // De una sesión propia se dice la materia; de la del asesor con otro alumno, solo la hora
            $mensajes[] = (int) $otra['alumno_id'] === $datos['alumno_id']
                ? 'Ya tienes ' . $otra['materia'] . ' de ' . $horario . '.'
                : 'El asesor ya tiene una sesión de ' . $horario . '.';
        }
        return $mensajes;
    }
}
