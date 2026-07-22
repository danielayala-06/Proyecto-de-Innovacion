@echo off
:: ============================================================
:: INICIAR.bat — Ronceros Fotografía
:: Primera vez: instala todo automáticamente.
:: Siguientes veces: solo inicia los servicios.
:: ============================================================
title Ronceros Fotografía

:: Auto-elevar a Administrador si no lo somos
net session >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo Solicitando permisos de Administrador...
    powershell -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b
)

set "APP=%~dp0"
:: Quitar barra final (C:\RoncerosRuntime\app\ -> C:\RoncerosRuntime\app)
if "%APP:~-1%"=="\" set "APP=%APP:~0,-1%"

:: Verificar si ya está instalado: el servicio MariaDB existe y .env también
sc query Ronceros-MariaDB >nul 2>&1
set SVC_OK=%ERRORLEVEL%

if exist "%APP%\.env" (
    set ENV_OK=0
) else (
    set ENV_OK=1
)

if %SVC_OK%==0 if %ENV_OK%==0 (
    :: Ya instalado — solo arrancar
    echo.
    echo  Iniciando Ronceros Fotografía...
    powershell -ExecutionPolicy Bypass -WindowStyle Hidden -File "%APP%\scripts\start.ps1"
    echo.
    echo  Abriendo navegador en http://localhost/
    timeout /t 2 /nobreak >nul
    start http://localhost/
) else (
    :: Primera vez — instalación completa
    echo.
    echo  Primera ejecucion - Iniciando instalador...
    echo.
    powershell -ExecutionPolicy Bypass -File "%APP%\scripts\instalar-portable.ps1"
    echo.
    echo  Abriendo navegador en http://localhost/
    timeout /t 3 /nobreak >nul
    start http://localhost/
)

echo.
pause
