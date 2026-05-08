const DRAFT_KEY  = 'cot_borrador_crear';
const FILTER_KEY = 'cot_filtros_index';

export const manager = {
  /* ── Borrador del formulario crear ──────────────────────────── */
  guardar(data) {
    try { localStorage.setItem(DRAFT_KEY, JSON.stringify(data)); } catch (_) {}
  },
  cargar() {
    try { return JSON.parse(localStorage.getItem(DRAFT_KEY)); } catch (_) { return null; }
  },
  limpiar() {
    localStorage.removeItem(DRAFT_KEY);
  },

  /* ── Preferencias de filtro del index ───────────────────────── */
  guardarFiltros(data) {
    try { localStorage.setItem(FILTER_KEY, JSON.stringify(data)); } catch (_) {}
  },
  cargarFiltros() {
    try { return JSON.parse(localStorage.getItem(FILTER_KEY)); } catch (_) { return null; }
  },
};
