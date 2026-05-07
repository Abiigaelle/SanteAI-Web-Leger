@echo off
title SanteAI - Installation

echo.
echo  Création du lien vers XAMPP...
echo.

REM Crée un lien symbolique de htdocs/santeai vers ce dossier
mklink /D "C:\xampp\htdocs\santeai" "%~dp0"

if %errorlevel%==0 (
    echo.
    echo  [OK] Lien créé.
    echo.
    echo  Ouvre : http://localhost/santeai/
    echo.
    start "" "http://localhost/santeai/"
) else (
    echo.
    echo  [!] Lien déjà existant ou erreur.
    echo      Essaye de supprimer C:\xampp\htdocs\santeai\ et relancer.
    echo.
)

pause
