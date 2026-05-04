/**
 * contratosIndexForm.js
 * Acciones de usuario sobre contratos: generar, ver detalle, cancelar.
 */

import {
    getContratoById, setDeleteId, getDeleteId,
    agregarContrato, actualizarEstadoContrato, eliminarContratoLocal, formatFecha,
} from './contratosIndexManager.js';
import { badgeEstado, render } from './contratosIndexUI.js';
import {
    getCotizacionesDisponibles, createContrato,
    updateEstadoContrato, deleteContrato,
} from '../../api/contratosApi.js';

let _cotizacionSeleccionada = null;

// ── Modal 1: listar cotizaciones disponibles ──────────────────────────────────
export async function handleAbrirModalCotizaciones() {
    const contenedor = document.getElementById('cotizacionesDisponibles');
    contenedor.innerHTML = `<div class="text-center py-3" style="color:var(--text-muted);">
        <span class="spinner-border spinner-border-sm me-2"></span>Cargando...
    </div>`;

    new bootstrap.Modal(document.getElementById('modalCotizaciones')).show();

    const data = await getCotizacionesDisponibles();

    if (!data || !data.length) {
        contenedor.innerHTML = `<p style="text-align:center;color:var(--text-muted);padding:1rem;">
            No hay cotizaciones aprobadas disponibles para generar contrato.
        </p>`;
        return;
    }

    contenedor.innerHTML = data.map(c => `
        <div onclick="seleccionarCotizacion(${c.id_cotizacion})"
             style="border:1px solid var(--border);border-radius:8px;padding:12px 14px;margin-bottom:8px;
                    cursor:pointer;transition:background .15s;"
             onmouseover="this.style.background='var(--bg-hover)'"
             onmouseout="this.style.background=''">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <span class="cot-codigo">${'COT-' + String(c.id_cotizacion).padStart(3, '0')}</span>
                    <span style="margin-left:8px;font-weight:600;color:var(--text-primary);">${c.cliente}</span>
                </div>
                <span style="font-weight:700;color:var(--accent);">S/ ${parseFloat(c.total_estimado).toLocaleString('es-PE')}</span>
            </div>
            ${c.nombre_cotizacion ? `<div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">${c.nombre_cotizacion}</div>` : ''}
            ${c.fecha_hora_inicio ? `<div style="font-size:0.78rem;color:var(--text-secondary);margin-top:2px;">
                <i class="bi bi-calendar3 me-1"></i>${(c.fecha_hora_inicio || '').slice(0, 10)}
            </div>` : ''}
        </div>`).join('');

    // Filtro de búsqueda dentro del modal
    document.getElementById('searchCotModal').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        contenedor.querySelectorAll('[onclick^="seleccionarCotizacion"]').forEach(el => {
            el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

// ── Seleccionar cotización → abrir Modal 2 ────────────────────────────────────
export function handleSeleccionarCotizacion(cotData) {
    _cotizacionSeleccionada = cotData;

    bootstrap.Modal.getInstance(document.getElementById('modalCotizaciones'))?.hide();

    document.getElementById('cotResumen').innerHTML = `
        <div style="background:var(--bg-input);border-radius:8px;padding:12px 14px;margin-bottom:1rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="cot-codigo">${'COT-' + String(cotData.id_cotizacion).padStart(3, '0')}</span>
                <span style="font-weight:700;color:var(--accent);">
                    S/ ${parseFloat(cotData.total_estimado).toLocaleString('es-PE')}
                </span>
            </div>
            <div style="font-weight:600;color:var(--text-primary);margin-top:4px;">${cotData.cliente}</div>
            ${cotData.nombre_cotizacion ? `<div style="font-size:0.78rem;color:var(--text-muted);">${cotData.nombre_cotizacion}</div>` : ''}
        </div>`;

    document.getElementById('contratoFechaFirma').value = new Date().toISOString().slice(0, 10);
    document.getElementById('contratoAdelanto').value   = '';
    document.getElementById('contratoObservaciones').value = '';

    new bootstrap.Modal(document.getElementById('modalGenerarContrato')).show();
}

export function handleVolverACotizaciones() {
    bootstrap.Modal.getInstance(document.getElementById('modalGenerarContrato'))?.hide();
    handleAbrirModalCotizaciones();
}

// ── Confirmar creación de contrato ────────────────────────────────────────────
export async function handleConfirmarContrato() {
    if (!_cotizacionSeleccionada) return;

    const btn = document.querySelector('#modalGenerarContrato .btn-primary');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando...'; }

    const payload = {
        id_cotizacion:  _cotizacionSeleccionada.id_cotizacion,
        fecha_contrato: document.getElementById('contratoFechaFirma').value,
        adelanto:       parseFloat(document.getElementById('contratoAdelanto').value) || 0,
        observaciones:  document.getElementById('contratoObservaciones').value,
    };

    const nuevo = await createContrato(payload);

    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Generar contrato'; }

    if (!nuevo) {
        alert('Error al generar el contrato. Intenta de nuevo.');
        return;
    }

    agregarContrato(nuevo);
    bootstrap.Modal.getInstance(document.getElementById('modalGenerarContrato'))?.hide();
    _cotizacionSeleccionada = null;
    render();
}

// ── Ver detalle ───────────────────────────────────────────────────────────────
export function handleVerDetalle(id) {
    const c = getContratoById(id);
    if (!c) return;

    document.getElementById('detalleTitle').innerHTML =
        `<span style="color:var(--text-muted);font-weight:400;margin-right:6px;">${c.codigo}</span>${c.cliente}`;

    document.getElementById('detalleBody').innerHTML = `
        <div class="row g-3">
            <div class="col-md-6"><p class="cot-detail-label">Cliente</p><p class="cot-detail-val">${c.cliente}</p></div>
            <div class="col-md-6"><p class="cot-detail-label">Teléfono</p><p class="cot-detail-val">${c.telefono || '—'}</p></div>
            <div class="col-md-6"><p class="cot-detail-label">Cotización</p><p class="cot-detail-val">${c.cotizacionCod}${c.cotizacionNombre ? ' — ' + c.cotizacionNombre : ''}</p></div>
            <div class="col-md-6"><p class="cot-detail-label">Estado</p><p class="cot-detail-val">${badgeEstado(c.estado)}</p></div>
            <div class="col-md-6"><p class="cot-detail-label">Fecha del contrato</p><p class="cot-detail-val">${formatFecha(c.creado)}</p></div>
            <div class="col-md-6"><p class="cot-detail-label">Fecha del evento</p><p class="cot-detail-val">${formatFecha(c.fechaEvento)}</p></div>
            <div class="col-12 d-flex justify-content-between align-items-end" style="border-top:1px solid var(--border);padding-top:12px;margin-top:4px;">
                <div>${c.observaciones ? `<p class="cot-detail-label">Observaciones</p><p style="font-size:0.83rem;color:var(--text-secondary);">${c.observaciones}</p>` : ''}</div>
                <div style="text-align:right;">
                    <p class="cot-detail-label">Adelanto</p>
                    <p style="font-size:1rem;color:var(--text-secondary);">S/ ${c.adelanto.toLocaleString('es-PE')}</p>
                    <p class="cot-detail-label" style="margin-top:6px;">Total final</p>
                    <p style="font-size:1.4rem;font-weight:800;color:var(--accent);">S/ ${c.total.toLocaleString('es-PE')}</p>
                </div>
            </div>
        </div>`;

    document.getElementById('detalleAcciones').innerHTML = `
        ${c.estado === 'vigente' ? `
            <button class="btn btn-success btn-sm" onclick="cambiarEstadoContrato(${c.id},'completado')">
                <i class="bi bi-check2-all me-1"></i>Completar
            </button>` : ''}`;

    new bootstrap.Modal(document.getElementById('modalDetalle')).show();
}

// ── Cambiar estado ────────────────────────────────────────────────────────────
export async function handleCambiarEstado(id, estado) {
    const ok = await updateEstadoContrato(id, estado.toUpperCase());
    if (ok) {
        actualizarEstadoContrato(id, estado);
        bootstrap.Modal.getInstance(document.getElementById('modalDetalle'))?.hide();
        render();
    }
}

// ── Confirmar eliminar ────────────────────────────────────────────────────────
export function handleConfirmarEliminar(id) {
    const c = getContratoById(id);
    if (!c) return;
    setDeleteId(id);
    document.getElementById('confirmCod').textContent = c.codigo;
    new bootstrap.Modal(document.getElementById('modalConfirm')).show();
}

// ── Eliminar (cancelar) definitivo ────────────────────────────────────────────
export async function handleEliminarContrato() {
    const id  = getDeleteId();
    if (!id) return;

    const btn = document.querySelector('#modalConfirm .btn-danger');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Cancelando...'; }

    const ok = await deleteContrato(id);

    if (btn) { btn.disabled = false; btn.innerHTML = 'Sí, eliminar'; }

    if (!ok) { alert('Error al cancelar el contrato. Intenta de nuevo.'); return; }

    eliminarContratoLocal();
    bootstrap.Modal.getInstance(document.getElementById('modalConfirm'))?.hide();
    render();
}

// ── Scope global ──────────────────────────────────────────────────────────────
window.abrirModalCotizaciones    = handleAbrirModalCotizaciones;
window.seleccionarCotizacion     = (id) => {
    // Recupera el objeto de cotización del DOM para pasarlo al handler
    const cots = document.querySelectorAll('#cotizacionesDisponibles [onclick^="seleccionarCotizacion"]');
    // Reconstruye desde la API directamente como ya fue renderizado
    const btn  = [...cots].find(el => el.getAttribute('onclick') === `seleccionarCotizacion(${id})`);
    handleSeleccionarCotizacion({ id_cotizacion: id, total_estimado: 0, cliente: '', nombre_cotizacion: '' });
};
window.volverACotizaciones       = handleVolverACotizaciones;
window.confirmarContrato         = handleConfirmarContrato;
window.verDetalleContrato        = handleVerDetalle;
window.cambiarEstadoContrato     = handleCambiarEstado;
window.confirmarEliminarContrato = handleConfirmarEliminar;
window.eliminarContrato          = handleEliminarContrato;
