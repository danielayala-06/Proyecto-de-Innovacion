/**
 * contratosIndexUI.js
 * Render del DOM para el listado de contratos.
 */

import {
    getContratos, filtrar, formatFecha,
    getSortCol, setSortCol, getSortAsc, setSortAsc,
    getPagina, setPagina, POR_PAGINA,
} from './contratosIndexManager.js';

const _SORT_COLS = ['codigo','cotizacionCod','cliente','tipoEvento','fechaEvento','total','estado','creado'];

function _getFiltros() {
    return {
        q:      document.getElementById('searchInput').value,
        estado: document.getElementById('filterEstado').value,
    };
}

export function badgeEstado(e) {
    const m = {
        vigente:    ['badge-pendiente',  'Vigente'],
        completado: ['badge-aprobada',   'Completado'],
        cancelado:  ['badge-rechazada',  'Cancelado'],
    };
    const [cls, lb] = m[e] || ['badge-inactivo', e || '—'];
    return `<span class="${cls}">${lb}</span>`;
}

export function render() {
    const { q, estado } = _getFiltros();
    const todos     = filtrar(q, estado);
    const total     = todos.length;
    const totalPags = Math.max(1, Math.ceil(total / POR_PAGINA));

    if (getPagina() > totalPags) setPagina(totalPags);

    const inicio = (getPagina() - 1) * POR_PAGINA;
    const pag    = todos.slice(inicio, inicio + POR_PAGINA);

    _renderStats(todos);
    _renderSortIcons();
    _renderTabla(pag);
    _renderPaginacion(total, inicio, totalPags);
}

function _renderStats(filtrados) {
    const all = getContratos();
    document.getElementById('statTotal').textContent      = all.length;
    document.getElementById('statVigentes').textContent   = all.filter(c => c.estado === 'vigente').length;
    document.getElementById('statCompletados').textContent= all.filter(c => c.estado === 'completado').length;
    const monto = all.reduce((s, c) => s + c.total, 0);
    document.getElementById('statMonto').textContent = `S/ ${monto.toLocaleString('es-PE')}`;
}

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

function _renderTabla(pag) {
    const tbody = document.getElementById('tablaBody');
    if (!pag.length) {
        tbody.innerHTML = `
            <tr><td colspan="9" style="text-align:center;padding:2.5rem;color:var(--text-muted);">
                <i class="bi bi-file-earmark-x" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                No se encontraron contratos
            </td></tr>`;
        return;
    }
    tbody.innerHTML = pag.map(c => `
        <tr onclick="verDetalleContrato(${c.id})" style="cursor:pointer;">
            <td><span class="cot-codigo">${c.codigo}</span></td>
            <td style="font-size:0.8rem;color:var(--text-muted);">${c.cotizacionCod}</td>
            <td>
                <div style="font-weight:600;color:var(--text-primary);">${c.cliente}</div>
                <div style="font-size:0.72rem;color:var(--text-muted);">${c.telefono}</div>
            </td>
            <td style="color:var(--text-muted);font-size:0.82rem;">${c.tipoEvento || '—'}</td>
            <td style="color:var(--text-secondary);white-space:nowrap;">${formatFecha(c.fechaEvento)}</td>
            <td style="font-weight:700;color:var(--accent);">S/ ${c.total.toLocaleString('es-PE')}</td>
            <td>${badgeEstado(c.estado)}</td>
            <td style="color:var(--text-muted);font-size:0.78rem;white-space:nowrap;">${formatFecha(c.creado)}</td>
            <td>
                <div class="cot-actions" onclick="event.stopPropagation()">
                    <button class="btn-icon" title="Ver" onclick="verDetalleContrato(${c.id})">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn-icon danger" title="Cancelar" onclick="confirmarEliminarContrato(${c.id})">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </td>
        </tr>`).join('');
}

function _renderPaginacion(total, inicio, totalPags) {
    const p = getPagina();
    document.getElementById('paginaInfo').textContent =
        total === 0 ? 'Sin resultados' : `Mostrando ${inicio + 1}–${Math.min(inicio + POR_PAGINA, total)} de ${total}`;

    let html = `<button class="pag-btn ${p === 1 ? 'disabled' : ''}" onclick="irPagContratos(${p - 1})"><i class="bi bi-chevron-left"></i></button>`;
    for (let i = 1; i <= totalPags; i++) {
        if (totalPags <= 7 || i === 1 || i === totalPags || Math.abs(i - p) <= 1)
            html += `<button class="pag-btn ${i === p ? 'active' : ''}" onclick="irPagContratos(${i})">${i}</button>`;
        else if (Math.abs(i - p) === 2)
            html += `<button class="pag-btn disabled" style="cursor:default;">…</button>`;
    }
    html += `<button class="pag-btn ${p === totalPags ? 'disabled' : ''}" onclick="irPagContratos(${p + 1})"><i class="bi bi-chevron-right"></i></button>`;
    document.getElementById('paginaBtns').innerHTML = html;
}

function handleSortBy(col) {
    getSortCol() === col ? setSortAsc(!getSortAsc()) : (setSortCol(col), setSortAsc(true));
    setPagina(1);
    render();
}

function handleIrPag(p) {
    const { q, estado } = _getFiltros();
    const max = Math.max(1, Math.ceil(filtrar(q, estado).length / POR_PAGINA));
    if (p < 1 || p > max) return;
    setPagina(p);
    render();
}

window.sortBy          = handleSortBy;
window.irPagContratos  = handleIrPag;
