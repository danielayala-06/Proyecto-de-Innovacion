@echo off
REM ============================================================
REM ACTUALIZAR.bat — Comprueba y aplica actualizaciones
REM Ronceros Fotografía - Sistema de gestión
REM
REM Requiere conexión a internet.
REM ============================================================

title Ronceros Fotografía — Actualizador

net session >nul 2>&1
if %errorLevel% neq 0 (
    powershell -Command "Start-Process cmd -ArgumentList '/c \"%~f0\"' -Verb RunAs"
    exit /b
)

powershell.exe -ExecutionPolicy Bypass -NoProfile ^
    -File "%~dp0installer\actualizar-gui.ps1"
