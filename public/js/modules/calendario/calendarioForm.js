/**
 * calendarioForm.js
 * Validación de inputs del modal y operaciones guardar/eliminar.
 * Importa del manager (lógica) y de UI (render + cerrar modal).
 */

import { guardarEvento, eliminarEvento, getEditId, setEditId } from './calendarioManager.js';
import { render } from './calendarioUI.js';

function _cerrarModal() {
    const instance = bootstrap.Modal.getInstance(document.getElementById('modalEvento'));
    if (instance) instance.hide();
}

function _leerFormulario() {
    return {
        id:      parseInt(document.getElementById('eId').value) || null,
        titulo:  document.getElementById('eTitulo').value.trim(),
        tipo:    document.getElementById('eTipo').value,
        estado:  document.getElementById('eEstado').value,
        fecha:   document.getElementById('eFecha').value,
        hora:    document.getElementById('eHora').value,
        cliente: document.getElementById('eCliente').value.trim(),
        monto:   parseFloat(document.getElementById('eMonto').value) || 0,
        notas:   document.getElementById('eNotas').value.trim(),
    };
}

function _validar(datos) {
    if (!datos.titulo) { alert('El título es obligatorio.'); return false; }
    if (!datos.fecha)  { alert('La fecha es obligatoria.');  return false; }
    return true;
}

export function handleGuardar() {
    const datos = _leerFormulario();
    if (!_validar(datos)) return;
    guardarEvento(datos);
    _cerrarModal();
    render();
}

export function handleEliminar() {
    const id = parseInt(document.getElementById('eId').value);
    if (!id || !confirm('¿Eliminar este evento?')) return;
    eliminarEvento(id);
    _cerrarModal();
    render();
}

// ── Exponer al scope global (usados en onclick del HTML) ───────────
window.guardarEvento  = handleGuardar;
window.eliminarEvento = handleEliminar;
