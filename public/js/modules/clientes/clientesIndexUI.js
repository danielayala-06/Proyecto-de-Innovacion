/**
 * clientesIndexUI.js
 * Render del DOM para el listado de clientes.
 */

import {
    getClientes, filtrar,
    getSortCol, setSortCol, getSortAsc, setSortAsc,
    getPagina, setPagina, POR_PAGINA,
} from './clientesIndexManager.js';

const _SORT_COLS = ['nombre', 'apellido', 'dni'];

function _getFiltro() {
    return document.getElementById('searchInput').value;
}

function _iniciales(nombre, apellido) {
    return ((nombre[0] || '') + (apellido[0] || '')).toUpperCase() || '?';
}

// ── Render principal ───────────────────────────────────────────────────────────
export function render() {
    const q         = _getFiltro();
    const todos     = filtrar(q);
    const total     = todos.length;
    const totalPags = Math.max(1, Math.ceil(total / POR_PAGINA));

    if (getPagina() > totalPags) setPagina(totalPags);

    const inicio = (getPagina() - 1) * POR_PAGINA;
    const pag    = todos.slice(inicio, inicio + POR_PAGINA);

    _renderStats(total, pag.length);
    _renderSortIcons();
    _renderTabla(pag);
    _renderPaginacion(total, inicio, totalPags);
}

// ── Stats ──────────────────────────────────────────────────────────────────────
function _renderStats(filtrado, enPagina) {
    document.getElementById('statTotal').textContent  = getClientes().length;
    document.getElementById('statFiltro').textContent = filtrado;
    document.getElementById('statPagina').textContent = enPagina;
}

// ── Sort icons ─────────────────────────────────────────────────────────────────
function _renderSortIcons() {
    const col = getSortCol();
    const asc = getSortAsc();
    _SORT_COLS.forEach(c => {
        const el = document.getElementById('sort-' + c);
        if (!el) return;
        const th = el.closest('th');
        if (col === c) {
            el.className = `bi bi-arrow-${asc ? 'up' : 'down'} sort-icon`;
            th?.classList.add('sorted');
        } else {
            el.className = 'bi bi-arrow-down-up sort-icon';
            th?.classList.remove('sorted');
        }
    });
}

// ── Tabla ──────────────────────────────────────────────────────────────────────
function _renderTabla(pag) {
    const tbody = document.getElementById('tablaBody');
    if (!pag.length) {
        tbody.innerHTML = `
            <tr><td colspan="7" style="text-align:center;padding:2.5rem;color:var(--text-muted);">
                <i class="bi bi-person-x" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                No se encontraron clientes
            </td></tr>`;
        return;
    }
    tbody.innerHTML = pag.map(c => `
        <tr>
            <td>
                <div style="width:34px;height:34px;border-radius:50%;background:var(--accent-light);
                            display:flex;align-items:center;justify-content:center;
                            font-size:0.72rem;font-weight:700;color:var(--accent-text);flex-shrink:0;">
                    ${_iniciales(c.nombre, c.apellido)}
                </div>
            </td>
            <td style="font-weight:600;color:var(--text-primary);">${c.nombre || '—'}</td>
            <td style="color:var(--text-secondary);">${c.apellido || '—'}</td>
            <td style="font-family:monospace;font-size:0.85rem;color:var(--text-secondary);">${c.dni || '—'}</td>
            <td style="color:var(--text-secondary);">${c.telefono || '—'}</td>
            <td style="color:var(--text-muted);font-size:0.83rem;">${c.correo || '—'}</td>
            <td>
                <div class="cot-actions">
                    <button class="btn-icon" title="Editar" onclick="abrirEditar(${c.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn-icon danger" title="Eliminar" onclick="confirmarEliminarCliente(${c.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`).join('');
}

// ── Paginación ─────────────────────────────────────────────────────────────────
function _renderPaginacion(total, inicio, totalPags) {
    const p = getPagina();
    document.getElementById('paginaInfo').textContent =
        total === 0 ? 'Sin resultados' : `Mostrando ${inicio + 1}–${Math.min(inicio + POR_PAGINA, total)} de ${total}`;

    let html = `<button class="pag-btn ${p === 1 ? 'disabled' : ''}" onclick="irPagClientes(${p - 1})"><i class="bi bi-chevron-left"></i></button>`;
    for (let i = 1; i <= totalPags; i++) {
        if (totalPags <= 7 || i === 1 || i === totalPags || Math.abs(i - p) <= 1)
            html += `<button class="pag-btn ${i === p ? 'active' : ''}" onclick="irPagClientes(${i})">${i}</button>`;
        else if (Math.abs(i - p) === 2)
            html += `<button class="pag-btn disabled" style="cursor:default;">…</button>`;
    }
    html += `<button class="pag-btn ${p === totalPags ? 'disabled' : ''}" onclick="irPagClientes(${p + 1})"><i class="bi bi-chevron-right"></i></button>`;
    document.getElementById('paginaBtns').innerHTML = html;
}

// ── Handlers globales ──────────────────────────────────────────────────────────
function handleSortBy(col) {
    getSortCol() === col ? setSortAsc(!getSortAsc()) : (setSortCol(col), setSortAsc(true));
    setPagina(1);
    render();
}

function handleIrPag(p) {
    const max = Math.max(1, Math.ceil(filtrar(_getFiltro()).length / POR_PAGINA));
    if (p < 1 || p > max) return;
    setPagina(p);
    render();
}

window.sortBy        = handleSortBy;
window.irPagClientes = handleIrPag;
