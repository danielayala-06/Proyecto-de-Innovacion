import { state }     from './cotizacion.state.js';
import { ui }        from './cotizacion.ui.js';
import { initIndex } from './cotizacion.events.js';

// Datos inyectados por PHP en el layout de la vista
const cotizaciones = window.COTIZACIONES_DATA ?? [];
const resumenes    = window.RESUMENES_DATA    ?? {};

state.filas     = cotizaciones;
state.filtradas = [...cotizaciones];

ui.renderStats(resumenes);
ui.renderTabla(state.filtradas.slice(0, state.porPagina));
ui.renderPaginacion(state.filtradas.length, state.pagina, state.porPagina);

initIndex();
