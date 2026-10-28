/*
 * app.js — JavaScript común de Nexus FMAT.
 * Mantiene las funciones existentes de formularios e ISS-09.
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

document.addEventListener('submit', confirmarEnvio);

// ===== ISS-09 · Filtro de materias en el inicio (P3) =====

/**
 * Pasa un texto a minúsculas y le quita los acentos.
 * @param {string} texto Texto original.
 * @returns {string} Texto normalizado.
 */
function normalizarTexto(texto) {
    return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
}

/**
 * Filtra las tarjetas de materias por el texto escrito en el buscador.
 * @param {InputEvent} evento Evento de escritura en el buscador.
 */
function filtrarTarjetas(evento) {
    const busqueda = normalizarTexto(evento.target.value);
    const tarjetas = document.querySelectorAll('[data-tarjeta-materia]');
    let visibles = 0;

    tarjetas.forEach((tarjeta) => {
        const coincide = normalizarTexto(tarjeta.dataset.nombre || '').includes(busqueda);
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

// ===== ISS-08 · Registro unificado =====

/** Comprueba que la confirmación coincida con la contraseña. */
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

/**
 * Muestra u oculta los datos académicos según el tipo de cuenta.
 * La opción admin solo se presenta a una sesión administrativa activa y
 * el servidor vuelve a comprobar el permiso antes de crear la cuenta.
 */
function actualizarCamposRegistro() {
    const rol = document.querySelector('#rol');
    const grupo = document.querySelector('#datos-academicos');

    if (!rol || !grupo) {
        return;
    }

    const puedeCrearAdmin = rol.dataset.permiteAdmin === 'true';
    const esAdmin = puedeCrearAdmin && rol.value === 'admin';
    grupo.hidden = esAdmin;

    grupo.querySelectorAll('input, select').forEach((campo) => {
        campo.disabled = esAdmin;
        campo.required = !esAdmin;
    });
}

const formularioRegistro = document.querySelector('#formulario-registro');
if (formularioRegistro) {
    const rolRegistro = formularioRegistro.querySelector('#rol');
    const contrasenaRegistro = formularioRegistro.querySelector('#contrasena');
    const confirmacionRegistro = formularioRegistro.querySelector('#confirmar_contrasena');

    if (rolRegistro) {
        rolRegistro.addEventListener('change', actualizarCamposRegistro);
        actualizarCamposRegistro();
    }

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
