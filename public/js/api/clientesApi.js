/**
 * API - Clientes
 */

export function detectarTipo(input) {
    input = String(input).trim();
    if (/^\d{8}$/.test(input))                          return "numero_documento";
    if (/^\d{9}$/.test(input))                          return "telefono";
    if (/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(input))      return "nombres";
    return "desconocido";
}

export async function buscarCliente(valor) {
    const tipo = detectarTipo(valor);

    if (!tipo || tipo === "desconocido") {
        return null;
    }

    try {
        const res = await fetch(`${BASE_URL}/cotizaciones/searchCliente`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ tipo, valor }),
        });

        if (!res.ok) return null;

        const json = await res.json();
        // El servidor devuelve un array; tomamos el primer resultado
        const item = Array.isArray(json) ? json[0] : json;
        return item ?? null;
    } catch (e) {
        console.error(e);
        return null;
    }
}

export async function buscarClientePorDni(dni) {
    try {
        const res = await fetch(`${BASE_URL}/api/clientes/dni?dni=${encodeURIComponent(dni)}`, {
            headers: { "Accept": "application/json" },
        });

        if (!res.ok) return null;

        const json = await res.json();
        const d    = json.data ?? json;

        // Normaliza la respuesta de DECOLECTA al formato interno
        // Campos: first_name=Nombres, first_last_name+second_last_name=Apellidos
        return {
            nombres:   d.first_name  ?? "",
            apellidos: [d.first_last_name, d.second_last_name].filter(Boolean).join(" "),
            dni:       d.document_number ?? dni,
            telefono:  "",
            email:     "",
        };
    } catch (e) {
        console.error("DECOLECTA error:", e);
        return null;
    }
}