/**
 * @file    contratoCrear.js
 * @module  modules/contratos/contratoCrear
 *
 * Módulo autoejecutable para la vista de creación de contratos (`/contratos/crear?cot={id}`).
 * Lee el parámetro `cot` de la URL, carga la cotización y la promoción asociada,
 * valida el estado de la cotización y, si es válida, inicializa el formulario
 * de generación de contrato.
 *
 * Flujo:
 *  1. Lee `?cot=N` de la URL; si no existe, muestra error y termina.
 *  2. Carga en paralelo la cotización y la primera promoción vinculada.
 *  3. Renderiza la preview de cotización y de promoción en los paneles laterales.
 *  4. Verifica el estado de la cotización (debe ser APROBADA); si no, bloquea el formulario.
 *  5. Inicializa el formulario con fecha, adelanto y formas de pago.
 *  6. Al hacer click en "Generar", valida y crea el contrato vía API.
 */

import { cotizacionApi } from '../../api/cotizacion.api.js';
import { contratoApi }   from '../../api/contrato.api.js';
import { promocionApi }  from '../../api/promocion.api.js';
import { formatters }    from '../../utils/formatters.js';
import { alerts }        from '../../utils/alerts.js';

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS DE URL Y FECHA
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Lee un parámetro de la query string de la URL actual.
 *
 * @param {string} key - Nombre del parámetro.
 * @returns {string|null} Valor del parámetro o `null` si no existe.
 */
function _param(key) {
  return new URLSearchParams(window.location.search).get(key);
}

/**
 * Serializa un `Date` en formato `YYYY-MM-DD`.
 *
 * @param {Date} d
 * @returns {string}
 */
function _isoDate(d) {
  const y  = d.getFullYear();
  const m  = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${dd}`;
}

// ─────────────────────────────────────────────────────────────────────────────
// RENDER HELPERS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Muestra un skeleton de carga dentro de un elemento del DOM.
 *
 * @param {HTMLElement} el - Contenedor donde mostrar el skeleton.
 * @returns {void}
 */
function _renderSkeleton(el) {
  el.innerHTML = `
    <div class="placeholder-glow d-flex flex-column gap-2">
      ${Array.from({ length: 4 }, () =>
        `<span class="placeholder col-${6 + Math.floor(Math.random() * 5)} rounded"></span>`
      ).join('')}
    </div>`;
}

/**
 * Muestra un mensaje de error dentro de un elemento del DOM.
 *
 * @param {HTMLElement} el  - Contenedor donde mostrar el error.
 * @param {string}      msg - Mensaje de error.
 * @returns {void}
 */
function _renderError(el, msg) {
  el.innerHTML = `
    <div class="text-center py-4" style="color:var(--red-text);font-size:.85rem;">
      <i class="bi bi-exclamation-circle me-1"></i>${msg}
    </div>`;
}

/**
 * Genera un badge de icono según el tipo de ítem de la cotización.
 *
 * @param {'paquete'|'producto'|'servicio'} tipo
 * @returns {string} HTML del badge.
 */
function _tipoBadge(tipo) {
  const map = { paquete: 'bi-box-seam', producto: 'bi-tag', servicio: 'bi-tools' };
  const icon = map[tipo] ?? 'bi-dot';
  return `<span style="font-size:.7rem;color:var(--text-muted);"><i class="bi ${icon} me-1"></i>${tipo}</span>`;
}

/**
 * Renderiza la preview completa de la cotización en el panel izquierdo.
 *
 * @param {Object}      cot - Datos de la cotización (con `items[]`, `cliente`, `total`).
 * @param {HTMLElement} el  - Contenedor donde renderizar.
 * @returns {void}
 */
function _renderPreviewCotizacion(cot, el) {
  const items = cot.items ?? [];
  const filas = items.map(it => `
    <tr>
      <td>${_tipoBadge(it.tipo_item)}<br><span style="font-size:.83rem;">${it.referencia_nombre ?? it.descripcion}</span></td>
      <td class="text-center py-4">${it.cantidad}</td>
      <td class="py-4" style="text-align:right;">${formatters.moneda(it.precio_unitario)}</td>
      <td class="py-4" style="text-align:right;">${formatters.moneda(it.cantidad * it.precio_unitario)}</td>
    </tr>`).join('');

  el.innerHTML = `
    <div class="cc-section">
      <div class="cc-section-title"><i class="bi bi-file-earmark-text me-2"></i>Cotización</div>
      <div class="w-100 mb-4 shadow border bg-body-tertiary rounded"></div>
      <div class="cc-row"><span>Código</span><strong>${formatters.codigo(cot.id)}</strong></div>
      <div class="cc-row"><span>Fecha</span><strong>${formatters.fecha(cot.fecha?.slice(0,10))}</strong></div>
      <div class="cc-row"><span>Cliente</span><strong>${cot.cliente?.nombre_completo ?? '—'}</strong></div>
      <div class="cc-row"><span>Estado</span><strong>${cot.estado}</strong></div>
    </div>

    <div class="cc-section">
      <div class="cc-section-title"><i class="bi bi-box-seam me-2"></i>Paquetes cotizados</div>
      <div class="w-100 mb-4 shadow border bg-body-tertiary rounded"></div>
      ${items.length ? `
        <table class="cc-items-table table table-striped  table-hover bg-body-tertiary">
          <thead>
            <tr>
              <th>Paquetes</th>
              <th style="text-align:center;">Cant.</th>
              <th style="text-align:right;">P. Unit.</th>
              <th style="text-align:right;">Subtotal</th>
            </tr>
          </thead>
          <tbody>${filas}</tbody>
        </table>` : '<p style="font-size:.82rem;color:var(--text-muted);">Sin ítems registrados.</p>'}
    </div>

    <div class="cc-total-row">
      <span>Total estimado</span>
      <strong>${formatters.moneda(cot.total)}</strong>
    </div>`;
}

/**
 * Renderiza la sección de beneficios activados por las reglas de cantidad.
 *
 * @param {Array} activadas   - Reglas activadas (tipo_beneficio, descripcion, nombre_producto_beneficio, valor_beneficio).
 * @param {Array} violaciones - Reglas que superaron el límite.
 * @param {HTMLElement} container - Contenedor de la cotización donde se inserta el bloque.
 * @returns {void}
 */
function _rowReglaBase(icon, color, descripcion, badge) {
  return `<div style="display:flex;align-items:baseline;gap:8px;padding:5px 0;border-bottom:1px solid var(--border-color);font-size:.8rem;">
    <span style="color:${color};font-weight:700;flex-shrink:0;">${icon}</span>
    <span style="flex:1;color:var(--text-primary);">${descripcion}</span>
    ${badge}
  </div>`;
}

function _rowActivada(r) {
  const label = r.nombre_producto_beneficio ?? r.valor_beneficio ?? '';
  const badge  = label
    ? `<span style="background:var(--accent-faint,#e8f0fe);color:var(--accent);border-radius:12px;padding:2px 9px;font-size:.72rem;font-weight:600;white-space:nowrap;">${label}</span>`
    : '';
  return _rowReglaBase('+', 'var(--green-text,#4caf50)', r.descripcion, badge);
}

function _rowViolacion(r) {
  const badge = `<span style="background:#fff0f0;color:var(--red-text,#c0392b);border-radius:12px;padding:2px 9px;font-size:.72rem;font-weight:600;white-space:nowrap;">Límite: ${r.limite} uds.</span>`;
  return _rowReglaBase('!', 'var(--red-text,#e57373)', r.descripcion, badge);
}

function _renderReglas(activadas, violaciones, container) {
  if (!container) return;
  const existing = container.querySelector('.cc-reglas-block');
  if (existing) existing.remove();

  if (!activadas.length && !violaciones.length) return;

  const rows = [
    ...activadas.map(_rowActivada),
    ...violaciones.map(_rowViolacion),
  ].join('');

  const block = document.createElement('div');
  block.className = 'cc-reglas-block';
  block.style.cssText = 'margin-top:16px;';
  block.innerHTML = `
    <div class="cc-section-title" style="margin-bottom:8px;">
      <i class="bi bi-gift me-2" style="color:var(--accent);"></i>Beneficios y condiciones aplicables
    </div>
    ${rows}`;

  container.appendChild(block);
}

/**
 * Renderiza la preview de la promoción escolar vinculada a la cotización.
 *
 * @param {Object|null} prom - Datos de la promoción, o `null` si no hay ninguna.
 * @param {HTMLElement} el   - Contenedor donde renderizar.
 * @returns {void}
 */
function _renderPreviewPromocion(prom, el) {
  if (!prom) {
    el.innerHTML = `<p style="font-size:.82rem;color:var(--text-muted);">Sin promoción vinculada a esta cotización.</p>`;
    return;
  }
  const nivel = [prom.grado, prom.seccion].filter(Boolean).join(' — ');
  el.innerHTML = `
    <div class="cc-row"><span>Nombre</span><strong>${prom.nombre}</strong></div>
    <div class="cc-row"><span>Nivel</span><strong>${nivel}</strong></div>
    <div class="cc-row"><span>Colegio</span><strong>${prom.nombre_colegio ?? '—'}</strong></div>
    <div class="cc-row"><span>Estudiantes</span><strong>${prom.num_estudiantes}</strong></div>
    <div class="cc-row"><span>Año</span><strong>${prom.anio}</strong></div>`;
}

// ─────────────────────────────────────────────────────────────────────────────
// BLOQUEO DE FORMULARIO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Deshabilita el botón de generación y muestra un aviso de error en el formulario.
 * Se usa cuando la cotización no está en estado APROBADA.
 *
 * @param {string} mensaje - Razón del bloqueo mostrada al usuario.
 * @returns {void}
 */
function _bloquearFormulario(mensaje) {
  const formCol = document.querySelector('.cc-card:last-child');
  const btn     = document.getElementById('btnGenerar');

  if (btn) {
    btn.disabled          = true;
    btn.style.opacity     = '0.5';
    btn.style.cursor      = 'not-allowed';
  }

  const aviso = document.createElement('div');
  aviso.style.cssText = 'background:var(--red-bg,#fff0f0);border:1px solid var(--red-text,#c0392b);border-radius:8px;padding:12px 16px;font-size:.83rem;color:var(--red-text,#c0392b);display:flex;align-items:center;gap:8px;margin-bottom:16px;';
  aviso.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i><span>${mensaje}</span>`;

  const fieldsets = document.querySelectorAll('.cc-fieldset');
  if (fieldsets.length) {
    fieldsets[0].before(aviso);
  } else if (formCol) {
    formCol.prepend(aviso);
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// FORMULARIO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Inicializa el formulario de generación de contrato:
 * - Configura restricciones de fecha (máx. hoy, mín. hace 2 días).
 * - Carga el catálogo de formas de pago en el select.
 * - Registra el listener del botón de envío.
 *
 * @param {number} cotId - ID de la cotización para la que se genera el contrato.
 * @param {number} total - Total de la cotización (para validar el adelanto).
 * @returns {void}
 */
function _initForm(cotId, total) {
  const hoy  = new Date(SERVER_TODAY + 'T00:00:00');
  const min2 = new Date(hoy);
  min2.setDate(hoy.getDate() - 2);

  const fechaInput = document.getElementById('contratoFechaFirma');
  if (fechaInput) {
    fechaInput.value = SERVER_TODAY;
    fechaInput.max   = SERVER_TODAY;
    fechaInput.min   = _isoDate(min2);
  }

  const adelantoInput = document.getElementById('contratoAdelanto');
  if (adelantoInput && total) {
    adelantoInput.placeholder = `Máx. ${formatters.moneda(total)}`;
  }

  const selectForma = document.getElementById('contratoFormaPago');
  if (selectForma) {
    selectForma.innerHTML = '<option value="">— Cargando... —</option>';
    contratoApi.formasPago().then(res => {
      const formas = res.data ?? [];
      selectForma.innerHTML = '<option value="">— Seleccionar —</option>' +
        formas.map(f => `<option value="${f.id_form_pago}">${f.nombre_forma_pago}</option>`).join('');
    }).catch(() => {
      selectForma.innerHTML = '<option value="">— Error al cargar —</option>';
    });
  }

  document.getElementById('btnGenerar')?.addEventListener('click', () => _abrirConfirmacion(cotId, total));
}

// ─────────────────────────────────────────────────────────────────────────────
// MODAL DE CONFIRMACIÓN
// ─────────────────────────────────────────────────────────────────────────────

let _modalContrato = null;

/**
 * Valida el formulario, llena el modal de resumen y lo muestra.
 * La llamada real a la API ocurre solo cuando el usuario confirma en el modal.
 */
function _abrirConfirmacion(cotId, total) {
  const adelanto = parseFloat(document.getElementById('contratoAdelanto')?.value) || 0;
  if (!adelanto || adelanto <= 0) {
    alerts.warning('Ingresa un adelanto válido mayor a cero.');
    return;
  }
  if (total && adelanto > total + 0.001) {
    alerts.warning(`El adelanto no puede superar el total (${formatters.moneda(total)}).`);
    return;
  }

  const fechaFirma = document.getElementById('contratoFechaFirma')?.value || null;
  if (fechaFirma) {
    const hoy      = new Date(SERVER_TODAY + 'T00:00:00');
    const minFecha = new Date(hoy); minFecha.setDate(hoy.getDate() - 2);
    const selDate  = new Date(fechaFirma + 'T00:00:00');
    if (selDate > hoy)      { alerts.warning('La fecha de pago no puede ser en el futuro.'); return; }
    if (selDate < minFecha) { alerts.warning('La fecha de pago no puede ser anterior a 2 días de hoy.'); return; }
  }

  const contacto2Nombre   = document.getElementById('contacto2Nombre')?.value.trim()   || null;
  const contacto2Telefono = document.getElementById('contacto2Telefono')?.value.trim() || null;

  const formaPagoSel  = document.getElementById('contratoFormaPago');
  const formaPagoText = formaPagoSel?.options[formaPagoSel.selectedIndex]?.text ?? '—';
  const saldo         = total - adelanto;
  const pct           = total > 0 ? Math.min(100, Math.round((adelanto / total) * 100)) : 0;

  // Llenar resumen en el modal
  const clienteNombre = document.getElementById('pageTitleCot')?.textContent ?? '';
  const partes        = clienteNombre.split(' — ');
  document.getElementById('confCotId').textContent       = partes[0] ?? '';
  document.getElementById('confClienteNombre').textContent = partes.slice(1).join(' — ') || '—';
  document.getElementById('confTotalCot').textContent    = formatters.moneda(total);
  document.getElementById('confAdelanto').textContent    = formatters.moneda(adelanto);
  document.getElementById('confSaldo').textContent       = formatters.moneda(saldo);
  document.getElementById('confFecha').textContent       = fechaFirma
    ? new Date(fechaFirma + 'T00:00:00').toLocaleDateString('es-PE', { day: '2-digit', month: 'long', year: 'numeric' })
    : '—';
  document.getElementById('confFormaPago').textContent   = formaPagoText;
  document.getElementById('confPctLabel').textContent    = pct + '%';
  document.getElementById('confPctBar').style.width      = pct + '%';

  const alerta = document.getElementById('confAlerta');
  if (adelanto >= total - 0.001) {
    alerta.style.display     = '';
    alerta.style.background  = '#f0fff4';
    alerta.style.border      = '1px solid #c3e6cb';
    alerta.style.color       = '#155724';
    alerta.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>El adelanto cubre el total. El contrato quedará sin saldo pendiente.';
  } else if (pct < 20) {
    alerta.style.display     = '';
    alerta.style.background  = '#fff8e1';
    alerta.style.border      = '1px solid #ffe082';
    alerta.style.color       = '#795548';
    alerta.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i>El adelanto es menor al 20% del total. Confirma que el monto es correcto.`;
  } else {
    alerta.style.display = 'none';
  }

  // Registrar handler del botón confirmar (solo una vez)
  const btnConf = document.getElementById('btnConfirmarContrato');
  const nuevoBtn = btnConf.cloneNode(true);
  btnConf.replaceWith(nuevoBtn);
  nuevoBtn.addEventListener('click', () => _submit(cotId, total, adelanto, fechaFirma, formaPagoSel?.value ?? '', contacto2Nombre, contacto2Telefono));

  const modalEl = document.getElementById('modalConfirmarContrato');
  _modalContrato = _modalContrato ?? new bootstrap.Modal(modalEl);
  _modalContrato.show();
}

/**
 * Envía el contrato a la API tras la confirmación del usuario.
 * Cierra el modal, deshabilita el botón y redirige al éxito.
 */
async function _submit(cotId, total, adelanto, fechaFirma, formaPago, contacto2Nombre, contacto2Telefono) {
  const clausulas     = document.getElementById('contratoClausulas')?.value.trim() ?? '';
  const observaciones = document.getElementById('contratoObservaciones')?.value.trim() ?? '';

  const obsPartes = [];
  if (formaPago)     obsPartes.push(`Forma de pago: ${formaPago}`);
  if (clausulas)     obsPartes.push(`Cláusulas: ${clausulas}`);
  if (observaciones) obsPartes.push(observaciones);
  const obsTexto = obsPartes.join('\n\n') || null;

  const btnConf = document.getElementById('btnConfirmarContrato');
  const btnGen  = document.getElementById('btnGenerar');
  if (btnConf) { btnConf.disabled = true; btnConf.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generando…'; }
  if (btnGen)  { btnGen.disabled = true; }

  try {
    await contratoApi.crear({
      id_cotizacion:      cotId,
      adelanto,
      fecha_emision:      fechaFirma,
      observaciones:      obsTexto,
      contacto2_nombre:   contacto2Nombre,
      contacto2_telefono: contacto2Telefono,
    });
    _modalContrato?.hide();
    alerts.ok('Contrato generado correctamente.');
    setTimeout(() => { window.location.href = BASE_URL + 'contratos'; }, 800);
  } catch (e) {
    alerts.error(e.message || 'No se pudo generar el contrato.');
    if (btnConf) { btnConf.disabled = false; btnConf.innerHTML = '<i class="bi bi-file-earmark-check me-1"></i>Confirmar y generar'; }
    if (btnGen)  { btnGen.disabled = false; }
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// INICIALIZACIÓN
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Función principal de inicialización. Lee `?cot=N` de la URL, carga los datos
 * necesarios y prepara la vista de creación de contrato.
 *
 * @returns {Promise<void>}
 */
async function init() {
  const cotId = parseInt(_param('cot'));
  if (!cotId) {
    document.getElementById('previewCotizacion')?.replaceWith(
      Object.assign(document.createElement('p'), {
        className: 'text-danger',
        textContent: 'No se especificó ninguna cotización.',
      })
    );
    return;
  }

  const elCot  = document.getElementById('previewCotizacion');
  const elProm = document.getElementById('previewPromocion');

  if (elCot)  _renderSkeleton(elCot);
  if (elProm) _renderSkeleton(elProm);

  try {
    const [resCot, resProm] = await Promise.allSettled([
      cotizacionApi.obtener(cotId),
      promocionApi.listar({ id_cotizacion: cotId }),
    ]);

    if (resCot.status === 'rejected' || !resCot.value?.data) {
      if (elCot) _renderError(elCot, 'No se pudo cargar la cotización.');
      return;
    }

    const cot  = resCot.value.data;
    const prom = resProm.status === 'fulfilled'
      ? (resProm.value?.data?.[0] ?? null)
      : null;

    if (elCot)  _renderPreviewCotizacion(cot, elCot);
    if (elProm) _renderPreviewPromocion(prom, elProm);

    _renderReglas(cot.reglas_activadas ?? [], cot.reglas_violaciones ?? [], elCot);

    const titleEl = document.getElementById('pageTitleCot');
    if (titleEl) titleEl.textContent = formatters.codigo(cot.id) + ' — ' + (cot.cliente?.nombre_completo ?? '');

    if (cot.estado?.toUpperCase() === 'EXPIRADA') {
      _bloquearFormulario('Esta cotización ha expirado (más de 30 días). Ya no puede convertirse en contrato.');
      return;
    }
    if (cot.estado?.toUpperCase() !== 'APROBADA') {
      _bloquearFormulario(`Esta cotización está en estado "${cot.estado}" y no puede convertirse en contrato.`);
      return;
    }

    _initForm(cotId, cot.total);
  } catch (e) {
    if (elCot) _renderError(elCot, 'Error al cargar los datos.');
  }
}

init();
