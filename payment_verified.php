<?php
// Test de diagnostic complet
echo "<!DOCTYPE html><html><head><title>Test Webhook</title></head><body>";
echo "<h1>DIAGNOSTIC WEBHOOK</h1>";
echo "<p>Date/Heure: " . date('Y-m-d H:i:s') . "</p>";

// Test 1: PHP fonctionne
echo "<h2>1. PHP Status</h2>";
echo "<p>✅ PHP fonctionne - Version: " . phpversion() . "</p>";

// Test 2: Écriture fichier
echo "<h2>2. Test écriture fichier</h2>";
$test_write = file_put_contents('test_write.txt', 'Test écriture: ' . date('Y-m-d H:i:s'));
if ($test_write !== false) {
    echo "<p>✅ Écriture fichier OK</p>";
} else {
    echo "<p>❌ Écriture fichier ÉCHOUE</p>";
}

// Test 3: Méthode de requête
echo "<h2>3. Méthode de requête</h2>";
echo "<p>Méthode: " . ($_SERVER['REQUEST_METHOD'] ?? 'NON_DEFINIE') . "</p>";

// Test 4: URL complète
echo "<h2>4. URL complète</h2>";
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$url_complete = $protocol . ($_SERVER['HTTP_HOST'] ?? 'HOST_INDEFINI') . ($_SERVER['REQUEST_URI'] ?? 'URI_INDEFINIE');
echo "<p>URL complète: <strong>" . htmlspecialchars($url_complete) . "</strong></p>";

// Test 5: Headers Stripe (si présents)
echo "<h2>5. Headers Stripe</h2>";
if (isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
    echo "<p>✅ Header Stripe détecté: " . substr($_SERVER['HTTP_STRIPE_SIGNATURE'], 0, 20) . "...</p>";
} else {
    echo "<p>❌ Pas de header Stripe (normal si test navigateur)</p>";
}

// Test 6: Body de la requête
echo "<h2>6. Body de la requête</h2>";
$body = file_get_contents("php://input");
if (!empty($body)) {
    echo "<p>✅ Body présent (" . strlen($body) . " caractères)</p>";
} else {
    echo "<p>❌ Body vide (normal si GET)</p>";
}

// Test 7: Composer/autoload
echo "<h2>7. Test Stripe SDK</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "<p>✅ vendor/autoload.php trouvé</p>";
    require_once 'vendor/autoload.php';
    if (class_exists('\Stripe\Stripe')) {
        echo "<p>✅ Stripe SDK chargé</p>";
    } else {
        echo "<p>❌ Stripe SDK non trouvé après autoload</p>";
    }
} else {
    echo "<p>❌ vendor/autoload.php NON TROUVÉ</p>";
    echo "<p>Chemin actuel: " . __DIR__ . "</p>";
    echo "<p>Fichiers dans le dossier:</p><ul>";
    $files = scandir(__DIR__);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "<li>" . htmlspecialchars($file) . "</li>";
        }
    }
    echo "</ul>";
}

// Instructions
echo "<h2>8. Instructions</h2>";
echo "<p><strong>Copie cette URL complète dans Stripe:</strong></p>";
echo "<p style='background:#f0f0f0;padding:10px;'>" . htmlspecialchars($url_complete) . "</p>";

echo "</body></html>";

// Log aussi dans un fichier
$log = "DIAGNOSTIC " . date('Y-m-d H:i:s') . "\n";
$log .= "URL: " . $url_complete . "\n";
$log .= "Méthode: " . ($_SERVER['REQUEST_METHOD'] ?? 'NON_DEFINIE') . "\n";
$log .= "Autoload exists: " . (file_exists('vendor/autoload.php') ? 'OUI' : 'NON') . "\n";
$log .= "------------------------\n";
file_put_contents('diagnostic_log.txt', $log, FILE_APPEND);
?>