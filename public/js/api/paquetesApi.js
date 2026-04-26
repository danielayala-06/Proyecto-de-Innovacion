export async function fetchPaquetes() {
    try {
        const res = await fetch(`${BASE_URL}/api/paquetes`);
        // En caso de que haya sucedido un error
        if (!res.ok) {
            console.error("Error obteniendo paquetes:", res.status);
            return null;
        }

        // Devolvemos los paquetes
        return await res.json();
    } catch (error) {
        console.error("Error en fetchPaquetes:", error);
        return null;
    }
}