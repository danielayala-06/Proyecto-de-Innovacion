export async function fetchProductos() {
    try {
        const res = await fetch(`${BASE_URL}/api/productos`);
        if (!res.ok) {
            console.error("Error obteniendo productos:", res.status);
            return null;
        }
        return await res.json();
    } catch (error) {
        console.error("Error en fetchProductos:", error);
        return null;
    }
}