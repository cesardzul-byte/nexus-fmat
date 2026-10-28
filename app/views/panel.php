<?php
/**
 * Vista del panel (public/panel.php).
 *
 * Recibe:
 * - $usuario (array): id, nombre y rol del usuario con sesión.
 * - $nombreRol (string): el rol escrito para mostrarlo ("Alumno", "Asesor", "Administrador").
 * - $conteoRoles (array|null): usuarios activos por rol; solo para el administrador.
 * - $totalMaterias (int|null): materias activas; solo para el administrador.
 */
?>
<section class="saludo">
    <span class="etiqueta etiqueta-neutra"><?= e($nombreRol) ?></span>
    <h1>Hola, <?= e($usuario['nombre']) ?></h1>
    <p class="texto-suave">¿Qué quieres hacer hoy?</p>
</section>

<?php if ($usuario['rol'] === 'alumno'): ?>
    <div class="rejilla-tarjetas">
        <article class="tarjeta">
            <h2 class="tarjeta-titulo">Buscar asesoría por materia</h2>
            <p class="texto-suave">Revisa qué asesores dan la materia que necesitas y sus horarios.</p>
            <a class="boton boton-destacado" href="<?= e(url()) ?>">Buscar materia</a>
        </article>
    </div>

<?php elseif ($usuario['rol'] === 'asesor'): ?>
    <div class="rejilla-tarjetas">
        <article class="tarjeta">
            <h2 class="tarjeta-titulo">Mis materias</h2>
            <p class="texto-suave">Elige las materias en las que das asesoría.</p>
            <a class="boton boton-secundario" href="<?= e(url('mis_materias.php')) ?>">Ver mis materias</a>
        </article>
        <article class="tarjeta">
            <h2 class="tarjeta-titulo">Mi disponibilidad</h2>
            <p class="texto-suave">Publica los días y horas en que puedes dar asesoría.</p>
            <a class="boton boton-destacado" href="<?= e(url('disponibilidad.php')) ?>">Ver mi disponibilidad</a>
        </article>
    </div>

<?php elseif ($usuario['rol'] === 'admin'): ?>
    <h2>Resumen</h2>
    <div class="rejilla-estadisticas">
        <article class="tarjeta tarjeta-estadistica">
            <p class="estadistica-numero"><?= e($conteoRoles['alumno']) ?></p>
            <p class="estadistica-etiqueta">Alumnos</p>
        </article>
        <article class="tarjeta tarjeta-estadistica">
            <p class="estadistica-numero"><?= e($conteoRoles['asesor']) ?></p>
            <p class="estadistica-etiqueta">Asesores</p>
        </article>
        <article class="tarjeta tarjeta-estadistica">
            <p class="estadistica-numero"><?= e($conteoRoles['admin']) ?></p>
            <p class="estadistica-etiqueta">Administradores</p>
        </article>
        <article class="tarjeta tarjeta-estadistica">
            <p class="estadistica-numero"><?= e($totalMaterias) ?></p>
            <p class="estadistica-etiqueta">Materias activas</p>
        </article>
    </div>
<?php endif; ?>
