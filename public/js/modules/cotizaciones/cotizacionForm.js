import { calcularTotal } from "./cotizacionManager.js";
import { createCotizacion } from "../../api/cotizacionesApi.js";

/**
 * Manejo del formulario
 */

export function initForm() {
    const form = document.getElementById("form-cotizacion");

    form.addEventListener("submit", handleSubmit);
}

function handleSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    const data = {
        id_cliente: 4,
        id_usuario: 1,
        nombre_cotizacion: formData.get("nombre"),
        fecha_hora_inicio: formData.get("fechaInicio"),
        fecha_hora_fin: formData.get("fechaFin"),
        direccion: formData.get("direccion"),
        referencia: formData.get("referencia"),
        observaciones: formData.get("observaciones"),
        total_estimado: calcularTotal(),
    };
    console.log(data)
    enviar(data);
}

async function enviar(data) {
    const res = await createCotizacion(data);
    console.log(res)
    if (!res) {
        alert("Error al guardar");
        return;
    }

    alert("Cotización guardada");
}