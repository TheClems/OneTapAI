<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

$apiKey = '84b3c98803124c7cae41a7bd8b06ad1d'; // Remplace par ta vraie clé API

$endpoint = 'https://api.fluxaiimagegenerator.com/v1/images/generate';

$data = [
    "prompt" => "un dragon mécanique en plein vol",
    "width" => 512,
    "height" => 512,
    "num_images" => 1
];

$headers = [
    "X-API-KEY: $apiKey",
    "Content-Type: application/json"
];

// Initialise cURL
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HEADER, true); // pour avoir les en-têtes HTTP

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Séparer en-têtes et corps de la réponse
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headersRaw = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

// Affiche les infos pour déboguer
echo "<h3>Code HTTP : $httpCode</h3>";
echo "<h4>Réponse brute de l'API :</h4>";
echo "<pre>" . htmlspecialchars($body) . "</pre>";

// Essaie de décoder le JSON
$json = json_decode($body, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "<p style='color:red'>❌ Erreur de décodage JSON : " . json_last_error_msg() . "</p>";
} elseif (isset($json['data']['images'][0])) {
    $img = $json['data']['images'][0];
    echo "<h3>✅ Image générée :</h3>";
    echo "<img src='$img' alt='Image générée' />";
} else {
    echo "<p style='color:red'>❌ Réponse inattendue :</p>";
    echo "<pre>" . print_r($json, true) . "</pre>";
}


