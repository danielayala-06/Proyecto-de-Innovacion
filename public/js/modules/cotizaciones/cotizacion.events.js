/**
 * @file    cotizacion.events.js
 * @module  modules/cotizaciones/events
 *
 * Lógica de interacción del módulo de cotizaciones del index.
 * Gestiona filtrado, paginación, ordenamiento y los modales de detalle,
 * confirmación de rechazo y aprobación.
 *
 * Expone funciones globales en `window.*` para ser invocadas desde
 * los atributos `onclick` generados dinámicamente en el HTML.
 *
 * @exports initIndex - Registra los listeners del index (búsqueda y filtro).
 */

import { state, calcularResumenes, ordenarPorEstado, ESTADOS_ARCHIVADOS_COT } from './cotizacion.state.js';
import { ui }                       from './cotizacion.ui.js';
import { manager }                  from './cotizacion.manager.js';
import { cotizacionApi }            from '../../api/cotizacion.api.js';
import { alerts }                   from '../../utils/alerts.js';

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS INTERNOS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Renderiza el slice de `state.filtradas` correspondiente a la página activa.
 * @returns {void}
 */
function _renderPagina() {
  const { filtradas, pagina, porPagina } = state;
  const desde = (pagina - 1) * porPagina;
  ui.renderTabla(filtradas.slice(desde, desde + porPagina));
  ui.renderPaginacion(filtradas.length, pagina, porPagina);
}

/**
 * Extrae el nombre del cliente de una cotización para búsqueda/ordenamiento.
 *
 * @param {Object} c - Cotización con campo `cliente` (string u objeto).
 * @returns {string}
 */
function _nombreCliente(c) {
  if (!c.cliente) return '';
  return typeof c.cliente === 'object'
    ? (c.cliente.nombre_completo ?? '')
    : c.cliente;
}

/**
 * Aplica los filtros activos de búsqueda y estado, persiste las preferencias
 * y vuelve a la página 1.
 *
 * @returns {void}
 */
function _filtrar() {
  const search = (document.getElementById('searchInput')?.value  || '').toLowerCase().trim();
  const estado = (document.getElementById('filterEstado')?.value || '').toUpperCase().trim();

  manager.guardarFiltros({ search, estado });

  // Si el filtro activo es un estado archivado pero están ocultas, lo reseteamos
  if (!state.mostrarArchivadas && ESTADOS_ARCHIVADOS_COT.has(estado)) {
    const filterEl = document.getElementById('filterEstado');
    if (filterEl) filterEl.value = '';
    estado = '';
  }

  const filtradas = state.filas.filter(c => {
    const e        = c.estado?.toUpperCase();
    const okArch   = state.mostrarArchivadas || !ESTADOS_ARCHIVADOS_COT.has(e);
    const okSearch = !search
      || _nombreCliente(c).toLowerCase().includes(search)
      || String(c.id).includes(search);
    const okEstado = !estado || e === estado;
    return okArch && okSearch && okEstado;
  });

  state.filtradas = ordenarPorEstado(filtradas);
  state.pagina = 1;
  _renderPagina();
}

// ─────────────────────────────────────────────────────────────────────────────
// PAGINACIÓN Y ORDEN (window.*)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Cambia la página activa.
 *
 * @param {number} n - Número de página (base 1).
 */
window.irPagina = function (n) {
  state.pagina = n;
  _renderPagina();
};

/**
 * Ordena la tabla por la columna indicada, alternando asc/desc.
 *
 * @param {string} key - Clave de columna (`'codigo'`, `'cliente'`, `'total'`, etc.).
 */
window.sortBy = function (key) {
  if (state.sortKey === key) {
    state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
  } else {
    state.sortKey = key;
    state.sortDir = 'asc';
  }
  const dir = state.sortDir === 'asc' ? 1 : -1;
  const keyMap = {
    codigo: 'id', cliente: 'cliente', fecha: 'fecha',
    total: 'total', estado: 'estado', creado: 'fecha',
  };
  const campo = keyMap[key] || key;

  state.filtradas.sort((a, b) => {
    const va = campo === 'cliente' ? _nombreCliente(a) : (a[campo] ?? '');
    const vb = campo === 'cliente' ? _nombreCliente(b) : (b[campo] ?? '');
    return va < vb ? -dir : va > vb ? dir : 0;
  });

  _renderPagina();
};

// ─────────────────────────────────────────────────────────────────────────────
// RECHAZO / ELIMINACIÓN (window.*)
// ─────────────────────────────────────────────────────────────────────────────

/** @type {number|null} ID de la cotización pendiente de rechazo. */
let _pendingId = null;
/** @type {bootstrap.Modal|null} */
let _modalDel  = null;

/**
 * Abre el modal de confirmación de rechazo de la cotización.
 *
 * @param {number} id     - ID de la cotización.
 * @param {string} codigo - Código formateado para mostrar en el mensaje.
 */
window.confirmarEliminar = function (id, codigo) {
  _pendingId = id;
  const span = document.getElementById('confirmCod');
  if (span) span.textContent = codigo;
  if (!_modalDel) _modalDel = new bootstrap.Modal(document.getElementById('modalConfirm'));
  _modalDel.show();
};

/**
 * Rechaza (elimina) la cotización pendiente y actualiza la tabla localmente
 * sin volver a llamar a la API.
 */
window.eliminarCotizacion = async function () {
  if (!_pendingId) return;
  try {
    await cotizacionApi.eliminar(_pendingId);
    _modalDel?.hide();
    alerts.ok('Cotización rechazada correctamente.');

    state.filas     = state.filas.filter(c => c.id !== _pendingId);
    state.filtradas = state.filtradas.filter(c => c.id !== _pendingId);
    _pendingId = null;

    ui.renderStats(calcularResumenes(state.filas));
    _renderPagina();
  } catch (e) {
    alerts.error(e.message || 'No se pudo rechazar la cotización.');
  }
};

// ─────────────────────────────────────────────────────────────────────────────
// DETALLE EN MODAL (window.*)
// ─────────────────────────────────────────────────────────────────────────────

/** @type {bootstrap.Modal|null} */
let _modalDet = null;

/**
 * Abre el modal de detalle de la cotización especificada.
 * Las acciones disponibles dependen del estado y si ya tiene contrato.
 *
 * @param {number} id - ID de la cotización.
 */
window.verDetalle = async function (id) {
  if (!_modalDet) _modalDet = new bootstrap.Modal(document.getElementById('modalDetalle'));

  const bodyEl  = document.getElementById('detalleBody');
  const titleEl = document.getElementById('detalleTitle');
  const accsEl  = document.getElementById('detalleAcciones');

  if (titleEl) titleEl.textContent = `Cotización #${String(id).padStart(4, '0')}`;
  if (bodyEl)  bodyEl.innerHTML = `
    <div class="text-center py-3">
      <div class="spinner-border spinner-border-sm" role="status"></div>
    </div>`;
  if (accsEl) accsEl.innerHTML = '';
  _modalDet.show();

  try {
    const res  = await cotizacionApi.obtener(id);
    const data = res.data;
    if (bodyEl) bodyEl.innerHTML = ui.renderDetalle(data);

    const estado        = (data.estado ?? '').toUpperCase();
    const tieneContrato = !!data.tiene_contrato;
    if (accsEl) {
      if (estado === 'PENDIENTE') {
        accsEl.innerHTML = `
          <button class="btn btn-sm btn-success" onclick="aprobarCotizacion(${id})">
            <i class="bi bi-check-circle me-1"></i>Aprobar
          </button>`;
      } else if (estado === 'APROBADA' && !tieneContrato) {
        accsEl.innerHTML = `
          <button class="btn btn-sm btn-primary" onclick="irAGenerarContrato(${id})">
            <i class="bi bi-file-earmark-plus me-1"></i>Generar contrato
          </button>`;
      } else if (estado === 'APROBADA' && tieneContrato) {
        accsEl.innerHTML = `
          <span class="badge bg-success" style="font-size:.8rem;">
            <i class="bi bi-check-circle me-1"></i>Contrato generado
          </span>`;
      }
    }
  } catch (e) {
    if (bodyEl) bodyEl.innerHTML =
      `<p class="text-danger text-center py-2">${e.message}</p>`;
  }
};

// ─────────────────────────────────────────────────────────────────────────────
// APROBACIÓN (window.*)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Aprueba una cotización y actualiza su estado localmente en `state.filas`.
 * Muestra el botón "Generar contrato" en el modal de detalle si sigue abierto.
 *
 * @param {number} id - ID de la cotización a aprobar.
 */
window.aprobarCotizacion = async function (id) {
  try {
    await cotizacionApi.cambiarEstado(id, 'APROBADA');
    alerts.ok('Cotización aprobada.');

    const row = state.filas.find(c => c.id === id);
    if (row) row.estado = 'APROBADA';

    const accsEl = document.getElementById('detalleAcciones');
    if (accsEl) {
      accsEl.innerHTML = `
        <button class="btn btn-sm btn-primary" onclick="irAGenerarContrato(${id})">
          <i class="bi bi-file-earmark-plus me-1"></i>Generar contrato
        </button>`;
    }

    ui.renderStats(calcularResumenes(state.filas));
    _renderPagina();
  } catch (e) {
    alerts.error(e.message || 'No se pudo aprobar la cotización.');
  }
};

/**
 * Redirige a la vista de creación de contrato con la cotización indicada.
 *
 * @param {number} id - ID de la cotización.
 */
window.irAGenerarContrato = function (id) {
  window.location.href = (window.BASE_URL || '/') + 'contratos/crear?cot=' + id;
};

// ─────────────────────────────────────────────────────────────────────────────
// INICIALIZACIÓN PÚBLICA
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Registra los listeners de búsqueda y filtro de estado del index.
 * Debe llamarse una vez en el punto de entrada del módulo.
 *
 * @returns {void}
 */
function _actualizarBtnArchivadas() {
  const btn = document.getElementById('btnToggleArchivadas');
  if (!btn) return;
  btn.innerHTML = state.mostrarArchivadas
    ? '<i class="bi bi-eye-slash"></i> Ocultar archivadas'
    : '<i class="bi bi-archive"></i> Mostrar archivadas';
  btn.classList.toggle('btn-secondary',  state.mostrarArchivadas);
  btn.classList.toggle('btn-outline-secondary', !state.mostrarArchivadas);
}

export function initIndex() {
  document.getElementById('searchInput') ?.addEventListener('input',  _filtrar);
  document.getElementById('filterEstado')?.addEventListener('change', _filtrar);
  document.getElementById('btnToggleArchivadas')?.addEventListener('click', () => {
    state.mostrarArchivadas = !state.mostrarArchivadas;
    _actualizarBtnArchivadas();
    _filtrar();
  });
}
