<?php
/**
 * Vista para agendar una sesión (public/agendar.php, ISS-18).
 *
 * Recibe:
 * - $materia: id y nombre de la materia.
 * - $asesor: nombre y apellidos del asesor.
 * - $bloques: bloques del asesor en esta materia; cada uno trae 'dia', 'horario' y 'lugar' listos para mostrar.
 * - $solicitud: valores del formulario (fecha, hora_inicio, hora_fin, tema).
 * - $errores: mensajes de validación del último envío.
 * - $hoy: fecha de hoy en 'AAAA-MM-DD', el mínimo del calendario.
 * - $rutaFormulario: agendar.php con ?materia=&asesor=, a donde se envía el formulario.
 */
$nombreAsesor = $asesor['nombre'] . ' ' . $asesor['apellidos'];
?>
<p><a class="enlace-volver" href="<?= e(url('materia.php?id=' . $materia['id'])) ?>">← <?= e($materia['nombre']) ?></a></p>

<section class="seccion">
    <h1>Agendar sesión</h1>
    <p class="texto-suave"><?= e($materia['nombre']) ?> con <?= e($nombreAsesor) ?>.</p>
</section>

<?php if ($bloques === []): ?>
    <div class="alerta alerta-info" role="status">
        <?= e($nombreAsesor) ?> todavía no publica horarios para esta materia.
    </div>
<?php else: ?>
    <section class="seccion">
        <h2>Horarios del asesor</h2>
        <p class="texto-suave">La sesión debe quedar completa dentro de uno de estos bloques.</p>
        <div class="tabla-envoltura">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Día</th>
                        <th scope="col">Horario</th>
                        <th scope="col">Lugar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bloques as $bloque): ?>
                        <tr>
                            <td><?= e($bloque['dia']) ?></td>
                            <td><?= e($bloque['horario']) ?></td>
                            <td><?= e($bloque['lugar']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="seccion">
        <h2>Tu solicitud</h2>

        <?php foreach ($errores as $error): ?>
            <div class="alerta alerta-error" role="alert"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form class="formulario" method="post" action="<?= e(url($rutaFormulario)) ?>">
            <?php csrfCampo(); ?>

            <div class="campo">
                <label for="fecha">Fecha</label>
                <input id="fecha" name="fecha" type="date" value="<?= e($solicitud['fecha']) ?>" min="<?= e($hoy) ?>"
                    aria-describedby="ayuda-fecha" required>
                <span class="campo-ayuda" id="ayuda-fecha">De lunes a sábado, en un día que tenga bloque.</span>
            </div>

            <div class="campo">
                <label for="hora_inicio">Hora de inicio</label>
                <input id="hora_inicio" name="hora_inicio" type="time" value="<?= e($solicitud['hora_inicio']) ?>"
                    step="<?= e(MINUTOS_BLOQUE * 60) ?>" min="<?= e(HORA_APERTURA) ?>" max="<?= e(HORA_CIERRE) ?>"
                    aria-describedby="ayuda-horas" required>
            </div>

            <div class="campo">
                <label for="hora_fin">Hora de fin</label>
                <input id="hora_fin" name="hora_fin" type="time" value="<?= e($solicitud['hora_fin']) ?>"
                    step="<?= e(MINUTOS_BLOQUE * 60) ?>" min="<?= e(HORA_APERTURA) ?>" max="<?= e(HORA_CIERRE) ?>"
                    aria-describedby="ayuda-horas" required>
                <span class="campo-ayuda" id="ayuda-horas">En horas exactas o medias horas.</span>
            </div>

            <div class="campo">
                <label for="tema">Tema</label>
                <input id="tema" name="tema" type="text" value="<?= e($solicitud['tema']) ?>"
                    maxlength="<?= e(Sesion::LONGITUD_MAX_TEMA) ?>" placeholder="Ej. Regla de la cadena" required>
            </div>

            <div class="acciones">
                <button class="boton boton-destacado" type="submit">Solicitar sesión</button>
            </div>
        </form>
    </section>
<?php endif; ?>
