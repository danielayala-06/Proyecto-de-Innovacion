; ============================================================
; setup.iss — Inno Setup 6 script
; Ronceros Fotografía — Sistema de gestión
;
; Cómo compilar:
;   1. Instala Inno Setup 6: https://jrsoftware.org/isinfo.php
;   2. Abre este archivo con Inno Setup Compiler
;   3. Presiona F9 (Build > Compile)
;   Salida: installer\RoncerosFotografia-Setup.exe
;
; Alternativa rápida (sin .exe, solo PS1):
;   Usa installer\INSTALAR.bat directamente en el equipo de destino.
; ============================================================

#define AppName    "Ronceros Fotografía"
#define AppVersion "1.0"
#define AppDir     "C:\RoncerosRuntime\app"

[Setup]
AppId               = {{8F3C2A1D-7E4B-4F91-B6D2-0A5C3E8F1294}
AppName             = {#AppName}
AppVersion          = {#AppVersion}
AppPublisher        = Ronceros Fotografía
DefaultDirName      = {#AppDir}
DefaultGroupName    = {#AppName}
OutputDir           = .
OutputBaseFilename  = RoncerosFotografia-Setup
Compression         = lzma2/ultra64
SolidCompression    = yes
PrivilegesRequired  = admin
WizardStyle         = modern
MinVersion          = 10.0.17763
UninstallDisplayName= {#AppName}
; El desinstalador solo borra los archivos del app, no los servicios ni la BD.
; Para desinstalar los servicios usa DETENER.bat + sc delete manualmente.

[Languages]
Name: "es"; MessagesFile: "compiler:Languages\Spanish.isl"

[CustomMessages]
es.InstallDesc=Instalar PHP, MariaDB y Nginx como servicios de Windows y configurar la aplicación.

[Dirs]
Name: "{app}\writable\cache"
Name: "{app}\writable\logs"
Name: "{app}\writable\session"
Name: "{app}\writable\uploads"
Name: "{app}\writable\debugbar"

[Files]
; ── Archivos del proyecto (sin vendor/, .env, .git, archivos compilados) ──
Source: "..\*";             DestDir: "{app}";           Flags: recursesubdirs ignoreversion; \
    Excludes: ".git,vendor,writable\cache\*,writable\logs\*,writable\session\*,writable\debugbar\*,.env,installer\*.exe,installer\COMPILAR.bat"

; composer.phar siempre incluido (lo usa el PS1 durante la instalación)
Source: "composer.phar";    DestDir: "{app}\installer"; Flags: ignoreversion

[Run]
; Al terminar el asistente, ofrece lanzar el instalador de servicios
Filename: "powershell.exe"; \
    Parameters: "-ExecutionPolicy Bypass -NoProfile -File ""{app}\installer\instalar-gui.ps1"" -RutaRuntime ""{code:GetRuntime}"""; \
    Description: "Instalar servicios (PHP, MariaDB, Nginx) y configurar la aplicación"; \
    Flags: postinstall nowait; \
    StatusMsg: "Iniciando instalador de servicios..."

[Code]
{ Calcula el directorio de runtime (padre del directorio de la app) }
function GetRuntime(Param: String): String;
var
  AppPath: String;
begin
  AppPath := ExpandConstant('{app}');
  Result  := ExtractFileDir(AppPath);
  if Result = '' then
    Result := 'C:\RoncerosRuntime';
end;

procedure CurStepChanged(CurStep: TSetupStep);
begin
  if CurStep = ssInstall then begin
    Log('Extrayendo archivos de Ronceros Fotografía...');
  end;
end;
