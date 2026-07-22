#Requires -Version 5.1
# ============================================================
# actualizar-gui.ps1 — Actualizador con interfaz gráfica
# Ronceros Fotografía - Sistema de gestión
#
# Flujo: comprueba GitHub Releases → descarga ZIP → detiene
#        servicios → copia archivos → migra BD → reinicia.
# ============================================================

# ── CONFIGURA ESTO ANTES DE DISTRIBUIR ───────────────────────────────────────
$GITHUB_OWNER = 'daniel-06'           # tu usuario de GitHub
$GITHUB_REPO  = 'ronceros-fotografia' # nombre del repositorio en GitHub
$GITHUB_TOKEN = ''                    # Personal Access Token (solo si el repo es privado)
# ─────────────────────────────────────────────────────────────────────────────

param()  # no recibe parámetros externos

# ── Auto-elevación a Administrador ──────────────────────────────────────────
$id = [Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()
if (-not $id.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Start-Process powershell "-ExecutionPolicy Bypass -File `"$PSCommandPath`"" -Verb RunAs
    exit
}

Add-Type -AssemblyName System.Windows.Forms, System.Drawing

# ── Rutas ─────────────────────────────────────────────────────────────────────
$APP_DIR      = Split-Path -Parent $PSScriptRoot
$RUNTIME      = Split-Path -Parent $APP_DIR
$VERSION_FILE = Join-Path $APP_DIR 'version.txt'
$PHP_EXE      = Join-Path $RUNTIME 'php\php.exe'

$SVC_MARIADB = 'Ronceros-MariaDB'
$SVC_PHP     = 'Ronceros-PHP'
$SVC_NGINX   = 'Ronceros-Nginx'

# Versión instalada actualmente
$LOCAL_VER = if (Test-Path $VERSION_FILE) { (Get-Content $VERSION_FILE -Raw).Trim() } else { '0.0.0' }

# ── Formulario ───────────────────────────────────────────────────────────────
$form                = New-Object System.Windows.Forms.Form
$form.Text           = 'Ronceros Fotografía — Actualizador'
$form.ClientSize     = New-Object System.Drawing.Size(560, 440)
$form.StartPosition  = 'CenterScreen'
$form.FormBorderStyle= 'FixedDialog'
$form.MaximizeBox    = $false
$form.BackColor      = [System.Drawing.Color]::White
$form.Font           = New-Object System.Drawing.Font('Segoe UI', 9)

# Cabecera
$pnl             = New-Object System.Windows.Forms.Panel
$pnl.Dock        = 'Top'
$pnl.Height      = 80
$pnl.BackColor   = [System.Drawing.Color]::FromArgb(15, 23, 42)

$lbrand          = New-Object System.Windows.Forms.Label
$lbrand.Text     = 'Ronceros Fotografía'
$lbrand.Font     = New-Object System.Drawing.Font('Segoe UI', 16, [System.Drawing.FontStyle]::Bold)
$lbrand.ForeColor= [System.Drawing.Color]::White
$lbrand.Location = New-Object System.Drawing.Point(20, 12)
$lbrand.AutoSize = $true

$lsub            = New-Object System.Windows.Forms.Label
$lsub.Text       = 'Actualizador del sistema'
$lsub.Font       = New-Object System.Drawing.Font('Segoe UI', 9)
$lsub.ForeColor  = [System.Drawing.Color]::FromArgb(148, 163, 184)
$lsub.Location   = New-Object System.Drawing.Point(22, 48)
$lsub.AutoSize   = $true

$pnl.Controls.AddRange(@($lbrand, $lsub))
$form.Controls.Add($pnl)

# Panel de versiones
$pnlVer           = New-Object System.Windows.Forms.Panel
$pnlVer.Location  = New-Object System.Drawing.Point(20, 96)
$pnlVer.Size      = New-Object System.Drawing.Size(518, 60)
$pnlVer.BackColor = [System.Drawing.Color]::FromArgb(241, 245, 249)
$pnlVer.BorderStyle = 'FixedSingle'
$form.Controls.Add($pnlVer)

$lblVerIns         = New-Object System.Windows.Forms.Label
$lblVerIns.Text    = "Versión instalada:"
$lblVerIns.Font    = New-Object System.Drawing.Font('Segoe UI', 9)
$lblVerIns.Location= New-Object System.Drawing.Point(14, 10)
$lblVerIns.AutoSize= $true

$lblVerInsVal         = New-Object System.Windows.Forms.Label
$lblVerInsVal.Text    = $LOCAL_VER
$lblVerInsVal.Font    = New-Object System.Drawing.Font('Segoe UI', 9, [System.Drawing.FontStyle]::Bold)
$lblVerInsVal.Location= New-Object System.Drawing.Point(130, 10)
$lblVerInsVal.AutoSize= $true

$lblVerNueva         = New-Object System.Windows.Forms.Label
$lblVerNueva.Text    = "Última versión:"
$lblVerNueva.Font    = New-Object System.Drawing.Font('Segoe UI', 9)
$lblVerNueva.Location= New-Object System.Drawing.Point(14, 34)
$lblVerNueva.AutoSize= $true

$lblVerNuevaVal         = New-Object System.Windows.Forms.Label
$lblVerNuevaVal.Text    = "Comprobando..."
$lblVerNuevaVal.Font    = New-Object System.Drawing.Font('Segoe UI', 9, [System.Drawing.FontStyle]::Bold)
$lblVerNuevaVal.ForeColor= [System.Drawing.Color]::Gray
$lblVerNuevaVal.Location = New-Object System.Drawing.Point(130, 34)
$lblVerNuevaVal.AutoSize = $true

$pnlVer.Controls.AddRange(@($lblVerIns, $lblVerInsVal, $lblVerNueva, $lblVerNuevaVal))

# Notas de la release (changelog)
$lblNotes          = New-Object System.Windows.Forms.Label
$lblNotes.Text     = 'Notas de la actualización:'
$lblNotes.Location = New-Object System.Drawing.Point(20, 166)
$lblNotes.AutoSize = $true
$form.Controls.Add($lblNotes)

$txtNotes          = New-Object System.Windows.Forms.RichTextBox
$txtNotes.ReadOnly = $true
$txtNotes.Font     = New-Object System.Drawing.Font('Segoe UI', 8.5)
$txtNotes.Location = New-Object System.Drawing.Point(20, 184)
$txtNotes.Size     = New-Object System.Drawing.Size(518, 130)
$txtNotes.BackColor= [System.Drawing.Color]::FromArgb(248, 250, 252)
$txtNotes.Text     = 'Comprobando conexión con GitHub...'
$form.Controls.Add($txtNotes)

# Barra de progreso y estado
$progress          = New-Object System.Windows.Forms.ProgressBar
$progress.Location = New-Object System.Drawing.Point(20, 326)
$progress.Size     = New-Object System.Drawing.Size(518, 16)
$progress.Style    = 'Continuous'
$form.Controls.Add($progress)

$lblStatus          = New-Object System.Windows.Forms.Label
$lblStatus.Text     = 'Listo.'
$lblStatus.ForeColor= [System.Drawing.Color]::Gray
$lblStatus.Location = New-Object System.Drawing.Point(20, 346)
$lblStatus.Size     = New-Object System.Drawing.Size(518, 18)
$form.Controls.Add($lblStatus)

# Botones
$btnActualizar                           = New-Object System.Windows.Forms.Button
$btnActualizar.Text                      = 'Actualizar'
$btnActualizar.Font                      = New-Object System.Drawing.Font('Segoe UI', 10, [System.Drawing.FontStyle]::Bold)
$btnActualizar.Location                  = New-Object System.Drawing.Point(322, 390)
$btnActualizar.Size                      = New-Object System.Drawing.Size(104, 34)
$btnActualizar.BackColor                 = [System.Drawing.Color]::FromArgb(37, 99, 235)
$btnActualizar.ForeColor                 = [System.Drawing.Color]::White
$btnActualizar.FlatStyle                 = 'Flat'
$btnActualizar.FlatAppearance.BorderSize = 0
$btnActualizar.Enabled                   = $false
$form.Controls.Add($btnActualizar)

$btnCerrar          = New-Object System.Windows.Forms.Button
$btnCerrar.Text     = 'Cerrar'
$btnCerrar.Location = New-Object System.Drawing.Point(434, 390)
$btnCerrar.Size     = New-Object System.Drawing.Size(104, 34)
$btnCerrar.Add_Click({ $form.Close() })
$form.Controls.Add($btnCerrar)

# ── Helpers ─────────────────────────────────────────────────────────────────
function Set-Status([string]$msg, [int]$pct = -1) {
    $lblStatus.Text = $msg
    if ($pct -ge 0) { $progress.Value = [Math]::Min($pct, 100) }
    [System.Windows.Forms.Application]::DoEvents()
}

function Compare-SemVer([string]$local, [string]$remote) {
    # Elimina prefijo 'v' si existe
    $l = [Version]($local  -replace '^v', '')
    $r = [Version]($remote -replace '^v', '')
    return $r.CompareTo($l)  # >0 = remote más nuevo
}

# ── Variables de release compartidas ────────────────────────────────────────
$script:releaseTag = $null
$script:zipUrl     = $null

# ── Comprobar versión al cargar el formulario ────────────────────────────────
$form.Add_Shown({
    Set-Status 'Consultando GitHub...' 0
    try {
        $headers = @{ 'User-Agent' = 'RoncerosUpdater/1.0' }
        if ($GITHUB_TOKEN) { $headers['Authorization'] = "token $GITHUB_TOKEN" }

        $apiUrl   = "https://api.github.com/repos/$GITHUB_OWNER/$GITHUB_REPO/releases/latest"
        $response = Invoke-RestMethod -Uri $apiUrl -Headers $headers -ErrorAction Stop

        $script:releaseTag = $response.tag_name
        $script:zipUrl     = $response.zipball_url
        $remoteBody        = $response.body

        $lblVerNuevaVal.Text = $script:releaseTag
        $txtNotes.Text       = if ($remoteBody) { $remoteBody } else { 'Sin notas de versión.' }

        $cmp = Compare-SemVer $LOCAL_VER $script:releaseTag
        if ($cmp -gt 0) {
            $lblVerNuevaVal.ForeColor = [System.Drawing.Color]::FromArgb(22, 163, 74)  # verde
            $btnActualizar.Enabled    = $true
            Set-Status "Actualización disponible: $($script:releaseTag)" 0
        } elseif ($cmp -eq 0) {
            $lblVerNuevaVal.ForeColor = [System.Drawing.Color]::Gray
            $txtNotes.Text            = 'Ya tienes la versión más reciente instalada.'
            Set-Status 'El sistema está actualizado.' 100
        } else {
            $lblVerNuevaVal.ForeColor = [System.Drawing.Color]::FromArgb(234, 88, 12)  # naranja
            $txtNotes.Text            = "Tu versión ($LOCAL_VER) es más nueva que la publicada ($($script:releaseTag))."
            Set-Status 'Sin actualizaciones disponibles.' 100
        }
    } catch {
        $lblVerNuevaVal.Text     = 'Sin conexión'
        $lblVerNuevaVal.ForeColor= [System.Drawing.Color]::FromArgb(220, 38, 38)
        $txtNotes.Text           = "No se pudo consultar GitHub:`n$_`n`nVerifica tu conexión a internet."
        Set-Status 'Error de conexión con GitHub.' 0
    }
})

# ── Lógica de actualización ──────────────────────────────────────────────────
$btnActualizar.Add_Click({
    $confirm = [System.Windows.Forms.MessageBox]::Show(
        "Se actualizará el sistema a la versión $($script:releaseTag).$([Environment]::NewLine)$([Environment]::NewLine)" +
        "Los servicios se detendrán brevemente durante la actualización.$([Environment]::NewLine)" +
        "¿Deseas continuar?",
        'Confirmar actualización',
        [System.Windows.Forms.MessageBoxButtons]::YesNo,
        [System.Windows.Forms.MessageBoxIcon]::Question
    )
    if ($confirm -ne 'Yes') { return }

    $btnActualizar.Enabled = $false
    $btnCerrar.Enabled     = $false
    $TMP = [System.IO.Path]::GetTempPath()

    try {
        # ── 1. Detener servicios ──────────────────────────────────
        Set-Status 'Deteniendo servicios...' 5
        foreach ($svc in @($SVC_NGINX, $SVC_PHP)) {
            $s = Get-Service $svc -ErrorAction SilentlyContinue
            if ($s -and $s.Status -eq 'Running') { Stop-Service $svc -Force -ErrorAction SilentlyContinue }
        }
        Start-Sleep -Seconds 2

        # ── 2. Descargar ZIP de la release ────────────────────────
        Set-Status "Descargando $($script:releaseTag)..." 15
        $zipPath = Join-Path $TMP "ronceros-update.zip"
        $headers = @{ 'User-Agent' = 'RoncerosUpdater/1.0' }
        if ($GITHUB_TOKEN) { $headers['Authorization'] = "token $GITHUB_TOKEN" }

        $wc = New-Object System.Net.WebClient
        foreach ($k in $headers.Keys) { $wc.Headers.Add($k, $headers[$k]) }
        $wc.DownloadFile($script:zipUrl, $zipPath)

        # ── 3. Extraer en directorio temporal ─────────────────────
        Set-Status 'Extrayendo archivos...' 40
        $extractDir = Join-Path $TMP "ronceros-update-$([System.Guid]::NewGuid())"
        Expand-Archive -LiteralPath $zipPath -DestinationPath $extractDir -Force

        # El ZIP de GitHub contiene una carpeta raíz (owner-repo-sha/)
        $sourceRoot = Get-ChildItem $extractDir -Directory | Select-Object -First 1
        if (-not $sourceRoot) { throw 'El ZIP de la release tiene formato inesperado.' }

        # ── 4. Copiar archivos de la aplicación ───────────────────
        # Preserva: .env, writable/, vendor/, INICIAR.bat, DETENER.bat
        Set-Status 'Copiando archivos actualizados...' 60

        $preserve = @('.env', 'vendor', 'writable', 'INICIAR.bat', 'DETENER.bat', 'Desinstalar.exe')

        # Directorios a actualizar
        foreach ($dir in @('app', 'public', 'scripts', 'installer')) {
            $src = Join-Path $sourceRoot.FullName $dir
            $dst = Join-Path $APP_DIR $dir
            if (Test-Path $src) {
                if (Test-Path $dst) { Remove-Item $dst -Recurse -Force }
                Copy-Item $src $dst -Recurse -Force
            }
        }

        # Archivos raíz (spark, composer.json, composer.lock, preload.php, version.txt)
        $rootFiles = @('spark', 'composer.json', 'composer.lock', 'preload.php', 'version.txt')
        foreach ($f in $rootFiles) {
            $src = Join-Path $sourceRoot.FullName $f
            if (Test-Path $src) { Copy-Item $src (Join-Path $APP_DIR $f) -Force }
        }

        # ── 5. Actualizar dependencias si cambió composer.json ─────
        Set-Status 'Actualizando dependencias PHP...' 72
        if (Test-Path $PHP_EXE) {
            $composerPhar = Join-Path $APP_DIR 'installer\composer.phar'
            if (Test-Path $composerPhar) {
                Push-Location $APP_DIR
                try {
                    & $PHP_EXE $composerPhar install --no-dev --optimize-autoloader --no-interaction 2>&1 | Out-Null
                } finally { Pop-Location }
            }
        }

        # ── 6. Ejecutar migraciones nuevas ────────────────────────
        Set-Status 'Aplicando migraciones de base de datos...' 85
        $mariadbSvc = Get-Service $SVC_MARIADB -ErrorAction SilentlyContinue
        if ($mariadbSvc -and $mariadbSvc.Status -ne 'Running') {
            Start-Service $SVC_MARIADB
            Start-Sleep -Seconds 3
        }
        if (Test-Path $PHP_EXE) {
            Push-Location $APP_DIR
            try { & $PHP_EXE spark migrate --all 2>&1 | Out-Null }
            finally { Pop-Location }
        }

        # ── 7. Reiniciar servicios ────────────────────────────────
        Set-Status 'Reiniciando servicios...' 93
        foreach ($svc in @($SVC_PHP, $SVC_NGINX)) {
            $s = Get-Service $svc -ErrorAction SilentlyContinue
            if ($s) { Start-Service $svc -ErrorAction SilentlyContinue }
        }
        Start-Sleep -Seconds 2

        # ── 8. Limpiar temporales y registrar versión ─────────────
        Remove-Item $zipPath      -Force -ErrorAction SilentlyContinue
        Remove-Item $extractDir   -Recurse -Force -ErrorAction SilentlyContinue

        # version.txt ya fue copiado; asegurar que refleja la release
        Set-Content $VERSION_FILE ($script:releaseTag -replace '^v', '') -Encoding UTF8

        Set-Status "¡Actualización a $($script:releaseTag) completada!" 100
        $lblVerInsVal.Text = $script:releaseTag -replace '^v', ''

        [System.Windows.Forms.MessageBox]::Show(
            "Sistema actualizado a la versión $($script:releaseTag).$([Environment]::NewLine)$([Environment]::NewLine)" +
            "El sistema ya está disponible en http://localhost",
            'Actualización completada',
            [System.Windows.Forms.MessageBoxButtons]::OK,
            [System.Windows.Forms.MessageBoxIcon]::Information
        ) | Out-Null

    } catch {
        Set-Status "Error: $_" $progress.Value
        [System.Windows.Forms.MessageBox]::Show(
            "Error durante la actualización:`n`n$_`n`nLos servicios pueden necesitar reiniciarse manualmente.",
            'Error de actualización',
            [System.Windows.Forms.MessageBoxButtons]::OK,
            [System.Windows.Forms.MessageBoxIcon]::Error
        ) | Out-Null
        # Intentar reiniciar servicios aunque falle
        foreach ($svc in @($SVC_PHP, $SVC_NGINX)) {
            Start-Service $svc -ErrorAction SilentlyContinue
        }
    } finally {
        $btnCerrar.Enabled = $true
        $btnCerrar.Text    = 'Cerrar'
    }
})

[System.Windows.Forms.Application]::Run($form)
