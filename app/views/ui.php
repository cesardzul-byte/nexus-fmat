<?php
/**
 * Vista de la guía de componentes (public/ui.php). Solo muestra HTML de ejemplo
 * con las clases de app.css. Se borra junto con public/ui.php antes de la entrega final.
 *
 * Recibe $simulaSesion (bool): true si la navegación se está viendo como usuario con sesión.
 */
?>
<section class="seccion">
    <h1>Guía de componentes</h1>
    <p class="texto-suave">Copia las clases de esta página para que todas las vistas se vean igual.</p>
    <div class="acciones">
        <?php if ($simulaSesion): ?>
            <a class="boton boton-secundario" href="<?= e(url('ui.php')) ?>">Ver como visitante</a>
        <?php else: ?>
            <a class="boton boton-secundario" href="<?= e(url('ui.php?sesion=1')) ?>">Ver como usuario con sesión</a>
        <?php endif; ?>
    </div>
</section>

<section class="seccion">
    <h2>Colores</h2>
    <div class="muestras-color">
        <div class="muestra-color muestra-tinta">Tinta<br>--color-tinta</div>
        <div class="muestra-color muestra-primario">Primario<br>--color-primario</div>
        <div class="muestra-color muestra-destacado">Destacado<br>--color-destacado</div>
        <div class="muestra-color muestra-progreso">Éxito<br>--color-exito</div>
        <div class="muestra-color muestra-suave">Suave<br>--color-primario-suave</div>
    </div>
</section>

<section class="seccion">
    <h2>Botones</h2>
    <p class="texto-suave"><code>.boton-destacado</code> es solo para la acción principal de cada pantalla.</p>
    <div class="acciones">
        <button class="boton boton-destacado" type="button">Solicitar asesoría</button>
        <button class="boton" type="button">Guardar</button>
        <button class="boton boton-secundario" type="button">Cancelar</button>
        <button class="boton boton-peligro" type="button">Eliminar</button>
        <button class="boton" type="button" disabled>Deshabilitado</button>
    </div>
</section>

<section class="seccion">
    <h2>Alertas</h2>
    <div class="alerta alerta-exito" role="status">Tu sesión quedó agendada.</div>
    <div class="alerta alerta-error" role="alert">Ese horario se empalma con otro bloque.</div>
    <div class="alerta alerta-info" role="status">Las reseñas se habilitan cuando la sesión termina.</div>
</section>

<section class="seccion">
    <h2>Formulario con error</h2>
    <form class="formulario" method="post" action="#">
        <div class="campo">
            <label for="ui-nombre">Nombre</label>
            <input id="ui-nombre" name="nombre" type="text" value="Ana" required>
        </div>
        <div class="campo campo-error">
            <label for="ui-correo">Correo institucional</label>
            <input id="ui-correo" name="correo" type="email" value="ana@gmail.com" aria-describedby="ui-correo-error" aria-invalid="true" required>
            <span class="mensaje-error" id="ui-correo-error">Usa tu correo @alumnos.uady.mx</span>
        </div>
        <div class="campo">
            <label for="ui-materia">Materia</label>
            <select id="ui-materia" name="materia">
                <option>Álgebra Lineal</option>
                <option>Cálculo Diferencial</option>
            </select>
            <span class="campo-ayuda">Solo aparecen las materias activas.</span>
        </div>
        <div class="acciones">
            <button class="boton" type="submit" data-confirmar="¿Enviar el formulario de prueba?">Enviar</button>
            <button class="boton boton-secundario" type="reset">Limpiar</button>
        </div>
    </form>
</section>

<section class="seccion">
    <h2>Tabla</h2>
    <div class="tabla-envoltura">
        <table class="tabla">
            <thead>
                <tr>
                    <th scope="col">Día</th>
                    <th scope="col">Horario</th>
                    <th scope="col">Materia</th>
                    <th scope="col">Lugar</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Lunes</td>
                    <td>10:00 – 11:00</td>
                    <td>Álgebra Lineal</td>
                    <td>Biblioteca</td>
                </tr>
                <tr>
                    <td>Miércoles</td>
                    <td>16:00 – 17:30</td>
                    <td>Programación Estructurada</td>
                    <td>Salón C-4</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="seccion">
    <h2>Tarjetas</h2>
    <div class="rejilla-tarjetas">
        <article class="tarjeta">
            <span class="etiqueta">3 asesores</span>
            <h3 class="tarjeta-titulo">Álgebra Lineal</h3>
            <p class="texto-suave">Matrices, vectores y sistemas de ecuaciones.</p>
            <a class="boton boton-destacado" href="#">Ver horarios</a>
        </article>
        <article class="tarjeta">
            <span class="etiqueta">1 asesor</span>
            <h3 class="tarjeta-titulo">Cálculo Diferencial</h3>
            <p class="texto-suave">Límites, derivadas y sus aplicaciones.</p>
            <a class="boton boton-secundario" href="#">Ver horarios</a>
        </article>
    </div>
</section>
