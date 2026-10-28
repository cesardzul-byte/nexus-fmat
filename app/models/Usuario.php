<?php
/**
 * Consultas de usuarios para autenticación y autorización.
 */
declare(strict_types=1);

final class Usuario
{
    /**
     * Busca una cuenta por su correo institucional.
     * Incluye el hash únicamente para que el controlador pueda verificar la contraseña.
     *
     * @param string $correo Correo que se intentó utilizar para iniciar sesión.
     * @return array<string, mixed>|null Datos de la cuenta o null si no existe.
     */
    public static function buscarPorCorreo(string $correo): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, nombre, apellidos, correo, password_hash, rol, activo,
                    matricula, licenciatura_id, semestre, creado_en
               FROM usuarios
              WHERE correo = :correo
              LIMIT 1'
        );
        $stmt->execute(['correo' => $correo]);

        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    /**
     * Busca una cuenta por identificador sin devolver el hash de la contraseña.
     * Se utiliza también para comprobar que una cuenta siga activa durante la sesión.
     *
     * @param int $id Identificador de usuario.
     * @return array<string, mixed>|null Datos públicos de la cuenta o null si no existe.
     */
    public static function buscarPorId(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $stmt = db()->prepare(
            'SELECT id, nombre, apellidos, correo, rol, activo,
                    matricula, licenciatura_id, semestre, creado_en
               FROM usuarios
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }
}
