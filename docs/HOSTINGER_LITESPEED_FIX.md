# Fix Erreur 403 - Hostinger avec LiteSpeed

## Important : Serveur LiteSpeed, pas Apache

Votre hébergement Hostinger utilise **LiteSpeed**, pas Apache. Les configurations sont similaires mais il y a des différences importantes.

## Diagnostic du Problème 403

### Étape 1 : Identifier la Cause Exacte

**Accédez aux logs d'erreur dans hPanel :**
1. Connectez-vous à hPanel Hostinger
2. **Sites Web** → `peye.smartdealz.tech` → **Logs** (ou Journal d'erreurs)
3. Consultez les erreurs récentes

**Visitez votre site** : `https://peye.smartdealz.tech`

Notez l'erreur exacte :
- "403 Forbidden" → Problème de permissions ou blocage
- "500 Internal Server Error" → Problème de configuration
- Page blanche → Erreur PHP

### Étape 2 : Vérifier le Document Root

**Dans hPanel :**
1. **Sites Web** → `peye.smartdealz.tech` → **Avancé** → **Modifier le site web**
2. Cherchez **"Document Root"** ou **"Répertoire racine"**

**Deux options :**

#### Option A : Document Root = `public_html/payment-hub/public` (RECOMMANDÉ ✅)

Si vous pouvez le changer :
```
Document Root : public_html/payment-hub/public
```

Avec cette config :
- Pas besoin de règles .htaccess complexes
- Plus sécurisé
- Meilleur performance
- L'URL sera directement : `https://peye.smartdealz.tech`

#### Option B : Document Root = `public_html/payment-hub` (avec .htaccess)

Si vous ne pouvez pas changer :
- Le `.htaccess` à la racine redirige vers `/public`
- L'URL sera aussi : `https://peye.smartdealz.tech`
- Mais les fichiers sensibles sont protégés par les règles de blocage

### Étape 3 : Test de Réécriture LiteSpeed

Créez un fichier `test-rewrite.txt` dans `public_html/payment-hub/public/` :
```
Rewrite fonctionne !
```

Visitez : `https://peye.smartdealz.tech/test-rewrite.txt`

**Si visible** → ✅ Les réécritures fonctionnent

### Étape 4 : Vérifier les Permissions

```bash
# Via SSH
cd public_html/payment-hub
ls -la

# Les permissions doivent être :
# Dossiers : 755 (ou 775)
# Fichiers : 644

# Correction si nécessaire
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 775 storage bootstrap/cache
```

### Étape 5 : Tester .htaccess Minimal

**Temporairement**, remplacez le contenu de `.htaccess` root par :

```apache
RewriteEngine On
RewriteRule ^(.*)$ public/$1 [L,QSA]
```

Visitez : `https://peye.smartdealz.tech`

**Si ça marche** → Le problème venait des règles de protection
**Si ça ne marche pas** → Passez à l'étape 6

### Étape 6 : Vérifier public/.htaccess

Le fichier `public/.htaccess` doit contenir :

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### Étape 7 : Cas Spécifiques LiteSpeed

#### Si "Options -Indexes" cause une erreur

Certaines configs LiteSpeed n'acceptent pas cette directive. Supprimez-la du `.htaccess`.

#### Si les règles [F] ne fonctionnent pas

Au lieu de `RewriteRule ^app/ - [F,L]`, utilisez :
```apache
RewriteRule ^app/ - [R=403,L]
```

#### Configuration .htaccess optimale pour LiteSpeed

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirection simple vers public/
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L,QSA]
</IfModule>
```

## Solutions par Symptôme

### Symptôme 1 : "403 Forbidden" + Liste des dossiers visible

**Cause :** Document root pointe vers le dossier du projet, pas vers `/public`

**Solution :**
1. Changez le Document Root vers `public_html/payment-hub/public`
2. Ou assurez-vous que le `.htaccess` redirige correctement

### Symptôme 2 : "403 Forbidden" + Aucun contenu

**Cause :** Permissions incorrectes ou règles .htaccess trop restrictives

**Solution :**
```bash
# Corriger les permissions
chmod -R 755 public_html/payment-hub
chmod -R 775 public_html/payment-hub/storage
chmod -R 775 public_html/payment-hub/bootstrap/cache
```

### Symptôme 3 : Page blanche

**Cause :** Erreur PHP ou .env manquant

**Solution :**
1. Consultez les logs dans hPanel
2. Vérifiez que `.env` existe et contient `APP_KEY`
3. Exécutez : `php artisan config:clear`

### Symptôme 4 : "500 Internal Server Error"

**Cause :** Erreur dans .htaccess ou erreur PHP

**Solution :**
1. Renommez `.htaccess` en `.htaccess.bak`
2. Si ça marche → problème dans .htaccess
3. Consultez les logs pour l'erreur exacte

## Commandes de Diagnostic

### Via SSH

```bash
# Test 1 : Vérifier que Laravel répond
cd public_html/payment-hub
php artisan route:list

# Test 2 : Vérifier la configuration
php artisan config:show app

# Test 3 : Nettoyer les caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Test 4 : Permissions
ls -la storage bootstrap/cache
```

### Via Navigateur

```bash
# Test direct du dossier public
https://peye.smartdealz.tech/public/

# Si ça affiche Laravel → .htaccess ne redirige pas
# Si 404 → Document root déjà configuré sur /public (bien !)
```

## Configuration Finale Recommandée

### hPanel - Document Root
```
Document Root : public_html/payment-hub/public
```

### .htaccess root (simplifié)
```apache
# Optionnel si Document Root = /public
# Garde-le quand même pour la sécurité

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L,QSA]
</IfModule>
```

### public/.htaccess (standard Laravel)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## Checklist de Vérification

- [ ] Logs consultés dans hPanel (Sites Web → Logs)
- [ ] Document Root vérifié (idéalement = `/public`)
- [ ] Permissions OK (755 pour dossiers, 644 pour fichiers)
- [ ] `.env` existe avec `APP_KEY` défini
- [ ] Test réécriture réussi
- [ ] `storage/` et `bootstrap/cache/` en 775
- [ ] .htaccess minimal testé
- [ ] Laravel accessible via navigateur

## Support Hostinger

Si le problème persiste après ces étapes :

**Contactez le support Hostinger avec ces informations :**
- "Erreur 403 sur Laravel Payment Hub"
- "Serveur LiteSpeed"
- "Besoin de changer Document Root vers `public_html/payment-hub/public`"
- Partagez les logs d'erreur récents

Le support peut :
- Vérifier la configuration LiteSpeed
- Modifier le Document Root
- Vérifier les permissions au niveau serveur

## Test Final

Une fois configuré, testez :

```bash
# Test 1
curl -I https://peye.smartdealz.tech
# Attendu : 302 redirect ou 200 OK

# Test 2
curl https://peye.smartdealz.tech
# Attendu : HTML Laravel (redirection login)

# Test 3
curl -I https://peye.smartdealz.tech/api/ping
# Attendu : 401 ou 403 (normal, besoin de signature)
```

Si tous les tests passent → ✅ Configuration correcte !