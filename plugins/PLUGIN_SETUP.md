# Configuration du Plugin WooCommerce Payment Hub

## Installation

1. **Zipper le dossier** `woo-payment-hub` (si pas déjà fait)
2. Dans **WordPress** → Extensions → Ajouter → Téléverser
3. Sélectionner le fichier zip et installer
4. Activer le plugin

## Configuration

### WooCommerce → Réglages → Paiements → Payment Hub

**URL Payment Hub** :
- Format : `https://votre-domaine-hub.com`
- ⚠️ **NE PAS** inclure `/public` à la fin
- ⚠️ **NE PAS** inclure `/api` à la fin
- Le plugin ajoutera automatiquement `/api/payment/init`

**Clé secrète** :
- Doit être **identique** à `PAYMENT_HUB_SECRET` dans le `.env` du Hub
- Sur le Hub, exécutez : `grep PAYMENT_HUB_SECRET .env`
- Copiez la valeur exacte (sensible à la casse)

**Exemple de Configuration** :
```
✓ URL Payment Hub: https://peye.smartdealz.tech
✓ Clé secrète: a1b2c3d4e5f6...xyz
```

## Test de Connexion

Une fois configuré, cliquez sur **"Tester"** dans les paramètres du plugin.

### Résultat Attendu

✅ **Succès** : "Connexion établie avec le Payment Hub"
- Le plugin peut communiquer avec le Hub
- Prêt pour les paiements

❌ **Échec** : "Impossible de contacter le Payment Hub"
- Vérifiez l'URL (doit être accessible depuis le serveur WordPress)
- Vérifiez la clé secrète (doit être identique)
- Voir section Dépannage ci-dessous

## Fonctionnement

### 1. Client finalise sa commande
Le client sélectionne "Payment Hub" comme méthode de paiement et clique sur "Commander".

### 2. Requête au Hub
Le plugin envoie une requête sécurisée au Hub avec :
- ID de la commande
- Montant
- Devise
- URL de callback

### 3. Redirection
Le Hub génère un token unique et retourne une URL de redirection.
Le client est redirigé vers le formulaire de paiement du Hub.

### 4. Paiement et Validation
Le client remplit le formulaire et soumet sa preuve de paiement.
Un admin du Hub valide ou rejette le paiement.

### 5. Callback WooCommerce
Le Hub envoie une notification sécurisée à WooCommerce.
La commande est automatiquement mise à jour.

## Endpoints Plugin

Le plugin expose un endpoint REST pour recevoir les callbacks :

```
POST /wp-json/payment/v1/confirm
```

Cet endpoint :
- Vérifie la signature HMAC de la requête
- Met à jour le statut de la commande
- Envoie une confirmation au Hub

## Sécurité

### Signature HMAC
Toutes les communications utilisent HMAC SHA256 :
1. Les données sont triées alphabétiquement par clé
2. Les valeurs sont concaténées avec `|`
3. La signature est générée avec la clé secrète partagée

### Vérification
- Le Hub vérifie la signature des requêtes entrantes
- Le plugin vérifie la signature des callbacks

## Dépannage

### Erreur : "Impossible de contacter le Payment Hub"

**Vérifications** :

1. **URL accessible ?**
   ```bash
   curl -I https://votre-domaine-hub.com
   ```
   Devrait retourner 200 ou 302

2. **Firewall ?**
   - Le serveur WordPress doit pouvoir accéder au Hub
   - Vérifiez les règles de pare-feu

3. **HTTPS valide ?**
   - Certificat SSL valide requis
   - Testez avec : `curl https://votre-domaine-hub.com`

### Erreur : "Signature invalide"

**Cause** : Les clés secrètes ne correspondent pas

**Solution** :
```bash
# Sur le Hub
grep PAYMENT_HUB_SECRET .env

# Copiez cette valeur EXACTEMENT dans les paramètres du plugin WooCommerce
```

### Erreur : "Le Payment Hub a renvoyé une réponse invalide"

**Cause** : Le Hub ne retourne pas les données attendues

**Solution** :
1. Testez l'API du Hub manuellement (voir TEST_PLUGIN_COMMUNICATION.md dans le Hub)
2. Consultez les logs Laravel du Hub : `storage/logs/laravel.log`

### Erreur lors du Callback

**Cause** : Le Hub ne peut pas atteindre l'endpoint WordPress

**Vérifications** :
1. WordPress accessible depuis l'extérieur ?
2. Endpoint REST actif : `/wp-json/payment/v1/confirm`
3. Test manuel :
   ```bash
   curl -I https://votre-site-woocommerce.com/wp-json/payment/v1/confirm
   ```

## Logs et Debug

### Activer le Mode Debug WordPress

Dans `wp-config.php` :
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Les logs seront dans : `wp-content/debug.log`

### Voir les Requêtes du Plugin

Les requêtes et réponses sont loggées automatiquement en mode debug.

### Tester la Signature Manuellement

Depuis le Hub, exécutez :
```bash
php test-plugin-communication.php
```

Ce script teste la communication dans les deux sens.

## Support

- **Hub non accessible** → Voir TROUBLESHOOTING.md dans le Hub
- **Problème de signature** → Vérifier que les secrets correspondent
- **Callback ne fonctionne pas** → Vérifier les logs WordPress

## Développement

### Structure du Plugin

```
woo-payment-hub/
├── includes/
│   ├── class-gateway-payment-hub.php  # Gateway WooCommerce
│   ├── class-hmac.php                 # Gestion HMAC
│   ├── class-rest-api.php             # Endpoints REST
│   └── helpers.php                     # Fonctions utilitaires
├── woo-payment-hub.php                # Fichier principal
└── README.md                           # Ce fichier
```

### Modifier la Gateway

Éditez `includes/class-gateway-payment-hub.php` :
- `process_payment()` : Logique d'envoi au Hub
- `init_form_fields()` : Champs de configuration admin

### Modifier le Callback

Éditez `includes/class-rest-api.php` :
- `handle_confirm()` : Logique de réception callback Hub