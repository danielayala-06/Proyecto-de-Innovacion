export async function fetchPaquetes() {
    try {
        const res = await fetch(`${BASE_URL}/api/paquetes`);
        if (!res.ok) { console.error('Error obteniendo paquetes:', res.status); return null; }
        return await res.json();
    } catch (e) { console.error('fetchPaquetes:', e); return null; }
}

export async function createPaquete(data) {
    try {
        const res = await fetch(`${BASE_URL}/api/paquetes`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        if (!res.ok) { console.error('Error creando paquete:', res.status); return null; }
        return await res.json();
    } catch (e) { console.error('createPaquete:', e); return null; }
}

export async function updatePaquete(id, data) {
    try {
        const res = await fetch(`${BASE_URL}/api/paquetes/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        if (!res.ok) { console.error('Error actualizando paquete:', res.status); return null; }
        return await res.json();
    } catch (e) { console.error('updatePaquete:', e); return null; }
}

export async function deletePaquete(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/paquetes/${id}`, { method: 'DELETE' });
        if (!res.ok) { console.error('Error eliminando paquete:', res.status); return false; }
        return true;
    } catch (e) { console.error('deletePaquete:', e); return false; }
}
