/*
 * app.js — JavaScript común de Nexus FMAT.
 *
 * Por ahora solo pide confirmación antes de enviar un formulario cuando
 * el botón que lo envía tiene data-confirmar="mensaje".
 *
 * Dueña: P3 (@Pao-Parra). Quien agregue funciones lo hace al final,
 * en su propia sección comentada.
 */

/**
 * Pide confirmación antes de enviar un formulario si el botón que lo
 * envió tiene el atributo data-confirmar. Si la persona cancela, el
 * formulario no se envía.
 * @param {SubmitEvent} evento Evento de envío del formulario.
 */
function confirmarEnvio(evento) {
    const boton = evento.submitter;
    if (!boton || !boton.dataset.confirmar) {
        return;
    }
    if (!window.confirm(boton.dataset.confirmar)) {
        evento.preventDefault();
    }
}

// Se escucha en todo el documento para que funcione con cualquier formulario
document.addEventListener('submit', confirmarEnvio);

// ===== ISS-09 · Filtro de materias en el inicio (P3) =====

/**
 * Pasa un texto a minúsculas y le quita los acentos, para que buscar
 * "algebra" encuentre "Álgebra".
 * @param {string} texto Texto original.
 * @returns {string} Texto normalizado.
 */
function normalizarTexto(texto) {
    return texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
}

/**
 * Muestra solo las tarjetas de materia cuyo nombre contiene lo que se
 * escribió en el buscador. Si ninguna coincide, muestra el aviso de
 * "sin resultados". No recarga la página.
 * @param {InputEvent} evento Evento de escritura en el buscador.
 */
function filtrarTarjetas(evento) {
    const busqueda = normalizarTexto(evento.target.value);
    const tarjetas = document.querySelectorAll('[data-tarjeta-materia]');
    let visibles = 0;

    tarjetas.forEach((tarjeta) => {
        const coincide = normalizarTexto(tarjeta.dataset.nombre).includes(busqueda);
        tarjeta.hidden = !coincide;
        if (coincide) {
            visibles += 1;
        }
    });

    const avisoSinResultados = document.querySelector('[data-sin-resultados]');
    if (avisoSinResultados) {
        avisoSinResultados.hidden = visibles > 0;
    }
}

const buscadorMaterias = document.querySelector('[data-filtro-materias]');
if (buscadorMaterias) {
    buscadorMaterias.addEventListener('input', filtrarTarjetas);
}

// ===== ISS-08 · Registro de alumnos y asesores (P2) =====

/**
 * Marca el campo de confirmación como inválido si no coincide con la contraseña.
 * Es solo una ayuda en el navegador: la validación que manda es la del servidor.
 * @returns {boolean} true si coinciden (o si la página no tiene esos campos).
 */
function validarConfirmacionContrasena() {
    const contrasena = document.querySelector('#contrasena');
    const confirmacion = document.querySelector('#confirmar_contrasena');

    if (!contrasena || !confirmacion) {
        return true;
    }

    if (confirmacion.value !== '' && confirmacion.value !== contrasena.value) {
        confirmacion.setCustomValidity('Las contraseñas no coinciden.');
        return false;
    }

    confirmacion.setCustomValidity('');
    return true;
}

const formularioRegistro = document.querySelector('#formulario-registro');
if (formularioRegistro) {
    const contrasenaRegistro = formularioRegistro.querySelector('#contrasena');
    const confirmacionRegistro = formularioRegistro.querySelector('#confirmar_contrasena');

    if (contrasenaRegistro && confirmacionRegistro) {
        contrasenaRegistro.addEventListener('input', validarConfirmacionContrasena);
        confirmacionRegistro.addEventListener('input', validarConfirmacionContrasena);

        formularioRegistro.addEventListener('submit', (evento) => {
            if (!validarConfirmacionContrasena()) {
                evento.preventDefault();
                confirmacionRegistro.reportValidity();
            }
        });
    }
}
