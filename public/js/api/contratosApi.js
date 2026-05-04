/**
 * contratosApi.js
 * Comunicación exclusiva con el backend de contratos.
 */

export async function getContratos() {
    try {
        const res = await fetch(`${BASE_URL}/api/contratos`);
        if (!res.ok) { console.error('getContratos:', res.status); return null; }
        return await res.json();
    } catch (e) { console.error('getContratos:', e); return null; }
}

export async function getContratoById(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/contratos/${id}`);
        if (!res.ok) { console.error('getContratoById:', res.status); return null; }
        return await res.json();
    } catch (e) { console.error('getContratoById:', e); return null; }
}

export async function createContrato(data) {
    try {
        const res = await fetch(`${BASE_URL}/api/contratos`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        const result = await res.json();
        if (!res.ok) { console.error('createContrato error:', result); return null; }
        return result;
    } catch (e) { console.error('createContrato:', e); return null; }
}

export async function updateEstadoContrato(id, estado) {
    try {
        const res = await fetch(`${BASE_URL}/api/contratos/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado }),
        });
        if (!res.ok) { console.error('updateEstadoContrato:', res.status); return false; }
        return true;
    } catch (e) { console.error('updateEstadoContrato:', e); return false; }
}

export async function deleteContrato(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/contratos/${id}`, { method: 'DELETE' });
        if (!res.ok) { console.error('deleteContrato:', res.status); return false; }
        return true;
    } catch (e) { console.error('deleteContrato:', e); return false; }
}

export async function getCotizacionesDisponibles() {
    try {
        const res = await fetch(`${BASE_URL}/api/cotizaciones/disponibles`);
        if (!res.ok) { console.error('getCotizacionesDisponibles:', res.status); return null; }
        return await res.json();
    } catch (e) { console.error('getCotizacionesDisponibles:', e); return null; }
}
