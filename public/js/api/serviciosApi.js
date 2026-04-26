export async function fetchServicios() {
    try {
        const res = await fetch(`${BASE_URL}/api/servicios`);
        if (!res.ok) {
            console.error("Error obteniendo servicios:", res.status);
            return null;
        }
        return await res.json();
    } catch (error) {
        console.error("Error en fetchServicios:", error);
        return null;
    }
}