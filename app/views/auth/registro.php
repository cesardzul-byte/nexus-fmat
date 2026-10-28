<?php
/** Formulario único de registro para alumnos, asesores y altas administrativas autorizadas. */
$esAdminActual = isset($esAdminActual) && $esAdminActual === true;
$modoAdminSolicitado = isset($modoAdminSolicitado) && $modoAdminSolicitado === true;
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
$rolElegido = $valor('rol');
$rolesDisponibles = $esAdminActual ? ['alumno', 'asesor', 'admin'] : ['alumno', 'asesor'];
if (!in_array($rolElegido, $rolesDisponibles, true)) {
    $rolElegido = 'alumno';
}
$esAltaAdmin = $esAdminActual && $rolElegido === 'admin';
?>
<section class="tarjeta registro" aria-labelledby="titulo-registro">
    <h1 id="titulo-registro"><?= $esAdminActual ? 'Registrar cuenta' : 'Crear cuenta' ?></h1>
    <?php if ($esAdminActual): ?>
        <p>Elige el tipo de cuenta. Para registrar administradores necesitas una sesión administrativa activa.</p>
        <?php if ($modoAdminSolicitado): ?>
            <p class="texto-suave">Modo de alta administrativa: matrícula, licenciatura y semestre no son necesarios.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>Regístrate para utilizar Nexus FMAT. Puedes crear una cuenta de alumno o asesor.</p>
    <?php endif; ?>

    <?php if ($errorGeneral !== ''): ?>
        <div class="alerta alerta-error" role="alert"><?= e($errorGeneral) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url($modoAdminSolicitado ? 'registro.php?modo=admin' : 'registro.php')) ?>" class="formulario" id="formulario-registro">
        <?php csrfCampo(); ?>

        <div class="campo">
            <label for="nombre">Nombre(s)</label>
            <input id="nombre" name="nombre" type="text" value="<?= e($valor('nombre')) ?>"
                autocomplete="given-name" maxlength="100" required<?= $atributosError('nombre') ?>>
            <?php $errorCampo('nombre'); ?>
        </div>

        <div class="campo">
            <label for="apellidos">Apellidos</label>
            <input id="apellidos" name="apellidos" type="text" value="<?= e($valor('apellidos')) ?>"
                autocomplete="family-name" maxlength="100" required<?= $atributosError('apellidos') ?>>
            <?php $errorCampo('apellidos'); ?>
        </div>

        <div class="campo">
            <label for="correo">Correo institucional</label>
            <input id="correo" name="correo" type="email" value="<?= e($valor('correo')) ?>"
                autocomplete="email" maxlength="150" pattern="<?= e($patronCorreo) ?>" required
                <?= isset($errores['correo']) ? 'aria-invalid="true" aria-describedby="ayuda-correo error-correo"' : 'aria-describedby="ayuda-correo"' ?>>
            <small id="ayuda-correo">Usa una cuenta @<?= e(DOMINIO_CORREO) ?>.</small>
            <?php $errorCampo('correo'); ?>
        </div>

        <div class="campo">
            <label for="rol">Tipo de cuenta</label>
            <select id="rol" name="rol" required data-permite-admin="<?= $esAdminActual ? 'true' : 'false' ?>"<?= $atributosError('rol') ?>>
                <option value="alumno" <?= $rolElegido === 'alumno' ? 'selected' : '' ?>>Alumno</option>
                <option value="asesor" <?= $rolElegido === 'asesor' ? 'selected' : '' ?>>Asesor</option>
                <?php if ($esAdminActual): ?>
                    <option value="admin" <?= $rolElegido === 'admin' ? 'selected' : '' ?>>Administrador</option>
                <?php endif; ?>
            </select>
            <?php $errorCampo('rol'); ?>
        </div>

        <fieldset id="datos-academicos" class="grupo-campos" <?= $esAltaAdmin ? 'hidden' : '' ?>>
            <legend>Datos académicos</legend>
            <p>Alumnos y asesores comparten estos datos; el rol determina cómo utilizarán la plataforma.</p>

            <div class="campo">
                <label for="matricula">Matrícula</label>
                <input id="matricula" name="matricula" type="text" value="<?= e($valor('matricula')) ?>"
                    inputmode="numeric" autocomplete="off" pattern="[0-9]{8}" minlength="8" maxlength="8"
                    title="Escribe los ocho dígitos de tu matrícula."
                    <?= $esAltaAdmin ? 'disabled' : 'required' ?><?= $atributosError('matricula') ?>>
                <?php $errorCampo('matricula'); ?>
            </div>

            <div class="campo">
                <label for="licenciatura_id">Licenciatura</label>
                <select id="licenciatura_id" name="licenciatura_id" <?= $esAltaAdmin ? 'disabled' : 'required' ?><?= $atributosError('licenciatura_id') ?>>
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
                <select id="semestre" name="semestre" <?= $esAltaAdmin ? 'disabled' : 'required' ?><?= $atributosError('semestre') ?>>
                    <option value="">Selecciona tu semestre</option>
                    <?php for ($semestre = 1; $semestre <= 9; $semestre++): ?>
                        <option value="<?= $semestre ?>" <?= $valor('semestre') === (string) $semestre ? 'selected' : '' ?>>
                            <?= $semestre ?><?= $semestre === 1 ? 'er' : 'º' ?> semestre
                        </option>
                    <?php endfor; ?>
                </select>
                <?php $errorCampo('semestre'); ?>
            </div>
        </fieldset>

        <div class="campo">
            <label for="contrasena">Contraseña</label>
            <input id="contrasena" name="contrasena" type="password" autocomplete="new-password"
                minlength="8" required<?= $atributosError('contrasena') ?>>
            <small>Debe tener al menos 8 caracteres.</small>
            <?php $errorCampo('contrasena'); ?>
        </div>

        <div class="campo">
            <label for="confirmar_contrasena">Confirmar contraseña</label>
            <input id="confirmar_contrasena" name="confirmar_contrasena" type="password"
                autocomplete="new-password" minlength="8" required<?= $atributosError('confirmar_contrasena') ?>>
            <?php $errorCampo('confirmar_contrasena'); ?>
        </div>

        <button class="boton boton-principal" type="submit">
            <?= $esAltaAdmin ? 'Crear administrador' : ($esAdminActual ? 'Crear cuenta' : 'Crear cuenta') ?>
        </button>
        <?php if ($esAdminActual): ?>
            <a class="boton boton-secundario" href="<?= e(url('panel.php')) ?>">Volver al panel</a>
        <?php endif; ?>
    </form>

    <?php if (!$esAdminActual): ?>
        <p>¿Ya tienes una cuenta? <a href="<?= e(url('login.php')) ?>">Inicia sesión</a>.</p>
    <?php endif; ?>
    <?php if ($esAltaAdmin): ?>
        <p><small>Las cuentas administradoras no tienen matrícula, licenciatura ni semestre.</small></p>
    <?php endif; ?>
</section>
