#!/bin/bash
# ============================================================
# Levanta el servidor CI4 + Cloudflare Tunnel en paralelo.
# El formulario queda accesible desde internet.
# El panel de gestión solo desde la red local.
#
# Uso: bash scripts/tunnel.sh
# Para detener: Ctrl+C
# ============================================================

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

TUNNEL_URL_FILE="$ROOT/writable/tunnel_url.txt"

# Verificar que cloudflared está instalado
if ! command -v cloudflared &>/dev/null; then
    echo "✗ cloudflared no está instalado."
    echo "  Ejecuta primero: bash scripts/install.sh"
    exit 1
fi

# Verificar que PHP está disponible
if ! command -v php &>/dev/null; then
    echo "✗ PHP no encontrado en el PATH."
    exit 1
fi

PORT=${PORT:-8080}

echo "============================================"
echo "  Ronceros Fotografía — Modo Túnel"
echo "============================================"
echo ""
echo "→ Levantando servidor PHP en puerto $PORT..."
php spark serve --port "$PORT" &
SERVER_PID=$!

# Esperar a que el servidor arranque
sleep 2

echo "→ Conectando con Cloudflare..."
echo ""

# Limpiar archivo anterior
rm -f "$TUNNEL_URL_FILE"

# Limpiar al salir
cleanup() {
    echo ""
    echo "→ Cerrando servidor y túnel..."
    rm -f "$TUNNEL_URL_FILE"
    kill "$SERVER_PID" 2>/dev/null
    exit 0
}
trap cleanup INT TERM

# Iniciar túnel y capturar la URL pública de su output
cloudflared tunnel --url "http://localhost:$PORT" 2>&1 | while IFS= read -r line; do
    echo "$line"

    # Cloudflare imprime la URL pública en una línea como:
    # "Your quick Tunnel has been created! ... https://xyz.trycloudflare.com"
    if [[ "$line" =~ (https://[a-zA-Z0-9-]+\.trycloudflare\.com) ]]; then
        TUNNEL_URL="${BASH_REMATCH[1]}"
        echo "$TUNNEL_URL" > "$TUNNEL_URL_FILE"
        echo ""
        echo "  ============================================"
        echo "  URL PÚBLICA DEL FORMULARIO:"
        echo "  $TUNNEL_URL/formulario/grupo/<token>"
        echo ""
        echo "  Copia ese link desde el panel de Formularios"
        echo "  y compártelo con el colegio."
        echo "  ============================================"
        echo ""
    fi
done

# Si cloudflared termina, cerrar el servidor también
kill "$SERVER_PID" 2>/dev/null
rm -f "$TUNNEL_URL_FILE"
