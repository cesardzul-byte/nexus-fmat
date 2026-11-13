<?php
/**
 * Modelo de la tabla `resenas`: la calificación que un alumno da a una sesión completada (RF-16).
 *
 * Una sesión tiene a lo más una reseña (RN-08): lo garantiza la UNIQUE de `sesion_id` en schema.sql.
 */
declare(strict_types=1);

class Resena
{
    // Igual que el CHECK de la tabla `resenas` en schema.sql
    public const CALIFICACION_MIN = 1;
    public const CALIFICACION_MAX = 5;

    // Límite de la ficha del ISS-24; la columna es TEXT, así que el límite lo pone PHP
    public const LONGITUD_MAX_COMENTARIO = 500;

    // Código de error de MariaDB/MySQL cuando un INSERT repite una llave UNIQUE
    private const ERROR_DUPLICADO = 1062;

    /**
     * Valida los datos de una reseña.
     * Es una regla pura: no usa la BD.
     *
     * @param int $calificacion La calificación de la reseña.
     * @param string $comentario El comentario de la reseña.
     * @return array<int, string> Un array con los errores encontrados, vacío si no hay errores.
     */
    public static function validar(int $calificacion, string $comentario): array
    {
        $errores = [];

        if ($calificacion < self::CALIFICACION_MIN || $calificacion > self::CALIFICACION_MAX) {
            $errores[] = 'La calificación debe estar entre ' . self::CALIFICACION_MIN . ' y ' . self::CALIFICACION_MAX . '.';
        }
        // Un texto que no es UTF-8 (petición modificada) lo rechazaría la BD con una excepción
        if (!mb_check_encoding($comentario, 'UTF-8') || mb_strlen($comentario) > self::LONGITUD_MAX_COMENTARIO) {
            $errores[] = 'El comentario no puede tener más de ' . self::LONGITUD_MAX_COMENTARIO . ' caracteres.';
        }
        return $errores;
    }

    /**
     * Crea una nueva reseña en la base de datos.
     *
     * @param int $sesionId El ID de la sesión a la que pertenece la reseña.
     * @param int $calificacion La calificación de la reseña.
     * @param string $comentario El comentario de la reseña; si llega vacío se guarda NULL.
     * @return bool True si se creó la reseña, false si ya existía una reseña para esa sesión.
     * @throws PDOException Si falla la BD por otro motivo (no por reseña repetida).
     */
    public static function crear(int $sesionId, int $calificacion, string $comentario): bool
    {
        $stmt = db()->prepare(
            'INSERT INTO resenas (sesion_id, calificacion, comentario)
             VALUES (?, ?, ?)'
        );
        try {
            $stmt->execute([$sesionId, $calificacion, $comentario === '' ? null : $comentario]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) === self::ERROR_DUPLICADO) {
                return false; // Ya existe una reseña para esta sesión
            }
            throw $e; // Re-lanzar la excepción si es otro error
        }
        return true;
    }

    /**
     * Calcula el promedio y el número de reseñas de cada asesor en una sola consulta.
     * Los asesores sin reseñas no aparecen; la vista muestra "Sin reseñas" para ellos.
     *
     * @return array<int, array{promedio: float, total: int}> Indexado por asesor_id.
     */
    public static function promediosPorAsesor(): array
    {
        $stmt = db()->query(
            'SELECT s.asesor_id, AVG(r.calificacion) AS promedio, COUNT(*) AS total
               FROM resenas r
               JOIN sesiones s ON r.sesion_id = s.id
              GROUP BY s.asesor_id'
        );

        $promedios = [];
        foreach ($stmt->fetchAll() as $fila) {
            $promedios[(int) $fila['asesor_id']] = [
                'promedio' => (float) $fila['promedio'],
                'total' => (int) $fila['total'],
            ];
        }
        return $promedios;
    }

    
    /**
     * Indica si una sesión ya tiene reseña, para avisar antes de mostrar el formulario.
     *
     * @param int $sesionId Id de la sesión.
     * @return bool true si ya existe una reseña para esa sesión.
     */
    public static function existeDeSesion(int $sesionId): bool
    {
        $stmt = db()->prepare('SELECT 1 FROM resenas WHERE sesion_id = ?');
        $stmt->execute([$sesionId]);
        return $stmt->fetchColumn() !== false;
    }
}