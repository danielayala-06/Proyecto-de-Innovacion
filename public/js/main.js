import { initForm } from "./modules/cotizaciones/cotizacionForm.js";
import {abrirModal, renderPaquetesModal} from "./modules/cotizaciones/cotizacionUI.js";
import { initClienteSearch } from "./modules/clientes/clienteSearch.js";
import { fetchPaquetes } from "./api/paquetesApi.js";
import { fetchServicios } from "./api/serviciosApi.js";

document.addEventListener("DOMContentLoaded", () => {

    /**
     * ==============================
     * INICIALIZACIÓN POR MÓDULOS
     * ==============================
     */

    // Cotización (solo si existe el form)
    if (document.getElementById("form-cotizacion")) {
        initForm();
    }

    // Búsqueda de cliente (solo si existe input)
    if (document.getElementById("searchCliente")) {
        initClienteSearch();
    }

    /**
     * ==============================
     * EVENTOS UI (DEFENSIVOS)
     * ==============================
     */

    const btnServicio = document.getElementById("btn-modal-sevicio");
    if (btnServicio) {
        btnServicio.addEventListener("click", async() => {
            abrirModal("modalServicio");
            const servicios = await fetchServicios()
            //renderResumen();
        });
    }

    const btnPaquete = document.getElementById("btn-modal-paquete");
    if (btnPaquete) {
        btnPaquete.addEventListener("click", async() => {
            abrirModal("modalPaquete");
            const paquetes = await fetchPaquetes()
            await renderPaquetesModal();
            //renderResumen();
        });
    }

});