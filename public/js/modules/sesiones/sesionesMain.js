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

    const tipos = [...new Set((CONFIG_SESIONES || []).map(c => c.tipo_sesion))];
    state.limites[idPromocion] = {};
    for (const tipo of tipos) {
        try {
            const r = await sesionApi.limite(idPromocion, tipo);
            state.limites[idPromocion][tipo] = r.data;
        } catch {
            state.limites[idPromocion][tipo] = { permitidas: 0, usadas: 0, puede_crear: false };
        }
    }

    ui.renderTabs(state.promociones, idPromocion);
    ui.renderSesiones(
        state.sesiones[idPromocion],
        state.limites[idPromocion],
        CONFIG_SESIONES || []
    );
    ui.renderEstudiantes(state.estudiantes[idPromocion], idPromocion);

    document.querySelectorAll('.btn-add-sesion').forEach(btn => {
        btn.addEventListener('click', () => abrirNuevaSesion(btn.dataset.tipo));
    });
}

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
