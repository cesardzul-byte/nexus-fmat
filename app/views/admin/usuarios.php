<?php
/**
 * Vista de administración de cuentas (public/admin_usuarios.php, ISS-23).
 *
 * Recibe:
 * - $usuarios: cuentas filtradas con nombre, correo, matrícula, rol, licenciatura, semestre y estado.
 * - $roles: etiquetas de los roles permitidos para el filtro.
 * - $rolFiltro: rol seleccionado o null para mostrar todas las cuentas.
 * - $licenciaturas: catálogo para el formulario de edición.
 * - $usuarioEdicion: cuenta seleccionada; null cuando no se edita ninguna.
 * - $errores: mensajes de validación por campo.
 * - $rutaListado: URL del listado, conservando el filtro actual.
 */
$editando = is_array($usuarioEdicion);
?>
<section class="seccion">
    <h1>Usuarios</h1>
    <p class="texto-suave">Consulta las cuentas registradas y administra el acceso de alumnos y asesores. Las cuentas de administrador se muestran protegidas.</p>
</section>

<section class="seccion">
    <h2>Filtrar cuentas</h2>
    <form class="formulario" method="get" action="<?= e(url('admin_usuarios.php')) ?>">
        <div class="campo">
            <label for="rol">Rol</label>
            <select id="rol" name="rol">
                <option value="" <?= $rolFiltro === null ? 'selected' : '' ?>>Todos los roles</option>
                <?php foreach ($roles as $claveRol => $nombreRol): ?>
                    <option value="<?= e($claveRol) ?>" <?= $rolFiltro === $claveRol ? 'selected' : '' ?>>
                        <?= e($nombreRol) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="acciones">
            <button class="boton boton-destacado" type="submit">Filtrar</button>
            <a class="boton boton-secundario" href="<?= e(url('admin_usuarios.php')) ?>">Quitar filtro</a>
        </div>
    </form>
</section>

<section class="seccion">
    <h2>Listado de cuentas</h2>
    <?php if ($usuarios === []): ?>
        <div class="alerta alerta-info" role="status">No hay cuentas para el filtro seleccionado.</div>
    <?php else: ?>
        <div class="tabla-envoltura">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">Correo</th>
                        <th scope="col">Matrícula</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Licenciatura</th>
                        <th scope="col">Semestre</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $fila): ?>
                        <?php
                        $esAdmin = $fila['rol'] === 'admin';
                        $estaActivo = (int) $fila['activo'] === 1;
                        $nombreCompleto = trim((string) $fila['nombre'] . ' ' . (string) $fila['apellidos']);
                        $parametrosEdicion = ['editar' => (int) $fila['id']];
                        if ($rolFiltro !== null) {
                            $parametrosEdicion['rol'] = $rolFiltro;
                        }
                        ?>
                        <tr>
                            <td><?= e($nombreCompleto) ?></td>
                            <td><?= e($fila['correo']) ?></td>
                            <td><?= e($fila['matricula'] ?? '—') ?></td>
                            <td><?= e($roles[$fila['rol']] ?? 'Desconocido') ?></td>
                            <td><?= e($fila['licenciatura'] ?? 'No aplica') ?></td>
                            <td><?= $fila['semestre'] === null ? '—' : e($fila['semestre']) ?></td>
                            <td>
                                <span class="etiqueta <?= $estaActivo ? 'etiqueta-completada' : 'etiqueta-cancelada' ?>">
                                    <?= $estaActivo ? 'Activa' : 'Inactiva' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($esAdmin): ?>
                                    <span class="texto-suave">Cuenta protegida</span>
                                <?php else: ?>
                                    <div class="acciones">
                                        <a class="boton boton-secundario" href="<?= e(url('admin_usuarios.php?' . http_build_query($parametrosEdicion) . '#formulario-usuario')) ?>">Editar</a>
                                        <form method="post" action="<?= e(url($rutaListado)) ?>">
                                            <?php csrfCampo(); ?>
                                            <input type="hidden" name="id" value="<?= e($fila['id']) ?>">
                                            <?php if ($estaActivo): ?>
                                                <input type="hidden" name="accion" value="desactivar">
                                                <button class="boton boton-peligro" type="submit"
                                                    data-confirmar="¿Desactivar la cuenta de <?= e($nombreCompleto) ?>? Sus sesiones se conservarán y su acceso terminará en la siguiente petición.">
                                                    Desactivar
                                                </button>
                                            <?php else: ?>
                                                <input type="hidden" name="accion" value="activar">
                                                <button class="boton" type="submit"
                                                    data-confirmar="¿Activar nuevamente la cuenta de <?= e($nombreCompleto) ?>?">
                                                    Activar
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php if ($editando): ?>
    <section class="seccion" id="formulario-usuario">
        <h2>Editar cuenta</h2>
        <p class="texto-suave">
            <?= e($usuarioEdicion['correo']) ?> · <?= e($roles[$usuarioEdicion['rol']] ?? '') ?> · Matrícula: <?= e($usuarioEdicion['matricula']) ?>
        </p>
        <p class="texto-suave">El correo, la matrícula y el rol no se pueden modificar.</p>

        <form class="formulario" method="post" action="<?= e(url($rutaListado)) ?>">
            <?php csrfCampo(); ?>
            <input type="hidden" name="accion" value="guardar">
            <input type="hidden" name="id" value="<?= e($usuarioEdicion['id']) ?>">

            <div class="campo <?= isset($errores['nombre']) ? 'campo-error' : '' ?>">
                <label for="nombre">Nombre(s)</label>
                <input id="nombre" name="nombre" type="text" maxlength="<?= Usuario::LONGITUD_MAXIMA_NOMBRE ?>"
                    value="<?= e($usuarioEdicion['nombre']) ?>" required
                    <?= isset($errores['nombre']) ? 'aria-invalid="true" aria-describedby="error-nombre"' : '' ?>>
                <?php if (isset($errores['nombre'])): ?>
                    <span class="mensaje-error" id="error-nombre"><?= e($errores['nombre']) ?></span>
                <?php endif; ?>
            </div>

            <div class="campo <?= isset($errores['apellidos']) ? 'campo-error' : '' ?>">
                <label for="apellidos">Apellidos</label>
                <input id="apellidos" name="apellidos" type="text" maxlength="<?= Usuario::LONGITUD_MAXIMA_APELLIDOS ?>"
                    value="<?= e($usuarioEdicion['apellidos']) ?>" required
                    <?= isset($errores['apellidos']) ? 'aria-invalid="true" aria-describedby="error-apellidos"' : '' ?>>
                <?php if (isset($errores['apellidos'])): ?>
                    <span class="mensaje-error" id="error-apellidos"><?= e($errores['apellidos']) ?></span>
                <?php endif; ?>
            </div>

            <div class="campo <?= isset($errores['licenciatura_id']) ? 'campo-error' : '' ?>">
                <label for="licenciatura_id">Licenciatura</label>
                <select id="licenciatura_id" name="licenciatura_id" required
                    <?= isset($errores['licenciatura_id']) ? 'aria-invalid="true" aria-describedby="error-licenciatura_id"' : '' ?>>
                    <option value="">Selecciona una licenciatura</option>
                    <?php foreach ($licenciaturas as $licenciatura): ?>
                        <?php $idLicenciatura = (string) (int) $licenciatura['id']; ?>
                        <option value="<?= e($idLicenciatura) ?>"
                            <?= (string) $usuarioEdicion['licenciatura_id'] === $idLicenciatura ? 'selected' : '' ?>>
                            <?= e($licenciatura['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errores['licenciatura_id'])): ?>
                    <span class="mensaje-error" id="error-licenciatura_id"><?= e($errores['licenciatura_id']) ?></span>
                <?php endif; ?>
            </div>

            <div class="campo <?= isset($errores['semestre']) ? 'campo-error' : '' ?>">
                <label for="semestre">Semestre</label>
                <select id="semestre" name="semestre" required
                    <?= isset($errores['semestre']) ? 'aria-invalid="true" aria-describedby="error-semestre"' : '' ?>>
                    <option value="">Selecciona un semestre</option>
                    <?php for ($semestre = 1; $semestre <= Usuario::SEMESTRE_MAXIMO; $semestre++): ?>
                        <option value="<?= $semestre ?>" <?= (string) $usuarioEdicion['semestre'] === (string) $semestre ? 'selected' : '' ?>>
                            <?= $semestre ?><?= $semestre === 1 ? 'er' : 'º' ?> semestre
                        </option>
                    <?php endfor; ?>
                </select>
                <?php if (isset($errores['semestre'])): ?>
                    <span class="mensaje-error" id="error-semestre"><?= e($errores['semestre']) ?></span>
                <?php endif; ?>
            </div>

            <div class="acciones">
                <button class="boton boton-destacado" type="submit">Guardar cambios</button>
                <a class="boton boton-secundario" href="<?= e(url($rutaListado)) ?>">Cancelar</a>
            </div>
        </form>
    </section>
<?php endif; ?>
