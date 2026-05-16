/**
 * @file    cotizacion.form.js
 * @module  modules/cotizaciones/form
 *
 * Gestiona la tabla de ítems del formulario de cotizaciones (vista legacy con tabla editable).
 * Cada fila de la tabla representa un ítem de tipo PAQUETE o PERSONALIZADO.
 *
 * Responsabilidades:
 *  - Construir y agregar filas dinámicas al `<tbody id="detallesCuerpo">`.
 *  - Recalcular subtotales y total general en tiempo real.
 *  - Reaccionar a cambios de tipo (PAQUETE/PERSONALIZADO) y de paquete seleccionado.
 *  - Validar el formulario completo y construir el payload para la API.
 */

import { formatters } from '../../utils/formatters.js';

/** @type {Array<Object>} Caché de paquetes activos cargados desde la API. */
let _paquetes = [];

/**
 * Inicializa el módulo con la lista de paquetes disponibles.
 * Debe llamarse antes de `agregarFila()` para que el select de paquetes esté poblado.
 *
 * @param {Array<Object>} paquetes - Lista de paquetes activos obtenida de la API.
 * @returns {void}
 */
export function initForm(paquetes) {
  _paquetes = paquetes;
}

/**
 * Genera el HTML interno de una nueva fila de ítem para el `<tbody>`.
 * El select de paquetes solo muestra paquetes con estado ACTIVO.
 *
 * @returns {string} HTML de las celdas `<td>` de la fila.
 */
function _buildRowHtml() {
  const opsPaquete = _paquetes
    .filter(p => p.estado === 'ACTIVO')
    .map(p =>
      `<option value="${p.id_paquete}" data-precio="${p.precio}" data-nombre="${p.nombre_paquete}">
        ${p.nombre_paquete} — ${formatters.moneda(p.precio)}
      </option>`
    ).join('');

  return `
    <td>
      <select class="form-select form-select-sm tipo-select" required>
        <option value="">Tipo...</option>
        <option value="PAQUETE">Paquete</option>
        <option value="PERSONALIZADO">Personalizado</option>
      </select>
    </td>
    <td>
      <div class="campo-paquete" style="display:none;">
        <select class="form-select form-select-sm paquete-select">
          <option value="">Seleccionar paquete...</option>
          ${opsPaquete}
        </select>
      </div>
      <div class="campo-desc">
        <input type="text" class="form-control form-control-sm desc-input"
               placeholder="Descripción del ítem...">
      </div>
    </td>
    <td>
      <input type="number" class="form-control form-control-sm cant-input"
             value="1" min="1" max="999">
    </td>
    <td>
      <input type="number" class="form-control form-control-sm precio-input"
             value="" min="0" step="0.01" placeholder="0.00">
    </td>
    <td class="text-end">
      <span class="subtotal-span" style="font-size:.84rem;white-space:nowrap;">S/ 0.00</span>
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-link text-danger p-0 btn-del-fila" title="Quitar ítem">
        <i class="bi bi-trash3" style="font-size:.95rem;"></i>
      </button>
    </td>`;
}

/**
 * Agrega una nueva fila vacía al final del `<tbody id="detallesCuerpo">`.
 * Oculta el mensaje "sin ítems" si existe.
 *
 * @returns {void}
 */
export function agregarFila() {
  const tbody  = document.getElementById('detallesCuerpo');
  const sinDet = document.getElementById('sinDetalles');
  if (!tbody) return;
  if (sinDet) sinDet.style.display = 'none';

  const tr = document.createElement('tr');
  tr.innerHTML = _buildRowHtml();
  tbody.appendChild(tr);
  _actualizarTotal();
}

/**
 * Elimina la fila correspondiente al botón presionado.
 * Muestra el mensaje "sin ítems" si el tbody queda vacío.
 *
 * @param {HTMLButtonElement} btn - Botón de eliminar dentro de la fila.
 * @returns {void}
 */
export function eliminarFila(btn) {
  btn.closest('tr')?.remove();
  const tbody  = document.getElementById('detallesCuerpo');
  const sinDet = document.getElementById('sinDetalles');
  if (sinDet) sinDet.style.display = tbody?.children.length === 0 ? '' : 'none';
  _actualizarTotal();
}

/**
 * Reacciona al cambio de tipo en el select de tipo (PAQUETE / PERSONALIZADO).
 * Muestra u oculta el campo de paquete o descripción según corresponda.
 * Pre-llena el precio si hay un paquete ya seleccionado.
 *
 * @param {HTMLSelectElement} select - Select de tipo de ítem.
 * @returns {void}
 */
export function onTipoChange(select) {
  const tr   = select.closest('tr');
  const tipo = select.value;
  const campoPaq = tr.querySelector('.campo-paquete');
  const campoDes = tr.querySelector('.campo-desc');
  const precioIn = tr.querySelector('.precio-input');
  const paqSel   = tr.querySelector('.paquete-select');

  if (tipo === 'PAQUETE') {
    campoPaq.style.display = '';
    campoDes.style.display = 'none';
    const opt = paqSel?.options[paqSel.selectedIndex];
    if (opt?.dataset.precio) precioIn.value = opt.dataset.precio;
  } else {
    campoPaq.style.display = 'none';
    campoDes.style.display = '';
    precioIn.value = '';
  }
  _actualizarSubtotal(tr);
}

/**
 * Reacciona al cambio de paquete seleccionado en el select de paquetes.
 * Pre-llena el precio y la descripción con los datos del `data-*` del option.
 *
 * @param {HTMLSelectElement} select - Select de paquetes.
 * @returns {void}
 */
export function onPaqueteChange(select) {
  const tr    = select.closest('tr');
  const opt   = select.options[select.selectedIndex];
  const prIn  = tr.querySelector('.precio-input');
  const desIn = tr.querySelector('.desc-input');

  if (opt?.dataset.precio) {
    prIn.value  = opt.dataset.precio;
    if (desIn)  desIn.value = opt.dataset.nombre || '';
  }
  _actualizarSubtotal(tr);
}

/**
 * Recalcula el subtotal (cantidad × precio) para una fila y actualiza el total general.
 *
 * @param {HTMLTableRowElement} tr - Fila cuyo subtotal debe recalcularse.
 * @returns {void}
 */
function _actualizarSubtotal(tr) {
  const cant    = parseFloat(tr.querySelector('.cant-input')?.value  || 0);
  const precio  = parseFloat(tr.querySelector('.precio-input')?.value || 0);
  const span    = tr.querySelector('.subtotal-span');
  if (span) span.textContent = formatters.moneda(cant * precio);
  _actualizarTotal();
}

/**
 * Recalcula la suma de todos los subtotales y actualiza el elemento `#totalEstimado`.
 *
 * @returns {void}
 */
function _actualizarTotal() {
  let total = 0;
  document.querySelectorAll('#detallesCuerpo tr').forEach(tr => {
    const cant   = parseFloat(tr.querySelector('.cant-input')?.value  || 0);
    const precio = parseFloat(tr.querySelector('.precio-input')?.value || 0);
    total += cant * precio;
  });
  const el = document.getElementById('totalEstimado');
  if (el) el.textContent = formatters.moneda(total);
}

/**
 * Delegado de evento para el input de cantidad de una fila.
 *
 * @param {HTMLInputElement} input - Input de cantidad.
 * @returns {void}
 */
export function onCantidadChange(input) { _actualizarSubtotal(input.closest('tr')); }

/**
 * Delegado de evento para el input de precio de una fila.
 *
 * @param {HTMLInputElement} input - Input de precio.
 * @returns {void}
 */
export function onPrecioChange(input)   { _actualizarSubtotal(input.closest('tr')); }

/**
 * Valida el formulario completo de cotización (cliente + filas de ítems).
 *
 * @returns {string[]} Array de mensajes de error; vacío si el formulario es válido.
 */
export function validarForm() {
  const errores = [];

  if (!document.getElementById('id_cliente')?.value) {
    errores.push('Selecciona un cliente.');
  }

  const filas = document.querySelectorAll('#detallesCuerpo tr');
  if (!filas.length) {
    errores.push('Agrega al menos un ítem a la cotización.');
    return errores;
  }

  filas.forEach((tr, i) => {
    const n      = i + 1;
    const tipo   = tr.querySelector('.tipo-select')?.value;
    const cant   = parseFloat(tr.querySelector('.cant-input')?.value  || 0);
    const precio = parseFloat(tr.querySelector('.precio-input')?.value || 0);

    if (!tipo)      errores.push(`Ítem ${n}: selecciona un tipo.`);
    if (cant < 1)   errores.push(`Ítem ${n}: la cantidad debe ser al menos 1.`);
    if (precio <= 0) errores.push(`Ítem ${n}: el precio debe ser mayor a 0.`);

    if (tipo === 'PAQUETE' && !tr.querySelector('.paquete-select')?.value) {
      errores.push(`Ítem ${n}: selecciona un paquete.`);
    }
    if (tipo === 'PERSONALIZADO' && !tr.querySelector('.desc-input')?.value?.trim()) {
      errores.push(`Ítem ${n}: ingresa una descripción.`);
    }
  });

  return errores;
}

/**
 * Construye el payload para `POST /api/cotizaciones` recorriendo todas las filas del tbody.
 *
 * @param {number} idUsuario - ID del usuario autenticado (obtenido de la sesión PHP).
 * @returns {{
 *   id_cliente: number,
 *   id_usuario: number,
 *   observaciones: string|null,
 *   detalles: Array<{
 *     tipo_item: string,
 *     id_referencia: number|null,
 *     descripcion: string,
 *     cantidad: number,
 *     precio_unitario: number,
 *     subtotal: number
 *   }>
 * }} Payload listo para enviar a la API.
 */
export function buildPayload(idUsuario) {
  const detalles = [];

  document.querySelectorAll('#detallesCuerpo tr').forEach(tr => {
    const tipo   = tr.querySelector('.tipo-select')?.value;
    const cant   = parseInt(tr.querySelector('.cant-input')?.value  || 1);
    const precio = parseFloat(tr.querySelector('.precio-input')?.value || 0);
    let desc = '', idRef = null;

    if (tipo === 'PAQUETE') {
      const sel = tr.querySelector('.paquete-select');
      idRef = parseInt(sel?.value) || null;
      desc  = sel?.options[sel.selectedIndex]?.dataset.nombre || '';
    } else {
      desc = tr.querySelector('.desc-input')?.value?.trim() || '';
    }

    detalles.push({
      tipo_item:       tipo,
      id_referencia:   idRef,
      descripcion:     desc,
      cantidad:        cant,
      precio_unitario: precio,
      subtotal:        cant * precio,
    });
  });

  return {
    id_cliente:    parseInt(document.getElementById('id_cliente').value),
    id_usuario:    idUsuario,
    observaciones: document.getElementById('observaciones')?.value?.trim() || null,
    detalles,
  };
}
