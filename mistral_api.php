<?php
require_once 'api_config.php';
$cleanMessages = $_POST['messages'] ?? [];  // ou depuis la source appropriée


// Clé API Mistral
$apiKey = 'OX4fzStQrzPd2PfyCAl7PR6ip3bcsvey';


// Préparer les données pour l'API Mistral
$data = [
    "model" => "mistral-medium",
    "messages" => $cleanMessages,
    "temperature" => 0.7,
    "max_tokens" => 1000,
    "top_p" => 0.95,
    "stream" => false
];


// Configuration cURL
$success = curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.mistral.ai/v1/chat/completions',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT => 'Mistral-Chat-App/1.0',
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 3
]);


// Gestion des erreurs cURL
if ($response === false || !empty($curlError)) {
    logError("CURL Error: " . $curlError);
    sendJsonResponse(['success' => false, 'error' => 'Erreur de connexion à l\'API Mistral']);
}

// Vérification du code HTTP
if ($httpCode !== 200) {
    logError("API Error: HTTP $httpCode - " . substr($response, 0, 500));

    $errorMessage = 'Erreur API Mistral';
    if ($httpCode === 401) {
        $errorMessage = 'Clé API invalide ou expirée';
    } elseif ($httpCode === 429) {
        $errorMessage = 'Trop de requêtes, réessayez dans quelques secondes';
    } elseif ($httpCode === 500) {
        $errorMessage = 'Erreur serveur Mistral, réessayez plus tard';
    } elseif ($httpCode >= 400) {
        // Essayer de décoder la réponse pour obtenir plus de détails
        $errorResponse = json_decode($response, true);
        if ($errorResponse && isset($errorResponse['message'])) {
            $errorMessage = $errorResponse['message'];
        }
    }

    sendJsonResponse(['success' => false, 'error' => $errorMessage], $httpCode >= 500 ? 500 : 400);
}

// Envoyer la réponse finale
sendJsonResponse([
    'success' => true,
    'content' => $formattedContent,
    'model' => $result['model'] ?? 'mistral-medium',
    'usage' => $result['usage'] ?? null,
    'timestamp' => date('c'),
    'chat_channel_id' => $chatChannelId
]);
?>