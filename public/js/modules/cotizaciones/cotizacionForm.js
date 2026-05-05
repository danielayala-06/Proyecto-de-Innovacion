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

    const idCliente  = formData.get("id_cliente") || null;
    const nombres    = formData.get("nombres_cliente")?.trim();
    const apellidos  = formData.get("apellidos_cliente")?.trim() || null;
    const dni        = formData.get("dni")?.trim();
    const telefono   = formData.get("telefono")?.trim();
    const email      = formData.get("correo")?.trim() || null;

    // Validar que existe un cliente (registrado o datos para crearlo)
    if (!idCliente && !nombres) {
        alert("Por favor busca un cliente antes de guardar.");
        return;
    }

    if (!idCliente && !telefono) {
        alert("El teléfono es obligatorio para registrar un nuevo cliente.");
        return;
    }

    if (!formData.get("nombre")?.trim()) {
        alert("Por favor ingresa el nombre del evento.");
        return;
    }

    const data = {
        id_cliente:        idCliente ? parseInt(idCliente) : null,
        nombre_cotizacion: formData.get("nombre"),
        fecha_hora_inicio: formData.get("fechaInicio"),
        fecha_hora_fin:    formData.get("fechaFin"),
        direccion:         formData.get("direccion"),
        referencia:        formData.get("referencia"),
        observaciones:     formData.get("observaciones"),
        total_estimado:    calcularTotal(),
        // Siempre incluir datos del cliente para mantener nombres/apellidos actualizados
        cliente_nuevo: { nombres, apellidos, dni, telefono, email },
    };

    const res = await createCotizacion(data);

    if (!res) {
        alert("Error al guardar la cotización. Revisa los datos e intenta de nuevo.");
        return;
    }

    alert("¡Cotización guardada correctamente!");
    window.location.href = `${BASE_URL}/cotizaciones`;
}