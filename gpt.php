<?php
// Ta clé API OpenAI (à garder secrète)
$api_key = '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';

// Le message que tu veux envoyer à ChatGPT
$prompt = "Explique-moi la différence entre HTML et PHP.";

// Préparation des données à envoyer
$data = [
    "model" => "gpt-3.5-turbo", // ou "gpt-4" si tu y as accès
    "messages" => [
        ["role" => "user", "content" => $prompt]
    ]
];

// Initialisation de cURL
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Exécution de la requête
$response = curl_exec($ch);

// Vérification des erreurs
if (curl_errno($ch)) {
    echo 'Erreur cURL : ' . curl_error($ch);
} else {
    $responseData = json_decode($response, true);
    echo "<pre>";
    echo $responseData['choices'][0]['message']['content'];
    echo "</pre>";
}

// Fermeture de cURL
curl_close($ch);
?>
