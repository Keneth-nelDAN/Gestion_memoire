#!/bin/bash

# Script de test rapide de la configuration

echo "=========================================="
echo "TEST RAPIDE - GénieMémoires"
echo "=========================================="
echo ""

# Test 1: Vérifier la syntaxe PHP
echo "1️⃣  Vérification syntaxe PHP..."
ERROR_COUNT=$(find /opt/lampp/htdocs/Gestion_memoire -type f -name "*.php" -exec php -l {} \; 2>&1 | grep -E "(Parse error|Syntax error)" | wc -l)
if [ $ERROR_COUNT -eq 0 ]; then
    echo "   ✅ Aucune erreur de syntaxe PHP"
else
    echo "   ❌ $ERROR_COUNT erreur(s) de syntaxe détectée(s)"
fi
echo ""

# Test 2: Vérifier les fichiers importants
echo "2️⃣  Vérification des fichiers critiques..."
CRITICAL_FILES=(
    "/opt/lampp/htdocs/Gestion_memoire/public/login.php"
    "/opt/lampp/htdocs/Gestion_memoire/app/views/auth/connexion.php"
    "/opt/lampp/htdocs/Gestion_memoire/app/views/direction_etude/dashboard_de.php"
    "/opt/lampp/htdocs/Gestion_memoire/app/views/direction_etude/publications.php"
    "/opt/lampp/htdocs/Gestion_memoire/app/views/professeur/dashboard_professeur.php"
    "/opt/lampp/htdocs/Gestion_memoire/app/views/etudiant/dashboard_etudiant.php"
    "/opt/lampp/htdocs/Gestion_memoire/config/mysqli_config.php"
)

for file in "${CRITICAL_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "   ✅ $(basename $file)"
    else
        echo "   ❌ $(basename $file) - MANQUANT"
    fi
done
echo ""

# Test 3: Vérifier la structure des dossiers
echo "3️⃣  Vérification structure des dossiers..."
FOLDERS=(
    "/opt/lampp/htdocs/Gestion_memoire/app/views/direction_etude"
    "/opt/lampp/htdocs/Gestion_memoire/app/views/professeur"
    "/opt/lampp/htdocs/Gestion_memoire/app/views/etudiant"
    "/opt/lampp/htdocs/Gestion_memoire/app/controllers"
    "/opt/lampp/htdocs/Gestion_memoire/config"
)

for folder in "${FOLDERS[@]}"; do
    if [ -d "$folder" ]; then
        FILE_COUNT=$(ls -1 "$folder"/*.php 2>/dev/null | wc -l)
        echo "   ✅ $(basename $folder) - $FILE_COUNT fichiers"
    else
        echo "   ❌ $(basename $folder) - DOSSIER MANQUANT"
    fi
done
echo ""

echo "=========================================="
echo "✅ TESTS COMPLÉTÉS"
echo "=========================================="
echo ""
echo "👉 Prochaines étapes:"
echo "   1. Lancer XAMPP: sudo /opt/lampp/xampp start"
echo "   2. Vérifier la connexion à MySQL"
echo "   3. Visiter: http://localhost/Gestion_memoire/app/views/auth/connexion.php"
echo ""
