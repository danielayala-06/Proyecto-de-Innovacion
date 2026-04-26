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

export function seleccionarOpcion(paquete, container) {
    const paquetes = document.querySelectorAll('.paquete-option');

    paquetes.forEach(p=> p.addEventListener("click",function(){console.log("has presionado un paquete")}))
    paquetes.forEach(p => p.classList.remove('selected'));
    // Cambiamos de estado al container del paquete
    container.classList.add('selected');

    // Agregamos los paquetes al array para su inserccion
    paqueteSeleccionado = {
        nombre:      paquete['nombre_paquete'],
        descripcion: paquete['paquete_desccripcion'],
        precio:      paquete['precio_base']
    };
}
/*
function seleccionarOpcion(el, nombre, desc, precio) {
    document.querySelectorAll('.paquete-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    paqueteSeleccionado = { nombre, desc, precio };
}*/
