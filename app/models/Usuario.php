<?php
/**
 * Consultas de usuarios para autenticación, autorización y registro.
 */
declare(strict_types=1);

final class Usuario
{
    public const LONGITUD_MINIMA_CONTRASENA = 8;
    public const LONGITUD_MAXIMA_NOMBRE = 100;
    public const LONGITUD_MAXIMA_APELLIDOS = 100;
    public const LONGITUD_MAXIMA_CORREO = 150;
    public const LONGITUD_MATRICULA = 8;
    public const SEMESTRE_MAXIMO = 9;

    /**
     * Cuenta caracteres, no bytes: "Ñúñez" mide 5 aunque ocupe más bytes en UTF-8.
     *
     * @param string $texto Texto a medir.
     * @return int Número de caracteres.
     */
    public static function longitudCaracteres(string $texto): int
    {
        return mb_strlen($texto, 'UTF-8');
    }

    /**
     * Comprueba que el correo pertenece exactamente al dominio institucional configurado.
     * Se compara todo lo que va después de la última @, así que un subdominio
     * como "x.alumnos.uady.mx" no se acepta.
     *
     * @param string $correo Correo a revisar.
     * @return bool true si el dominio es igual a DOMINIO_CORREO.
     */
    public static function tieneDominioInstitucional(string $correo): bool
    {
        $dominioPermitido = strtolower(ltrim((string) DOMINIO_CORREO, '@'));
        $posicionArroba = strrpos($correo, '@');

        if ($dominioPermitido === '' || $posicionArroba === false) {
            return false;
        }

        return strtolower(substr($correo, $posicionArroba + 1)) === $dominioPermitido;
    }

    /**
     * Devuelve errores de los campos básicos compartidos por todas las cuentas.
     * Se reutiliza en el controlador para mostrar errores por campo y en crear()
     * para que la validación del modelo nunca dependa únicamente del formulario.
     *
     * @param array<string, mixed> $datos Datos recibidos del formulario.
     * @return array<string, string> Errores indexados por nombre de campo.
     */
    public static function erroresDatosBasicos(array $datos): array
    {
        $errores = [];
        $nombre = isset($datos['nombre']) && is_string($datos['nombre']) ? trim($datos['nombre']) : '';
        $apellidos = isset($datos['apellidos']) && is_string($datos['apellidos']) ? trim($datos['apellidos']) : '';
        $correo = isset($datos['correo']) && is_string($datos['correo'])
            ? strtolower(trim($datos['correo']))
            : '';
        $contrasena = isset($datos['contrasena']) && is_string($datos['contrasena'])
            ? $datos['contrasena']
            : '';

        if ($nombre === '') {
            $errores['nombre'] = 'El nombre es obligatorio.';
        } elseif (self::longitudCaracteres($nombre) > self::LONGITUD_MAXIMA_NOMBRE) {
            $errores['nombre'] = 'El nombre no puede superar ' . self::LONGITUD_MAXIMA_NOMBRE . ' caracteres.';
        }

        if ($apellidos === '') {
            $errores['apellidos'] = 'Los apellidos son obligatorios.';
        } elseif (self::longitudCaracteres($apellidos) > self::LONGITUD_MAXIMA_APELLIDOS) {
            $errores['apellidos'] = 'Los apellidos no pueden superar ' . self::LONGITUD_MAXIMA_APELLIDOS . ' caracteres.';
        }

        if ($correo === '') {
            $errores['correo'] = 'El correo institucional es obligatorio.';
        } elseif (self::longitudCaracteres($correo) > self::LONGITUD_MAXIMA_CORREO) {
            $errores['correo'] = 'El correo no puede superar ' . self::LONGITUD_MAXIMA_CORREO . ' caracteres.';
        } elseif (filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
            $errores['correo'] = 'Escribe una dirección de correo válida.';
        } elseif (!self::tieneDominioInstitucional($correo)) {
            $dominio = strtolower(ltrim((string) DOMINIO_CORREO, '@'));
            $errores['correo'] = 'Usa tu correo institucional @' . $dominio . '.';
        }

        if ($contrasena === '') {
            $errores['contrasena'] = 'La contraseña es obligatoria.';
        } elseif (self::longitudCaracteres($contrasena) < self::LONGITUD_MINIMA_CONTRASENA) {
            $errores['contrasena'] = 'La contraseña debe tener al menos '
                . self::LONGITUD_MINIMA_CONTRASENA . ' caracteres.';
        }

        return $errores;
    }

    /**
     * Genera el hash con el que se guarda una contraseña; nunca se guarda en texto plano.
     *
     * @param string $contrasena Contraseña escrita por el usuario.
     * @return string Hash de password_hash().
     * @throws RuntimeException Si no se pudo generar el hash.
     */
    private static function hashContrasena(string $contrasena): string
    {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        if (!is_string($hash) || $hash === '') {
            throw new RuntimeException('No fue posible proteger la contraseña.');
        }

        return $hash;
    }

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
        $erroresBasicos = self::erroresDatosBasicos($datos);
        if ($erroresBasicos !== []) {
            throw new InvalidArgumentException((string) reset($erroresBasicos));
        }

        $nombre = trim((string) $datos['nombre']);
        $apellidos = trim((string) $datos['apellidos']);
        $matricula = trim(is_string($datos['matricula'] ?? null) ? $datos['matricula'] : '');
        $correo = strtolower(trim((string) $datos['correo']));
        $licenciaturaId = filter_var(
            $datos['licenciatura_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $semestre = filter_var(
            $datos['semestre'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => self::SEMESTRE_MAXIMO]]
        );
        $rol = $datos['rol'] ?? null;
        $contrasena = (string) $datos['contrasena'];

        if ($matricula === '') {
            throw new InvalidArgumentException('La matrícula es obligatoria.');
        }
        if (!is_int($licenciaturaId) || !is_int($semestre)) {
            throw new InvalidArgumentException('La licenciatura o el semestre no son válidos.');
        }
        if (!is_string($rol) || !in_array($rol, ['alumno', 'asesor'], true)) {
            throw new InvalidArgumentException('El rol de registro no es válido.');
        }
        $patronMatricula = '/^[0-9]{' . self::LONGITUD_MATRICULA . '}$/D';
        if (preg_match($patronMatricula, $matricula) !== 1) {
            throw new InvalidArgumentException(
                'La matrícula debe contener exactamente ' . self::LONGITUD_MATRICULA . ' dígitos.'
            );
        }
        if (self::existeCorreoOMatricula($correo, $matricula)) {
            throw new DomainException('El correo o la matrícula ya están registrados.');
        }

        $hash = self::hashContrasena($contrasena);
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
     *
     * @param string $correo Correo a buscar.
     * @param string $matricula Matrícula a buscar.
     * @return bool true si alguno de los dos ya existe.
     */
    public static function existeCorreoOMatricula(string $correo, string $matricula): bool
    {
        return self::existeCorreo($correo) || self::existeMatricula($matricula);
    }

    /**
     * Comprueba si el correo ya está registrado. Va separada de la matrícula
     * para mostrar el error junto al campo correcto.
     *
     * @param string $correo Correo a buscar; se compara en minúsculas y sin espacios.
     * @return bool true si ya existe una cuenta con ese correo.
     */
    public static function existeCorreo(string $correo): bool
    {
        $stmt = db()->prepare(
            'SELECT 1 FROM usuarios WHERE correo = :correo LIMIT 1'
        );
        $stmt->execute(['correo' => strtolower(trim($correo))]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Comprueba si la matrícula ya está registrada. Va separada del correo
     * para mostrar el error junto al campo correcto.
     *
     * @param string $matricula Matrícula a buscar, sin espacios.
     * @return bool true si ya existe una cuenta con esa matrícula.
     */
    public static function existeMatricula(string $matricula): bool
    {
        $stmt = db()->prepare(
            'SELECT 1 FROM usuarios WHERE matricula = :matricula LIMIT 1'
        );
        $stmt->execute(['matricula' => trim($matricula)]);
        return $stmt->fetchColumn() !== false;
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

    // ===== ISS-23 · Administración de usuarios (P2) =====

    /**
     * Devuelve las cuentas, opcionalmente filtradas por rol, sin incluir password_hash.
     * Las cuentas de administrador también aparecen, pero son de solo lectura en el panel.
     *
     * @param string|null $rol Rol por el que se filtra: alumno, asesor o admin; null muestra todos.
     * @return array<int, array<string, mixed>> Cuentas con los datos necesarios para administración.
     */
    public static function todos(?string $rol): array
    {
        if ($rol !== null && !in_array($rol, self::ROLES, true)) {
            return [];
        }

        $sql = 'SELECT u.id, u.nombre, u.apellidos, u.correo, u.rol,
                       u.matricula, u.licenciatura_id, l.nombre AS licenciatura,
                       u.semestre, u.activo
                  FROM usuarios u
                  LEFT JOIN licenciaturas l ON l.id = u.licenciatura_id';
        if ($rol !== null) {
            $sql .= ' WHERE u.rol = :rol';
        }
        $sql .= " ORDER BY CASE u.rol
                                WHEN 'alumno' THEN 1
                                WHEN 'asesor' THEN 2
                                ELSE 3
                           END,
                           u.apellidos, u.nombre";

        $stmt = db()->prepare($sql);
        $stmt->execute($rol === null ? [] : ['rol' => $rol]);

        return $stmt->fetchAll();
    }

    /**
     * Valida los campos editables de una cuenta. No recibe ni modifica rol, correo o matrícula.
     *
     * @param array<string, mixed> $datos Nombre, apellidos, licenciatura_id y semestre.
     * @return array<string, string> Errores indexados por campo.
     */
    public static function erroresDatosAdministrables(array $datos): array
    {
        $errores = [];
        $nombre = is_string($datos['nombre'] ?? null) ? trim($datos['nombre']) : '';
        $apellidos = is_string($datos['apellidos'] ?? null) ? trim($datos['apellidos']) : '';

        if ($nombre === '' || !mb_check_encoding($nombre, 'UTF-8')) {
            $errores['nombre'] = 'El nombre es obligatorio.';
        } elseif (self::longitudCaracteres($nombre) > self::LONGITUD_MAXIMA_NOMBRE) {
            $errores['nombre'] = 'El nombre no puede superar ' . self::LONGITUD_MAXIMA_NOMBRE . ' caracteres.';
        }

        if ($apellidos === '' || !mb_check_encoding($apellidos, 'UTF-8')) {
            $errores['apellidos'] = 'Los apellidos son obligatorios.';
        } elseif (self::longitudCaracteres($apellidos) > self::LONGITUD_MAXIMA_APELLIDOS) {
            $errores['apellidos'] = 'Los apellidos no pueden superar ' . self::LONGITUD_MAXIMA_APELLIDOS . ' caracteres.';
        }

        $licenciaturaId = filter_var(
            $datos['licenciatura_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        if (!is_int($licenciaturaId)) {
            $errores['licenciatura_id'] = 'Selecciona una licenciatura válida.';
        } else {
            $stmt = db()->prepare('SELECT 1 FROM licenciaturas WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $licenciaturaId]);
            if ($stmt->fetchColumn() === false) {
                $errores['licenciatura_id'] = 'La licenciatura seleccionada no existe.';
            }
        }

        $semestre = filter_var(
            $datos['semestre'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => self::SEMESTRE_MAXIMO]]
        );
        if (!is_int($semestre)) {
            $errores['semestre'] = 'Selecciona un semestre del 1 al ' . self::SEMESTRE_MAXIMO . '.';
        }

        return $errores;
    }

    /**
     * Actualiza únicamente los datos permitidos de un alumno o asesor.
     * El rol, correo y matrícula no se reciben y la consulta excluye administradores.
     *
     * @param int $id Identificador de la cuenta.
     * @param array<string, mixed> $datos Nombre, apellidos, licenciatura_id y semestre.
     * @throws InvalidArgumentException Si los datos no cumplen los límites del registro.
     */
    public static function actualizarDatos(int $id, array $datos): void
    {
        if ($id < 1) {
            throw new InvalidArgumentException('La cuenta indicada no es válida.');
        }

        $errores = self::erroresDatosAdministrables($datos);
        if ($errores !== []) {
            throw new InvalidArgumentException((string) reset($errores));
        }

        $stmt = db()->prepare(
            "UPDATE usuarios
                SET nombre = :nombre,
                    apellidos = :apellidos,
                    licenciatura_id = :licenciatura_id,
                    semestre = :semestre
              WHERE id = :id
                AND rol IN ('alumno', 'asesor')"
        );
        $stmt->execute([
            'nombre' => trim((string) $datos['nombre']),
            'apellidos' => trim((string) $datos['apellidos']),
            'licenciatura_id' => (int) $datos['licenciatura_id'],
            'semestre' => (int) $datos['semestre'],
            'id' => $id,
        ]);
    }

    /**
     * Activa o desactiva una cuenta de alumno o asesor sin tocar sus sesiones.
     * La condición del UPDATE impide modificar administradores incluso si se llama directamente.
     *
     * @param int $id Identificador de la cuenta.
     * @param bool $activo true para activar; false para desactivar.
     */
    public static function cambiarActivo(int $id, bool $activo): void
    {
        if ($id < 1) {
            return;
        }

        $stmt = db()->prepare(
            "UPDATE usuarios
                SET activo = :activo
              WHERE id = :id
                AND rol IN ('alumno', 'asesor')"
        );
        $stmt->execute([
            'activo' => $activo ? 1 : 0,
            'id' => $id,
        ]);
    }
}
