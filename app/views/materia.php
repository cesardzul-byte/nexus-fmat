<?php
/**
 * Vista del detalle de materia (public/materia.php).
 *
 * Recibe:
 * - $materia (array): id, clave y nombre de la materia.
 * - $asesores (array): asesores con nombre, apellidos, licenciatura y bloques;
 *   cada bloque ya trae 'dia', 'horario' y 'lugar' listos para mostrar.
 */
?>
<p><a class="enlace-volver" href="<?= e(url()) ?>">← Todas las materias</a></p>

<section class="seccion">
    <span class="etiqueta etiqueta-neutra"><?= e($materia['clave']) ?></span>
    <h1 class="materia-titulo"><?= e($materia['nombre']) ?></h1>
    <p class="texto-suave">Asesores que imparten esta materia y los horarios en que pueden ayudarte.</p>
</section>

<?php if ($asesores === []): ?>
    <div class="alerta alerta-info" role="status">
        Esta materia todavía no tiene asesores. Vuelve pronto.
    </div>
<?php else: ?>
    <div class="lista-asesores">
        <?php foreach ($asesores as $asesor): ?>
            <article class="tarjeta">
                <h2 class="tarjeta-titulo"><?= e($asesor['nombre'] . ' ' . $asesor['apellidos']) ?></h2>
                <p class="texto-suave"><?= e($asesor['licenciatura'] ?? 'Licenciatura no registrada') ?></p>

                <?php if ($asesor['bloques'] === []): ?>
                    <p class="texto-suave">Aún no publica horarios para esta materia.</p>
                <?php else: ?>
                    <div class="tabla-envoltura">
                        <table class="tabla">
                            <caption class="solo-lectores">Horarios de <?= e($asesor['nombre']) ?></caption>
                            <thead>
                                <tr>
                                    <th scope="col">Día</th>
                                    <th scope="col">Horario</th>
                                    <th scope="col">Lugar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($asesor['bloques'] as $bloque): ?>
                                    <tr>
                                        <td><?= e($bloque['dia']) ?></td>
                                        <td><?= e($bloque['horario']) ?></td>
                                        <td><?= e($bloque['lugar']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
