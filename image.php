<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

error_reporting(E_ALL);
$apiKey = '84b3c98803124c7cae41a7bd8b06ad1d'; // remplace par ta clé API
$endpoint = 'https://api-flux.aiturboapi.com/v1/image-to-prompt'; // ou /v1/text-to-prompt selon ton besoin

// Adaptation de payload pour génération d'images
$data = [
    "prompt" => "un chat robotique dans un paysage futuriste",
    "width"  => 512,
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
    if (isset($res['data']['images'][0])) {
        $img = $res['data']['images'][0];
        // si c’est un encodage base64
        if (strpos($img, 'data:image') === 0) {
            echo '<img src="' . $img . '" />';
        } else {
            // sinon on suppose que c’est une URL
            echo '<img src="' . htmlspecialchars($img) . '" />';
        }
    } else {
        echo 'Réponse API : ' . htmlspecialchars($response);
    }
}

curl_close($ch);
