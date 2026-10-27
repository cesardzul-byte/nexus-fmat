<?php
/**
 * Encabezado común de todas las páginas: <head>, logo, navegación según la
 * sesión, apertura del <main> y mensajes flash. Lo incluye vista() antes de cada vista.
 *
 * Recibe $titulo (opcional) desde los datos de vista().
 */
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($titulo) ? e($titulo) . ' · ' : '' ?>Nexus FMAT</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/app.css')) ?>">
</head>
<body>
    <header class="encabezado">
        <div class="contenedor">
            <a class="marca" href="<?= e(url()) ?>">
                <img src="<?= e(url('assets/img/logo.svg')) ?>" alt="Nexus FMAT, ir al inicio">
            </a>

            <nav class="nav-escritorio" aria-label="Navegación principal">
                <?php require __DIR__ . '/nav.php'; ?>
            </nav>

            <details class="menu-movil">
                <summary>Menú</summary>
                <nav aria-label="Navegación principal">
                    <?php require __DIR__ . '/nav.php'; ?>
                </nav>
            </details>
        </div>
    </header>

    <main class="contenido">
        <div class="contenedor">
            <?php foreach (obtenerFlash() as $aviso): ?>
                <div class="alerta alerta-<?= e($aviso['tipo']) ?>" role="status">
                    <?= e($aviso['mensaje']) ?>
                </div>
            <?php endforeach; ?>
