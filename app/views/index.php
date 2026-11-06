<?php
/**
 * Vista de la página de inicio (public/index.php).
 *
 * Recibe:
 * - $usuario (array|null): el usuario con sesión, o null si es visitante.
 * - $materias (array): materias activas con id, clave, nombre y cantidad_asesores.
 */
?>
<section class="portada">
    <h1 class="portada-titulo">Aprende con quien ya pasó por esa materia</h1>
    <p class="portada-texto">
        Nexus FMAT conecta a estudiantes de la Facultad de Matemáticas con compañeros
        que dan asesorías. Encuentra a tu asesor, revisa sus horarios y agenda tu sesión.
    </p>
    <div class="acciones">
        <?php if ($usuario === null): ?>
            <a class="boton boton-destacado" href="<?= e(url('registro.php')) ?>">Registrarse</a>
            <a class="boton boton-secundario" href="<?= e(url('login.php')) ?>">Iniciar sesión</a>
        <?php else: ?>
            <a class="boton boton-destacado" href="<?= e(url('panel.php')) ?>">Ir al panel</a>
        <?php endif; ?>
    </div>
</section>

<section class="seccion" aria-labelledby="titulo-pasos">
    <h2 id="titulo-pasos">¿Cómo funciona?</h2>
    <ol class="pasos">
        <li class="paso">
            <span class="paso-numero" aria-hidden="true">1</span>
            <h3 class="paso-titulo">Busca tu materia</h3>
            <p class="texto-suave">Revisa qué asesores dan la materia que se te complica.</p>
        </li>
        <li class="paso">
            <span class="paso-numero" aria-hidden="true">2</span>
            <h3 class="paso-titulo">Elige un horario</h3>
            <p class="texto-suave">Cada asesor publica los días y horas en que puede ayudarte.</p>
        </li>
        <li class="paso">
            <span class="paso-numero" aria-hidden="true">3</span>
            <h3 class="paso-titulo">Agenda y califica</h3>
            <p class="texto-suave">Toma tu asesoría y después cuéntanos cómo te fue.</p>
        </li>
    </ol>
</section>

<section class="seccion" aria-labelledby="titulo-materias">
    <h2 id="titulo-materias">Materias</h2>

    <div class="campo buscador">
        <label for="filtro-materias">Buscar materia</label>
        <input id="filtro-materias" type="search" placeholder="Ej. Álgebra" autocomplete="off" data-filtro-materias>
    </div>

    <?php if ($materias === []): ?>
        <div class="alerta alerta-info" role="status">Todavía no hay materias registradas.</div>
    <?php else: ?>
        <div class="rejilla-tarjetas">
            <?php foreach ($materias as $materia): ?>
                <article class="tarjeta" data-tarjeta-materia data-nombre="<?= e($materia['nombre']) ?>">
                    <?php if ((int) $materia['cantidad_asesores'] === 0): ?>
                        <span class="etiqueta etiqueta-neutra">Sin asesores aún</span>
                    <?php else: ?>
                        <span class="etiqueta">
                            <?= e($materia['cantidad_asesores']) ?> <?= (int) $materia['cantidad_asesores'] === 1 ? 'asesor' : 'asesores' ?>
                        </span>
                    <?php endif; ?>
                    <h3 class="tarjeta-titulo"><?= e($materia['nombre']) ?></h3>
                    <p class="texto-suave tarjeta-clave"><?= e($materia['clave']) ?></p>
                    <a class="boton boton-secundario" href="<?= e(url('materia.php?id=' . $materia['id'])) ?>">Ver asesores</a>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="alerta alerta-info" data-sin-resultados hidden>No encontramos materias con ese nombre.</p>
    <?php endif; ?>
</section>
