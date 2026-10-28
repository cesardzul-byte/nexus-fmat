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
        $stmt = db() -> prepare(
            'SELECT m.id, m.clave, m.nombre
               FROM asesor_materia am
               JOIN materias m ON m.id=am.materia_id
              WHERE am.asesor_id = ?
              ORDER BY m.nombre'
        );
        $stmt -> execute([$asesorId]);
        return $stmt -> fetchAll();
    }
}