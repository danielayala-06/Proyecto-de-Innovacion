/**
 * API de cotizaciones
 * Encargado SOLO de comunicación con backend
 */

export async function createCotizacion(data) {
    if (!data) {
        console.error("No hay datos para enviar");
        return null;
    }

    try {
        const res = await fetch(`${BASE_URL}/cotizaciones/insertar`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
        });

        const result = await res.json();

        if (res.status !== 201) {
            console.error("Error al insertar cotización:", result);
            return null;
        }

        return result;
    } catch (error) {
        console.error("Error en la petición:", error);
        return null;
    }
}

export async function getCotizaciones() {
    try {
        const res = await fetch(`${BASE_URL}/cotizaciones`);
        if (!res.ok) return null;
        return await res.json();
    } catch (e) {
        console.error("Error obteniendo cotizaciones:", e);
        return null;
    }
}

export async function deleteCotizacion(id) {
    try {
        const res = await fetch(`${BASE_URL}/cotizaciones/${id}`, {
            method: "DELETE",
        });
        if (!res.ok) return false;
        return true;
    } catch (e) {
        console.error("Error eliminando cotización:", e);
        return false;
    }
}