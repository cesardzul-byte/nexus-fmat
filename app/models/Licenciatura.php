<?php

/**
 * Modelo de la tabla `licenciaturas`: catálogo de carreras de la FMAT.
 */

declare(strict_types=1);

class Licenciatura
{
    /**
     * Devuelve todas las licenciaturas ordenadas por nombre.
     *
     * @return array<int, array<string, mixed>> Licenciaturas con su id y nombre.
     */
    public static function todas(): array
    {

        $stmt = db()->query(
            'SELECT id, nombre
       FROM licenciaturas
     ORDER BY nombre'
        );

        return $stmt->fetchAll();
    }
}
