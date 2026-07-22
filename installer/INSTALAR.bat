@echo off
REM ============================================================
REM INSTALAR.bat — Lanzador del instalador gráfico
REM Ronceros Fotografía - Sistema de gestión
REM
REM Uso: doble clic sobre este archivo
REM Requiere: Windows 10+ y conexión a internet
REM ============================================================

title Ronceros Fotografía — Instalador

REM Elevar a Administrador si no lo es
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Solicitando permisos de administrador...
    powershell -Command "Start-Process cmd -ArgumentList '/c \"%~f0\"' -Verb RunAs"
    exit /b
)

echo.
echo  Ronceros Fotografía — Instalador
echo  ==================================
echo  Iniciando interfaz grafica...
echo.

powershell.exe -ExecutionPolicy Bypass -NoProfile ^
    -File "%~dp0instalar-gui.ps1"

if %errorLevel% neq 0 (
    echo.
    echo  [ERROR] El instalador cerro con un error.
    echo  Revisa el registro dentro de la ventana del instalador.
    pause
)
