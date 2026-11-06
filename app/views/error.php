<?php
/**
 * Vista mínima de error. La muestra abortar() con $codigo y $mensaje.
 */
?>
<section>
    <h1>Error <?= e($codigo) ?></h1>
    <p><?= e($mensaje) ?></p>
    <p><a href="<?= e(url()) ?>">Volver al inicio</a></p>
</section>
