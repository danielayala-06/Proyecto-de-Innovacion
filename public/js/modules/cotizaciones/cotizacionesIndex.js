/**
 * @file    cotizacionesIndex.js
 * @module  modules/cotizaciones/cotizacionesIndex
 *
 * Punto de entrada del módulo de cotizaciones para la vista index (`/cotizaciones`).
 * Se ejecuta como módulo ES6 con top-level `await`.
 *
 * Flujo de inicialización:
 *  1. Muestra skeleton de carga inmediatamente.
 *  2. Restaura filtros guardados en `localStorage` en los controles del DOM.
 *  3. Carga la lista de cotizaciones desde la API.
 *  4. Aplica los filtros restaurados antes de renderizar (para no mostrar lista completa al regresar).
 *  5. Renderiza stats, tabla y paginación.
 *  6. Registra los listeners de búsqueda y filtro.
 */

import { state, calcularResumenes, ordenarPorEstado, ESTADOS_ARCHIVADOS_COT } from './cotizacion.state.js';
import { ui }                       from './cotizacion.ui.js';
import { manager }                  from './cotizacion.manager.js';
import { initIndex }                from './cotizacion.events.js';
import { cotizacionApi }            from '../../api/cotizacion.api.js';
import { alerts }                   from '../../utils/alerts.js';

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
  const res = await cotizacionApi.listar();
  const cotizaciones = res.data ?? [];

  state.filas = ordenarPorEstado(cotizaciones);

  // Aplicar filtros restaurados + filtro de archivadas
  const _search = (pref?.search || '').toLowerCase().trim();
  const _estado = (pref?.estado || '').toUpperCase().trim();

  state.filtradas = state.filas.filter(c => {
    const e      = c.estado?.toUpperCase();
    const okArch = state.mostrarArchivadas || !ESTADOS_ARCHIVADOS_COT.has(e);
    if (!okArch) return false;
    const nombre   = typeof c.cliente === 'object'
      ? (c.cliente?.nombre_completo ?? '')
      : (c.cliente ?? '');
    const okSearch = !_search || nombre.toLowerCase().includes(_search) || String(c.id).includes(_search);
    const okEstado = !_estado || e === _estado;
    return okSearch && okEstado;
  });

  ui.renderStats(calcularResumenes(cotizaciones));
  ui.renderTabla(state.filtradas.slice(0, state.porPagina));
  ui.renderPaginacion(state.filtradas.length, state.pagina, state.porPagina);

} catch (e) {
  ui.renderError('No se pudieron cargar las cotizaciones. Intenta nuevamente.');
  alerts.error(e.message || 'Error de conexión con el servidor.');
}

/* ── 4. Cablear eventos (búsqueda, filtro, sort, modales) ───────────────── */
initIndex();
