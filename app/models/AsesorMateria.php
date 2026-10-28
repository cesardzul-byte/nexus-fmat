<?php

/**
 * Modelo de la tabla `asesor_materia`: qué materias imparte cada asesor.
 */

declare(strict_types=1);

class AsesorMateria
{
    /**
     * Devuelve las materias que imparte un asesor, ordenadas por nombre. 
     * 
     * @param int $asesorId Id del asesor.
     * @return array<int, array<string, mixed>> Materias con su id, clave y nombre.
     */
    public static function materiasDe(int $asesorId): array
    {
        $stmt = db()->prepare(
            'SELECT m.id, m.clave, m.nombre
               FROM asesor_materia am
               JOIN materias m ON m.id=am.materia_id
              WHERE am.asesor_id = ?
              ORDER BY m.nombre'
        );
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll();
    }

    /**
     * Indica si un asesor ya imparte una materia.
     * 
     * @param int $asesorId Id del asesor.
     * @param int $materiaId Id de la materia.
     * @return bool true si el asesor ya imparte la materia, false si no.
     */
    public static function imparte(int $asesorId, int $materiaId): bool
    {
        $stmt = db()->prepare(
            'SELECT 1
               FROM asesor_materia
              WHERE asesor_id = ? AND materia_id = ?'
        );
        $stmt->execute([$asesorId, $materiaId]);
        return (bool) $stmt->fetchColumn();
    }
}