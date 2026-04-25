/**
 * Maneja el estado y la lógica de la cotización
 */

const presets = { dron: false, video: false, foto: false };

let fotoPrecio = 0;
let serviciosCustom = [];
let paquetes = [];
let paqueteSeleccionado = null;

/**
 * ==============================
 * GETTERS
 * ==============================
 */

export function getServiciosPreset() {
    const servicios = [];

    if (presets.dron)
        servicios.push({ nombre: "Toma con Dron", precio: 150 });

    if (presets.video)
        servicios.push({ nombre: "Video resumen", precio: 100 });

    if (presets.foto)
        servicios.push({ nombre: "Foto", precio: fotoPrecio });

    return servicios;
}

export function getAllItems() {
    return [...getServiciosPreset(), ...serviciosCustom, ...paquetes];
}

export function calcularTotal() {
    return getAllItems().reduce((sum, item) => sum + item.precio, 0);
}

/**
 * ==============================
 * ACCIONES
 * ==============================
 */

export function togglePreset(key) {
    presets[key] = !presets[key];
}

export function setFotoPrecio(valor) {
    fotoPrecio = parseFloat(valor) || 0;
}

export function agregarServicio(nombre, precio) {
    serviciosCustom.push({ nombre, precio });
}

export function eliminarServicio(index) {
    serviciosCustom.splice(index, 1);
}

export function agregarPaquete(paquete) {
    paquetes.push(paquete);
}

export function eliminarPaquete(index) {
    paquetes.splice(index, 1);
}