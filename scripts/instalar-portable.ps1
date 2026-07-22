# ============================================================
# instalar-portable.ps1 — Ronceros Fotografía
# Stack portable: MariaDB + Nginx + PHP-CGI (sin XAMPP, sin PATH)
#
# Estructura esperada:
#   C:\RoncerosRuntime\
#     app\        <- este proyecto (git clone aquí)
#       scripts\  <- este script
#       public\   <- webroot
#       spark     <- CI4 entrypoint
#     mariadb\    <- MariaDB portable
#     nginx\      <- Nginx portable
#     php\        <- PHP portable (php.exe, php-cgi.exe)
#
# Ejecutar UNA SOLA VEZ, como Administrador:
#   powershell -ExecutionPolicy Bypass -File scripts\instalar-portable.ps1
# ============================================================

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

# ── Rutas base ────────────────────────────────────────────────
# $PSScriptRoot = C:\RoncerosRuntime\app\scripts
# $APP_DIR      = C:\RoncerosRuntime\app    (proyecto CI4)
# $RUNTIME      = C:\RoncerosRuntime        (mariadb, nginx, php)
$APP_DIR  = (Get-Item (Split-Path -Parent $PSScriptRoot)).FullName
$RUNTIME  = (Get-Item (Split-Path -Parent $APP_DIR)).FullName
$APP_DIRF = $APP_DIR  -replace '\\', '/'
$RUNTIMEF = $RUNTIME  -replace '\\', '/'

# ── Helpers ──────────────────────────────────────────────────
function ok   { param($m) Write-Host "  [OK]  $m" -ForegroundColor Green }
function info { param($m) Write-Host "  [>>]  $m" -ForegroundColor Cyan }
function warn { param($m) Write-Host "  [!!]  $m" -ForegroundColor Yellow }
function fail { param($m) Write-Host "  [XX]  $m" -ForegroundColor Red; exit 1 }
function sep  { Write-Host ("-" * 50) -ForegroundColor DarkGray }
function ask  { param($m) Write-Host "`n$m" -ForegroundColor White }

Clear-Host
Write-Host ""
Write-Host "================================================" -ForegroundColor White
Write-Host "  Ronceros Fotografía — Instalador Portable" -ForegroundColor White
Write-Host "================================================" -ForegroundColor White
Write-Host "  App:     $APP_DIR" -ForegroundColor DarkGray
Write-Host "  Runtime: $RUNTIME"  -ForegroundColor DarkGray
Write-Host ""

# ── 0. Admin ─────────────────────────────────────────────────
$isAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole(
    [Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) { fail "Ejecuta PowerShell como Administrador." }
ok "Ejecutando como Administrador."

# ── 1. Verificar estructura ───────────────────────────────────
sep
info "Verificando estructura..."
@{
    "MariaDB"    = "$RUNTIME\mariadb\bin\mariadbd.exe"
    "Nginx"      = "$RUNTIME\nginx\nginx.exe"
    "PHP CLI"    = "$RUNTIME\php\php.exe"
    "PHP CGI"    = "$RUNTIME\php\php-cgi.exe"
    "CI4 spark"  = "$APP_DIR\spark"
    "Webroot"    = "$APP_DIR\public\index.php"
}.GetEnumerator() | ForEach-Object {
    if (-not (Test-Path $_.Value)) { fail "$($_.Key) no encontrado: $($_.Value)" }
    ok "$($_.Key)"
}

# ── 2. Credenciales ───────────────────────────────────────────
sep
Write-Host ""
Write-Host "  Configuración de la base de datos" -ForegroundColor White
Write-Host ""

ask "Nombre de la base de datos [ronceros_foto]:"
$DB_NAME = (Read-Host "  >").Trim()
if (-not $DB_NAME) { $DB_NAME = "ronceros_foto" }

ask "Usuario de la app [ronceros]:"
$DB_USER = (Read-Host "  >").Trim()
if (-not $DB_USER) { $DB_USER = "ronceros" }

ask "Contraseña para '$DB_USER' (mín. 8 caracteres):"
$DB_PASS = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
    [Runtime.InteropServices.Marshal]::SecureStringToBSTR((Read-Host "  >" -AsSecureString)))
if ($DB_PASS.Length -lt 8) { fail "Contraseña muy corta." }

ask "Contraseña root de MariaDB (para administración):"
$ROOT_PASS = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
    [Runtime.InteropServices.Marshal]::SecureStringToBSTR((Read-Host "  >" -AsSecureString)))
if ($ROOT_PASS.Length -lt 8) { fail "Contraseña root muy corta." }

# ── 3. Dependencias PHP (Composer) ───────────────────────────
sep
info "Verificando dependencias PHP (vendor/)..."

$VENDOR_DIR    = "$APP_DIR\vendor"
$COMPOSER_PHAR = "$APP_DIR\installer\composer.phar"

if (Test-Path "$VENDOR_DIR\autoload.php") {
    ok "vendor/ ya existe — omitiendo Composer."
} else {
    warn "vendor/ no encontrado. Instalando dependencias..."

    # Buscar composer.phar: 1) installer/, 2) app/, 3) descargar
    $composerFound = $COMPOSER_PHAR
    if (-not (Test-Path $COMPOSER_PHAR)) {
        if (Test-Path "$APP_DIR\composer.phar") {
            $composerFound = "$APP_DIR\composer.phar"
        } else {
            info "Descargando composer.phar..."
            if (-not (Test-Path "$APP_DIR\installer")) {
                New-Item -ItemType Directory -Force -Path "$APP_DIR\installer" | Out-Null
            }
            try {
                Invoke-WebRequest `
                    -Uri "https://getcomposer.org/composer-stable.phar" `
                    -OutFile $COMPOSER_PHAR `
                    -UseBasicParsing
                ok "composer.phar descargado."
            } catch {
                fail "No se pudo descargar Composer. Verifica la conexión a internet o copia composer.phar a installer\."
            }
        }
    }

    info "Ejecutando composer install --no-dev..."
    Push-Location $APP_DIR
    try {
        & "$RUNTIME\php\php.exe" $composerFound install --no-dev --optimize-autoloader --no-interaction
        if ($LASTEXITCODE -ne 0) { fail "composer install falló." }
        ok "Dependencias instaladas en vendor/."
    } finally {
        Pop-Location
    }
}

# ── 4. Inicializar MariaDB ────────────────────────────────────
sep
info "Configurando MariaDB..."

$DB_DATA = "$RUNTIME\mariadb\data"
$MY_INI  = "$RUNTIME\config\my.ini"

if (-not (Test-Path "$RUNTIME\config")) {
    New-Item -ItemType Directory -Force -Path "$RUNTIME\config" | Out-Null
}
if (-not (Test-Path "$RUNTIME\logs")) {
    New-Item -ItemType Directory -Force -Path "$RUNTIME\logs"  | Out-Null
}

if (Test-Path "$DB_DATA\mysql") {
    warn "Directorio de datos ya existe — omitiendo inicialización."
} else {
    New-Item -ItemType Directory -Force -Path $DB_DATA | Out-Null
    info "Ejecutando mariadb-install-db..."
    $p = Start-Process "$RUNTIME\mariadb\bin\mariadb-install-db.exe" `
        -ArgumentList "--datadir=`"$DB_DATA`"", "--basedir=`"$RUNTIME\mariadb`"" `
        -NoNewWindow -PassThru -Wait
    if ($p.ExitCode -ne 0) { fail "mariadb-install-db falló (código $($p.ExitCode))." }
    ok "Directorio de datos inicializado."
}

# Generar my.ini
@"
[mysqld]
basedir        = $RUNTIMEF/mariadb
datadir        = $RUNTIMEF/mariadb/data
port           = 3306
bind-address   = 127.0.0.1
character-set-server  = utf8mb4
collation-server      = utf8mb4_unicode_ci
innodb_buffer_pool_size = 64M
max_connections = 20
skip_name_resolve = 1
log_error      = $RUNTIMEF/logs/mariadb-error.log

[client]
port                 = 3306
default-character-set = utf8mb4

[mysql]
default-character-set = utf8mb4
"@ | Set-Content $MY_INI -Encoding UTF8
ok "my.ini generado."

# ── 5. Registrar e iniciar MariaDB como servicio ─────────────
sep
info "Registrando servicio 'Ronceros-MariaDB'..."

$svc = Get-Service "Ronceros-MariaDB" -ErrorAction SilentlyContinue
if ($svc) {
    Stop-Service "Ronceros-MariaDB" -Force -ErrorAction SilentlyContinue
    & sc.exe delete "Ronceros-MariaDB" | Out-Null
    Start-Sleep -Seconds 2
}

$p = Start-Process "$RUNTIME\mariadb\bin\mariadbd.exe" `
    -ArgumentList "--defaults-file=`"$MY_INI`"", "--install-manual", "Ronceros-MariaDB" `
    -NoNewWindow -PassThru -Wait
if ($p.ExitCode -ne 0) { fail "No se pudo registrar el servicio MariaDB." }

Start-Service "Ronceros-MariaDB"
Start-Sleep -Seconds 4
$svc = Get-Service "Ronceros-MariaDB"
if ($svc.Status -ne 'Running') { fail "MariaDB no arrancó. Revisa $RUNTIME\logs\mariadb-error.log" }
ok "MariaDB corriendo."

# ── 6. Crear base de datos y usuario ─────────────────────────
sep
info "Creando base de datos '$DB_NAME' y usuario '$DB_USER'..."

$MYSQL = "$RUNTIME\mariadb\bin\mariadb.exe"
$tmpSql = "$env:TEMP\ronceros_init.sql"

@"
ALTER USER 'root'@'localhost' IDENTIFIED BY '$ROOT_PASS';
CREATE DATABASE IF NOT EXISTS ``$DB_NAME`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS';
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost'  IDENTIFIED BY '$DB_PASS';
GRANT ALL ON ``$DB_NAME``.* TO '$DB_USER'@'127.0.0.1';
GRANT ALL ON ``$DB_NAME``.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
"@ | Set-Content $tmpSql -Encoding UTF8

# Primera conexión sin contraseña (instalación limpia)
$p = Start-Process $MYSQL -ArgumentList "-u", "root", "--execute=`"source $tmpSql`"" `
    -NoNewWindow -PassThru -Wait
if ($p.ExitCode -ne 0) {
    # Reinstalación: puede que ya tenga contraseña
    $p2 = Start-Process $MYSQL `
        -ArgumentList "-u", "root", "-p$ROOT_PASS", "--execute=`"source $tmpSql`"" `
        -NoNewWindow -PassThru -Wait
    if ($p2.ExitCode -ne 0) { fail "No se pudo configurar la BD. ¿Olvidaste ejecutar limpiar-mariadb.ps1?" }
}
Remove-Item $tmpSql -Force
ok "Base de datos y usuario creados."

# ── 7. Configurar Nginx ───────────────────────────────────────
sep
info "Generando nginx.conf..."

$NGINX_CONF = "$RUNTIME\nginx\conf\nginx.conf"

# Las variables de nginx ($uri, $args) se escapan con backtick en PowerShell heredoc
@"
worker_processes  1;
error_log  logs/error.log warn;
pid        logs/nginx.pid;

events { worker_connections 256; }

http {
    include       mime.types;
    default_type  application/octet-stream;
    access_log    logs/access.log;
    sendfile      on;
    keepalive_timeout 65;
    client_max_body_size 20M;

    server {
        listen      80;
        server_name localhost;
        root        $APP_DIRF/public;
        index       index.php index.html;
        charset     utf-8;

        location / {
            try_files `$uri `$uri/ /index.php`$is_args`$args;
        }

        location ~ \.php$ {
            fastcgi_pass  127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME `$document_root`$fastcgi_script_name;
            fastcgi_param PATH_INFO       `$fastcgi_path_info;
            include       fastcgi_params;
        }

        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }
        location ~ /\.          { deny all; }
    }
}
"@ | Set-Content $NGINX_CONF -Encoding UTF8

$p = Start-Process "$RUNTIME\nginx\nginx.exe" `
    -ArgumentList "-p", "$RUNTIME\nginx", "-t" `
    -NoNewWindow -PassThru -Wait
if ($p.ExitCode -ne 0) { fail "nginx.conf inválido — revisa la sintaxis." }
ok "nginx.conf generado y validado."

# ── 8. Crear .env ─────────────────────────────────────────────
sep
info "Creando app\.env..."

$ENV_FILE = "$APP_DIR\.env"
if ((Test-Path $ENV_FILE) -and (Read-Host "  .env ya existe. ¿Sobreescribir? (s/n)") -notin @('s','S')) {
    info "Manteniendo .env existente."
} else {
@"
CI_ENVIRONMENT = production

app.baseURL = 'http://localhost/'

database.default.hostname  = 127.0.0.1
database.default.database  = $DB_NAME
database.default.username  = $DB_USER
database.default.password  = $DB_PASS
database.default.DBDriver  = MySQLi
database.default.DBPrefix  =
database.default.port      = 3306
database.default.charset   = utf8mb4
database.default.collation = utf8mb4_unicode_ci
"@ | Set-Content $ENV_FILE -Encoding UTF8
    ok ".env creado."
}

# ── 9. Permisos de writable/ ──────────────────────────────────
sep
info "Configurando writable/..."
$writable = "$APP_DIR\writable"
foreach ($sub in @('cache','logs','session','uploads','debugbar')) {
    $path = "$writable\$sub"
    if (-not (Test-Path $path)) { New-Item -ItemType Directory -Force -Path $path | Out-Null }
}
$acl  = Get-Acl $writable
$rule = New-Object System.Security.AccessControl.FileSystemAccessRule(
    "Everyone", "FullControl", "ContainerInherit,ObjectInherit", "None", "Allow")
$acl.SetAccessRule($rule)
Set-Acl -Path $writable -AclObject $acl
ok "writable/ configurado."

# ── 10. Iniciar Nginx + PHP-CGI para migraciones ─────────────
sep
info "Iniciando PHP-CGI para ejecutar migraciones..."

$env:PHP_FCGI_CHILDREN     = "2"
$env:PHP_FCGI_MAX_REQUESTS = "200"
$phpCgiProc = Start-Process "$RUNTIME\php\php-cgi.exe" `
    -ArgumentList "-b", "127.0.0.1:9000" -WindowStyle Hidden -PassThru

Start-Sleep -Seconds 2

# ── 11. Migraciones ───────────────────────────────────────────
sep
info "Ejecutando migraciones..."
Push-Location $APP_DIR
try {
    & "$RUNTIME\php\php.exe" spark migrate --all
    if ($LASTEXITCODE -ne 0) { fail "Migraciones fallaron." }
    ok "Migraciones aplicadas."
} finally { Pop-Location }

# ── 12. Seeders (opcional) ────────────────────────────────────
sep
ask "¿Cargar datos de ejemplo (seeders)? (s/n)"
if ((Read-Host "  >") -in @('s', 'S')) {
    Push-Location $APP_DIR
    try {
        & "$RUNTIME\php\php.exe" spark db:seed DatabaseSeeder
        ok "Seeders ejecutados."
    } finally { Pop-Location }
    Write-Host ""
    Write-Host "  Usuarios de prueba:" -ForegroundColor Cyan
    Write-Host "    carlos.admin   / Admin1234!   -> Administrador"
    Write-Host "    maria.ventas   / Ventas123!   -> Vendedor"
    Write-Host "    jorge.foto     / Foto1234!    -> Fotografo"
    Write-Host "    ana.supervisor / Super123!    -> Supervisor"
} else { info "Seeders omitidos." }

# Detener el php-cgi temporal
$phpCgiProc | Stop-Process -Force -ErrorAction SilentlyContinue

# ── 13. Tarea de inicio automático ───────────────────────────
sep
info "Registrando tarea de inicio automático..."

$taskArgs = "-ExecutionPolicy Bypass -WindowStyle Hidden -NonInteractive -File `"$APP_DIR\scripts\start.ps1`""
$action   = New-ScheduledTaskAction -Execute "powershell.exe" -Argument $taskArgs
$trigger  = New-ScheduledTaskTrigger -AtStartup
$settings = New-ScheduledTaskSettingsSet -ExecutionTimeLimit 0 -RestartCount 3 -RestartInterval (New-TimeSpan -Minutes 1)
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest

Get-ScheduledTask "RoncerosApp" -ErrorAction SilentlyContinue |
    Unregister-ScheduledTask -Confirm:$false -ErrorAction SilentlyContinue

Register-ScheduledTask "RoncerosApp" `
    -Action $action -Trigger $trigger -Settings $settings -Principal $principal `
    -Description "Nginx + PHP-CGI para Ronceros Fotografía" | Out-Null
ok "Tarea 'RoncerosApp' registrada."

# ── 14. Arranque final ────────────────────────────────────────
sep
info "Iniciando todos los servicios..."
& "$APP_DIR\scripts\start.ps1"

# ── Resumen ───────────────────────────────────────────────────
Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host "  Instalación completada" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  http://localhost/" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Comandos:" -ForegroundColor White
Write-Host "    INICIAR.bat                            -> Arranque con un clic"
Write-Host "    powershell scripts\start.ps1           -> Solo iniciar servicios"
Write-Host "    powershell scripts\stop.ps1            -> Detener servicios"
Write-Host "    powershell scripts\tunnel.ps1          -> Formulario público (internet)"
Write-Host ""
Write-Host "  Logs:" -ForegroundColor DarkGray
Write-Host "    $RUNTIME\logs\mariadb-error.log"
Write-Host "    $RUNTIME\nginx\logs\error.log"
Write-Host "    $RUNTIME\logs\php-error.log"
Write-Host ""
