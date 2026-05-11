import { formatters } from '../../utils/formatters.js';

const BADGE_MAP = {
  PENDIENTE:         ['badge-pendiente',  'Pendiente'],
  APROBADA:          ['badge-aprobada',   'Aprobada'],
  RECHAZADA:         ['badge-rechazada',  'Rechazada'],
  CONTRATO_GENERADO: ['badge-completada', 'Contrato'],
};

function badgeEstado(estado) {
  const [cls, label] = BADGE_MAP[estado?.toUpperCase()] ?? ['badge-inactivo', estado ?? '—'];
  return `<span class="${cls}">${label}</span>`;
}

/** Extrae el nombre del cliente sin importar si viene como string u objeto */
function nombreCliente(c) {
  if (!c.cliente) return '—';
  return typeof c.cliente === 'object'
    ? (c.cliente.nombre_completo ?? '—')
    : c.cliente;
}

export const ui = {
  /* ── Loading skeleton en tbody ───────────────────────────────── */
  renderLoading() {
    const tbody = document.getElementById('tablaBody');
    if (!tbody) return;
    const fila = `<td><div class="placeholder-glow"><span class="placeholder col-8"></span></div></td>`;
    tbody.innerHTML = Array.from({ length: 5 }, () =>
      `<tr style="opacity:.5;">${fila.repeat(7)}</tr>`
    ).join('');
  },

  /* ── Error en tbody ──────────────────────────────────────────── */
  renderError(msg = 'Error al cargar los datos.') {
    const tbody = document.getElementById('tablaBody');
    if (!tbody) return;
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-4" style="color:var(--red-text,#e57373);font-size:.85rem;">
          <i class="bi bi-exclamation-circle me-1"></i>${msg}
        </td>
      </tr>`;
  },

  /* ── Stats ───────────────────────────────────────────────────── */
  renderStats(r) {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('statTotal',      r.total      ?? 0);
    set('statPend',       r.borrador   ?? r.pendientes ?? 0);
    set('statAprobadas',  r.aprobadas  ?? 0);
    set('statRechazadas', r.rechazadas ?? 0);
    set('statMonto',      formatters.moneda(r.monto_total ?? 0));
  },

  /* ── Tabla ───────────────────────────────────────────────────── */
  renderTabla(filas) {
    const tbody = document.getElementById('tablaBody');
    if (!tbody) return;

    if (!filas.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7" class="text-center py-4"
              style="color:var(--text-muted);font-size:.85rem;">
            No hay cotizaciones para mostrar.
          </td>
        </tr>`;
      return;
    }

    tbody.innerHTML = filas.map(c => {
      const codigo      = formatters.codigo(c.id);
      const nombre      = nombreCliente(c);
      const estado      = c.estado?.toUpperCase();
      const canDel      = estado === 'PENDIENTE';
      const canContract = estado === 'APROBADA';

      return `
        <tr>
          <td><code style="font-size:.78rem;">${codigo}</code></td>
          <td>${nombre}</td>
          <td>${formatters.moneda(c.total)}</td>
          <td>${badgeEstado(c.estado)}</td>
          <td>${formatters.fecha(c.fecha)}</td>
          <td class="text-center">
            <div class="d-flex gap-1 justify-content-center">
              <button class="btn-accion ver" title="Ver detalle"
                      onclick="verDetalle(${c.id})">
                <i class="bi bi-eye"></i>
              </button>
              ${canContract ? `
              <button class="btn-accion" title="Generar contrato"
                      style="color:var(--accent);"
                      onclick="irAGenerarContrato(${c.id})">
                <i class="bi bi-file-earmark-plus"></i>
              </button>` : ''}
              ${canDel ? `
              <button class="btn-accion del" title="Rechazar"
                      onclick="confirmarEliminar(${c.id},'${codigo}')">
                <i class="bi bi-trash"></i>
              </button>` : ''}
            </div>
          </td>
        </tr>`;
    }).join('');
  },

  /* ── Paginación ──────────────────────────────────────────────── */
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

  /* ── Modal detalle ───────────────────────────────────────────── */
  renderDetalle(data) {
    const cot     = data.cotizacion ?? data;
    const cliente = data.cliente    ?? {};
    const det     = data.items      ?? data.detalles ?? [];

    const filasDet = det.length
      ? det.map(d => `
          <tr>
            <td><small class="text-muted">${d.tipo_item ?? '—'}</small></td>
            <td>${d.descripcion || d.referencia_nombre || '—'}</td>
            <td class="text-center">${d.cantidad ?? 0}</td>
            <td class="text-end">${formatters.moneda(d.precio_unitario)}</td>
            <td class="text-end fw-semibold">${formatters.moneda(d.subtotal ?? (d.cantidad * d.precio_unitario))}</td>
          </tr>`).join('')
      : `<tr><td colspan="5" class="text-center text-muted py-2"
              style="font-size:.82rem;">Sin ítems</td></tr>`;

    return `
      <div class="row g-2 mb-3" style="font-size:.84rem;">
        <div class="col-6">
          <span style="color:var(--text-muted);">Código</span><br>
          <strong>${formatters.codigo(cot.id)}</strong>
        </div>
        <div class="col-6">
          <span style="color:var(--text-muted);">Estado</span><br>
          ${badgeEstado(cot.estado)}
        </div>
        <div class="col-6">
          <span style="color:var(--text-muted);">Cliente</span><br>
          <span>${cliente.nombre_completo || '—'}</span>
        </div>
        <div class="col-6">
          <span style="color:var(--text-muted);">Fecha</span><br>
          <span>${formatters.fecha(cot.fecha)}</span>
        </div>
        ${cot.observaciones ? `
        <div class="col-12">
          <span style="color:var(--text-muted);">Observaciones</span><br>
          <span>${cot.observaciones}</span>
        </div>` : ''}
      </div>

      <div class="table-responsive">
        <table class="table table-sm" style="font-size:.81rem;">
          <thead>
            <tr>
              <th>Tipo</th><th>Descripción</th>
              <th class="text-center">Cant.</th>
              <th class="text-end">Precio</th>
              <th class="text-end">Subtotal</th>
            </tr>
          </thead>
          <tbody>${filasDet}</tbody>
        </table>
      </div>

      <div class="text-end mt-1" style="font-size:.9rem;">
        Total estimado:
        <strong>${formatters.moneda(cot.total ?? cot.total_estimado)}</strong>
      </div>`;
  },
};
