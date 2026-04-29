/**
 * cotizacionesApi.js
 * Comunicación exclusiva con el backend de cotizaciones.
 */

export async function createCotizacion(data) {
    if (!data) { console.error('No hay datos para enviar'); return null; }
    try {
        const res = await fetch(`${BASE_URL}/cotizaciones/insertar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        const result = await res.json();
        if (res.status !== 201) { console.error('Error al insertar cotización:', result); return null; }
        return result;
    } catch (e) { console.error('createCotizacion:', e); return null; }
}

export async function getCotizaciones() {
    try {
        const res = await fetch(`${BASE_URL}/api/cotizaciones`);
        if (!res.ok) return null;
        return await res.json();
    } catch (e) { console.error('getCotizaciones:', e); return null; }
}

export async function getCotizacionById(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/cotizaciones/${id}`);
        if (!res.ok) { console.error('Error obteniendo cotización:', res.status); return null; }
        return await res.json();
    } catch (e) { console.error('getCotizacionById:', e); return null; }
}

export async function deleteCotizacion(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/cotizaciones/${id}`, { method: 'DELETE' });
        if (!res.ok) { console.error('Error eliminando cotización:', res.status); return false; }
        return true;
    } catch (e) { console.error('deleteCotizacion:', e); return false; }
}

export async function updateEstadoCotizacion(id, estado) {
    try {
        const res = await fetch(`${BASE_URL}/api/cotizaciones/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado }),
        });
        if (!res.ok) { console.error('Error actualizando estado:', res.status); return false; }
        return true;
    } catch (e) { console.error('updateEstadoCotizacion:', e); return false; }
}
