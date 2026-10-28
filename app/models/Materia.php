<?php

/**
 * Modelo de la tabla `materias`: catálogo de materias de la FMAT.
 */

declare(strict_types=1);

class Materia
{
    /**
     * Devuelve todas las materias activas ordenadas por nombre.
     *
     * @return array<int, array<string, mixed>> Materias con su id, clave y nombre.
     */
    public static function activas(): array
    {
        $stmt = db()->query(
            'SELECT id, clave, nombre
               FROM materias
              WHERE activa = 1
              ORDER BY nombre'
        );

        return $stmt->fetchAll();
    }

    /**
     * Busca una materia por su id, esté activa o no.
     *
     * @param int $id Id de la materia.
     * @return array<string, mixed>|null Materia con su id, clave, nombre y activa, o null si no existe.
     */
    public static function buscarPorId(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, clave, nombre, activa
               FROM materias
              WHERE id = ?'
        );

        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Devuelve la lista de materias ACTIVAS con la cantidad de asesores activos que tienen asignados, ordenadas por nombre.
     *
     * @return array<int, array<string, mixed>> Materias con su id, clave, nombre y cantidad de asesores activos.
     */
    public static function conNumeroAsesores(): array
    {
        // Se agrupa por todas las columnas del SELECT porque MariaDB con ONLY_FULL_GROUP_BY no acepta solo m.id
        // u.activo va en el ON (no en el WHERE) para conservar las materias sin asesores; por eso se cuenta u.id
        $stmt = db()->query(
            'SELECT m.id, m.clave, m.nombre, COUNT(u.id) AS cantidad_asesores
               FROM materias m
               LEFT JOIN asesor_materia am ON am.materia_id = m.id
               LEFT JOIN usuarios u ON u.id = am.asesor_id AND u.activo = 1
              WHERE m.activa = 1
              GROUP BY m.id, m.clave, m.nombre
              ORDER BY m.nombre'
        );

        return $stmt->fetchAll();
    }

    // ===== ISS-09 · Detalle de materia (P3) =====

    /**
     * Devuelve los asesores activos que imparten una materia, cada uno con su
     * licenciatura y sus bloques de disponibilidad ordenados por día y hora.
     *
     * Se usa LEFT JOIN con disponibilidad para que también aparezca el asesor
     * que todavía no publica horarios (con su lista de bloques vacía).
     *
     * @param int $materiaId Id de la materia.
     * @return array<int, array{id: int, nombre: string, apellidos: string, licenciatura: ?string, bloques: array<int, array{dia_semana: int, hora_inicio: string, hora_fin: string, lugar: string}>}>
     *         Asesores ordenados por apellidos y nombre.
     */
    public static function asesoresConDisponibilidad(int $materiaId): array
    {
        $stmt = db()->prepare(
            'SELECT u.id, u.nombre, u.apellidos, l.nombre AS licenciatura,
                    d.dia_semana, d.hora_inicio, d.hora_fin, d.lugar
               FROM asesor_materia am
               JOIN usuarios u ON u.id = am.asesor_id
               LEFT JOIN licenciaturas l ON l.id = u.licenciatura_id
               LEFT JOIN disponibilidad d ON d.asesor_id = am.asesor_id
                                         AND d.materia_id = am.materia_id
              WHERE am.materia_id = ?
                AND u.activo = 1
              ORDER BY u.apellidos, u.nombre, d.dia_semana, d.hora_inicio'
        );
        $stmt->execute([$materiaId]);

        return self::agruparBloquesPorAsesor($stmt->fetchAll());
    }

    /**
     * Convierte las filas de asesoresConDisponibilidad() (una fila por bloque)
     * en una lista de asesores, cada uno con sus bloques adentro.
     *
     * @param array<int, array<string, mixed>> $filas Filas tal como las regresa la consulta.
     * @return array<int, array<string, mixed>> Asesores en el mismo orden de las filas.
     */
    private static function agruparBloquesPorAsesor(array $filas): array
    {
        $asesores = [];
        foreach ($filas as $fila) {
            $asesorId = (int) $fila['id'];
            $asesores[$asesorId] ??= [
                'id' => $asesorId,
                'nombre' => $fila['nombre'],
                'apellidos' => $fila['apellidos'],
                'licenciatura' => $fila['licenciatura'],
                'bloques' => [],
            ];

            // Un asesor sin horarios publicados llega con las columnas de disponibilidad en null
            if ($fila['dia_semana'] === null) {
                continue;
            }
            $asesores[$asesorId]['bloques'][] = [
                'dia_semana' => (int) $fila['dia_semana'],
                'hora_inicio' => $fila['hora_inicio'],
                'hora_fin' => $fila['hora_fin'],
                'lugar' => $fila['lugar'],
            ];
        }

        return array_values($asesores);
    }
}
