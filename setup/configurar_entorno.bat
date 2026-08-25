@echo off
chcp 65001 >nul
setlocal EnableExtensions EnableDelayedExpansion
title Mi Botica - Configurar Entorno de Respaldos

echo ============================================================
echo   MI BOTICA - Configurar Entorno de Respaldos
echo ============================================================
echo.
echo Este script prepara tu computadora para que el sistema pueda
echo crear copias de seguridad automaticas ANTES de usar la opcion
echo "Restablecer a Estado de Fabrica".
echo.
echo No necesitas saber nada tecnico: solo espera a que termine.
echo.

rem --- Carpeta raiz del proyecto (un nivel arriba de esta carpeta setup\) ---
set "RAIZ=%~dp0.."
for %%I in ("%RAIZ%") do set "RAIZ=%%~fI"

echo [1/4] Buscando mysqldump.exe en tu computadora...
echo.

set "MYSQLDUMP="

rem --- Ubicaciones mas comunes en Laragon: busca cualquier version instalada ---
for /d %%D in ("C:\laragon\bin\mysql\*") do (
    if exist "%%D\bin\mysqldump.exe" set "MYSQLDUMP=%%D\bin\mysqldump.exe"
)
for /d %%D in ("C:\laragon\bin\mariadb\*") do (
    if exist "%%D\bin\mysqldump.exe" set "MYSQLDUMP=%%D\bin\mysqldump.exe"
)

rem --- XAMPP ---
if not defined MYSQLDUMP (
    if exist "C:\xampp\mysql\bin\mysqldump.exe" set "MYSQLDUMP=C:\xampp\mysql\bin\mysqldump.exe"
)

rem --- WAMP: cualquier version ---
if not defined MYSQLDUMP (
    for /d %%D in ("C:\wamp64\bin\mysql\*") do (
        if exist "%%D\bin\mysqldump.exe" set "MYSQLDUMP=%%D\bin\mysqldump.exe"
    )
)

rem --- MySQL Server instalado directamente en Program Files ---
if not defined MYSQLDUMP (
    for /d %%D in ("C:\Program Files\MySQL\*") do (
        if exist "%%D\bin\mysqldump.exe" set "MYSQLDUMP=%%D\bin\mysqldump.exe"
    )
)

rem --- Ultimo intento: mysqldump ya disponible en el PATH del sistema ---
if not defined MYSQLDUMP (
    where mysqldump.exe >nul 2>nul
    if not errorlevel 1 (
        for /f "delims=" %%P in ('where mysqldump.exe') do set "MYSQLDUMP=%%P"
    )
)

if defined MYSQLDUMP (
    echo   Encontrado: !MYSQLDUMP!
) else (
    echo   No se encontro mysqldump.exe en las ubicaciones habituales.
    echo   No hay problema: el sistema usara su metodo alterno de respaldo
    echo   en PHP, que no necesita mysqldump para funcionar.
)
echo.

echo [2/4] Guardando la ruta encontrada para que el sistema la use...
if not exist "%RAIZ%\app\config" mkdir "%RAIZ%\app\config" >nul 2>nul
if defined MYSQLDUMP (
    > "%RAIZ%\app\config\mysqldump_path.txt" echo !MYSQLDUMP!
    echo   Listo: app\config\mysqldump_path.txt actualizado.
) else (
    echo   Nada que guardar: no se encontro mysqldump.exe.
)
echo.

echo [3/4] Creando la carpeta de respaldos si no existe...
if not exist "%RAIZ%\app\backups" mkdir "%RAIZ%\app\backups" >nul 2>nul
if exist "%RAIZ%\app\backups" (
    echo   Carpeta lista: app\backups\
) else (
    echo   ADVERTENCIA: no se pudo crear la carpeta app\backups\
    echo   Intenta ejecutar este script como Administrador.
)
echo.

echo [4/4] Dando permisos de escritura a las carpetas necesarias...
echo       Esto evita que Windows bloquee la creacion de backups.
icacls "%RAIZ%\app\backups" /grant "Users:(OI)(CI)F" /T >nul 2>nul
icacls "%RAIZ%\app\config"  /grant "Users:(OI)(CI)F" /T >nul 2>nul
icacls "%RAIZ%\storage"     /grant "Users:(OI)(CI)F" /T >nul 2>nul
echo   Permisos aplicados.
echo.

echo ============================================================
echo   LISTO. Tu entorno ya esta configurado.
echo ============================================================
echo.
echo   Ya puedes usar con confianza la opcion "Restablecer a Estado
echo   de Fabrica" dentro de Configuracion. El sistema creara un
echo   respaldo automatico antes de borrar cualquier dato.
echo.
echo   Si tienes dudas, abre el archivo:
echo   MANUAL_RESPALDO_Y_RESETEO.md - carpeta principal del sistema.
echo.
echo ============================================================
echo.
pause
