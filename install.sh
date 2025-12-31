#!/bin/bash

echo "=== Payment Hub Installation ==="

# Vérification PHP
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "PHP version: $PHP_VERSION"
if [[ $(php -r "echo version_compare(PHP_VERSION, '8.1.0', '>=');") -ne 1 ]]; then
  echo "PHP 8.1 ou supérieur requis."
  exit 1
fi

# Vérifier composer
if ! command -v composer &> /dev/null; then
    echo "Composer non trouvé. Veuillez installer Composer."
    exit 1
fi

# Installer les dépendances
echo "Installation des dépendances Composer..."
composer install --no-interaction --prefer-dist

# Copier le fichier .env
if [ ! -f .env ]; then
  echo "Création du fichier .env..."
  cp .env.example .env
fi

# Générer la clé Laravel
echo "Génération de la clé d'application..."
php artisan key:generate

# Vérifier la base de données
read -p "Base de données nom: " DB_NAME
read -p "Utilisateur DB: " DB_USER
read -s -p "Mot de passe DB: " DB_PASS
echo ""

sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

# Migrations et seeders
echo "Exécution des migrations et seeders..."
php artisan migrate --seed

# Lien de stockage pour les preuves
echo "Création du lien de stockage pour les preuves..."
php artisan storage:link

# Permissions
echo "Définition des permissions pour storage et bootstrap/cache..."
chmod -R 775 storage bootstrap/cache

# Vérification installation
echo "Vérification de l'installation..."
php artisan route:list

echo "=== Installation terminée ==="
echo "Connectez-vous avec l'admin par défaut défini dans les seeders."
