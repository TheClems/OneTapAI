<?php
require_once 'api_config.php';

// Récupérer les messages depuis la configuration commune
$cleanMessages = $cleanMessages ?? []; // Cette variable doit venir d'api_config.php

function convertMessagesToGeminiFormat($messages)
{
    $geminiMessages = [];
    
    foreach ($messages as $message) {
        $role = $message['role'];
        $content = $message['content'];
        
        // Gemini utilise 'user' et 'model' au lieu de 'user' et 'assistant'
        if ($role === 'assistant') {
            $role = 'model';
        }
        
        // Ignorer les messages système pour Gemini
        if ($role === 'system') {
            continue;
        }
        
        $geminiMessages[] = [
            'role' => $role,
            'parts' => [
                ['text' => $content]
            ]
        ];
    }
    
    return $geminiMessages;
}

// Clé API Gemini - Remplacez par votre vraie clé API
$apiKey = 'AIzaSyBs7CiasxHyT2IrrZiiBPsUMeKaBcFCA7A';

// Convertir les messages au format Gemini
$geminiMessages = convertMessagesToGeminiFormat($cleanMessages);

// Préparer les données pour l'API Gemini
$data = [
    "contents" => $geminiMessages,
    "generationConfig" => [
        "temperature" => 0.7,
        "topK" => 40,
        "topP" => 0.95,
        "maxOutputTokens" => 1000,
        "stopSequences" => []
    ],
    "safetySettings" => [
        [
            "category" => "HARM_CATEGORY_HARASSMENT",
            "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
        ],
        [
            "category" => "HARM_CATEGORY_HATE_SPEECH",
            "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
        ],
        [
            "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
            "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
        ],
        [
            "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
            "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
        ]
    ]
];

// URL de l'API Gemini
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

// Initialiser cURL
$ch = curl_init();
if ($ch === false) {
    logError("Impossible d'initialiser cURL");
    sendJsonResponse(['success' => false, 'error' => 'Erreur système']);
}

// Configuration cURL
$success = curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT => 'Gemini-Chat-App/1.0',
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
    sendJsonResponse(['success' => false, 'error' => 'Erreur de connexion à l\'API Gemini']);
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

    $errorMessage = 'Erreur API Gemini';
    if ($httpCode === 400) {
        $errorMessage = 'Requête invalide';
    } elseif ($httpCode === 401) {
        $errorMessage = 'Clé API invalide ou expirée';
    } elseif ($httpCode === 403) {
        $errorMessage = 'Accès refusé';
    } elseif ($httpCode === 429) {
        $errorMessage = 'Trop de requêtes, réessayez dans quelques secondes';
    } elseif ($httpCode === 500) {
        $errorMessage = 'Erreur serveur Gemini, réessayez plus tard';
    } elseif ($httpCode >= 400) {
        // Essayer de décoder la réponse pour obtenir plus de détails
        $errorResponse = json_decode($response, true);
        if ($errorResponse && isset($errorResponse['error']['message'])) {
            $errorMessage = $errorResponse['error']['message'];
        }
    }

    sendJsonResponse(['success' => false, 'error' => $errorMessage], $httpCode >= 500 ? 500 : 400);
}

// Vérifier la structure de la réponse Gemini
if (!isset($result['candidates']) || !is_array($result['candidates']) || empty($result['candidates'])) {
    logError("Unexpected API Response structure: " . json_encode($result));
    sendJsonResponse(['success' => false, 'error' => 'Format de réponse inattendu']);
}

if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    logError("Missing content in API response: " . json_encode($result['candidates'][0] ?? 'No candidates[0]'));
    sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse manquant']);
}

// Extraire et nettoyer le contenu
$content = $result['candidates'][0]['content']['parts'][0]['text'];
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
    'model' => 'gemini-pro',
    'usage' => $result['usageMetadata'] ?? null,
    'timestamp' => date('c'),
    'chat_channel_id' => $chatChannelId
]);
?>