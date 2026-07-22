@echo off
REM ============================================================
REM COMPILAR.bat — Genera RoncerosFotografia-Setup.exe
REM
REM Opcion A (recomendada): Inno Setup 6
REM   1. Descarga: https://jrsoftware.org/isinfo.php
REM   2. Ejecuta este .bat o abre setup.iss con el compilador
REM
REM Opcion B: ps2exe (PowerShell, solo el instalador sin extractor)
REM   Genera un .exe que muestra la GUI directamente.
REM ============================================================

title Compilar instalador de Ronceros Fotografía
echo.
echo  Ronceros Fotografía — Compilador de instalador
echo  ================================================
echo.

REM ── Opcion A: Inno Setup ──────────────────────────────────────────────────
set ISCC=
if exist "%ProgramFiles(x86)%\Inno Setup 6\ISCC.exe" set ISCC="%ProgramFiles(x86)%\Inno Setup 6\ISCC.exe"
if exist "%ProgramFiles%\Inno Setup 6\ISCC.exe"       set ISCC="%ProgramFiles%\Inno Setup 6\ISCC.exe"

if defined ISCC (
    echo  [Inno Setup] Compilando setup.iss...
    %ISCC% "%~dp0setup.iss"
    if exist "%~dp0RoncerosFotografia-Setup.exe" (
        echo.
        echo  OK  Instalador creado: installer\RoncerosFotografia-Setup.exe
        goto :done
    ) else (
        echo  [ERROR] Inno Setup fallo. Revisa los errores de arriba.
        goto :fallback
    )
)

:fallback
REM ── Opcion B: ps2exe ──────────────────────────────────────────────────────
echo  Inno Setup no encontrado. Intentando ps2exe...
echo.
powershell -Command "if (-not (Get-Module -ListAvailable ps2exe)) { Install-Module ps2exe -Scope CurrentUser -Force }"
powershell -Command ^
    "Invoke-ps2exe '%~dp0instalar-gui.ps1' '%~dp0..\RoncerosFotografia-Instalador.exe' -noConsole -requireAdmin -title 'Ronceros Fotografía — Instalador' -description 'Instalador del sistema de gestión de Ronceros Fotografía'"

if exist "%~dp0..\RoncerosFotografia-Instalador.exe" (
    echo.
    echo  OK  Instalador creado: RoncerosFotografia-Instalador.exe
    echo  Nota: este .exe solo ejecuta la GUI del PS1; no incluye los archivos del proyecto.
    echo        Usa Inno Setup si necesitas un instalador todo-en-uno.
) else (
    echo.
    echo  [ERROR] No se pudo compilar el instalador.
    echo.
    echo  Opciones:
    echo    1. Instala Inno Setup 6 desde https://jrsoftware.org/isinfo.php
    echo       y abre installer\setup.iss con el compilador.
    echo.
    echo    2. Distribuye la carpeta completa del proyecto y pide al usuario
    echo       que ejecute installer\INSTALAR.bat (no requiere compilacion).
)

:done
echo.
pause
