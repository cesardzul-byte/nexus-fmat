<?php
/**
 * Vista de sesiones del alumno o asesor (ISS-19).
 *
 * Recibe:
 * - $rol: alumno o asesor.
 * - $proximas: sesiones pendientes o confirmadas con sus acciones permitidas.
 * - $historial: sesiones completadas o canceladas, con reseña cuando existe.
 */
?>
<section class="seccion">
    <h1><?= e($rol === 'alumno' ? 'Mis sesiones' : 'Solicitudes y sesiones') ?></h1>
    <p class="texto-suave">
        <?= e($rol === 'alumno'
            ? 'Consulta tus próximas asesorías y revisa las que ya terminaron.'
            : 'Revisa las solicitudes recibidas y administra tus asesorías.') ?>
    </p>
</section>

<section class="seccion">
    <h2>Próximas</h2>

    <?php if ($proximas === []): ?>
        <div class="alerta alerta-info" role="status">
            No tienes sesiones próximas.
        </div>
    <?php else: ?>
        <div class="rejilla-tarjetas">
            <?php foreach ($proximas as $sesion): ?>
                <article class="tarjeta">
                    <div class="acciones">
                        <span class="etiqueta <?= e($sesion['estado_clase']) ?>"><?= e($sesion['estado_texto']) ?></span>
                    </div>
                    <h3 class="tarjeta-titulo"><?= e($sesion['materia']) ?></h3>
                    <p><strong><?= e($rol === 'alumno' ? 'Asesor:' : 'Alumno:') ?></strong> <?= e($sesion['otra_persona']) ?></p>
                    <p><strong>Fecha:</strong> <?= e($sesion['fecha_formato']) ?></p>
                    <p><strong>Horario:</strong> <?= e($sesion['horario']) ?></p>
                    <p><strong>Tema:</strong> <?= e($sesion['tema']) ?></p>
                    <p><strong>Lugar:</strong> <?= e($sesion['lugar']) ?></p>

                    <?php if ($sesion['acciones'] !== []): ?>
                        <div class="acciones">
                            <?php foreach ($sesion['acciones'] as $accion): ?>
                                <form method="post" action="<?= e(url('mis_sesiones.php')) ?>">
                                    <?php csrfCampo(); ?>
                                    <input type="hidden" name="sesion_id" value="<?= e($sesion['id']) ?>">
                                    <input type="hidden" name="estado_nuevo" value="<?= e($accion['estado']) ?>">
                                    <button class="<?= e($accion['clase']) ?>" type="submit"
                                        <?php if ($accion['confirmacion'] !== null): ?>
                                            data-confirmar="<?= e($accion['confirmacion']) ?>"
                                        <?php endif; ?>>
                                        <?= e($accion['texto']) ?>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="seccion">
    <h2>Historial</h2>

    <?php if ($historial === []): ?>
        <div class="alerta alerta-info" role="status">
            Todavía no tienes sesiones en el historial.
        </div>
    <?php else: ?>
        <div class="rejilla-tarjetas">
            <?php foreach ($historial as $sesion): ?>
                <article class="tarjeta">
                    <span class="etiqueta <?= e($sesion['estado_clase']) ?>"><?= e($sesion['estado_texto']) ?></span>
                    <h3 class="tarjeta-titulo"><?= e($sesion['materia']) ?></h3>
                    <p><strong><?= e($rol === 'alumno' ? 'Asesor:' : 'Alumno:') ?></strong> <?= e($sesion['otra_persona']) ?></p>
                    <p><strong>Fecha:</strong> <?= e($sesion['fecha_formato']) ?></p>
                    <p><strong>Horario:</strong> <?= e($sesion['horario']) ?></p>
                    <p><strong>Tema:</strong> <?= e($sesion['tema']) ?></p>
                    <p><strong>Lugar:</strong> <?= e($sesion['lugar']) ?></p>

                    <?php if ($sesion['calificacion'] !== null): ?>
                        <p><strong>Calificación:</strong> <?= e($sesion['calificacion']) ?>/5</p>
                    <?php endif; ?>
                    <?php if (is_string($sesion['comentario']) && trim($sesion['comentario']) !== ''): ?>
                        <p><strong>Comentario:</strong> <?= e($sesion['comentario']) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
