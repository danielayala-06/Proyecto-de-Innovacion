import { buscarCliente } from "../../api/clientesApi.js";
import { setDataCliente } from "./clienteUI.js";

/**
 * Inicializa búsqueda de cliente
 */

export function initClienteSearch() {
    const inputBusqueda = document.getElementById("searchCliente");
    const btnBuscar     = document.getElementById("btnBuscar");

    if (!inputBusqueda) return;

    // Buscar al presionar Enter en el input
    inputBusqueda.addEventListener("keydown", async (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            await handleSearch(inputBusqueda.value);
        }
    });

    // Buscar al hacer click en el botón lupa
    if (btnBuscar) {
        btnBuscar.addEventListener("click", async () => {
            await handleSearch(inputBusqueda.value);
        });
    }
}

async function handleSearch(valor) {
    valor = valor.trim();
    if (!valor) return;

    const data = await buscarCliente(valor);

    if (!data) {
        console.warn("No se encontró cliente con:", valor);
        return;
    }

    setDataCliente(data);
}