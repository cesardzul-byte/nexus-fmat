<?php
/**
 * Consultas de usuarios para autenticación, autorización y registro.
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

    /**
     * Crea un usuario nuevo y devuelve su identificador.
     * La contraseña recibida se transforma a hash antes de llegar a la base de datos.
     * Solo se permiten altas públicas con rol alumno o asesor; nunca admin.
     *
     * @param array<string, mixed> $datos Datos previamente validados por el controlador.
     * @return int ID del usuario insertado.
     */
    public static function crear(array $datos): int
    {
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $apellidos = trim((string) ($datos['apellidos'] ?? ''));
        $matricula = trim((string) ($datos['matricula'] ?? ''));
        $correo = strtolower(trim((string) ($datos['correo'] ?? '')));
        $licenciaturaId = filter_var(
            $datos['licenciatura_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $semestre = filter_var(
            $datos['semestre'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 9]]
        );
        $rol = $datos['rol'] ?? null;
        $contrasena = $datos['contrasena'] ?? null;

        if ($nombre === '' || $apellidos === '' || $correo === '' || $matricula === '') {
            throw new InvalidArgumentException('Faltan datos obligatorios para crear el usuario.');
        }
        if (strlen($nombre) > 100 || strlen($apellidos) > 100 || strlen($correo) > 150) {
            throw new InvalidArgumentException('Uno de los campos supera la longitud permitida.');
        }
        if (filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('El correo no tiene un formato válido.');
        }
        $dominioPermitido = strtolower(ltrim((string) DOMINIO_CORREO, '@'));
        $partesCorreo = explode('@', $correo);
        if ($dominioPermitido === '' || count($partesCorreo) !== 2
            || strtolower($partesCorreo[1]) !== $dominioPermitido) {
            throw new InvalidArgumentException('El correo debe pertenecer al dominio institucional.');
        }
        if (!is_int($licenciaturaId) || !is_int($semestre)) {
            throw new InvalidArgumentException('La licenciatura o el semestre no son válidos.');
        }
        if (!is_string($rol) || !in_array($rol, ['alumno', 'asesor'], true)) {
            throw new InvalidArgumentException('El rol de registro no es válido.');
        }
        if (!is_string($contrasena) || $contrasena === '') {
            throw new InvalidArgumentException('La contraseña es obligatoria.');
        }
        $longitudContrasena = function_exists('mb_strlen')
            ? mb_strlen($contrasena, 'UTF-8')
            : (preg_match_all('/./us', $contrasena, $coincidencias) ?: strlen($contrasena));
        if ($longitudContrasena < 8) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }
        if (!preg_match('/^[0-9]{8}$/D', $matricula)) {
            throw new InvalidArgumentException('La matrícula debe contener exactamente ocho dígitos.');
        }
        if (self::existeCorreoOMatricula($correo, $matricula)) {
            throw new DomainException('El correo o la matrícula ya están registrados.');
        }

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        if (!is_string($hash) || $hash === '') {
            throw new RuntimeException('No fue posible proteger la contraseña.');
        }

        $stmt = db()->prepare(
            'INSERT INTO usuarios
                (nombre, apellidos, correo, password_hash, rol, matricula,
                 licenciatura_id, semestre, activo)
             VALUES
                (:nombre, :apellidos, :correo, :password_hash, :rol, :matricula,
                 :licenciatura_id, :semestre, 1)'
        );
        $stmt->execute([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'correo' => $correo,
            'password_hash' => $hash,
            'rol' => $rol,
            'matricula' => $matricula,
            'licenciatura_id' => $licenciaturaId,
            'semestre' => $semestre,
        ]);

        $id = (int) db()->lastInsertId();
        if ($id < 1) {
            throw new RuntimeException('No fue posible recuperar el identificador del usuario creado.');
        }

        return $id;
    }

    /**
     * Indica si el correo o la matrícula ya están registrados.
     */
    public static function existeCorreoOMatricula(string $correo, string $matricula): bool
    {
        return self::existeCorreo($correo) || self::existeMatricula($matricula);
    }

    /** Comprueba si existe el correo, para asociar el error al campo correcto. */
    public static function existeCorreo(string $correo): bool
    {
        $stmt = db()->prepare(
            'SELECT 1 FROM usuarios WHERE correo = :correo LIMIT 1'
        );
        $stmt->execute(['correo' => strtolower(trim($correo))]);
        return $stmt->fetchColumn() !== false;
    }

    /** Comprueba si existe la matrícula, para asociar el error al campo correcto. */
    public static function existeMatricula(string $matricula): bool
    {
        $stmt = db()->prepare(
            'SELECT 1 FROM usuarios WHERE matricula = :matricula LIMIT 1'
        );
        $stmt->execute(['matricula' => trim($matricula)]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Crea una cuenta administradora desde el modo protegido de registro.
     * La operación exige una sesión de administrador activa y asigna los campos
     * matrícula, licenciatura y semestre como NULL.
     *
     * @param array<string, mixed> $datos Datos de la nueva cuenta administradora.
     * @return int Identificador de la cuenta creada.
     */
    public static function crearAdministrador(array $datos): int
    {
        $sesion = usuarioActual();
        $idActor = is_array($sesion) ? (int) ($sesion['id'] ?? 0) : 0;
        $actor = $idActor > 0 ? self::buscarPorId($idActor) : null;

        // No basta con confiar en el rol que pueda quedar guardado en la sesión.
        if ($actor === null || (int) ($actor['activo'] ?? 0) !== 1 || ($actor['rol'] ?? null) !== 'admin') {
            throw new RuntimeException('Solo un administrador activo puede crear otra cuenta administradora.');
        }

        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $apellidos = trim((string) ($datos['apellidos'] ?? ''));
        $correo = strtolower(trim((string) ($datos['correo'] ?? '')));
        $contrasena = $datos['contrasena'] ?? null;

        if ($nombre === '' || $apellidos === '' || $correo === '' || !is_string($contrasena) || $contrasena === '') {
            throw new InvalidArgumentException('Todos los campos son obligatorios.');
        }
        if (strlen($nombre) > 100 || strlen($apellidos) > 100 || strlen($correo) > 150) {
            throw new InvalidArgumentException('Uno de los campos supera la longitud permitida.');
        }
        if (filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('El correo no tiene un formato válido.');
        }

        $dominioPermitido = strtolower(ltrim((string) DOMINIO_CORREO, '@'));
        $partesCorreo = explode('@', $correo);
        if ($dominioPermitido === '' || count($partesCorreo) !== 2
            || strtolower($partesCorreo[1]) !== $dominioPermitido) {
            throw new InvalidArgumentException('El correo debe pertenecer al dominio institucional.');
        }

        $longitudContrasena = function_exists('mb_strlen')
            ? mb_strlen($contrasena, 'UTF-8')
            : (preg_match_all('/./us', $contrasena, $coincidencias) ?: strlen($contrasena));
        if ($longitudContrasena < 8) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }
        if (self::existeCorreo($correo)) {
            throw new DomainException('Ya existe una cuenta registrada con este correo.');
        }

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        if (!is_string($hash) || $hash === '') {
            throw new RuntimeException('No fue posible proteger la contraseña.');
        }

        $stmt = db()->prepare(
            'INSERT INTO usuarios
                (nombre, apellidos, correo, password_hash, rol, matricula,
                 licenciatura_id, semestre, activo)
             VALUES
                (:nombre, :apellidos, :correo, :password_hash, \'admin\', NULL, NULL, NULL, 1)'
        );
        $stmt->execute([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'correo' => $correo,
            'password_hash' => $hash,
        ]);

        $id = (int) db()->lastInsertId();
        if ($id < 1) {
            throw new RuntimeException('No fue posible recuperar el identificador del administrador creado.');
        }

        return $id;
    }

    // ===== ISS-10 · Panel por rol (P3) =====

    /** Roles que existen en la tabla usuarios, en el orden en que se muestran. */
    private const ROLES = ['alumno', 'asesor', 'admin'];

    /**
     * Cuenta los usuarios activos de cada rol con una sola consulta.
     * Los roles sin usuarios aparecen con 0, para que el panel siempre muestre los tres.
     *
     * @return array{alumno: int, asesor: int, admin: int} Total de usuarios activos por rol.
     */
    public static function conteoPorRol(): array
    {
        $stmt = db()->query(
            'SELECT rol, COUNT(*) AS total
               FROM usuarios
              WHERE activo = 1
              GROUP BY rol'
        );

        $conteo = array_fill_keys(self::ROLES, 0);
        foreach ($stmt->fetchAll() as $fila) {
            $conteo[$fila['rol']] = (int) $fila['total'];
        }

        return $conteo;
    }
}
