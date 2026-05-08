import { clienteApi }  from '../../api/cliente.api.js';
import { paqueteApi }  from '../../api/paquete.api.js';
import { cotizacionApi } from '../../api/cotizacion.api.js';
import { manager }     from './cotizacion.manager.js';
import {
  initForm, agregarFila, eliminarFila,
  onTipoChange, onPaqueteChange, onCantidadChange, onPrecioChange,
  validarForm, buildPayload,
} from './cotizacion.form.js';
import { alerts } from '../../utils/alerts.js';

/* ── 1. Cargar clientes y paquetes en paralelo ───────────────── */
let clientes = [], paquetes = [];
try {
  const [resC, resP] = await Promise.all([
    clienteApi.listar(),
    paqueteApi.listar({ estado: 'ACTIVO' }),
  ]);
  clientes = resC.data ?? [];
  paquetes = resP.data ?? [];
} catch {
  alerts.error('Error cargando datos iniciales. Recarga la página.');
}

/* ── 2. Inicializar módulo de form con los paquetes ─────────── */
initForm(paquetes);

/* ── 3. Poblar select de clientes ────────────────────────────── */
const selectCliente = document.getElementById('id_cliente');
if (selectCliente) {
  clientes
    .filter(c => c.estado === 'ACTIVO')
    .forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.id_cliente;
      opt.textContent = `${c.nombres} ${c.apellidos || ''}`.trim();
      selectCliente.appendChild(opt);
    });
}

/* ── 4. Restaurar borrador guardado en localStorage ─────────── */
const borrador = manager.cargar();
if (borrador) {
  if (borrador.id_cliente && selectCliente) selectCliente.value = borrador.id_cliente;
  const obsEl = document.getElementById('observaciones');
  if (borrador.observaciones && obsEl) obsEl.value = borrador.observaciones;
}

/* ── 5. Añadir primera fila vacía ────────────────────────────── */
agregarFila();

/* ── 6. Delegación de eventos en la tabla de ítems ──────────── */
const tbody = document.getElementById('detallesCuerpo');

tbody?.addEventListener('change', e => {
  if (e.target.classList.contains('tipo-select'))    onTipoChange(e.target);
  if (e.target.classList.contains('paquete-select')) onPaqueteChange(e.target);
  if (e.target.classList.contains('cant-input'))     onCantidadChange(e.target);
  if (e.target.classList.contains('precio-input'))   onPrecioChange(e.target);
});

tbody?.addEventListener('input', e => {
  if (e.target.classList.contains('cant-input') ||
      e.target.classList.contains('precio-input')) {
    onCantidadChange(e.target);
  }
});

tbody?.addEventListener('click', e => {
  const btn = e.target.closest('.btn-del-fila');
  if (btn) eliminarFila(btn);
});

/* ── 7. Botón "Agregar ítem" ─────────────────────────────────── */
document.getElementById('btnAgregarFila')?.addEventListener('click', agregarFila);

/* ── 8. Auto-guardar borrador al editar campos de cabecera ───── */
function _saveDraft() {
  manager.guardar({
    id_cliente:    selectCliente?.value ?? null,
    observaciones: document.getElementById('observaciones')?.value ?? '',
  });
}
selectCliente?.addEventListener('change', _saveDraft);
document.getElementById('observaciones')?.addEventListener('input', _saveDraft);

/* ── 9. Submit ───────────────────────────────────────────────── */
document.getElementById('formCrear')?.addEventListener('submit', async e => {
  e.preventDefault();

  const errores = validarForm();
  if (errores.length) {
    alerts.error(errores[0]);
    return;
  }

  const btnGuardar = document.getElementById('btnGuardar');
  btnGuardar.disabled = true;
  btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Guardando...';

  try {
    const payload = buildPayload(window.CURRENT_USER_ID ?? 1);
    await cotizacionApi.crear(payload);
    manager.limpiar();
    alerts.ok('Cotización creada correctamente.');
    setTimeout(() => { window.location.href = (window.BASE_URL ?? '/') + 'cotizaciones'; }, 1100);
  } catch (err) {
    alerts.error(err.message || 'Error al crear la cotización.');
    btnGuardar.disabled = false;
    btnGuardar.innerHTML = '<i class="bi bi-save me-1"></i>Guardar cotización';
  }
});
