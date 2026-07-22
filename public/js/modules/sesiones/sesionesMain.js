/**
 * @file    sesionesMain.js
 * @module  modules/sesiones/main
 *
 * Punto de entrada del módulo de sesiones para la vista de detalle de un contrato
 * (`/contratos/:id/sesiones`). Orquesta la carga de datos por promoción, los modales
 * de CRUD de sesiones y el offcanvas de gestión de asistencia.
 *
 * Dependencias de variables globales inyectadas por PHP:
 *  - `window.ID_CONTRATO`    — ID del contrato activo (entero).
 *  - `window.PROMOCIONES`    — Array de promociones del contrato.
 *  - `window.CONFIG_SESIONES`— Array de configuración de tipos de sesión por paquete.
 *
 * Flujo de inicialización (`init`):
 *  1. Asigna `ID_CONTRATO` y `PROMOCIONES` al estado compartido.
 *  2. Inicializa los modales Bootstrap (`#modalSesion`, `#modalEstudiante`)
 *     y el offcanvas Bootstrap (`#offcanvasAsistencia`).
 *  3. Registra el listener de pestañas (`#promocionesTabs`).
 *  4. Carga la primera promoción si existe; muestra estado vacío si no.
 *
 * Funciones globales expuestas en `window.*`:
 *  - {@link window.abrirNuevaSesion}
 *  - {@link window.abrirEditarSesion}
 *  - {@link window.guardarSesion}
 *  - {@link window.cambiarEstadoSesion}
 *  - {@link window.abrirAsistencia}
 *  - {@link window.agregarAAsistencia}
 *  - {@link window.quitarDeAsistencia}
 *  - {@link window.marcarAsistencia}
 *  - {@link window.abrirNuevoEstudiante}
 *  - {@link window.guardarEstudiante}
 *  - {@link window.eliminarEstudiante}
 */

import { sesionApi }                              from '../../api/sesion.api.js';
import { estudianteApi }                          from '../../api/estudiante.api.js';
import { state, TIPO_LABEL, TIPO_ICON,
         ESTADO_LABEL, ESTADO_CLASS }             from './sesion.state.js';
import { ui }                                     from './sesion.ui.js';
import { sesionForm, estudianteForm }             from './sesion.form.js';
import { alerts }                                 from '../../utils/alerts.js';
import { formatters }                             from '../../utils/formatters.js';

// ─────────────────────────────────────────────────────────────────────────────
// MODALES Y OFFCANVAS
// ─────────────────────────────────────────────────────────────────────────────

/** @type {bootstrap.Modal|null} Modal de creación/edición de sesión. */
let _modalSesion        = null;
/** @type {bootstrap.Modal|null} Modal de registro de nuevo estudiante. */
let _modalEstudiante    = null;
/** @type {bootstrap.Modal|null} Modal de detalle de sesión. */
let _modalDetalleSesion = null;
/** @type {bootstrap.Modal|null} Modal de confirmación de guardado de sesión. */
let _modalConfirmarSesion = null;
/** @type {bootstrap.Modal|null} Modal de advertencia de conflicto de horario. */
let _modalConflicto       = null;

// ─────────────────────────────────────────────────────────────────────────────
// CARGA POR PROMOCIÓN
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Carga los datos de una promoción (sesiones, estudiantes y límites por tipo)
 * y renderiza las tres secciones de la vista.
 * Los límites se consultan en paralelo por tipo según `CONFIG_SESIONES`.
 *
 * @param {number} idPromocion - ID de la promoción a cargar.
 * @returns {Promise<void>}
 */
async function cargarPromocion(idPromocion) {
    state.activePromocion = idPromocion;

    try {
        const res = await sesionApi.listar({ id_promocion: idPromocion });
        state.sesiones[idPromocion] = res.data ?? [];
    } catch { state.sesiones[idPromocion] = []; }

    try {
        const res = await estudianteApi.listar(idPromocion);
        state.estudiantes[idPromocion] = res.data ?? [];
    } catch { state.estudiantes[idPromocion] = []; }

    // Cargar asistencia de cada sesión activa
    const sesiones = state.sesiones[idPromocion] ?? [];
    const activas  = sesiones.filter(s => s.estado !== 'cancelado');
    const asistencias = {};
    await Promise.all(activas.map(async s => {
        try {
            const res = await sesionApi.obtener(s.id_sesion);
            asistencias[s.id_sesion] = res.data.asistencia ?? [];
        } catch { asistencias[s.id_sesion] = []; }
    }));

    const promo = state.promociones.find(p => p.id_promocion === idPromocion) ?? null;
    ui.renderTabs(state.promociones, idPromocion);
    ui.renderSesiones(sesiones, state.estudiantes[idPromocion], asistencias, promo);
    ui.renderEstudiantes(state.estudiantes[idPromocion], idPromocion);
}

// ─────────────────────────────────────────────────────────────────────────────
// MINI CALENDARIO (modal de sesión)
// ─────────────────────────────────────────────────────────────────────────────

const MESES_CAL = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                   'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
const DIAS_CAL  = ['L','M','X','J','V','S','D'];

const TIPO_LABEL_CAL = { colegio: 'Colegio', exteriores: 'Exteriores', estudio: 'Estudio', otro: 'Otro' };
const DIAS_CORTOS    = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
const MESES_CORTOS   = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

const miniCal = {
    año: new Date().getFullYear(),
    mes: new Date().getMonth(),
    _data: [],

    _todasSesiones() {
        if (this._data.length > 0) return this._data;
        return Object.values(state.sesiones).flat().filter(s => s.estado !== 'cancelado');
    },

    async cargar() {
        try {
            const res  = await sesionApi.listar();
            this._data = (res.data ?? []).filter(s => s.estado !== 'cancelado');
            this.render();
        } catch { /* fallback: usa state.sesiones */ }
    },

    ir(año, mes) { this.año = año; this.mes = mes; this.render(); },

    render() {
        const labelEl = document.getElementById('miniCalLabel');
        const grid    = document.getElementById('miniCalGrid');
        if (!labelEl || !grid) return;

        labelEl.textContent = `${MESES_CAL[this.mes]} ${this.año}`;

        const mapa = {};
        for (const s of this._todasSesiones()) {
            const d = s.fecha_hora_sesion?.slice(0, 10);
            if (d) (mapa[d] ??= []).push(s);
        }

        const hoyISO = new Date().toISOString().slice(0, 10);
        const selISO = document.getElementById('sfFecha')?.value ?? '';
        const offset = (new Date(this.año, this.mes, 1).getDay() + 6) % 7;
        const ultimo = new Date(this.año, this.mes + 1, 0).getDate();

        let html = DIAS_CAL.map(d => `<div class="mca-name">${d}</div>`).join('');
        for (let i = 0; i < offset; i++) html += `<div class="mca-cell empty"></div>`;

        for (let d = 1; d <= ultimo; d++) {
            const iso  = `${this.año}-${String(this.mes + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const sess = mapa[iso] ?? [];
            let cls = 'mca-cell';
            if (iso < hoyISO) cls += ' mca-past';
            if (iso === hoyISO) cls += ' mca-hoy';
            if (iso === selISO) cls += ' mca-sel';
            const dots  = sess.slice(0, 3).map(s => `<span class="mca-dot ses-${s.estado}"></span>`).join('');
            const click = iso < hoyISO ? '' : `onclick="miniCalSelDia('${iso}')"`;
            html += `<div class="${cls}" ${click}><span class="mca-num">${d}</span><div class="mca-dots">${dots}</div></div>`;
        }
        grid.innerHTML = html;
    },

    renderDayInfo(iso) {
        const el = document.getElementById('miniCalDayInfo');
        if (!el) return;

        const sesiones = this._todasSesiones()
            .filter(s => s.fecha_hora_sesion?.slice(0, 10) === iso)
            .sort((a, b) => a.fecha_hora_sesion.localeCompare(b.fecha_hora_sesion));

        const d     = new Date(iso + 'T00:00:00');
        const label = `${DIAS_CORTOS[d.getDay()]} ${d.getDate()} ${MESES_CORTOS[d.getMonth()]}`;

        if (!sesiones.length) {
            el.style.display = 'block';
            el.innerHTML = `<div class="mca-day-title">${label}</div><div class="mca-day-empty">Sin sesiones este día.</div>`;
            return;
        }

        const rows = sesiones.map(s => {
            const hora    = s.fecha_hora_sesion?.slice(11, 16) ?? '';
            const colegio = s.nombre_colegio ?? s.nombre_promocion ?? '—';
            const tipo    = TIPO_LABEL_CAL[s.tipo] ?? s.tipo;
            return `<div class="mca-ses-row">
                <span class="mca-ses-hora">${hora}</span>
                <div class="mca-ses-info">
                    <div class="mca-ses-name" title="${colegio}">${colegio}</div>
                    <div class="mca-ses-meta">${tipo} · ${s.estado}</div>
                </div>
            </div>`;
        }).join('');

        el.style.display = 'block';
        el.innerHTML = `<div class="mca-day-title">${label}</div>${rows}`;
    },
};

/**
 * Recalcula los días válidos del select de día al cambiar mes o año.
 * Maneja correctamente febrero en años bisiestos.
 * Llamado desde onchange de los selects de fecha de nacimiento.
 *
 * @param {string} idAnio
 * @param {string} idMes
 * @param {string} idDia
 */
window.actualizarDias = function (idAnio, idMes, idDia) {
    const anioEl = document.getElementById(idAnio);
    const mesEl  = document.getElementById(idMes);
    const diaEl  = document.getElementById(idDia);
    if (!anioEl || !mesEl || !diaEl) return;

    const mes  = parseInt(mesEl.value,  10) || 0;
    // Sin año seleccionado se usa el actual (relevante solo para febrero bisiesto)
    const anio = parseInt(anioEl.value, 10) || new Date().getFullYear();

    // new Date(year, mes, 0) devuelve el último día del mes anterior → días del mes
    const maxDia = mes ? new Date(anio, mes, 0).getDate() : 31;
    const prevDia = parseInt(diaEl.value, 10) || 0;

    diaEl.innerHTML = '<option value="">Día</option>';
    for (let d = 1; d <= maxDia; d++) {
        const opt = document.createElement('option');
        opt.value = String(d).padStart(2, '0');
        opt.textContent = d;
        diaEl.appendChild(opt);
    }
    // Restaurar selección previa solo si sigue siendo válida
    if (prevDia && prevDia <= maxDia) {
        diaEl.value = String(prevDia).padStart(2, '0');
    }
};

window.miniCalPrev = () => {
    let { año, mes } = miniCal;
    mes--; if (mes < 0) { mes = 11; año--; }
    miniCal.ir(año, mes);
};
window.miniCalNext = () => {
    let { año, mes } = miniCal;
    mes++; if (mes > 11) { mes = 0; año++; }
    miniCal.ir(año, mes);
};
window.miniCalSelDia = function (iso) {
    const sfFecha = document.getElementById('sfFecha');
    if (sfFecha) sfFecha.value = iso;
    miniCal.render();
    miniCal.renderDayInfo(iso);
};

// ─────────────────────────────────────────────────────────────────────────────
// SESIONES — FUNCIONES GLOBALES
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Abre el modal de nueva sesión con el tipo pre-seleccionado.
 * Asigna la promoción activa al campo oculto `#sfPromocion`.
 *
 * @param {string} [tipo=''] - Tipo de sesión a pre-seleccionar en el formulario.
 */
window.abrirNuevaSesion = function (tipo = '') {
    sesionForm.limpiar(tipo);
    document.getElementById('sfPromocion').value = state.activePromocion;
    document.getElementById('modalSesionTitulo').textContent = 'Nueva sesión';
    const el = document.getElementById('miniCalDayInfo');
    if (el) el.style.display = 'none';
    const hoy = new Date();
    miniCal.año = hoy.getFullYear();
    miniCal.mes = hoy.getMonth();
    miniCal.render();
    miniCal.cargar();
    _modalSesion?.show();
};

/**
 * Carga los datos de una sesión desde la API y abre el modal en modo edición.
 *
 * @param {number} id - ID de la sesión a editar.
 * @returns {Promise<void>}
 */
window.abrirEditarSesion = async function (id) {
    try {
        const res = await sesionApi.obtener(id);
        sesionForm.poblar(res.data);
        document.getElementById('sfPromocion').value = state.activePromocion;
        document.getElementById('modalSesionTitulo').textContent = 'Editar sesión';
        const fecha = res.data.fecha_hora_sesion?.slice(0, 10);
        if (fecha) { const [a, m] = fecha.split('-').map(Number); miniCal.ir(a, m - 1); }
        else { const hoy = new Date(); miniCal.ir(hoy.getFullYear(), hoy.getMonth()); }
        miniCal.cargar();
        if (fecha) miniCal.renderDayInfo(fecha);
        _modalSesion?.show();
    } catch {
        alerts.error('No se pudo cargar la sesión.');
    }
};

/**
 * Devuelve la primera sesión (no cancelada) que coincide exactamente
 * en fecha y hora con `fechaHora`, excluyendo la sesión que se está editando.
 *
 * @param {string}      fechaHora  - Formato `'YYYY-MM-DD HH:MM:SS'`.
 * @param {number|null} idEditando - ID a ignorar (edición), o null si es nueva.
 * @returns {Object|null}
 */
function _detectarConflicto(fechaHora, idEditando) {
    const todas = Object.values(state.sesiones).flat();
    return todas.find(s =>
        s.estado !== 'cancelado' &&
        s.id_sesion !== idEditando &&
        s.fecha_hora_sesion === fechaHora
    ) ?? null;
}

/**
 * Muestra el modal de confirmación definitiva de sesión.
 * @param {Object} datos - Datos del formulario.
 */
function _mostrarConfirmacion(datos) {
    const tipoLabel = TIPO_LABEL[datos.tipo] || datos.tipo || 'esta sesión';
    const fecha = datos.fecha_hora_sesion.slice(0, 16).replace(' ', ' a las ');
    const mensajeEl = document.getElementById('confirmarSesionMensaje');
    if (mensajeEl) mensajeEl.textContent =
        `¿Estás seguro de querer agendar la sesión: ${tipoLabel} para el ${fecha}?`;
    _modalConfirmarSesion?.show();
}

/**
 * Valida el formulario de sesión. Si hay conflicto de horario muestra
 * un modal de advertencia; de lo contrario pasa directo a confirmación.
 *
 * @returns {void}
 */
window.confirmarGuardarSesion = function () {
    const err = sesionForm.validar();
    if (err) { alerts.error(err); return; }

    const datos      = sesionForm.datos();
    const idEditando = sesionForm.getId();
    const todasActivas = Object.values(state.sesiones).flat()
        .filter(s => s.estado !== 'cancelado' && s.id_sesion !== idEditando);

    // Regla 1: máximo 2 sesiones en la misma hora (YYYY-MM-DD HH)
    const nuevaHora    = datos.fecha_hora_sesion.slice(0, 13);
    const mismaHora    = todasActivas.filter(s => s.fecha_hora_sesion.slice(0, 13) === nuevaHora);
    if (mismaHora.length >= 2) {
        alerts.error('Ya hay 2 sesiones programadas en ese horario. El máximo permitido es 2 sesiones por hora.');
        return;
    }

    // Regla 2: la nueva sesión no puede ser anterior a la primera sesión ya registrada
    // de la misma promoción
    const idPromocion  = parseInt(document.getElementById('sfPromocion')?.value) || 0;
    const sesionesProm = (state.sesiones[idPromocion] ?? [])
        .filter(s => s.estado !== 'cancelado' && s.id_sesion !== idEditando);
    if (sesionesProm.length > 0) {
        const fechaMin = sesionesProm.map(s => s.fecha_hora_sesion).sort()[0];
        const nuevaFecha = datos.fecha_hora_sesion.slice(0, 10);
        const minFecha   = fechaMin.slice(0, 10);
        if (nuevaFecha < minFecha) {
            alerts.error(`La sesión no puede ser anterior a la primera ya programada (${minFecha}).`);
            return;
        }
    }

    // Conflicto de horario exacto (advertencia, no bloqueo)
    const conflicto  = _detectarConflicto(datos.fecha_hora_sesion, idEditando);
    if (conflicto) {
        const tipoConflicto = TIPO_LABEL[conflicto.tipo] || conflicto.tipo;
        const promo = conflicto.nombre_promocion
            ? `${conflicto.nombre_promocion}${conflicto.grado ? ` — ${conflicto.grado}${conflicto.seccion ? ' ' + conflicto.seccion : ''}` : ''}`
            : `Promoción #${conflicto.id_promocion}`;

        document.getElementById('conflictoSesionDetalle').textContent =
            `${tipoConflicto} · ${promo}`;

        document.getElementById('conflictoContinuar').onclick = () => {
            _modalConflicto?.hide();
            _mostrarConfirmacion(datos);
        };
        _modalConflicto?.show();
        return;
    }

    _mostrarConfirmacion(datos);
};

window.guardarSesion = async function () {
    const err = sesionForm.validar();
    if (err) { alerts.error(err); return; }

    const datos = sesionForm.datos();
    const id    = sesionForm.getId();

    try {
        if (id) {
            await sesionApi.actualizar(id, datos);
            alerts.ok('Sesión actualizada.');
        } else {
            await sesionApi.crear(datos);
            alerts.ok('Sesión creada.');
        }
        _modalConfirmarSesion?.hide();
        _modalSesion?.hide();
        await cargarPromocion(state.activePromocion);
    } catch (e) {
        alerts.error(e.message || 'Error al guardar la sesión.');
    }
};

/**
 * Cambia el estado de una sesión y recarga la promoción activa.
 *
 * @param {number} id     - ID de la sesión.
 * @param {string} estado - Nuevo estado (`'finalizado'` o `'cancelado'`).
 * @returns {Promise<void>}
 */
window.cambiarEstadoSesion = async function (id, estado) {
    try {
        await sesionApi.cambiarEstado(id, estado);
        alerts.ok(`Sesión marcada como ${estado}.`);
        await cargarPromocion(state.activePromocion);
    } catch (e) {
        alerts.error(e.message || 'Error al cambiar el estado.');
    }
};

// ─────────────────────────────────────────────────────────────────────────────
// ASISTENCIA — TABLA UNIFICADA
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Cicla el estado de asistencia de un estudiante en una sesión:
 * null → asistió (1) → faltó (0) → null
 *
 * @param {number}   idSesion     - ID de la sesión.
 * @param {number}   idEstudiante - ID del estudiante.
 * @param {1|0|null} estadoActual - Estado actual de asistencia.
 */
window.toggleAsistencia = async function (idSesion, idEstudiante, estadoActual) {
    try {
        if (estadoActual === null) {
            await sesionApi.agregarEstudiante(idSesion, idEstudiante);
            await sesionApi.marcarAsistencia(idSesion, idEstudiante, 1);
        } else if (estadoActual === 1) {
            await sesionApi.marcarAsistencia(idSesion, idEstudiante, 0);
        } else {
            await sesionApi.quitarEstudiante(idSesion, idEstudiante);
        }
        await cargarPromocion(state.activePromocion);
    } catch (e) {
        alerts.error(e.message || 'Error al actualizar asistencia.');
    }
};

// ─────────────────────────────────────────────────────────────────────────────
// ESTUDIANTES — FUNCIONES GLOBALES
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Limpia el formulario de estudiante, asigna la promoción activa y abre el modal.
 *
 * @param {number} idPromocion - ID de la promoción a la que se asociará el estudiante.
 */
window.abrirNuevoEstudiante = function (idPromocion) {
    estudianteForm.limpiar();
    state.activePromocion = idPromocion;
    _modalEstudiante?.show();

    // Cargar stock de productos en segundo plano; si falla, la sección se mantiene oculta
    fetch(`${BASE_URL}index.php/api/estudiantes/stock?id_promocion=${idPromocion}`)
        .then(r => r.json())
        .then(res => { if (res.status === 'success') estudianteForm.setStock(res.data); })
        .catch(() => {});
};

/**
 * Valida el formulario de estudiante y registra el nuevo estudiante en la API.
 * Cierra el modal y recarga la promoción activa al finalizar.
 *
 * @returns {Promise<void>}
 */
window.guardarEstudiante = async function () {
    const err = estudianteForm.validar();
    if (err) { alerts.error(err); return; }

    const datos = estudianteForm.datos(state.activePromocion);
    try {
        await estudianteApi.crear(datos);
        alerts.ok('Estudiante registrado.');
        _modalEstudiante?.hide();
        await cargarPromocion(state.activePromocion);
    } catch (e) {
        alerts.error(e.message || 'Error al registrar el estudiante.');
    }
};

/**
 * Solicita confirmación y elimina un estudiante de la promoción activa.
 *
 * @param {number} id - ID del estudiante a eliminar.
 * @returns {Promise<void>}
 */
window.eliminarEstudiante = async function (id) {
    if (!confirm('¿Eliminar este estudiante de la promoción? Esta acción no se puede deshacer.')) return;
    bootstrap.Modal.getInstance(document.getElementById('modalPerfilEstudiante'))?.hide();
    try {
        await estudianteApi.eliminar(id);
        alerts.ok('Estudiante eliminado.');
        await cargarPromocion(state.activePromocion);
    } catch (e) {
        alerts.error(e.message || 'Error al eliminar el estudiante.');
    }
};

// ─────────────────────────────────────────────────────────────────────────────
// DETALLE SESIÓN — MODAL
// ─────────────────────────────────────────────────────────────────────────────

window.verDetalleSesion = async function (id) {
    const body   = document.getElementById('detalleSesionBody');
    const footer = document.getElementById('detalleSesionFooter');
    const titulo = document.getElementById('detalleSesionTitulo');

    body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="bi bi-arrow-repeat"></i> Cargando...</div>';
    footer.innerHTML = '<button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>';
    _modalDetalleSesion?.show();

    try {
        const res = await sesionApi.obtener(id);
        const s   = res.data;

        const [fecha, horaFull] = s.fecha_hora_sesion.split(' ');
        const hora  = horaFull?.slice(0, 5) ?? '';
        const h     = parseInt(hora, 10);
        const min   = hora.slice(3, 5);
        const ampm  = h >= 12 ? 'p.m.' : 'a.m.';
        const h12   = h % 12 || 12;

        titulo.innerHTML = `<i class="bi bi-calendar3-event me-2" style="color:var(--accent);"></i>Sesión ${TIPO_LABEL[s.tipo] ?? s.tipo}`;

        const tipoBadge   = `<span class="tipo-badge tipo-${s.tipo}"><i class="bi ${TIPO_ICON[s.tipo] ?? 'bi-calendar'}"></i> ${TIPO_LABEL[s.tipo] ?? s.tipo}</span>`;
        const estadoBadge = `<span class="${ESTADO_CLASS[s.estado] ?? 'badge-pendiente'}">${ESTADO_LABEL[s.estado] ?? s.estado}</span>`;

        body.innerHTML = `
            <div style="display:flex;flex-direction:column;gap:1.1rem;">
                <div>
                    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.35rem;">Tipo</div>
                    ${tipoBadge}
                </div>
                <div>
                    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.35rem;">Fecha y hora</div>
                    <div style="font-size:.9rem;"><i class="bi bi-calendar3 me-1"></i>${formatters.fecha(fecha)} &nbsp;<strong>${h12}:${min} ${ampm}</strong></div>
                </div>
                <div>
                    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.35rem;">Estado</div>
                    ${estadoBadge}
                </div>
                ${s.observaciones ? `
                <div>
                    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.35rem;">Observaciones</div>
                    <div style="font-size:.85rem;color:var(--text-secondary);">${s.observaciones}</div>
                </div>` : ''}
            </div>`;

        const esconder = `bootstrap.Modal.getInstance(document.getElementById('modalDetalleSesion'))?.hide();`;
        const acciones = [];
        if (s.estado !== 'finalizado' && s.estado !== 'cancelado') {
            acciones.push(`<button class="btn btn-secondary btn-sm" onclick="${esconder}abrirEditarSesion(${id})"><i class="bi bi-pencil me-1"></i>Editar</button>`);
        }
        if (s.estado === 'pendiente') {
            acciones.push(`<button class="btn btn-success btn-sm" onclick="${esconder}cambiarEstadoSesion(${id},'finalizado')"><i class="bi bi-check-circle me-1"></i>Finalizar</button>`);
            acciones.push(`<button class="btn btn-danger btn-sm" onclick="${esconder}cambiarEstadoSesion(${id},'cancelado')"><i class="bi bi-x-circle me-1"></i>Cancelar</button>`);
        }
        footer.innerHTML = `<button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>${acciones.join('')}`;

    } catch {
        body.innerHTML = '<div style="color:var(--red-text);padding:1rem;">No se pudo cargar la sesión.</div>';
    }
};

// ─────────────────────────────────────────────────────────────────────────────
// PERFIL ESTUDIANTE
// ─────────────────────────────────────────────────────────────────────────────

const TIPO_LABEL_SESION = { colegio: 'Colegio', exteriores: 'Exteriores', estudio: 'Estudio', otro: 'Otro' };
const MESES = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

function _fechaCorta(str) {
    if (!str) return '—';
    const d = new Date(str.includes('T') ? str : str + 'T00:00:00');
    if (isNaN(d)) return str;
    return `${String(d.getDate()).padStart(2,'0')} ${MESES[d.getMonth()]} ${d.getFullYear()}`;
}

/**
 * Abre el modal de perfil del estudiante con sus datos, productos y asistencia.
 *
 * @param {number} idEstudiante - ID del estudiante.
 * @returns {Promise<void>}
 */
window.verDetalleEstudiante = async function(idEstudiante) {
    const modalEl = document.getElementById('modalPerfilEstudiante');
    const modal   = bootstrap.Modal.getOrCreateInstance(modalEl);
    const body    = document.getElementById('perfilBody');
    const titulo  = document.getElementById('perfilNombre');

    titulo.textContent = 'Cargando...';
    body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);"><i class="bi bi-arrow-repeat"></i> Cargando...</div>';
    modal.show();

    try {
        const res = await estudianteApi.obtener(idEstudiante);
        const e   = res.data;

        titulo.textContent = e.apellidos ? `${e.apellidos}, ${e.nombres}` : e.nombres;

        // ── Sección: datos personales ──────────────────────────────────────────
        const colorChip = e.color_fav
            ? `<span style="display:inline-flex;align-items:center;gap:6px;background:var(--bg-hover);
                            border:1px solid var(--border-color);border-radius:20px;padding:2px 10px;font-size:.8rem;">
                   <span style="width:12px;height:12px;border-radius:50%;background:${e.color_fav};
                                border:1px solid var(--border-color);display:inline-block;"></span>
                   ${e.color_fav}
               </span>`
            : '<span style="color:var(--text-muted);font-size:.8rem;">—</span>';

        const datosPersonales = `
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="row g-2">
                        <div class="col-6">
                            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">Nombres</div>
                            <div style="font-weight:500;">${e.nombres}</div>
                        </div>
                        <div class="col-6">
                            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">Apellidos</div>
                            <div style="font-weight:500;">${e.apellidos || '—'}</div>
                        </div>
                        <div class="col-6">
                            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">Nacimiento</div>
                            <div>${_fechaCorta(e.fecha_nacimiento)}</div>
                        </div>
                        <div class="col-6">
                            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">Profesión futura</div>
                            <div>${e.profesion_futura ?? '—'}</div>
                        </div>
                        <div class="col-12">
                            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Color favorito</div>
                            ${colorChip}
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6" style="border-left:1px solid var(--border-color);padding-left:1rem;">
                    <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.5rem;">Apoderado</div>
                    <div style="font-weight:500;">${e.apoderado_nombres ?? '—'} ${e.apoderado_apellidos ?? ''}</div>
                    <div style="font-size:.8rem;color:var(--text-muted);margin-top:2px;">${e.tipo_relacion ?? ''}</div>
                    <div style="font-size:.83rem;margin-top:6px;">
                        <i class="bi bi-telephone me-1"></i>${e.apoderado_telefono ?? '—'}
                    </div>
                </div>
            </div>`;

        // ── Sección: productos asignados al estudiante ────────────────────────
        const CATEGORIA_ICON = { cuadro: 'image', anuario: 'book', photobook: 'images', otro: 'tag' };
        const productosHtml = e.productos?.length
            ? `<div class="mb-4">
                   <div style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.5rem;">
                       Productos asignados
                   </div>
                   <div style="border:1px solid var(--border-color);border-radius:8px;overflow:hidden;">
                       ${e.productos.map((p, i) => `
                       <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;
                                   ${i < e.productos.length - 1 ? 'border-bottom:1px solid var(--border-color);' : ''}
                                   background:${i % 2 === 0 ? 'transparent' : 'var(--bg-hover)'};">
                           <i class="bi bi-${CATEGORIA_ICON[p.categoria] ?? 'tag'}"
                              style="color:var(--accent);font-size:.9rem;flex-shrink:0;"></i>
                           <div style="flex:1;font-size:.83rem;">${p.nombre_producto}</div>
                       </div>`).join('')}
                   </div>
               </div>`
            : `<div class="mb-4" style="font-size:.83rem;color:var(--text-muted);">Sin productos asignados.</div>`;

        // ── Sección: historial de sesiones ────────────────────────────────────
        const totalSesiones = e.sesiones?.length ?? 0;
        const asistio       = e.sesiones?.filter(s => s.asistio === 1).length ?? 0;
        const ausente       = e.sesiones?.filter(s => s.asistio === 0).length ?? 0;
        const sinMarcar     = e.sesiones?.filter(s => s.asistio === null).length ?? 0;

        const resumenHtml = totalSesiones
            ? `<div class="row g-2 mb-3">
                   ${[
                       { label:'Asistió',    val: asistio,   color:'var(--green-text)'  },
                       { label:'Ausente',    val: ausente,   color:'var(--red-text)'    },
                       { label:'Sin marcar', val: sinMarcar, color:'var(--amber-text)'  },
                   ].map(s => `
                   <div class="col-4">
                       <div style="text-align:center;background:var(--bg-hover);border:1px solid var(--border-color);
                                   border-radius:8px;padding:8px 4px;">
                           <div style="font-size:1.2rem;font-weight:700;color:${s.color};">${s.val}</div>
                           <div style="font-size:.68rem;color:var(--text-muted);">${s.label}</div>
                       </div>
                   </div>`).join('')}
               </div>`
            : '';

        const filasHtml = totalSesiones
            ? `<div style="max-height:200px;overflow-y:auto;border:1px solid var(--border-color);border-radius:8px;">
                   ${e.sesiones.map(s => {
                       const asistioIcon = s.asistio === 1
                           ? '<i class="bi bi-check-circle-fill" style="color:var(--green-text);" title="Asistió"></i>'
                           : s.asistio === 0
                               ? '<i class="bi bi-x-circle-fill" style="color:var(--red-text);" title="Ausente"></i>'
                               : '<i class="bi bi-dash-circle" style="color:var(--amber-text);" title="Sin marcar"></i>';
                       const [fecha, hora] = (s.fecha_hora_sesion || '').split(' ');
                       return `
                       <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;
                                   border-bottom:1px solid var(--border-color);">
                           ${asistioIcon}
                           <div style="flex:1;min-width:0;">
                               <div style="font-size:.83rem;font-weight:500;">${TIPO_LABEL_SESION[s.tipo] ?? s.tipo}</div>
                               <div style="font-size:.73rem;color:var(--text-muted);">${_fechaCorta(fecha)}${hora ? ' · ' + hora.slice(0,5) : ''}</div>
                           </div>
                           <span class="${ESTADO_CLASS[s.estado] ?? 'badge-pendiente'}"
                                 style="font-size:.68rem;">${ESTADO_LABEL[s.estado] ?? s.estado}</span>
                       </div>`;
                   }).join('')}
               </div>`
            : '<div style="font-size:.83rem;color:var(--text-muted);">Sin sesiones registradas.</div>';

        const sesionesHtml = `
            <div>
                <div style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.5rem;">
                    Asistencia a sesiones
                </div>
                ${resumenHtml}
                ${filasHtml}
            </div>`;

        const eliminarBtn = `
            <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border-color);text-align:right;">
                <button onclick="eliminarEstudiante(${idEstudiante})"
                        style="background:none;border:1px solid var(--red-border,#dc3545);color:var(--red-text,#dc3545);
                               border-radius:8px;padding:6px 14px;font-size:.8rem;cursor:pointer;font-family:inherit;
                               transition:background .15s;"
                        onmouseover="this.style.background='var(--red-bg,#ffeef0)'"
                        onmouseout="this.style.background='none'">
                    <i class="bi bi-trash me-1"></i> Eliminar estudiante
                </button>
            </div>`;

        body.innerHTML = datosPersonales + productosHtml + sesionesHtml + eliminarBtn;

    } catch (err) {
        console.error('Error cargando perfil:', err);
        body.innerHTML = '<div style="color:var(--red-text);padding:1rem;">No se pudo cargar el perfil del estudiante.</div>';
    }
};

// ─────────────────────────────────────────────────────────────────────────────
// NAVEGACIÓN A PANEL DE FORMULARIOS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Redirige al panel de administración de formularios para la promoción actual.
 * @returns {void}
 */
window.irAFormulario = function() {
    if (!state.activePromocion) {
        alerts.warning('Selecciona una promoción primero.');
        return;
    }
    window.location.href = `${BASE_URL}admin/formularios/promo-escolar/${state.activePromocion}`;
};

window.imprimirListaPromocion = function (idPromocion) {
    if (!idPromocion) {
        alerts.warning('No se encontró la promoción para imprimir.');
        return;
    }
    window.open(`${BASE_URL}contratos/${ID_CONTRATO}/sesiones/${idPromocion}/lista-pdf`, '_blank');
};

// ─────────────────────────────────────────────────────────────────────────────
// INICIALIZACIÓN
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Inicializa el módulo: asigna el estado global, crea las instancias de Bootstrap
 * y carga la primera promoción del contrato.
 *
 * @returns {void}
 */
function init() {
    state.idContrato  = ID_CONTRATO;
    state.promociones = PROMOCIONES;

    _modalSesion          = new bootstrap.Modal(document.getElementById('modalSesion'));
    _modalEstudiante      = new bootstrap.Modal(document.getElementById('modalEstudiante'));
    _modalDetalleSesion   = new bootstrap.Modal(document.getElementById('modalDetalleSesion'));
    _modalConfirmarSesion = new bootstrap.Modal(document.getElementById('modalConfirmarSesion'));
    _modalConflicto       = new bootstrap.Modal(document.getElementById('modalConflictoSesion'));

    document.getElementById('promocionesTabs')?.addEventListener('click', e => {
        const btn = e.target.closest('.promo-tab');
        if (btn) cargarPromocion(parseInt(btn.dataset.id, 10));
    });

    // Botón para ir al panel de formularios
    document.querySelector('.btn-nuevo-paquete')?.addEventListener('click', () => {
        window.irAFormulario();
    });

    // Sincronizar mini calendario cuando el usuario escribe la fecha directamente
    document.getElementById('sfFecha')?.addEventListener('change', () => {
        const val = document.getElementById('sfFecha').value;
        if (val) {
            const [a, m] = val.split('-').map(Number);
            miniCal.ir(a, m - 1);
            miniCal.renderDayInfo(val);
        } else {
            miniCal.render();
            const el = document.getElementById('miniCalDayInfo');
            if (el) el.style.display = 'none';
        }
    });

    if (state.promociones.length) {
        cargarPromocion(state.promociones[0].id_promocion);
    } else {
        ui.renderTabs([], null);
        document.getElementById('sesionesContainer').innerHTML = `
            <div class="empty-state">
                <i class="bi bi-mortarboard"></i>
                Este contrato no tiene promociones asociadas.
            </div>`;
    }
}

init();
