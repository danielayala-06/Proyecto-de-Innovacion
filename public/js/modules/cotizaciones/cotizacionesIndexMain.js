/**
 * cotizacionesIndexMain.js
 * Entry point del módulo listado de cotizaciones.
 * PHP renderiza la tabla inicial; JS toma el control al interactuar.
 */

import { setPagina } from './cotizacionesIndexManager.js';
import { render } from './cotizacionesIndexUI.js';
import './cotizacionesIndexForm.js'; // registra window.confirmarEliminar, eliminarCotizacion, editarCotizacion

document.addEventListener('DOMContentLoaded', () => {

    // No se llama render() aquí: PHP ya renderizó las filas correctamente.
    // JS toma el control solo cuando el usuario interactúa con los filtros o el sort.

    document.getElementById('searchInput').addEventListener('input', () => { setPagina(1); render(); });
    document.getElementById('filterEstado').addEventListener('change', () => { setPagina(1); render(); });

});
