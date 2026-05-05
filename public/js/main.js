/**
 * main.js — Punto de entrada del módulo JS
 *
 * Solo se carga en páginas que incluyan <script type="module" src=".../main.js">
 * Se activa cada sección de forma defensiva (comprueba que el elemento existe antes).
 */

import { initForm }                                      from "./modules/cotizaciones/cotizacionForm.js";
import { abrirModal, renderPaquetesModal,
    renderServiciosModal, renderResumen,
    renderServiciosCustom,
    renderPaquetesSeleccionados }               from "./modules/cotizaciones/cotizacionUI.js";
import { initClienteSearch }                           from "./modules/clientes/clienteSearch.js";

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

        // ==============================
        // FECHAS – selector de fecha + hora
        // ==============================
        const pad       = n => String(n).padStart(2, "0");
        const toDateStr = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;

        const now     = new Date();
        const maxDate = new Date(now);
        maxDate.setFullYear(maxDate.getFullYear() + 1);

        const dateInicio   = document.getElementById("fechaInicio-date");
        const timeInicio   = document.getElementById("fechaInicio-time");
        const hiddenInicio = document.getElementById("fechaInicio");
        const dateFin      = document.getElementById("fechaFin-date");
        const timeFin      = document.getElementById("fechaFin-time");
        const hiddenFin    = document.getElementById("fechaFin");

        function sincronizarHidden(dateEl, timeEl, hiddenEl) {
            hiddenEl.value = (dateEl.value && timeEl.value)
                ? `${dateEl.value}T${timeEl.value}`
                : "";
        }

        function actualizarMinFin() {
            if (!dateInicio.value || !timeInicio.value) return;

            const inicio     = new Date(`${dateInicio.value}T${timeInicio.value}`);
            const minFin     = new Date(inicio.getTime() + 2 * 60 * 60 * 1000);
            const minFinDate = toDateStr(minFin);

            dateFin.min = minFinDate;

            timeFin.min = (dateFin.value === minFinDate)
                ? `${pad(minFin.getHours())}:${pad(minFin.getMinutes())}`
                : "";

            if (hiddenFin.value && new Date(hiddenFin.value) < minFin) {
                timeFin.value   = "";
                hiddenFin.value = "";
            }

            sincronizarHidden(dateFin, timeFin, hiddenFin);
        }

        if (dateInicio && timeInicio) {
            dateInicio.min = toDateStr(now);
            dateInicio.max = toDateStr(maxDate);

            dateInicio.addEventListener("change", () => {
                sincronizarHidden(dateInicio, timeInicio, hiddenInicio);
                actualizarMinFin();
            });
            timeInicio.addEventListener("change", () => {
                sincronizarHidden(dateInicio, timeInicio, hiddenInicio);
                actualizarMinFin();
            });
        }

        if (dateFin && timeFin) {
            dateFin.min = toDateStr(now);
            dateFin.max = toDateStr(maxDate);

            dateFin.addEventListener("change", () => {
                actualizarMinFin();
                sincronizarHidden(dateFin, timeFin, hiddenFin);
            });
            timeFin.addEventListener("change", () => {
                sincronizarHidden(dateFin, timeFin, hiddenFin);
            });
        }

        // ==============================
        // TELÉFONO – validación Perú (debe empezar con 9)
        // ==============================
        const inputTel     = document.getElementById("telefonoCliente");
        const feedbackTel  = document.getElementById("telefonoFeedback");

        if (inputTel && feedbackTel) {
            inputTel.addEventListener("input", () => {
                // Solo dígitos
                inputTel.value = inputTel.value.replace(/\D/g, "").slice(0, 9);

                const val = inputTel.value;
                if (val.length > 0 && val[0] !== "9") {
                    feedbackTel.textContent = "El teléfono debe empezar con 9.";
                    feedbackTel.style.display = "block";
                    inputTel.classList.add("is-invalid");
                } else if (val.length > 0 && val.length < 9) {
                    feedbackTel.textContent = "El teléfono debe tener 9 dígitos.";
                    feedbackTel.style.display = "block";
                    inputTel.classList.remove("is-invalid");
                } else {
                    feedbackTel.style.display = "none";
                    inputTel.classList.remove("is-invalid");
                }
            });
        }
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
    const btnServicio = document.getElementById("btn-modal-servicio");
    if (btnServicio) {
        btnServicio.addEventListener("click", async () => {
            abrirModal("modalServicio");
            await renderServiciosModal();
        });
    }

    // ================================================
    // BOTÓN – CONFIRMAR SERVICIO (dentro del modal)
    // ================================================
    const btnConfirmarServicio = document.getElementById("btn-confirmar-servicio");
    if (btnConfirmarServicio) {
        btnConfirmarServicio.addEventListener("click", () => {
            window.confirmarServicio();
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