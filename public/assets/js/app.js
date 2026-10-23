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
