<?php
require_once 'api_config.php';


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


// Gestion des erreurs cURL
if ($response === false || !empty($curlError)) {
    logError("CURL Error: " . $curlError);
    sendJsonResponse(['success' => false, 'error' => 'Erreur de connexion à l\'API Gemini']);
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