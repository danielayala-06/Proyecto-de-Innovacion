export async function fetchEventos() {
    try {
        const res = await fetch(`${BASE_URL}/api/eventos`);
        if (!res.ok) { console.error('fetchEventos:', res.status); return null; }
        return await res.json();
    } catch (e) {
        console.error('fetchEventos:', e);
        return null;
    }
}

export async function createEvento(data) {
    try {
        const res = await fetch(`${BASE_URL}/api/eventos`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        if (!res.ok) { console.error('createEvento:', res.status); return null; }
        return await res.json();
    } catch (e) {
        console.error('createEvento:', e);
        return null;
    }
}

export async function updateEvento(id, data) {
    try {
        const res = await fetch(`${BASE_URL}/api/eventos/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        if (!res.ok) { console.error('updateEvento:', res.status); return null; }
        return await res.json();
    } catch (e) {
        console.error('updateEvento:', e);
        return null;
    }
}

export async function deleteEvento(id) {
    try {
        const res = await fetch(`${BASE_URL}/api/eventos/${id}`, { method: 'DELETE' });
        if (!res.ok) { console.error('deleteEvento:', res.status); return false; }
        return true;
    } catch (e) {
        console.error('deleteEvento:', e);
        return false;
    }
}
