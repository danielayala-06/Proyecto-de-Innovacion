#!/bin/bash
# ============================================================
# Deploy de Ronceros Fotografía — VPS Ubuntu/Debian
# Instala: Nginx + PHP 8.3-FPM + MySQL + Composer
#
# Uso (en el VPS como root o con sudo):
#   bash deploy-vps.sh
# ============================================================

set -e

# ── Colores ──────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

ok()   { echo -e "${GREEN}✓${RESET} $1"; }
info() { echo -e "${CYAN}→${RESET} $1"; }
warn() { echo -e "${YELLOW}⚠${RESET} $1"; }
err()  { echo -e "${RED}✗${RESET} $1"; exit 1; }
ask()  { echo -e "\n${BOLD}$1${RESET}"; }

REPO_URL="https://github.com/danielayala-06/Proyecto-de-Innovacion.git"
APP_DIR="/var/www/ronceros"
NGINX_CONF="/etc/nginx/sites-available/ronceros"

echo ""
echo -e "${BOLD}============================================${RESET}"
echo -e "${BOLD}  Ronceros Fotografía — Deploy VPS${RESET}"
echo -e "${BOLD}============================================${RESET}"
echo ""

# ── 0. Verificar root ────────────────────────────────────────
if [ "$EUID" -ne 0 ]; then
    err "Ejecuta este script como root: sudo bash deploy-vps.sh"
fi

# ── 1. Datos iniciales ───────────────────────────────────────
ask "IP pública o dominio del servidor (ej: 123.45.67.89 o misitio.com):"
read -r SERVER_HOST; SERVER_HOST=${SERVER_HOST:-localhost}

ask "¿El repo de GitHub es privado? Necesitas un Personal Access Token. (s/n):"
read -r REPO_PRIVATE
if [[ "$REPO_PRIVATE" == "s" || "$REPO_PRIVATE" == "S" ]]; then
    ask "Token de GitHub (github.com → Settings → Developer settings → Tokens):"
    read -rs GH_TOKEN; echo ""
    CLONE_URL="https://${GH_TOKEN}@github.com/danielayala-06/Proyecto-de-Innovacion.git"
else
    CLONE_URL="$REPO_URL"
fi

ask "Nombre de la base de datos [ronceros]:"
read -r DB_NAME; DB_NAME=${DB_NAME:-ronceros}

ask "Usuario MySQL para la app [ronceros_user]:"
read -r DB_USER; DB_USER=${DB_USER:-ronceros_user}

ask "Contraseña para el usuario MySQL:"
read -rs DB_PASS; echo ""
[ -z "$DB_PASS" ] && err "La contraseña no puede estar vacía."

ask "Clave API DECOLECTA (RENIEC) — dejar vacío si no la tienes:"
read -r DECOLECTA_KEY

# ── 2. Actualizar sistema ────────────────────────────────────
echo ""
info "Actualizando sistema..."
apt-get update -qq
apt-get upgrade -y -qq
ok "Sistema actualizado."

# ── 3. Instalar Nginx ────────────────────────────────────────
info "Instalando Nginx..."
apt-get install -y -qq nginx
systemctl enable nginx
systemctl start nginx
ok "Nginx instalado."

# ── 4. Instalar PHP 8.3 + FPM + extensiones ──────────────────
info "Instalando PHP 8.3 + FPM..."
apt-get install -y -qq software-properties-common
# Ondrej PPA (Ubuntu) o repo de Sury (Debian)
if grep -qi ubuntu /etc/os-release; then
    add-apt-repository -y ppa:ondrej/php
else
    apt-get install -y -qq apt-transport-https lsb-release ca-certificates curl
    curl -sSL https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /usr/share/keyrings/sury-php.gpg
    echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" \
        > /etc/apt/sources.list.d/sury-php.list
    apt-get update -qq
fi

apt-get install -y -qq \
    php8.3-fpm \
    php8.3-cli \
    php8.3-mysql \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-gd \
    php8.3-intl
ok "PHP 8.3-FPM instalado."

# ── 5. Instalar MySQL ─────────────────────────────────────────
info "Instalando MySQL Server..."
apt-get install -y -qq default-mysql-server
systemctl enable mysql
systemctl start mysql
ok "MySQL instalado."

# Crear BD y usuario
info "Configurando base de datos..."
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
ok "Base de datos '${DB_NAME}' y usuario '${DB_USER}' listos."

# ── 6. Instalar Git + Composer ───────────────────────────────
info "Instalando Git y Composer..."
apt-get install -y -qq git unzip
if ! command -v composer &>/dev/null; then
    php -r "copy('https://getcomposer.org/installer', '/tmp/cs.php');"
    php /tmp/cs.php --install-dir=/usr/local/bin --filename=composer --quiet
    rm /tmp/cs.php
fi
ok "Git y Composer listos."

# ── 7. Clonar repositorio ────────────────────────────────────
info "Clonando repositorio en ${APP_DIR}..."
if [ -d "$APP_DIR/.git" ]; then
    warn "El directorio ya existe. Actualizando con git pull..."
    cd "$APP_DIR"
    git pull origin main
else
    git clone "$CLONE_URL" "$APP_DIR"
fi
ok "Repositorio clonado."

cd "$APP_DIR"

# ── 8. Dependencias Composer ─────────────────────────────────
info "Instalando dependencias PHP (sin dev)..."
composer install --no-dev --optimize-autoloader --quiet
ok "Dependencias instaladas."

# ── 9. Archivo .env ──────────────────────────────────────────
info "Creando archivo .env de producción..."
cat > "$APP_DIR/.env" <<EOF
CI_ENVIRONMENT = production

app.baseURL = 'http://${SERVER_HOST}/'

database.default.hostname = localhost
database.default.database = ${DB_NAME}
database.default.username = ${DB_USER}
database.default.password = ${DB_PASS}
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port     = 3306

DECOLECTA.KEY = ${DECOLECTA_KEY}
EOF
ok ".env creado."

# ── 10. Migraciones y seeders ─────────────────────────────────
info "Ejecutando migraciones..."
php spark migrate --quiet
ok "Migraciones aplicadas."

ask "¿Cargar datos base (usuarios, paquetes, productos)? (s/n)"
read -r RUN_SEED
if [[ "$RUN_SEED" == "s" || "$RUN_SEED" == "S" ]]; then
    php spark db:seed DatabaseSeeder
    ok "Datos base cargados."
fi

ask "¿Cargar contratos reales de producción? (s/n)"
read -r RUN_REAL
if [[ "$RUN_REAL" == "s" || "$RUN_REAL" == "S" ]]; then
    php spark db:seed ContratosRealSeeder
    ok "Contratos reales cargados."
fi

# ── 11. Permisos ─────────────────────────────────────────────
info "Configurando permisos..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/writable"
chmod -R 775 "$APP_DIR/public/images"
ok "Permisos configurados."

# ── 12. Configurar Nginx ──────────────────────────────────────
info "Configurando Nginx..."
cat > "$NGINX_CONF" <<NGINX
server {
    listen 80;
    server_name ${SERVER_HOST};

    root ${APP_DIR}/public;
    index index.php;

    charset utf-8;
    client_max_body_size 20M;

    # Logs
    access_log /var/log/nginx/ronceros_access.log;
    error_log  /var/log/nginx/ronceros_error.log;

    location / {
        try_files \$uri \$uri/ /index.php\$is_args\$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    # Bloquear acceso a archivos sensibles
    location ~ /\.(env|git|htaccess) {
        deny all;
        return 404;
    }

    # Bloquear acceso a directorios internos de CI4
    location ~ ^/(app|system|writable|tests)/ {
        deny all;
        return 404;
    }

    location = /favicon.ico { log_not_found off; access_log off; }
    location = /robots.txt  { log_not_found off; access_log off; }
}
NGINX

# Activar site
ln -sf "$NGINX_CONF" /etc/nginx/sites-enabled/ronceros
rm -f /etc/nginx/sites-enabled/default

# Verificar config y recargar
nginx -t && systemctl reload nginx
ok "Nginx configurado y recargado."

# ── 13. PHP-FPM: ajustes de producción ───────────────────────
info "Ajustando PHP-FPM para producción..."
PHP_INI="/etc/php/8.3/fpm/php.ini"
sed -i 's/^;*upload_max_filesize.*/upload_max_filesize = 20M/' "$PHP_INI"
sed -i 's/^;*post_max_size.*/post_max_size = 22M/'             "$PHP_INI"
sed -i 's/^;*memory_limit.*/memory_limit = 256M/'              "$PHP_INI"
sed -i 's/^;*max_execution_time.*/max_execution_time = 60/'    "$PHP_INI"
systemctl restart php8.3-fpm
ok "PHP-FPM configurado."

# ── Resumen ───────────────────────────────────────────────────
echo ""
echo -e "${BOLD}============================================${RESET}"
echo -e "${GREEN}${BOLD}  ✓ Deploy completado${RESET}"
echo -e "${BOLD}============================================${RESET}"
echo ""
echo -e "  App disponible en: ${CYAN}http://${SERVER_HOST}${RESET}"
echo ""
echo "  Usuarios de prueba (si cargaste los seeders):"
echo "    carlos.admin  / Admin1234!"
echo "    maria.ventas  / Ventas123!"
echo ""
echo "  Logs de Nginx:"
echo "    tail -f /var/log/nginx/ronceros_error.log"
echo ""
echo -e "${YELLOW}  IMPORTANTE: El repo de GitHub debe tener el branch 'main' actualizado.${RESET}"
echo -e "${YELLOW}  Para updates futuros: cd ${APP_DIR} && git pull && php spark migrate${RESET}"
echo ""
