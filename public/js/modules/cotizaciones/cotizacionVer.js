/**
 * @file  cotizacionVer.js
 * Carga y renderiza el detalle completo de una cotización en /cotizaciones/:id.
 */

import { cotizacionApi } from '../../api/cotizacion.api.js';

// ─────────────────────────────────────────────────────────────────────────────
// BADGES / HELPERS
// ─────────────────────────────────────────────────────────────────────────────

const ESTADO_STYLE = {
    PENDIENTE:  'background:#fff8ec;color:#7a4f00;border:1px solid #ffd699;',
    APROBADA:   'background:#f0fff4;color:#155724;border:1px solid #c3e6cb;',
    RECHAZADA:  'background:#fff0f0;color:#721c24;border:1px solid #f5c6cb;',
    EXPIRADA:   'background:#f4f4f4;color:#555;border:1px solid #ccc;',
    CANCELADA:  'background:#f4f4f4;color:#555;border:1px solid #ccc;',
};

const TIPO_ITEM_LABEL = { paquete: 'Paquete', producto: 'Producto', personalizado: 'Personalizado' };

function _moneda(v) { return `S/ ${parseFloat(v || 0).toFixed(2)}`; }
function _fecha(v)  {
    if (!v) return '—';
    const d = new Date(v);
    return isNaN(d) ? v : d.toLocaleDateString('es-PE', { year: 'numeric', month: 'short', day: '2-digit' });
}

// ─────────────────────────────────────────────────────────────────────────────
// RENDERIZADO
// ─────────────────────────────────────────────────────────────────────────────

function _renderCliente(cot) {
    const cl  = cot.cliente   ?? {};
    const cl2 = cot.cliente2  ?? null;
    const col = cot.colegio   ?? null;
    const el  = document.getElementById('cvCliente');
    if (!el) return;

    let html = `
        <div class="cv-row"><span>Nombre</span><strong>${cl.nombre_completo ?? '—'}</strong></div>
        <div class="cv-row"><span>${cl.tipo_documento ?? 'Doc.'}</span><strong>${cl.numero_documento ?? '—'}</strong></div>`;

    if (cl.telefono) html += `<div class="cv-row"><span>Teléfono</span><strong>${cl.telefono}</strong></div>`;
    if (cl.correo)   html += `<div class="cv-row"><span>Correo</span><strong>${cl.correo}</strong></div>`;

    if (cl2) {
        html += `
            <div class="cv-section-title" style="margin-top:14px;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-person-fill" style="font-size:.8rem;"></i> Segundo responsable
            </div>
            <div class="cv-row"><span>Nombre</span><strong>${cl2.nombre_completo ?? '—'}</strong></div>`;
        if (cl2.numero_documento) html += `<div class="cv-row"><span>${cl2.tipo_documento ?? 'Doc.'}</span><strong>${cl2.numero_documento}</strong></div>`;
        if (cl2.telefono)         html += `<div class="cv-row"><span>Teléfono</span><strong>${cl2.telefono}</strong></div>`;
        if (cl2.correo)           html += `<div class="cv-row"><span>Correo</span><strong>${cl2.correo}</strong></div>`;
    }

    if (col) {
        html += `
            <div class="cv-section-title" style="margin-top:14px;">Colegio</div>
            <div class="cv-row"><span>Nombre</span><strong>${col.nombre}</strong></div>
            <div class="cv-row"><span>Ubicación</span><strong>${[col.distrito, col.provincia].filter(Boolean).join(', ') || '—'}</strong></div>`;
    }

    el.innerHTML = html;
}

function _renderItems(cot) {
    const el = document.getElementById('cvItems');
    if (!el) return;

    const items = cot.items ?? [];
    if (!items.length) {
        el.innerHTML = '<p style="font-size:.8rem;color:var(--text-muted);">Sin ítems registrados.</p>';
        return;
    }

    const filas = items.map(it => {
        const subtotal = (it.precio_unitario * it.cantidad).toFixed(2);
        const tipoBadge = `<span style="font-size:.68rem;color:var(--text-muted);text-transform:uppercase;">
            ${TIPO_ITEM_LABEL[it.tipo_item] ?? it.tipo_item}</span>`;
        return `<tr>
            <td>${tipoBadge}<br><strong style="font-size:.82rem;">${it.descripcion ?? it.referencia_nombre ?? '—'}</strong></td>
            <td style="text-align:center;">${it.cantidad}</td>
            <td style="text-align:right;">${_moneda(it.precio_unitario)}</td>
            <td style="text-align:right;font-weight:600;">${_moneda(subtotal)}</td>
        </tr>`;
    }).join('');

    const bruto    = items.reduce((s, it) => s + it.precio_unitario * it.cantidad, 0);
    const descuento = parseFloat(cot.descuento_monto) || 0;

    const descRow = descuento > 0 ? `
        <div class="cv-total-row" style="border-top:none;padding-top:4px;font-size:.83rem;">
            <span style="color:var(--text-muted);">Subtotal</span>
            <span>${_moneda(bruto)}</span>
        </div>
        <div class="cv-total-row" style="border-top:none;padding-top:2px;font-size:.83rem;">
            <span style="color:#198754;"><i class="bi bi-tag-fill"></i> Descuento</span>
            <span style="color:#198754;">- ${_moneda(descuento)}</span>
        </div>` : '';

    el.innerHTML = `
        <table class="cv-items-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th style="text-align:center;">Cant.</th>
                    <th style="text-align:right;">Precio unit.</th>
                    <th style="text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>${filas}</tbody>
        </table>
        ${descRow}
        <div class="cv-total-row" style="${descuento > 0 ? 'padding-top:4px;' : ''}">
            <span>Total cotización</span>
            <strong>${_moneda(cot.total)}</strong>
        </div>`;
}

function _renderPromocion(cot) {
    const prom    = cot.promocion ?? null;
    const card    = document.getElementById('cvPromCard');
    const el      = document.getElementById('cvProm');
    if (!card || !el) return;

    if (!prom) { card.style.display = 'none'; return; }

    card.style.display = '';
    el.innerHTML = `
        <div class="cv-row"><span>Nombre</span><strong>${prom.nombre ?? '—'}</strong></div>
        <div class="cv-row"><span>N.° estudiantes</span><strong>${prom.num_estudiantes ?? '—'}</strong></div>`;
}


function _renderPanel(cot) {
    // Estado
    const estadoEl = document.getElementById('cvEstado');
    if (estadoEl) {
        const style = ESTADO_STYLE[cot.estado?.toUpperCase()] ?? '';
        estadoEl.innerHTML = `<span class="cv-estado-badge" style="${style}">${cot.estado ?? '—'}</span>`;
    }

    // Datos rápidos
    const setTxt = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    setTxt('cvTotal',   _moneda(cot.total));
    setTxt('cvFecha',   _fecha(cot.fecha));
    setTxt('cvUsuario', cot.usuario?.username ?? '—');
    document.getElementById('cvSubtitle').textContent = `COT-${String(cot.id).padStart(5, '0')}`;

    // Observaciones
    const obsWrap = document.getElementById('cvObsWrap');
    const obsEl   = document.getElementById('cvObs');
    if (obsWrap && obsEl) {
        if (cot.observaciones) {
            obsWrap.style.display = '';
            obsEl.textContent     = cot.observaciones;
        } else {
            obsWrap.style.display = 'none';
        }
    }

    // Acciones
    const accEl = document.getElementById('cvAcciones');
    if (accEl) {
        const btns = [];

        if (!cot.tiene_contrato && ['PENDIENTE', 'APROBADA'].includes(cot.estado?.toUpperCase())) {
            btns.push(`<a href="${BASE_URL}contratos/crear?cotizacion=${cot.id}"
                          class="cv-btn-action cv-btn-primary">
                          <i class="bi bi-file-earmark-check"></i> Generar contrato
                       </a>`);
        }

        if (cot.tiene_contrato) {
            btns.push(`<div style="font-size:.76rem;text-align:center;color:var(--text-muted);padding:.4rem 0;">
                <i class="bi bi-check2-circle" style="color:#198754;"></i> Contrato generado
            </div>`);
        }

        if (cot.estado?.toUpperCase() === 'PENDIENTE') {
            btns.push(`<a href="${BASE_URL}cotizaciones/editar/${cot.id}"
                          class="cv-btn-action cv-btn-secondary">
                          <i class="bi bi-pencil-square"></i> Editar cotización
                       </a>`);
        }

        accEl.innerHTML = btns.join('') || '';
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────────────────────────────────────

async function init() {
    try {
        const res = await cotizacionApi.obtener(COT_ID);
        const cot = res.data;
        _renderCliente(cot);
        _renderItems(cot);
        _renderPromocion(cot);
        _renderPanel(cot);
    } catch (err) {
        document.getElementById('cvSubtitle').textContent = 'No se pudo cargar la cotización.';
        console.error(err);
    }
}

init();
