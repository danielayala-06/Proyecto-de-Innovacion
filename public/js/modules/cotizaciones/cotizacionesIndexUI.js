/**
 * cotizacionesIndexUI.js
 * Render del DOM para el listado de cotizaciones.
 * Importa únicamente desde cotizacionesIndexManager.
 */

import {
    getCotizaciones, filtrar, formatFecha,
    getSortCol, setSortCol, getSortAsc, setSortAsc,
    getPagina, setPagina, POR_PAGINA,
    cambiarEstadoCotizacion,
} from './cotizacionesIndexManager.js';
import { getCotizacionById } from '../../api/cotizacionesApi.js';

const _SORT_COLS = ['codigo','cliente','fecha','total','estado','creado'];

// ── Helpers internos ───────────────────────────────────────────────────────────
function _getFiltros() {
    return {
        q:   document.getElementById('searchInput').value,
        est: document.getElementById('filterEstado').value,
    };
}

export function badgeEstado(e) {
    const m = {
        pendiente:  ['badge-pendiente',  'Pendiente'],
        aprobada:   ['badge-aprobada',   'Aprobada'],
        rechazada:  ['badge-rechazada',  'Rechazada'],
        completada: ['badge-completada', 'Completada'],
    };
    const [cls, lb] = m[e] || ['badge-inactivo', e || '—'];
    return `<span class="${cls}">${lb}</span>`;
}

// ── Render principal ───────────────────────────────────────────────────────────
export function render() {
    const { q, est } = _getFiltros();
    const todos     = filtrar(q, est, '');
    const total     = todos.length;
    const totalPags = Math.max(1, Math.ceil(total / POR_PAGINA));

    if (getPagina() > totalPags) setPagina(totalPags);

    const inicio = (getPagina() - 1) * POR_PAGINA;
    const pag    = todos.slice(inicio, inicio + POR_PAGINA);

    _renderStats();
    _renderSortIcons();
    _renderTabla(pag);
    _renderPaginacion(total, inicio, totalPags);
}

// ── Stats ──────────────────────────────────────────────────────────────────────
function _renderStats() {
    // Usa los resúmenes precalculados por PHP (reflejan toda la BD, no solo la página)
    if (typeof RESUMENES_DATA !== 'undefined' && RESUMENES_DATA) {
        document.getElementById('statTotal').textContent      = RESUMENES_DATA.total_cotizaciones ?? 0;
        document.getElementById('statPend').textContent       = RESUMENES_DATA.pendientes         ?? 0;
        document.getElementById('statAprobadas').textContent  = RESUMENES_DATA.aprobadas          ?? 0;
        document.getElementById('statRechazadas').textContent = RESUMENES_DATA.rechazadas         ?? 0;
        const monto = parseFloat(RESUMENES_DATA.total_estimado) || 0;
        document.getElementById('statMonto').textContent = `S/ ${monto.toLocaleString('es-PE')}`;
        return;
    }
    // Fallback: calcula desde los datos demo locales
    const all = getCotizaciones();
    document.getElementById('statTotal').textContent      = all.length;
    document.getElementById('statPend').textContent       = all.filter(c => c.estado === 'pendiente').length;
    document.getElementById('statAprobadas').textContent  = all.filter(c => c.estado === 'aprobada').length;
    document.getElementById('statRechazadas').textContent = all.filter(c => c.estado === 'rechazada').length;
    const monto = all.filter(c => c.estado !== 'rechazada').reduce((s, c) => s + c.total, 0);
    document.getElementById('statMonto').textContent = `S/ ${monto.toLocaleString('es-PE')}`;
}

// ── Iconos de ordenamiento ─────────────────────────────────────────────────────
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
            <tr><td colspan="7" class="cot-empty">
                <i class="bi bi-file-earmark-x" style="font-size:2rem;display:block;margin-bottom:0.5rem;color:var(--border-strong);"></i>
                No se encontraron cotizaciones
            </td></tr>`;
        return;
    }
    tbody.innerHTML = pag.map(c => `
        <tr onclick="verDetalle(${c.id})">
            <td>
                <span class="cot-codigo">${c.codigo}</span>
                ${c.cotizacion ? `<div style="font-size:0.71rem;color:var(--text-muted);margin-top:2px;">${c.cotizacion}</div>` : ''}
            </td>
            <td>
                <div style="font-weight:600;color:var(--text-primary);">${c.cliente}</div>
                <div style="font-size:0.72rem;color:var(--text-muted);">${c.telefono}</div>
            </td>
            
            <td style="color:var(--text-secondary);white-space:nowrap;">${formatFecha(c.fecha)}</td>
            <td style="font-weight:700;color:var(--accent);">S/ ${c.total.toLocaleString('es-PE')}</td>
            <td>${badgeEstado(c.estado)}</td>
            <td style="color:var(--text-muted);font-size:0.78rem;white-space:nowrap;">${formatFecha(c.creado)}</td>
            <td>
                <div class="cot-actions" onclick="event.stopPropagation()">
                    <button class="btn-icon" title="Ver" onclick="verDetalle(${c.id})"><i class="bi bi-eye"></i></button>
                    <button class="btn-icon" title="Editar" onclick="editarCotizacion(${c.id})"><i class="bi bi-pencil"></i></button>
                    <button class="btn-icon danger" title="Eliminar" onclick="confirmarEliminar(${c.id})"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        </tr>`).join('');
}

// ── Paginación ─────────────────────────────────────────────────────────────────
function _renderPaginacion(total, inicio, totalPags) {
    const p = getPagina();
    document.getElementById('paginaInfo').textContent =
        total === 0 ? 'Sin resultados' : `Mostrando ${inicio + 1}–${Math.min(inicio + POR_PAGINA, total)} de ${total}`;

    let html = `<button class="pag-btn ${p === 1 ? 'disabled' : ''}" onclick="irPag(${p - 1})"><i class="bi bi-chevron-left"></i></button>`;
    for (let i = 1; i <= totalPags; i++) {
        if (totalPags <= 7 || i === 1 || i === totalPags || Math.abs(i - p) <= 1)
            html += `<button class="pag-btn ${i === p ? 'active' : ''}" onclick="irPag(${i})">${i}</button>`;
        else if (Math.abs(i - p) === 2)
            html += `<button class="pag-btn disabled" style="cursor:default;">…</button>`;
    }
    html += `<button class="pag-btn ${p === totalPags ? 'disabled' : ''}" onclick="irPag(${p + 1})"><i class="bi bi-chevron-right"></i></button>`;
    document.getElementById('paginaBtns').innerHTML = html;
}

// ── Helpers del modal ──────────────────────────────────────────────────────────
function _itemsTable(rows) {
    if (!rows.length) return '';
    return `
        <table class="cot-items-table">
            <thead><tr>
                <th>Nombre</th><th>Cant.</th><th>Precio unit.</th><th>Subtotal</th>
            </tr></thead>
            <tbody>
                ${rows.map(r => `<tr>
                    <td>${r.nombre ?? '—'}</td>
                    <td>${r.cantidad ?? 1}</td>
                    <td>S/ ${parseFloat(r.precio_unitario || 0).toLocaleString('es-PE', {minimumFractionDigits:2})}</td>
                    <td>S/ ${parseFloat(r.subtotal || 0).toLocaleString('es-PE', {minimumFractionDigits:2})}</td>
                </tr>`).join('')}
            </tbody>
        </table>`;
}

function _itemsSection(paquetes, servicios, productos) {
    const parts = [];
    if (paquetes.length)  parts.push(`<p class="cot-detail-label" style="margin-bottom:4px;">Paquetes</p>${_itemsTable(paquetes)}`);
    if (servicios.length) parts.push(`<p class="cot-detail-label" style="margin-bottom:4px;">Servicios adicionales</p>${_itemsTable(servicios)}`);
    if (productos.length) parts.push(`<p class="cot-detail-label" style="margin-bottom:4px;">Productos</p>${_itemsTable(productos)}`);
    if (!parts.length) return '';
    return `<div class="col-12" style="border-top:1px solid var(--border);padding-top:12px;">${parts.join('<div style="margin-top:12px;"></div>')}</div>`;
}

// ── Modal detalle ──────────────────────────────────────────────────────────────
export async function verDetalle(id) {
    const local  = getCotizaciones().find(x => x.id === id) || {};
    const codigo = local.codigo || `COT-${String(id).padStart(3, '0')}`;

    document.getElementById('detalleTitle').innerHTML =
        `<span style="color:var(--text-muted);font-weight:400;margin-right:6px;">${codigo}</span>${local.cliente || ''}`;
    document.getElementById('detalleBody').innerHTML =
        `<div class="text-center py-4" style="color:var(--text-muted);">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            Cargando detalle...
        </div>`;
    document.getElementById('detalleAcciones').innerHTML = '';
    new bootstrap.Modal(document.getElementById('modalDetalle')).show();

    const api = await getCotizacionById(id);

    if (!api) {
        document.getElementById('detalleBody').innerHTML =
            `<div class="text-center py-4" style="color:var(--red-text);">
                <i class="bi bi-exclamation-triangle me-2"></i>
                No se pudo cargar el detalle. Intenta de nuevo.
            </div>`;
        return;
    }

    const nombre    = api.nombre_cotizacion || local.cotizacion || '';
    const fechaIni  = (api.fecha_hora_inicio || '').slice(0, 10);
    const fechaFin  = (api.fecha_hora_fin    || '').slice(0, 10);
    const total     = parseFloat(api.total_estimado) || local.total || 0;
    const estado    = (api.estado || local.estado || '').toLowerCase();
    const direccion = api.direccion    || '';
    const referencia= api.referencia   || '';
    const notas     = api.observaciones || '';
    const cliente   = api.cliente  || local.cliente  || '—';
    const telefono  = api.telefono || local.telefono || '—';
    const paquetes  = api.paquetes  || [];
    const servicios = api.servicios || [];
    const productos = api.productos || [];

    // Título: código · cliente con nombre del evento como subtítulo
    document.getElementById('detalleTitle').innerHTML = `
        <div>
            <div>
                <span style="color:var(--text-muted);font-weight:400;margin-right:6px;">${codigo}</span>
                <span style="font-weight:700;">${cliente !== '—' ? cliente : ''}</span>
            </div>
            ${nombre ? `<span class="modal-subtitle">${nombre}</span>` : ''}
        </div>`;

    // Body: info + items + totales
    const colFechas = fechaFin ? '4' : '6';
    document.getElementById('detalleBody').innerHTML = `
        <div class="row g-3">
            <div class="col-md-6"><p class="cot-detail-label">Cliente</p><p class="cot-detail-val">${cliente}</p></div>
            <div class="col-md-6"><p class="cot-detail-label">Teléfono</p><p class="cot-detail-val">${telefono}</p></div>

            <div class="col-md-${colFechas}"><p class="cot-detail-label">Fecha inicio</p><p class="cot-detail-val">${formatFecha(fechaIni)}</p></div>
            ${fechaFin ? `<div class="col-md-${colFechas}"><p class="cot-detail-label">Fecha fin</p><p class="cot-detail-val">${formatFecha(fechaFin)}</p></div>` : ''}
            <div class="col-md-${colFechas}"><p class="cot-detail-label">Estado</p><p class="cot-detail-val">${badgeEstado(estado)}</p></div>

            ${direccion  ? `<div class="col-md-6"><p class="cot-detail-label">Dirección</p><p class="cot-detail-val">${direccion}</p></div>`  : ''}
            ${referencia ? `<div class="col-md-6"><p class="cot-detail-label">Referencia</p><p class="cot-detail-val">${referencia}</p></div>` : ''}

            ${_itemsSection(paquetes, servicios, productos)}

            <div class="col-12" style="border-top:1px solid var(--border);padding-top:12px;display:flex;justify-content:space-between;align-items:flex-end;gap:12px;">
                <div style="flex:1;">
                    ${notas ? `<p class="cot-detail-label">Observaciones</p><p style="font-size:0.83rem;color:var(--text-secondary);">${notas}</p>` : ''}
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <p class="cot-detail-label">Total estimado</p>
                    <p style="font-size:1.4rem;font-weight:800;color:var(--accent);">S/ ${total.toLocaleString('es-PE', {minimumFractionDigits:2})}</p>
                </div>
            </div>
        </div>`;

    document.getElementById('detalleAcciones').innerHTML =
        `${estado === 'pendiente' ? `
            <button class="btn btn-success btn-sm" onclick="cambiarEstado(${id},'aprobada')"><i class="bi bi-check-circle me-1"></i>Aprobar</button>
            <button class="btn btn-danger btn-sm" onclick="cambiarEstado(${id},'rechazada')"><i class="bi bi-x-circle me-1"></i>Rechazar</button>
        ` : ''}
        ${estado === 'aprobada' ? `
            <button class="btn btn-secondary btn-sm" onclick="cambiarEstado(${id},'completada')"><i class="bi bi-check2-all me-1"></i>Marcar completada</button>
        ` : ''}`;
}

// ── Handlers navegación/sort (expuestos globalmente) ──────────────────────────
function handleSortBy(col) {
    getSortCol() === col ? setSortAsc(!getSortAsc()) : (setSortCol(col), setSortAsc(true));
    setPagina(1);
    render();
}

function handleIrPag(p) {
    const { q, est } = _getFiltros();
    const max = Math.max(1, Math.ceil(filtrar(q, est, '').length / POR_PAGINA));
    if (p < 1 || p > max) return;
    setPagina(p);
    render();
}

async function handleCambiarEstado(id, estado) {
    const acciones = document.getElementById('detalleAcciones');
    const btns     = acciones?.querySelectorAll('button') ?? [];

    btns.forEach(b => { b.disabled = true; });

    const ok = await cambiarEstadoCotizacion(id, estado);

    if (!ok) {
        btns.forEach(b => { b.disabled = false; });
        alert('No se pudo cambiar el estado. Intenta de nuevo.');
        return;
    }

    bootstrap.Modal.getInstance(document.getElementById('modalDetalle')).hide();
    render();
}

// ── Exponer al scope global (usados en onclick del HTML) ───────────────────────
window.verDetalle    = verDetalle;
window.sortBy        = handleSortBy;
window.irPag         = handleIrPag;
window.cambiarEstado = handleCambiarEstado;
