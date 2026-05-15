import { cotizacionApi } from '../../api/cotizacion.api.js';
import { contratoApi }   from '../../api/contrato.api.js';
import { promocionApi }  from '../../api/promocion.api.js';
import { formatters }    from '../../utils/formatters.js';
import { alerts }        from '../../utils/alerts.js';

// ── helpers ───────────────────────────────────────────────────────────────────

function _param(key) {
  return new URLSearchParams(window.location.search).get(key);
}

function _isoDate(d) {
  return d.toISOString().slice(0, 10);
}

// ── render helpers ────────────────────────────────────────────────────────────

function _renderSkeleton(el) {
  el.innerHTML = `
    <div class="placeholder-glow d-flex flex-column gap-2">
      ${Array.from({ length: 4 }, () =>
        `<span class="placeholder col-${6 + Math.floor(Math.random() * 5)} rounded"></span>`
      ).join('')}
    </div>`;
}

function _renderError(el, msg) {
  el.innerHTML = `
    <div class="text-center py-4" style="color:var(--red-text);font-size:.85rem;">
      <i class="bi bi-exclamation-circle me-1"></i>${msg}
    </div>`;
}

function _tipoBadge(tipo) {
  const map = { paquete: 'bi-box-seam', producto: 'bi-tag', servicio: 'bi-tools' };
  const icon = map[tipo] ?? 'bi-dot';
  return `<span style="font-size:.7rem;color:var(--text-muted);"><i class="bi ${icon} me-1"></i>${tipo}</span>`;
}

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

// ── bloqueo de formulario ─────────────────────────────────────────────────────

function _bloquearFormulario(mensaje) {
  const formCol = document.querySelector('.cc-card:last-child');
  const btn     = document.getElementById('btnGenerar');

  if (btn) {
    btn.disabled  = true;
    btn.style.opacity = '0.5';
    btn.style.cursor  = 'not-allowed';
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

// ── form ──────────────────────────────────────────────────────────────────────

function _initForm(cotId, total) {
  // Fecha: hoy por defecto, máximo hoy, mínimo hace 2 días
  const hoy  = new Date();
  const min2 = new Date(hoy);
  min2.setDate(hoy.getDate() - 2);

  const fechaInput = document.getElementById('contratoFechaFirma');
  if (fechaInput) {
    fechaInput.value = _isoDate(hoy);
    fechaInput.max   = _isoDate(hoy);
    fechaInput.min   = _isoDate(min2);
  }

  // Mostrar total en placeholder del adelanto
  const adelantoInput = document.getElementById('contratoAdelanto');
  if (adelantoInput && total) {
    adelantoInput.placeholder = `Máx. ${formatters.moneda(total)}`;
  }

  // Cargar formas de pago
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

  // Submit
  document.getElementById('btnGenerar')?.addEventListener('click', () => _submit(cotId, total));
}

async function _submit(cotId, total) {
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
    const hoy      = new Date(); hoy.setHours(0, 0, 0, 0);
    const minFecha = new Date(hoy); minFecha.setDate(hoy.getDate() - 2);
    const selDate  = new Date(fechaFirma + 'T00:00:00');
    if (selDate > hoy) {
      alerts.warning('La fecha de pago no puede ser en el futuro.');
      return;
    }
    if (selDate < minFecha) {
      alerts.warning('La fecha de pago no puede ser anterior a 2 días de hoy.');
      return;
    }
  }

  const formaPago     = document.getElementById('contratoFormaPago')?.value ?? '';
  const clausulas     = document.getElementById('contratoClausulas')?.value.trim() ?? '';
  const observaciones = document.getElementById('contratoObservaciones')?.value.trim() ?? '';

  const obsPartes = [];
  if (formaPago)     obsPartes.push(`Forma de pago: ${formaPago}`);
  if (clausulas)     obsPartes.push(`Cláusulas: ${clausulas}`);
  if (observaciones) obsPartes.push(observaciones);
  const obsTexto = obsPartes.join('\n\n') || null;

  const btn = document.getElementById('btnGenerar');
  if (btn) { btn.disabled = true; btn.textContent = 'Generando…'; }

  try {
    await contratoApi.crear({
      id_cotizacion: cotId,
      adelanto,
      fecha_emision: fechaFirma,
      observaciones: obsTexto,
    });
    alerts.ok('Contrato generado correctamente.');
    setTimeout(() => { window.location.href = BASE_URL + 'contratos'; }, 800);
  } catch (e) {
    alerts.error(e.message || 'No se pudo generar el contrato.');
    if (btn) { btn.disabled = false; btn.textContent = 'Generar contrato'; }
  }
}

// ── init ──────────────────────────────────────────────────────────────────────

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

    // Título de la página
    const titleEl = document.getElementById('pageTitleCot');
    if (titleEl) titleEl.textContent = formatters.codigo(cot.id) + ' — ' + (cot.cliente?.nombre_completo ?? '');

    // Bloquear formulario si la cotización ya expiró o no está APROBADA
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
