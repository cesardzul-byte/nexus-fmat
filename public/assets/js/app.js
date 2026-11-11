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

// ===== ISS-21 · Tabla semanal de horarios (P3) =====

// Texto y clase de app.css de cada estado que manda api/horarios.php
const ESTADOS_HORARIO = {
    disponible: { texto: 'Disponible', clase: 'celda-disponible' },
    reservado: { texto: 'Reservado', clase: 'celda-reservado' },
    ocupado: { texto: 'Ocupado', clase: 'celda-ocupado' },
    sin_bloque: { texto: 'Sin bloque', clase: 'celda-sin-bloque' },
};
// La API manda de lunes a sábado; el índice 0 es el lunes
const DIAS_CORTOS = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
const DIAS_EN_SEMANA = 7;
// getDay() de JavaScript cuenta desde el domingo (0); esto lo convierte a "días desde el lunes"
const AJUSTE_LUNES = 6;

/**
 * Convierte una fecha a 'AAAA-MM-DD' con la hora local (toISOString usaría UTC y podría cambiar el día).
 * @param {Date} fecha Fecha a convertir.
 * @returns {string} Fecha en formato AAAA-MM-DD.
 */
function fechaComoTexto(fecha) {
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const dia = String(fecha.getDate()).padStart(2, '0');
    return `${fecha.getFullYear()}-${mes}-${dia}`;
}

/**
 * Convierte 'AAAA-MM-DD' en una fecha local (new Date('AAAA-MM-DD') la tomaría en UTC).
 * @param {string} texto Fecha en formato AAAA-MM-DD.
 * @returns {Date} La fecha a medianoche, hora local.
 */
function textoComoFecha(texto) {
    const [anio, mes, dia] = texto.split('-').map(Number);
    return new Date(anio, mes - 1, dia);
}

/**
 * Suma (o resta, con un número negativo) días a una fecha.
 * @param {Date} fecha Fecha de partida.
 * @param {number} dias Días a sumar.
 * @returns {Date} Una fecha nueva; la original no cambia.
 */
function sumarDias(fecha, dias) {
    const resultado = new Date(fecha);
    resultado.setDate(resultado.getDate() + dias);
    return resultado;
}

/**
 * Devuelve el lunes de la semana de una fecha. Un domingo pertenece a la semana que termina.
 * @param {Date} fecha Cualquier día de la semana.
 * @returns {Date} El lunes de esa semana.
 */
function lunesDeLaSemana(fecha) {
    const diasDesdeLunes = (fecha.getDay() + AJUSTE_LUNES) % DIAS_EN_SEMANA;
    return sumarDias(fecha, -diasDesdeLunes);
}

/**
 * Crea la celda de un intervalo. Solo la disponible es un <button>, para que se pueda elegir
 * con clic o con teclado; las demás son texto. Todo se escribe con textContent, nunca innerHTML.
 * @param {{inicio: string, fin: string, estado: string}} intervalo Intervalo que manda la API.
 * @param {string} fecha Fecha del día en AAAA-MM-DD.
 * @returns {HTMLTableCellElement} La celda lista para la tabla.
 */
function crearCelda(intervalo, fecha) {
    const celda = document.createElement('td');
    const estado = ESTADOS_HORARIO[intervalo.estado] ?? ESTADOS_HORARIO.sin_bloque;
    const esDisponible = intervalo.estado === 'disponible';
    const contenido = document.createElement(esDisponible ? 'button' : 'span');

    contenido.className = estado.clase;
    contenido.textContent = estado.texto;
    if (esDisponible) {
        contenido.type = 'button';
        contenido.setAttribute('aria-label', `Disponible, ${fecha} de ${intervalo.inicio} a ${intervalo.fin}`);
        contenido.addEventListener('click', () => elegirIntervalo(fecha, intervalo));
    }
    celda.append(contenido);
    return celda;
}

/**
 * Arma la tabla de la semana: una columna por día y una fila por cada media hora.
 * @param {{dias: Array<{fecha: string, intervalos: Array<object>}>}} datos Respuesta de la API.
 * @returns {HTMLTableElement} La tabla completa.
 */
function armarTabla(datos) {
    const tabla = document.createElement('table');
    tabla.className = 'tabla-semanal';

    const filaEncabezado = tabla.createTHead().insertRow();
    const encabezadoHora = document.createElement('th');
    encabezadoHora.scope = 'col';
    encabezadoHora.textContent = 'Hora';
    filaEncabezado.append(encabezadoHora);
    datos.dias.forEach((dia, indice) => {
        const [, mes, numeroDia] = dia.fecha.split('-');
        const encabezado = document.createElement('th');
        encabezado.scope = 'col';
        encabezado.textContent = `${DIAS_CORTOS[indice]} ${numeroDia}/${mes}`;
        filaEncabezado.append(encabezado);
    });

    // Todos los días traen las mismas medias horas, así que la fila N usa el intervalo N de cada día
    const cuerpo = tabla.createTBody();
    datos.dias[0].intervalos.forEach((intervaloBase, posicion) => {
        const fila = cuerpo.insertRow();
        const encabezadoFila = document.createElement('th');
        encabezadoFila.scope = 'row';
        encabezadoFila.textContent = intervaloBase.inicio;
        fila.append(encabezadoFila);
        datos.dias.forEach((dia) => fila.append(crearCelda(dia.intervalos[posicion], dia.fecha)));
    });
    return tabla;
}

/**
 * Llena el formulario de agendar con el intervalo elegido y lleva el cursor al tema.
 * @param {string} fecha Fecha en AAAA-MM-DD.
 * @param {{inicio: string, fin: string}} intervalo Intervalo elegido (30 minutos).
 */
function elegirIntervalo(fecha, intervalo) {
    const formulario = document.querySelector('#fecha')?.form;
    if (!formulario) {
        return;
    }
    formulario.elements.fecha.value = fecha;
    formulario.elements.hora_inicio.value = intervalo.inicio;
    formulario.elements.hora_fin.value = intervalo.fin;
    formulario.scrollIntoView({ behavior: 'smooth', block: 'start' });
    formulario.elements.tema.focus({ preventScroll: true });
}

/**
 * Pide a la API los horarios de una semana y arma la tabla sin recargar la página.
 * Si la petición falla, muestra el aviso y el alumno puede usar el formulario a mano.
 * @param {HTMLElement} seccion Sección con data-asesor, data-materia y data-api.
 * @param {Date} lunes Lunes de la semana a mostrar.
 */
async function cargarSemana(seccion, lunes) {
    const contenedor = seccion.querySelector('[data-contenedor-tabla]');
    const aviso = seccion.querySelector('[data-error-horarios]');
    const titulo = seccion.querySelector('[data-titulo-semana]');
    const lunesTexto = fechaComoTexto(lunes);
    const sabadoTexto = fechaComoTexto(sumarDias(lunes, DIAS_CORTOS.length - 1));

    seccion.dataset.lunes = lunesTexto;
    titulo.textContent = `Semana del ${lunesTexto} al ${sabadoTexto}`;

    const parametros = new URLSearchParams({
        asesor: seccion.dataset.asesor,
        materia: seccion.dataset.materia,
        lunes: lunesTexto,
    });

    try {
        const respuesta = await fetch(`${seccion.dataset.api}?${parametros}`, {
            headers: { Accept: 'application/json' },
        });
        if (!respuesta.ok) {
            throw new Error(`La API respondió ${respuesta.status}`);
        }
        const datos = await respuesta.json();
        contenedor.replaceChildren(armarTabla(datos));
        aviso.hidden = true;
    } catch {
        // Se muestra el aviso en la página y no se deja el error sin manejar
        contenedor.replaceChildren();
        aviso.hidden = false;
    }
}

/**
 * Prepara la tabla semanal: la muestra (sin JavaScript queda oculta), conecta los botones
 * de semana anterior y siguiente, y carga la semana actual.
 * @param {HTMLElement} seccion Sección con data-tabla-horarios.
 */
function iniciarTablaHorarios(seccion) {
    seccion.hidden = false;
    const cambiarSemana = (dias) => cargarSemana(seccion, sumarDias(textoComoFecha(seccion.dataset.lunes), dias));
    seccion.querySelector('[data-semana-anterior]').addEventListener('click', () => cambiarSemana(-DIAS_EN_SEMANA));
    seccion.querySelector('[data-semana-siguiente]').addEventListener('click', () => cambiarSemana(DIAS_EN_SEMANA));
    cargarSemana(seccion, lunesDeLaSemana(new Date()));
}

const seccionHorarios = document.querySelector('[data-tabla-horarios]');
if (seccionHorarios) {
    iniciarTablaHorarios(seccionHorarios);
}
