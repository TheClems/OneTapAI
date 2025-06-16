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
$chatId = isset($_POST['chatId']) ? $_POST['chatId'] : null;

if (!$chatId) {
    http_response_code(400);
    echo json_encode(['error' => 'ID du chat manquant']);
    exit;
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    // Vérifier que le chat appartient à l'utilisateur
    $stmt = $pdo->prepare("SELECT id FROM chat_channels WHERE id = ? AND id_user = ?");
    $stmt->execute([$chatId, $userId]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Chat non autorisé']);
        exit;
    }
    
    // Supprimer les messages
    $stmt = $pdo->prepare("DELETE FROM chat_messages WHERE chat_channel_id = ?");
    $stmt->execute([$chatId]);
    
    // Supprimer le channel
    $stmt = $pdo->prepare("DELETE FROM chat_channels WHERE id = ?");
    $stmt->execute([$chatId]);
    
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    error_log("Erreur suppression chat: " . $e->getMessage());
    echo json_encode(['error' => 'Erreur serveur']);
}
