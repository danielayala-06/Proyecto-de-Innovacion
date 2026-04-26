import { buscarCliente } from "../../api/clientesApi.js";
import { setDataCliente } from "./clienteUI.js";

/**
 * Inicializa búsqueda de cliente
 */

export function initClienteSearch() {
    document.addEventListener("DOMContentLoaded", init);
}

function init() {
    const inputBusqueda = document.getElementById("searchCliente");

    inputBusqueda.addEventListener("change", handleSearch);
}

async function handleSearch(e) {
    const valor = e.target.value.trim();

    if (!valor) return;

    const data = await buscarCliente(valor);

    console.log("Cliente encontrado:", data);

    setDataCliente(data);
}