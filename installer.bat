@echo off
title SanteAI - Installation

echo.
echo  Création du lien de jonction vers XAMPP...
echo.

REM Crée un lien de jonction de htdocs/santeai vers ce dossier (ne requiert pas de droits administrateur)
mklink /J "C:\xampp\htdocs\santeai" "%~dp0"

if %errorlevel%==0 (
    echo.
    echo  [OK] Lien de jonction créé avec succès.
    echo.
    echo  Ouvre : http://localhost/santeai/
    echo.
    start "" "http://localhost/santeai/"
) else (
    echo.
    echo  [!] Impossible de créer le lien. Il existe peut-être déjà.
    echo      Essaye de supprimer C:\xampp\htdocs\santeai et relancer.
    echo.
)

pause

