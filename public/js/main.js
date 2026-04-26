/**
 * main.js — Punto de entrada del módulo JS
 *
 * Solo se carga en páginas que incluyan <script type="module" src=".../main.js">
 * Se activa cada sección de forma defensiva (comprueba que el elemento existe antes).
 */

import { initForm }                            from "./modules/cotizaciones/cotizacionForm.js";
import { abrirModal, renderPaquetesModal,
    renderResumen, renderServiciosCustom,
    renderPaquetesSeleccionados }         from "./modules/cotizaciones/cotizacionUI.js";
import { initClienteSearch }                   from "./modules/clientes/clienteSearch.js";
import { fetchPaquetes }                       from "./api/paquetesApi.js";
import { fetchServicios }                      from "./api/serviciosApi.js";

document.addEventListener("DOMContentLoaded", () => {

    /**
     * ==============================
     * INICIALIZACIÓN POR MÓDULOS
     * ==============================
     */
    if (document.getElementById("form-cotizacion")) {
        initForm();

        // Render inicial del resumen y listas vacías
        renderResumen();
        renderServiciosCustom();
        renderPaquetesSeleccionados();
    }

    // ==============================
    // BÚSQUEDA DE CLIENTE
    // ==============================
    if (document.getElementById("searchCliente")) {
        initClienteSearch();
    }

    // =================================
    // BOTÓN – ABRIR MODAL SERVICIOS
    // =================================
    const btnServicio = document.getElementById("btn-modal-sevicio");
    if (btnServicio) {
        btnServicio.addEventListener("click", async () => {
            abrirModal("modalServicio");
            // Podrías cargar servicios desde la API aquí si los necesitas
            // const servicios = await fetchServicios();
        });
    }

    // =================================
    // BOTÓN – ABRIR MODAL PAQUETES
    // =================================
    const btnPaquete = document.getElementById("btn-modal-paquete");
    if (btnPaquete) {
        btnPaquete.addEventListener("click", async () => {
            abrirModal("modalPaquete");
            await renderPaquetesModal();   // carga paquetes desde la API
        });
    }

    // ================================================
    // BOTÓN – CONFIRMAR PAQUETE (dentro del modal)
    // ================================================
    const btnConfirmarPaquete = document.getElementById("btn-confirmar-paquetes");
    if (btnConfirmarPaquete) {
        btnConfirmarPaquete.addEventListener("click", () => {
            window.confirmarPaquete();
        });
    }

});