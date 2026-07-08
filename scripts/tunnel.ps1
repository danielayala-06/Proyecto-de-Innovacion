# ============================================================
# Levanta el servidor CI4 + Cloudflare Tunnel en paralelo.
# El formulario queda accesible desde internet.
# El panel de gestión solo desde la red local.
#
# Uso:
#   powershell -ExecutionPolicy Bypass -File scripts\tunnel.ps1
# Para detener: Ctrl+C
# ============================================================

$ROOT = Split-Path -Parent $PSScriptRoot
Set-Location $ROOT

$PORT = if ($env:PORT) { $env:PORT } else { "8080" }
$TUNNEL_URL_FILE = "$ROOT\writable\tunnel_url.txt"

# Verificar cloudflared
if (-not (Get-Command cloudflared -ErrorAction SilentlyContinue)) {
    Write-Host "  XX  cloudflared no está instalado." -ForegroundColor Red
    Write-Host "      Ejecuta primero: powershell -ExecutionPolicy Bypass -File scripts\install.ps1"
    exit 1
}

# Verificar PHP
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "  XX  PHP no encontrado en el PATH." -ForegroundColor Red
    exit 1
}

# Limpiar archivo anterior
if (Test-Path $TUNNEL_URL_FILE) { Remove-Item $TUNNEL_URL_FILE -Force }

Write-Host ""
Write-Host "============================================" -ForegroundColor White
Write-Host "  Ronceros Fotografía — Modo Túnel" -ForegroundColor White
Write-Host "============================================" -ForegroundColor White
Write-Host ""
Write-Host "  >>  Levantando servidor PHP en puerto $PORT..." -ForegroundColor Cyan

$phpJob = Start-Process -FilePath "php" `
    -ArgumentList "spark", "serve", "--port", $PORT `
    -PassThru -WindowStyle Hidden

Start-Sleep -Seconds 2

Write-Host "  >>  Conectando con Cloudflare..." -ForegroundColor Cyan
Write-Host ""

# Ejecutar cloudflared y capturar su output línea por línea
$cfProcess = Start-Process -FilePath "cloudflared" `
    -ArgumentList "tunnel", "--url", "http://localhost:$PORT" `
    -RedirectStandardError "$env:TEMP\cf_output.txt" `
    -RedirectStandardOutput "$env:TEMP\cf_stdout.txt" `
    -PassThru -WindowStyle Hidden

# Leer output en tiempo real
$urlFound = $false
$timeout  = 60
$elapsed  = 0

while (-not $cfProcess.HasExited -and $elapsed -lt $timeout) {
    Start-Sleep -Seconds 1
    $elapsed++

    $lines = @()
    if (Test-Path "$env:TEMP\cf_output.txt") {
        $lines += Get-Content "$env:TEMP\cf_output.txt" -ErrorAction SilentlyContinue
    }
    if (Test-Path "$env:TEMP\cf_stdout.txt") {
        $lines += Get-Content "$env:TEMP\cf_stdout.txt" -ErrorAction SilentlyContinue
    }

    foreach ($line in $lines) {
        if ($line -match 'https://([a-z0-9-]+\.trycloudflare\.com)') {
            $tunnelUrl = "https://$($Matches[1])"
            Set-Content -Path $TUNNEL_URL_FILE -Value $tunnelUrl -Encoding UTF8

            if (-not $urlFound) {
                $urlFound = $true
                Write-Host ""
                Write-Host "  ============================================" -ForegroundColor Green
                Write-Host "  URL PUBLICA DEL FORMULARIO:" -ForegroundColor Green
                Write-Host "  $tunnelUrl/formulario/grupo/<token>" -ForegroundColor Yellow
                Write-Host ""
                Write-Host "  Copia ese link desde el panel de Formularios"
                Write-Host "  y compartelo con el colegio."
                Write-Host "  ============================================" -ForegroundColor Green
                Write-Host ""
                Write-Host "  Panel de gestión: http://localhost:$PORT" -ForegroundColor Cyan
                Write-Host "  Para detener: cierra esta ventana o presiona Ctrl+C"
                Write-Host ""
            }
        }
    }
}

if (-not $urlFound) {
    Write-Host "  !!  No se pudo obtener la URL del túnel. Revisa la conexión a internet." -ForegroundColor Yellow
}

# Esperar a que cloudflared termine (Ctrl+C)
try {
    $cfProcess.WaitForExit()
} finally {
    Write-Host ""
    Write-Host "  >>  Cerrando servidor..." -ForegroundColor Cyan
    if (Test-Path $TUNNEL_URL_FILE) { Remove-Item $TUNNEL_URL_FILE -Force }
    if ($phpJob -and -not $phpJob.HasExited) {
        Stop-Process -Id $phpJob.Id -Force -ErrorAction SilentlyContinue
    }
    if (-not $cfProcess.HasExited) {
        Stop-Process -Id $cfProcess.Id -Force -ErrorAction SilentlyContinue
    }
}
