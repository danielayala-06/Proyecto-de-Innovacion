/**
 * contratosIndexMain.js
 * Entry point del módulo listado de contratos.
 */

import { setPagina } from './contratosIndexManager.js';
import { render } from './contratosIndexUI.js';
import './contratosIndexForm.js';

document.addEventListener('DOMContentLoaded', () => {
    render();
    document.getElementById('searchInput').addEventListener('input',  () => { setPagina(1); render(); });
    document.getElementById('filterEstado').addEventListener('change', () => { setPagina(1); render(); });
});
