#!/bin/bash
# ============================================================
# Instalador de Ronceros Fotografía
# Compatible con Ubuntu 22.04 / 24.04
#
# Uso:
#   bash scripts/install.sh
# ============================================================

set -e

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

# ── Colores ──────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

ok()   { echo -e "${GREEN}✓${RESET} $1"; }
info() { echo -e "${CYAN}→${RESET} $1"; }
warn() { echo -e "${YELLOW}⚠${RESET} $1"; }
err()  { echo -e "${RED}✗${RESET} $1"; exit 1; }
ask()  { echo -e "${BOLD}$1${RESET}"; }

# ── Encabezado ───────────────────────────────────────────────
echo ""
echo -e "${BOLD}============================================${RESET}"
echo -e "${BOLD}  Ronceros Fotografía — Instalador${RESET}"
echo -e "${BOLD}============================================${RESET}"
echo ""

# ── 1. Verificar OS ──────────────────────────────────────────
info "Verificando sistema operativo..."
if ! command -v apt-get &>/dev/null; then
    warn "Este script es para Linux (Ubuntu/Debian)."
    echo "  Si estás en Windows usa: powershell -ExecutionPolicy Bypass -File scripts\\install.ps1"
    err "apt-get no encontrado."
fi
ok "Sistema compatible detectado (Linux/Ubuntu)."

# ── 2. Actualizar paquetes ───────────────────────────────────
info "Actualizando lista de paquetes..."
sudo apt-get update -qq

# ── 3. PHP y extensiones ─────────────────────────────────────
echo ""
info "Verificando PHP..."

PHP_OK=true
if ! command -v php &>/dev/null; then
    PHP_OK=false
else
    PHP_VER=$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
    if awk "BEGIN{exit !($PHP_VER >= 8.2)}"; then
        ok "PHP $PHP_VER encontrado."
    else
        warn "PHP $PHP_VER es muy antiguo. Se necesita PHP 8.2+."
        PHP_OK=false
    fi
fi

if [ "$PHP_OK" = false ]; then
    info "Instalando PHP 8.3..."
    sudo apt-get install -y software-properties-common
    sudo add-apt-repository -y ppa:ondrej/php
    sudo apt-get update -qq
    sudo apt-get install -y php8.3
fi

info "Verificando extensiones PHP requeridas..."
EXTENSIONS="gd mbstring mysqli pdo-mysql curl zip xml"
MISSING=""
for ext in $EXTENSIONS; do
    MODULE=$(echo "$ext" | tr '-' '_')
    if ! php -m 2>/dev/null | grep -qi "^${MODULE}$"; then
        MISSING="$MISSING php8.3-${ext}"
    fi
done

if [ -n "$MISSING" ]; then
    info "Instalando extensiones faltantes:$MISSING"
    sudo apt-get install -y $MISSING
    ok "Extensiones instaladas."
else
    ok "Todas las extensiones PHP están disponibles."
fi

# ── 4. Composer ──────────────────────────────────────────────
echo ""
info "Verificando Composer..."
if ! command -v composer &>/dev/null; then
    info "Instalando Composer..."
    php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');"
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
    rm /tmp/composer-setup.php
    ok "Composer instalado."
else
    ok "Composer $(composer --version --no-ansi | head -1 | awk '{print $3}') encontrado."
fi

# ── 5. MySQL ─────────────────────────────────────────────────
echo ""
info "Verificando MySQL..."
if ! command -v mysql &>/dev/null; then
    info "Instalando MySQL Server..."
    sudo apt-get install -y mysql-server
    sudo systemctl start mysql
    sudo systemctl enable mysql
    ok "MySQL instalado y activo."
else
    ok "MySQL encontrado."
fi

# ── 6. Dependencias del proyecto ─────────────────────────────
echo ""
info "Instalando dependencias PHP del proyecto (Composer)..."
composer install --no-dev --optimize-autoloader --quiet
ok "Dependencias instaladas."

# ── 7. Configurar .env ───────────────────────────────────────
echo ""
echo -e "${BOLD}── Configuración de base de datos ──────────────${RESET}"
echo ""

if [ -f "$ROOT/.env" ]; then
    warn ".env ya existe. ¿Sobreescribir? (s/n)"
    read -r OVERWRITE
    if [[ "$OVERWRITE" != "s" && "$OVERWRITE" != "S" ]]; then
        info "Manteniendo .env existente."
        SKIP_ENV=true
    fi
fi

if [ "${SKIP_ENV:-false}" = false ]; then
    ask "Nombre de la base de datos [ronceros]:"
    read -r DB_NAME; DB_NAME=${DB_NAME:-ronceros}

    ask "Usuario de MySQL [root]:"
    read -r DB_USER; DB_USER=${DB_USER:-root}

    ask "Contraseña de MySQL:"
    read -rs DB_PASS; echo ""

    ask "Host de MySQL [localhost]:"
    read -r DB_HOST; DB_HOST=${DB_HOST:-localhost}

    # Verificar conexión
    info "Verificando conexión a MySQL..."
    if ! mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1;" &>/dev/null; then
        err "No se pudo conectar a MySQL. Verifica usuario y contraseña."
    fi
    ok "Conexión a MySQL exitosa."

    # Crear base de datos si no existe
    info "Creando base de datos '$DB_NAME' si no existe..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" \
        -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    ok "Base de datos lista."

    # Escribir .env
    info "Escribiendo archivo .env..."
    cat > "$ROOT/.env" <<EOF
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = ${DB_HOST}
database.default.database = ${DB_NAME}
database.default.username = ${DB_USER}
database.default.password = ${DB_PASS}
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port     = 3306
EOF
    ok ".env creado."
fi

# ── 8. Migraciones ───────────────────────────────────────────
echo ""
info "Ejecutando migraciones de base de datos..."
php spark migrate --quiet
ok "Migraciones aplicadas."

# ── 9. Seeders ───────────────────────────────────────────────
echo ""
ask "¿Cargar datos de ejemplo (seeders)? Útil para pruebas iniciales. (s/n)"
read -r RUN_SEED
if [[ "$RUN_SEED" == "s" || "$RUN_SEED" == "S" ]]; then
    info "Ejecutando seeders..."
    php spark db:seed DatabaseSeeder
    ok "Datos de ejemplo cargados."
    echo ""
    echo "  Usuarios de prueba:"
    echo "    carlos.admin   / Admin1234!   → Administrador"
    echo "    maria.ventas   / Ventas123!   → Vendedor"
    echo "    jorge.foto     / Foto1234!    → Fotógrafo"
else
    info "Seeders omitidos."
fi

# ── 10. Cloudflare Tunnel (opcional) ─────────────────────────
echo ""
ask "¿Instalar cloudflared para el formulario público en internet? (s/n)"
read -r INSTALL_CF
if [[ "$INSTALL_CF" == "s" || "$INSTALL_CF" == "S" ]]; then
    if command -v cloudflared &>/dev/null; then
        ok "cloudflared ya está instalado ($(cloudflared --version))."
    else
        info "Descargando cloudflared..."
        ARCH=$(uname -m)
        if [ "$ARCH" = "x86_64" ]; then
            PKG="cloudflared-linux-amd64.deb"
        elif [ "$ARCH" = "aarch64" ]; then
            PKG="cloudflared-linux-arm64.deb"
        else
            warn "Arquitectura $ARCH no soportada. Instala cloudflared manualmente."
            PKG=""
        fi

        if [ -n "$PKG" ]; then
            curl -fsSL "https://github.com/cloudflare/cloudflared/releases/latest/download/${PKG}" \
                -o /tmp/cloudflared.deb
            sudo dpkg -i /tmp/cloudflared.deb
            rm /tmp/cloudflared.deb
            ok "cloudflared instalado."
        fi
    fi
else
    info "cloudflared omitido. Puedes instalarlo luego ejecutando este script de nuevo."
fi

# ── 11. Permisos ─────────────────────────────────────────────
echo ""
info "Configurando permisos de directorios..."
chmod -R 775 "$ROOT/writable"
chmod -R 775 "$ROOT/public/images"
ok "Permisos configurados."

# ── Resumen final ────────────────────────────────────────────
echo ""
echo -e "${BOLD}============================================${RESET}"
echo -e "${GREEN}${BOLD}  ✓ Instalación completada${RESET}"
echo -e "${BOLD}============================================${RESET}"
echo ""
echo "  Comandos disponibles:"
echo ""
echo -e "  ${CYAN}php spark serve${RESET}          → Levantar servidor local"
echo -e "  ${CYAN}bash scripts/tunnel.sh${RESET}   → Levantar servidor + túnel público"
echo -e "  ${CYAN}php spark migrate${RESET}        → Aplicar migraciones nuevas"
echo -e "  ${CYAN}php spark test${RESET}           → Correr tests"
echo -e "  ${CYAN}php spark docs:api${RESET}       → Generar documentación de API"
echo ""
echo "  Sistema disponible en: http://localhost:8080"
echo ""
