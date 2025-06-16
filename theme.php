<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php'; // Connexion PDO à la BDD

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
    exit;
}

// Autoriser uniquement les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// Lire le corps JSON de la requête
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Vérifier que "theme" est présent
if (!isset($data['theme'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'error' => 'Thème manquant']);
    exit;
}

$user_id = $_SESSION['user_id'];
$theme = intval($data['theme']); // 0 = dark, 1 = light
$pdo = getDBConnection();

// Mise à jour du thème dans la base
$sql = "UPDATE users SET dark_mode = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);

if ($stmt && $stmt->execute([$theme, $user_id])) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour']);
}
?>
