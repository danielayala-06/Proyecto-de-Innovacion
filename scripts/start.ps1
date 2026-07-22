# ============================================================
# start.ps1 — Inicia MariaDB, PHP-CGI y Nginx
# Uso: powershell -ExecutionPolicy Bypass -File scripts\start.ps1
# ============================================================

$APP_DIR = (Get-Item (Split-Path -Parent $PSScriptRoot)).FullName
$RUNTIME = (Get-Item (Split-Path -Parent $APP_DIR)).FullName

function ok   { param($m) Write-Host "  [OK]  $m" -ForegroundColor Green }
function info { param($m) Write-Host "  [>>]  $m" -ForegroundColor Cyan }
function warn { param($m) Write-Host "  [!!]  $m" -ForegroundColor Yellow }
function fail { param($m) Write-Host "  [XX]  $m" -ForegroundColor Red; exit 1 }

if (-not (Test-Path "$RUNTIME\logs")) {
    New-Item -ItemType Directory -Force -Path "$RUNTIME\logs" | Out-Null
}

Write-Host ""
Write-Host "  Ronceros Fotografía — Iniciando servicios" -ForegroundColor White
Write-Host ""

# ── 1. MariaDB ────────────────────────────────────────────────
$svc = Get-Service "Ronceros-MariaDB" -ErrorAction SilentlyContinue
if (-not $svc) { fail "Servicio 'Ronceros-MariaDB' no encontrado. Ejecuta instalar-portable.ps1 primero." }

if ($svc.Status -eq 'Running') {
    ok "MariaDB ya está corriendo."
} else {
    info "Iniciando MariaDB..."
    Start-Service "Ronceros-MariaDB"
    Start-Sleep -Seconds 3
    $svc.Refresh()
    if ($svc.Status -ne 'Running') { fail "MariaDB no arrancó. Revisa $RUNTIME\logs\mariadb-error.log" }
    ok "MariaDB iniciado."
}

# ── 2. PHP-CGI ────────────────────────────────────────────────
$portOcupado = Get-NetTCPConnection -LocalPort 9000 -State Listen -ErrorAction SilentlyContinue
if ($portOcupado) {
    ok "PHP-CGI ya escucha en el puerto 9000."
} else {
    info "Iniciando PHP-CGI (5 workers en 127.0.0.1:9000)..."
    $env:PHP_FCGI_CHILDREN     = "5"
    $env:PHP_FCGI_MAX_REQUESTS = "500"
    Start-Process "$RUNTIME\php\php-cgi.exe" `
        -ArgumentList "-b", "127.0.0.1:9000" -WindowStyle Hidden

    $waited = 0
    do {
        Start-Sleep -Milliseconds 500
        $waited += 500
        $ready = Get-NetTCPConnection -LocalPort 9000 -State Listen -ErrorAction SilentlyContinue
    } while (-not $ready -and $waited -lt 10000)

    if (-not $ready) { fail "PHP-CGI no respondió. Verifica $RUNTIME\php\php.ini" }
    ok "PHP-CGI listo."
}

# ── 3. Nginx ─────────────────────────────────────────────────
if (Get-Process "nginx" -ErrorAction SilentlyContinue) {
    ok "Nginx ya está corriendo."
} else {
    info "Iniciando Nginx..."
    Start-Process "$RUNTIME\nginx\nginx.exe" `
        -WorkingDirectory "$RUNTIME\nginx" -WindowStyle Hidden
    Start-Sleep -Seconds 2

    if (-not (Get-NetTCPConnection -LocalPort 80 -State Listen -ErrorAction SilentlyContinue)) {
        warn "Nginx podría no estar en el puerto 80. Revisa $RUNTIME\nginx\logs\error.log"
    } else {
        ok "Nginx listo."
    }
}

Write-Host ""
Write-Host "  http://localhost/" -ForegroundColor Green
Write-Host ""
