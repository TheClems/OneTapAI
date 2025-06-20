<?php
// claude_api.php - API spécifique pour Claude (Anthropic)

require_once 'api_config.php';

// Configuration spécifique à Claude
const CLAUDE_API_KEY = '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';
const CLAUDE_API_URL = 'https://api.anthropic.com/v1/messages';
const CLAUDE_MODEL = 'claude-3-5-haiku-latest';
const ANTHROPIC_VERSION = '2023-06-01';
$userId = $_SESSION['user_id'];

/**
 * Processeur spécifique pour l'API Claude
 */
function processClaudeApi($cleanMessages, $chatChannelId)
{
    global $userId;
    
    // Préparer les données pour l'API Claude
    $data = [
        "model" => CLAUDE_MODEL,
        "max_tokens" => 1024,
        "messages" => $cleanMessages,
        "temperature" => 0.7,
        "top_p" => 0.95
    ];

    // Headers spécifiques à Claude/Anthropic
    $headers = [
        'Content-Type: application/json',
        'x-api-key: ' . CLAUDE_API_KEY,
        'anthropic-version: ' . ANTHROPIC_VERSION
    ];

    // Initialiser et exécuter la requête cURL
    $ch = initializeCurl(CLAUDE_API_URL, $data, $headers);
    $curlResult = executeCurlRequest($ch, 'Claude');
    
    $response = $curlResult['response'];
    $httpCode = $curlResult['httpCode'];

    // Vérification du code HTTP
    if ($httpCode !== 200) {
        $errorMessage = handleClaudeHttpError($httpCode, $response);
        sendJsonResponse(['success' => false, 'error' => $errorMessage], $httpCode >= 500 ? 500 : 400);
    }

    // Décoder et valider la réponse JSON
    $result = json_decode($response, true);
    if ($result === null || json_last_error() !== JSON_ERROR_NONE) {
        logError("JSON Decode Error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 200), 'claude_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Réponse API invalide']);
    }

    // Vérifier la structure de la réponse Claude
    if (!isset($result['content']) || !is_array($result['content']) || empty($result['content'])) {
        logError("Unexpected API Response structure: " . json_encode($result), 'claude_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Format de réponse inattendu']);
    }

    // Claude retourne un tableau de contenus, on prend le premier élément de type text
    $content = '';
    foreach ($result['content'] as $contentBlock) {
        if (isset($contentBlock['type']) && $contentBlock['type'] === 'text' && isset($contentBlock['text'])) {
            $content = $contentBlock['text'];
            break;
        }
    }

    if (empty($content)) {
        logError("No text content found in API response: " . json_encode($result['content']), 'claude_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Aucun contenu textuel trouvé dans la réponse']);
    }

    // Valider le contenu
    if (!is_string($content)) {
        logError("Content is not a string: " . gettype($content), 'claude_errors.log');
        sendJsonResponse(['success' => false, 'error' => 'Contenu de réponse invalide']);
    }

    $content = trim($content);
    if (empty($content)) {
        sendJsonResponse(['success' => false, 'error' => 'Réponse vide de l\'API']);
    }

    // Gestion de l'usage des tokens pour Claude
    $usage = $result['usage'] ?? null;
    if ($usage && isset($usage['input_tokens'], $usage['output_tokens'])) {
        $inputTokens = $usage['input_tokens'];
        $outputTokens = $usage['output_tokens'];
        $totalTokens = $inputTokens + $outputTokens;

        $pdo = getDBConnection();
        try {
            // Récupérer le coût des tokens d'entrée
            $stmt = $pdo->prepare("SELECT tokens_par_credit FROM ia_models WHERE modele_ia = 'Haiku 3.5' AND io_type = 'input'");
            $stmt->execute();
            $inputResult = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($inputResult) {
                $tokenParCreditsInput = $inputResult['tokens_par_credit'];
                $creditsUsedInput = (1 / $tokenParCreditsInput) * $inputTokens;
            } else {
                $creditsUsedInput = 0;
                logError("Model not found in database for input tokens: " . CLAUDE_MODEL, 'claude_errors.log');
            }

            // Récupérer le coût des tokens de sortie
            $stmt = $pdo->prepare("SELECT tokens_par_credit FROM ia_models WHERE modele_ia = 'Haiku 3.5' AND io_type = 'output'");
            $stmt->execute();
            $outputResult = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($outputResult) {
                $tokenParCreditsOutput = $outputResult['tokens_par_credit'];
                $creditsUsedOutput = (1 / $tokenParCreditsOutput) * $outputTokens;
            } else {
                $creditsUsedOutput = 0;
                logError("Model not found in database for output tokens: " . CLAUDE_MODEL, 'claude_errors.log');
            }

            $creditsUsed = $creditsUsedInput + $creditsUsedOutput;

            // Déduire les crédits de l'utilisateur
            $stmt = $pdo->prepare("UPDATE users SET credits = credits - ? WHERE id = ?");
            $stmt->execute([$creditsUsed, $userId]);

        } catch (PDOException $e) {
            error_log("Erreur récupération modèle Claude: " . $e->getMessage());
        }
    }

    return [
        'content' => $content,
        'model' => $result['model'] ?? CLAUDE_MODEL,
        'usage' => $result['usage'] ?? null
    ];
}

/**
 * Gérer les erreurs HTTP spécifiques à Claude
 */
function handleClaudeHttpError($httpCode, $response)
{
    logError("Claude API Error: HTTP $httpCode - " . substr($response, 0, 500), 'claude_errors.log');

    $errorMessage = 'Erreur API Claude';
    
    switch ($httpCode) {
        case 400:
            $errorMessage = 'Requête invalide - vérifiez le format des données';
            break;
        case 401:
            $errorMessage = 'Clé API Claude invalide ou expirée';
            break;
        case 403:
            $errorMessage = 'Accès refusé - vérifiez vos permissions';
            break;
        case 429:
            $errorMessage = 'Limite de taux atteinte, réessayez dans quelques secondes';
            break;
        case 500:
            $errorMessage = 'Erreur serveur Claude, réessayez plus tard';
            break;
        case 529:
            $errorMessage = 'Service Claude temporairement surchargé';
            break;
        default:
            if ($httpCode >= 400) {
                // Essayer de décoder la réponse pour obtenir plus de détails
                $errorResponse = json_decode($response, true);
                if ($errorResponse && isset($errorResponse['error']['message'])) {
                    $errorMessage = $errorResponse['error']['message'];
                } elseif ($errorResponse && isset($errorResponse['message'])) {
                    $errorMessage = $errorResponse['message'];
                }
            }
            break;
    }

    return $errorMessage;
}

// Point d'entrée principal
processApiRequest('processClaudeApi');
?>