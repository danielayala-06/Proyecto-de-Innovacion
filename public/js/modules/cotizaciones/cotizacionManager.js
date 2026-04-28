/**
 * Estado y lógica de la cotización
 */

const presets = { dron: false, video: false, foto: false };

let fotoPrecio = 0;
let serviciosCustom = [];
let paquetesList = [];
let paqueteSeleccionado = null;
let servicioSeleccionado = null;

/**
 * ==============================
 * GETTERS
 * ==============================
 */
export function getPresets() {
    return { ...presets };
}

export function getServiciosPreset() {
    const servicios = [];
    if (presets.dron)  servicios.push({ nombre: "Toma con Dron",         precio: 150 });
    if (presets.video) servicios.push({ nombre: "Video resumen (3 min)", precio: 100 });
    if (presets.foto)  servicios.push({ nombre: "Foto",                  precio: fotoPrecio });
    return servicios;
}

export function getServiciosCustom() {
    return [...serviciosCustom];
}

export function getPaquetes() {
    return [...paquetesList];
}

export function getAllItems() {
    return [...getServiciosPreset(), ...serviciosCustom, ...paquetesList];
}

export function calcularTotal() {
    return getAllItems().reduce((sum, item) => sum + Number(item.precio), 0);
}

export function getPaqueteSeleccionado() {
    return paqueteSeleccionado;
}

/**
 * ==============================
 * ACCIONES
 * ==============================
 */
export function togglePreset(key) {
    if (!(key in presets)) return;
    presets[key] = !presets[key];
}

export function setFotoPrecio(valor) {
    fotoPrecio = parseFloat(valor) || 0;
}

export function agregarServicio(nombre, precio) {
    serviciosCustom.push({ nombre, precio: parseFloat(precio) || 0 });
}

export function eliminarServicio(index) {
    serviciosCustom.splice(index, 1);
}

export function agregarPaquete(paquete) {
    paquetesList.push({ ...paquete });
}

export function eliminarPaquete(index) {
    paquetesList.splice(index, 1);
}

/**
 * Marca un paquete como seleccionado (para confirmar luego con confirmarPaquete())
 * @param {Object} paquete  - objeto con campos del backend
 * @param {HTMLElement} el  - elemento DOM del paquete clickeado
 */
export function seleccionarPaquete(paquete, el) {
    // Quitar selección anterior
    document.querySelectorAll(".paquete-option").forEach(p => p.classList.remove("selected"));
    el.classList.add("selected");

    paqueteSeleccionado = {
        nombre:      paquete.nombre_paquete      || paquete.nombre,
        descripcion: paquete.descripcion         || "",
        precio:      parseFloat(paquete.precio_base || paquete.precio) || 0,
    };
}

/**
 * Confirma el paquete seleccionado y lo agrega a la lista
 * @returns {boolean} true si se agregó, false si no había selección
 */
export function confirmarPaquete() {
    if (!paqueteSeleccionado) return false;
    paquetesList.push({ ...paqueteSeleccionado });
    paqueteSeleccionado = null;
    return true;
}

export function seleccionarServicio(servicio, el) {
    document.querySelectorAll(".servicio-option").forEach(s => s.classList.remove("selected"));
    el.classList.add("selected");
    servicioSeleccionado = {
        nombre:      servicio.nombre_servicio,
        descripcion: servicio.detalle_servicio || "",
        precio:      0,
    };
}

export function confirmarServicio(precio) {
    if (!servicioSeleccionado) return false;
    serviciosCustom.push({ ...servicioSeleccionado, precio: parseFloat(precio) || 0 });
    servicioSeleccionado = null;
    return true;
}
