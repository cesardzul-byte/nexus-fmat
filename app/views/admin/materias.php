<?php
/**
 * Vista de la administración de materias (public/admin_materias.php, ISS-22).
 *
 * Recibe:
 * - $materias: todas las materias (id, clave, nombre, activa), activas e inactivas.
 * - $materia: valores del formulario (id, clave, nombre); id 0 es una materia nueva.
 * - $errores: mensajes del último envío, con llave 'clave' o 'nombre'.
 */
$editando = (int) $materia['id'] !== 0;
?>
<section class="seccion">
    <h1>Materias</h1>
    <p class="texto-suave">Crea, edita y desactiva las materias del catálogo. Una materia desactivada deja de verse en el inicio, pero conserva sus asesores, horarios y sesiones.</p>
</section>

<section class="seccion">
    <h2>Catálogo</h2>
    <div class="tabla-envoltura">
        <table class="tabla">
            <thead>
                <tr>
                    <th scope="col">Clave</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materias as $fila): ?>
                    <?php $activa = (int) $fila['activa'] === 1; ?>
                    <tr>
                        <td><?= e($fila['clave']) ?></td>
                        <td><?= e($fila['nombre']) ?></td>
                        <td>
                            <span class="etiqueta <?= $activa ? 'etiqueta-completada' : 'etiqueta-cancelada' ?>">
                                <?= $activa ? 'Activa' : 'Inactiva' ?>
                            </span>
                        </td>
                        <td>
                            <div class="acciones">
                                <a class="boton boton-secundario" href="<?= e(url('admin_materias.php?editar=' . $fila['id'] . '#formulario-materia')) ?>">Editar</a>
                                <form method="post" action="<?= e(url('admin_materias.php')) ?>">
                                    <?php csrfCampo(); ?>
                                    <input type="hidden" name="id" value="<?= e($fila['id']) ?>">
                                    <?php if ($activa): ?>
                                        <input type="hidden" name="accion" value="desactivar">
                                        <button class="boton boton-peligro" type="submit"
                                            data-confirmar="¿Desactivar <?= e($fila['nombre']) ?>? Dejará de verse en el inicio, pero no se borra nada.">
                                            Desactivar
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="accion" value="activar">
                                        <button class="boton" type="submit">Activar</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="seccion" id="formulario-materia">
    <h2><?= e($editando ? 'Editar materia' : 'Nueva materia') ?></h2>

    <form class="formulario" method="post" action="<?= e(url('admin_materias.php')) ?>">
        <?php csrfCampo(); ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?= e($materia['id']) ?>">

        <div class="campo <?= isset($errores['clave']) ? 'campo-error' : '' ?>">
            <label for="clave">Clave</label>
            <input id="clave" name="clave" type="text" value="<?= e($materia['clave']) ?>"
                maxlength="<?= e(LONGITUD_MAX_CLAVE) ?>" placeholder="Ej. MAT-110" required
                <?= isset($errores['clave']) ? 'aria-invalid="true" aria-describedby="error-clave"' : '' ?>>
            <?php if (isset($errores['clave'])): ?>
                <span class="mensaje-error" id="error-clave"><?= e($errores['clave']) ?></span>
            <?php endif; ?>
        </div>

        <div class="campo <?= isset($errores['nombre']) ? 'campo-error' : '' ?>">
            <label for="nombre">Nombre</label>
            <input id="nombre" name="nombre" type="text" value="<?= e($materia['nombre']) ?>"
                maxlength="<?= e(LONGITUD_MAX_NOMBRE) ?>" placeholder="Ej. Geometría Analítica" required
                <?= isset($errores['nombre']) ? 'aria-invalid="true" aria-describedby="error-nombre"' : '' ?>>
            <?php if (isset($errores['nombre'])): ?>
                <span class="mensaje-error" id="error-nombre"><?= e($errores['nombre']) ?></span>
            <?php endif; ?>
        </div>

        <div class="acciones">
            <button class="boton boton-destacado" type="submit"><?= e($editando ? 'Guardar cambios' : 'Crear materia') ?></button>
            <?php if ($editando): ?>
                <a class="boton boton-secundario" href="<?= e(url('admin_materias.php')) ?>">Cancelar</a>
            <?php endif; ?>
        </div>
    </form>
</section>
