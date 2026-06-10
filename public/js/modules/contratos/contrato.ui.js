/**
 * @file    contrato.ui.js
 * @module  modules/contratos/ui
 *
 * Capa de renderizado DOM para el módulo de contratos.
 * Todas las funciones operan sobre el DOM directamente; ninguna realiza
 * llamadas a la API ni modifica el estado global.
 *
 * Elementos DOM requeridos (IDs):
 *  - `tablaBody`               : <tbody> de la tabla principal.
 *  - `statTotal`, `statVigentes`, `statCompletados`, `statMonto` : tarjetas de resumen.
 *  - `paginaInfo`, `paginaBtns`   : pie de paginación.
 *  - `cotizacionesDisponibles`    : contenedor de la lista en el modal 1.
 *  - `cotResumen`                 : resumen de cotización seleccionada en el modal 2.
 *  - `detalleBody`, `detalleTitle`, `detalleAcciones` : modal de detalle.
 */

import { formatters } from '../../utils/formatters.js';

/** @type {Object<string, string[]>} Mapa de estado → [clase CSS de badge, etiqueta]. */
const BADGE_MAP = {
  ACTIVO:     ['badge-pendiente', 'Vigente'],
  COMPLETADO: ['badge-aprobada',  'Completado'],
  CANCELADO:  ['badge-rechazada', 'Cancelado'],
};

/**
 * Genera el HTML del badge de estado de un contrato.
 *
 * @param {string} estado - Estado del contrato (ACTIVO | COMPLETADO | CANCELADO).
 * @returns {string} HTML del badge `<span>`.
 */
function badgeEstado(estado) {
  const [cls, label] = BADGE_MAP[estado?.toUpperCase()] ?? ['badge-inactivo', estado ?? '—'];
  return `<span class="${cls}">${label}</span>`;
}

/**
 * Extrae el nombre del cliente de un contrato, independientemente de si viene
 * como string u objeto anidado.
 *
 * @param {Object} c - Contrato con campo `cliente` (string u objeto).
 * @returns {string} Nombre del cliente o `'—'` si no está disponible.
 */
function nombreCot(c) {
  if (!c.cliente) return '—';
  return typeof c.cliente === 'object'
    ? (c.cliente.nombre_completo ?? '—')
    : c.cliente;
}

/**
 * Colección de funciones de renderizado para el módulo de contratos.
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
      `<tr style="opacity:.5;">${fila.repeat(8)}</tr>`
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
        <td colspan="8" class="text-center py-4" style="color:var(--red-text);font-size:.85rem;">
          <i class="bi bi-exclamation-circle me-1"></i>${msg}
        </td>
      </tr>`;
  },

  /**
   * Actualiza los valores de las tarjetas de estadísticas superiores.
   *
   * @param {{ total: number, vigentes: number, completados: number, monto_total: number }} stats
   * @returns {void}
   */
  renderStats({ total, vigentes, completados, monto_total }) {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('statTotal',       total);
    set('statVigentes',    vigentes);
    set('statCompletados', completados);
    set('statMonto',       formatters.moneda(monto_total));
  },

  /**
   * Renderiza las filas de la tabla principal de contratos.
   * Cada fila incluye acciones condicionales según el estado del contrato.
   *
   * @param {Array<Object>} filas - Contratos a renderizar (ya paginados).
   * @returns {void}
   */
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
      const cod        = formatters.codigo(c.id);
      const cotCod     = c.id_cotizacion ? formatters.codigo(c.id_cotizacion) : '—';
      const isActivo   = c.estado?.toUpperCase() === 'ACTIVO';
      const canArchive = !isActivo;
      const editAttr   = isActivo
        ? `onclick="event.stopPropagation();editarContrato(${c.id})"  title="Corregir contrato"`
        : `disabled title="Solo se puede editar un contrato vigente"`;

      return `
        <tr style="cursor:pointer;" onclick="verDetalleContrato(${c.id})">
          <td><span class="con-codigo">${cod}</span></td>
          <td><a class="con-cot-ref-small btn btn-small" href="${BASE_URL}cotizaciones/${c.id_cotizacion}" onclick="event.stopPropagation()">${cotCod}</a></td>
          <td>
            ${c.promocion
              ? `<span class="text-uppercase fw-semibold" style="font-size:.85rem;">${c.promocion.colegio}</span>
                 <br><span style="font-size:.75rem;color:var(--text-muted);">
                   ${[c.promocion.grado, c.promocion.seccion].filter(Boolean).join(' · ')}
                   ${c.promocion.num_estudiantes ? `· ${c.promocion.num_estudiantes} est.` : ''}
                 </span>
                 <br><span class="text-uppercase" style="font-size:.72rem;color:var(--text-muted);">${c.cliente?.nombre ?? '—'}</span>`
              : `<span class="text-uppercase" style="font-size:.85rem;">${c.cliente?.nombre ?? '—'}</span>`
            }
          </td>
          <td style="color:var(--text-muted);">—</td>
          <td>${formatters.moneda(c.total)}</td>
          <td>${badgeEstado(c.estado)}</td>
          <td>${formatters.fecha(c.fecha_creacion)}</td>
          <td onclick="event.stopPropagation()">
            <div class="con-actions">
              <button class="btn btn-sm btn-outline-warning" ${editAttr}>
                <i class="bi bi-pencil-square"></i>
              </button>
              <a class="btn btn-sm btn-outline-info" title="Gestionar sesiones"
                 href="/contratos/${c.id}/sesiones"
                 onclick="event.stopPropagation()">
                <i class="bi bi-camera"></i>
              </a>
              ${canArchive ? `
              <button class="btn btn-sm ${c.archivado ? 'btn-secondary' : 'btn-outline-secondary'}"
                      title="${c.archivado ? 'Desarchivar' : 'Archivar'}"
                      onclick="event.stopPropagation();archivarContrato(${c.id}, ${c.archivado ? 'true' : 'false'})">
                <i class="bi ${c.archivado ? 'bi-archive-fill' : 'bi-archive'}"></i>
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
   * Renderiza la lista de cotizaciones aprobadas disponibles en el modal 1.
   * Aplica filtro de búsqueda local sobre nombre del cliente o código.
   *
   * @param {Array<Object>} cotizaciones - Lista completa de cotizaciones disponibles.
   * @param {string}        [filtro=''] - Término de búsqueda para filtrar la lista.
   * @returns {void}
   */
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

  /**
   * Muestra el resumen de la cotización seleccionada en el modal 2 (generar contrato).
   *
   * @param {Object} cot - Datos de la cotización seleccionada.
   * @returns {void}
   */
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

  /**
   * Genera el HTML del cuerpo del modal de detalle de un contrato.
   * Incluye metadata del contrato e historial de pagos completo.
   *
   * @param {Object}        data        - Contrato completo con campos `pagos[]`, `total_pagado`, `saldo`.
   * @param {Array<Object>} [data.pagos=[]] - Historial de pagos del contrato.
   * @returns {string} HTML del contenido del modal de detalle.
   */
  renderDetalle(data, id, isActivo) {
    const pagos = data.pagos ?? [];
    const filasPagos = pagos.length
      ? pagos.map(p => `
          <tr>
            <td>${formatters.fecha(p.fecha)}</td>
            <td>${p.nombre_forma_pago ?? '—'}</td>
            <td class="text-end">${formatters.moneda(p.monto)}</td>
          </tr>`).join('')
      : `<tr><td colspan="3" class="text-center text-muted py-2" style="font-size:.82rem;">Sin pagos registrados</td></tr>`;

    const btnImprimir = `
      <a href="${BASE_URL}contratos/${id}" target="_blank"
         style="color:var(--text-muted);font-size:.78rem;display:inline-flex;align-items:center;gap:.3rem;
                text-decoration:none;padding:.25rem .5rem;border-radius:6px;transition:background .15s;"
         onmouseover="this.style.background='var(--bg-hover)'"
         onmouseout="this.style.background=''">
        <i class="bi bi-printer"></i> Imprimir
      </a>`;

    const btnAgregarPago = isActivo ? `
      <button onclick="abrirModalPago(${id})"
              style="background:none;border:none;cursor:pointer;color:var(--accent);font-size:.75rem;
                     font-weight:600;display:inline-flex;align-items:center;gap:.3rem;padding:.15rem .4rem;
                     border-radius:5px;transition:background .15s;"
              onmouseover="this.style.background='var(--accent-light)'"
              onmouseout="this.style.background=''">
        <i class="bi bi-plus-circle"></i> Añadir pago
      </button>` : '';

    return `
      <div style="position:relative;margin-bottom:1rem;">
        <div style="position:absolute;top:0;right:0;">${btnImprimir}</div>
        <div class="row g-2" style="font-size:.84rem;">
          <div class="col-6">
            <span style="color:var(--text-muted);">Código contrato</span><br>
            <span class="con-codigo">${formatters.codigo(data.id)}</span>
          </div>
          <div class="col-6">
            <span style="color:var(--text-muted);">Estado</span><br>
            ${badgeEstado(data.estado)}
          </div>
          <div class="col-6">
            <span style="color:var(--text-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">
              <i class="bi bi-person-fill me-1"></i>Cliente principal
            </span><br>
            <strong>${data.cliente?.nombre ?? '—'}</strong><br>
            <span style="font-size:.78rem;color:var(--text-muted);">${data.cliente?.telefono ?? '—'}</span>
          </div>
          <div class="col-6">
            ${data.contacto2 ? `
            <span style="color:var(--accent);font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;font-weight:700;">
              <i class="bi bi-person-plus-fill me-1"></i>Contacto adicional
            </span><br>
            <strong>${data.contacto2.nombre}</strong><br>
            <span style="font-size:.78rem;color:var(--text-muted);">${data.contacto2.telefono ?? '—'}</span>
            ` : `
            <span style="color:var(--text-muted);">Teléfono</span><br>
            ${data.cliente?.telefono ?? '—'}
            `}
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
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
        <span style="font-size:.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px;">
          Historial de pagos
        </span>
        ${btnAgregarPago}
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
