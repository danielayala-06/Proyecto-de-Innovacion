/**
 * @file    contratosIndexMain.js
 * @module  modules/contratos/contratosIndexMain
 *
 * Punto de entrada del módulo de contratos para la vista index (`/contratos`).
 * Se ejecuta como módulo ES6 con top-level `await`.
 *
 * Flujo de inicialización:
 *  1. Muestra skeleton de carga inmediatamente.
 *  2. Restaura filtros guardados en `localStorage`.
 *  3. Carga la lista de contratos desde la API y renderiza tabla + stats.
 *  4. Registra los listeners de búsqueda, filtro y modales.
 *  5. Si la URL contiene `?cot_id=N`, abre automáticamente el modal de generación
 *     de contrato con esa cotización pre-seleccionada (flujo desde cotizaciones).
 */

import { state, calcularStats, ordenarPorEstado, ESTADOS_ARCHIVADOS_CON }  from './contrato.state.js';
import { ui }                                       from './contrato.ui.js';
import { manager }                                  from './contrato.manager.js';
import { initEvents, _filtrar, abrirConCotizacionId } from './contrato.events.js';
import { contratoApi }                              from '../../api/contrato.api.js';
import { alerts }                                   from '../../utils/alerts.js';

/* ── 1. Mostrar estado de carga inmediatamente ───────────────────────────── */
ui.renderLoading();

/* ── 2. Restaurar filtros guardados en localStorage ─────────────────────── */
const pref = manager.cargarFiltros();
if (pref?.search) {
  const el = document.getElementById('searchInput');
  if (el) el.value = pref.search;
}
if (pref?.estado) {
  const el = document.getElementById('filterEstado');
  if (el) el.value = pref.estado;
}

/* ── 3. Fetch a la API y renderizar ─────────────────────────────────────── */
try {
  const res = await contratoApi.listar();
  state.filas = ordenarPorEstado(res.data ?? []);

  // Aplicar filtros restaurados + filtro de archivadas
  const _search = (pref?.search || '').toLowerCase().trim();
  const _estado = (pref?.estado || '').toLowerCase().trim();
  const _estadoMap = { vigente: 'ACTIVO', completado: 'COMPLETADO', cancelado: 'CANCELADO' };

  state.filtradas = state.filas.filter(c => {
    const e      = c.estado?.toUpperCase();
    const okArch = state.mostrarArchivadas || !ESTADOS_ARCHIVADOS_CON.has(e);
    if (!okArch) return false;
    const nombre  = (c.cliente?.nombre ?? '').toLowerCase();
    const cod     = String(c.id);
    const okSearch = !_search || nombre.includes(_search) || cod.includes(_search);
    const okEstado = !_estado || e === (_estadoMap[_estado] ?? _estado.toUpperCase());
    return okSearch && okEstado;
  });

  ui.renderStats(calcularStats(state.filas));
  ui.renderTabla(state.filtradas.slice(0, state.porPagina));
  ui.renderPaginacion(state.filtradas.length, state.pagina, state.porPagina);
} catch (e) {
  ui.renderError('No se pudieron cargar los contratos. Intenta nuevamente.');
  alerts.error(e.message || 'Error de conexión con el servidor.');
}

/* ── 4. Cablear eventos (búsqueda, filtro, sort, modales) ───────────────── */
initEvents();

/* ── 5. Auto-abrir modal si viene de cotizaciones con ?cot_id ───────────── */
const _cotId = new URL(window.location.href).searchParams.get('cot_id');
if (_cotId) {
  history.replaceState(null, '', window.location.pathname);
  abrirConCotizacionId(parseInt(_cotId));
}
