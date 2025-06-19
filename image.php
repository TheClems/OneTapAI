<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

$apiKey = '84b3c98803124c7cae41a7bd8b06ad1d'; // Remplace par ta vraie clé API
$endpoint = 'https://api-flux.aiturboapi.com//v1/text-to-prompt';

$data = [
    "prompt" => "chat astronaute dans l'espace",
    "width" => 512,
    "height" => 512,
    "num_images" => 1,
    "steps" => 30
];

$headers = [
    "x-api-key: $apiKey",
    "Content-Type: application/json"
];
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Vérifie le certificat SSL
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "<p style='color:red'>❌ Erreur cURL : " . curl_error($ch) . "</p>";
} else {
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "<h3>Code HTTP : $httpCode</h3>";
    echo "<pre>Réponse : " . htmlspecialchars($response) . "</pre>";
}

curl_close($ch);
