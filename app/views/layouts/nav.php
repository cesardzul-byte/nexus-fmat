<?php
/**
 * Lista de enlaces de la navegación. El header la incluye dos veces: una para
 * escritorio y otra dentro del menú desplegable de celular, así no se repite el HTML.
 *
 * El visitante ve Inicio, Iniciar sesión y Registrarse. El usuario con sesión
 * ve Inicio, Panel, su nombre y Cerrar sesión.
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
        <li class="nav-usuario"><?= e($usuarioNav['nombre']) ?></li>
        <li>
            <!-- Cerrar sesión es POST para que un enlace externo no pueda cerrarla (ISS-07 crea logout.php) -->
            <form method="post" action="<?= e(url('logout.php')) ?>">
                <?php if (function_exists('csrfCampo')): ?>
                    <?php csrfCampo(); ?>
                <?php endif; ?>
                <button type="submit">Cerrar sesión</button>
            </form>
        </li>
    <?php endif; ?>
</ul>
