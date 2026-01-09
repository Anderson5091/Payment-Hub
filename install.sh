#!/bin/bash

echo "=== Payment Hub Installation ==="

# Correction immédiate des fins de ligne (au cas où)
sed -i 's/\r$//' install.sh

# Get Domain from argument or prompt
DOMAIN=$1
if [ -z "$DOMAIN" ]; then
    read -p "Entrez votre domaine (ex: peye.smartdealz.tech): " DOMAIN
fi

# Configuration .env
if [ ! -f .env ]; then
  echo "Création du fichier .env..."
  cp .env.example .env
  
  # Configuration de base
  sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|" .env
  sed -i "s|ADMIN_EMAIL=.*|ADMIN_EMAIL=admin@$DOMAIN|" .env
  
  echo "⚠️ Veuillez configurer vos accès DB dans le fichier .env, "
  echo "Une fois fait, relancez ce script."
  exit 0
fi

# Création des dossiers nécessaires
echo "Création des dossiers de cache et storage..."
mkdir -p bootstrap/cache storage/framework/{sessions,views,cache} storage/logs
# Permissions
echo "Définition des permissions..."
chmod -R 775 storage bootstrap/cache

# Installation des dépendances
echo "Installation des dépendances Composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Générer la clé Laravel si vide
if ! grep -q "APP_KEY=base64" .env; then
    echo "Génération de la clé d'application..."
    php artisan key:generate
fi


# Génération automatique du secret Payment Hub si vide
if grep -q "PAYMENT_HUB_SECRET=CHANGE_ME_SECRET" .env; then
    echo "Génération de la clé secrète Payment Hub..."
    HUB_SECRET=$(php -r "echo bin2hex(random_bytes(32));")
    sed -i "s/PAYMENT_HUB_SECRET=.*/PAYMENT_HUB_SECRET=$HUB_SECRET/" .env
fi


# Migrations et seeders
echo "Exécution des migrations et seeders..."
php artisan migrate --seed --force


# Lien de stockage (Version compatible Hostinger)
echo "Création du lien de stockage (méthode native Linux)..."
rm -rf public/storage # Supprime un éventuel lien mort
ln -s ../storage/app/public public/storage


echo "=== Installation terminée ==="
echo "Domaine: https://$DOMAIN"
echo "Admin email: admin@$DOMAIN (Password: root)"

# Récupérer la clé secrète pour l'affichage
HUB_SECRET=$(grep "PAYMENT_HUB_SECRET=" .env | cut -d '=' -f2)
echo "Payment Hub Secret: $HUB_SECRET"
echo ""
echo "IMPORTANT: Veuillez installer et configurer le plugin 'wo_payment-hub' sur votre site WordPress"
echo "en utilisant la clé secrète ci-dessus."
echo "------------------------------------------------------------"
