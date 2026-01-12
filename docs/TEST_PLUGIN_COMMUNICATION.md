# Test de Communication Plugin ↔ Payment Hub

## Prérequis

1. ✅ Payment Hub installé et accessible
2. ✅ Plugin WooCommerce installé
3. ✅ Même `PAYMENT_HUB_SECRET` dans `.env` du Hub et paramètres du plugin

## Étape 1 : Vérifier la Configuration du Plugin

Dans **WooCommerce → Réglages → Paiements → Payment Hub** :

```
✓ URL Payment Hub : https://votre-domaine.com
✓ Clé secrète   : [la même que PAYMENT_HUB_SECRET dans .env]
```

**Important :** L'URL **NE DOIT PAS** contenir `/public` à la fin

## Étape 2 : Test Depuis le Plugin

Dans les paramètres du plugin, cliquez sur **"Tester"**.

### Si Succès ✅
- Message : "Connexion OK" ou "Test réussi"
- La communication fonctionne !

### Si Échec ❌
- Passez à l'étape de diagnostic ci-dessous

## Étape 3 : Diagnostic Manuel

### Test 1 : Vérifier que l'API répond

```bash
# Test simple de l'endpoint ping
curl -X POST https://votre-domaine.com/api/ping \
  -H "Content-Type: application/json" \
  -H "X-Signature: test" \
  -d '{"timestamp":"123"}'
```

**Résultat attendu :** 
- Erreur 403 "Invalid signature" → ✅ API accessible, signature incorrecte (normal)
- Erreur 404 → ❌ Route non trouvée, problème de routing
- Timeout/Connection refused → ❌ Serveur injoignable

### Test 2 : Générer une Signature Valide

Créez un fichier `test-signature.php` à la racine du Hub :

```php
<?php
require __DIR__.'/vendor/autoload.php';

$secret = env('PAYMENT_HUB_SECRET'); // Ou copiez depuis .env

$data = [
    'timestamp' => time()
];

ksort($data);
$values = array_map(fn($val) => is_scalar($val) ? (string)$val : json_encode($val), $data);
$payload = implode('|', $values);
$signature = hash_hmac('sha256', $payload, $secret);

echo "Données : " . json_encode($data) . "\n";
echo "Signature : " . $signature . "\n";
echo "\nTest avec curl :\n";
echo "curl -X POST https://votre-domaine.com/api/ping \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'X-Signature: {$signature}' \\\n";
echo "  -d '" . json_encode($data) . "'\n";
```

Exécutez :
```bash
php test-signature.php
```

Copiez la commande curl générée et exécutez-la.

**Résultat attendu :** `{"status":"ok"}` → ✅ Signature valide !

### Test 3 : Simuler une Requête du Plugin

Créez `test-payment-init.php` :

```php
<?php
require __DIR__.'/vendor/autoload.php';

$secret = 'VOTRE_PAYMENT_HUB_SECRET'; // À remplacer

$data = [
    'order_id' => 12345,
    'amount' => 100.00,
    'currency' => 'HTG',
    'shop_domain' => 'https://votre-shop.com',
    'callback_url' => 'https://votre-shop.com/wp-json/payment/v1/confirm',
];

ksort($data);
$values = array_map(fn($val) => is_scalar($val) ? (string)$val : json_encode($val), $data);
$payload = implode('|', $values);
$signature = hash_hmac('sha256', $payload, $secret);

echo "Test de /api/payment/init\n\n";
echo "Données : " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
echo "Signature : {$signature}\n\n";

// Test avec curl
$ch = curl_init('https://votre-domaine.com/api/payment/init');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        "X-Signature: {$signature}"
    ],
    CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP : {$httpCode}\n";
echo "Réponse : {$response}\n\n";

if ($httpCode === 200) {
    $result = json_decode($response, true);
    echo "✅ Succès ! Token : " . ($result['token'] ?? 'N/A') . "\n";
    echo "✅ URL de redirection : " . ($result['redirect_url'] ?? 'N/A') . "\n";
} else {
    echo "❌ Échec - Code : {$httpCode}\n";
    echo "Réponse : {$response}\n";
}
```

Exécutez :
```bash
php test-payment-init.php
```

**Résultat attendu :**
```
✅ Succès ! Token : xxxxx-xxxx-xxxx
✅ URL de redirection : https://votre-domaine.com/pay?token=xxxxx
```

## Étape 4 : Consulter les Logs Laravel

Les requêtes sont maintenant loggées. Consultez :

```bash
tail -f storage/logs/laravel.log
```

Ensuite, essayez un paiement depuis WooCommerce. Vous verrez :

```
[YYYY-MM-DD HH:MM:SS] local.INFO: Payment Init Request {"data":{"order_id":123,...},"signature":"...",..."ip":"..."}
[YYYY-MM-DD HH:MM:SS] local.INFO: Payment Token Created {"token":"...","order_id":123}
```

### Si Vous Voyez les Logs
✅ La requête arrive au Hub → Problème résolu ou presque

### Si Pas de Logs
❌ La requête n'arrive pas → Problème réseau/firewall/URL

## Problèmes Courants et Solutions

### Erreur : "Invalid signature"

**Cause :** Les secrets ne correspondent pas

**Solution :**
1. Dans le Hub, vérifiez `.env` :
   ```bash
   grep PAYMENT_HUB_SECRET .env
   ```
2. Dans WooCommerce, vérifiez les paramètres du plugin
3. Ils doivent être **EXACTEMENT identiques**

### Erreur : "Missing signature"

**Cause :** Le header `X-Signature` n'est pas envoyé

**Solution :**
- Vérifiez que le plugin est bien configuré
- Le header est ajouté dans `class-gateway-payment-hub.php` ligne 89

### Erreur : Connection timeout

**Cause :** URL incorrecte ou serveur inaccessible

**Solutions :**
1. Vérifiez l'URL dans le plugin (sans /public)
2. Testez l'URL avec curl depuis le serveur WooCommerce :
   ```bash
   curl -I https://votre-domaine.com
   ```
3. Vérifiez le firewall/CORS

### Erreur : 403 Forbidden

**Cause :** API bloquée ou signature invalide

**Solutions :**
1. Vérifiez que l'API est accessible : `curl https://votre-domaine.com/api/ping`
2. Testez avec une signature valide (voir Test 2)
3. Consultez les logs Laravel

### Erreur : 500 Internal Server Error

**Cause :** Erreur PHP dans le Hub

**Solution :**
```bash
# Consultez les logs Laravel
tail -50 storage/logs/laravel.log

# Et les logs Apache/PHP
tail -50 ~/logs/error_log  # Sur Hostinger
```

## Étape 5 : Test Complet Checkout

1. **Créez un produit test** dans WooCommerce
2. **Ajoutez au panier** et procédez au checkout
3. **Sélectionnez** "Payment Hub" comme moyen de paiement
4. **Finalisez** la commande

### Résultat Attendu

1. ✅ Redirection vers `https://votre-domaine.com/pay?token=xxxxx`
2. ✅ Formulaire de paiement affiché
3. ✅ Après soumission, preuve uploadée et commande marquée "En attente"

### Si Échec

- Consultez les logs : `storage/logs/laravel.log`
- Vérifiez l'état de la commande dans WooCommerce
- Recherchez les erreurs JavaScript dans la console du navigateur

## Checklist Finale

- [ ] Plugin activé dans WooCommerce
- [ ] URL correcte (sans /public)
- [ ] Secret identique dans Hub et plugin
- [ ] Test manuel avec curl réussi
- [ ] Logs Laravel montrent les requêtes entrantes
- [ ] Test checkout complet réussi

## Support

Si problème persiste après tous ces tests :

1. **Partagez les logs** :
   - Les 50 dernières lignes de `storage/logs/laravel.log`
   - Le code HTTP retourné par les tests curl

2. **Vérifiez la version PHP** :
   - Hub : PHP 8.1+ recommandé
   - WordPress : PHP 7.4+ minimum

3. **Vérifiez les extensions PHP** :
   - curl, json, mbstring doivent être activées