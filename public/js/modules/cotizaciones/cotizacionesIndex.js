import { state, calcularResumenes } from './cotizacion.state.js';
import { ui }                       from './cotizacion.ui.js';
import { manager }                  from './cotizacion.manager.js';
import { initIndex }                from './cotizacion.events.js';
import { cotizacionApi }            from '../../api/cotizacion.api.js';
import { alerts }                   from '../../utils/alerts.js';

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
  const res = await cotizacionApi.listar();
  const cotizaciones = res.data ?? [];

  // Guardar en estado global
  state.filas     = cotizaciones;
  state.filtradas = cotizaciones;

  // Aplicar filtros restaurados antes de renderizar
  if (pref?.search || pref?.estado) {
    const search = (pref.search  || '').toLowerCase().trim();
    const estado = (pref.estado  || '').toUpperCase().trim();

    state.filtradas = cotizaciones.filter(c => {
      const nombre   = typeof c.cliente === 'object'
        ? (c.cliente?.nombre_completo ?? '')
        : (c.cliente ?? '');
      const okSearch = !search || nombre.toLowerCase().includes(search) || String(c.id).includes(search);
      const okEstado = !estado || c.estado?.toUpperCase() === estado;
      return okSearch && okEstado;
    });
  }

  // Renderizar stats, tabla y paginación
  ui.renderStats(calcularResumenes(cotizaciones));
  ui.renderTabla(state.filtradas.slice(0, state.porPagina));
  ui.renderPaginacion(state.filtradas.length, state.pagina, state.porPagina);

} catch (e) {
  ui.renderError('No se pudieron cargar las cotizaciones. Intenta nuevamente.');
  alerts.error(e.message || 'Error de conexión con el servidor.');
}

/* ── 4. Cablear eventos (búsqueda, filtro, sort, modales) ────── */
initIndex();
