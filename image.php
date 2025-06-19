<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

$apiKey = '84b3c98803124c7cae41a7bd8b06ad1d'; // Remplace par ta vraie clé API
$endpoint = 'https://api-flux.aiturboapi.com/v1/text-to-image';

$data = [
    "prompt" => "un chat robotique dans un paysage futuriste",
    "width" => 512,
    "height" => 512,
    "num_images" => 1
];

$headers = [
    "X-API-KEY: $apiKey",
    "Content-Type: application/json"
];

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Erreur cURL : ' . curl_error($ch);
} else {
    $res = json_decode($response, true);

    // Affiche l’image générée
    if (isset($res['data']['images'][0])) {
        $imageUrl = $res['data']['images'][0]; // Peut être une URL ou base64
        echo '<h2>Image générée :</h2>';
        echo '<img src="' . htmlspecialchars($imageUrl) . '" alt="Image générée" />';
    } else {
        echo 'Réponse inattendue :<br>';
        echo '<pre>' . htmlspecialchars(json_encode($res, JSON_PRETTY_PRINT)) . '</pre>';
    }
}

curl_close($ch);

