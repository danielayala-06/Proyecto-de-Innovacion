/**
 * clientesIndexForm.js
 * Acciones de usuario: abrir modal, guardar, eliminar.
 */

import { getClienteById, setDeleteId } from './clientesIndexManager.js';

// ── Abrir modal nuevo ──────────────────────────────────────────────────────────
export function handleAbrirNuevo() {
    document.getElementById('modalTitulo').textContent  = 'Nuevo cliente';
    document.getElementById('cId').value                = '';
    document.getElementById('cNombre').value            = '';
    document.getElementById('cApellido').value          = '';
    document.getElementById('cDni').value               = '';
    document.getElementById('cTelefono').value          = '';
    document.getElementById('cCorreo').value            = '';
    document.getElementById('cDireccion').value         = '';
    document.getElementById('btnElimModal').style.display = 'none';
    new bootstrap.Modal(document.getElementById('modalCliente')).show();
}

// ── Abrir modal editar ─────────────────────────────────────────────────────────
export function handleAbrirEditar(id) {
    const c = getClienteById(id);
    if (!c) return;
    document.getElementById('modalTitulo').textContent  = 'Editar cliente';
    document.getElementById('cId').value                = c.id;
    document.getElementById('cNombre').value            = c.nombre;
    document.getElementById('cApellido').value          = c.apellido;
    document.getElementById('cDni').value               = c.dni;
    document.getElementById('cTelefono').value          = c.telefono;
    document.getElementById('cCorreo').value            = c.correo;
    document.getElementById('cDireccion').value         = '';
    document.getElementById('btnElimModal').style.display = '';
    new bootstrap.Modal(document.getElementById('modalCliente')).show();
}

// ── Guardar ────────────────────────────────────────────────────────────────────
export function handleGuardarCliente() {
    // TODO: implementar cuando existan rutas POST/PUT /api/clientes
    alert('Funcionalidad en desarrollo.');
}

// ── Confirmar eliminar (desde tabla) ──────────────────────────────────────────
export function handleConfirmarEliminarCliente(id) {
    const c = getClienteById(id);
    if (!c) return;
    setDeleteId(id);
    document.getElementById('confirmNombre').textContent = `${c.nombre} ${c.apellido}`;
    new bootstrap.Modal(document.getElementById('modalConfirm')).show();
}

// ── Confirmar eliminar (desde modal editar, sin args) ─────────────────────────
function handleConfirmarEliminarModal() {
    const id = parseInt(document.getElementById('cId').value);
    if (!id) return;
    bootstrap.Modal.getInstance(document.getElementById('modalCliente'))?.hide();
    handleConfirmarEliminarCliente(id);
}

// ── Eliminar definitivo ────────────────────────────────────────────────────────
export function handleEliminarCliente() {
    // TODO: implementar cuando existan rutas DELETE /api/clientes/{id}
    alert('Funcionalidad en desarrollo.');
    bootstrap.Modal.getInstance(document.getElementById('modalConfirm'))?.hide();
}

// ── Exponer al scope global ────────────────────────────────────────────────────
window.abrirNuevo               = handleAbrirNuevo;
window.abrirEditar              = handleAbrirEditar;
window.guardarCliente           = handleGuardarCliente;
window.confirmarEliminarCliente = handleConfirmarEliminarCliente;
window.confirmarEliminar        = handleConfirmarEliminarModal;
window.eliminarCliente          = handleEliminarCliente;
