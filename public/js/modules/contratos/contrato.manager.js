const FILTER_KEY = 'con_filtros_index';

export const manager = {
  guardarFiltros(data) {
    try { localStorage.setItem(FILTER_KEY, JSON.stringify(data)); } catch (_) {}
  },
  cargarFiltros() {
    try { return JSON.parse(localStorage.getItem(FILTER_KEY)); } catch (_) { return null; }
  },
};
