<?php
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['credits']) || !is_numeric($data['credits'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Crédits invalides.']);
        exit;
    }

    $credits_to_add = intval($data['credits']);
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
    $success = $stmt->execute([$credits_to_add, $_SESSION['user_id']]);

    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erreur base de données']);
    }
}
