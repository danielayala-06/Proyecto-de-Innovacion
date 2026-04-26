import {getAllItems, calcularTotal, seleccionarOpcion} from "./cotizacionManager.js";
import {fetchPaquetes} from "../../api/paquetesApi.js";

/**
 * UI: render y manipulación del DOM
 */

export function abrirModal(id) {
    new bootstrap.Modal(document.getElementById(id)).show();
}

export function cerrarModal(id) {
    bootstrap.Modal.getInstance(document.getElementById(id)).hide();
}

export function renderResumen() {
    const items = getAllItems();
    const cont = document.getElementById("resumenItems");

    cont.innerHTML = items.length
        ? items
            .map(
                (i) => `
        <div class="resumen-row">
          <span>${i.nombre}</span>
          <span>S/ ${i.precio.toFixed(2)}</span>
        </div>`
            )
            .join("")
        : `<div class="resumen-row">Sin ítems</div>`;

    document.getElementById(
        "totalResumen"
    ).textContent = `S/ ${calcularTotal().toFixed(2)}`;
}

function renderPaquetes() {
    const cont = document.getElementById('paquetesContainer');
    cont.innerHTML = paquetes.map((p, i) => `
                <div style="background:#1a1a1a;border:1px solid #2e2e2e;border-radius:8px;padding:10px 13px;font-size:0.81rem;">
                  <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:#ccc;font-weight:500;">${p.nombre}</span>
                    <button style="background:none;border:none;color:#555;cursor:pointer;" onclick="eliminarPaquete(${i})">
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                  ${p.desc ? `<div style="color:#555;font-size:0.74rem;margin-top:2px;">${p.desc}</div>` : ''}
                  <div style="color:#5a9eff;margin-top:4px;font-size:0.82rem;">S/ ${p.precio.toFixed(2)}</div>
                </div>`).join('');
}
export async function renderPaquetesModal() {
    // Referencia al container de PAQUETES
    const cont = document.getElementById('panel-quinceaneros');

    // Limpiamos el modal
    cont.innerHTML = '';

    // Obtenemos los paquetes
    const paquetes = await fetchPaquetes();

    // En caso de que no haya paquetes
    if (!paquetes) return;

    // Convertimos el objeto a array
    const arrayPaquetes = Object.values(paquetes);

    // Iteramos correctamente el array
    arrayPaquetes.forEach(paquete => {
        layoutModalPaquete(paquete, cont);
    });
}

/**
 *         "id_paquete": "1",
 *         "nombre_paquete": "Paquete Básico",
 *         "precio_base": "500.00",
 *         "imagen": null,
 *         "descripcion": "Incluye fotografía",
 *         "estado": "ACTIVO"
 */

/**
 * Renderiza el html de un paquete
 * @param {Object} paquete - es un objeto que tendra la data de los paquetes.
 * @param {HTMLElement} html_container - Sera el contenedor de los paquetes generados.
 */
function layoutModalPaquete(paquete, html_container){

    // En caso de que no se encuentre el contenedor
    if(!html_container)return;

    // NO mostrar los paquetes inactivos
    if(paquete['estado'] === 'INACTIVO') return;


    const newPaquete = `
         <div class="paquete-option" id="paquete-${paquete['id_paquete']}" >
            <div class="po-left">
                <div class="po-name">${paquete['nombre_paquete']}</div>
                <div class="po-desc">${paquete['descripcion']}</div>
            </div>

            <span class="po-price">S/ ${paquete['precio_base']}</span>
            <i class="bi bi-check-circle-fill po-check"></i>
         </div>`

    // Agregamos el paquete renderizado al modal cotizaciones
    html_container.innerHTML += newPaquete

    // Referenciamos al paquete
    const contPaquete = document.getElementById(`paquete-${paquete['id_paquete']}`)
}
function layoutPaqueteRender(paquete, html_container){
    cont.innerHTML = paquetes.map((p, i) => `
                <div style="background:#1a1a1a;border:1px solid #2e2e2e;border-radius:8px;padding:10px 13px;font-size:0.81rem;">
                  <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:#ccc;font-weight:500;">${paquete['nombre_paquete']}</span>
                    <button style="background:none;border:none;color:#555;cursor:pointer;" onclick="eliminarPaquete(${paquete['id_paquete']})">
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                  ${paquete['descripcion'] ? `<div style="color:#555;font-size:0.74rem;margin-top:2px;">${paquete['descripcion']}</div>` : ''}
                  <div style="color:#5a9eff;margin-top:4px;font-size:0.82rem;">S/ ${paquete['precio_base']}</div>
                </div>`);
}
