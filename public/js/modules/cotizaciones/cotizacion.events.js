import { state, calcularResumenes } from './cotizacion.state.js';
import { ui }                       from './cotizacion.ui.js';
import { manager }                  from './cotizacion.manager.js';
import { cotizacionApi }            from '../../api/cotizacion.api.js';
import { alerts }                   from '../../utils/alerts.js';

/* ── Renderiza la página actual de la tabla ──────────────────── */
function _renderPagina() {
  const { filtradas, pagina, porPagina } = state;
  const desde = (pagina - 1) * porPagina;
  ui.renderTabla(filtradas.slice(desde, desde + porPagina));
  ui.renderPaginacion(filtradas.length, pagina, porPagina);
}

/* ── Extrae nombre del cliente (string u objeto) ─────────────── */
function _nombreCliente(c) {
  if (!c.cliente) return '';
  return typeof c.cliente === 'object'
    ? (c.cliente.nombre_completo ?? '')
    : c.cliente;
}

/* ── Filtra y vuelve a la página 1 ──────────────────────────── */
function _filtrar() {
  const search = (document.getElementById('searchInput')?.value  || '').toLowerCase().trim();
  const estado = (document.getElementById('filterEstado')?.value || '').toUpperCase().trim();

  manager.guardarFiltros({ search, estado });

  state.filtradas = state.filas.filter(c => {
    const okSearch = !search
      || _nombreCliente(c).toLowerCase().includes(search)
      || String(c.id).includes(search);
    const okEstado = !estado || c.estado?.toUpperCase() === estado;
    return okSearch && okEstado;
  });

  state.pagina = 1;
  _renderPagina();
}

/* ── Paginación global (onclick en el HTML) ──────────────────── */
window.irPagina = function (n) {
  state.pagina = n;
  _renderPagina();
};

/* ── Orden de columnas ───────────────────────────────────────── */
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

/* ── Confirmar rechazo/eliminación ──────────────────────────── */
let _pendingId = null;
let _modalDel  = null;

window.confirmarEliminar = function (id, codigo) {
  _pendingId = id;
  const span = document.getElementById('confirmCod');
  if (span) span.textContent = codigo;
  if (!_modalDel) _modalDel = new bootstrap.Modal(document.getElementById('modalConfirm'));
  _modalDel.show();
};

window.eliminarCotizacion = async function () {
  if (!_pendingId) return;
  try {
    await cotizacionApi.eliminar(_pendingId);
    _modalDel?.hide();
    alerts.ok('Cotización rechazada correctamente.');

    // Actualizar estado local y re-renderizar sin volver a llamar a la API
    state.filas     = state.filas.filter(c => c.id !== _pendingId);
    state.filtradas = state.filtradas.filter(c => c.id !== _pendingId);
    _pendingId = null;

    ui.renderStats(calcularResumenes(state.filas));
    _renderPagina();
  } catch (e) {
    alerts.error(e.message || 'No se pudo rechazar la cotización.');
  }
};

/* ── Ver detalle en modal ────────────────────────────────────── */
let _modalDet = null;

window.verDetalle = async function (id) {
  if (!_modalDet) _modalDet = new bootstrap.Modal(document.getElementById('modalDetalle'));

  const bodyEl  = document.getElementById('detalleBody');
  const titleEl = document.getElementById('detalleTitle');

  if (titleEl) titleEl.textContent = `Cotización #${String(id).padStart(4, '0')}`;
  if (bodyEl)  bodyEl.innerHTML = `
    <div class="text-center py-3">
      <div class="spinner-border spinner-border-sm" role="status"></div>
    </div>`;
  _modalDet.show();

  try {
    const res = await cotizacionApi.obtener(id);
    if (bodyEl) bodyEl.innerHTML = ui.renderDetalle(res.data);
  } catch (e) {
    if (bodyEl) bodyEl.innerHTML =
      `<p class="text-danger text-center py-2">${e.message}</p>`;
  }
};

/* ── Inicializa los listeners del index ──────────────────────── */
export function initIndex() {
  document.getElementById('searchInput') ?.addEventListener('input',  _filtrar);
  document.getElementById('filterEstado')?.addEventListener('change', _filtrar);
}
