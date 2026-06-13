#!/usr/bin/env bash
set -euo pipefail

PORT=${1:-8080}

# Obtener la IP local de la interfaz con salida a la red
IP=$(ip route get 1.1.1.1 2>/dev/null | awk '{for(i=1;i<=NF;i++) if ($i=="src") {print $(i+1); exit}}')

if [[ -z "$IP" ]]; then
    # Fallback: primera IP no-loopback
    IP=$(hostname -I | awk '{print $1}')
fi

if [[ -z "$IP" ]]; then
    echo "No se pudo detectar la IP local." >&2
    exit 1
fi

ENV_FILE="$(dirname "$0")/.env"

# Actualizar app.baseURL en .env (crea la línea si no existe)
if grep -q "^app\.baseURL" "$ENV_FILE"; then
    sed -i "s|^app\.baseURL\s*=.*|app.baseURL = 'http://${IP}:${PORT}/'|" "$ENV_FILE"
else
    echo "app.baseURL = 'http://${IP}:${PORT}/'" >> "$ENV_FILE"
fi

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  IP detectada : $IP"
echo "  Puerto       : $PORT"
echo "  URL local    : http://${IP}:${PORT}/"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

php spark serve --host "$IP" --port "$PORT"
