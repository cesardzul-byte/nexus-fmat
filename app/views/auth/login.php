<?php
/**
 * Vista del formulario de inicio de sesión.
 * Variables recibidas desde public/login.php: $correo y $error.
 */
$correo = isset($correo) && is_string($correo) ? $correo : '';
$error = isset($error) && is_string($error) ? $error : '';
?>
<section class="tarjeta login" aria-labelledby="titulo-login">
    <h1 id="titulo-login">Iniciar sesión</h1>
    <p>Ingresa con tu correo institucional y contraseña para continuar a Nexus FMAT.</p>

    <?php if ($error !== ''): ?>
        <div class="alerta alerta-error" role="alert">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('login.php')) ?>" class="formulario">
        <?php csrfCampo(); ?>

        <div class="campo">
            <label for="correo">Correo institucional</label>
            <input
                id="correo"
                name="correo"
                type="email"
                value="<?= e($correo) ?>"
                autocomplete="username"
                maxlength="150"
                required
            >
        </div>

        <div class="campo">
            <label for="contrasena">Contraseña</label>
            <input
                id="contrasena"
                name="contrasena"
                type="password"
                autocomplete="current-password"
                required
            >
        </div>

        <button class="boton boton-principal" type="submit">Iniciar sesión</button>
    </form>

    <p>¿Todavía no tienes cuenta? <a href="<?= e(url('registro.php')) ?>">Regístrate aquí</a>.</p>
    <p><a href="<?= e(url()) ?>">Volver al inicio</a></p>
</section>
