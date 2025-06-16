<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT 
            cc.id, 
            cc.created_at,
            COALESCE(
                (SELECT content 
                 FROM chat_messages 
                 WHERE chat_channel_id = cc.id 
                   AND role = 'user' 
                 ORDER BY created_at ASC 
                 LIMIT 1), 
                'Nouveau chat'
            ) as first_message
        FROM chat_channels cc
        WHERE cc.id_user = ? 
        ORDER BY cc.created_at DESC
    ");
    $stmt->execute([$userId]);
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($chats);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erreur récupération chats: " . $e->getMessage());
    echo json_encode(['error' => 'Erreur serveur']);
}
