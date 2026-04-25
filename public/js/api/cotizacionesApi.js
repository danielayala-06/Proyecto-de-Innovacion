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
            console.error("Error:", result);
            console.log(res.status)
            return;
        }

        console.log(result)
        return result;
    } catch (error) {
        console.error("Error en la petición:", error);
        return null;
    }
}