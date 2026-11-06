<?php
/**
 * Vista de la disponibilidad del asesor (public/disponibilidad.php, ISS-12).
 *
 * Recibe:
 * - $materias: materias que imparte el asesor (id, clave, nombre).
 * - $nombresDia: días que se pueden elegir, [1 => 'Lunes', … 6 => 'Sábado'].
 * - $bloquesPorDia: bloques agrupados por nombre del día; cada uno trae id, materia, horario y lugar.
 * - $bloque: valores del formulario (id, materia_id, dia_semana, hora_inicio, hora_fin, lugar); id 0 es un bloque nuevo.
 * - $errores: mensajes de validación del último envío.
 */
$editando = (int) $bloque['id'] !== 0;
?>
<section class="seccion">
    <h1>Mi disponibilidad</h1>
    <p class="texto-suave">Publica los días y horas en que das asesoría. Un bloque no puede empalmarse con otro tuyo, aunque sea de otra materia.</p>
</section>

<?php if ($materias === []): ?>
    <div class="alerta alerta-info" role="status">
        Todavía no impartes ninguna materia.
        <a href="<?= e(url('mis_materias.php')) ?>">Agrega tus materias</a> para publicar tu disponibilidad.
    </div>
<?php else: ?>
    <section class="seccion">
        <h2>Mis bloques</h2>

        <?php if ($bloquesPorDia === []): ?>
            <p class="texto-suave">Todavía no publicas bloques. Agrega el primero abajo.</p>
        <?php else: ?>
            <?php foreach ($bloquesPorDia as $dia => $bloques): ?>
                <section class="dia-disponibilidad">
                    <h3><?= e($dia) ?></h3>
                    <div class="tabla-envoltura">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th scope="col">Horario</th>
                                    <th scope="col">Materia</th>
                                    <th scope="col">Lugar</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bloques as $fila): ?>
                                    <tr>
                                        <td><?= e($fila['horario']) ?></td>
                                        <td><?= e($fila['materia']) ?></td>
                                        <td><?= e($fila['lugar']) ?></td>
                                        <td>
                                            <div class="acciones">
                                                <a class="boton boton-secundario" href="<?= e(url('disponibilidad.php?editar=' . $fila['id'] . '#formulario-bloque')) ?>">Editar</a>
                                                <form method="post" action="<?= e(url('disponibilidad.php')) ?>">
                                                    <?php csrfCampo(); ?>
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?= e($fila['id']) ?>">
                                                    <button class="boton boton-peligro" type="submit"
                                                        data-confirmar="¿Eliminar el bloque de <?= e($fila['materia']) ?>, <?= e($dia) ?> <?= e($fila['horario']) ?>?">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="seccion" id="formulario-bloque">
        <h2><?= e($editando ? 'Editar bloque' : 'Publicar un bloque') ?></h2>

        <?php foreach ($errores as $error): ?>
            <div class="alerta alerta-error" role="alert"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form class="formulario" method="post" action="<?= e(url('disponibilidad.php')) ?>">
            <?php csrfCampo(); ?>
            <input type="hidden" name="accion" value="guardar">
            <input type="hidden" name="id" value="<?= e($bloque['id']) ?>">

            <div class="campo">
                <label for="materia_id">Materia</label>
                <select id="materia_id" name="materia_id" required>
                    <option value="">Elige una materia</option>
                    <?php foreach ($materias as $materia): ?>
                        <option value="<?= e($materia['id']) ?>" <?= (int) $materia['id'] === (int) $bloque['materia_id'] ? 'selected' : '' ?>>
                            <?= e($materia['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="dia_semana">Día</label>
                <select id="dia_semana" name="dia_semana" required>
                    <option value="">Elige un día</option>
                    <?php foreach ($nombresDia as $numero => $nombre): ?>
                        <option value="<?= e($numero) ?>" <?= $numero === (int) $bloque['dia_semana'] ? 'selected' : '' ?>>
                            <?= e($nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="hora_inicio">Hora de inicio</label>
                <input id="hora_inicio" name="hora_inicio" type="time" value="<?= e($bloque['hora_inicio']) ?>"
                    step="<?= e(MINUTOS_BLOQUE * 60) ?>" min="<?= e(HORA_APERTURA) ?>" max="<?= e(HORA_CIERRE) ?>"
                    aria-describedby="ayuda-horas" required>
            </div>

            <div class="campo">
                <label for="hora_fin">Hora de fin</label>
                <input id="hora_fin" name="hora_fin" type="time" value="<?= e($bloque['hora_fin']) ?>"
                    step="<?= e(MINUTOS_BLOQUE * 60) ?>" min="<?= e(HORA_APERTURA) ?>" max="<?= e(HORA_CIERRE) ?>"
                    aria-describedby="ayuda-horas" required>
                <span class="campo-ayuda" id="ayuda-horas">
                    De <?= e(HORA_APERTURA) ?> a <?= e(HORA_CIERRE) ?>, en horas exactas o medias horas.
                </span>
            </div>

            <div class="campo">
                <label for="lugar">Lugar</label>
                <input id="lugar" name="lugar" type="text" value="<?= e($bloque['lugar']) ?>"
                    maxlength="<?= e(LONGITUD_MAX_LUGAR) ?>" placeholder="Ej. Biblioteca, Aula 101" required>
            </div>

            <div class="acciones">
                <button class="boton boton-destacado" type="submit"><?= e($editando ? 'Guardar cambios' : 'Publicar bloque') ?></button>
                <?php if ($editando): ?>
                    <a class="boton boton-secundario" href="<?= e(url('disponibilidad.php')) ?>">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </section>
<?php endif; ?>
