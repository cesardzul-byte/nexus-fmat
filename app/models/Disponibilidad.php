<?php
/**
 * Modelo de la tabla `disponibilidad`: bloques semanales que publica cada asesor.
 *
 * Toda consulta de un bloque concreto filtra también por asesor_id, que sale de la
 * sesión: así un asesor no ve, edita ni borra bloques de otro aunque cambie el id.
 */
declare(strict_types=1);

class Disponibilidad
{
    /**
     * Devuelve los bloques de un asesor ordenados por día y hora.
     *
     * @param int $asesorId Id del asesor (sale de usuarioActual(), nunca del formulario).
     * @return array<int, array<string, mixed>> Bloques con el nombre de su materia en 'materia'.
     */
    public static function deAsesor(int $asesorId): array
    {
        $stmt = db()->prepare(
            'SELECT d.*, m.nombre AS materia
               FROM disponibilidad d
               JOIN materias m ON m.id = d.materia_id
              WHERE d.asesor_id = ?
              ORDER BY d.dia_semana, d.hora_inicio'
        );
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll();
    }

    /**
     * Devuelve los bloques de un asesor en un día, de cualquier materia, para revisar empalmes.
     *
     * Filtra los de deAsesor() en PHP: son pocas filas y así la consulta vive en un solo lugar.
     *
     * @param int $asesorId Id del asesor.
     * @param int $dia Día de la semana (1 = lunes … 6 = sábado).
     * @return array<int, array<string, mixed>> Bloques del día con el nombre de su materia, ordenados por hora.
     */
    public static function delDia(int $asesorId, int $dia): array
    {
        return array_values(array_filter(
            self::deAsesor($asesorId),
            fn(array $bloque): bool => (int) $bloque['dia_semana'] === $dia
        ));
    }

    /**
     * Busca un bloque solo si pertenece al asesor.
     *
     * @param int $id Id del bloque.
     * @param int $asesorId Id del asesor en sesión.
     * @return array<string, mixed>|null El bloque, o null si no existe o es de otro asesor.
     */
    public static function buscar(int $id, int $asesorId): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, materia_id, dia_semana, hora_inicio, hora_fin, lugar
               FROM disponibilidad
              WHERE id = ? AND asesor_id = ?'
        );
        $stmt->execute([$id, $asesorId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Publica un bloque. Quien la llama ya validó el horario, la materia y los empalmes.
     *
     * @param int $asesorId Id del asesor en sesión.
     * @param array{materia_id: int, dia_semana: int, hora_inicio: string, hora_fin: string, lugar: string} $bloque Datos del bloque.
     * @return void
     */
    public static function crear(int $asesorId, array $bloque): void
    {
        $stmt = db()->prepare(
            'INSERT INTO disponibilidad (asesor_id, materia_id, dia_semana, hora_inicio, hora_fin, lugar)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $asesorId,
            $bloque['materia_id'],
            $bloque['dia_semana'],
            $bloque['hora_inicio'],
            $bloque['hora_fin'],
            $bloque['lugar'],
        ]);
    }

    /**
     * Cambia un bloque del asesor. Quien la llama ya comprobó con buscar() que el bloque es suyo
     * y validó el horario, la materia y los empalmes.
     *
     * @param int $id Id del bloque.
     * @param int $asesorId Id del asesor en sesión.
     * @param array{materia_id: int, dia_semana: int, hora_inicio: string, hora_fin: string, lugar: string} $bloque Datos nuevos.
     * @return void
     */
    public static function actualizar(int $id, int $asesorId, array $bloque): void
    {
        $stmt = db()->prepare(
            'UPDATE disponibilidad
                SET materia_id = ?, dia_semana = ?, hora_inicio = ?, hora_fin = ?, lugar = ?
              WHERE id = ? AND asesor_id = ?'
        );
        $stmt->execute([
            $bloque['materia_id'],
            $bloque['dia_semana'],
            $bloque['hora_inicio'],
            $bloque['hora_fin'],
            $bloque['lugar'],
            $id,
            $asesorId,
        ]);
    }

    /**
     * Borra un bloque del asesor.
     *
     * @param int $id Id del bloque.
     * @param int $asesorId Id del asesor en sesión.
     * @return bool true si se borró, false si no existe o es de otro asesor.
     */
    public static function eliminar(int $id, int $asesorId): bool
    {
        $stmt = db()->prepare(
            'DELETE FROM disponibilidad
              WHERE id = ? AND asesor_id = ?'
        );
        $stmt->execute([$id, $asesorId]);
        return $stmt->rowCount() > 0;
    }
}
