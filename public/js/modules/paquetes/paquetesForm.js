/**
 * paquetesForm.js
 * Validación de inputs del modal y operaciones guardar/eliminar.
 * Importa del manager (lógica) y de UI (render).
 */

import { getEditId, setDeleteId, guardarPaquete, eliminarPaquete, getPaquetes } from './paquetesManager.js';
import { renderGrid, renderItemsModal } from './paquetesUI.js';

// ── Items del modal ────────────────────────────────────────────────────────────
export function getItemsModal() {
    return [...document.querySelectorAll('.item-input')].map(inp => inp.value.trim());
}

export function handleAgregarItem() {
    const items = getItemsModal();
    items.push('');
    renderItemsModal(items);
    const inputs = document.querySelectorAll('.item-input');
    if (inputs.length) inputs[inputs.length - 1].focus();
}

export function handleQuitarItem(i) {
    const items = getItemsModal();
    items.splice(i, 1);
    renderItemsModal(items);
}

// ── Guardar paquete ────────────────────────────────────────────────────────────
export function handleGuardar() {
    const nombre = document.getElementById('pNombre').value.trim();
    const precio = parseFloat(document.getElementById('pPrecio').value) || 0;
    if (!nombre) { alert('Ingresa el nombre del paquete'); return; }

    const editId = getEditId();
    const datos = {
        id:        editId || null,
        nombre,
        categoria: document.getElementById('pCategoria').value,
        desc:      document.getElementById('pDesc').value.trim(),
        precio,
        estado:    document.getElementById('pEstado').value,
        duracion:  document.getElementById('pDuracion').value.trim(),
        items:     getItemsModal().filter(it => it !== ''),
    };

    guardarPaquete(datos);
    bootstrap.Modal.getInstance(document.getElementById('modalPaquete')).hide();
    renderGrid();
}

// ── Confirmar eliminar (desde el modal de edición) ─────────────────────────────
export function handleConfirmarEliminar() {
    const editId = getEditId();
    if (!editId) return;
    const p = getPaquetes().find(x => x.id === editId);
    if (!p) return;
    setDeleteId(editId);
    document.getElementById('confirmNombre').textContent = p.nombre;
    bootstrap.Modal.getInstance(document.getElementById('modalPaquete')).hide();
    setTimeout(() => new bootstrap.Modal(document.getElementById('modalConfirm')).show(), 300);
}

// ── Confirmar eliminar (desde la tarjeta) ──────────────────────────────────────
export function handleConfirmarEliminarCard(id) {
    const p = getPaquetes().find(x => x.id === id);
    if (!p) return;
    setDeleteId(id);
    document.getElementById('confirmNombre').textContent = p.nombre;
    new bootstrap.Modal(document.getElementById('modalConfirm')).show();
}

// ── Eliminar definitivo (desde modal de confirmación) ─────────────────────────
export function handleEliminar() {
    eliminarPaquete();
    bootstrap.Modal.getInstance(document.getElementById('modalConfirm')).hide();
    renderGrid();
}

// ── Exponer al scope global (usados en onclick del HTML) ───────────────────────
window.guardarPaquete        = handleGuardar;
window.confirmarEliminar     = handleConfirmarEliminar;
window.confirmarEliminarCard = handleConfirmarEliminarCard;
window.eliminarPaquete       = handleEliminar;
window.agregarItemModal      = handleAgregarItem;
window.quitarItem            = handleQuitarItem;
