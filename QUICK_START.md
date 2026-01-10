# Payment Hub - Quick Start Guide

## Installation en 3 Étapes

### Étape 1 : Déployer le Code

**Via Hostinger File Manager ou FTP :**
- Uploadez tous les fichiers dans `public_html/payment-hub/`

**Ou via Git (recommandé) :**
```bash
# SSH dans Hostinger
cd public_html
git clone https://votre-repo-git.git payment-hub
cd payment-hub
```

### Étape 2 : Lancer l'Installation

```bash
# Via SSH
chmod +x install.sh
./install.sh votre-domaine.com
```

**Première exécution :**
- Crée le fichier `.env`
- Configure APP_URL avec votre domaine

**Éditez `.env` et ajoutez vos accès DB :**
```env
DB_DATABASE=votre_base
DB_USERNAME=votre_user
DB_PASSWORD=votre_password
```

**Relancez le script :**
```bash
./install.sh
```

Le script va :
- ✅ Installer les dépendances Composer
- ✅ Générer APP_KEY automatiquement
- ✅ Générer PAYMENT_HUB_SECRET automatiquement
- ✅ Créer les tables de la base de données
- ✅ Créer un utilisateur admin (email: `admin@votre-domaine.com`, password: `root`)

### Étape 3 : Configurer le Serveur

**Option A - Recommandée : Modifier Document Root**
1. Hostinger → Domaines → Gérer
2. Document Root → `public_html/payment-hub/public`
3. Sauvegarder

**Option B - Sans Modifier Document Root**
- Le `.htaccess` à la racine redirige automatiquement vers `/public`
- Si erreur 403 → Voir [FIX_403_ERROR.md](./FIX_403_ERROR.md)

## Configuration WooCommerce

1. **Installer le plugin** `woo-payment-hub.zip`
2. **WooCommerce → Réglages → Paiements → Payment Hub**
3. **Configurer :**
   - URL Payment Hub : `https://votre-domaine.com`
   - Clé secrète : Copiez le `PAYMENT_HUB_SECRET` depuis `.env`

## Test

1. **Accédez au Hub :** `https://votre-domaine.com`
   - Devrait rediriger vers `/admin/login`
   
2. **Connectez-vous :**
   - Email : `admin@votre-domaine.com`
   - Password : `root`

3. **Test depuis WooCommerce :**
   - Dans les paramètres du plugin, cliquez "Tester"
   - Devrait afficher "Connexion OK"

## Dépannage

- **Erreur 403** → Voir [FIX_403_ERROR.md](./FIX_403_ERROR.md)
- **Erreur de communication plugin** → Vérifiez que `PAYMENT_HUB_SECRET` est identique dans `.env` et plugin
- **Problème DB** → Vérifiez les accès dans `.env` avec phpMyAdmin

## Support

- **Documentation complète :** [HOSTINGER_DEPLOYMENT.md](./HOSTINGER_DEPLOYMENT.md)
- **Dépannage :** [TROUBLESHOOTING.md](./TROUBLESHOOTING.md)
- **Script diagnostic :** `php check-setup.php`