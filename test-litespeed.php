<?php
/**
 * Test de Configuration LiteSpeed - Payment Hub
 * 
 * Usage: php test-litespeed.php
 * Ou créer test-litespeed.html et accéder via navigateur
 */

echo "=== Test Configuration LiteSpeed - Payment Hub ===\n\n";

// Test 1: Détection du serveur
echo "1. Détection du serveur web:\n";
$server = $_SERVER['SERVER_SOFTWARE'] ?? php_sapi_name();
echo "   Serveur: {$server}\n";
if (stripos($server, 'litespeed') !== false) {
    echo "   ✅ LiteSpeed détecté\n";
} else {
    echo "   ⚠️  LiteSpeed non détecté (peut être normal en CLI)\n";
}
echo "\n";

// Test 2: Structure des dossiers
echo "2. Vérification de la structure:\n";
$dirs = [
    'app' => 'Dossier application',
    'public' => 'Dossier public',
    'storage' => 'Dossier stockage',
    'bootstrap/cache' => 'Cache de démarrage',
    '.env' => 'Configuration',
];

foreach ($dirs as $dir => $desc) {
    if (file_exists(__DIR__ . '/' . $dir)) {
        echo "   ✅ {$desc} ({$dir})\n";
    } else {
        echo "   ❌ {$desc} MANQUANT ({$dir})\n";
    }
}
echo "\n";

// Test 3: Permissions
echo "3. Vérification des permissions:\n";
$writableDirs = ['storage', 'bootstrap/cache'];
foreach ($writableDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (file_exists($path)) {
        if (is_writable($path)) {
            echo "   ✅ {$dir} est accessible en écriture\n";
        } else {
            echo "   ❌ {$dir} N'EST PAS accessible en écriture\n";
            echo "      Exécutez: chmod -R 775 {$dir}\n";
        }
    }
}
echo "\n";

// Test 4: .htaccess
echo "4. Vérification des .htaccess:\n";
$htaccessFiles = [
    '.htaccess' => 'Root',
    'public/.htaccess' => 'Public',
];

foreach ($htaccessFiles as $file => $desc) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "   ✅ {$desc} .htaccess existe\n";
        $content = file_get_contents($path);
        if (stripos($content, 'RewriteEngine On') !== false) {
            echo "      ✅ Contient RewriteEngine On\n";
        } else {
            echo "      ❌ RewriteEngine On MANQUANT\n";
        }
    } else {
        echo "   ❌ {$desc} .htaccess MANQUANT\n";
    }
}
echo "\n";

// Test 5: Configuration Laravel
echo "5. Test Laravel:\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "   ✅ Laravel chargé avec succès\n";
    echo "   APP_ENV: " . config('app.env') . "\n";
    echo "   APP_URL: " . config('app.url') . "\n";
    
    if (config('app.key')) {
        echo "   ✅ APP_KEY défini\n";
    } else {
        echo "   ❌ APP_KEY MANQUANT - Exécutez: php artisan key:generate\n";
    }
    
    $secret = config('app.payment_hub_secret');
    if ($secret && $secret !== 'CHANGE_ME_SECRET') {
        echo "   ✅ PAYMENT_HUB_SECRET configuré\n";
    } else {
        echo "   ❌ PAYMENT_HUB_SECRET non configuré ou par défaut\n";
    }
} else {
    echo "   ❌ Vendor non trouvé - Exécutez: composer install\n";
}
echo "\n";

// Test 6: URL et chemins
echo "6. Chemins et URLs:\n";
echo "   Document Root actuel: " . getcwd() . "\n";
if (isset($_SERVER['DOCUMENT_ROOT'])) {
    echo "   DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
    
    // Vérifier si on est dans /public
    if (stripos($_SERVER['DOCUMENT_ROOT'], '/public') !== false) {
        echo "   ✅ Document Root pointe vers /public (IDÉAL)\n";
    } else {
        echo "   ⚠️  Document Root ne pointe pas vers /public\n";
        echo "      Recommandé: Configurez-le vers: public_html/payment-hub/public\n";
    }
}
echo "\n";

// Recommandations
echo "=== Recommandations ===\n\n";

$issues = 0;

if (!file_exists(__DIR__ . '/.env')) {
    echo "❌ Créez le fichier .env: cp .env.example .env\n";
    $issues++;
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "❌ Installez les dépendances: composer install\n";
    $issues++;
}

if (!is_writable(__DIR__ . '/storage')) {
    echo "❌ Permissions storage: chmod -R 775 storage bootstrap/cache\n";
    $issues++;
}

if (isset($_SERVER['DOCUMENT_ROOT']) && stripos($_SERVER['DOCUMENT_ROOT'], '/public') === false) {
    echo "⚠️  Document Root: Configurez vers public_html/payment-hub/public dans hPanel\n";
    $issues++;
}

if ($issues === 0) {
    echo "✅ Aucun problème détecté!\n";
    echo "\nTest final - Visitez: " . (config('app.url') ?? 'https://votre-domaine.com') . "\n";
}

echo "\n=== Logs Hostinger ===\n";
echo "Consultez les logs dans hPanel:\n";
echo "Sites Web → peye.smartdealz.tech → Logs\n";
echo "\n";

echo "=== Commandes Utiles ===\n";
echo "php artisan route:list       # Lister les routes\n";
echo "php artisan config:clear     # Nettoyer cache config\n";
echo "php test-plugin-communication.php  # Tester l'API\n";
echo "\n";