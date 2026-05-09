import { paqueteApi }                    from '../../api/paquete.api.js';
import { state, calcularStats, filtrar } from './paquete.state.js';
import { ui }                            from './paquete.ui.js';
import { form }                          from './paquete.form.js';
import { alerts }                        from '../../utils/alerts.js';

let _modal        = null;
let _modalConfirm = null;
let _idEditando   = null;

/* ═══════════════════════════════════════════════════════════
   CARGA (fetch → api)
═══════════════════════════════════════════════════════════ */
async function cargarPaquetes() {
    ui.renderLoading();
    try {
        const res       = await paqueteApi.listar();
        state.todos     = res.data ?? [];
        state.filtrados = [...state.todos];
        ui.renderStats(calcularStats(state.todos));
        ui.renderGrid(state.filtrados);
    } catch {
        ui.renderError('No se pudieron cargar los paquetes.');
    }
}

/* ═══════════════════════════════════════════════════════════
   FILTRADO (estado)
═══════════════════════════════════════════════════════════ */
function _aplicarFiltros() {
    const busqueda     = document.getElementById('searchInput')?.value    ?? '';
    const categoria    = document.getElementById('filterCat')?.value      ?? '';
    const estadoFiltro = document.getElementById('filterEstado')?.value   ?? '';
    state.filtrados = filtrar(state.todos, { busqueda, categoria, estadoFiltro });
    ui.renderGrid(state.filtrados);
}

/* ═══════════════════════════════════════════════════════════
   FUNCIONES GLOBALES — llamadas desde la vista
═══════════════════════════════════════════════════════════ */

/** Abre el modal para crear un nuevo paquete */
window.abrirNuevo = function () {
    _idEditando = null;
    form.limpiar();
    document.getElementById('modalTitulo').textContent = 'Nuevo paquete';
    document.getElementById('btnEliminarModal').style.display = 'none';
    _modal?.show();
};

/** Abre el modal con los datos de un paquete existente para editar */
window.editarPaquete = async function (id) {
    try {
        const res = await paqueteApi.obtener(id);
        _idEditando = id;
        form.poblar(res.data);
        document.getElementById('modalTitulo').textContent = 'Editar paquete';
        document.getElementById('btnEliminarModal').style.display = '';
        _modal?.show();
    } catch {
        alerts.error('No se pudo cargar el paquete.');
    }
};

/** Guarda (crea o actualiza) el paquete desde el modal */
window.guardarPaquete = async function () {
    const error = form.validar();
    if (error) { alerts.error(error); return; }

    try {
        if (_idEditando) {
            const { datos, estado } = form.datosActualizar();
            await paqueteApi.actualizar(_idEditando, datos);
            await paqueteApi.cambiarEstado(_idEditando, estado);
        } else {
            await paqueteApi.crear(form.datosCrear());
        }
        _modal?.hide();
        alerts.ok(_idEditando ? 'Paquete actualizado.' : 'Paquete creado.');
        await cargarPaquetes();
    } catch (err) {
        alerts.error(err.message || 'Error al guardar el paquete.');
    }
};

/** Activa o desactiva un paquete desde la tarjeta */
window.toggleEstado = async function (id, estadoActual) {
    const nuevo = estadoActual === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
    try {
        await paqueteApi.cambiarEstado(id, nuevo);
        alerts.ok(nuevo === 'ACTIVO' ? 'Paquete activado.' : 'Paquete desactivado.');
        await cargarPaquetes();
    } catch {
        alerts.error('No se pudo cambiar el estado del paquete.');
    }
};

/** Abre el modal de confirmación de eliminación */
window.confirmarEliminar = function () {
    const nombre = document.getElementById('pNombre')?.value || '';
    document.getElementById('confirmNombre').textContent = nombre;
    _modal?.hide();
    _modalConfirm?.show();
};

/** Desactiva el paquete (la API no tiene DELETE, se desactiva) */
window.eliminarPaquete = async function () {
    if (!_idEditando) return;
    try {
        await paqueteApi.cambiarEstado(_idEditando, 'INACTIVO');
        _modalConfirm?.hide();
        alerts.ok('Paquete desactivado.');
        await cargarPaquetes();
    } catch {
        alerts.error('No se pudo desactivar el paquete.');
    }
};

/** Agrega un ítem vacío a la lista del modal */
window.agregarItemModal = function () {
    form.agregarItem();
};

/* ═══════════════════════════════════════════════════════════
   INIT
═══════════════════════════════════════════════════════════ */
function init() {
    const modalEl        = document.getElementById('modalPaquete');
    const modalConfirmEl = document.getElementById('modalConfirm');
    if (modalEl)        _modal        = new bootstrap.Modal(modalEl);
    if (modalConfirmEl) _modalConfirm = new bootstrap.Modal(modalConfirmEl);

    document.getElementById('searchInput')?.addEventListener('input',  _aplicarFiltros);
    document.getElementById('filterCat')?.addEventListener('change',   _aplicarFiltros);
    document.getElementById('filterEstado')?.addEventListener('change', _aplicarFiltros);

    cargarPaquetes();
}

init();
