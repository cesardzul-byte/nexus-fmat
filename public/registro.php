<?php
/** Controlador del registro público de alumnos y asesores (ISS-08). */
declare(strict_types=1);

require __DIR__ . '/../app/includes/bootstrap.php';

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metodo, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    abortar(405, 'Método no permitido.');
}

// El registro es para visitantes; una sesión activa debe continuar en el panel.
if (usuarioActual() !== null) {
    redirigir('panel.php');
}

$licenciaturas = Licenciatura::todas();
$valores = [
    'nombre' => '',
    'apellidos' => '',
    'matricula' => '',
    'correo' => '',
    'licenciatura_id' => '',
    'semestre' => '',
    'rol' => 'alumno',
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

if ($metodo === 'POST') {
    verificarCsrf();

    foreach (['nombre', 'apellidos', 'correo', 'matricula', 'licenciatura_id', 'semestre', 'rol'] as $campo) {
        $valores[$campo] = $leerCadena($campo);
    }
    $valores['correo'] = strtolower($valores['correo']);
    $contrasena = $leerSinRecortar('contrasena');
    $confirmacion = $leerSinRecortar('confirmar_contrasena');

    $errores = Usuario::erroresDatosBasicos([
        'nombre' => $valores['nombre'],
        'apellidos' => $valores['apellidos'],
        'correo' => $valores['correo'],
        'contrasena' => $contrasena,
    ]);

    if (!in_array($valores['rol'], ['alumno', 'asesor'], true)) {
        $errores['rol'] = 'El tipo de cuenta seleccionado no está permitido.';
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

    $patronMatricula = '/^[0-9]{' . Usuario::LONGITUD_MATRICULA . '}$/D';
    if ($valores['matricula'] !== '' && preg_match($patronMatricula, $valores['matricula']) !== 1) {
        $errores['matricula'] = 'La matrícula debe contener exactamente '
            . Usuario::LONGITUD_MATRICULA . ' dígitos.';
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
    $patronSemestre = '/^[1-' . Usuario::SEMESTRE_MAXIMO . ']$/D';
    if ($valores['semestre'] !== '' && preg_match($patronSemestre, $valores['semestre']) !== 1) {
        $errores['semestre'] = 'Selecciona un semestre del 1 al ' . Usuario::SEMESTRE_MAXIMO . '.';
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
    if (preg_match($patronMatricula, $valores['matricula']) === 1
        && !isset($errores['matricula']) && Usuario::existeMatricula($valores['matricula'])) {
        $errores['matricula'] = 'Ya existe una cuenta registrada con esta matrícula.';
    }

    if ($errores === []) {
        try {
            $idUsuario = Usuario::crear([
                'nombre' => $valores['nombre'],
                'apellidos' => $valores['apellidos'],
                'matricula' => $valores['matricula'],
                'correo' => $valores['correo'],
                'licenciatura_id' => (int) $valores['licenciatura_id'],
                'semestre' => (int) $valores['semestre'],
                'rol' => $valores['rol'],
                'contrasena' => $contrasena,
            ]);

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
            if ($valores['matricula'] !== '' && Usuario::existeMatricula($valores['matricula'])) {
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
                if ($valores['matricula'] !== '' && Usuario::existeMatricula($valores['matricula'])) {
                    $errores['matricula'] = 'Ya existe una cuenta registrada con esta matrícula.';
                }
                if ($errores === []) {
                    $errorGeneral = 'Uno de los datos ingresados ya está registrado.';
                }
            } else {
                error_log('Nexus FMAT · error al registrar usuario: ' . $ex->getMessage());
                $errorGeneral = 'No fue posible crear la cuenta. Intenta nuevamente más tarde.';
            }
        } catch (Throwable $ex) {
            error_log('Nexus FMAT · error en el registro: ' . $ex->getMessage());
            $errorGeneral = 'No fue posible crear la cuenta. Revisa los datos e inténtalo de nuevo.';
        }
    }
}

vista('auth/registro', [
    'titulo' => 'Registro de cuenta',
    'valores' => $valores,
    'errores' => $errores,
    'errorGeneral' => $errorGeneral,
    'licenciaturas' => $licenciaturas,
]);
