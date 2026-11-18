<?php

/**
 * Modelo de la tabla `asesor_materia`: qué materias imparte cada asesor.
 */

declare(strict_types=1);

class AsesorMateria
{
    /**
     * Devuelve las materias activas que imparte un asesor, ordenadas por nombre.
     *
     * Las que el administrador desactivó no salen (ISS-25): así no aparecen en
     * "Mis materias" ni en el formulario de "Mi disponibilidad".
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
                AND m.activa = 1
              ORDER BY m.nombre'
        );
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll();
    }

   /**
     * Indica si un asesor imparte una materia que sigue activa.
     *
     * Una materia desactivada cuenta como no impartida (ISS-25): así disponibilidad.php
     * rechaza un bloque de esa materia aunque se envíe editando el HTML.
     *
     * @param int $asesorId Id del asesor.
     * @param int $materiaId Id de la materia.
     * @return bool true si el asesor imparte la materia y está activa, false si no.
     */
    public static function imparte(int $asesorId, int $materiaId): bool
    {
        $stmt = db()->prepare(
            'SELECT 1
               FROM asesor_materia am
               JOIN materias m ON m.id = am.materia_id
              WHERE am.asesor_id = ? AND am.materia_id = ?
                AND m.activa = 1'
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