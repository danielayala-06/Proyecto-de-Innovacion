/**
 * cotizacionesIndexForm.js
 * Acciones de usuario sobre el listado: eliminar, cambiar estado, navegar a editar.
 */

import { setDeleteId, getDeleteId, eliminarCotizacion, getCotizaciones } from './cotizacionesIndexManager.js';
import { render } from './cotizacionesIndexUI.js';
import { deleteCotizacion } from '../../api/cotizacionesApi.js';

// ── Confirmar eliminar (abre el modal de confirmación) ─────────────────────────
export function handleConfirmarEliminar(id) {
    const c = getCotizaciones().find(x => x.id === id);
    if (!c) return;
    setDeleteId(id);
    document.getElementById('confirmCod').textContent = `${c.codigo} — ${c.cliente}`;
    new bootstrap.Modal(document.getElementById('modalConfirm')).show();
}

// ── Eliminar definitivo → DELETE /api/cotizaciones/{id} ────────────────────────
export async function handleEliminarCotizacion() {
    const id  = getDeleteId();
    if (!id) return;

    const btn = document.querySelector('#modalConfirm .btn-danger');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Eliminando...'; }

    const ok = await deleteCotizacion(id);

    if (btn) { btn.disabled = false; btn.innerHTML = 'Sí, eliminar'; }

    if (!ok) {
        alert('Error al eliminar la cotización. Intenta de nuevo.');
        return;
    }

    // Elimina del estado local y cierra el modal
    eliminarCotizacion();
    bootstrap.Modal.getInstance(document.getElementById('modalConfirm')).hide();
    render();
}

// ── Navegar a editar ───────────────────────────────────────────────────────────
export function handleEditarCotizacion(id) {
    window.location.href = `${BASE_URL}/cotizaciones/editar/${id}`;
}

// ── Exponer al scope global (usados en onclick del HTML) ───────────────────────
window.confirmarEliminar  = handleConfirmarEliminar;
window.eliminarCotizacion = handleEliminarCotizacion;
window.editarCotizacion   = handleEditarCotizacion;
