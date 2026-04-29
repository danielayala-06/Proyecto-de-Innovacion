/**
 * clientesIndexMain.js
 * Entry point del módulo listado de clientes.
 */

import { setPagina } from './clientesIndexManager.js';
import { render } from './clientesIndexUI.js';
import './clientesIndexForm.js';

document.addEventListener('DOMContentLoaded', () => {
    render();
    document.getElementById('searchInput').addEventListener('input', () => { setPagina(1); render(); });
});
