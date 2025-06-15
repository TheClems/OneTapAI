<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$apiKey = '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'; // remplace par ta clé OpenAI


$ch = curl_init();

$data = [
    "model" => "gpt-3.5-turbo", // Remplace par "gpt-4" si tu as accès
    "messages" => [
        ["role" => "user", "content" => "Dis-moi une blague !"]
    ]
];

curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Erreur CURL : ' . curl_error($ch);
} else {
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $result = json_decode($response, true);

    if ($httpCode !== 200) {
        echo "Erreur API (HTTP $httpCode) :<br>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    } elseif (isset($result['choices'][0]['message']['content'])) {
        echo $result['choices'][0]['message']['content'];
    } else {
        echo "Réponse inattendue de l'API :<br>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
}

curl_close($ch);
?>