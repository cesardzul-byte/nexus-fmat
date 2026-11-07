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
}
