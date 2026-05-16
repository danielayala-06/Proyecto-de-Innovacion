/**
 * @file    sesionesLista.js
 * @module  modules/sesiones/lista
 *
 * Punto de entrada del módulo de listado global de sesiones (`/sesiones`).
 * Vista de solo lectura: muestra todas las sesiones del sistema con filtros
 * por estado, tipo y búsqueda de texto.
 *
 * A diferencia de `sesionesMain.js`, este módulo es autónomo (no depende de
 * un contrato específico ni usa el estado compartido de `sesion.state.js`).
 *
 * Flujo de inicialización (`init`):
 *  1. Muestra skeleton de carga.
 *  2. Carga todas las sesiones desde `GET /api/sesiones`.
 *  3. Aplica los filtros iniciales (todos vacíos) y renderiza tabla + stats.
 *  4. Registra listeners de búsqueda (`#searchInput`), estado (`#filterEstado`)
 *     y tipo (`#filterTipo`).
 */

import { sesionApi }                                          from '../../api/sesion.api.js';
import { formatters }                                         from '../../utils/formatters.js';
import { TIPO_LABEL, TIPO_ICON, ESTADO_LABEL, ESTADO_CLASS }  from './sesion.state.js';

// ─────────────────────────────────────────────────────────────────────────────
// ESTADO INTERNO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Lista completa de sesiones cargadas desde la API.
 * @type {Array<Object>}
 */
let _sesiones = [];

/**
 * Subconjunto de `_sesiones` después de aplicar los filtros activos.
 * @type {Array<Object>}
 */
let _filtrado = [];

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS PRIVADOS DE BADGE
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Genera el HTML del badge de tipo de sesión con icono Bootstrap Icons.
 *
 * @param {string} tipo - Tipo de sesión.
 * @returns {string} HTML del `<span class="tipo-badge">`.
 */
function _tipoBadge(tipo) {
    const icon  = TIPO_ICON[tipo]  ?? 'bi-calendar';
    const label = TIPO_LABEL[tipo] ?? tipo;
    return `<span class="tipo-badge tipo-${tipo}"><i class="bi ${icon}"></i> ${label}</span>`;
}

/**
 * Genera el HTML del badge de estado de sesión.
 *
 * @param {string} estado - Estado de la sesión.
 * @returns {string} HTML del `<span>` con clase CSS de estado.
 */
function _estadoBadge(estado) {
    const cls   = ESTADO_CLASS[estado] ?? 'badge-pendiente';
    const label = ESTADO_LABEL[estado] ?? estado;
    return `<span class="${cls}">${label}</span>`;
}

// ─────────────────────────────────────────────────────────────────────────────
// RENDERIZADO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Actualiza las tarjetas de estadísticas con los conteos del array `_filtrado`.
 *
 * Elementos DOM actualizados (IDs):
 *  `statTotal`, `statPendientes`, `statFinalizadas`, `statCanceladas`.
 *
 * @returns {void}
 */
function _renderStats() {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('statTotal',       _filtrado.length);
    set('statPendientes',  _filtrado.filter(s => s.estado === 'pendiente').length);
    set('statFinalizadas', _filtrado.filter(s => s.estado === 'finalizado').length);
    set('statCanceladas',  _filtrado.filter(s => s.estado === 'cancelado').length);
}

/**
 * Renderiza las filas de `_filtrado` en el `<tbody id="tablaBody">`.
 * Actualiza el texto de `#paginaInfo` con el total de resultados.
 *
 * Cada fila muestra: tipo (badge), nombre de promoción + grado, colegio,
 * fecha/hora, estado y un enlace al detalle del contrato (si `id_contrato` existe).
 *
 * @returns {void}
 */
function _render() {
    const tbody = document.getElementById('tablaBody');
    const info  = document.getElementById('paginaInfo');
    if (!tbody) return;

    if (!_filtrado.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="con-empty">
                    <i class="bi bi-camera" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                    No hay sesiones para mostrar.
                </td>
            </tr>`;
        if (info) info.textContent = 'Sin resultados';
        return;
    }

    tbody.innerHTML = _filtrado.map(s => {
        const fecha = s.fecha_hora_sesion
            ? formatters.fecha(s.fecha_hora_sesion.slice(0, 10)) +
              ' ' + s.fecha_hora_sesion.slice(11, 16)
            : '—';

        const verLink = s.id_contrato
            ? `<a class="btn btn-sm btn-outline-secondary" title="Ver sesiones del contrato"
                  href="contratos/${s.id_contrato}/sesiones">
                 <i class="bi bi-arrow-right-circle"></i>
               </a>`
            : `<button class="btn btn-sm btn-outline-secondary" disabled title="Sin contrato vinculado">
                 <i class="bi bi-arrow-right-circle"></i>
               </button>`;

        return `
            <tr>
                <td>${_tipoBadge(s.tipo)}</td>
                <td>
                    <span style="font-size:.85rem;">${s.nombre_promocion ?? '—'}</span>
                    ${s.grado ? `<br><small style="color:var(--text-muted);">${s.grado}</small>` : ''}
                </td>
                <td style="font-size:.84rem;">${s.nombre_colegio ?? '—'}</td>
                <td style="font-size:.83rem;">${fecha}</td>
                <td>${_estadoBadge(s.estado)}</td>
                <td style="text-align:center;">${verLink}</td>
            </tr>`;
    }).join('');

    if (info) info.textContent = `${_filtrado.length} sesión${_filtrado.length !== 1 ? 'es' : ''}`;
}

// ─────────────────────────────────────────────────────────────────────────────
// FILTRADO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Lee los controles de filtro activos y aplica el filtro sobre `_sesiones`.
 * Actualiza `_filtrado`, re-renderiza la tabla y las estadísticas.
 *
 * Filtros disponibles:
 *  - `#searchInput`   : búsqueda de texto en `nombre_promocion`, `nombre_colegio`, `grado`.
 *  - `#filterEstado`  : filtro exacto por campo `estado`.
 *  - `#filterTipo`    : filtro exacto por campo `tipo`.
 *
 * @returns {void}
 */
function _aplicarFiltros() {
    const q      = document.getElementById('searchInput')?.value.toLowerCase().trim() ?? '';
    const estado = document.getElementById('filterEstado')?.value ?? '';
    const tipo   = document.getElementById('filterTipo')?.value   ?? '';

    _filtrado = _sesiones.filter(s => {
        if (estado && s.estado !== estado) return false;
        if (tipo   && s.tipo   !== tipo)   return false;
        if (q) {
            const haystack = [s.nombre_promocion, s.nombre_colegio, s.grado]
                .filter(Boolean).join(' ').toLowerCase();
            if (!haystack.includes(q)) return false;
        }
        return true;
    });

    _render();
    _renderStats();
}

// ─────────────────────────────────────────────────────────────────────────────
// ESTADO DE CARGA Y ERROR
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Muestra un skeleton de carga en el `<tbody>` (5 filas semitransparentes).
 *
 * @returns {void}
 */
function _renderLoading() {
    const tbody = document.getElementById('tablaBody');
    if (!tbody) return;
    const fila = `<td><div class="placeholder-glow"><span class="placeholder col-8"></span></div></td>`;
    tbody.innerHTML = Array.from({ length: 5 }, () =>
        `<tr style="opacity:.5;">${fila.repeat(6)}</tr>`
    ).join('');
}

/**
 * Muestra un mensaje de error en el `<tbody>`.
 *
 * @returns {void}
 */
function _renderError() {
    const tbody = document.getElementById('tablaBody');
    if (!tbody) return;
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4" style="color:var(--red-text);font-size:.85rem;">
                <i class="bi bi-exclamation-circle me-1"></i>Error al cargar las sesiones.
            </td>
        </tr>`;
}

// ─────────────────────────────────────────────────────────────────────────────
// INICIALIZACIÓN
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Inicializa el módulo: muestra el skeleton, carga las sesiones desde la API,
 * aplica el filtro inicial y registra los listeners de filtrado.
 *
 * @returns {Promise<void>}
 */
async function init() {
    _renderLoading();

    try {
        const res = await sesionApi.listar();
        _sesiones = res.data ?? [];
    } catch {
        _renderError();
        return;
    }

    _aplicarFiltros();

    document.getElementById('searchInput')?.addEventListener('input',  _aplicarFiltros);
    document.getElementById('filterEstado')?.addEventListener('change', _aplicarFiltros);
    document.getElementById('filterTipo')?.addEventListener('change',   _aplicarFiltros);
}

init();
