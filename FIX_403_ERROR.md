# Solution pour l'Erreur 403

## Le Problème

Quand le document root du serveur ne peut pas pointer vers `/public`, une erreur 403 apparaît.

## La Solution (Déjà Appliquée)

Le fichier `.htaccess` à la racine a été mis à jour pour :
1. **Bloquer l'accès** aux dossiers sensibles (app, config, storage, etc.)
2. **Rediriger** automatiquement toutes les requêtes vers `/public`

## Vérification

1. **Test simple** - Visitez : `https://votre-domaine.com`
   - ✅ Devrait afficher la page Laravel (redirection login)
   - ❌ Si erreur 403, passez à l'étape 2

2. **Vérifier que mod_rewrite est actif**
   ```bash
   # Via SSH
   php -r "echo (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) ? 'mod_rewrite OK' : 'mod_rewrite KO';"
   ```

3. **Vérifier les permissions du .htaccess**
   ```bash
   ls -la .htaccess
   # Devrait être lisible (644 ou 755)
   chmod 644 .htaccess
   ```

## Si l'Erreur Persiste

### Option 1 : Vérifier les logs Apache
```bash
# Hostinger - Via SSH
tail -f ~/logs/error_log
```
Recherchez les messages d'erreur concernant `.htaccess` ou `mod_rewrite`

### Option 2 : Tester avec .htaccess simplifié

Temporairement, remplacez le contenu de `.htaccess` par :
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

Si cela fonctionne, le problème vient des règles de blocage.

### Option 3 : Méthode Alternative Sans .htaccess

Si `.htaccess` ne fonctionne vraiment pas :

1. **Créer un fichier `index.php` à la racine** :
```php
<?php
// Redirection vers public/index.php
require __DIR__.'/public/index.php';
```

2. **Créer des fichiers `.htaccess` dans chaque dossier sensible** :
```apache
# Dans app/.htaccess, config/.htaccess, etc.
Order deny,allow
Deny from all
```

## Configuration Hostinger Spécifique

### Méthode Recommandée : Modifier le Document Root

1. **Panneau Hostinger** → Domaines → Gérer
2. **Document Root** → Changer vers : `public_html/payment-hub/public`
3. Sauvegarder et attendre 5 minutes

Cette méthode est **la meilleure** car :
- Plus sécurisée (dossiers sensibles inaccessibles)
- Meilleures performances
- Pas besoin de règles .htaccess complexes

### Si Modification Document Root Impossible

Le `.htaccess` mis à jour devrait fonctionner. Si vraiment pas :

**Contactez le support Hostinger** et demandez :
- Vérification que `AllowOverride All` est activé
- Vérification que `mod_rewrite` est chargé
- Logs d'erreur Apache pour votre domaine

## Test Final

Après correction, testez :

```bash
# Test 1 : Page principale
curl -I https://votre-domaine.com

# Test 2 : API ping
curl -X POST https://votre-domaine.com/api/ping \
  -H "Content-Type: application/json" \
  -H "X-Signature: test" \
  -d '{"timestamp":"123"}'

# Test 3 : Bloquer accès sensible (doit retourner 403)
curl -I https://votre-domaine.com/app/
```

Si Tests 1 et 2 = 200 OK et Test 3 = 403 → ✅ Configuration correcte !

## En Résumé

1. ✅ `.htaccess` mis à jour avec règles de sécurité
2. ✅ Redirection automatique vers `/public`
3. ✅ Blocage des dossiers sensibles
4. Si problème → Modifier Document Root (méthode recommandée)
5. Si toujours problème → Contacter support Hostinger