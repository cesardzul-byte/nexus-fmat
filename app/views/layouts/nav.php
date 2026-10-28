<?php
/**
 * Lista de enlaces de navegación. El header la incluye tanto en escritorio
 * como en el menú móvil, para no duplicar la lógica de permisos.
 */
$usuarioNav = usuarioActual();
?>
<ul class="lista-nav">
    <li><a href="<?= e(url()) ?>">Inicio</a></li>
    <?php if ($usuarioNav === null): ?>
        <li><a href="<?= e(url('login.php')) ?>">Iniciar sesión</a></li>
        <li><a href="<?= e(url('registro.php')) ?>">Registrarse</a></li>
    <?php else: ?>
        <li><a href="<?= e(url('panel.php')) ?>">Panel</a></li>
        <?php if (($usuarioNav['rol'] ?? null) === 'admin'): ?>
            <li><a href="<?= e(url('registro.php?tipo=admin')) ?>">Registrar administrador</a></li>
        <?php endif; ?>
        <li class="nav-usuario"><?= e($usuarioNav['nombre'] ?? '') ?></li>
        <li>
            <!-- El cierre usa POST y token CSRF (ISS-07). -->
            <form method="post" action="<?= e(url('logout.php')) ?>">
                <?php csrfCampo(); ?>
                <button type="submit">Cerrar sesión</button>
            </form>
        </li>
    <?php endif; ?>
</ul>
