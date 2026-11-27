<?php
/**
 * Vista de estadísticas (public/estadisticas.php, ISS-32).
 *
 * No recibe datos: la sección data-estadisticas le dice a app.js a qué API pedirlos, y app.js
 * llena las tarjetas, el ranking y la tabla. Sin JavaScript la sección queda oculta y se ve el aviso.
 */
?>
<section class="seccion">
    <h1>Estadísticas</h1>
    <p class="texto-suave">Demanda de asesorías por periodo. Cada sesión se cuenta por su fecha, en cualquier estado.</p>
</section>

<noscript>
    <div class="alerta alerta-info" role="status">Activa JavaScript para ver las estadísticas.</div>
</noscript>

<div data-estadisticas data-api="<?= e(url('api/estadisticas.php')) ?>" hidden>
    <section class="seccion">
        <h2>Periodo</h2>
        <form class="formulario filtro-fechas" data-filtro-estadisticas>
            <div class="campo">
                <label for="desde">Desde</label>
                <input id="desde" name="desde" type="date">
            </div>
            <div class="campo">
                <label for="hasta">Hasta</label>
                <input id="hasta" name="hasta" type="date">
            </div>
            <div class="acciones">
                <button class="boton boton-destacado" type="submit">Aplicar</button>
                <button class="boton boton-secundario" type="reset">Ver todo el historial</button>
            </div>
        </form>
        <p class="periodo-elegido" data-periodo aria-live="polite"></p>
    </section>

    <div class="alerta alerta-error" role="alert" data-error-estadisticas hidden></div>

    <div data-resultados hidden>
        <section class="seccion">
            <h2>Resumen</h2>
            <div class="rejilla-estadisticas">
                <article class="tarjeta tarjeta-estadistica">
                    <p class="estadistica-numero" data-indicador="solicitadas"></p>
                    <p class="estadistica-etiqueta">Solicitadas</p>
                </article>
                <article class="tarjeta tarjeta-estadistica">
                    <p class="estadistica-numero" data-indicador="completadas"></p>
                    <p class="estadistica-etiqueta">Completadas</p>
                </article>
                <article class="tarjeta tarjeta-estadistica">
                    <p class="estadistica-numero" data-indicador="canceladas"></p>
                    <p class="estadistica-etiqueta">Canceladas</p>
                </article>
                <article class="tarjeta tarjeta-estadistica">
                    <p class="estadistica-numero" data-indicador="promedio"></p>
                    <p class="estadistica-etiqueta" data-indicador="resenas"></p>
                </article>
            </div>
        </section>

        <section class="seccion">
            <h2>Materias más solicitadas</h2>
            <ol class="ranking-materias" data-ranking></ol>
            <p class="texto-suave" data-sin-solicitudes hidden>No hay solicitudes en este periodo.</p>
        </section>

        <section class="seccion">
            <h2>Asesorías por materia</h2>
            <div class="tabla-envoltura">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th scope="col">Clave</th>
                            <th scope="col">Materia</th>
                            <th scope="col">Solicitadas</th>
                        </tr>
                    </thead>
                    <tbody data-tabla-materias></tbody>
                </table>
            </div>
        </section>
    </div>
</div>
