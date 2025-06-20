<?php
// grok_api.php - API spécifique pour Grok

require_once 'api_config.php';

// Configuration spécifique à Grok
const GROK_API_KEY = 'xai-q6WfymzwDtajjdqItVwTX96MybrSNPplIRvz8ysK2wPyLSkS7hkv40YWnfCGK4bDxgqNmKSyhqMvJJl4'; // Remplacez par votre clé API
const GROK_API_URL = 'https://api.x.ai/v1/chat/completions';
const GROK_MODEL = 'grok-3-mini';
$userId = $_SESSION['user_id'];

/**
 * Processeur spécifique pour l'API Grok
 */
function processGrokApi($cleanMessages, $chatChannelId)
{
    global $userId;
    // Préparer les données pour l'API Grok
    $data = [
        "model" => GROK_MODEL,
        "messages" => $cleanMessages,
        "temperature" => 0.7,
        "stream" => false
    ];

    // Headers spécifiques à Grok
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROK_API_KEY
    ];

    // Initialiser et exécuter la requête cURL
    $ch = initializeCurl(GROK_API_URL, $data, $headers);
    $curlResult = executeCurlRequest($ch, 'Grok');
    
    $response = $curlResult['response'];
    $httpCode = $curlResult['httpCode'];

    // Vérification du code HTTP
    if ($httpCode !== 200) {
        $errorMessage = handleGrokHttpError($httpCode, $response);
        sendJsonResponse(['success' => false, 'error' => $errorMessage], $httpCode >= 500 ? 500 : 400);
    }

    // Décoder et valider la réponse JSON
    $result = json_decode($response, true);
    if ($result === null || json_last_error() !== JSON_ERROR_NONE) {
        logError("JSON Decode Error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 200), 'grok_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Réponse API invalide']);
    }

    // Vérifier la structure de la réponse Grok
    if (!isset($result['choices']) || !is_array($result['choices']) || empty($result['choices'])) {
        logError("Unexpected API Response structure: " . json_encode($result), 'grok_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Format de réponse inattendu']);
    }

    if (!isset($result['choices'][0]['message']['content'])) {
        logError("Missing content in API response: " . json_encode($result['choices'][0] ?? 'No choices[0]'), 'grok_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse manquant']);
    }

    // Extraire et valider le contenu
    $content = $result['choices'][0]['message']['content'];
    if (!is_string($content)) {
        logError("Content is not a string: " . gettype($content), 'grok_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse invalide']);
    }

    $content = trim($content);
    if (empty($content)) {
        sendJsonResponse(['success' => false, 'error' => 'Réponse vide de l\'API']);
    }
    
    // Gestion des tokens et crédits (si l'API Grok retourne des informations d'usage)
    $usage = $result['usage'] ?? null;
    if ($usage && isset($usage['prompt_tokens'], $usage['completion_tokens'])) {
        $promptTokens = $usage['prompt_tokens'];
        $completionTokens = $usage['completion_tokens'];
        $totalTokens = $promptTokens + $completionTokens;   

        $pdo = getDBConnection();
        try {
            // Récupérer le coût des tokens d'entrée pour Grok
            $stmt = $pdo->prepare("SELECT tokens_par_credit FROM ia_models WHERE modele_ia = 'grok-3-latest' AND io_type = 'input'");
            $stmt->execute();
            $inputResult = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($inputResult) {
                $tokenParCreditsInput = $inputResult['tokens_par_credit'];
                $creditsUsedInput = (1 / $tokenParCreditsInput) * $promptTokens;

                // Récupérer le coût des tokens de sortie pour Grok
                $stmt = $pdo->prepare("SELECT tokens_par_credit FROM ia_models WHERE modele_ia = 'grok-3-latest' AND io_type = 'output'");
                $stmt->execute();
                $outputResult = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($outputResult) {
                    $tokenParCreditsOutput = $outputResult['tokens_par_credit'];
                    $creditsUsedOutput = (1 / $tokenParCreditsOutput) * $completionTokens;

                    $creditsUsed = $creditsUsedInput + $creditsUsedOutput;

                    // Déduire les crédits de l'utilisateur
                    $stmt = $pdo->prepare("UPDATE users SET credits = credits - ? WHERE id = ?");
                    $stmt->execute([$creditsUsed, $userId]);
                }
            }

        } catch (PDOException $e) {
            error_log("Erreur récupération modèle Grok: " . $e->getMessage());
        }
    }
    
    return [
        'content' => $content,
        'model' => $result['model'] ?? GROK_MODEL,
        'usage' => $result['usage'] ?? null
    ];
}

/**
 * Gérer les erreurs HTTP spécifiques à Grok
 */
function handleGrokHttpError($httpCode, $response)
{
    logError("API Error: HTTP $httpCode - " . substr($response, 0, 500), 'grok_errors.log');

    $errorMessage = 'Erreur API Grok';
    if ($httpCode === 401) {
        $errorMessage = 'Clé API Grok invalide ou expirée';
    } elseif ($httpCode === 429) {
        $errorMessage = 'Limite de taux atteinte, réessayez dans quelques secondes';
    } elseif ($httpCode === 500) {
        $errorMessage = 'Erreur serveur Grok, réessayez plus tard';
    } elseif ($httpCode >= 400) {
        // Essayer de décoder la réponse pour obtenir plus de détails
        $errorResponse = json_decode($response, true);
        if ($errorResponse && isset($errorResponse['error']['message'])) {
            $errorMessage = $errorResponse['error']['message'];
        } elseif ($errorResponse && isset($errorResponse['message'])) {
            $errorMessage = $errorResponse['message'];
        }
    }

    return $errorMessage;
}

// Point d'entrée principal
processApiRequest('processGrokApi');
?>