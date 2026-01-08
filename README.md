# Payment Hub

## Description

Payment Hub est un système sécurisé de gestion des paiements manuels (Mobile Money et virements bancaires) pour les boutiques WooCommerce. Il permet de centraliser, valider et synchroniser les paiements via API sécurisée.

---

## Pré-requis

* Serveur Linux (Ubuntu/Debian recommandé)
* PHP >= 8.1
* MySQL / MariaDB
* Composer
* Apache ou Nginx
* Accès SSH

Extensions PHP nécessaires :

* php-mbstring
* php-xml
* php-curl
* php-mysql
* php-fileinfo
* php-bcmath

---

## Installation

1. Cloner le dépôt :

```bash
git clone https://github.com/your-org/payment-hub.git
cd payment-hub
```

2. Exécuter le script d'installation :

```bash
chmod +x install.sh
./install.sh
```

Le script :

* vérifie PHP et Composer
* installe les dépendances
* crée le fichier `.env`
* génère la clé Laravel
* exécute les migrations et seeders
* crée le lien de stockage pour les preuves
* définit les permissions

3. Accéder au Payment Hub via le navigateur :

```
http://votre-domaine.com
```

---

## Configuration `.env`

Les variables principales :

```env
APP_NAME=PaymentHub
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=paymenthub
DB_USERNAME=root
DB_PASSWORD=

PAYMENT_HUB_SECRET=change_this_secret_key
```

---

## Utilisation

* Les wallets destination par défaut sont configurés via le dashboard admin.
* Les banques locales sont configurées dans le hub et affichées pour les virements.
* Les clients sont redirigés depuis WooCommerce avec un token sécurisé.
* L’admin valide ou rejette les paiements via le dashboard.
* Les paiements validés déclenchent un callback sécurisé vers WooCommerce.

---

## Sécurité

* HMAC SHA256 pour toutes les communications API
* Fichiers de preuves stockés dans `storage/app/private/proofs`
* Permissions sécurisées pour `storage` et `bootstrap/cache`
* Données sensibles non stockées sur la boutique WooCommerce

---

## Mise à jour

* Pull du dépôt
* `composer install` si nouvelles dépendances
* `php artisan migrate` si nouvelles migrations
* Vérifier permissions

---

## Support

Pour toute question, créez un ticket GitHub ou contactez l’équipe technique.

