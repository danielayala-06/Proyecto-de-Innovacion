import { state }          from './cotizacion.state.js';
import { ui }             from './cotizacion.ui.js';
import { cotizacionApi }  from '../../api/cotizacion.api.js';
import { alerts }         from '../../utils/alerts.js';

/* ── Renderiza la página actual ──────────────────────────────── */
function _renderPagina() {
  const { filtradas, pagina, porPagina } = state;
  const desde = (pagina - 1) * porPagina;
  ui.renderTabla(filtradas.slice(desde, desde + porPagina));
  ui.renderPaginacion(filtradas.length, pagina, porPagina);
}

/* ── Filtra y vuelve a la página 1 ──────────────────────────── */
function _filtrar() {
  const search = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();
  const estado = (document.getElementById('filterEstado')?.value || '').toUpperCase().trim();

  state.filtradas = state.filas.filter(c => {
    const okSearch = !search
      || c.cliente?.toLowerCase().includes(search)
      || String(c.id).includes(search);
    const okEstado = !estado || c.estado?.toUpperCase() === estado;
    return okSearch && okEstado;
  });

  state.pagina = 1;
  _renderPagina();
}

/* ── Paginación (expuesta globalmente para onclick en HTML) ───── */
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
  const keyMap = { codigo: 'id', cliente: 'cliente', fecha: 'fecha', total: 'total', estado: 'estado', creado: 'fecha' };
  const campo = keyMap[key] || key;
  state.filtradas.sort((a, b) => {
    const va = a[campo] ?? '';
    const vb = b[campo] ?? '';
    return va < vb ? -dir : va > vb ? dir : 0;
  });
  _renderPagina();
};

/* ── Confirmar rechazo/eliminación ──────────────────────────── */
let _pendingId  = null;
let _modalDel   = null;

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
    state.filas     = state.filas.filter(c => c.id !== _pendingId);
    state.filtradas = state.filtradas.filter(c => c.id !== _pendingId);
    _pendingId = null;
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
  if (titleEl) titleEl.textContent = `Cotización #${String(id).padStart(4,'0')}`;
  if (bodyEl)  bodyEl.innerHTML = `
    <div class="text-center py-3">
      <div class="spinner-border spinner-border-sm" role="status"></div>
    </div>`;
  _modalDet.show();

  try {
    const res = await cotizacionApi.obtener(id);
    if (bodyEl) bodyEl.innerHTML = ui.renderDetalle(res.data);
  } catch (e) {
    if (bodyEl) bodyEl.innerHTML = `<p class="text-danger text-center py-2">${e.message}</p>`;
  }
};

/* ── Inicializa los listeners del index ──────────────────────── */
export function initIndex() {
  document.getElementById('searchInput') ?.addEventListener('input',  _filtrar);
  document.getElementById('filterEstado')?.addEventListener('change', _filtrar);
}
