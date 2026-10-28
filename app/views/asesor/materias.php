<?php
/**
 * Vista de las materias del asesor (ISS-11).
 *
 * Recibe:
 * - $materias: materias que imparte el asesor (id, clave, nombre).
 * - $disponibles: materias activas que todavía no imparte.
 */
?>
<section class="seccion">
    <h1>Mis materias</h1>
    <p class="texto-suave">Elige las materias de las que das asesoría. Después podrás publicar tu disponibilidad para cada una.</p>
</section>

<section class="seccion">
    <h2>Materias que impartes</h2>

    <?php if (empty($materias)): ?>
        <div class="alerta alerta-info" role="status">Todavía no impartes ninguna materia. Agrega una abajo.</div>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Clave</th>
                        <th scope="col">Materia</th>
                        <th scope="col">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materias as $materia): ?>
                        <tr>
                            <td><?= e($materia['clave']) ?></td>
                            <td><?= e($materia['nombre']) ?></td>
                            <td>
                                <form method="post" action="<?= e(url('mis_materias.php')) ?>">
                                    <?php csrfCampo(); ?>
                                    <input type="hidden" name="accion" value="quitar">
                                    <input type="hidden" name="materia_id" value="<?= e($materia['id']) ?>">
                                    <button class="boton boton-peligro" type="submit"
                                        data-confirmar="¿Quitar <?= e($materia['nombre']) ?>? También se borrarán tus bloques de disponibilidad de esta materia.">
                                        Quitar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="seccion">
    <h2>Agregar una materia</h2>

    <?php if (empty($disponibles)): ?>
        <p class="texto-suave">Ya impartes todas las materias activas.</p>
    <?php else: ?>
        <form class="formulario" method="post" action="<?= e(url('mis_materias.php')) ?>">   
        <?php csrfCampo(); ?>
        <input type="hidden" name="accion" value="agregar">
            <div class="campo">
                <label for="materia_id">Materia</label>
                <select id="materia_id" name="materia_id" required>
                    <?php foreach ($disponibles as $materia): ?>
                        <option value="<?= e($materia['id']) ?>"><?= e($materia['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="acciones">
                <button class="boton boton-destacado" type="submit">Agregar</button>
            </div>
        </form>
    <?php endif; ?>
</section>