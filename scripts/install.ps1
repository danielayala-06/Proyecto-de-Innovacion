# ============================================================
# Instalador de Ronceros Fotografía — Windows
# Compatible con Windows 10/11 (PowerShell 5.1+)
#
# Uso (ejecutar como Administrador):
#   powershell -ExecutionPolicy Bypass -File scripts\install.ps1
# ============================================================

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$ROOT = Split-Path -Parent $PSScriptRoot

# ── Helpers ──────────────────────────────────────────────────
function ok   { param($msg) Write-Host "  OK  $msg" -ForegroundColor Green }
function info { param($msg) Write-Host "  >>  $msg" -ForegroundColor Cyan }
function warn { param($msg) Write-Host "  !!  $msg" -ForegroundColor Yellow }
function err  { param($msg) Write-Host "  XX  $msg" -ForegroundColor Red; exit 1 }
function ask  { param($msg) Write-Host "`n$msg" -ForegroundColor White }

# ── Encabezado ───────────────────────────────────────────────
Write-Host ""
Write-Host "============================================" -ForegroundColor White
Write-Host "  Ronceros Fotografía — Instalador Windows" -ForegroundColor White
Write-Host "============================================" -ForegroundColor White
Write-Host ""

# ── 1. Verificar que corre como Administrador ────────────────
if (-not ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    err "Ejecuta PowerShell como Administrador (clic derecho → Ejecutar como administrador)."
}
ok "Ejecutando como Administrador."

# ── 2. PHP ───────────────────────────────────────────────────
Write-Host ""
info "Verificando PHP..."

$phpCmd = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpCmd) {
    Write-Host ""
    warn "PHP no encontrado en el PATH."
    Write-Host "  Opciones para instalar PHP en Windows:" -ForegroundColor Yellow
    Write-Host "    1. XAMPP (recomendado para principiantes):" -ForegroundColor Yellow
    Write-Host "       https://www.apachefriends.org/download.html" -ForegroundColor Cyan
    Write-Host "       Luego agrega C:\xampp\php al PATH del sistema." -ForegroundColor Yellow
    Write-Host "    2. Chocolatey: choco install php" -ForegroundColor Yellow
    Write-Host ""
    err "Instala PHP 8.2+ y vuelve a ejecutar este script."
} else {
    $phpVer = php -r "echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;"
    $major, $minor = $phpVer -split '\.'
    if ([int]$major -lt 8 -or ([int]$major -eq 8 -and [int]$minor -lt 2)) {
        err "PHP $phpVer es muy antiguo. Se necesita PHP 8.2+."
    }
    ok "PHP $phpVer encontrado en $($phpCmd.Source)."
}

# ── 3. Extensiones PHP ───────────────────────────────────────
info "Verificando extensiones PHP requeridas..."
$loaded = php -m 2>$null
$required = @('gd', 'mbstring', 'mysqli', 'curl', 'zip', 'xml')
$missing = @()
foreach ($ext in $required) {
    if ($loaded -notcontains $ext) { $missing += $ext }
}

if ($missing.Count -gt 0) {
    warn "Extensiones faltantes: $($missing -join ', ')"
    Write-Host "  En php.ini habilita las siguientes líneas (quita el ;):" -ForegroundColor Yellow
    foreach ($ext in $missing) {
        Write-Host "    extension=$ext" -ForegroundColor Cyan
    }
    Write-Host "  php.ini suele estar en C:\xampp\php\php.ini" -ForegroundColor Yellow
    Write-Host ""
    $continue = Read-Host "  ¿Continuar de todas formas? (s/n)"
    if ($continue -ne 's' -and $continue -ne 'S') { exit 1 }
} else {
    ok "Todas las extensiones PHP están disponibles."
}

# ── 4. MySQL ─────────────────────────────────────────────────
Write-Host ""
info "Verificando MySQL..."
$mysqlCmd = Get-Command mysql -ErrorAction SilentlyContinue
if (-not $mysqlCmd) {
    warn "mysql no encontrado en el PATH."
    Write-Host "  Si usas XAMPP, agrega C:\xampp\mysql\bin al PATH del sistema." -ForegroundColor Yellow
    Write-Host "  Asegúrate de que el servicio MySQL esté corriendo." -ForegroundColor Yellow
    Write-Host ""
    $continue = Read-Host "  ¿MySQL está corriendo y quieres continuar? (s/n)"
    if ($continue -ne 's' -and $continue -ne 'S') { exit 1 }
} else {
    ok "MySQL encontrado en $($mysqlCmd.Source)."
}

# ── 5. Composer ──────────────────────────────────────────────
Write-Host ""
info "Verificando Composer..."
$composerCmd = Get-Command composer -ErrorAction SilentlyContinue
if (-not $composerCmd) {
    info "Descargando e instalando Composer..."
    $composerInstaller = "$env:TEMP\composer-setup.php"
    Invoke-WebRequest -Uri "https://getcomposer.org/installer" -OutFile $composerInstaller
    php $composerInstaller --install-dir="$env:ProgramData\ComposerSetup\bin" --filename=composer
    Remove-Item $composerInstaller

    $env:PATH += ";$env:ProgramData\ComposerSetup\bin"
    ok "Composer instalado."
} else {
    ok "Composer encontrado."
}

# ── 6. Dependencias del proyecto ─────────────────────────────
Write-Host ""
info "Instalando dependencias PHP del proyecto (Composer)..."
Set-Location $ROOT
composer install --no-dev --optimize-autoloader --quiet
ok "Dependencias instaladas."

# ── 7. Configurar .env ───────────────────────────────────────
Write-Host ""
Write-Host "── Configuración de base de datos ──────────────" -ForegroundColor White
Write-Host ""

$skipEnv = $false
if (Test-Path "$ROOT\.env") {
    warn ".env ya existe."
    $overwrite = Read-Host "  ¿Sobreescribir? (s/n)"
    if ($overwrite -ne 's' -and $overwrite -ne 'S') {
        info "Manteniendo .env existente."
        $skipEnv = $true
    }
}

if (-not $skipEnv) {
    ask "Nombre de la base de datos [ronceros]:"
    $dbName = Read-Host "  >"
    if (-not $dbName) { $dbName = "ronceros" }

    ask "Usuario de MySQL [root]:"
    $dbUser = Read-Host "  >"
    if (-not $dbUser) { $dbUser = "root" }

    ask "Contraseña de MySQL:"
    $dbPassSecure = Read-Host "  >" -AsSecureString
    $dbPass = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
        [Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPassSecure)
    )

    ask "Host de MySQL [localhost]:"
    $dbHost = Read-Host "  >"
    if (-not $dbHost) { $dbHost = "localhost" }

    # Verificar conexión
    info "Verificando conexión a MySQL..."
    $testQuery = mysql -h $dbHost -u $dbUser "-p$dbPass" -e "SELECT 1;" 2>&1
    if ($LASTEXITCODE -ne 0) {
        err "No se pudo conectar a MySQL. Verifica usuario y contraseña."
    }
    ok "Conexión a MySQL exitosa."

    # Crear base de datos
    info "Creando base de datos '$dbName' si no existe..."
    mysql -h $dbHost -u $dbUser "-p$dbPass" -e "CREATE DATABASE IF NOT EXISTS ``$dbName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>$null
    ok "Base de datos lista."

    # Escribir .env
    info "Escribiendo archivo .env..."
    @"
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = $dbHost
database.default.database = $dbName
database.default.username = $dbUser
database.default.password = $dbPass
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port     = 3306
"@ | Set-Content "$ROOT\.env" -Encoding UTF8
    ok ".env creado."
}

# ── 8. Migraciones ───────────────────────────────────────────
Write-Host ""
info "Ejecutando migraciones de base de datos..."
php spark migrate --quiet
ok "Migraciones aplicadas."

# ── 9. Seeders ───────────────────────────────────────────────
Write-Host ""
ask "¿Cargar datos de ejemplo (seeders)? Útil para pruebas iniciales. (s/n)"
$runSeed = Read-Host "  >"
if ($runSeed -eq 's' -or $runSeed -eq 'S') {
    info "Ejecutando seeders..."
    php spark db:seed DatabaseSeeder
    ok "Datos de ejemplo cargados."
    Write-Host ""
    Write-Host "  Usuarios de prueba:" -ForegroundColor Cyan
    Write-Host "    carlos.admin   / Admin1234!   -> Administrador"
    Write-Host "    maria.ventas   / Ventas123!   -> Vendedor"
    Write-Host "    jorge.foto     / Foto1234!    -> Fotografo"
} else {
    info "Seeders omitidos."
}

# ── 10. Cloudflare Tunnel (opcional) ─────────────────────────
Write-Host ""
ask "¿Instalar cloudflared para el formulario público en internet? (s/n)"
$installCF = Read-Host "  >"
if ($installCF -eq 's' -or $installCF -eq 'S') {
    $cfCmd = Get-Command cloudflared -ErrorAction SilentlyContinue
    if ($cfCmd) {
        ok "cloudflared ya está instalado."
    } else {
        info "Descargando cloudflared para Windows..."
        $cfDest = "$env:ProgramFiles\cloudflared"
        New-Item -ItemType Directory -Force -Path $cfDest | Out-Null
        Invoke-WebRequest `
            -Uri "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe" `
            -OutFile "$cfDest\cloudflared.exe"

        # Agregar al PATH del sistema
        $currentPath = [Environment]::GetEnvironmentVariable("PATH", "Machine")
        if ($currentPath -notlike "*$cfDest*") {
            [Environment]::SetEnvironmentVariable("PATH", "$currentPath;$cfDest", "Machine")
            $env:PATH += ";$cfDest"
        }
        ok "cloudflared instalado en $cfDest."
        warn "Cierra y vuelve a abrir PowerShell para que el PATH se actualice."
    }
} else {
    info "cloudflared omitido. Puedes instalarlo luego ejecutando este script de nuevo."
}

# ── Resumen final ────────────────────────────────────────────
Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host "  OK  Instalación completada" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Comandos disponibles:"
Write-Host ""
Write-Host "    php spark serve                  " -NoNewline; Write-Host "-> Levantar servidor local" -ForegroundColor Cyan
Write-Host "    powershell scripts\tunnel.ps1    " -NoNewline; Write-Host "-> Levantar servidor + tunel publico" -ForegroundColor Cyan
Write-Host "    php spark migrate                " -NoNewline; Write-Host "-> Aplicar migraciones nuevas" -ForegroundColor Cyan
Write-Host "    php spark test                   " -NoNewline; Write-Host "-> Correr tests" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Sistema disponible en: http://localhost:8080"
Write-Host ""
