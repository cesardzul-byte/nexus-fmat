<?php
/**
 * Registro unificado de cuentas para Nexus FMAT (ISS-08).
 * Una sola ruta y un solo formulario admiten alumnos, asesores y,
 * exclusivamente para administradores autenticados, otras cuentas admin.
 */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metodo, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    abortar(405, 'Método no permitido.');
}

// El modo administrativo es una entrada explícita y protegida del mismo registro.
// El parámetro por sí solo no concede privilegios: requiereRol() vuelve a validar
// en la base de datos que la cuenta autenticada siga activa y sea administradora.
$modoAdminSolicitado = isset($_GET['modo'])
    && is_string($_GET['modo'])
    && $_GET['modo'] === 'admin';
if ($modoAdminSolicitado) {
    requiereRol('admin');
}

// Comprueba contra la base de datos quién tiene la sesión actual.
$sesion = usuarioActual();
$actor = null;
if (is_array($sesion)) {
    $idSesion = (int) ($sesion['id'] ?? 0);
    $actor = $idSesion > 0 ? Usuario::buscarPorId($idSesion) : null;

    if ($actor === null || (int) ($actor['activo'] ?? 0) !== 1) {
        cerrarSesion();
        $actor = null;
    } else {
        // Actualiza los datos de rol y nombre con los valores actuales de la BD.
        $_SESSION['usuario'] = [
            'id' => (int) $actor['id'],
            'nombre' => trim((string) $actor['nombre'] . ' ' . (string) $actor['apellidos']),
            'rol' => (string) $actor['rol'],
        ];
    }
}

$esAdminActual = $actor !== null && ($actor['rol'] ?? null) === 'admin';

// Un alumno/asesor con sesión iniciada no debe crear cuentas desde el registro público.
if ($actor !== null && !$esAdminActual) {
    redirigir('panel.php');
}

$licenciaturas = Licenciatura::todas();
$rolInicial = $esAdminActual
    && ($modoAdminSolicitado || (isset($_GET['rol']) && is_string($_GET['rol']) && $_GET['rol'] === 'admin'))
    ? 'admin'
    : 'alumno';
$valores = [
    'nombre' => '',
    'apellidos' => '',
    'matricula' => '',
    'correo' => '',
    'licenciatura_id' => '',
    'semestre' => '',
    'rol' => $rolInicial,
];
$errores = [];
$errorGeneral = '';

$leerCadena = static function (string $campo): string {
    $valor = $_POST[$campo] ?? '';
    return is_string($valor) ? trim($valor) : '';
};
$leerSinRecortar = static function (string $campo): string {
    $valor = $_POST[$campo] ?? '';
    return is_string($valor) ? $valor : '';
};
$longitudCaracteres = static function (string $valor): int {
    if (function_exists('mb_strlen')) {
        return mb_strlen($valor, 'UTF-8');
    }
    $cantidad = preg_match_all('/./us', $valor, $coincidencias);
    return $cantidad === false ? strlen($valor) : $cantidad;
};
$validarCorreoInstitucional = static function (string $correo): ?string {
    if (filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
        return 'Escribe una dirección de correo válida.';
    }

    $dominioPermitido = strtolower(ltrim((string) DOMINIO_CORREO, '@'));
    $posicionArroba = strrpos($correo, '@');
    $dominioRecibido = $posicionArroba === false
        ? ''
        : strtolower(substr($correo, $posicionArroba + 1));

    if ($dominioPermitido === '' || $dominioRecibido !== $dominioPermitido) {
        return 'Usa tu correo institucional @' . $dominioPermitido . '.';
    }

    return null;
};

if ($metodo === 'POST') {
    verificarCsrf();

    $valores['nombre'] = $leerCadena('nombre');
    $valores['apellidos'] = $leerCadena('apellidos');
    $valores['correo'] = strtolower($leerCadena('correo'));
    $rolRecibido = $leerCadena('rol');
    $valores['rol'] = $rolRecibido;
    $contrasena = $leerSinRecortar('contrasena');
    $confirmacion = $leerSinRecortar('confirmar_contrasena');

    $rolesPermitidos = $esAdminActual
        ? ['alumno', 'asesor', 'admin']
        : ['alumno', 'asesor'];

    if (!in_array($rolRecibido, $rolesPermitidos, true)) {
        // La selección pública no puede elevar privilegios manipulando el HTML.
        $errores['rol'] = 'El tipo de cuenta seleccionado no está permitido.';
    }

    $esAltaAdmin = $esAdminActual && $rolRecibido === 'admin';
    $requiereDatosAcademicos = !$esAltaAdmin;

    foreach ([
        'nombre' => 'El nombre es obligatorio.',
        'apellidos' => 'Los apellidos son obligatorios.',
        'correo' => 'El correo institucional es obligatorio.',
    ] as $campo => $mensaje) {
        if ($valores[$campo] === '') {
            $errores[$campo] = $mensaje;
        }
    }

    foreach (['nombre' => 100, 'apellidos' => 100] as $campo => $maximo) {
        if ($valores[$campo] !== '' && strlen($valores[$campo]) > $maximo) {
            $errores[$campo] = 'Este campo no puede superar ' . $maximo . ' caracteres.';
        }
    }
    if (strlen($valores['correo']) > 150) {
        $errores['correo'] = 'El correo no puede superar 150 caracteres.';
    }
    if ($valores['correo'] !== '' && !isset($errores['correo'])) {
        $errorCorreo = $validarCorreoInstitucional($valores['correo']);
        if ($errorCorreo !== null) {
            $errores['correo'] = $errorCorreo;
        }
    }

    if ($requiereDatosAcademicos) {
        foreach (['matricula', 'licenciatura_id', 'semestre'] as $campo) {
            $valores[$campo] = $leerCadena($campo);
        }

        foreach ([
            'matricula' => 'La matrícula es obligatoria.',
            'licenciatura_id' => 'Selecciona una licenciatura.',
            'semestre' => 'Selecciona tu semestre.',
        ] as $campo => $mensaje) {
            if ($valores[$campo] === '') {
                $errores[$campo] = $mensaje;
            }
        }

        if ($valores['matricula'] !== '' && !preg_match('/^[0-9]{8}$/D', $valores['matricula'])) {
            $errores['matricula'] = 'La matrícula debe contener exactamente ocho dígitos.';
        }

        $idsLicenciaturas = array_map(
            static fn (array $licenciatura): int => (int) ($licenciatura['id'] ?? 0),
            $licenciaturas
        );
        if ($valores['licenciatura_id'] !== ''
            && (!ctype_digit($valores['licenciatura_id'])
                || !in_array((int) $valores['licenciatura_id'], $idsLicenciaturas, true))) {
            $errores['licenciatura_id'] = 'La licenciatura seleccionada no existe.';
        }
        if ($valores['semestre'] !== '' && !preg_match('/^[1-9]$/D', $valores['semestre'])) {
            $errores['semestre'] = 'Selecciona un semestre del 1 al 9.';
        }
    } else {
        // En el alta administrativa los campos académicos no forman parte del registro.
        $valores['matricula'] = '';
        $valores['licenciatura_id'] = '';
        $valores['semestre'] = '';
    }

    if ($contrasena === '') {
        $errores['contrasena'] = 'La contraseña es obligatoria.';
    } elseif ($longitudCaracteres($contrasena) < 8) {
        $errores['contrasena'] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if ($confirmacion === '') {
        $errores['confirmar_contrasena'] = 'Confirma tu contraseña.';
    } elseif ($contrasena !== '' && !hash_equals($contrasena, $confirmacion)) {
        $errores['confirmar_contrasena'] = 'Las contraseñas no coinciden.';
    }

    if ($valores['correo'] !== '' && !isset($errores['correo'])
        && Usuario::existeCorreo($valores['correo'])) {
        $errores['correo'] = 'Ya existe una cuenta registrada con este correo.';
    }
    if ($requiereDatosAcademicos && preg_match('/^[0-9]{8}$/D', $valores['matricula']) === 1
        && !isset($errores['matricula']) && Usuario::existeMatricula($valores['matricula'])) {
        $errores['matricula'] = 'Ya existe una cuenta registrada con esta matrícula.';
    }

    if ($errores === []) {
        try {
            if ($esAltaAdmin) {
                Usuario::crearAdministrador([
                    'nombre' => $valores['nombre'],
                    'apellidos' => $valores['apellidos'],
                    'correo' => $valores['correo'],
                    'contrasena' => $contrasena,
                ]);
                flash('exito', 'La cuenta administradora se creó correctamente.');
                redirigir('registro.php?modo=admin');
            }

            $idUsuario = Usuario::crear([
                'nombre' => $valores['nombre'],
                'apellidos' => $valores['apellidos'],
                'matricula' => $valores['matricula'],
                'correo' => $valores['correo'],
                'licenciatura_id' => (int) $valores['licenciatura_id'],
                'semestre' => (int) $valores['semestre'],
                'rol' => $rolRecibido,
                'contrasena' => $contrasena,
            ]);

            if ($esAdminActual) {
                // El administrador conserva su sesión cuando crea una cuenta de alumno/asesor.
                flash('exito', 'La cuenta se creó correctamente.');
                redirigir('registro.php');
            }

            $usuarioCreado = Usuario::buscarPorId($idUsuario);
            if ($usuarioCreado === null || (int) ($usuarioCreado['activo'] ?? 0) !== 1) {
                throw new RuntimeException('La cuenta se creó, pero no se pudo recuperar para iniciar sesión.');
            }

            iniciarSesion($usuarioCreado);
            flash('exito', '¡Bienvenido a Nexus FMAT! Tu cuenta se creó correctamente.');
            redirigir('panel.php');
        } catch (DomainException $ex) {
            if ($valores['correo'] !== '' && Usuario::existeCorreo($valores['correo'])) {
                $errores['correo'] = 'Ya existe una cuenta registrada con este correo.';
            }
            if ($requiereDatosAcademicos && $valores['matricula'] !== ''
                && Usuario::existeMatricula($valores['matricula'])) {
                $errores['matricula'] = 'Ya existe una cuenta registrada con esta matrícula.';
            }
            if ($errores === []) {
                $errorGeneral = 'El correo o la matrícula ya están registrados.';
            }
        } catch (PDOException $ex) {
            if ((int) ($ex->errorInfo[1] ?? 0) === 1062) {
                if ($valores['correo'] !== '' && Usuario::existeCorreo($valores['correo'])) {
                    $errores['correo'] = 'Ya existe una cuenta registrada con este correo.';
                }
                if ($requiereDatosAcademicos && $valores['matricula'] !== ''
                    && Usuario::existeMatricula($valores['matricula'])) {
                    $errores['matricula'] = 'Ya existe una cuenta registrada con esta matrícula.';
                }
                if ($errores === []) {
                    $errorGeneral = 'Uno de los datos ingresados ya está registrado.';
                }
            } else {
                error_log('Nexus FMAT · error al registrar cuenta: ' . $ex->getMessage());
                $errorGeneral = 'No fue posible crear la cuenta. Intenta nuevamente más tarde.';
            }
        } catch (Throwable $ex) {
            error_log('Nexus FMAT · error en el alta de cuenta: ' . $ex->getMessage());
            if ($ex instanceof InvalidArgumentException) {
                $errorGeneral = $ex->getMessage();
            } elseif ($esAltaAdmin) {
                $errorGeneral = 'No fue posible crear la cuenta administradora. Comprueba que el correo termine en @'
                    . ltrim((string) DOMINIO_CORREO, '@')
                    . ', que no esté registrado y que tu sesión siga siendo de administrador.';
            } else {
                $errorGeneral = 'No fue posible crear la cuenta. Revisa los datos e inténtalo de nuevo.';
            }
        }
    }
}

$rolVisible = in_array($valores['rol'], ['alumno', 'asesor', 'admin'], true)
    ? $valores['rol']
    : 'alumno';
if (!$esAdminActual && $rolVisible === 'admin') {
    $rolVisible = 'alumno';
}
$valores['rol'] = $rolVisible;

vista('auth/registro', [
    'titulo' => 'Registro de cuenta',
    'esAdminActual' => $esAdminActual,
    'modoAdminSolicitado' => $modoAdminSolicitado,
    'valores' => $valores,
    'errores' => $errores,
    'errorGeneral' => $errorGeneral,
    'licenciaturas' => $licenciaturas,
]);
