<?php
/**
 * Script de Test - Communication Plugin WooCommerce ↔ Payment Hub
 * 
 * Usage: php test-plugin-communication.php
 */

echo "\n=== Test Communication Plugin → Payment Hub ===\n\n";

// Charger Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Récupérer le secret
$secret = config('app.payment_hub_secret');

if (empty($secret) || $secret === 'CHANGE_ME_SECRET') {
    echo "❌ ERREUR: PAYMENT_HUB_SECRET n'est pas configuré dans .env\n";
    echo "   Configurez-le, puis relancez ce script.\n\n";
    exit(1);
}

echo "✓ Secret Payment Hub chargé\n\n";

// Test 1: Endpoint Ping
echo "--- Test 1: Endpoint /api/ping ---\n";

$data = ['timestamp' => (string)time()];
ksort($data);
$values = array_map(fn($val) => is_scalar($val) ? (string)$val : json_encode($val), $data);
$payload = implode('|', $values);
$signature = hash_hmac('sha256', $payload, $secret);

$hubUrl = config('app.url');
$pingUrl = rtrim($hubUrl, '/') . '/api/ping';

echo "URL: {$pingUrl}\n";
echo "Data: " . json_encode($data) . "\n";
echo "Signature: {$signature}\n\n";

$ch = curl_init($pingUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        "X-Signature: {$signature}"
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_SSL_VERIFYPEER => false, // Pour tests locaux
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Erreur cURL: {$error}\n\n";
} elseif ($httpCode === 200) {
    echo "✅ Ping réussi! Code: {$httpCode}\n";
    echo "   Réponse: {$response}\n\n";
} else {
    echo "❌ Ping échoué! Code: {$httpCode}\n";
    echo "   Réponse: {$response}\n\n";
}

// Test 2: Endpoint Payment Init
echo "--- Test 2: Endpoint /api/payment/init ---\n";

$data = [
    'order_id' => 99999,
    'amount' => '150.50',
    'currency' => 'HTG',
    'shop_domain' => 'https://test-shop.com',
    'callback_url' => 'https://test-shop.com/wp-json/payment/v1/confirm',
];

ksort($data);
$values = array_map(fn($val) => is_scalar($val) ? (string)$val : json_encode($val), $data);
$payload = implode('|', $values);
$signature = hash_hmac('sha256', $payload, $secret);

$initUrl = rtrim($hubUrl, '/') . '/api/payment/init';

echo "URL: {$initUrl}\n";
echo "Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
echo "Signature: {$signature}\n\n";

$ch = curl_init($initUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        "X-Signature: {$signature}"
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Erreur cURL: {$error}\n\n";
} elseif ($httpCode === 200) {
    echo "✅ Payment Init réussi! Code: {$httpCode}\n";
    $result = json_decode($response, true);
    if (isset($result['token']) && isset($result['redirect_url'])) {
        echo "   Token: {$result['token']}\n";
        echo "   Redirect URL: {$result['redirect_url']}\n";
        echo "\n✅ SUCCÈS COMPLET - La communication fonctionne parfaitement!\n\n";
    } else {
        echo "   Réponse: {$response}\n\n";
    }
} else {
    echo "❌ Payment Init échoué! Code: {$httpCode}\n";
    echo "   Réponse: {$response}\n\n";
}

// Résumé et instructions
echo "--- Résumé ---\n";
echo "Secret utilisé: " . substr($secret, 0, 10) . "...\n";
echo "URL Hub: {$hubUrl}\n\n";

echo "--- Instructions pour le Plugin WooCommerce ---\n";
echo "Dans WooCommerce → Réglages → Paiements → Payment Hub:\n";
echo "  • URL Payment Hub: {$hubUrl}\n";
echo "  • Clé secrète: {$secret}\n\n";

echo "--- Commande cURL Manuelle (Ping) ---\n";
echo "curl -X POST '{$pingUrl}' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'X-Signature: {$signature}' \\\n";
echo "  -d '" . json_encode(['timestamp' => (string)time()]) . "'\n\n";

echo "=== Fin des Tests ===\n\n";