const DRAFT_KEY = 'cot_borrador_crear';

export const manager = {
  guardar(data) {
    try { localStorage.setItem(DRAFT_KEY, JSON.stringify(data)); } catch (_) {}
  },

  cargar() {
    try { return JSON.parse(localStorage.getItem(DRAFT_KEY)); } catch (_) { return null; }
  },

  limpiar() {
    localStorage.removeItem(DRAFT_KEY);
  },
};
