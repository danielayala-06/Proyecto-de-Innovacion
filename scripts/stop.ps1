# ============================================================
# stop.ps1 — Detiene todos los servicios de Ronceros Fotografía
# Uso: powershell -ExecutionPolicy Bypass -File scripts\stop.ps1
# ============================================================

$APP_DIR = (Get-Item (Split-Path -Parent $PSScriptRoot)).FullName
$RUNTIME = (Get-Item (Split-Path -Parent $APP_DIR)).FullName

function ok   { param($m) Write-Host "  [OK]  $m" -ForegroundColor Green }
function info { param($m) Write-Host "  [>>]  $m" -ForegroundColor Cyan }
function warn { param($m) Write-Host "  [!!]  $m" -ForegroundColor Yellow }

Write-Host ""
Write-Host "  Ronceros Fotografía — Deteniendo servicios" -ForegroundColor White
Write-Host ""

# Nginx
if (Get-Process "nginx" -ErrorAction SilentlyContinue) {
    info "Deteniendo Nginx..."
    Start-Process "$RUNTIME\nginx\nginx.exe" `
        -ArgumentList "-s", "quit" -WorkingDirectory "$RUNTIME\nginx" -WindowStyle Hidden -Wait
    Start-Sleep -Milliseconds 800
    Get-Process "nginx" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
    ok "Nginx detenido."
} else { warn "Nginx no estaba corriendo." }

# PHP-CGI
$cgi = Get-Process "php-cgi" -ErrorAction SilentlyContinue
if ($cgi) {
    info "Deteniendo PHP-CGI ($($cgi.Count) proceso(s))..."
    $cgi | Stop-Process -Force -ErrorAction SilentlyContinue
    ok "PHP-CGI detenido."
} else { warn "PHP-CGI no estaba corriendo." }

# MariaDB
$svc = Get-Service "Ronceros-MariaDB" -ErrorAction SilentlyContinue
if ($svc -and $svc.Status -eq 'Running') {
    info "Deteniendo MariaDB..."
    Stop-Service "Ronceros-MariaDB" -Force
    Start-Sleep -Seconds 2
    ok "MariaDB detenido."
} elseif ($svc) { warn "MariaDB ya estaba detenido." }
  else           { warn "Servicio 'Ronceros-MariaDB' no encontrado." }

Write-Host ""
ok "Todos los servicios detenidos."
Write-Host ""
