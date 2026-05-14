import { state, calcularStats }  from './contrato.state.js';
import { ui }                     from './contrato.ui.js';
import { manager }                from './contrato.manager.js';
import { contratoApi }            from '../../api/contrato.api.js';
import { cotizacionApi }          from '../../api/cotizacion.api.js';
import { alerts }                 from '../../utils/alerts.js';
import { formatters }             from '../../utils/formatters.js';

/* ── Helpers internos ───────────────────────────────────────── */
function _renderPagina() {
  const { filtradas, pagina, porPagina } = state;
  const desde = (pagina - 1) * porPagina;
  ui.renderTabla(filtradas.slice(desde, desde + porPagina));
  ui.renderPaginacion(filtradas.length, pagina, porPagina);
}

function _filtrar() {
  const search = (document.getElementById('searchInput')?.value  || '').toLowerCase().trim();
  const estado = (document.getElementById('filterEstado')?.value || '').toLowerCase().trim();

  manager.guardarFiltros({ search, estado });

  const estadoMap = { vigente: 'ACTIVO', completado: 'COMPLETADO', cancelado: 'CANCELADO' };

  state.filtradas = state.filas.filter(c => {
    const nombre   = (c.cliente?.nombre ?? '').toLowerCase();
    const cod      = String(c.id);
    const okSearch = !search || nombre.includes(search) || cod.includes(search);
    const okEstado = !estado || c.estado?.toUpperCase() === (estadoMap[estado] ?? estado.toUpperCase());
    return okSearch && okEstado;
  });

  state.pagina = 1;
  _renderPagina();
}

/* ── Helpers para modo crear / editar en Modal 2 ────────────── */
let _editContratoId = null;

function _setModoModal2(modo) {
  const titulo  = document.getElementById('modal2TitleText');
  const btnVolver = document.getElementById('btnVolverCot');
  const btnText   = document.getElementById('btnSubmitText');

  if (modo === 'editar') {
    if (titulo)    titulo.textContent    = 'Corregir contrato';
    if (btnText)   btnText.textContent   = 'Guardar cambios';
    if (btnVolver) btnVolver.style.display = 'none';
  } else {
    if (titulo)    titulo.textContent    = 'Generar contrato';
    if (btnText)   btnText.textContent   = 'Generar contrato';
    if (btnVolver) btnVolver.style.display = '';
  }
}

function _isoDate(d) {
  const y  = d.getFullYear();
  const m  = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${dd}`;
}

function _limpiarFormContrato() {
  _editContratoId = null;
  ['contratoFechaFirma', 'contratoClausulas', 'contratoObservaciones']
    .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  const adelanto  = document.getElementById('contratoAdelanto');
  const formaPago = document.getElementById('contratoFormaPago');
  if (adelanto)  adelanto.value  = '';
  if (formaPago) formaPago.value = 'Efectivo';

  // Fecha pago de adelanto: máximo hoy, mínimo hace 2 días
  const fechaInput = document.getElementById('contratoFechaFirma');
  if (fechaInput) {
    const hoy  = new Date();
    const min2 = new Date(hoy);
    min2.setDate(hoy.getDate() - 2);
    fechaInput.max = _isoDate(hoy);
    fechaInput.min = _isoDate(min2);
  }
  _setModoModal2('crear');
}

/* ── Paginación y sort ──────────────────────────────────────── */
window.irPagina = function (n) {
  state.pagina = n;
  _renderPagina();
};

window.sortBy = function (key) {
  if (state.sortKey === key) {
    state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
  } else {
    state.sortKey = key;
    state.sortDir = 'asc';
  }

  document.querySelectorAll('.con-table-card thead th').forEach(th => th.classList.remove('sorted'));
  const sortEl = document.getElementById(`sort-${key}`);
  if (sortEl) sortEl.closest('th')?.classList.add('sorted');

  const dir = state.sortDir === 'asc' ? 1 : -1;
  const keyMap = {
    codigo:       'id',
    cotizacionCod:'id_cotizacion',
    cliente:      'cliente',
    tipoEvento:   'tipo_evento',
    fechaEvento:  'fecha_evento',
    total:        'total',
    estado:       'estado',
    creado:       'fecha_creacion',
  };
  const campo = keyMap[key] || key;

  state.filtradas.sort((a, b) => {
    const va = a[campo] ?? '';
    const vb = b[campo] ?? '';
    return va < vb ? -dir : va > vb ? dir : 0;
  });

  _renderPagina();
};

/* ── Modal 1: Seleccionar cotización ────────────────────────── */
let _modal1 = null;
let _modal2 = null;

window.abrirModalCotizaciones = async function () {
  if (!_modal1) _modal1 = new bootstrap.Modal(document.getElementById('modalCotizaciones'));
  _modal1.show();

  const cotEl = document.getElementById('cotizacionesDisponibles');
  if (cotEl) cotEl.innerHTML = `
    <div class="text-center py-3">
      <div class="spinner-border spinner-border-sm" role="status"></div>
    </div>`;

  if (!state.todasCotizaciones.length) {
    try {
      const res = await cotizacionApi.listar();
      const todas = res.data ?? [];
      state.todasCotizaciones = todas.filter(c => c.estado?.toUpperCase() === 'APROBADA');
    } catch {
      if (cotEl) cotEl.innerHTML =
        `<p class="text-danger text-center py-2">No se pudieron cargar las cotizaciones.</p>`;
      return;
    }
  }

  const filtroActual = document.getElementById('searchCotModal')?.value ?? '';
  ui.renderCotizacionesDisponibles(state.todasCotizaciones, filtroActual);
};

window.seleccionarCotizacion = function (id) {
  _modal1?.hide();
  window.location.href = BASE_URL + 'contratos/crear?cot=' + id;
};

window.volverACotizaciones = function () {
  const el2 = document.getElementById('modalGenerarContrato');
  el2.addEventListener('hidden.bs.modal', () => {
    if (!_modal1) _modal1 = new bootstrap.Modal(document.getElementById('modalCotizaciones'));
    _modal1.show();
  }, { once: true });
  _modal2?.hide();
};

window.confirmarContrato = async function () {
  const adelanto = parseFloat(document.getElementById('contratoAdelanto')?.value) || 0;
  if (!adelanto || adelanto <= 0) {
    alerts.warning('Ingresa un adelanto válido.');
    return;
  }

  const fechaFirma = document.getElementById('contratoFechaFirma')?.value || null;
  if (fechaFirma) {
    const hoy      = new Date(); hoy.setHours(0, 0, 0, 0);
    const minFecha = new Date(hoy); minFecha.setDate(hoy.getDate() - 2);
    const selDate  = new Date(fechaFirma + 'T00:00:00');
    if (selDate > hoy) {
      alerts.warning('La fecha de pago de adelanto no puede ser en el futuro.');
      return;
    }
    if (selDate < minFecha) {
      alerts.warning('La fecha de pago de adelanto no puede ser anterior a 2 días de hoy.');
      return;
    }
  }

  const formaPago     = document.getElementById('contratoFormaPago')?.value     || '';
  const clausulas     = document.getElementById('contratoClausulas')?.value.trim()     || '';
  const observaciones = document.getElementById('contratoObservaciones')?.value.trim() || '';

  const obsPartes = [];
  if (formaPago)     obsPartes.push(`Forma de pago: ${formaPago}`);
  if (clausulas)     obsPartes.push(`Cláusulas: ${clausulas}`);
  if (observaciones) obsPartes.push(observaciones);
  const obsTexto = obsPartes.join('\n\n') || null;

  try {
    if (_editContratoId !== null) {
      // ── Modo editar ──────────────────────────────────────────
      await contratoApi.actualizar(_editContratoId, {
        adelanto,
        fecha_emision: fechaFirma,
        observaciones: obsTexto,
      });
      _modal2?.hide();
      alerts.ok('Contrato actualizado correctamente.');
    } else {
      // ── Modo crear ───────────────────────────────────────────
      const cot = state.cotizacionSeleccionada;
      if (!cot) return;
      await contratoApi.crear({
        id_cotizacion: cot.id,
        adelanto,
        fecha_emision: fechaFirma,
        observaciones: obsTexto,
      });
      _modal2?.hide();
      alerts.ok('Contrato generado correctamente.');
      state.todasCotizaciones = [];
    }

    const res = await contratoApi.listar();
    state.filas     = res.data ?? [];
    state.filtradas = state.filas;
    state.pagina    = 1;
    ui.renderStats(calcularStats(state.filas));
    _renderPagina();
  } catch (e) {
    alerts.error(e.message || 'No se pudo guardar el contrato.');
  }
};

/* ── Editar contrato existente ──────────────────────────────── */
window.editarContrato = async function (id) {
  try {
    const res    = await contratoApi.obtener(id);
    const data   = res.data;
    _editContratoId = id;

    // Poblar form con datos actuales
    const fechaInput = document.getElementById('contratoFechaFirma');
    const adelantoInput = document.getElementById('contratoAdelanto');
    const obsInput   = document.getElementById('contratoObservaciones');
    if (fechaInput)   fechaInput.value  = data.fecha_emision ?? '';
    if (adelantoInput) adelantoInput.value = data.adelanto ?? '';
    if (obsInput)     obsInput.value    = data.observaciones ?? '';

    // Quitar restricción de fechas en modo edición
    if (fechaInput) { fechaInput.min = ''; fechaInput.max = ''; }

    // Resumen del contrato en lugar del resumen de cotización
    const cotResumen = document.getElementById('cotResumen');
    if (cotResumen) {
      cotResumen.innerHTML = `
        <div class="con-cot-ref mb-3">
          <i class="bi bi-file-earmark-text" style="font-size:1.1rem;"></i>
          <div style="flex:1;">
            <span>Contrato seleccionado</span>
            <div style="display:flex;gap:10px;align-items:center;margin-top:3px;">
              <strong style="font-size:.9rem;">${data.cliente?.nombre ?? '—'}</strong>
              <span class="con-codigo">${formatters.codigo(id)}</span>
            </div>
          </div>
          <strong style="font-size:.95rem;">${formatters.moneda(data.total)}</strong>
        </div>`;
    }

    _setModoModal2('editar');
    if (!_modal2) _modal2 = new bootstrap.Modal(document.getElementById('modalGenerarContrato'));
    _modal2.show();
  } catch (e) {
    alerts.error(e.message || 'No se pudo cargar el contrato.');
  }
};

/* ── Modal 3: Detalle ───────────────────────────────────────── */
let _modalDet = null;

window.verDetalleContrato = async function (id) {
  if (!_modalDet) _modalDet = new bootstrap.Modal(document.getElementById('modalDetalle'));

  const bodyEl  = document.getElementById('detalleBody');
  const titleEl = document.getElementById('detalleTitle');
  const accsEl  = document.getElementById('detalleAcciones');

  if (titleEl) titleEl.textContent = `Contrato ${formatters.codigo(id)}`;
  if (bodyEl)  bodyEl.innerHTML = `
    <div class="text-center py-3">
      <div class="spinner-border spinner-border-sm" role="status"></div>
    </div>`;
  if (accsEl) accsEl.innerHTML = '';

  _modalDet.show();

  try {
    const res  = await contratoApi.obtener(id);
    const data = res.data;

    if (bodyEl) bodyEl.innerHTML = ui.renderDetalle(data);

    if (accsEl) {
      const isActivo = data.estado?.toUpperCase() === 'ACTIVO';
      accsEl.innerHTML = `
        <a href="${BASE_URL}contratos/${id}" target="_blank"
           class="btn btn-sm btn-outline-primary">
          <i class="bi bi-printer me-1"></i>Imprimir contrato
        </a>
        ${isActivo ? `
        <button class="btn btn-sm btn-outline-success" onclick="abrirModalPago(${id})">
          <i class="bi bi-cash-coin me-1"></i>Añadir pago
        </button>
        <button class="btn btn-sm btn-success" onclick="cambiarEstadoContrato(${id},'COMPLETADO')">
          <i class="bi bi-check-circle me-1"></i>Completar
        </button>
        <button class="btn btn-sm btn-outline-danger"
                onclick="confirmarEliminar(${id},'${formatters.codigo(id)}')">
          <i class="bi bi-x-circle me-1"></i>Cancelar
        </button>` : ''}`;
    }
  } catch (e) {
    if (bodyEl) bodyEl.innerHTML =
      `<p class="text-danger text-center py-2">${e.message}</p>`;
  }
};

window.cambiarEstadoContrato = async function (id, estado) {
  try {
    await contratoApi.cambiarEstado(id, estado);
    _modalDet?.hide();
    alerts.ok(estado === 'COMPLETADO' ? 'Contrato completado.' : 'Estado actualizado.');

    const row = state.filas.find(c => c.id == id);
    if (row) row.estado = estado;
    ui.renderStats(calcularStats(state.filas));
    _renderPagina();
  } catch (e) {
    alerts.error(e.message || 'No se pudo actualizar el estado.');
  }
};

/* ── Modal 4: Confirmar cancelar ────────────────────────────── */
let _pendingId = null;
let _modalDel  = null;

let _modalPago        = null;
let _pagoContratoId   = null;
let _pagoContratoData = null;

window.confirmarEliminar = function (id, codigo) {
  _pendingId = id;
  const span = document.getElementById('confirmCod');
  if (span) span.textContent = codigo;
  if (!_modalDel) _modalDel = new bootstrap.Modal(document.getElementById('modalConfirm'));
  _modalDel.show();
};

window.eliminarContrato = async function () {
  if (!_pendingId) return;
  try {
    await contratoApi.cambiarEstado(_pendingId, 'CANCELADO');
    _modalDel?.hide();
    _modalDet?.hide();
    alerts.ok('Contrato cancelado.');

    const row = state.filas.find(c => c.id == _pendingId);
    if (row) row.estado = 'CANCELADO';
    _pendingId = null;

    ui.renderStats(calcularStats(state.filas));
    _renderPagina();
  } catch (e) {
    alerts.error(e.message || 'No se pudo cancelar el contrato.');
  }
};

/* ── Modal 5: Registrar pago ────────────────────────────────── */
function _setResumenPago(data) {
  const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  if (!data) {
    set('pagoTotal', '…'); set('pagoPagado', '…'); set('pagoSaldo', '…');
    return;
  }
  set('pagoTotal',  formatters.moneda(data.total));
  set('pagoPagado', formatters.moneda(data.total_pagado));
  set('pagoSaldo',  formatters.moneda(data.saldo));
}

function _renderHistorialPago(pagos) {
  const el = document.getElementById('pagoHistorial');
  if (!el) return;
  if (!pagos.length) {
    el.innerHTML = '<p style="font-size:.78rem;color:var(--text-muted);text-align:center;padding:8px 0;margin:0;">Sin pagos registrados</p>';
    return;
  }
  const rows = pagos.map(p => `
    <tr style="border-top:1px solid var(--border-color);">
      <td style="padding:4px 8px;">${formatters.fecha(p.fecha)}</td>
      <td style="padding:4px 8px;">${p.nombre_forma_pago ?? '—'}</td>
      <td style="padding:4px 8px;text-align:right;">${formatters.moneda(p.monto)}</td>
    </tr>`).join('');
  el.innerHTML = `
    <table style="width:100%;font-size:.78rem;border-collapse:collapse;">
      <thead>
        <tr style="background:var(--bg-hover);">
          <th style="padding:4px 8px;color:var(--text-muted);font-weight:600;text-align:left;">Fecha</th>
          <th style="padding:4px 8px;color:var(--text-muted);font-weight:600;text-align:left;">Método</th>
          <th style="padding:4px 8px;color:var(--text-muted);font-weight:600;text-align:right;">Monto</th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>`;
}

window.abrirModalPago = async function (id) {
  _pagoContratoId   = id;
  _pagoContratoData = null;

  if (!_modalPago) _modalPago = new bootstrap.Modal(document.getElementById('modalPago'));

  // Set title
  const titleEl = document.getElementById('pagoTitle');
  if (titleEl) titleEl.innerHTML =
    `<i class="bi bi-cash-coin me-2" style="color:var(--green-text);"></i>Registrar pago · ${formatters.codigo(id)}`;

  // Fecha: hoy por defecto, máximo hoy, mínimo hace 3 días
  const fechaInput = document.getElementById('pagoFecha');
  if (fechaInput) {
    const hoy  = new Date();
    const min3 = new Date(hoy);
    min3.setDate(hoy.getDate() - 3);
    fechaInput.value = _isoDate(hoy);
    fechaInput.max   = _isoDate(hoy);
    fechaInput.min   = _isoDate(min3);
  }

  // Reset form
  ['pagoMonto', 'pagoVoucher'].forEach(fid => { const el = document.getElementById(fid); if (el) el.value = ''; });
  const selectForma = document.getElementById('pagoFormaPago');
  if (selectForma) selectForma.innerHTML = '<option value="">— Cargando... —</option>';

  _setResumenPago(null);
  document.getElementById('pagoHistorial').innerHTML =
    '<p style="font-size:.78rem;color:var(--text-muted);text-align:center;padding:8px 0;margin:0;">Cargando...</p>';

  _modalPago.show();

  try {
    const [resContrato, resFormas] = await Promise.all([
      contratoApi.obtener(id),
      contratoApi.formasPago(),
    ]);

    _pagoContratoData = resContrato.data;

    if (selectForma) {
      const formas = resFormas.data ?? [];
      selectForma.innerHTML = '<option value="">— Seleccionar —</option>' +
        formas.map(f => `<option value="${f.id_form_pago}">${f.nombre_forma_pago}</option>`).join('');
    }

    _setResumenPago(_pagoContratoData);
    _renderHistorialPago(_pagoContratoData.pagos ?? []);
  } catch (e) {
    alerts.error(e.message || 'No se pudo cargar los datos del contrato.');
  }
};

window.confirmarPago = async function () {
  const monto   = parseFloat(document.getElementById('pagoMonto')?.value) || 0;
  const forma   = document.getElementById('pagoFormaPago')?.value ?? '';
  const fecha   = document.getElementById('pagoFecha')?.value ?? '';
  const voucher = document.getElementById('pagoVoucher')?.value.trim() || null;

  // ── Validaciones ────────────────────────────────────────────
  if (!monto || monto <= 0) {
    alerts.warning('Ingresa un monto válido mayor a cero.');
    return;
  }
  const saldo = _pagoContratoData?.saldo ?? 0;
  if (monto > saldo + 0.001) {
    alerts.warning(`El monto (${formatters.moneda(monto)}) supera el saldo pendiente (${formatters.moneda(saldo)}).`);
    return;
  }
  if (!forma) {
    alerts.warning('Selecciona una forma de pago.');
    return;
  }
  if (!fecha) {
    alerts.warning('Selecciona la fecha de pago.');
    return;
  }
  const hoy      = new Date(); hoy.setHours(0, 0, 0, 0);
  const minFecha = new Date(hoy); minFecha.setDate(hoy.getDate() - 3);
  const selDate  = new Date(fecha + 'T00:00:00');
  if (selDate > hoy) {
    alerts.warning('La fecha de pago no puede ser en el futuro.');
    return;
  }
  if (selDate < minFecha) {
    alerts.warning('La fecha de pago no puede ser anterior a 3 días de hoy.');
    return;
  }

  try {
    await contratoApi.registrarPago({
      id_contrato:  _pagoContratoId,
      id_form_pago: parseInt(forma),
      monto,
      fecha,
      voucher,
      moneda: 'PEN',
    });

    alerts.ok('Pago registrado correctamente.');

    // Reset campos
    document.getElementById('pagoMonto').value = '';
    if (document.getElementById('pagoVoucher')) document.getElementById('pagoVoucher').value = '';

    // Refrescar datos del contrato
    const resContrato = await contratoApi.obtener(_pagoContratoId);
    _pagoContratoData = resContrato.data;

    // Actualizar pago modal
    _setResumenPago(_pagoContratoData);
    _renderHistorialPago(_pagoContratoData.pagos ?? []);

    // Actualizar cuerpo del modal detalle (sigue abierto detrás)
    const bodyEl = document.getElementById('detalleBody');
    if (bodyEl) bodyEl.innerHTML = ui.renderDetalle(_pagoContratoData);

    // Si el contrato quedó COMPLETADO, cerrar modal pago y limpiar botones de detalle
    if (_pagoContratoData.estado?.toUpperCase() !== 'ACTIVO') {
      _modalPago?.hide();
      const accsEl = document.getElementById('detalleAcciones');
      if (accsEl) accsEl.innerHTML = '';
    }

    // Refrescar tabla principal
    const resLista = await contratoApi.listar();
    state.filas = resLista.data ?? [];
    ui.renderStats(calcularStats(state.filas));
    _filtrar();
  } catch (e) {
    alerts.error(e.message || 'No se pudo registrar el pago.');
  }
};

/* ── Inicializar listeners del DOM ──────────────────────────── */
export function initEvents() {
  document.getElementById('searchInput') ?.addEventListener('input',  _filtrar);
  document.getElementById('filterEstado')?.addEventListener('change', _filtrar);
  document.getElementById('searchCotModal')?.addEventListener('input', function () {
    ui.renderCotizacionesDisponibles(state.todasCotizaciones, this.value);
  });
}

export { _filtrar, _renderPagina };

/* ── Abrir directamente Modal 2 con una cotización pre-seleccionada ── */
export async function abrirConCotizacionId(cotId) {
  if (!state.todasCotizaciones.length) {
    try {
      const res = await cotizacionApi.listar();
      state.todasCotizaciones = (res.data ?? []).filter(c => c.estado?.toUpperCase() === 'APROBADA');
    } catch {
      alerts.error('No se pudieron cargar las cotizaciones.');
      return;
    }
  }

  const cot = state.todasCotizaciones.find(c => c.id === cotId || c.id === parseInt(cotId));
  if (!cot) {
    alerts.warning('Cotización no encontrada o ya tiene un contrato activo.');
    return;
  }

  state.cotizacionSeleccionada = cot;
  _limpiarFormContrato();
  ui.renderResumenCotizacion(cot);

  if (!_modal2) _modal2 = new bootstrap.Modal(document.getElementById('modalGenerarContrato'));
  _modal2.show();
}
