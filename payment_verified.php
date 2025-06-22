<?php
// Webhook de debug ultra-simple
// Sauvegarde TOUT pour voir ce qui se passe

// 1. Log de base
$debug_log = "=== WEBHOOK DEBUG " . date('Y-m-d H:i:s') . " ===\n";
$debug_log .= "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'NON_DEFINIE') . "\n";
$debug_log .= "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NON_DEFINIE') . "\n";
$debug_log .= "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NON_DEFINIE') . "\n";
$debug_log .= "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NON_DEFINIE') . "\n";

// 2. Headers
$debug_log .= "\n--- HEADERS ---\n";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0) {
        $debug_log .= $key . ": " . $value . "\n";
    }
}

// 3. Body
$debug_log .= "\n--- BODY ---\n";
$body = file_get_contents("php://input");
$debug_log .= "Body length: " . strlen($body) . "\n";
$debug_log .= "Body preview: " . substr($body, 0, 200) . "...\n";

// 4. Sauvegarde
file_put_contents('webhook_debug_full.txt', $debug_log . "\n\n", FILE_APPEND);

// 5. Réponse immédiate
http_response_code(200);
header('Content-Type: text/plain');
echo "DEBUG OK - " . date('Y-m-d H:i:s');

// 6. Force l'arrêt pour éviter toute redirection
exit();
?>