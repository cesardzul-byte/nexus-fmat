<?php
/**
 * Pie común de todas las páginas: cierra el <main> que abrió el header,
 * muestra el pie de página e incluye app.js. Lo incluye vista() después de cada vista.
 */
?>
        </div>
    </main>

    <footer class="pie">
        <div class="contenedor">
            <p>Nexus FMAT · Asesorías entre estudiantes de la Facultad de Matemáticas, UADY</p>
        </div>
    </footer>

    <script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body>
</html>
