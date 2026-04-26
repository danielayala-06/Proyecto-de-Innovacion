import {
    getAllItems,
    calcularTotal,
    getServiciosCustom,
    getPaquetes,
    togglePreset,
    setFotoPrecio,
    agregarServicio,
    eliminarServicio,
    agregarPaquete,
    eliminarPaquete as eliminarPaqueteEstado,
    seleccionarPaquete,
    confirmarPaquete,
} from "./cotizacionManager.js";
import { fetchPaquetes } from "../../api/paquetesApi.js";
/**
 * UI: render y manipulación del DOM
 */

// ──────────────────────────────────────────
// MODALES
// ──────────────────────────────────────────

export function abrirModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    new bootstrap.Modal(el).show();
}

export function cerrarModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const instance = bootstrap.Modal.getInstance(el);
    if (instance) instance.hide();
}

// ──────────────────────────────────────────
// RESUMEN LATERAL
// ──────────────────────────────────────────

export function renderResumen() {
    const items = getAllItems();
    const cont  = document.getElementById("resumenItems");
    if (!cont) return;

    cont.innerHTML = items.length
        ? items.map(i => `
            <div class="resumen-row">
                <span>${i.nombre}</span>
                <span>S/ ${Number(i.precio).toFixed(2)}</span>
            </div>`).join("")
        : `<div class="resumen-row" style="color:#555;font-size:0.79rem;justify-content:center;">Sin ítems aún</div>`;

    const totalEl = document.getElementById("totalResumen");
    if (totalEl) totalEl.textContent = `S/ ${calcularTotal().toFixed(2)}`;
}

// ──────────────────────────────────────────
// SERVICIOS CUSTOM
// ──────────────────────────────────────────

export function renderServiciosCustom() {
    const cont     = document.getElementById("serviciosList");
    const servicios = getServiciosCustom();
    if (!cont) return;

    if (!servicios.length) {
        cont.innerHTML = `<div class="servicios-empty">Sin servicios adicionales</div>`;
        return;
    }

    cont.innerHTML = servicios.map((s, i) => `
        <div class="servicio-item">
            <span class="servicio-name">${s.nombre}</span>
            <span class="servicio-price">S/ ${s.precio.toFixed(2)}</span>
            <button class="btn-remove" data-index="${i}">
                <i class="bi bi-x"></i>
            </button>
        </div>`).join("");

    // Eventos de eliminar
    cont.querySelectorAll(".btn-remove").forEach(btn => {
        btn.addEventListener("click", () => {
            eliminarServicio(parseInt(btn.dataset.index));
            renderServiciosCustom();
            renderResumen();
        });
    });
}

// ──────────────────────────────────────────
// PAQUETES SELECCIONADOS (lista en formulario)
// ──────────────────────────────────────────

export function renderPaquetesSeleccionados() {
    const cont    = document.getElementById("paquetesContainer");
    const paquetes = getPaquetes();
    if (!cont) return;

    cont.innerHTML = paquetes.map((p, i) => `
        <div style="background:#1a1a1a;border:1px solid #2e2e2e;border-radius:8px;padding:10px 13px;font-size:0.81rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="color:#ccc;font-weight:500;">${p.nombre}</span>
                <button style="background:none;border:none;color:#555;cursor:pointer;" data-index="${i}">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            ${p.descripcion ? `<div style="color:#555;font-size:0.74rem;margin-top:2px;">${p.descripcion}</div>` : ""}
            <div style="color:#5a9eff;margin-top:4px;font-size:0.82rem;">S/ ${Number(p.precio).toFixed(2)}</div>
        </div>`).join("");

    cont.querySelectorAll("button[data-index]").forEach(btn => {
        btn.addEventListener("click", () => {
            eliminarPaqueteEstado(parseInt(btn.dataset.index));
            renderPaquetesSeleccionados();
            renderResumen();
        });
    });
}

// ──────────────────────────────────────────
// MODAL PAQUETES – carga dinámica desde API
// ──────────────────────────────────────────

export async function renderPaquetesModal() {
    const cont = document.getElementById("panel-quinceaneros");
    if (!cont) return;

    cont.innerHTML = `<div style="color:#555;padding:1rem;text-align:center;">Cargando paquetes...</div>`;

    const paquetes = await fetchPaquetes();

    if (!paquetes) {
        cont.innerHTML = `<div style="color:#e07070;padding:1rem;">Error al cargar paquetes.</div>`;
        return;
    }

    const arrayPaquetes = Array.isArray(paquetes) ? paquetes : Object.values(paquetes);
    const activos = arrayPaquetes.filter(p => p.estado !== "INACTIVO");

    if (!activos.length) {
        cont.innerHTML = `<div style="color:#555;padding:1rem;text-align:center;">No hay paquetes disponibles.</div>`;
        return;
    }

    cont.innerHTML = "";
    activos.forEach(paquete => _renderLayoutPaqueteModal(paquete, cont));
}

function _renderLayoutPaqueteModal(paquete, container) {
    const div = document.createElement("div");
    div.className = "paquete-option";
    div.id = `paquete-${paquete.id_paquete}`;
    div.innerHTML = `
        <div class="po-left">
            <div class="po-name">${paquete.nombre_paquete}</div>
            <div class="po-desc">${paquete.descripcion || ""}</div>
        </div>
        <span class="po-price">S/ ${Number(paquete.precio_base).toFixed(2)}</span>
        <i class="bi bi-check-circle-fill po-check"></i>`;

    div.addEventListener("click", () => seleccionarPaquete(paquete, div));
    container.appendChild(div);
}

// ──────────────────────────────────────────
// CAMBIO DE CATEGORÍA EN EL MODAL PAQUETES
// ──────────────────────────────────────────

export function cambiarCategoria(cat, tabEl) {
    document.querySelectorAll(".cat-tab").forEach(t => t.classList.remove("active"));
    document.querySelectorAll(".cat-panel").forEach(p => p.classList.remove("active"));
    tabEl.classList.add("active");
    const panel = document.getElementById("panel-" + cat);
    if (panel) panel.classList.add("active");
}

// ──────────────────────────────────────────
// EXPONER FUNCIONES AL SCOPE GLOBAL
// (necesario porque el HTML usa onclick= con funciones que viven en módulos ES)
// ──────────────────────────────────────────

window.togglePreset = (key) => {
    togglePreset(key);
    // Actualiza el botón visualmente
    const btn = document.getElementById("btn-" + key);
    if (btn) btn.classList.toggle("selected", !btn.classList.contains("selected"));
    // Re-sincroniza: el estado ya se actualizó, usamos el getter
    renderResumen();
};

window.onFotoPrecioInput = () => {
    const input = document.getElementById("fotoPrecioInput");
    if (input) setFotoPrecio(input.value);
    renderResumen();
};

window.agregarServicioCustom = () => {
    const nombre = document.getElementById("servicioNombre")?.value.trim();
    const precio = document.getElementById("servicioPrecio")?.value;

    if (!nombre) return;

    agregarServicio(nombre, precio);
    renderServiciosCustom();
    renderResumen();

    if (document.getElementById("servicioNombre")) document.getElementById("servicioNombre").value = "";
    if (document.getElementById("servicioPrecio")) document.getElementById("servicioPrecio").value = "";

    cerrarModal("modalServicio");
};

window.cambiarCategoria = cambiarCategoria;

window.confirmarPaquete = () => {
    const ok = confirmarPaquete();
    if (!ok) return;
    renderPaquetesSeleccionados();
    renderResumen();
    cerrarModal("modalPaquete");
};