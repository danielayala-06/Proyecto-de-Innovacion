import { buscarCliente, detectarTipo, buscarClientePorDni } from "../../api/clientesApi.js";
import { setDataCliente, setEstadoBusqueda } from "./clienteUI.js";

export function initClienteSearch() {
    const inputBusqueda = document.getElementById("searchCliente");
    const btnBuscar     = document.getElementById("btnBuscar");

    if (!inputBusqueda) return;

    inputBusqueda.addEventListener("keydown", async (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            await handleSearch(inputBusqueda.value);
        }
    });

    if (btnBuscar) {
        btnBuscar.addEventListener("click", async () => {
            await handleSearch(inputBusqueda.value);
        });
    }
}

async function handleSearch(valor) {
    valor = valor.trim();
    if (!valor) return;

    const tipo = detectarTipo(valor);

    if (tipo === "desconocido") {
        setEstadoBusqueda("error", "Ingresa un DNI (8 dígitos), teléfono (9 dígitos) o nombre");
        return;
    }

    setEstadoBusqueda("loading", "Buscando...");

    // Búsqueda por DNI: llamar local + DECOLECTA en paralelo
    // DECOLECTA siempre da nombres/apellidos bien separados (first_name / apellidos)
    // La BD local aporta id_cliente, teléfono y correo
    if (tipo === "numero_documento") {
        const [dataLocal, dataReniec] = await Promise.all([
            buscarCliente(valor),
            buscarClientePorDni(valor),
        ]);

        if (!dataLocal && !dataReniec) {
            setEstadoBusqueda("notfound", valor);
            return;
        }

        const merged = {
            id_cliente: dataLocal?.id_cliente ?? null,
            nombres:    dataReniec?.nombres   || dataLocal?.nombres   || "",
            apellidos:  dataReniec?.apellidos || dataLocal?.apellidos || "",
            dni:        valor,
            telefono:   dataLocal?.telefono   || "",
            email:      dataLocal?.email      || "",
        };

        setDataCliente(merged);
        setEstadoBusqueda(
            dataLocal ? "idle" : "error",
            dataLocal ? "" : "Cliente nuevo desde RENIEC — completa el teléfono y se registrará al guardar."
        );
        return;
    }

    // Para teléfono o nombre: solo buscar en la BD local
    const dataLocal = await buscarCliente(valor);
    if (dataLocal) {
        setDataCliente(dataLocal);
        setEstadoBusqueda("idle");
        return;
    }

    setEstadoBusqueda("notfound", valor);
}