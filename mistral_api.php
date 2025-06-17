<?php
require_once 'api_config.php';

// Récupérer les messages depuis la configuration commune
$cleanMessages = $cleanMessages ?? []; // Cette variable doit venir d'api_config.php

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

// Initialiser cURL
$ch = curl_init();
if ($ch === false) {
    logError("Impossible d'initialiser cURL");
    sendJsonResponse(['success' => false, 'error' => 'Erreur système']);
}

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

if (!$success) {
    curl_close($ch);
    logError("Erreur configuration cURL");
    sendJsonResponse(['success' => false, 'error' => 'Erreur de configuration']);
}

// Exécuter la requête
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

// Gestion des erreurs cURL
if ($response === false || !empty($curlError)) {
    logError("CURL Error: " . $curlError);
    sendJsonResponse(['success' => false, 'error' => 'Erreur de connexion à l\'API Mistral']);
}

// Décoder la réponse JSON
$result = json_decode($response, true);

if ($result === null || json_last_error() !== JSON_ERROR_NONE) {
    logError("JSON Decode Error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 200));
    sendJsonResponse(['success' => false, 'error' => 'Réponse API invalide']);
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

// Vérifier la structure de la réponse Mistral
if (!isset($result['choices']) || !is_array($result['choices']) || empty($result['choices'])) {
    logError("Unexpected API Response structure: " . json_encode($result));
    sendJsonResponse(['success' => false, 'error' => 'Format de réponse inattendu']);
}

if (!isset($result['choices'][0]['message']['content'])) {
    logError("Missing content in API response: " . json_encode($result['choices'][0] ?? 'No choices[0]'));
    sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse manquant']);
}

// Extraire et nettoyer le contenu
$content = $result['choices'][0]['message']['content'];
if (!is_string($content)) {
    logError("Content is not a string: " . gettype($content));
    sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse invalide']);
}

$content = trim($content);
if (empty($content)) {
    sendJsonResponse(['success' => false, 'error' => 'Réponse vide de l\'API']);
}

// Sauvegarder la réponse de l'assistant en base
$saveAssistantResult = saveMessageToDatabase($pdo, $chatChannelId, 'assistant', $content);
if (!$saveAssistantResult) {
    logError("Failed to save assistant message to database");
}

// Formater la réponse (convertir les retours à la ligne en <br>)
$formattedContent = nl2br(htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

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