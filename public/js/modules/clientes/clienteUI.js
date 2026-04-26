/**
 * UI - Cliente: actualiza el DOM con datos del cliente encontrado
 */

export function setDataCliente(data) {
    if (!data) {
        console.warn("setDataCliente: no se recibió data");
        return;
    }

    const campos = {
        "#nombreCliente":   data.nombres  || "",
        "#dniCliente":      data.dni      || "",
        "#telefonoCliente": data.telefono || "",
        "#emailCliente":    data.email    || "",
    };

    for (const [selector, valor] of Object.entries(campos)) {
        const el = document.querySelector(selector);
        if (el) el.value = valor;
    }

    // Actualiza el resumen lateral de cliente seleccionado
    const resumen = document.getElementById("clienteSeleccionado");
    if (resumen && data.nombres) {
        const inicial = data.nombres.charAt(0).toUpperCase();
        resumen.innerHTML = `
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:26px;height:26px;border-radius:50%;background:#254870;display:flex;
                            align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#7db8f0;">
                    ${inicial}
                </div>
                <div>
                    <div style="color:#ccc;font-weight:500;font-size:0.82rem;">${data.nombres}</div>
                    <div style="color:#555;font-size:0.74rem;">DNI: ${data.dni || "—"}</div>
                </div>
            </div>`;
    }
}