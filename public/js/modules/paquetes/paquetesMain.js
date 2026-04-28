/**
 * paquetesMain.js
 * Entry point del módulo paquetes.
 * Conecta los eventos del DOM con el manager y la UI.
 */

import { renderGrid } from './paquetesUI.js';
import './paquetesForm.js'; // registra window.guardarPaquete, confirmarEliminar, etc.

document.addEventListener('DOMContentLoaded', () => {

    // ── Render inicial ─────────────────────────────────────────────────────
    renderGrid();

    // ── Filtros y búsqueda ─────────────────────────────────────────────────
    document.getElementById('searchInput').addEventListener('input', renderGrid);
    document.getElementById('filterCat').addEventListener('change', renderGrid);
    document.getElementById('filterEstado').addEventListener('change', renderGrid);

});
