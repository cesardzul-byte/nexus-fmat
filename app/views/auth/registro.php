<?php
/** Formulario público de registro de alumnos y asesores (ISS-08). */
$valores = isset($valores) && is_array($valores) ? $valores : [];
$errores = isset($errores) && is_array($errores) ? $errores : [];
$errorGeneral = isset($errorGeneral) && is_string($errorGeneral) ? $errorGeneral : '';
$licenciaturas = isset($licenciaturas) && is_array($licenciaturas) ? $licenciaturas : [];

$valor = static function (string $campo) use ($valores): string {
    $dato = $valores[$campo] ?? '';
    return is_string($dato) ? $dato : '';
};
$errorCampo = static function (string $campo) use ($errores): void {
    if (isset($errores[$campo]) && is_string($errores[$campo])) {
        echo '<small class="error-campo" id="error-' . e($campo) . '" role="alert">'
            . e($errores[$campo]) . '</small>';
    }
};
$atributosError = static function (string $campo) use ($errores): string {
    return isset($errores[$campo])
        ? ' aria-invalid="true" aria-describedby="error-' . e($campo) . '"'
        : '';
};
$patronCorreo = '.+@' . preg_quote((string) DOMINIO_CORREO, '/');
$rolElegido = in_array($valor('rol'), ['alumno', 'asesor'], true) ? $valor('rol') : 'alumno';
?>
<section class="tarjeta registro" aria-labelledby="titulo-registro">
    <h1 id="titulo-registro">Crear cuenta</h1>
    <p>Regístrate para utilizar Nexus FMAT. Puedes crear una cuenta de alumno o asesor.</p>

    <?php if ($errorGeneral !== ''): ?>
        <div class="alerta alerta-error" role="alert"><?= e($errorGeneral) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('registro.php')) ?>" class="formulario" id="formulario-registro">
        <?php csrfCampo(); ?>

        <div class="campo">
            <label for="nombre">Nombre(s)</label>
            <input id="nombre" name="nombre" type="text" value="<?= e($valor('nombre')) ?>"
                autocomplete="given-name" maxlength="<?= Usuario::LONGITUD_MAXIMA_NOMBRE ?>" required<?= $atributosError('nombre') ?>>
            <?php $errorCampo('nombre'); ?>
        </div>

        <div class="campo">
            <label for="apellidos">Apellidos</label>
            <input id="apellidos" name="apellidos" type="text" value="<?= e($valor('apellidos')) ?>"
                autocomplete="family-name" maxlength="<?= Usuario::LONGITUD_MAXIMA_APELLIDOS ?>" required<?= $atributosError('apellidos') ?>>
            <?php $errorCampo('apellidos'); ?>
        </div>

        <div class="campo">
            <label for="correo">Correo institucional</label>
            <input id="correo" name="correo" type="email" value="<?= e($valor('correo')) ?>"
                autocomplete="email" maxlength="<?= Usuario::LONGITUD_MAXIMA_CORREO ?>" pattern="<?= e($patronCorreo) ?>" required
                <?= isset($errores['correo']) ? 'aria-invalid="true" aria-describedby="ayuda-correo error-correo"' : 'aria-describedby="ayuda-correo"' ?>>
            <small id="ayuda-correo">Usa una cuenta @<?= e(DOMINIO_CORREO) ?>.</small>
            <?php $errorCampo('correo'); ?>
        </div>

        <div class="campo">
            <label for="matricula">Matrícula</label>
            <input id="matricula" name="matricula" type="text" value="<?= e($valor('matricula')) ?>"
                inputmode="numeric" autocomplete="off"
                pattern="[0-9]{<?= Usuario::LONGITUD_MATRICULA ?>}"
                minlength="<?= Usuario::LONGITUD_MATRICULA ?>" maxlength="<?= Usuario::LONGITUD_MATRICULA ?>"
                title="Escribe los ocho dígitos de tu matrícula." required<?= $atributosError('matricula') ?>>
            <?php $errorCampo('matricula'); ?>
        </div>

        <div class="campo">
            <label for="licenciatura_id">Licenciatura</label>
            <select id="licenciatura_id" name="licenciatura_id" required<?= $atributosError('licenciatura_id') ?>>
                <option value="">Selecciona tu licenciatura</option>
                <?php foreach ($licenciaturas as $licenciatura): ?>
                    <?php $idLicenciatura = (string) (int) ($licenciatura['id'] ?? 0); ?>
                    <option value="<?= e($idLicenciatura) ?>" <?= $valor('licenciatura_id') === $idLicenciatura ? 'selected' : '' ?>>
                        <?= e((string) ($licenciatura['nombre'] ?? '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($licenciaturas === []): ?>
                <small class="error-campo">No hay licenciaturas disponibles. Inténtalo más tarde.</small>
            <?php endif; ?>
            <?php $errorCampo('licenciatura_id'); ?>
        </div>

        <div class="campo">
            <label for="semestre">Semestre</label>
            <select id="semestre" name="semestre" required<?= $atributosError('semestre') ?>>
                <option value="">Selecciona tu semestre</option>
                <?php for ($semestre = 1; $semestre <= Usuario::SEMESTRE_MAXIMO; $semestre++): ?>
                    <option value="<?= $semestre ?>" <?= $valor('semestre') === (string) $semestre ? 'selected' : '' ?>>
                        <?= $semestre ?><?= $semestre === 1 ? 'er' : 'º' ?> semestre
                    </option>
                <?php endfor; ?>
            </select>
            <?php $errorCampo('semestre'); ?>
        </div>

        <div class="campo">
            <label for="rol">Tipo de cuenta</label>
            <select id="rol" name="rol" required<?= $atributosError('rol') ?>>
                <option value="alumno" <?= $rolElegido === 'alumno' ? 'selected' : '' ?>>Alumno</option>
                <option value="asesor" <?= $rolElegido === 'asesor' ? 'selected' : '' ?>>Asesor</option>
            </select>
            <?php $errorCampo('rol'); ?>
        </div>

        <div class="campo">
            <label for="contrasena">Contraseña</label>
            <input id="contrasena" name="contrasena" type="password" autocomplete="new-password"
                minlength="<?= Usuario::LONGITUD_MINIMA_CONTRASENA ?>" required<?= $atributosError('contrasena') ?>>
            <small>Debe tener al menos <?= Usuario::LONGITUD_MINIMA_CONTRASENA ?> caracteres.</small>
            <?php $errorCampo('contrasena'); ?>
        </div>

        <div class="campo">
            <label for="confirmar_contrasena">Confirmar contraseña</label>
            <input id="confirmar_contrasena" name="confirmar_contrasena" type="password"
                autocomplete="new-password" minlength="<?= Usuario::LONGITUD_MINIMA_CONTRASENA ?>"
                required<?= $atributosError('confirmar_contrasena') ?>>
            <?php $errorCampo('confirmar_contrasena'); ?>
        </div>

        <button class="boton boton-principal" type="submit">Crear cuenta</button>
    </form>

    <p>¿Ya tienes una cuenta? <a href="<?= e(url('login.php')) ?>">Inicia sesión</a>.</p>
</section>
