/**
 * calendarioMain.js
 * Entry point del módulo calendario.
 * Conecta los eventos del DOM con el manager y la UI.
 */

import { navegarMes, irAHoy, setVista } from './calendarioManager.js';
import { render } from './calendarioUI.js';
import './calendarioForm.js'; // registra window.guardarEvento y window.eliminarEvento

document.addEventListener('DOMContentLoaded', () => {

    // ── Render inicial ─────────────────────────────────────────────
    render();

    // ── Navegación de mes ──────────────────────────────────────────
    document.getElementById('prevBtn').addEventListener('click', () => {
        navegarMes(-1);
        render();
    });
    document.getElementById('nextBtn').addEventListener('click', () => {
        navegarMes(1);
        render();
    });
    document.getElementById('todayBtn').addEventListener('click', () => {
        irAHoy();
        render();
    });

    // ── Cambio de vista (Mes / Lista) ──────────────────────────────
    document.getElementById('btnMes').addEventListener('click', () => {
        setVista('mes');
        document.getElementById('btnMes').classList.add('active');
        document.getElementById('btnLista').classList.remove('active');
        document.getElementById('viewMes').style.display   = '';
        document.getElementById('viewLista').style.display = 'none';
        render();
    });
    document.getElementById('btnLista').addEventListener('click', () => {
        setVista('lista');
        document.getElementById('btnLista').classList.add('active');
        document.getElementById('btnMes').classList.remove('active');
        document.getElementById('viewLista').style.display = '';
        document.getElementById('viewMes').style.display   = 'none';
        render();
    });

    // ── Filtros de tipo de evento ──────────────────────────────────
    document.querySelectorAll('.filter-cb').forEach(cb => {
        cb.addEventListener('change', render);
    });

});
