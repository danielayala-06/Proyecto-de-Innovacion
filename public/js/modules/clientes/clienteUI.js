/**
 * UI - Cliente: actualiza el DOM con datos del cliente encontrado
 */

export function setEstadoBusqueda(estado, info = "") {
    const btn      = document.getElementById("btnBuscar");
    const feedback = document.getElementById("searchFeedback");

    if (estado === "loading") {
        if (btn) {
            btn.disabled     = true;
            btn.innerHTML    = `<span class="spinner-border spinner-border-sm" role="status"></span>`;
        }
        if (feedback) {
            feedback.textContent = info || "Buscando...";
            feedback.className   = "form-text text-muted mt-1";
        }
    } else if (estado === "notfound") {
        if (btn) {
            btn.disabled  = false;
            btn.innerHTML = `<i class="bi bi-search"></i>`;
        }
        if (feedback) {
            feedback.textContent = `No se encontró ningún cliente con "${info}"`;
            feedback.className   = "form-text text-danger mt-1";
        }
    } else if (estado === "error") {
        if (btn) {
            btn.disabled  = false;
            btn.innerHTML = `<i class="bi bi-search"></i>`;
        }
        if (feedback) {
            feedback.textContent = info;
            feedback.className   = "form-text text-warning mt-1";
        }
    } else {
        if (btn) {
            btn.disabled  = false;
            btn.innerHTML = `<i class="bi bi-search"></i>`;
        }
        if (feedback) {
            feedback.textContent = "";
            feedback.className   = "form-text mt-1";
        }
    }
}

export function setDataCliente(data) {
    if (!data) {
        console.warn("setDataCliente: no se recibió data");
        return;
    }

    const campos = {
        "#idCliente":        data.id_cliente ?? "",
        "#nombresCliente":   data.nombres    || "",
        "#apellidosCliente": data.apellidos  || "",
        "#dniCliente":       data.dni        || "",
        "#telefonoCliente":  data.telefono   || "",
        "#emailCliente":     data.email      || "",
    };

    for (const [selector, valor] of Object.entries(campos)) {
        const el = document.querySelector(selector);
        if (el) el.value = valor;
    }

    // Nombre completo para el resumen lateral
    const nombreCompleto = [data.nombres, data.apellidos].filter(Boolean).join(" ");
    const resumen = document.getElementById("clienteSeleccionado");
    if (resumen && nombreCompleto) {
        const inicial = nombreCompleto.charAt(0).toUpperCase();
        resumen.innerHTML = `
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:26px;height:26px;border-radius:50%;background:#254870;display:flex;
                            align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#7db8f0;">
                    ${inicial}
                </div>
                <div>
                    <div style="color:#ccc;font-weight:500;font-size:0.82rem;">${nombreCompleto}</div>
                    <div style="color:#555;font-size:0.74rem;">DNI: ${data.dni || "—"}</div>
                </div>
            </div>`;
    }
}