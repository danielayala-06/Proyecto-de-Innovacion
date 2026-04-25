/**
 * API - Clientes
 */

export async function buscarCliente(valor) {
    const tipo = detectarTipo(valor);

    if (!tipo || tipo === "desconocido") {
        return null;
    }

    try {
        const res = await fetch(`${BASE_URL}/cotizaciones/searchCliente`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                tipo,
                valor,
            }),
        });

        if (!res.ok) {
            console.error("Error en la respuesta");
            return null;
        }

        return await res.json();
    } catch (e) {
        console.error(e);
        return null;
    }
}