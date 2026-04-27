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

        const dateInicio  = document.getElementById("fechaInicio-date");
        const timeInicio  = document.getElementById("fechaInicio-time");
        const hiddenInicio = document.getElementById("fechaInicio");
        const dateFin     = document.getElementById("fechaFin-date");
        const timeFin     = document.getElementById("fechaFin-time");
        const hiddenFin   = document.getElementById("fechaFin");

        // Rellena un <select> con opciones cada 30 min de 06:00 a 23:30
        function llenarHoras(selectEl, horaMinima = null) {
            const prevVal = selectEl.value;
            selectEl.innerHTML = `<option value="">Hora</option>`;
            for (let h = 6; h < 24; h++) {
                for (let m = 0; m < 60; m += 30) {
                    const val = `${pad(h)}:${pad(m)}`;
                    if (horaMinima && val < horaMinima) continue;
                    const opt = document.createElement("option");
                    opt.value = val;
                    opt.textContent = val;
                    selectEl.appendChild(opt);
                }
            }
            // Restaurar selección si sigue siendo válida
            if (prevVal && selectEl.querySelector(`option[value="${prevVal}"]`)) {
                selectEl.value = prevVal;
            }
        }

        function sincronizarHidden(dateEl, timeEl, hiddenEl) {
            hiddenEl.value = (dateEl.value && timeEl.value)
                ? `${dateEl.value}T${timeEl.value}`
                : "";
        }

        // Recalcula la hora mínima de fechaFin según fechaInicio + 2h
        function actualizarMinFin() {
            if (!dateInicio.value || !timeInicio.value) return;

            const inicio   = new Date(`${dateInicio.value}T${timeInicio.value}`);
            const minFin   = new Date(inicio.getTime() + 2 * 60 * 60 * 1000);
            const minFinDate = toDateStr(minFin);

            dateFin.min = minFinDate;

            // Si misma fecha, filtrar horas disponibles
            if (dateFin.value === minFinDate) {
                llenarHoras(timeFin, `${pad(minFin.getHours())}:${pad(minFin.getMinutes())}`);
            } else {
                llenarHoras(timeFin);
            }

            // Limpiar fin si ya no es válido
            if (hiddenFin.value && new Date(hiddenFin.value) < minFin) {
                timeFin.value  = "";
                hiddenFin.value = "";
            }

            sincronizarHidden(dateFin, timeFin, hiddenFin);
        }

        if (dateInicio && timeInicio) {
            llenarHoras(timeInicio);
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
            llenarHoras(timeFin);
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