# Déploiement Payment Hub sur Hostinger

## Guide Complet - Configuration Spécifique Hostinger

### Étape 1 : Préparer les Fichiers

1. **Uploader les fichiers**
   - Via FTP ou File Manager de Hostinger
   - Destination : `public_html/payment-hub/`
   - Transférer TOUS les fichiers du projet Laravel

2. **Structure attendue**
   ```
   public_html/
   └── payment-hub/
       ├── app/
       ├── bootstrap/
       ├── config/
       ├── database/
       ├── public/          ← Important !
       ├── resources/
       ├── routes/
       ├── storage/
       ├── .env
       ├── artisan
       └── composer.json
   ```

### Étape 2 : Configurer le Document Root

**CRUCIAL :** Le serveur web doit pointer vers le dossier `/public`

1. Dans le panneau Hostinger :
   - Allez dans **Domaines** → Cliquez sur votre domaine → **Gérer**
   - Trouvez la section **Document Root**
   - Changez de `public_html` vers `public_html/payment-hub/public`
   - Sauvegardez

2. Vérification :
   - L'URL `https://votre-domaine.com` doit charger Laravel
   - Vous ne devriez PAS voir la liste des dossiers

### Étape 3 : Configurer PHP

1. **Version PHP**
   - Dans Hostinger : **Paramètres PHP**
   - Sélectionnez **PHP 8.1** ou supérieur
   - Recommandé : **PHP 8.2**

2. **Extensions PHP requises** (généralement déjà activées)
   - ✅ mbstring
   - ✅ xml
   - ✅ curl
   - ✅ mysql/mysqli
   - ✅ fileinfo
   - ✅ bcmath

### Étape 4 : Configurer la Base de Données

1. **Créer la base de données**
   - Dans Hostinger : **Bases de données MySQL**
   - Cliquez **Créer une nouvelle base de données**
   - Notez :
     - Nom de la BDD : `u123456_paymenthub`
     - Utilisateur : `u123456_admin`
     - Mot de passe : (généré ou personnalisé)
     - Hôte : `localhost` (ou fourni par Hostinger)

2. **Importer le schéma** (optionnel si vous utilisez les migrations)
   - Via phpMyAdmin ou le gestionnaire Hostinger
   - Ou utiliser SSH : `php artisan migrate`

### Étape 5 : Configurer le Fichier .env

1. **Via File Manager ou FTP**
   - Copiez `.env.example` vers `.env`
   - Éditez `.env` :

```env
APP_NAME="Payment Hub"
APP_ENV=production
APP_KEY=                        # ← Sera généré
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=localhost              # Ou adresse fournie par Hostinger
DB_PORT=3306
DB_DATABASE=u123456_paymenthub  # Votre nom de BDD
DB_USERNAME=u123456_admin       # Votre utilisateur
DB_PASSWORD=votre_mot_de_passe  # Votre mot de passe

PAYMENT_HUB_SECRET=VotreSecretPartageAvecPlugin123

ADMIN_EMAIL=votre-email@example.com

# Cache et sessions
CACHE_DRIVER=file
SESSION_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
```

### Étape 6 : Installation via SSH (Recommandé)

1. **Activer SSH** (si pas déjà fait)
   - Hostinger → Avancé → SSH Access → Activer

2. **Se connecter**
   ```bash
   ssh u123456@yoursite.com -p 65002
   cd public_html/payment-hub
   ```

3. **Installer les dépendances**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Générer la clé**
   ```bash
   php artisan key:generate
   ```

5. **Exécuter les migrations**
   ```bash
   php artisan migrate --force
   ```

6. **Créer le lien de stockage**
   ```bash
   php artisan storage:link
   ```

7. **Définir les permissions**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

8. **Mettre en cache la configuration**
   ```bash
   php artisan config:cache
   php artisan route:cache
   ```

### Étape 7 : Configuration Alternative (Sans SSH)

Si vous n'avez pas accès SSH :

1. **Installer Composer localement**
   - Exécutez `composer install` sur votre machine
   - Uploadez le dossier `vendor/` complet via FTP

2. **Générer APP_KEY**
   - Sur votre machine : `php artisan key:generate --show`
   - Copiez la clé générée dans `.env`

3. **Créer un script d'installation web** (temporaire !)
   - Créez `public/install.php` :

```php
<?php
// À SUPPRIMER APRÈS INSTALLATION !
$basePath = dirname(__DIR__);
require $basePath.'/vendor/autoload.php';
$app = require_once $basePath.'/bootstrap/app.php';

echo "<h1>Installation Payment Hub</h1>";

// Migrer
echo "<h2>Migrations</h2>";
Artisan::call('migrate', ['--force' => true]);
echo "<pre>".Artisan::output()."</pre>";

// Lien storage
echo "<h2>Storage Link</h2>";
Artisan::call('storage:link');
echo "<pre>".Artisan::output()."</pre>";

echo "<h2>✓ Installation terminée ! SUPPRIMEZ ce fichier maintenant.</h2>";
```

   - Visitez `https://votre-domaine.com/install.php`
   - **SUPPRIMEZ immédiatement** `public/install.php` après

### Étape 8 : Configurer SSL/HTTPS

1. **Activer SSL gratuit**
   - Dans Hostinger : Sécurité → SSL → Activer SSL
   - Attendre 5-15 minutes pour propagation

2. **Forcer HTTPS**
   - Déjà configuré dans `.htaccess`
   - Vérifiez que `APP_URL` dans `.env` commence par `https://`

### Étape 9 : Créer un Utilisateur Admin

**Via SSH :**
```bash
php artisan tinker
```

Puis dans Tinker :
```php
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('MotDePasseSecurise'),
    'is_admin' => true
]);
exit
```

**Ou créez un fichier temporaire** `public/create-admin.php` :
```php
<?php
// À SUPPRIMER APRÈS UTILISATION !
$basePath = dirname(__DIR__);
require $basePath.'/vendor/autoload.php';
$app = require_once $basePath.'/bootstrap/app.php';

\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('VotreMotDePasse'),
    'is_admin' => true
]);

echo "Admin créé ! SUPPRIMEZ ce fichier.";
```

### Étape 10 : Tester le Déploiement

1. **Test basique**
   - Visitez `https://votre-domaine.com`
   - Vous devriez être redirigé vers `/admin/login`

2. **Test API**
   - Exécutez depuis SSH : `php check-setup.php`
   - Ou visitez temporairement : `https://votre-domaine.com/api/ping` (devrait retourner erreur 403 - c'est normal sans signature)

3. **Consulter les logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Étape 11 : Configurer le Plugin WooCommerce

1. **Dans WooCommerce**
   - Réglages → Paiements → Payment Hub → Configurer

2. **Paramètres**
   - **URL Payment Hub** : `https://votre-domaine.com` (SANS /public !)
   - **Clé secrète** : La même valeur que `PAYMENT_HUB_SECRET` dans `.env`

3. **Tester la connexion**
   - Cliquez sur "Tester" dans les paramètres du plugin
   - Si succès : Communication OK
   - Si échec : Vérifier les logs Laravel

### Checklist Finale

- [ ] Document root pointe vers `/public`
- [ ] PHP 8.1+ activé
- [ ] Base de données créée et configurée
- [ ] `.env` configuré avec les bonnes valeurs
- [ ] `composer install` exécuté
- [ ] `APP_KEY` généré
- [ ] Migrations exécutées
- [ ] Permissions définies (775 sur storage et bootstrap/cache)
- [ ] SSL/HTTPS activé
- [ ] Utilisateur admin créé
- [ ] Plugin WooCommerce configuré avec URL et secret corrects
- [ ] Test API réussi

### Dépannage Hostinger

**Erreur "Internal Server Error"**
- Vérifiez `.htaccess` dans `/public`
- Vérifiez les logs : `storage/logs/laravel.log`
- Vérifiez PHP error log dans Hostinger

**Erreur "Base64::decode(): Argument #1"**
- Votre `APP_KEY` n'est pas définie
- Exécutez : `php artisan key:generate`

**Erreur 403**
- Document root incorrect → doit pointer vers `/public`
- Permissions incorrectes → `chmod -R 775 storage bootstrap/cache`

**"Class not found"**
- `composer install` pas exécuté
- Dossier `vendor/` manquant
- Autoloader pas à jour : `composer dump-autoload`

### Support Hostinger

Si problèmes persistent :
- Chat Hostinger (24/7)
- Base de connaissances : https://support.hostinger.com
- Tutoriels Laravel : https://support.hostinger.com/fr/articles/how-to-deploy-laravel