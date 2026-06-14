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

import { sesionApi }                    from '../../api/sesion.api.js';
import { estudianteApi }                from '../../api/estudiante.api.js';
import { state }                        from './sesion.state.js';
import { ui }                           from './sesion.ui.js';
import { sesionForm, estudianteForm }   from './sesion.form.js';
import { alerts }                       from '../../utils/alerts.js';

// ─────────────────────────────────────────────────────────────────────────────
// MODALES Y OFFCANVAS
// ─────────────────────────────────────────────────────────────────────────────

/** @type {bootstrap.Modal|null} Modal de creación/edición de sesión. */
let _modalSesion     = null;
/** @type {bootstrap.Modal|null} Modal de registro de nuevo estudiante. */
let _modalEstudiante = null;
/** @type {bootstrap.Offcanvas|null} Offcanvas de gestión de asistencia. */
let _offcanvas       = null;

/** Estado del ordenamiento de sesiones. */
let _sortCampo = 'sesion';   // 'sesion' | 'creacion'
let _sortDir   = 'asc';      // 'asc' | 'desc'

function _aplicarOrden(sesiones) {
    return [...sesiones].sort((a, b) => {
        const va = _sortCampo === 'sesion' ? a.fecha_hora_sesion : a.id_sesion;
        const vb = _sortCampo === 'sesion' ? b.fecha_hora_sesion : b.id_sesion;
        const d  = _sortDir === 'asc' ? 1 : -1;
        return va < vb ? -d : va > vb ? d : 0;
    });
}

function _sincronizarControles() {
    const sel = document.getElementById('sesionSortCampo');
    if (sel) sel.value = _sortCampo;

    const btnAsc  = document.getElementById('sesionSortAsc');
    const btnDesc = document.getElementById('sesionSortDesc');
    const activeStyle  = 'background:var(--accent);color:#fff;border-color:var(--accent);';
    const defaultStyle = 'background:var(--bg-surface);color:var(--text-primary);';
    if (btnAsc)  btnAsc.style.cssText  += _sortDir === 'asc'  ? activeStyle : defaultStyle;
    if (btnDesc) btnDesc.style.cssText += _sortDir === 'desc' ? activeStyle : defaultStyle;
}

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

    const sesiones      = _aplicarOrden(state.sesiones[idPromocion] ?? []);
    const totalActivas  = sesiones.filter(s => s.estado !== 'cancelado').length;
    const puedeAgregar  = totalActivas < 3;

    ui.renderTabs(state.promociones, idPromocion);
    ui.renderSesiones(sesiones, puedeAgregar);
    _sincronizarControles();
    ui.renderEstudiantes(state.estudiantes[idPromocion], idPromocion);
}

window.ordenarSesiones = function (dir) {
    // Si se pasa dirección, actualízala; si no, solo recampo desde el select
    if (dir === 'asc' || dir === 'desc') {
        _sortDir = dir;
    }
    const sel = document.getElementById('sesionSortCampo');
    if (sel) _sortCampo = sel.value;

    const sesiones     = _aplicarOrden(state.sesiones[state.activePromocion] ?? []);
    const totalActivas = sesiones.filter(s => s.estado !== 'cancelado').length;
    ui.renderSesiones(sesiones, totalActivas < 3);
    _sincronizarControles();
};

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
 * Valida el formulario de sesión y guarda (crea o actualiza).
 * Recarga la promoción activa al finalizar con éxito.
 *
 * @returns {Promise<void>}
 */
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
// ASISTENCIA — FUNCIONES GLOBALES
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Carga los datos de una sesión con su asistencia y abre el offcanvas.
 * Actualiza `state.sesionActiva` y el título del offcanvas.
 *
 * @param {number} idSesion - ID de la sesión a gestionar.
 * @returns {Promise<void>}
 */
window.abrirAsistencia = async function (idSesion) {
    try {
        const res          = await sesionApi.obtener(idSesion);
        state.sesionActiva = res.data;
        const estudiantes  = state.estudiantes[state.activePromocion] ?? [];
        document.getElementById('asistenciaTitulo').textContent =
            `Asistencia · ${res.data.tipo} · ${res.data.fecha_hora_sesion.slice(0, 10)}`;
        ui.renderAsistencia(res.data, estudiantes);
        _offcanvas?.show();
    } catch {
        alerts.error('No se pudo cargar la sesión.');
    }
};

/**
 * Agrega un estudiante a la lista de asistencia de una sesión y recarga el offcanvas.
 *
 * @param {number} idSesion     - ID de la sesión.
 * @param {number} idEstudiante - ID del estudiante a agregar.
 * @returns {Promise<void>}
 */
window.agregarAAsistencia = async function (idSesion, idEstudiante) {
    try {
        await sesionApi.agregarEstudiante(idSesion, idEstudiante);
        await abrirAsistencia(idSesion);
    } catch (e) {
        alerts.error(e.message || 'Error al agregar estudiante.');
    }
};

/**
 * Quita un estudiante de la lista de asistencia de una sesión y recarga el offcanvas.
 *
 * @param {number} idSesion     - ID de la sesión.
 * @param {number} idEstudiante - ID del estudiante a quitar.
 * @returns {Promise<void>}
 */
window.quitarDeAsistencia = async function (idSesion, idEstudiante) {
    try {
        await sesionApi.quitarEstudiante(idSesion, idEstudiante);
        await abrirAsistencia(idSesion);
    } catch (e) {
        alerts.error(e.message || 'Error al quitar estudiante.');
    }
};

/**
 * Marca la asistencia de un estudiante en una sesión (1 = asistió, 0 = ausente)
 * y recarga el offcanvas.
 *
 * @param {number} idSesion     - ID de la sesión.
 * @param {number} idEstudiante - ID del estudiante.
 * @param {0|1}   valor         - `1` si asistió, `0` si estuvo ausente.
 * @returns {Promise<void>}
 */
window.marcarAsistencia = async function (idSesion, idEstudiante, valor) {
    try {
        await sesionApi.marcarAsistencia(idSesion, idEstudiante, valor);
        await abrirAsistencia(idSesion);
    } catch (e) {
        alerts.error(e.message || 'Error al marcar asistencia.');
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
    try {
        await estudianteApi.eliminar(id);
        alerts.ok('Estudiante eliminado.');
        await cargarPromocion(state.activePromocion);
    } catch (e) {
        alerts.error(e.message || 'Error al eliminar el estudiante.');
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
                            <div style="font-weight:500;">${e.apellidos}</div>
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
                    <div style="font-weight:500;">${e.apoderado_nombres} ${e.apoderado_apellidos ?? ''}</div>
                    <div style="font-size:.8rem;color:var(--text-muted);margin-top:2px;">${e.tipo_relacion ?? ''}</div>
                    <div style="font-size:.83rem;margin-top:6px;">
                        <i class="bi bi-telephone me-1"></i>${e.apoderado_telefono ?? '—'}
                    </div>
                </div>
            </div>`;

        // ── Sección: productos adquiridos ──────────────────────────────────────
        const productosHtml = e.productos?.length
            ? `<div class="mb-4">
                   <div style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.5rem;">
                       Productos / paquetes contratados
                   </div>
                   <div style="border:1px solid var(--border-color);border-radius:8px;overflow:hidden;">
                       ${e.productos.map((p, i) => `
                       <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;
                                   ${i < e.productos.length - 1 ? 'border-bottom:1px solid var(--border-color);' : ''}
                                   background:${i % 2 === 0 ? 'transparent' : 'var(--bg-hover)'};">
                           <i class="bi bi-${p.tipo_item === 'paquete' ? 'box-seam' : 'tag'}"
                              style="color:var(--accent);font-size:.9rem;flex-shrink:0;"></i>
                           <div style="flex:1;font-size:.83rem;">${p.descripcion}</div>
                           <span style="font-size:.75rem;color:var(--text-muted);white-space:nowrap;">x${p.cantidad}</span>
                       </div>`).join('')}
                   </div>
               </div>`
            : `<div class="mb-4" style="font-size:.83rem;color:var(--text-muted);">Sin productos registrados.</div>`;

        // ── Sección: historial de sesiones ────────────────────────────────────
        const totalSesiones = e.sesiones?.length ?? 0;
        const asistio       = e.sesiones?.filter(s => s.asistio === 1).length ?? 0;
        const ausente       = e.sesiones?.filter(s => s.asistio === 0).length ?? 0;
        const sinMarcar     = e.sesiones?.filter(s => s.asistio === null).length ?? 0;

        const resumenHtml = totalSesiones
            ? `<div class="row g-2 mb-3">
                   ${[
                       { label:'Total',      val: totalSesiones, color:'var(--text-primary)' },
                       { label:'Asistió',    val: asistio,       color:'var(--green-text)'  },
                       { label:'Ausente',    val: ausente,       color:'var(--red-text)'    },
                       { label:'Sin marcar', val: sinMarcar,     color:'var(--amber-text)'  },
                   ].map(s => `
                   <div class="col-3">
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
                           <span class="${s.estado === 'finalizado' ? 'badge-aprobada' : s.estado === 'cancelado' ? 'badge-rechazada' : 'badge-pendiente'}"
                                 style="font-size:.68rem;">${s.estado}</span>
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

        body.innerHTML = datosPersonales + productosHtml + sesionesHtml;

    } catch (err) {
        console.error('Error cargando perfil:', err);
        body.innerHTML = '<div style="color:var(--red-text);padding:1rem;">No se pudo cargar el perfil del estudiante.</div>';
    }
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

    _modalSesion     = new bootstrap.Modal(document.getElementById('modalSesion'));
    _modalEstudiante = new bootstrap.Modal(document.getElementById('modalEstudiante'));
    _offcanvas       = new bootstrap.Offcanvas(document.getElementById('offcanvasAsistencia'));

    document.getElementById('promocionesTabs')?.addEventListener('click', e => {
        const btn = e.target.closest('.promo-tab');
        if (btn) cargarPromocion(parseInt(btn.dataset.id, 10));
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
