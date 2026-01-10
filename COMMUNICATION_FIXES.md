# Fixes pour la Communication Plugin ↔ Hub

## Problèmes Identifiés et Corrigés

### 1. Middleware HMAC - Parsing du JSON Body ✅

**Problème :** Le middleware ne récupérait pas correctement les données du JSON body envoyé par le plugin.

**Solution :** Modification de `app/Http/Middleware/VerifyApiSignature.php` pour utiliser `$request->all()` qui récupère toutes les données, y compris le JSON body.

```php
// Avant (ne fonctionnait pas toujours)
$data = $request->except(['signature']);

// Après (fonctionne correctement)
$data = $request->all();
unset($data['signature']);
```

### 2. Configuration Laravel - Secret HMAC ✅

**Problème :** La clé `payment_hub_secret` n'était pas définie dans `config/app.php`.

**Solution :** Ajout de la configuration dans `config/app.php` :

```php
'payment_hub_secret' => env('PAYMENT_HUB_SECRET', 'CHANGE_ME_SECRET'),
```

### 3. Logging des Requêtes API ✅

**Problème :** Difficile de débugger sans voir les requêtes entrantes.

**Solution :** Ajout de logs dans `PaymentInitController` :

```php
Log::info('Payment Init Request', [
    'data' => $request->all(),
    'signature' => $request->header('X-Signature'),
    'ip' => $request->ip()
]);
```

## Comment Tester

### Test Automatique

Exécutez le script de test :

```bash
php test-plugin-communication.php
```

Ce script va :
- ✅ Tester l'endpoint `/api/ping`
- ✅ Tester l'endpoint `/api/payment/init`
- ✅ Afficher les URLs et secrets à configurer dans le plugin

### Test Manuel depuis WooCommerce

1. **Configurez le plugin** :
   - URL : Celle affichée par le script de test
   - Secret : Celui affiché par le script de test

2. **Cliquez sur "Tester"** dans les paramètres du plugin

3. **Résultat attendu** : "Connexion OK"

### Test avec un Checkout Réel

1. Créez une commande test dans WooCommerce
2. Sélectionnez "Payment Hub" comme méthode de paiement
3. Finalisez la commande
4. Vous devriez être redirigé vers le formulaire de paiement du Hub

## Vérification des Logs

Après un test, consultez les logs Laravel :

```bash
tail -50 storage/logs/laravel.log
```

Vous devriez voir :

```
[timestamp] local.INFO: Payment Init Request {"data":{...},"signature":"...","ip":"..."}
[timestamp] local.INFO: Payment Token Created {"token":"...","order_id":...}
```

## Checklist de Vérification

- [ ] `.env` contient `PAYMENT_HUB_SECRET` (généré par install.sh)
- [ ] Plugin WooCommerce a la même clé secrète
- [ ] URL du plugin = URL du Hub (sans /public)
- [ ] Script `test-plugin-communication.php` réussit les 2 tests
- [ ] Test "Connexion" du plugin réussit
- [ ] Checkout WooCommerce redirige vers le Hub

## Si Problème Persiste

### Vérifier la Configuration du Plugin

```php
// Dans WooCommerce, le plugin envoie :
$payload = [
    'order_id' => 12345,
    'amount' => '100.00',
    'currency' => 'HTG',
    'shop_domain' => 'https://...',
    'callback_url' => 'https://...',
];

// Signature générée avec :
$signature = Payment_Hub_HMAC::sign($payload, $secret);

// Envoyée dans le header :
'X-Signature' => $signature
```

### Vérifier que les Algorithmes Correspondent

**Plugin** (`plugins/woo-payment-hub/includes/class-hmac.php`) :
```php
ksort($data);
$values = array_map(fn($val) => is_scalar($val) ? (string)$val : json_encode($val), $data);
$payload = implode('|', $values);
return hash_hmac('sha256', $payload, $secret);
```

**Hub** (`app/Services/HmacService.php`) :
```php
ksort($data);
$values = array_map(fn($val) => is_scalar($val) ? (string)$val : json_encode($val), $data);
$payload = implode('|', $values);
return hash_hmac('sha256', $payload, $secret);
```

✅ Les deux utilisent le **même algorithme** → Signatures compatibles

## Guide Complet

Pour des tests détaillés et solutions avancées, consultez :
- [TEST_PLUGIN_COMMUNICATION.md](./TEST_PLUGIN_COMMUNICATION.md) - Guide complet de test