/**
 * UI - Cliente
 */

export function setDataCliente(data) {
    if (!data) return;

    document.querySelector("#nombreCliente").value = data.nombres || "";
    document.querySelector("#dniCliente").value = data.dni || "";
    document.querySelector("#telefonoCliente").value = data.telefono || "";
    document.querySelector("#emailCliente").value = data.email || "";
}