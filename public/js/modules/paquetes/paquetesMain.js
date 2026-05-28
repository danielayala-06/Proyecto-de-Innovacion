/**
 * @file    paquetesMain.js
 * @module  modules/paquetes/main
 *
 * Punto de entrada del módulo de paquetes (`/paquetes`).
 * Orquesta la carga de datos, el filtrado, los modales de CRUD y el toggle
 * de estado activo/inactivo.
 *
 * Flujo de inicialización (`init`):
 *  1. Crea instancias de los modales Bootstrap `#modalPaquete` y `#modalConfirm`.
 *  2. Registra los listeners de búsqueda (`#searchInput`), categoría
 *     (`#filterCat`) y nivel (`#filterNivel`).
 *  3. Llama a `cargarPaquetes()` para la carga inicial.
 *
 * Funciones globales expuestas en `window.*` para ser invocadas desde
 * los atributos `onclick` generados dinámicamente en las tarjetas:
 *  - {@link window.abrirNuevo}
 *  - {@link window.editarPaquete}
 *  - {@link window.guardarPaquete}
 *  - {@link window.toggleEstado}
 *  - {@link window.confirmarEliminar}
 *  - {@link window.eliminarPaquete}
 *  - {@link window.agregarItemModal}
 */

import { paqueteApi }                    from '../../api/paquete.api.js';
import { productoApi }                   from '../../api/producto.api.js';
import { state, calcularStats, filtrar } from './paquete.state.js';
import { ui }                            from './paquete.ui.js';
import { form }                          from './paquete.form.js';
import { alerts }                        from '../../utils/alerts.js';

// ─────────────────────────────────────────────────────────────────────────────
// ESTADO INTERNO
// ─────────────────────────────────────────────────────────────────────────────

/** @type {bootstrap.Modal|null} Modal de creación/edición de paquete. */
let _modal        = null;
/** @type {bootstrap.Modal|null} Modal de confirmación de desactivación. */
let _modalConfirm = null;
/** @type {number|null} ID del paquete que se está editando; `null` si es creación. */
let _idEditando   = null;

// ─────────────────────────────────────────────────────────────────────────────
// CARGA DESDE API
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Carga todos los paquetes desde la API, aplica el filtro vacío inicial
 * y renderiza el grid y las estadísticas.
 *
 * @returns {Promise<void>}
 */
async function cargarPaquetes() {
    ui.renderLoading();
    try {
        const res       = await paqueteApi.listar();
        state.todos     = res.data ?? [];
        state.filtrados = filtrar(state.todos, {});
        ui.renderStats(calcularStats(state.filtrados));
        ui.renderGrid(state.filtrados);
    } catch {
        ui.renderError('No se pudieron cargar los paquetes.');
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// FILTRADO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Lee los controles de filtro activos y aplica el filtro sobre `state.todos`.
 * Actualiza el grid y las estadísticas sin volver a llamar a la API.
 *
 * @returns {void}
 */
function _aplicarFiltros() {
    const busqueda    = document.getElementById('searchInput')?.value  ?? '';
    const categoria   = document.getElementById('filterCat')?.value    ?? '';
    const nivelFiltro = document.getElementById('filterNivel')?.value  ?? '';
    state.filtrados = filtrar(state.todos, { busqueda, categoria, nivelFiltro });
    ui.renderStats(calcularStats(state.filtrados));
    ui.renderGrid(state.filtrados);
}

// ─────────────────────────────────────────────────────────────────────────────
// FUNCIONES GLOBALES — CRUD
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Abre el modal en modo creación: limpia el formulario y oculta el botón
 * de desactivación. La zona de imagen solo aplica en edición.
 */
window.abrirNuevo = function () {
    _idEditando = null;
    form.limpiar();
    document.getElementById('modalTitulo').textContent = 'Nuevo paquete';
    document.getElementById('btnEliminarModal').style.display = 'none';
    document.getElementById('sesionesSection').style.display = '';
    document.getElementById('pImagenSection').style.display = 'none';
    _modal?.show();
};

/**
 * Carga los datos del paquete desde la API y abre el modal en modo edición.
 * Muestra el botón de desactivación y la zona de imagen.
 *
 * @param {number} id - ID del paquete a editar.
 * @returns {Promise<void>}
 */
window.editarPaquete = async function (id) {
    try {
        const res = await paqueteApi.obtener(id);
        _idEditando = id;
        form.poblar(res.data);
        document.getElementById('modalTitulo').textContent = 'Editar paquete';
        document.getElementById('btnEliminarModal').style.display = '';
        document.getElementById('sesionesSection').style.display = 'none';
        document.getElementById('pImagenSection').style.display = '';
        _actualizarPreviewImagen(res.data.imagen_url ?? null);
        _modal?.show();
    } catch {
        alerts.error('No se pudo cargar el paquete.');
    }
};

/**
 * Valida el formulario y guarda el paquete (crea o actualiza según `_idEditando`).
 * Cierra el modal y recarga el grid al finalizar con éxito.
 *
 * @returns {Promise<void>}
 */
window.guardarPaquete = async function () {
    const error = form.validar();
    if (error) { alerts.error(error); return; }

    try {
        if (_idEditando) {
            await paqueteApi.actualizar(_idEditando, form.datosActualizar());
        } else {
            const res       = await paqueteApi.crear(form.datosCrear());
            const idPaquete = res.id_paquete;
            const pendientes = form.getReglasPendientes();
            for (const regla of pendientes) {
                await paqueteApi.crearRegla(idPaquete, regla);
            }
        }
        _modal?.hide();
        alerts.ok(_idEditando ? 'Paquete actualizado.' : 'Paquete creado.');
        await cargarPaquetes();
    } catch (err) {
        alerts.error(err.message || 'Error al guardar el paquete.');
    }
};

/**
 * Alterna el estado de un paquete entre `'ACTIVO'` e `'INACTIVO'` y recarga el grid.
 * Se invoca desde los botones de activar/desactivar de cada tarjeta.
 *
 * @param {number} id           - ID del paquete.
 * @param {string} estadoActual - Estado actual (`'ACTIVO'` o `'INACTIVO'`).
 * @returns {Promise<void>}
 */
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

/**
 * Lee el nombre del paquete del formulario abierto, lo muestra en el modal
 * de confirmación y cierra el modal principal.
 * La API no soporta DELETE; se usa desactivación.
 */
window.confirmarEliminar = function () {
    const nombre = document.getElementById('pNombre')?.value || '';
    document.getElementById('confirmNombre').textContent = nombre;
    _modal?.hide();
    _modalConfirm?.show();
};

/**
 * Desactiva el paquete actualmente en edición a través de la API.
 * La API no expone un endpoint DELETE; se usa `PATCH estado = 'INACTIVO'`.
 *
 * @returns {Promise<void>}
 */
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

/**
 * Delega en `form.agregarItem()` para agregar un ítem vacío al modal.
 * Expuesto globalmente para el botón `#btnAgregarItem` del modal.
 */
window.agregarItemModal = function () {
    form.agregarItem();
};

window.agregarSesionModal = function () {
    form.agregarSesion();
};

/**
 * Lee el mini-formulario de regla y la guarda (en edición vía API, en creación en memoria).
 */
window.agregarReglaModal = async function () {
    const regla = form.leerReglaForm();
    if (!regla) {
        alerts.error('Completa todos los campos de la regla antes de agregar.');
        return;
    }

    if (form.modoEdicion) {
        try {
            const res = await paqueteApi.crearRegla(form.idPaqueteActual, regla);
            // Re-fetch the package so saved rules show updated data (incl. nombre_producto_beneficio)
            const updated = await paqueteApi.obtener(form.idPaqueteActual);
            form.poblar(updated.data);
            alerts.ok('Regla guardada.');
        } catch (err) {
            alerts.error(err.message || 'Error al guardar la regla.');
        }
    } else {
        form.agregarReglaPendiente(regla);
    }
};

/**
 * Elimina una regla vía API y actualiza la vista (modo edición).
 * Referenciado desde `window.__paqEliminarReglaApi` para que form.js pueda invocarlo.
 *
 * @param {number} idRegla
 */
window.__paqEliminarReglaApi = async function (idRegla) {
    try {
        await paqueteApi.eliminarRegla(idRegla);
        alerts.ok('Regla eliminada.');
        const res = await paqueteApi.obtener(form.idPaqueteActual);
        form.poblar(res.data);
    } catch (err) {
        alerts.error(err.message || 'Error al eliminar la regla.');
    }
};

// ─────────────────────────────────────────────────────────────────────────────
// IMAGEN DE PAQUETE
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Actualiza la preview de imagen en el modal.
 * @param {string|null} url - URL de la imagen, o null si no hay imagen.
 */
function _actualizarPreviewImagen(url) {
    const preview     = document.getElementById('pImagenPreview');
    const placeholder = document.getElementById('pImagenPlaceholder');
    const acciones    = document.getElementById('pImagenAcciones');
    if (!preview || !placeholder || !acciones) return;

    if (url) {
        preview.src          = url;
        preview.style.display = '';
        placeholder.style.display = 'none';
        acciones.style.display    = '';
    } else {
        preview.src           = '';
        preview.style.display = 'none';
        placeholder.style.display = '';
        acciones.style.display    = 'none';
    }
}

/**
 * Quita la imagen del paquete en edición (llama al endpoint DELETE).
 */
window.quitarImagenPaquete = async function () {
    if (!_idEditando) return;
    try {
        await paqueteApi.eliminarImagen(_idEditando);
        _actualizarPreviewImagen(null);
        alerts.ok('Imagen eliminada.');
        // Actualizar el card en el grid sin recargar todo
        const idx = state.todos.findIndex(p => p.id_paquete === _idEditando);
        if (idx !== -1) {
            state.todos[idx].imagen     = null;
            state.todos[idx].imagen_url = null;
            state.filtrados = state.todos.filter(p => state.filtrados.some(f => f.id_paquete === p.id_paquete));
            ui.renderGrid(state.filtrados);
        }
    } catch {
        alerts.error('No se pudo eliminar la imagen.');
    }
};

// ─────────────────────────────────────────────────────────────────────────────
// INICIALIZACIÓN
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Inicializa el módulo: crea los modales, registra listeners y carga los paquetes.
 *
 * @returns {void}
 */
async function _cargarProductosEnSelect() {
    try {
        const res = await productoApi.listar();
        const select = document.getElementById('rIdProductoBeneficio');
        if (!select) return;
        const placeholder = select.querySelector('option[value=""]');
        select.innerHTML = '';
        if (placeholder) select.appendChild(placeholder);
        (res.data ?? []).forEach(p => {
            const opt = document.createElement('option');
            opt.value       = p.id_producto;
            opt.textContent = p.nombre_producto;
            select.appendChild(opt);
        });
    } catch (err) { console.warn('No se pudieron cargar productos para el selector:', err); }
}

function init() {
    const modalEl        = document.getElementById('modalPaquete');
    const modalConfirmEl = document.getElementById('modalConfirm');
    if (modalEl)        _modal        = new bootstrap.Modal(modalEl);
    if (modalConfirmEl) _modalConfirm = new bootstrap.Modal(modalConfirmEl);

    document.getElementById('searchInput')?.addEventListener('input',  _aplicarFiltros);
    document.getElementById('filterCat')?.addEventListener('change',   _aplicarFiltros);
    document.getElementById('filterNivel')?.addEventListener('change', _aplicarFiltros);

    document.getElementById('rTipoBeneficio')?.addEventListener('change', e => {
        form.toggleBeneficioUI(e.target.value);
    });

    document.getElementById('pImagenInput')?.addEventListener('change', async function () {
        const file = this.files?.[0];
        if (!file || !_idEditando) return;

        // Mostrar preview local inmediatamente (sin esperar al servidor)
        const localUrl = URL.createObjectURL(file);
        _actualizarPreviewImagen(localUrl);

        try {
            const res = await paqueteApi.subirImagen(_idEditando, file);
            URL.revokeObjectURL(localUrl);
            if (res.status === 'success') {
                _actualizarPreviewImagen(res.imagen_url);
                alerts.ok('Imagen guardada.');
                // Actualizar objeto en state para que el grid refleje la imagen
                const idx = state.todos.findIndex(p => p.id_paquete === _idEditando);
                if (idx !== -1) {
                    state.todos[idx].imagen_url = res.imagen_url;
                    ui.renderGrid(state.filtrados);
                }
            } else {
                _actualizarPreviewImagen(null);
                alerts.error(res.message || 'Error al subir la imagen.');
            }
        } catch {
            URL.revokeObjectURL(localUrl);
            _actualizarPreviewImagen(null);
            alerts.error('Error de red al subir la imagen.');
        }
        // Limpiar el input para permitir subir el mismo archivo de nuevo
        this.value = '';
    });

    _cargarProductosEnSelect();
    cargarPaquetes();
}

/**
 * Adapta el formulario de regla según el tipo de condición elegido.
 *
 * - ELEGIBILIDAD_MIN: oculta la sección de beneficio (es solo una restricción)
 *   y actualiza los placeholders para reflejar que se trata de un requisito mínimo.
 * - CANTIDAD_MIN / CANTIDAD_MAX: muestra la sección de beneficio normalmente.
 */
window.__paqOnCondicionChange = function(tipo) {
    const wrapBenef   = document.getElementById('wrapRTipoBeneficio');
    const wrapValBen  = document.getElementById('wrapRValorBeneficio');
    const wrapProd    = document.getElementById('wrapRProductoBeneficio');
    const labelValor  = document.getElementById('rValorCondicionLabel');
    const labelBenef  = document.getElementById('rValorBeneficioLabel');
    const inputDesc   = document.getElementById('rDescripcion');
    const selBenef    = document.getElementById('rTipoBeneficio');
    const inputValBen = document.getElementById('rValorBeneficio');

    if (tipo === 'ELEGIBILIDAD_MIN') {
        // No tiene beneficio — es una restricción de elegibilidad
        if (wrapBenef)   wrapBenef.style.display  = 'none';
        if (wrapValBen)  wrapValBen.style.display  = '';
        if (wrapProd)    wrapProd.classList.add('d-none');
        if (labelValor)  labelValor.textContent    = 'Mínimo de alumnos requerido';
        if (labelBenef)  labelBenef.textContent    = 'Aviso cuando no se cumple';
        if (inputValBen) inputValBen.placeholder   = 'Ej: Paquete no disponible para grupos menores de 14 alumnos.';
        if (inputDesc)   inputDesc.placeholder     = 'Ej: Se requieren mínimo 14 alumnos para contratar este paquete.';
        if (selBenef)    selBenef.value = 'otro';
    } else if (tipo === 'CANTIDAD_MAX') {
        // No tiene beneficio — es una reducción de sesiones por grupo pequeño
        if (wrapBenef)   wrapBenef.style.display  = 'none';
        if (wrapValBen)  wrapValBen.style.display  = '';
        if (wrapProd)    wrapProd.classList.add('d-none');
        if (labelValor)  labelValor.textContent    = 'Máximo de alumnos para aplicar';
        if (labelBenef)  labelBenef.textContent    = 'Aviso que verá el usuario';
        if (inputValBen) inputValBen.placeholder   = 'Ej: La sesión se realizará en una sola fecha.';
        if (inputDesc)   inputDesc.placeholder     = 'Ej: Con menos de 12 alumnos, la sesión se realiza en una sola fecha.';
        if (selBenef)    selBenef.value = 'otro';
    } else {
        if (wrapBenef)   wrapBenef.style.display  = '';
        if (labelValor)  labelValor.textContent    = 'N.° de alumnos';
        if (labelBenef)  labelBenef.textContent    = 'Detalle del beneficio';
        if (inputValBen) inputValBen.placeholder   = 'Ej: Cuadro laminado para el docente';
        if (inputDesc)   inputDesc.placeholder     = 'Ej: Con 15 o más alumnos, el docente recibe un cuadro laminado.';
        if (selBenef)    selBenef.value = '';
        form.toggleBeneficioUI('');
    }
};

init();
