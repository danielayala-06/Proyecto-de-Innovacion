/**
 * @file    cotizacion.ui.js
 * @module  modules/cotizaciones/ui
 *
 * Capa de renderizado DOM para el módulo de cotizaciones del index.
 * Todas las funciones operan sobre el DOM directamente; ninguna realiza
 * llamadas a la API ni modifica el estado global.
 *
 * Elementos DOM requeridos (IDs):
 *  - `tablaBody`                : <tbody> de la tabla principal.
 *  - `statTotal`, `statPend`, `statAprobadas`, `statRechazadas`, `statExpiradas` : tarjetas de resumen.
 *  - `paginaInfo`, `paginaBtns` : pie de paginación.
 *  - `detalleBody`, `detalleTitle`, `detalleAcciones` : modal de detalle.
 */

import { formatters } from '../../utils/formatters.js';

/** @type {Object<string, string[]>} Mapa de estado → [clase CSS de badge, etiqueta]. */
const BADGE_MAP = {
  PENDIENTE:         ['badge-pendiente',  'Pendiente'],
  APROBADA:          ['badge-aprobada',   'Aprobada'],
  RECHAZADA:         ['badge-rechazada',  'Rechazada'],
  EXPIRADA:          ['badge-inactivo',   'Expirada'],
  CONTRATO_GENERADO: ['badge-completada', 'Contrato'],
};

/**
 * Genera el HTML del badge de estado de una cotización.
 *
 * @param {string} estado - Estado de la cotización (insensible a mayúsculas).
 * @returns {string} HTML del badge `<span>`.
 */
function badgeEstado(estado) {
  const [cls, label] = BADGE_MAP[estado?.toUpperCase()] ?? ['badge-inactivo', estado ?? '—'];
  return `<span class="${cls}">${label}</span>`;
}

/**
 * Extrae el nombre completo del cliente de una cotización.
 * Normaliza tanto el caso en que `cliente` sea un string como un objeto anidado.
 *
 * @param {Object} c - Cotización con campo `cliente`.
 * @returns {string} Nombre del cliente o cadena vacía si no está disponible.
 */
function nombreCliente(c) {
  if (!c.cliente) return '—';
  return typeof c.cliente === 'object'
    ? (c.cliente.nombre_completo ?? '—')
    : c.cliente;
}

/**
 * Colección de funciones de renderizado para el módulo de cotizaciones.
 *
 * @namespace ui
 */
export const ui = {
  /**
   * Muestra un skeleton de carga en el `<tbody>` de la tabla.
   * @returns {void}
   */
  renderLoading() {
    const tbody = document.getElementById('tablaBody');
    if (!tbody) return;
    const fila = `<td><div class="placeholder-glow"><span class="placeholder col-8"></span></div></td>`;
    tbody.innerHTML = Array.from({ length: 5 }, () =>
      `<tr style="opacity:.5;">${fila.repeat(7)}</tr>`
    ).join('');
  },

  /**
   * Muestra un mensaje de error en el `<tbody>` de la tabla.
   *
   * @param {string} [msg='Error al cargar los datos.'] - Mensaje de error.
   * @returns {void}
   */
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

  /**
   * Actualiza los valores de las tarjetas de estadísticas superiores.
   *
   * @param {{ total: number, borrador: number, aprobadas: number, rechazadas: number, expiradas: number }} r
   * @returns {void}
   */
  renderStats(r) {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('statTotal',      r.total      ?? 0);
    set('statPend',       r.borrador   ?? r.pendientes ?? 0);
    set('statAprobadas',  r.aprobadas  ?? 0);
    set('statRechazadas', r.rechazadas ?? 0);
    set('statExpiradas',  r.expiradas  ?? 0);
  },

  /**
   * Renderiza las filas de la tabla principal de cotizaciones.
   * Las acciones disponibles por fila dependen del estado y si ya tiene contrato:
   *  - PENDIENTE  : botón de rechazo.
   *  - APROBADA sin contrato: botón de generar contrato.
   *
   * @param {Array<Object>} filas - Cotizaciones a renderizar (ya paginadas).
   * @returns {void}
   */
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

    const baseUrl = window.BASE_URL || '/';
    tbody.innerHTML = filas.map(c => {
      const codigo      = formatters.codigo(c.id);
      const nombre      = nombreCliente(c);
      const estado      = c.estado?.toUpperCase();
      const canEdit     = estado === 'PENDIENTE';
      const canDel      = estado === 'PENDIENTE';
      const canContract = estado === 'APROBADA' && !c.tiene_contrato;

      return `
        <tr style="cursor:pointer;" onclick="verDetalle(${c.id})">
          <td><code style="font-size:.78rem;">${codigo}</code></td>
          <td class= "text-uppercase">${nombre}</td>
          <td>${formatters.moneda(c.total)}</td>
          <td>${badgeEstado(c.estado)}</td>
          <td>${formatters.fecha(c.fecha)}</td>
          <td class="text-center" onclick="event.stopPropagation()">
            <div class="d-flex gap-1 justify-content-center">
              ${canEdit ? `
              <button class="btn btn-sm btn-outline-warning" title="Editar cotización"
                      onclick="window.location.href='${baseUrl}cotizaciones/editar/${c.id}'">
                <i class="bi bi-pencil"></i>
              </button>` : ''}
              ${canContract ? `
              <button class="btn btn-sm btn-outline-success" title="Generar contrato"
                      onclick="irAGenerarContrato(${c.id})">
                <i class="bi bi-file-earmark-plus"></i>
              </button>` : ''}
              ${canDel ? `
              <button class="btn btn-sm btn-outline-danger" title="Rechazar"
                      onclick="confirmarEliminar(${c.id},'${codigo}')">
                <i class="bi bi-trash"></i>
              </button>` : ''}
            </div>
          </td>
        </tr>`;
    }).join('');
  },

  /**
   * Renderiza los controles de paginación (info textual + botones numéricos).
   *
   * @param {number} total     - Total de filas filtradas.
   * @param {number} pagina    - Página activa (base 1).
   * @param {number} porPagina - Filas por página.
   * @returns {void}
   */
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

  /**
   * Genera el HTML del cuerpo del modal de detalle de una cotización.
   * Incluye metadata de la cotización, datos del cliente y tabla de ítems.
   *
   * @param {Object} data - Objeto con `cotizacion`, `cliente` e `items[]` (o estructura plana).
   * @returns {string} HTML del contenido del modal de detalle.
   */
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
        ${data.cliente2 ? `
        <div class="col-12" style="padding-top:2px;">
          <span style="color:var(--text-muted);font-size:.78rem;">2.º Responsable</span><br>
          <span>${data.cliente2.nombre_completo || '—'}</span>
          ${data.cliente2.telefono ? `<span style="color:var(--text-muted);font-size:.78rem;margin-left:8px;">${data.cliente2.telefono}</span>` : ''}
        </div>` : ''}
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

      ${cot.descuento_monto > 0 ? `
      <div class="text-end" style="font-size:.83rem;color:var(--text-muted);">
        Subtotal: <span>${formatters.moneda((cot.total ?? 0) + cot.descuento_monto)}</span>
        &nbsp;·&nbsp;<span style="color:#198754;"><i class="bi bi-tag-fill"></i> Descuento: - ${formatters.moneda(cot.descuento_monto)}</span>
      </div>` : ''}
      <div class="text-end mt-1" style="font-size:.9rem;">
        Total estimado:
        <strong>${formatters.moneda(cot.total ?? cot.total_estimado)}</strong>
      </div>`;
  },
};
