import { formatters } from '../../utils/formatters.js';

const BADGE_MAP = {
  ACTIVO:     ['badge-pendiente', 'Vigente'],
  COMPLETADO: ['badge-aprobada',  'Completado'],
  CANCELADO:  ['badge-rechazada', 'Cancelado'],
};

function badgeEstado(estado) {
  const [cls, label] = BADGE_MAP[estado?.toUpperCase()] ?? ['badge-inactivo', estado ?? '—'];
  return `<span class="${cls}">${label}</span>`;
}

function nombreCot(c) {
  if (!c.cliente) return '—';
  return typeof c.cliente === 'object'
    ? (c.cliente.nombre_completo ?? '—')
    : c.cliente;
}

export const ui = {
  renderLoading() {
    const tbody = document.getElementById('tablaBody');
    if (!tbody) return;
    const fila = `<td><div class="placeholder-glow"><span class="placeholder col-8"></span></div></td>`;
    tbody.innerHTML = Array.from({ length: 5 }, () =>
      `<tr style="opacity:.5;">${fila.repeat(8)}</tr>`
    ).join('');
  },

  renderError(msg = 'Error al cargar los datos.') {
    const tbody = document.getElementById('tablaBody');
    if (!tbody) return;
    tbody.innerHTML = `
      <tr>
        <td colspan="8" class="text-center py-4" style="color:var(--red-text);font-size:.85rem;">
          <i class="bi bi-exclamation-circle me-1"></i>${msg}
        </td>
      </tr>`;
  },

  renderStats({ total, vigentes, completados, monto_total }) {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('statTotal',       total);
    set('statVigentes',    vigentes);
    set('statCompletados', completados);
    set('statMonto',       formatters.moneda(monto_total));
  },

  renderTabla(filas) {
    const tbody = document.getElementById('tablaBody');
    if (!tbody) return;

    if (!filas.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="8" class="con-empty">
            <i class="bi bi-file-earmark-x" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
            No hay contratos para mostrar.
          </td>
        </tr>`;
      return;
    }

    tbody.innerHTML = filas.map(c => {
      const cod      = formatters.codigo(c.id);
      const cotCod   = c.id_cotizacion ? formatters.codigo(c.id_cotizacion) : '—';
      const isActivo = c.estado?.toUpperCase() === 'ACTIVO';
      const editAttr = isActivo
        ? `onclick="event.stopPropagation();editarContrato(${c.id})"  title="Corregir contrato"`
        : `disabled title="Solo se puede editar un contrato vigente"`;

      return `
        <tr onclick="verDetalleContrato(${c.id})">
          <td><span class="con-codigo">${cod}</span></td>
          <td><span class="con-cot-ref-small">${cotCod}</span></td>
          <td>${c.cliente?.nombre ?? '—'}</td>
          <td style="color:var(--text-muted);">—</td>
          <td>${formatters.moneda(c.total)}</td>
          <td>${badgeEstado(c.estado)}</td>
          <td>${formatters.fecha(c.fecha_creacion)}</td>
          <td>
            <div class="con-actions">
              <button class="btn btn-sm btn-outline-secondary" title="Ver detalle"
                      onclick="event.stopPropagation();verDetalleContrato(${c.id})">
                <i class="bi bi-eye"></i>
              </button>
              <button class="btn btn-sm btn-outline-warning" ${editAttr}>
                <i class="bi bi-pencil-square"></i>
              </button>
              
            </div>
          </td>
        </tr>`;
    }).join('');
  },

  renderPaginacion(total, pagina, porPagina) {
    const totalPags = Math.ceil(total / porPagina) || 1;
    const info = document.getElementById('paginaInfo');
    const btns = document.getElementById('paginaBtns');
    if (!info || !btns) return;

    const desde = total ? (pagina - 1) * porPagina + 1 : 0;
    const hasta  = Math.min(pagina * porPagina, total);
    info.textContent = total
      ? `Mostrando ${desde}–${hasta} de ${total}`
      : 'Sin resultados';

    btns.innerHTML = Array.from({ length: totalPags }, (_, i) => i + 1)
      .map(i =>
        `<button class="pag-btn${i === pagina ? ' active' : ''}"
                 onclick="irPagina(${i})">${i}</button>`
      ).join('');
  },

  renderCotizacionesDisponibles(cotizaciones, filtro = '') {
    const el = document.getElementById('cotizacionesDisponibles');
    if (!el) return;

    const term = filtro.toLowerCase().trim();
    const visibles = cotizaciones.filter(c => {
      if (!term) return true;
      const nombre = nombreCot(c).toLowerCase();
      return nombre.includes(term) || String(c.id).includes(term);
    });

    if (!visibles.length) {
      el.innerHTML = `<div class="con-empty">No hay cotizaciones aprobadas disponibles.</div>`;
      return;
    }

    el.innerHTML = visibles.map(c => {
      const nombre = nombreCot(c);
      const cod    = formatters.codigo(c.id);
      return `
        <div class="con-cot-option" onclick="seleccionarCotizacion(${c.id})">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
            <strong style="font-size:.85rem;">${nombre}</strong>
            <span class="con-cot-ref-small">${cod}</span>
          </div>
          <div style="font-size:.78rem;color:var(--text-muted);margin-top:4px;">
            Total: <strong style="color:var(--text-primary);">${formatters.moneda(c.total)}</strong>
            ${c.fecha ? `· ${formatters.fecha(c.fecha)}` : ''}
          </div>
        </div>`;
    }).join('');
  },

  renderResumenCotizacion(cot) {
    const el = document.getElementById('cotResumen');
    if (!el) return;
    const nombre = nombreCot(cot);
    el.innerHTML = `
      <div class="con-cot-ref mb-3">
        <i class="bi bi-file-earmark-check" style="font-size:1.1rem;"></i>
        <div style="flex:1;">
          <span>Cotización seleccionada</span>
          <div style="display:flex;gap:10px;align-items:center;margin-top:3px;">
            <strong style="font-size:.9rem;">${nombre}</strong>
            <span class="con-cot-ref-small">${formatters.codigo(cot.id)}</span>
          </div>
        </div>
        <strong style="font-size:.95rem;">${formatters.moneda(cot.total)}</strong>
      </div>`;
  },

  renderDetalle(data) {
    const pagos = data.pagos ?? [];
    const filasPagos = pagos.length
      ? pagos.map(p => `
          <tr>
            <td>${formatters.fecha(p.fecha)}</td>
            <td>${p.nombre_forma_pago ?? '—'}</td>
            <td class="text-end">${formatters.moneda(p.monto)}</td>
          </tr>`).join('')
      : `<tr><td colspan="3" class="text-center text-muted py-2" style="font-size:.82rem;">Sin pagos registrados</td></tr>`;

    return `
      <div class="row g-2 mb-3" style="font-size:.84rem;">
        <div class="col-6">
          <span style="color:var(--text-muted);">Código contrato</span><br>
          <span class="con-codigo">${formatters.codigo(data.id)}</span>
        </div>
        <div class="col-6">
          <span style="color:var(--text-muted);">Estado</span><br>
          ${badgeEstado(data.estado)}
        </div>
        <div class="col-6">
          <span style="color:var(--text-muted);">Cliente</span><br>
          <strong>${data.cliente?.nombre ?? '—'}</strong>
        </div>
        <div class="col-6">
          <span style="color:var(--text-muted);">Teléfono</span><br>
          ${data.cliente?.telefono ?? '—'}
        </div>
        <div class="col-6">
          <span style="color:var(--text-muted);">Total contrato</span><br>
          <strong>${formatters.moneda(data.total)}</strong>
        </div>
        <div class="col-6">
          <span style="color:var(--text-muted);">Adelanto pagado</span><br>
          ${formatters.moneda(data.adelanto)}
        </div>
        <div class="col-6">
          <span style="color:var(--text-muted);">Saldo pendiente</span><br>
          <strong style="color:var(--amber-text);">${formatters.moneda(data.saldo)}</strong>
        </div>
        <div class="col-6">
          <span style="color:var(--text-muted);">Fecha creación</span><br>
          ${formatters.fecha(data.fecha_creacion)}
        </div>
        ${data.observaciones ? `
        <div class="col-12">
          <span style="color:var(--text-muted);">Observaciones</span><br>
          <span style="font-size:.82rem;white-space:pre-line;">${data.observaciones}</span>
        </div>` : ''}
      </div>

      <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">
        Historial de pagos
      </div>
      <div class="table-responsive">
        <table class="table table-sm" style="font-size:.81rem;">
          <thead>
            <tr><th>Fecha</th><th>Método</th><th class="text-end">Monto</th></tr>
          </thead>
          <tbody>${filasPagos}</tbody>
        </table>
      </div>
      <div class="text-end mt-1" style="font-size:.82rem;color:var(--text-muted);">
        Total pagado: <strong style="color:var(--green-text);">${formatters.moneda(data.total_pagado)}</strong>
      </div>`;
  },
};
