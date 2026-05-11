import { state, calcularStats }                    from './contrato.state.js';
import { ui }                                       from './contrato.ui.js';
import { manager }                                  from './contrato.manager.js';
import { initEvents, _filtrar, abrirConCotizacionId } from './contrato.events.js';
import { contratoApi }                              from '../../api/contrato.api.js';
import { alerts }                                   from '../../utils/alerts.js';

/* ── 1. Mostrar estado de carga inmediatamente ───────────────── */
ui.renderLoading();

/* ── 2. Restaurar filtros guardados en localStorage ──────────── */
const pref = manager.cargarFiltros();
if (pref?.search) {
  const el = document.getElementById('searchInput');
  if (el) el.value = pref.search;
}
if (pref?.estado) {
  const el = document.getElementById('filterEstado');
  if (el) el.value = pref.estado;
}

/* ── 3. Fetch a la API y renderizar ──────────────────────────── */
try {
  const res = await contratoApi.listar();
  state.filas     = res.data ?? [];
  state.filtradas = state.filas;

  if (pref?.search || pref?.estado) {
    _filtrar();
  }

  ui.renderStats(calcularStats(state.filas));
  ui.renderTabla(state.filtradas.slice(0, state.porPagina));
  ui.renderPaginacion(state.filtradas.length, state.pagina, state.porPagina);
} catch (e) {
  ui.renderError('No se pudieron cargar los contratos. Intenta nuevamente.');
  alerts.error(e.message || 'Error de conexión con el servidor.');
}

/* ── 4. Cablear eventos (búsqueda, filtro, sort, modales) ────── */
initEvents();

/* ── 5. Auto-abrir modal si viene de cotizaciones con ?cot_id ── */
const _cotId = new URL(window.location.href).searchParams.get('cot_id');
if (_cotId) {
  history.replaceState(null, '', window.location.pathname);
  abrirConCotizacionId(parseInt(_cotId));
}
