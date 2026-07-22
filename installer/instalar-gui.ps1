#Requires -Version 5.1
# ============================================================
# instalar-gui.ps1 — Instalador con interfaz gráfica
# Ronceros Fotografía - Sistema de gestión
#
# Uso directo : powershell -ExecutionPolicy Bypass -File instalar-gui.ps1
# Desde Inno  : se lanza automáticamente al finalizar el asistente
# ============================================================
param([string]$RutaRuntime = '')

# ── Auto-elevación a Administrador ───────────────────────────────────────────
$identidad = [Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()
if (-not $identidad.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    $extra = if ($RutaRuntime) { " -RutaRuntime `"$RutaRuntime`"" } else { '' }
    Start-Process powershell "-ExecutionPolicy Bypass -File `"$PSCommandPath`"$extra" -Verb RunAs
    exit
}

Add-Type -AssemblyName System.Windows.Forms, System.Drawing

# ── Rutas ─────────────────────────────────────────────────────────────────────
# $PSScriptRoot = <proyecto>\installer
# $APP_DIR      = <proyecto>
# $RUNTIME      = padre de <proyecto> (donde vivirán php/, mariadb/, nginx/)
$APP_DIR = Split-Path -Parent $PSScriptRoot
$defaultRuntime = if ($RutaRuntime) {
    $RutaRuntime
} elseif ((Split-Path -Parent $APP_DIR) -ne $APP_DIR) {
    Split-Path -Parent $APP_DIR
} else {
    'C:\RoncerosRuntime'
}

# ── Versiones a descargar ────────────────────────────────────────────────────
$PHP_VERSION     = '8.2.27'
$MARIADB_VERSION = '10.11.10'
$NGINX_VERSION   = '1.26.2'
$NSSM_VERSION    = '2.24'

$URL_PHP     = "https://windows.php.net/downloads/releases/php-$PHP_VERSION-nts-Win32-vs16-x64.zip"
$URL_MARIADB = "https://archive.mariadb.org/mariadb-$MARIADB_VERSION/winx64-packages/mariadb-$MARIADB_VERSION-winx64.zip"
$URL_NGINX   = "https://nginx.org/download/nginx-$NGINX_VERSION.zip"
$URL_NSSM    = "https://nssm.cc/release/nssm-$NSSM_VERSION.zip"

$SVC_MARIADB = 'Ronceros-MariaDB'
$SVC_PHP     = 'Ronceros-PHP'
$SVC_NGINX   = 'Ronceros-Nginx'
$CGI_PORT    = 9000
$WEB_PORT    = 80

# ── Formulario principal ─────────────────────────────────────────────────────
$form                = New-Object System.Windows.Forms.Form
$form.Text           = 'Ronceros Fotografía — Instalador'
$form.ClientSize     = New-Object System.Drawing.Size(668, 600)
$form.StartPosition  = 'CenterScreen'
$form.FormBorderStyle= 'FixedDialog'
$form.MaximizeBox    = $false
$form.BackColor      = [System.Drawing.Color]::White
$form.Font           = New-Object System.Drawing.Font('Segoe UI', 9)

# Cabecera
$pnlHeader            = New-Object System.Windows.Forms.Panel
$pnlHeader.Dock       = 'Top'
$pnlHeader.Height     = 90
$pnlHeader.BackColor  = [System.Drawing.Color]::FromArgb(15, 23, 42)

$lblBrand             = New-Object System.Windows.Forms.Label
$lblBrand.Text        = 'Ronceros Fotografía'
$lblBrand.Font        = New-Object System.Drawing.Font('Segoe UI', 18, [System.Drawing.FontStyle]::Bold)
$lblBrand.ForeColor   = [System.Drawing.Color]::White
$lblBrand.Location    = New-Object System.Drawing.Point(22, 14)
$lblBrand.AutoSize    = $true

$lblSub               = New-Object System.Windows.Forms.Label
$lblSub.Text          = 'Asistente de instalación del sistema de gestión'
$lblSub.Font          = New-Object System.Drawing.Font('Segoe UI', 9)
$lblSub.ForeColor     = [System.Drawing.Color]::FromArgb(148, 163, 184)
$lblSub.Location      = New-Object System.Drawing.Point(24, 56)
$lblSub.AutoSize      = $true

$pnlHeader.Controls.AddRange(@($lblBrand, $lblSub))
$form.Controls.Add($pnlHeader)

# ── Helpers de layout ────────────────────────────────────────────────────────
function New-Label([string]$text, [int]$x, [int]$y) {
    $l            = New-Object System.Windows.Forms.Label
    $l.Text       = $text
    $l.Location   = New-Object System.Drawing.Point($x, $y)
    $l.AutoSize   = $true
    $form.Controls.Add($l)
    return $l
}
function New-TextBox([string]$text, [int]$x, [int]$y, [int]$w, [bool]$password = $false) {
    $t            = New-Object System.Windows.Forms.TextBox
    $t.Text       = $text
    $t.Location   = New-Object System.Drawing.Point($x, $y)
    $t.Size       = New-Object System.Drawing.Size($w, 24)
    if ($password) { $t.PasswordChar = '*' }
    $form.Controls.Add($t)
    return $t
}

# Sección: directorio de runtime
New-Label 'Directorio de instalación (servicios PHP, MariaDB, Nginx):' 22 106 | Out-Null
$txtRuntime = New-TextBox $defaultRuntime 22 124 506
$txtRuntime.Font = New-Object System.Drawing.Font('Consolas', 9)

$btnDir           = New-Object System.Windows.Forms.Button
$btnDir.Text      = '…'
$btnDir.Location  = New-Object System.Drawing.Point(536, 123)
$btnDir.Size      = New-Object System.Drawing.Size(106, 26)
$btnDir.Add_Click({
    $dlg = New-Object System.Windows.Forms.FolderBrowserDialog
    $dlg.SelectedPath = $txtRuntime.Text
    $dlg.Description  = 'Selecciona dónde instalar PHP, MariaDB y Nginx'
    if ($dlg.ShowDialog($form) -eq 'OK') { $txtRuntime.Text = $dlg.SelectedPath }
})
$form.Controls.Add($btnDir)

# Sección: credenciales de base de datos
New-Label 'Base de datos:'   22 162 | Out-Null
New-Label 'Usuario BD:'     200 162 | Out-Null
New-Label 'Contraseña BD:'  378 162 | Out-Null

$txtDbName = New-TextBox 'ronceros_foto' 22  180 162
$txtDbUser = New-TextBox 'ronceros'      200 180 162
$txtDbPass = New-TextBox 'Ronceros2024!' 378 180 264 $true

# Separador
$sep             = New-Object System.Windows.Forms.Label
$sep.BorderStyle = 'Fixed3D'
$sep.Location    = New-Object System.Drawing.Point(22, 218)
$sep.Size        = New-Object System.Drawing.Size(622, 2)
$form.Controls.Add($sep)

# Registro de progreso
New-Label 'Registro de instalación:' 22 226 | Out-Null
$txtLog              = New-Object System.Windows.Forms.RichTextBox
$txtLog.ReadOnly     = $true
$txtLog.Font         = New-Object System.Drawing.Font('Consolas', 8)
$txtLog.Location     = New-Object System.Drawing.Point(22, 244)
$txtLog.Size         = New-Object System.Drawing.Size(622, 258)
$txtLog.BackColor    = [System.Drawing.Color]::FromArgb(15, 23, 42)
$txtLog.ForeColor    = [System.Drawing.Color]::FromArgb(226, 232, 240)
$txtLog.BorderStyle  = 'FixedSingle'
$form.Controls.Add($txtLog)

# Barra de progreso y estado
$progress          = New-Object System.Windows.Forms.ProgressBar
$progress.Location = New-Object System.Drawing.Point(22, 510)
$progress.Size     = New-Object System.Drawing.Size(622, 18)
$form.Controls.Add($progress)

$lblStatus           = New-Object System.Windows.Forms.Label
$lblStatus.Text      = 'Listo para instalar.'
$lblStatus.ForeColor = [System.Drawing.Color]::Gray
$lblStatus.Location  = New-Object System.Drawing.Point(22, 532)
$lblStatus.Size      = New-Object System.Drawing.Size(500, 18)
$form.Controls.Add($lblStatus)

# Botones
$btnInstall                              = New-Object System.Windows.Forms.Button
$btnInstall.Text                         = 'Instalar'
$btnInstall.Font                         = New-Object System.Drawing.Font('Segoe UI', 10, [System.Drawing.FontStyle]::Bold)
$btnInstall.Location                     = New-Object System.Drawing.Point(428, 558)
$btnInstall.Size                         = New-Object System.Drawing.Size(104, 34)
$btnInstall.BackColor                    = [System.Drawing.Color]::FromArgb(37, 99, 235)
$btnInstall.ForeColor                    = [System.Drawing.Color]::White
$btnInstall.FlatStyle                    = 'Flat'
$btnInstall.FlatAppearance.BorderSize    = 0
$form.Controls.Add($btnInstall)

$btnClose          = New-Object System.Windows.Forms.Button
$btnClose.Text     = 'Cancelar'
$btnClose.Location = New-Object System.Drawing.Point(540, 558)
$btnClose.Size     = New-Object System.Drawing.Size(104, 34)
$btnClose.Add_Click({ $form.Close() })
$form.Controls.Add($btnClose)

# ── Helpers de log/progress ──────────────────────────────────────────────────
function Write-Log([string]$msg, [string]$color = 'Light') {
    $ts = (Get-Date).ToString('HH:mm:ss')
    $txtLog.SelectionStart  = $txtLog.TextLength
    $txtLog.SelectionLength = 0
    $txtLog.SelectionColor  = switch ($color) {
        'Green'  { [System.Drawing.Color]::FromArgb(134, 239, 172) }
        'Red'    { [System.Drawing.Color]::FromArgb(252, 165, 165) }
        'Yellow' { [System.Drawing.Color]::FromArgb(253, 224,  71) }
        'Dim'    { [System.Drawing.Color]::FromArgb(100, 116, 139) }
        default  { [System.Drawing.Color]::FromArgb(226, 232, 240) }
    }
    $txtLog.AppendText("[$ts] $msg`n")
    $txtLog.ScrollToCaret()
    [System.Windows.Forms.Application]::DoEvents()
}

function Set-Progress([int]$pct, [string]$status = '') {
    $progress.Value = [Math]::Max(0, [Math]::Min($pct, 100))
    if ($status) { $lblStatus.Text = $status; Write-Log $status }
    [System.Windows.Forms.Application]::DoEvents()
}

function Get-Download([string]$url, [string]$dest, [string]$label) {
    Write-Log "  Descargando $label..." 'Dim'
    try {
        $wc = New-Object System.Net.WebClient
        $wc.Headers.Add('User-Agent', 'RoncerosInstaller/1.0')
        $wc.DownloadFile($url, $dest)
    } catch {
        throw "Error al descargar $label`: $_"
    }
    Write-Log "  $label descargado." 'Dim'
}

# Extrae un ZIP que puede tener una carpeta raíz interna (MariaDB, Nginx, NSSM)
function Expand-Zip([string]$zip, [string]$dest) {
    $tmp = Join-Path ([System.IO.Path]::GetTempPath()) ([System.Guid]::NewGuid().ToString())
    Expand-Archive -LiteralPath $zip -DestinationPath $tmp -Force
    $top = Get-ChildItem $tmp -Directory | Select-Object -First 1
    if ($top) {
        if (Test-Path $dest) { Remove-Item $dest -Recurse -Force -ErrorAction SilentlyContinue }
        Move-Item $top.FullName $dest
        Remove-Item $tmp -Recurse -Force -ErrorAction SilentlyContinue
    } else {
        if (Test-Path $dest) { Remove-Item $dest -Recurse -Force -ErrorAction SilentlyContinue }
        Move-Item $tmp $dest
    }
}

function Remove-WinSvc([string]$name, [string]$nssmExe) {
    $svc = Get-Service $name -ErrorAction SilentlyContinue
    if (-not $svc) { return }
    if ($svc.Status -eq 'Running') {
        Stop-Service $name -Force -ErrorAction SilentlyContinue
        Start-Sleep -Milliseconds 1500
    }
    if ($nssmExe -and (Test-Path $nssmExe)) {
        & $nssmExe remove $name confirm 2>&1 | Out-Null
    } else {
        sc.exe delete $name | Out-Null
    }
    Start-Sleep -Milliseconds 1500
}

function Set-EnvKey([ref]$content, [string]$key, [string]$value) {
    $escaped = [regex]::Escape($key)
    if ($content.Value -match "(?m)^#?\s*$escaped\s*=") {
        $content.Value = $content.Value -replace "(?m)^#?\s*$escaped\s*=.*$", "$key = $value"
    } else {
        $content.Value += "`n$key = $value"
    }
}

# ── Lógica principal del instalador ─────────────────────────────────────────
$btnInstall.Add_Click({
    # Bloquear controles durante la instalación
    foreach ($c in @($btnInstall, $btnClose, $btnDir, $txtRuntime,
                     $txtDbName, $txtDbUser, $txtDbPass)) {
        $c.Enabled = $false
    }

    $RUNTIME  = $txtRuntime.Text.TrimEnd('\')
    $DB_NAME  = $txtDbName.Text.Trim()
    $DB_USER  = $txtDbUser.Text.Trim()
    $DB_PASS  = $txtDbPass.Text
    $PHPDIR   = "$RUNTIME\php"
    $DBDIR    = "$RUNTIME\mariadb"
    $NGDIR    = "$RUNTIME\nginx"
    $NSSMDIR  = "$RUNTIME\nssm"
    $LOGDIR   = "$RUNTIME\logs"
    $TMP      = [System.IO.Path]::GetTempPath()

    try {
        # ── 0. Directorios base ──────────────────────────────────────
        Set-Progress 2 'Preparando directorios...'
        foreach ($d in @($RUNTIME, $PHPDIR, $DBDIR, $NGDIR, $NSSMDIR, $LOGDIR)) {
            New-Item -ItemType Directory -Force -Path $d | Out-Null
        }

        # ── 1. NSSM (gestor de servicios) ────────────────────────────
        Set-Progress 4 'Verificando NSSM...'
        $nssmExe = Get-ChildItem $NSSMDIR -Recurse -Filter nssm.exe -ErrorAction SilentlyContinue |
                   Where-Object { $_.Directory.Name -eq 'win64' } |
                   Select-Object -First 1 -ExpandProperty FullName
        if (-not $nssmExe) {
            $z = Join-Path $TMP 'ron-nssm.zip'
            Get-Download $URL_NSSM $z "NSSM $NSSM_VERSION"
            Expand-Zip $z $NSSMDIR
            $nssmExe = Get-ChildItem $NSSMDIR -Recurse -Filter nssm.exe |
                       Where-Object { $_.Directory.Name -eq 'win64' } |
                       Select-Object -First 1 -ExpandProperty FullName
        }
        if (-not $nssmExe) { throw "No se encontró nssm.exe en $NSSMDIR" }
        Write-Log "NSSM listo: $nssmExe" 'Green'

        # ── 2. PHP ───────────────────────────────────────────────────
        Set-Progress 8 'Verificando PHP...'
        $phpExe = "$PHPDIR\php.exe"
        $cgiExe = "$PHPDIR\php-cgi.exe"
        if (-not (Test-Path $phpExe)) {
            $z = Join-Path $TMP 'ron-php.zip'
            Get-Download $URL_PHP $z "PHP $PHP_VERSION NTS x64"
            Set-Progress 16 'Extrayendo PHP...'
            # El ZIP de PHP extrae directamente (sin carpeta raíz)
            if (Test-Path $PHPDIR) { Remove-Item "$PHPDIR\*" -Recurse -Force -ErrorAction SilentlyContinue }
            Expand-Archive -LiteralPath $z -DestinationPath $PHPDIR -Force
        }
        if (-not (Test-Path $phpExe)) { throw "php.exe no encontrado en $PHPDIR" }
        $phpVer = (& $phpExe -r 'echo PHP_VERSION;' 2>&1) -join ''
        Write-Log "PHP $phpVer en $PHPDIR" 'Green'

        # ── 3. php.ini ───────────────────────────────────────────────
        Set-Progress 22 'Configurando php.ini...'
        $iniPath = "$PHPDIR\php.ini"
        if (-not (Test-Path $iniPath)) {
            $prod = "$PHPDIR\php.ini-production"
            if (Test-Path $prod) { Copy-Item $prod $iniPath } else { Set-Content $iniPath '' -Encoding UTF8 }
        }
        [string]$ini = Get-Content $iniPath -Raw -Encoding UTF8

        # extension_dir
        $ini = $ini -replace '(?m)^;?\s*extension_dir\s*=.*$', 'extension_dir = "ext"'

        # Extensiones requeridas por CI4 + dompdf + phpspreadsheet
        foreach ($ext in @('curl','fileinfo','gd','intl','mbstring','mysqli',
                            'openssl','pdo_mysql','sodium','zip')) {
            if ($ini -match "(?m)^;extension=$ext\b") {
                $ini = $ini -replace "(?m)^;extension=$ext\b.*$", "extension=$ext"
            } elseif ($ini -notmatch "(?m)^extension=$ext\b") {
                $ini += "`r`nextension=$ext"
            }
        }

        # Configuración general
        $cfg = [ordered]@{
            'date.timezone'       = 'America/Lima'
            'upload_max_filesize' = '32M'
            'post_max_size'       = '32M'
            'memory_limit'        = '256M'
            'max_execution_time'  = '300'
            'display_errors'      = 'Off'
            'log_errors'          = 'On'
            'error_log'           = ($LOGDIR -replace '\\', '/') + '/php-error.log'
            'cgi.force_redirect'  = '0'
            'cgi.fix_pathinfo'    = '1'
        }
        foreach ($k in $cfg.Keys) {
            $v = $cfg[$k]; $e = [regex]::Escape($k)
            if ($ini -match "(?m)^;?\s*$e\s*=") { $ini = $ini -replace "(?m)^;?\s*$e\s*=.*$", "$k = $v" }
            else { $ini += "`r`n$k = $v" }
        }
        Set-Content $iniPath $ini -Encoding UTF8
        Write-Log 'php.ini configurado (extensiones + zona horaria).' 'Green'

        # ── 4. MariaDB ───────────────────────────────────────────────
        Set-Progress 28 'Verificando MariaDB...'
        $mysqld = "$DBDIR\bin\mariadbd.exe"
        if (-not (Test-Path $mysqld)) {
            $z = Join-Path $TMP 'ron-mariadb.zip'
            Get-Download $URL_MARIADB $z "MariaDB $MARIADB_VERSION LTS x64"
            Set-Progress 40 'Extrayendo MariaDB...'
            Expand-Zip $z $DBDIR
        }
        if (-not (Test-Path $mysqld)) { throw "mariadbd.exe no encontrado en $DBDIR\bin" }
        Write-Log "MariaDB en $DBDIR" 'Green'

        # ── 5. my.ini ────────────────────────────────────────────────
        Set-Progress 46 'Generando my.ini...'
        $myIni   = "$DBDIR\my.ini"
        $dataDir = "$DBDIR\data"
        $dbFwd   = ($DBDIR   -replace '\\', '/')
        $datFwd  = ($dataDir -replace '\\', '/')
        $logFwd  = ($LOGDIR  -replace '\\', '/')

        @"
[mysqld]
basedir               = $dbFwd
datadir               = $datFwd
port                  = 3306
bind-address          = 127.0.0.1
character-set-server  = utf8mb4
collation-server      = utf8mb4_unicode_ci
innodb_buffer_pool_size = 64M
max_connections       = 20
skip_name_resolve     = ON
log_error             = $logFwd/mariadb-error.log

[client]
port                  = 3306
default-character-set = utf8mb4
"@ | Set-Content $myIni -Encoding UTF8
        Write-Log 'my.ini generado.' 'Green'

        # ── 6. Inicializar directorio de datos ───────────────────────
        if (-not (Test-Path "$dataDir\mysql")) {
            Set-Progress 50 'Inicializando base de datos (primera vez)...'
            New-Item -ItemType Directory -Force -Path $dataDir | Out-Null
            $installDb = "$DBDIR\bin\mariadb-install-db.exe"
            if (Test-Path $installDb) {
                $initExe  = $installDb
                $initArgs = @("--datadir=`"$dataDir`"", "--basedir=`"$DBDIR`"")
            } else {
                $initExe  = $mysqld
                $initArgs = @('--initialize-insecure', "--datadir=`"$dataDir`"", "--defaults-file=`"$myIni`"")
            }
            $p = Start-Process $initExe -ArgumentList $initArgs -NoNewWindow -PassThru -Wait
            if ($p.ExitCode -ne 0) {
                throw "Inicialización de MariaDB falló (código $($p.ExitCode)). Revisa $LOGDIR\mariadb-error.log"
            }
            Write-Log 'Directorio de datos inicializado.' 'Green'
        } else {
            Write-Log 'Directorio de datos ya existe — omitiendo inicialización.' 'Yellow'
        }

        # ── 7. Servicio MariaDB ──────────────────────────────────────
        Set-Progress 55 "Registrando servicio '$SVC_MARIADB'..."
        Remove-WinSvc $SVC_MARIADB $null
        $p = Start-Process $mysqld `
            -ArgumentList "--defaults-file=`"$myIni`"", '--install-manual', $SVC_MARIADB `
            -NoNewWindow -PassThru -Wait
        if ($p.ExitCode -ne 0) { throw "No se pudo registrar el servicio MariaDB (código $($p.ExitCode))" }
        Start-Service $SVC_MARIADB
        Start-Sleep -Seconds 4
        if ((Get-Service $SVC_MARIADB).Status -ne 'Running') {
            throw "MariaDB no arrancó. Revisa $LOGDIR\mariadb-error.log"
        }
        Write-Log "Servicio '$SVC_MARIADB' iniciado." 'Green'

        # ── 8. Base de datos y usuario de la aplicación ──────────────
        Set-Progress 62 "Creando BD '$DB_NAME' y usuario '$DB_USER'..."
        $mariadbCli = "$DBDIR\bin\mariadb.exe"
        $tmpSql = [System.IO.Path]::GetTempFileName() + '.sql'
        @"
CREATE DATABASE IF NOT EXISTS ``$DB_NAME`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS';
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost'  IDENTIFIED BY '$DB_PASS';
GRANT ALL ON ``$DB_NAME``.* TO '$DB_USER'@'127.0.0.1';
GRANT ALL ON ``$DB_NAME``.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
"@ | Set-Content $tmpSql -Encoding UTF8
        $p = Start-Process $mariadbCli -ArgumentList '-u', 'root', "--execute=source $tmpSql" `
            -NoNewWindow -PassThru -Wait
        Remove-Item $tmpSql -Force -ErrorAction SilentlyContinue
        if ($p.ExitCode -ne 0) { throw "No se pudo crear la base de datos (código $($p.ExitCode))" }
        Write-Log "BD '$DB_NAME' y usuario '$DB_USER' listos." 'Green'

        # ── 9. Nginx ─────────────────────────────────────────────────
        Set-Progress 68 'Verificando Nginx...'
        $nginxExe = "$NGDIR\nginx.exe"
        if (-not (Test-Path $nginxExe)) {
            $z = Join-Path $TMP 'ron-nginx.zip'
            Get-Download $URL_NGINX $z "Nginx $NGINX_VERSION"
            Set-Progress 74 'Extrayendo Nginx...'
            Expand-Zip $z $NGDIR
        }
        if (-not (Test-Path $nginxExe)) { throw "nginx.exe no encontrado en $NGDIR" }
        Write-Log "Nginx en $NGDIR" 'Green'

        # ── 10. nginx.conf ────────────────────────────────────────────
        Set-Progress 78 'Generando nginx.conf...'
        $ngConf  = "$NGDIR\conf\nginx.conf"
        New-Item -ItemType Directory -Force -Path (Split-Path $ngConf) | Out-Null
        New-Item -ItemType Directory -Force -Path "$NGDIR\logs" | Out-Null
        $pubPath = (Join-Path $APP_DIR 'public') -replace '\\', '/'
        $ngLog   = ($NGDIR -replace '\\', '/') + '/logs'

        @"
worker_processes 1;
error_log  $ngLog/error.log warn;
pid        logs/nginx.pid;

events { worker_connections 256; }

http {
    include       mime.types;
    default_type  application/octet-stream;
    access_log    $ngLog/access.log;
    sendfile      on;
    keepalive_timeout 65;
    client_max_body_size 32M;

    server {
        listen      $WEB_PORT;
        server_name localhost;
        root        $pubPath;
        index       index.php index.html;
        charset     utf-8;

        location / {
            try_files `$uri `$uri/ /index.php`$is_args`$args;
        }

        location ~ \.php$ {
            fastcgi_pass  127.0.0.1:$CGI_PORT;
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
"@ | Set-Content $ngConf -Encoding ASCII

        # Copiar mime.types y fastcgi_params si no están en conf/
        foreach ($cfg2 in @('mime.types', 'fastcgi_params')) {
            $dst = "$NGDIR\conf\$cfg2"
            if (-not (Test-Path $dst)) {
                $src = Get-ChildItem $NGDIR -Recurse -Filter $cfg2 -ErrorAction SilentlyContinue |
                       Select-Object -First 1
                if ($src) { Copy-Item $src.FullName $dst }
            }
        }

        $ptest = Start-Process $nginxExe -ArgumentList '-p', $NGDIR, '-t' -NoNewWindow -PassThru -Wait
        if ($ptest.ExitCode -ne 0) { throw 'nginx.conf inválido — revisa la ruta de public/' }
        Write-Log 'nginx.conf generado y validado.' 'Green'

        # ── 11. Servicios PHP-CGI y Nginx vía NSSM ───────────────────
        Set-Progress 82 "Registrando servicio '$SVC_PHP'..."
        Remove-WinSvc $SVC_PHP $nssmExe
        & $nssmExe install $SVC_PHP $cgiExe "-b 127.0.0.1:$CGI_PORT" 2>&1 | Out-Null
        & $nssmExe set $SVC_PHP AppDirectory          $PHPDIR 2>&1 | Out-Null
        & $nssmExe set $SVC_PHP DisplayName           'Ronceros — PHP FastCGI' 2>&1 | Out-Null
        & $nssmExe set $SVC_PHP Start                 SERVICE_AUTO_START 2>&1 | Out-Null
        & $nssmExe set $SVC_PHP AppEnvironmentExtra   'PHP_FCGI_CHILDREN=4' 'PHP_FCGI_MAX_REQUESTS=1000' 2>&1 | Out-Null
        Start-Service $SVC_PHP
        Start-Sleep -Seconds 2
        Write-Log "Servicio '$SVC_PHP' registrado e iniciado." 'Green'

        Set-Progress 85 "Registrando servicio '$SVC_NGINX'..."
        Remove-WinSvc $SVC_NGINX $nssmExe
        & $nssmExe install $SVC_NGINX $nginxExe 2>&1 | Out-Null
        & $nssmExe set $SVC_NGINX AppDirectory  $NGDIR 2>&1 | Out-Null
        & $nssmExe set $SVC_NGINX DisplayName   'Ronceros — Nginx' 2>&1 | Out-Null
        & $nssmExe set $SVC_NGINX Start         SERVICE_AUTO_START 2>&1 | Out-Null
        & $nssmExe set $SVC_NGINX AppParameters "-p `"$NGDIR`"" 2>&1 | Out-Null
        Start-Service $SVC_NGINX
        Start-Sleep -Seconds 2
        Write-Log "Servicio '$SVC_NGINX' registrado e iniciado." 'Green'

        # ── 12. Agregar PHP al PATH del sistema ──────────────────────
        Set-Progress 87 'Actualizando PATH del sistema...'
        $syspath = [Environment]::GetEnvironmentVariable('Path', 'Machine') -as [string]
        if ($syspath -notlike "*$PHPDIR*") {
            [Environment]::SetEnvironmentVariable('Path', "$syspath;$PHPDIR", 'Machine')
            $env:Path = "$env:Path;$PHPDIR"
            Write-Log "PHP agregado al PATH: $PHPDIR" 'Green'
        } else {
            Write-Log 'PHP ya estaba en el PATH.' 'Dim'
        }

        # ── 13. Composer install ─────────────────────────────────────
        Set-Progress 88 'Instalando dependencias PHP (Composer)...'
        $vendorDir    = Join-Path $APP_DIR 'vendor'
        $composerPhar = Join-Path $PSScriptRoot 'composer.phar'
        if (-not (Test-Path "$vendorDir\autoload.php")) {
            if (-not (Test-Path $composerPhar)) {
                Get-Download 'https://getcomposer.org/composer-stable.phar' $composerPhar 'Composer'
            }
            Push-Location $APP_DIR
            try {
                $out = & $phpExe $composerPhar install --no-dev --optimize-autoloader --no-interaction 2>&1
                $out | ForEach-Object { Write-Log "  $_" 'Dim' }
                if ($LASTEXITCODE -ne 0) { throw 'composer install falló. Revisa el log.' }
            } finally { Pop-Location }
            Write-Log 'Dependencias instaladas.' 'Green'
        } else {
            Write-Log 'vendor/ ya existe — omitiendo composer install.' 'Dim'
        }

        # ── 14. Archivo .env ─────────────────────────────────────────
        Set-Progress 92 'Configurando .env...'
        $envFile = Join-Path $APP_DIR '.env'
        if (-not (Test-Path $envFile)) {
            $envEx = Join-Path $APP_DIR '.env.example'
            if (Test-Path $envEx) { Copy-Item $envEx $envFile } else { Set-Content $envFile '' -Encoding UTF8 }
        }
        [string]$envContent = Get-Content $envFile -Raw -Encoding UTF8

        Set-EnvKey ([ref]$envContent) 'CI_ENVIRONMENT'               'production'
        Set-EnvKey ([ref]$envContent) 'app.baseURL'                   "'http://localhost/'"
        Set-EnvKey ([ref]$envContent) 'database.default.hostname'     '127.0.0.1'
        Set-EnvKey ([ref]$envContent) 'database.default.database'     $DB_NAME
        Set-EnvKey ([ref]$envContent) 'database.default.username'     $DB_USER
        Set-EnvKey ([ref]$envContent) 'database.default.password'     $DB_PASS
        Set-EnvKey ([ref]$envContent) 'database.default.DBDriver'     'MySQLi'
        Set-EnvKey ([ref]$envContent) 'database.default.port'         '3306'

        Set-Content $envFile $envContent -Encoding UTF8
        Write-Log '.env configurado.' 'Green'

        # ── 15. Permisos en writable/ ────────────────────────────────
        Set-Progress 93 'Configurando permisos de writable/...'
        foreach ($sub in @('cache', 'logs', 'session', 'uploads', 'debugbar')) {
            New-Item -ItemType Directory -Force -Path (Join-Path $APP_DIR "writable\$sub") | Out-Null
        }
        $acl  = Get-Acl (Join-Path $APP_DIR 'writable')
        $rule = New-Object System.Security.AccessControl.FileSystemAccessRule(
            'Everyone', 'FullControl', 'ContainerInherit,ObjectInherit', 'None', 'Allow')
        $acl.SetAccessRule($rule)
        Set-Acl -Path (Join-Path $APP_DIR 'writable') -AclObject $acl
        Write-Log 'writable/ configurado con permisos correctos.' 'Green'

        # ── 16. Migraciones y seeders ────────────────────────────────
        Set-Progress 95 'Ejecutando migraciones...'
        Push-Location $APP_DIR
        try {
            $out = & $phpExe spark migrate --all 2>&1
            $out | ForEach-Object { Write-Log "  $_" 'Dim' }
            if ($LASTEXITCODE -ne 0) { throw 'Las migraciones fallaron. Revisa el log.' }
            Write-Log 'Migraciones aplicadas.' 'Green'

            Set-Progress 97 'Cargando datos de ejemplo (seeders)...'
            $out = & $phpExe spark db:seed DatabaseSeeder 2>&1
            $out | ForEach-Object { Write-Log "  $_" 'Dim' }
            if ($LASTEXITCODE -ne 0) {
                Write-Log 'Advertencia: seeders reportaron errores — revisa la BD.' 'Yellow'
            } else {
                Write-Log 'Datos de ejemplo cargados.' 'Green'
            }
        } finally { Pop-Location }

        # ── 17. Scripts INICIAR/DETENER y acceso directo ─────────────
        Set-Progress 99 'Creando accesos directos...'

        @"
@echo off
title Ronceros Fotografía — Iniciando...
net start $SVC_MARIADB >nul 2>&1
net start $SVC_PHP     >nul 2>&1
net start $SVC_NGINX   >nul 2>&1
timeout /t 2 /nobreak  >nul
start http://localhost
"@ | Set-Content (Join-Path $APP_DIR 'INICIAR.bat') -Encoding ASCII

        @"
@echo off
net stop $SVC_NGINX   >nul 2>&1
net stop $SVC_PHP     >nul 2>&1
net stop $SVC_MARIADB >nul 2>&1
echo Servicios detenidos.
pause
"@ | Set-Content (Join-Path $APP_DIR 'DETENER.bat') -Encoding ASCII

        $wsh = New-Object -ComObject WScript.Shell
        $lnk = $wsh.CreateShortcut(
            [Environment]::GetFolderPath('CommonDesktopDirectory') + '\Ronceros Fotografía.lnk')
        $lnk.TargetPath       = Join-Path $APP_DIR 'INICIAR.bat'
        $lnk.WorkingDirectory = $APP_DIR
        $lnk.Description      = 'Iniciar el sistema de gestión de Ronceros Fotografía'
        $lnk.Save()
        Write-Log 'Acceso directo creado en el escritorio.' 'Green'

        # ── Finalizado ────────────────────────────────────────────────
        Set-Progress 100 '¡Instalación completada exitosamente!'
        Write-Log ''
        Write-Log "  Sistema: http://localhost" 'Green'
        Write-Log "  Admin  : quique.admin / Admin1234!" 'Green'

        $btnClose.Text    = 'Abrir sistema'
        $btnClose.Enabled = $true
        $btnClose.Remove_Click.Invoke($btnClose.Click.GetInvocationList() | Select-Object -First 1) 2>$null
        Register-ObjectEvent -InputObject $btnClose -EventName 'Click' -Action {
            Start-Process 'http://localhost'; $form.Close()
        } | Out-Null

        [System.Windows.Forms.MessageBox]::Show(
            "Instalación completada.`n`n" +
            "El sistema está disponible en: http://localhost`n`n" +
            "Usuario administrador : quique.admin`n" +
            "Contraseña            : Admin1234!",
            'Instalación completa',
            [System.Windows.Forms.MessageBoxButtons]::OK,
            [System.Windows.Forms.MessageBoxIcon]::Information
        ) | Out-Null

    } catch {
        $err = "$_"
        Write-Log "ERROR: $err" 'Red'
        $lblStatus.Text = "Error — $err"
        [System.Windows.Forms.MessageBox]::Show(
            "Error durante la instalación:`n`n$err`n`nRevisa el registro para más detalles.",
            'Error de instalación',
            [System.Windows.Forms.MessageBoxButtons]::OK,
            [System.Windows.Forms.MessageBoxIcon]::Error
        ) | Out-Null
        # Rehabilitar controles para permitir reintentar
        foreach ($c in @($btnInstall, $btnClose, $btnDir, $txtRuntime,
                         $txtDbName, $txtDbUser, $txtDbPass)) {
            $c.Enabled = $true
        }
        $btnClose.Text = 'Cerrar'
    }
})

[System.Windows.Forms.Application]::Run($form)
