# ============================================================
# limpiar-mariadb.ps1
# Detecta y elimina TODOS los servicios MySQL/MariaDB existentes
# sin necesitar usuario ni contraseña — mata el proceso directamente.
#
# Ejecutar como Administrador ANTES de instalar el stack portable.
# ============================================================

Set-StrictMode -Version Latest
$ErrorActionPreference = "SilentlyContinue"

function ok   { param($m) Write-Host "  [OK]  $m" -ForegroundColor Green }
function info { param($m) Write-Host "  [>>]  $m" -ForegroundColor Cyan }
function warn { param($m) Write-Host "  [!!]  $m" -ForegroundColor Yellow }
function fail { param($m) Write-Host "  [XX]  $m" -ForegroundColor Red; exit 1 }

# Verificar admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole(
    [Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) { fail "Ejecuta como Administrador." }

Write-Host ""
Write-Host "================================================" -ForegroundColor White
Write-Host "  Limpiador de MariaDB/MySQL existentes" -ForegroundColor White
Write-Host "================================================" -ForegroundColor White
Write-Host ""

# ── Detectar servicios MySQL/MariaDB ─────────────────────────
$servicios = Get-Service | Where-Object {
    $_.Name  -match "(?i)mysql|mariadb" -or
    $_.DisplayName -match "(?i)mysql|mariadb"
}

# Incluir también el servicio del instalador portable si existe
$portableService = Get-Service "Ronceros-MariaDB" -ErrorAction SilentlyContinue
if ($portableService -and $servicios.Name -notcontains "Ronceros-MariaDB") {
    $servicios = @($servicios) + @($portableService)
}

if (-not $servicios -or $servicios.Count -eq 0) {
    warn "No se encontraron servicios MySQL/MariaDB instalados."
    Write-Host "  El sistema está limpio — puedes ejecutar instalar-portable.ps1" -ForegroundColor Cyan
    exit 0
}

Write-Host "  Servicios encontrados:" -ForegroundColor White
foreach ($s in $servicios) {
    $color = if ($s.Status -eq 'Running') { 'Red' } else { 'DarkGray' }
    Write-Host "    - $($s.Name)  [$($s.Status)]  $($s.DisplayName)" -ForegroundColor $color
}
Write-Host ""

$confirm = Read-Host "  ¿Eliminar TODOS estos servicios? (s/n)"
if ($confirm -notin @('s', 'S')) {
    info "Cancelado. No se realizaron cambios."
    exit 0
}

Write-Host ""

# ── Matar procesos primero (sin contraseña) ───────────────────
info "Deteniendo procesos mysqld / mariadbd..."
$procs = @('mysqld', 'mariadbd', 'mysqld-nt', 'mysqld-opt')
foreach ($procName in $procs) {
    $proc = Get-Process -Name $procName -ErrorAction SilentlyContinue
    if ($proc) {
        $proc | Stop-Process -Force -ErrorAction SilentlyContinue
        ok "Proceso '$procName' terminado."
    }
}
Start-Sleep -Seconds 2

# ── Eliminar cada servicio ────────────────────────────────────
foreach ($s in $servicios) {
    info "Eliminando servicio '$($s.Name)'..."

    # Intentar parada ordenada primero
    Stop-Service $s.Name -Force -ErrorAction SilentlyContinue
    Start-Sleep -Milliseconds 500

    # Eliminar con sc.exe
    $result = & sc.exe delete $s.Name 2>&1
    if ($LASTEXITCODE -eq 0) {
        ok "Servicio '$($s.Name)' eliminado."
    } else {
        # Puede estar marcado para borrar en el próximo reinicio
        warn "Servicio '$($s.Name)': se eliminará en el próximo inicio de Windows."
        warn "  ($result)"
    }
}

# ── Verificar puerto 3306 ─────────────────────────────────────
Write-Host ""
info "Verificando puerto 3306..."
$portInUse = Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue
if ($portInUse) {
    $pidUsing = $portInUse.OwningProcess
    $procUsing = Get-Process -Id $pidUsing -ErrorAction SilentlyContinue
    warn "Puerto 3306 aún en uso por PID $pidUsing ($($procUsing.Name))."
    $kill = Read-Host "  ¿Matar ese proceso? (s/n)"
    if ($kill -in @('s', 'S')) {
        Stop-Process -Id $pidUsing -Force -ErrorAction SilentlyContinue
        ok "Proceso terminado."
    }
} else {
    ok "Puerto 3306 libre."
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host "  Limpieza completada" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Si algún servicio quedó pendiente de eliminar, reinicia Windows primero."
Write-Host "  Luego ejecuta: powershell -ExecutionPolicy Bypass -File scripts\instalar-portable.ps1"
Write-Host ""
