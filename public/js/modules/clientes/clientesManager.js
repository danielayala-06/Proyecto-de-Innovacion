/**
 * Lógica de cliente
 */

export function detectarTipo(input) {
    input = String(input).trim();

    if (/^\d{8}$/.test(input)) return "numero_documento";
    if (/^\d{9}$/.test(input)) return "telefono";
    if (/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(input)) return "nombres";

    return "desconocido";   
}