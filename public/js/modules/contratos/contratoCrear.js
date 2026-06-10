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
  const items     = cot.items ?? [];
  const cortesias = items.filter(it =>
    it.tipo_item === 'personalizado' && parseFloat(it.precio_unitario) === 0
  );
  const regulares = items.filter(it =>
    !(it.tipo_item === 'personalizado' && parseFloat(it.precio_unitario) === 0)
  );

  const filas = regulares.map(it => `
    <tr>
      <td>${_tipoBadge(it.tipo_item)}<br><span style="font-size:.83rem;">${it.referencia_nombre ?? it.descripcion}</span></td>
      <td class="text-center py-4">${it.cantidad}</td>
      <td class="py-4" style="text-align:right;">${formatters.moneda(it.precio_unitario)}</td>
      <td class="py-4" style="text-align:right;">${formatters.moneda(it.cantidad * it.precio_unitario)}</td>
    </tr>`).join('');

  const cortesiasBlock = cortesias.length ? `
    <div style="margin-top:16px;">
      <div class="cc-section-title" style="margin-bottom:8px;">
        <i class="bi bi-gift me-2" style="color:var(--accent);"></i>Cortesías incluidas
      </div>
      ${cortesias.map(it => {
        const nombre = String(it.descripcion ?? '').replace(/^\[Cortesía\]\s*/i, '');
        const badge  = it.cantidad > 1
          ? `<span style="background:rgba(184,150,62,.15);color:var(--accent);border-radius:12px;padding:2px 9px;font-size:.72rem;font-weight:600;white-space:nowrap;">×${it.cantidad}</span>`
          : `<span style="background:rgba(184,150,62,.15);color:var(--accent);border-radius:12px;padding:2px 9px;font-size:.72rem;font-weight:600;white-space:nowrap;">Incluido</span>`;
        return `<div style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid var(--border);font-size:.8rem;">
          <span style="color:var(--green-text,#4caf50);font-weight:700;flex-shrink:0;">+</span>
          <span style="flex:1;color:var(--text-primary);">${nombre}</span>
          ${badge}
        </div>`;
      }).join('')}
    </div>` : '';

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
      ${regulares.length ? `
        <table class="cc-items-table table table-striped table-hover bg-body-tertiary">
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

    ${cot.descuento_monto > 0 ? `
    <div class="cc-row" style="font-size:.83rem;padding:3px 0;">
      <span>Subtotal</span>
      <span>${formatters.moneda((cot.total ?? 0) + cot.descuento_monto)}</span>
    </div>
    <div class="cc-row" style="font-size:.83rem;padding:3px 0;">
      <span style="color:#198754;"><i class="bi bi-tag-fill"></i> Descuento</span>
      <span style="color:#198754;">− ${formatters.moneda(cot.descuento_monto)}</span>
    </div>` : ''}
    <div class="cc-total-row">
      <span>Total estimado</span>
      <strong>${formatters.moneda(cot.total)}</strong>
    </div>
    ${cortesiasBlock}`;
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
// SESIONES FOTOGRÁFICAS (opcional)
// ─────────────────────────────────────────────────────────────────────────────

let _sesionIdx = 0;

/** Límites de fecha para sesiones: hoy como mínimo, +10 meses como máximo. */
function _limitesSesion() {
  const hoy = new Date(SERVER_TODAY + 'T00:00:00');
  const max = new Date(hoy);
  max.setMonth(hoy.getMonth() + 10);
  return { hoy, max };
}

function _updateBtnAgregar() {
  const container = document.getElementById('sesionesContainer');
  const btn       = document.getElementById('btnAgregarSesion');
  if (!container || !btn) return;
  btn.style.display = container.querySelectorAll('.sesion-row').length >= 3 ? 'none' : '';
}

function _agregarSesion() {
  const container = document.getElementById('sesionesContainer');
  if (!container || container.querySelectorAll('.sesion-row').length >= 3) return;
  const idx = ++_sesionIdx;
  const { hoy, max } = _limitesSesion();
  const minStr = SERVER_TODAY + 'T00:00';
  const maxStr = max.toISOString().slice(0, 16);

  const row = document.createElement('div');
  row.className     = 'sesion-row';
  row.dataset.idx   = idx;
  row.style.cssText = 'display:flex;flex-wrap:wrap;gap:.4rem;align-items:center;margin-bottom:.5rem;';
  row.innerHTML = `
    <input type="datetime-local" id="sesionFecha-${idx}" min="${minStr}" max="${maxStr}"
           style="flex:1;min-width:140px;border:1px solid var(--border);border-radius:6px;padding:.3rem .5rem;font-size:.83rem;background:var(--bg-input,#fff);color:var(--text-primary);">
    <select id="sesionTipo-${idx}"
            style="width:120px;flex-shrink:0;border:1px solid var(--border);border-radius:6px;padding:.3rem .5rem;font-size:.83rem;background:var(--bg-input,#fff);color:var(--text-primary);">
      <option value="estudio">Estudio</option>
      <option value="colegio">Colegio</option>
      <option value="exteriores">Exteriores</option>
      <option value="otro">Otro</option>
    </select>
    <button type="button" data-action="del-sesion" data-idx="${idx}"
            style="flex-shrink:0;background:none;border:1px solid var(--red-text,#dc3545);color:var(--red-text,#dc3545);border-radius:6px;padding:.3rem .55rem;cursor:pointer;"
            title="Quitar sesión">
      <i class="bi bi-trash3"></i>
    </button>`;
  container.appendChild(row);

  // Feedback visual en tiempo real
  const input = document.getElementById(`sesionFecha-${idx}`);
  input?.addEventListener('change', function () {
    if (!this.value) { this.style.borderColor = ''; return; }
    const sel  = new Date(this.value);
    const hora = parseInt(this.value.slice(11, 13), 10);
    const ok   = sel >= hoy && sel <= max && hora >= 7 && hora <= 20;
    this.style.borderColor = ok ? 'var(--green-text,#2e7d32)' : 'var(--red-text,#dc3545)';
  });

  _updateBtnAgregar();
}

function _recolectarSesiones() {
  const container = document.getElementById('sesionesContainer');
  if (!container) return [];
  const sesiones = [];
  for (const row of container.querySelectorAll('.sesion-row')) {
    const idx   = row.dataset.idx;
    const fecha = document.getElementById(`sesionFecha-${idx}`)?.value;
    const tipo  = document.getElementById(`sesionTipo-${idx}`)?.value || 'otro';
    if (fecha) sesiones.push({ fecha_hora_sesion: fecha.replace('T', ' ') + ':00', tipo });
  }
  return sesiones;
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
function _initForm(cotId, total, prom = null) {
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
    const numEst = prom?.num_estudiantes ? parseInt(prom.num_estudiantes) : 0;
    if (numEst > 0) {
      const minSugerido = numEst * 10;

      const hint = document.createElement('p');
      hint.style.cssText = 'font-size:.75rem;color:var(--accent-text);background:var(--accent-light);border:1px solid var(--accent);border-radius:6px;padding:4px 8px;margin-top:6px;margin-bottom:0;display:inline-flex;align-items:center;gap:5px;';
      hint.innerHTML = `<i class="bi bi-info-circle-fill"></i>Mínimo sugerido: <strong>${formatters.moneda(minSugerido)}</strong> (${numEst} est. × S/ 10)`;
      adelantoInput.closest('.cc-form-group')?.appendChild(hint);

      const _actualizarColorAdelanto = () => {
        const val = parseFloat(adelantoInput.value) || 0;
        if (!val) {
          adelantoInput.style.borderColor = '';
          adelantoInput.style.color       = '';
        } else if (val >= minSugerido) {
          adelantoInput.style.borderColor = 'var(--green-text, #2e7d32)';
          adelantoInput.style.color       = 'var(--green-text, #2e7d32)';
        } else {
          adelantoInput.style.borderColor = '#f59e0b';
          adelantoInput.style.color       = '#b45309';
        }
      };

      adelantoInput.addEventListener('input', _actualizarColorAdelanto);
      adelantoInput.addEventListener('change', _actualizarColorAdelanto);
    }
  }

  const selectForma = document.getElementById('contratoFormaPago');
  const inputOtro   = document.getElementById('contratoFormaPagoOtro');
  if (selectForma) {
    selectForma.innerHTML = `
      <option value="Efectivo">Efectivo</option>
      <option value="Yape">Yape</option>
      <option value="Plin">Plin</option>
      <option value="otro">Otro…</option>`;
    selectForma.value = 'Efectivo';

    if (inputOtro) {
      inputOtro.addEventListener('input', () => {
        const pos = inputOtro.selectionStart;
        const limpio = inputOtro.value.replace(/[^\p{L}\s\/\-\.]/gu, '');
        if (limpio !== inputOtro.value) {
          inputOtro.value = limpio;
          inputOtro.setSelectionRange(pos - 1, pos - 1);
        }
      });
    }
    selectForma.addEventListener('change', () => {
      if (inputOtro) inputOtro.style.display = selectForma.value === 'otro' ? '' : 'none';
    });
  }

  // Sesiones fotográficas
  const sesContainer = document.getElementById('sesionesContainer');
  const btnAgregar   = document.getElementById('btnAgregarSesion');
  if (sesContainer) {
    sesContainer.addEventListener('click', e => {
      const btn = e.target.closest('[data-action="del-sesion"]');
      if (!btn) return;
      sesContainer.querySelector(`[data-idx="${btn.dataset.idx}"]`)?.remove();
      _updateBtnAgregar();
    });
  }
  if (btnAgregar) btnAgregar.addEventListener('click', _agregarSesion);

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

  // Validar fechas y horarios de sesiones ingresadas
  const { hoy: hoyS, max: maxS } = _limitesSesion();
  for (const s of _recolectarSesiones()) {
    const d    = new Date(s.fecha_hora_sesion.replace(' ', 'T'));
    const hora = d.getHours();
    if (d < hoyS) {
      alerts.warning('Una sesión tiene fecha en el pasado. Corrígela antes de continuar.');
      return;
    }
    if (d > maxS) {
      alerts.warning('Una sesión supera el límite de 10 meses desde hoy. Corrígela antes de continuar.');
      return;
    }
    if (hora < 7 || hora > 20) {
      alerts.warning('El horario de las sesiones debe ser entre las 7:00 a.m. y las 8:00 p.m.');
      return;
    }
  }

  const formaPagoSel  = document.getElementById('contratoFormaPago');
  const esOtro        = formaPagoSel?.value === 'otro';
  const otroVal       = document.getElementById('contratoFormaPagoOtro')?.value.trim() || '';
  if (esOtro && !otroVal) {
    alerts.warning('Especifica el método de pago.');
    return;
  }
  if (esOtro && !/^[\p{L}\s\/\-\.]+$/u.test(otroVal)) {
    alerts.warning('El método de pago solo puede contener letras.');
    return;
  }
  const formaPagoText = esOtro ? otroVal : (formaPagoSel?.value ?? 'Efectivo');
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
  nuevoBtn.addEventListener('click', () => _submit(cotId, total, adelanto, fechaFirma, formaPagoText));

  const modalEl = document.getElementById('modalConfirmarContrato');
  _modalContrato = _modalContrato ?? new bootstrap.Modal(modalEl);
  _modalContrato.show();
}

/**
 * Envía el contrato a la API tras la confirmación del usuario.
 * Cierra el modal, deshabilita el botón y redirige al éxito.
 */
async function _submit(cotId, total, adelanto, fechaFirma, formaPago) {
  const obsTexto = formaPago ? `Forma de pago: ${formaPago}` : null;

  const btnConf = document.getElementById('btnConfirmarContrato');
  const btnGen  = document.getElementById('btnGenerar');
  if (btnConf) { btnConf.disabled = true; btnConf.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generando…'; }
  if (btnGen)  { btnGen.disabled = true; }

  try {
    await contratoApi.crear({
      id_cotizacion: cotId,
      adelanto,
      forma_pago:    formaPago || 'Efectivo',
      fecha_emision: fechaFirma,
      observaciones: obsTexto,
      sesiones:      _recolectarSesiones(),
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

    _initForm(cotId, cot.total, prom);
  } catch (e) {
    if (elCot) _renderError(elCot, 'Error al cargar los datos.');
  }
}

init();
