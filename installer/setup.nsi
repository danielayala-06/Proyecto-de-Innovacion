; ============================================================
; setup.nsi — Script NSIS para compilar en Linux
; Ronceros Fotografía - Sistema de gestión
;
; Compilar desde Linux (en la raíz del proyecto):
;   sudo apt install nsis
;   makensis installer/setup.nsi
;
; Salida: RoncerosFotografia-Setup.exe  (en la raíz del proyecto)
; ============================================================

Unicode true
SetCompressor /SOLID lzma

; ── Constantes ───────────────────────────────────────────────────────────────
!define APPNAME    "Ronceros Fotografía"
!define APPVERSION "1.0"
!define APPDEFDIR  "C:\RoncerosRuntime\app"
!define REGKEY     "Software\RoncerosFotografia"
!define PUBLISHER  "Ronceros Fotografía"

; ── Configuración general ─────────────────────────────────────────────────────
Name                   "${APPNAME} ${APPVERSION}"
OutFile                "..\RoncerosFotografia-Setup.exe"
InstallDir             "${APPDEFDIR}"
InstallDirRegKey HKLM  "${REGKEY}" "InstallDir"
RequestExecutionLevel  admin
ShowInstDetails        show
ShowUninstDetails      show
BrandingText           "${APPNAME} — Instalador v${APPVERSION}"

; ── Modern UI ────────────────────────────────────────────────────────────────
!include "MUI2.nsh"

!define MUI_ABORTWARNING
!define MUI_ICON   "${NSISDIR}\Contrib\Graphics\Icons\modern-install.ico"
!define MUI_UNICON "${NSISDIR}\Contrib\Graphics\Icons\modern-uninstall.ico"

; Páginas del asistente
!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_DIRECTORY
!insertmacro MUI_PAGE_INSTFILES

; Página final: ofrece lanzar el instalador de servicios
!define MUI_FINISHPAGE_TITLE         "Instalación de archivos completa"
!define MUI_FINISHPAGE_TEXT          "Los archivos de ${APPNAME} se instalaron en $INSTDIR.$\n$\nA continuación, el asistente de servicios instalará PHP, MariaDB y Nginx y configurará la aplicación.$\n$\nMarca la casilla y haz clic en Terminar para continuar."
!define MUI_FINISHPAGE_RUN           "$WINDIR\System32\WindowsPowerShell\v1.0\powershell.exe"
!define MUI_FINISHPAGE_RUN_PARAMETERS "-ExecutionPolicy Bypass -NoProfile -File $\"$INSTDIR\installer\instalar-gui.ps1$\""
!define MUI_FINISHPAGE_RUN_TEXT      "Instalar servicios (PHP, MariaDB, Nginx)"

!insertmacro MUI_PAGE_FINISH

; Páginas del desinstalador
!insertmacro MUI_UNPAGE_CONFIRM
!insertmacro MUI_UNPAGE_INSTFILES

!insertmacro MUI_LANGUAGE "Spanish"

; ── SECCIÓN PRINCIPAL ────────────────────────────────────────────────────────
Section "Ronceros Fotografía" SecMain
    SectionIn RO

    ; Archivos raíz del proyecto
    SetOutPath "$INSTDIR"
    File "..\spark"
    File "..\composer.json"
    File "..\composer.lock"
    File "..\preload.php"
    File "..\env"
    File "..\version.txt"
    File "..\ACTUALIZAR.bat"

    ; Código de la aplicación CodeIgniter 4
    SetOutPath "$INSTDIR\app"
    File /r "..\app\*"

    ; Webroot — lo que sirve Nginx
    SetOutPath "$INSTDIR\public"
    File /r "..\public\*"

    ; Scripts de inicio y parada
    SetOutPath "$INSTDIR\scripts"
    File "..\scripts\start.ps1"
    File "..\scripts\stop.ps1"
    File "..\scripts\instalar-portable.ps1"

    ; Archivos del instalador y actualizador
    SetOutPath "$INSTDIR\installer"
    File "composer.phar"
    File "instalar-gui.ps1"
    File "actualizar-gui.ps1"
    File "INSTALAR.bat"

    ; Directorios de runtime vacíos (PHP/Nginx los necesita con permisos de escritura)
    CreateDirectory "$INSTDIR\writable\cache"
    CreateDirectory "$INSTDIR\writable\logs"
    CreateDirectory "$INSTDIR\writable\session"
    CreateDirectory "$INSTDIR\writable\uploads"
    CreateDirectory "$INSTDIR\writable\debugbar"

    ; Registrar desinstalador
    WriteRegStr   HKLM "${REGKEY}" "InstallDir"      "$INSTDIR"
    WriteRegStr   HKLM "${REGKEY}" "Publisher"       "${PUBLISHER}"
    WriteRegStr   HKLM "${REGKEY}" "DisplayName"     "${APPNAME}"
    WriteRegStr   HKLM "${REGKEY}" "DisplayVersion"  "${APPVERSION}"
    WriteRegStr   HKLM "${REGKEY}" "UninstallString" "$INSTDIR\Desinstalar.exe"
    WriteRegDWORD HKLM "${REGKEY}" "NoModify"        1
    WriteRegDWORD HKLM "${REGKEY}" "NoRepair"        1
    WriteUninstaller "$INSTDIR\Desinstalar.exe"

SectionEnd

; ── DESINSTALACIÓN ───────────────────────────────────────────────────────────
; Solo elimina archivos del proyecto. Los servicios y la BD son independientes.
Section "Uninstall"

    ; Detener servicios antes de borrar archivos
    ExecWait 'net stop Ronceros-Nginx   /y'
    ExecWait 'net stop Ronceros-PHP     /y'
    ExecWait 'net stop Ronceros-MariaDB /y'

    ; Borrar directorios del proyecto
    RMDir /r "$INSTDIR\app"
    RMDir /r "$INSTDIR\public"
    RMDir /r "$INSTDIR\scripts"
    RMDir /r "$INSTDIR\installer"
    RMDir /r "$INSTDIR\vendor"
    RMDir /r "$INSTDIR\writable"

    ; Borrar archivos raíz
    Delete "$INSTDIR\spark"
    Delete "$INSTDIR\composer.json"
    Delete "$INSTDIR\composer.lock"
    Delete "$INSTDIR\preload.php"
    Delete "$INSTDIR\env"
    Delete "$INSTDIR\.env"
    Delete "$INSTDIR\INICIAR.bat"
    Delete "$INSTDIR\DETENER.bat"
    Delete "$INSTDIR\Desinstalar.exe"

    RMDir  "$INSTDIR"

    ; Limpiar registro
    DeleteRegKey HKLM "${REGKEY}"

SectionEnd
