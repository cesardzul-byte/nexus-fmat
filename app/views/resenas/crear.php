<?php
/**
 * Vista para calificar una sesión (public/resena.php, ISS-24).
 *
 * Recibe:
 * - $sesion: la fila de la sesión (se usa su tema).
 * - $materia, $asesor, $fecha: textos listos para mostrar.
 * - $avisoSesion: mensaje si la sesión no se puede calificar, o null para mostrar el formulario.
 * - $errores: mensajes de validación del último envío.
 * - $calificacion, $comentario: lo que el alumno envió, para no perderlo si hubo errores.
 * - $rutaFormulario: resena.php con ?sesion=, a donde se envía el formulario.
 */
?>
<p><a class="enlace-volver" href="<?= e(url('mis_sesiones.php')) ?>">← Mis sesiones</a></p>

<section class="seccion">
    <h1>Calificar sesión</h1>
    <p class="texto-suave">
        <?= e($materia) ?> con <?= e($asesor) ?>, el <?= e($fecha) ?>. Tema: <?= e($sesion['tema']) ?>.
    </p>
</section>

<?php if ($avisoSesion !== null): ?>
    <div class="alerta alerta-info" role="status"><?= e($avisoSesion) ?></div>
<?php else: ?>
    <section class="seccion">
        <?php foreach ($errores as $error): ?>
            <div class="alerta alerta-error" role="alert"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form class="formulario" method="post" action="<?= e(url($rutaFormulario)) ?>">
            <?php csrfCampo(); ?>

            <div class="campo">
                <label for="calificacion">Calificación</label>
                <select id="calificacion" name="calificacion" required>
                    <option value="">Elige de <?= e(Resena::CALIFICACION_MIN) ?> a <?= e(Resena::CALIFICACION_MAX) ?></option>
                    <?php for ($i = Resena::CALIFICACION_MAX; $i >= Resena::CALIFICACION_MIN; $i--): ?>
                        <option value="<?= e($i) ?>" <?= $i === $calificacion ? 'selected' : '' ?>>
                            <?= e(str_repeat('★', $i) . ' ' . $i) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="campo">
                <label for="comentario">Comentario (opcional)</label>
                <textarea id="comentario" name="comentario" rows="4"
                    maxlength="<?= e(Resena::LONGITUD_MAX_COMENTARIO) ?>" aria-describedby="ayuda-comentario"><?= e($comentario) ?></textarea>
                <span class="campo-ayuda" id="ayuda-comentario">
                    Hasta <?= e(Resena::LONGITUD_MAX_COMENTARIO) ?> caracteres. Solo lo ven tú y el asesor.
                </span>
            </div>

            <div class="acciones">
                <button class="boton boton-destacado" type="submit">Enviar reseña</button>
            </div>
        </form>
    </section>
<?php endif; ?>