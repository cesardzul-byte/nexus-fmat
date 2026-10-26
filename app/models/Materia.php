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
       WHERE activa=1
     ORDER BY nombre'
        );

        return $stmt->fetchAll();
    }

    /**
     * Busca una materia por su id, esté activa o no.
     * 
     * @param int $id Id de la materia.
     * @return array<string, mixed>|null Materia con su id, clave,activa y nombre o null si no existe.
     */
    public static function buscarPorId(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, clave, nombre,activa
       FROM materias
       WHERE id=?'
        );

        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }
}
