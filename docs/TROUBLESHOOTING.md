# Payment Hub - Guide de Dépannage

## Problème : Erreur 403 sur le Hub

### Solution Rapide

Le `.htaccess` à la racine redirige automatiquement vers `/public`. Si vous avez une erreur 403 :

**Voir le guide détaillé :** [FIX_403_ERROR.md](./docs/FIX_403_ERROR.md)

### Solution Recommandée

**Modifier le Document Root** (meilleure option) :
1. Hostinger → Domaines → Gérer
2. Document Root → `public_html/payment-hub/public`
3. Sauvegarder et attendre 5 minutes

### Si Modification Impossible

Le `.htaccess` devrait gérer la redirection. Si problème :
```bash
# Vérifier les permissions
chmod 644 .htaccess
chmod -R 775 storage bootstrap/cache

# Test du .htaccess
curl -I https://votre-domaine.com
```

## Problème : Plugin ne peut pas communiquer avec le Hub

### Causes Possibles

1. **Secret HMAC différent**
   - Dans `.env` du Hub : `PAYMENT_HUB_SECRET=votre_secret`
   - Dans WooCommerce plugin : utiliser EXACTEMENT le même secret
   - Le script `install.sh` génère ce secret automatiquement

2. **URL du Hub incorrecte dans le plugin**
   - Dans WooCommerce → Réglages → Paiements → Payment Hub
   - URL doit être : `https://votre-domaine.com` (SANS /public à la fin)

3. **Vérifier les logs Laravel**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Recherchez "Payment Init Request" pour voir les données reçues

## Tests de Diagnostic

### Script de vérification automatique
```bash
php check-setup.php
```

### Vérifier la communication API
```bash
# Dans WooCommerce, cliquez sur "Tester" dans les paramètres du plugin
# Ou testez manuellement l'endpoint ping
```

## Configuration Hostinger

### Étapes Recommandées

1. **Pointer le domaine vers /public**
   - Panneau Hostinger → Domaines → Gérer
   - Document Root : `public_html/payment-hub/public`

2. **Activer HTTPS**
   - Utilisez le SSL gratuit de Hostinger
   - HTTPS est déjà forcé dans `.htaccess`

3. **PHP Version**
   - Recommandé : PHP 8.1 ou supérieur

## Commandes Utiles

```bash
# Vider les caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Mettre en cache (production)
php artisan config:cache
php artisan route:cache

# Créer un utilisateur admin
php artisan tinker
>>> \App\Models\User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>bcrypt('password'),'is_admin'=>true]);

# Vérifier les routes
php artisan route:list
```

## Checklist Finale

- [ ] `.env` existe avec APP_KEY, PAYMENT_HUB_SECRET, DB credentials
- [ ] `composer install` exécuté
- [ ] `php artisan migrate` exécuté
- [ ] Permissions 775 sur `storage/` et `bootstrap/cache/`
- [ ] Même `PAYMENT_HUB_SECRET` dans Laravel et plugin WooCommerce
- [ ] URL du Hub correcte dans plugin (sans /public)
- [ ] SSL/HTTPS activé
- [ ] Document root pointe vers `/public` (recommandé)