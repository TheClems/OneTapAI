<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();

// Vérifier si l'utilisateur a des crédits
if ($user['credits'] <= 0) {
    header('Location: buy_credits.php');
    exit();
}

$messages = [];
$response = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message) && $user['credits'] > 0) {
        // Ajouter le message de l'utilisateur
        $messages[] = ['role' => 'user', 'content' => $message];
        
        // Simuler une réponse de l'IA (fictive)
        $ai_responses = [
            "Bonjour ! Je suis une IA fictive. Votre message était : \"" . $message . "\". Comment puis-je vous aider aujourd'hui ?",
            "C'est une excellente question ! En tant qu'IA de démonstration, je peux vous dire que je suis là pour vous aider.",
            "Merci pour votre message. Cette version est une simulation - l'intégration avec une vraie IA sera ajoutée prochainement.",
            "Votre demande a été reçue. Dans la version finale, une vraie IA répondra à vos questions de manière pertinente.",
            "Intéressant ! Je traite votre demande... (Ceci est une réponse simulée pour la démonstration)"
        ];
        $response = $ai_responses[array_rand($ai_responses)];
        $messages[] = ['role' => 'assistant', 'content' => $response];
        
        // Déduire un crédit (coût fictif : 10 crédits par message)
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET credits = credits - 10 WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Actualiser les données utilisateur
        $user = getCurrentUser();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Chat - AI Credits</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            height: 80vh;
            display: flex;
            flex-direction: column;
        }
        .header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .credits-info {
            background: #e7f3ff;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
        }
        .chat-area {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        .message {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 10px;
        }
        .user-message {
            background: #007bff;
            color: white;
            margin-left: 20%;
        }
        .ai-message {
            background: #f8f9fa;
            border: 1px solid #eee;
            margin-right: 20%;
        }
        .input-area {
            padding: 20px;
            border-top: 1px solid #eee;
        }
        .input-group {
            display: flex;
            gap: 10px;
        }
        .message-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .send-btn {
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .send-btn:hover {
            background: #0056b3;
        }
        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .back-link {
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .demo-notice {
            background: #fff3cd;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            color: #856404;
            font-size: 14px;
        }
        .cost-info {
            font-size: 12px;
            color: #666;
            text-align: center;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Chat avec l'IA</h1>
            <div>
                <div class="credits-info">
                    Crédits: <?php echo number_format($user['credits']); ?>
                </div>
                <a href="dashboard.php" class="back-link">← Retour</a>
            </div>
        </div>
        
        <div class="chat-area">
            <div class="demo-notice">
                <strong>Mode démonstration :</strong> Cette IA est fictive. L'intégration avec une vraie IA sera ajoutée ultérieurement.
            </div>
            
            <?php if (empty($messages)): ?>
                <div class="ai-message">
                    <strong>Assistant IA :</strong><br>
                    Bonjour ! Je suis votre assistant IA. Comment puis-je vous aider aujourd'hui ?
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="<?php echo $msg['role'] == 'user' ? 'user-message' : 'ai-message'; ?>">
                        <strong><?php echo $msg['role'] == 'user' ? 'Vous' : 'Assistant IA'; ?> :</strong><br>
                        <?php echo nl2br(htmlspecialchars($msg['content'])); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="input-area">
            <?php if ($user['credits'] > 0): ?>
                <form method="POST">
                    <div class="input-group">
                        <input type="text" name="message" class="message-input" placeholder="Tapez votre message..." required>
                        <button type="submit" class="send-btn">Envoyer</button>
                    </div>
                </form>
                <div class="cost-info">Coût par message : 10 crédits</div>
            <?php else: ?>
                <div style="text-align: center; color: #dc3545;">
                    <p>Vous n'avez plus de crédits suffisants pour continuer la conversation.</p>
                    <a href="buy_credits.php" style="color: #007bff;">Acheter des crédits</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-scroll vers le bas
        const chatArea = document.querySelector('.chat-area');
        chatArea.scrollTop = chatArea.scrollHeight;
        
        // Focus sur l'input
        const messageInput = document.querySelector('.message-input');
        if (messageInput) {
            messageInput.focus();
        }
    </script>
</body>
</html>