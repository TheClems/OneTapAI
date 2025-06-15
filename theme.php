<?php
header('Content-Type: application/json');
<script>
  const currentUserId = <?php echo json_encode($userId); ?>;
</script>
// Exemple d'exécution de la mise à jour
require_once 'config.php';
requireLogin();

// Vérifier si l'utilisateur est connecté
$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Utilisateur non authentifié']);
    exit;
}

// Récupérer le thème de la requête
$theme = isset($_POST['theme']) ? (int)$_POST['theme'] : null;

if ($theme === null || !in_array($theme, [0, 1])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Thème invalide']);
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET dark_mode = ? WHERE id = ?");
    $success = $stmt->execute([$theme, $user['id']]);
    
    echo json_encode(['success' => $success]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur de base de données']);
}

$stmt->close();
$pdo = null;
