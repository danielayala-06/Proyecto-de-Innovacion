/**
 * Manejo del formulario de cotización
 */

import { calcularTotal } from "./cotizacionManager.js";
import { createCotizacion } from "../../api/cotizacionesApi.js";

export function initForm() {
    const form = document.getElementById("form-cotizacion");
    if (!form) return;

    form.addEventListener("submit", handleSubmit);
}

async function handleSubmit(e) {
    e.preventDefault();

    const form     = e.target;
    const formData = new FormData(form);

    // Obtener id_cliente del input oculto (si se implementa) o del DNI ingresado
    const idCliente = formData.get("id_cliente") || null;

    const data = {
        id_cliente:        idCliente,
        id_usuario:        1,                   // Lo cargamos de la sesion
        nombre_cotizacion: formData.get("nombre"),
        fecha_hora_inicio: formData.get("fechaInicio"),
        fecha_hora_fin:    formData.get("fechaFin"),
        direccion:         formData.get("direccion"),
        referencia:        formData.get("referencia"),
        observaciones:     formData.get("observaciones"),
        total_estimado:    calcularTotal(),
    };

    // Validaciones
    if (!data.nombre_cotizacion) {
        alert("Por favor ingresa el nombre del evento.");
        return;
    }

    if (!data.id_cliente) {
        alert("Por favor selecciona un cliente primero.");
        return;
    }

    const res = await createCotizacion(data);

    if (!res) {
        alert("Error al guardar la cotización. Revisa los datos e intenta de nuevo.");
        return;
    }

    alert("¡Cotización guardada correctamente!");
    // Redirigir a la lista de cotizaciones
    window.location.href = `${BASE_URL}/cotizaciones`;
}