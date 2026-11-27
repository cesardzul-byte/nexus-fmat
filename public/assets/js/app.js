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

// ===== ISS-32 · Indicadores de estadísticas (P3) =====

// Cuántas materias entran en el ranking de "más solicitadas"; la tabla muestra todas
const LUGARES_RANKING = 3;
// Ancho de la barra de la materia más solicitada; las demás se miden contra ella
const PORCENTAJE_COMPLETO = 100;
const MENSAJE_ERROR_ESTADISTICAS = 'No se pudieron cargar las estadísticas. Intenta de nuevo en un momento.';

/**
 * Escribe una cantidad con su palabra en singular o plural, por ejemplo "1 reseña" o "3 reseñas".
 * @param {number} cantidad Número a mostrar.
 * @param {string} singular Palabra para 1.
 * @param {string} plural Palabra para 0 o más de 1.
 * @returns {string} El texto listo para mostrar.
 */
function contarCon(cantidad, singular, plural) {
    return `${cantidad} ${cantidad === 1 ? singular : plural}`;
}

/**
 * Convierte 'AAAA-MM-DD' en 'DD/MM/AAAA', como se muestran las fechas en el resto de la app.
 * @param {string} texto Fecha en formato AAAA-MM-DD.
 * @returns {string} Fecha en formato DD/MM/AAAA.
 */
function fechaParaMostrar(texto) {
    const [anio, mes, dia] = texto.split('-');
    return `${dia}/${mes}/${anio}`;
}

/**
 * Describe el periodo elegido. Sin fechas es todo el historial; con una sola, el otro extremo queda abierto.
 * @param {string} desde Fecha AAAA-MM-DD o cadena vacía.
 * @param {string} hasta Fecha AAAA-MM-DD o cadena vacía.
 * @returns {string} Texto del periodo.
 */
function textoPeriodo(desde, hasta) {
    if (desde && hasta) {
        return `Del ${fechaParaMostrar(desde)} al ${fechaParaMostrar(hasta)}`;
    }
    if (desde) {
        return `Desde el ${fechaParaMostrar(desde)}`;
    }
    if (hasta) {
        return `Hasta el ${fechaParaMostrar(hasta)}`;
    }
    return 'Todo el historial';
}

/**
 * Crea la barra de una materia. Es solo visual (aria-hidden): el número va escrito junto a ella.
 * @param {number} solicitadas Solicitudes de la materia.
 * @param {number} maximo Solicitudes de la materia más solicitada (al menos 1).
 * @returns {HTMLSpanElement} La barra con su relleno proporcional.
 */
function crearBarra(solicitadas, maximo) {
    const barra = document.createElement('span');
    const relleno = document.createElement('span');
    barra.className = 'barra';
    barra.setAttribute('aria-hidden', 'true');
    relleno.className = 'barra-relleno';
    relleno.style.width = `${(solicitadas / maximo) * PORCENTAJE_COMPLETO}%`;
    barra.append(relleno);
    return barra;
}

/**
 * Llena las cuatro tarjetas: solicitadas, completadas, canceladas y el promedio con su número de reseñas.
 * @param {HTMLElement} seccion Contenedor data-estadisticas.
 * @param {object} datos Respuesta de api/estadisticas.php.
 */
function mostrarResumen(seccion, datos) {
    ['solicitadas', 'completadas', 'canceladas'].forEach((indicador) => {
        seccion.querySelector(`[data-indicador="${indicador}"]`).textContent = datos[indicador];
    });

    const promedio = seccion.querySelector('[data-indicador="promedio"]');
    const resenas = seccion.querySelector('[data-indicador="resenas"]');
    const sinResenas = datos.resenas.promedio === null;
    // Sin reseñas no hay promedio que mostrar: se dice con texto en lugar de un 0 engañoso
    promedio.textContent = sinResenas ? 'Sin reseñas' : `★ ${datos.resenas.promedio.toFixed(1)}`;
    promedio.classList.toggle('estadistica-vacia', sinResenas);
    resenas.textContent = `Promedio · ${contarCon(datos.resenas.total, 'reseña', 'reseñas')}`;
}

/**
 * Llena el ranking (las primeras materias) y la tabla con todas. La API ya las manda
 * de más a menos solicitadas, así que la primera es la que marca el 100 % de las barras.
 * @param {HTMLElement} seccion Contenedor data-estadisticas.
 * @param {Array<{clave: string, nombre: string, solicitadas: number}>} materias Desglose por materia.
 */
function mostrarMaterias(seccion, materias) {
    const ranking = seccion.querySelector('[data-ranking]');
    const cuerpo = seccion.querySelector('[data-tabla-materias]');
    const hayMaterias = materias.length > 0;
    const maximo = hayMaterias ? materias[0].solicitadas : 0;

    ranking.hidden = !hayMaterias;
    seccion.querySelector('[data-sin-solicitudes]').hidden = hayMaterias;
    ranking.replaceChildren(...materias.slice(0, LUGARES_RANKING).map((materia) => {
        const lugar = document.createElement('li');
        const nombre = document.createElement('span');
        const cantidad = document.createElement('span');
        nombre.className = 'ranking-nombre';
        nombre.textContent = materia.nombre;
        cantidad.className = 'texto-suave';
        cantidad.textContent = contarCon(materia.solicitadas, 'solicitud', 'solicitudes');
        lugar.append(nombre, cantidad, crearBarra(materia.solicitadas, maximo));
        return lugar;
    }));

    if (!hayMaterias) {
        // Un periodo sin sesiones deja una fila que lo dice, en lugar de una tabla vacía
        const fila = document.createElement('tr');
        const celda = document.createElement('td');
        celda.colSpan = 3;
        celda.className = 'texto-suave';
        celda.textContent = 'No hay solicitudes en este periodo.';
        fila.append(celda);
        cuerpo.replaceChildren(fila);
        return;
    }

    cuerpo.replaceChildren(...materias.map((materia) => {
        const fila = document.createElement('tr');
        const clave = document.createElement('td');
        const nombre = document.createElement('td');
        const demanda = document.createElement('td');
        const contenido = document.createElement('div');
        const numero = document.createElement('span');
        clave.textContent = materia.clave;
        nombre.textContent = materia.nombre;
        contenido.className = 'demanda';
        numero.textContent = materia.solicitadas;
        contenido.append(crearBarra(materia.solicitadas, maximo), numero);
        demanda.append(contenido);
        fila.append(clave, nombre, demanda);
        return fila;
    }));
}

/**
 * Pide los indicadores del periodo del filtro y los muestra sin recargar la página.
 * Si la API responde con error, se ve su mensaje (por ejemplo, un rango invertido); si falla
 * la conexión o la respuesta no es JSON, un mensaje general. En ambos casos se ocultan los números viejos.
 * @param {HTMLElement} seccion Contenedor data-estadisticas con data-api.
 */
async function cargarEstadisticas(seccion) {
    const formulario = seccion.querySelector('[data-filtro-estadisticas]');
    const resultados = seccion.querySelector('[data-resultados]');
    const aviso = seccion.querySelector('[data-error-estadisticas]');
    const desde = formulario.elements.desde.value;
    const hasta = formulario.elements.hasta.value;

    // Un extremo vacío no se manda, para que la API lo deje abierto
    const parametros = new URLSearchParams();
    if (desde) {
        parametros.set('desde', desde);
    }
    if (hasta) {
        parametros.set('hasta', hasta);
    }
    seccion.querySelector('[data-periodo]').textContent = textoPeriodo(desde, hasta);
    seccion.setAttribute('aria-busy', 'true');

    let mensaje = MENSAJE_ERROR_ESTADISTICAS;
    try {
        const respuesta = await fetch(`${seccion.dataset.api}?${parametros}`, {
            headers: { Accept: 'application/json' },
        });
        const datos = await respuesta.json();
        if (!respuesta.ok) {
            if (typeof datos.error === 'string') {
                mensaje = datos.error;
            }
            throw new Error(`La API respondió ${respuesta.status}`);
        }
        mostrarResumen(seccion, datos);
        mostrarMaterias(seccion, datos.por_materia);
        resultados.hidden = false;
        aviso.hidden = true;
    } catch {
        // Se muestra el aviso en la página y no se deja el error sin manejar
        resultados.hidden = true;
        aviso.textContent = mensaje;
        aviso.hidden = false;
    } finally {
        seccion.removeAttribute('aria-busy');
    }
}

/**
 * Prepara la pantalla de estadísticas: la muestra (sin JavaScript queda oculta), conecta
 * el filtro y carga el historial completo.
 * @param {HTMLElement} seccion Contenedor data-estadisticas.
 */
function iniciarEstadisticas(seccion) {
    const formulario = seccion.querySelector('[data-filtro-estadisticas]');
    seccion.hidden = false;
    formulario.addEventListener('submit', (evento) => {
        evento.preventDefault();
        cargarEstadisticas(seccion);
    });
    // "Ver todo el historial" vacía las fechas y vuelve a pedir los datos
    formulario.addEventListener('reset', (evento) => {
        evento.preventDefault();
        formulario.elements.desde.value = '';
        formulario.elements.hasta.value = '';
        cargarEstadisticas(seccion);
    });
    cargarEstadisticas(seccion);
}

const seccionEstadisticas = document.querySelector('[data-estadisticas]');
if (seccionEstadisticas) {
    iniciarEstadisticas(seccionEstadisticas);
}
