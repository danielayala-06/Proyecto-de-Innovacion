/**
 * paquetesUI.js
 * Render del DOM y apertura de modales.
 * Importa únicamente desde paquetesManager.
 */

import {
    getPaquetes, setEditId, catClass,
    filtrarPaquetes, getStats, toggleEstadoPaquete,
} from './paquetesManager.js';

// ── Helpers internos ───────────────────────────────────────────────────────────
function _getFiltros() {
    return {
        q:   document.getElementById('searchInput').value,
        cat: document.getElementById('filterCat').value,
        est: document.getElementById('filterEstado').value,
    };
}

// ── Stats ──────────────────────────────────────────────────────────────────────
export function renderStats() {
    const { total, activos, prom, max } = getStats();
    document.getElementById('statTotal').textContent    = total;
    document.getElementById('statActivos').textContent  = activos;
    document.getElementById('statPromedio').textContent = `S/ ${Math.round(prom)}`;
    document.getElementById('statMax').textContent      = `S/ ${max.toFixed(0)}`;
}

// ── Grid de tarjetas ───────────────────────────────────────────────────────────
export function renderGrid() {
    const { q, cat, est } = _getFiltros();
    const lista = filtrarPaquetes(q, cat, est);
    const grid  = document.getElementById('paquetesGrid');
    renderStats();

    if (!lista.length) {
        grid.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-box-seam"></i>
                No se encontraron paquetes
            </div>`;
        return;
    }

    grid.innerHTML = lista.map(p => `
        <div class="paquete-card">
            <div class="pc-header">
                <div style="flex:1;min-width:0;">
                    <span class="pc-cat-badge cat-${catClass(p.categoria)}">${p.categoria}</span>
                    <div class="pc-name">${p.nombre}</div>
                    ${p.desc ? `<div class="pc-desc">${p.desc}</div>` : ''}
                </div>
                <span class="badge-${p.estado}">${p.estado === 'activo' ? 'Activo' : 'Inactivo'}</span>
            </div>

            <div class="pc-items">
                <div class="pc-items-title">Incluye</div>
                ${p.items.slice(0, 4).map(it =>
                    `<div class="pc-item-row"><i class="bi bi-check2"></i><span>${it}</span></div>`
                ).join('')}
                ${p.items.length > 4
                    ? `<div style="font-size:0.72rem;color:var(--text-muted);margin-top:3px;">+${p.items.length - 4} más...</div>`
                    : ''}
                ${p.duracion ? `
                <div class="pc-item-row" style="margin-top:6px;border-top:1px solid var(--border);padding-top:6px;">
                    <i class="bi bi-clock"></i><span style="color:var(--text-muted);">${p.duracion}</span>
                </div>` : ''}
            </div>

            <div class="pc-footer">
                <div class="pc-price">S/ ${p.precio.toFixed(2)} <span>/ paquete</span></div>
                <div class="pc-actions">
                    <button class="btn-icon" title="Editar" onclick="abrirEditar(${p.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn-icon" title="${p.estado === 'activo' ? 'Desactivar' : 'Activar'}"
                        onclick="toggleEstado(${p.id})">
                        <i class="bi bi-${p.estado === 'activo' ? 'toggle-on' : 'toggle-off'}"></i>
                    </button>
                    <button class="btn-icon danger" title="Eliminar" onclick="confirmarEliminarCard(${p.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>`).join('');
}

// ── Items del modal ────────────────────────────────────────────────────────────
export function renderItemsModal(items) {
    document.getElementById('itemsContainer').innerHTML = items.map((it, i) => `
        <div class="item-row-modal">
            <input type="text" class="form-control item-input" value="${it}" placeholder="Ej: 50 fotos editadas">
            <button class="btn-remove-item" onclick="quitarItem(${i})"><i class="bi bi-x-lg"></i></button>
        </div>`).join('');
}

// ── Apertura de modales ────────────────────────────────────────────────────────
export function abrirModalNuevo() {
    setEditId(null);
    document.getElementById('modalTitulo').textContent        = 'Nuevo paquete';
    document.getElementById('pId').value        = '';
    document.getElementById('pNombre').value    = '';
    document.getElementById('pCategoria').value = 'Quinceañeros';
    document.getElementById('pDesc').value      = '';
    document.getElementById('pPrecio').value    = '';
    document.getElementById('pEstado').value    = 'activo';
    document.getElementById('pDuracion').value  = '';
    document.getElementById('btnEliminarModal').style.display = 'none';
    renderItemsModal(['']);
    new bootstrap.Modal(document.getElementById('modalPaquete')).show();
}

export function abrirModalEditar(id) {
    const p = getPaquetes().find(x => x.id === id);
    if (!p) return;
    setEditId(id);
    document.getElementById('modalTitulo').textContent        = 'Editar paquete';
    document.getElementById('pId').value        = id;
    document.getElementById('pNombre').value    = p.nombre;
    document.getElementById('pCategoria').value = p.categoria;
    document.getElementById('pDesc').value      = p.desc;
    document.getElementById('pPrecio').value    = p.precio;
    document.getElementById('pEstado').value    = p.estado;
    document.getElementById('pDuracion').value  = p.duracion;
    document.getElementById('btnEliminarModal').style.display = '';
    renderItemsModal(p.items.length ? p.items : ['']);
    new bootstrap.Modal(document.getElementById('modalPaquete')).show();
}

// ── Toggle estado desde la tarjeta ─────────────────────────────────────────────
function handleToggleEstado(id) {
    toggleEstadoPaquete(id);
    renderGrid();
}

// ── Exponer al scope global (usados en onclick del HTML) ───────────────────────
window.abrirNuevo   = abrirModalNuevo;
window.abrirEditar  = abrirModalEditar;
window.toggleEstado = handleToggleEstado;
