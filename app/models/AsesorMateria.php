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

    /**
     * Asigna una materia a un asesor.
     * 
     * Quien la llama debe validar antes que la materia exista, esté activa y que el
     * asesor no la tenga ya (con imparte()).
     * 
     * @param int $asesorId Id del asesor.
     * @param int $materiaId Id de la materia.
     * @return void
     */
    public static function agregar(int $asesorId, int $materiaId): void
    {
        $stmt = db()->prepare(
            'INSERT INTO asesor_materia (asesor_id, materia_id)
             VALUES (?, ?)'
        );
        $stmt->execute([$asesorId, $materiaId]);
    }

    /**
     * Quita una materia a un asesor. Sus bloques de disponibilidad se borran solos 
     * por el ON DELETE CASCADE
     * 
     * @param int $asesorId Id del asesor.
     * @param int $materiaId Id de la materia.
     * @return bool true si se borró, false si el asesor no tenía esa materia.
     */
    public static function quitar(int $asesorId, int $materiaId): bool
    {
        $stmt = db()->prepare(
            'DELETE FROM asesor_materia
              WHERE asesor_id = ? AND materia_id = ?'
        );
        $stmt->execute([$asesorId, $materiaId]);
        return $stmt->rowCount() > 0;
    }
}