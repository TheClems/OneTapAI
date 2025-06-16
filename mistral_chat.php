<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once 'config.php';

session_start();  // Toujours démarrer la session en début de script

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    // Tu peux maintenant utiliser $userId
} else {
    // Pas d'utilisateur connecté ou ID non stocké en session
    $userId = null;
}

// Fonction pour récupérer l'historique des messages d'un channel
function getChannelHistory($channelId) {
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("
            SELECT role, content, created_at 
            FROM chat_messages 
            WHERE chat_channel_id = ? 
            ORDER BY created_at ASC
        ");
        $stmt->execute([$channelId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur récupération historique: " . $e->getMessage());
        return [];
    }
}

// Fonction pour récupérer tous les chats de l'utilisateur
function getUserChats($userId) {
    if (!$userId) return [];
    
    $pdo = getDBConnection();
    try {
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur récupération chats: " . $e->getMessage());
        return [];
    }
}

// Fonction pour supprimer un chat
function deleteChat($chatId, $userId) {
    $pdo = getDBConnection();
    try {
        $pdo->beginTransaction();
        
        // Vérifier que le chat appartient à l'utilisateur
        $stmt = $pdo->prepare("SELECT id FROM chat_channels WHERE id = ? AND id_user = ?");
        $stmt->execute([$chatId, $userId]);
        
        if ($stmt->rowCount() === 0) {
            return false;
        }
        
        // Supprimer les messages
        $stmt = $pdo->prepare("DELETE FROM chat_messages WHERE chat_channel_id = ?");
        $stmt->execute([$chatId]);
        
        // Supprimer le channel
        $stmt = $pdo->prepare("DELETE FROM chat_channels WHERE id = ?");
        $stmt->execute([$chatId]);
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Erreur suppression chat: " . $e->getMessage());
        return false;
    }
}

// Traitement de la suppression via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_chat') {
    header('Content-Type: application/json');
    
    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'Non authentifié']);
        exit;
    }
    
    $chatId = $_POST['chat_id'] ?? '';
    
    if (deleteChat($chatId, $userId)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression']);
    }
    exit;
}

$channelHistory = [];
$currentChannelId = null;
$userChats = getUserChats($userId);

if (!isset($_GET['id_channel']) || empty($_GET['id_channel'])) {
    // Pas de paramètre id_channel ou id_channel vide => création d'un nouveau chat

    $id = uniqid('chat_', true); // ID unique

    $createdAt = date('Y-m-d H:i:s');
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("INSERT INTO chat_channels (id, id_user, created_at) VALUES (:id, :id_user, :created_at)");
    $stmt->execute([
        ':id' => $id,
        ':id_user' => $userId,
        ':created_at' => $createdAt
    ]);
    $_SESSION['id_channel'] = $id;

    header("Location: ?id_channel=" . $id);
    exit;
} else {
    // Channel existant, récupérer l'historique
    $currentChannelId = $_GET['id_channel'];
    $channelHistory = getChannelHistory($currentChannelId);
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mistral AI Chat</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
        }

        /* Particles Background */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float linear infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Sidebar */
        .sidebar {
            width: 300px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 10;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            background: rgba(103, 126, 234, 0.1);
        }

        .sidebar-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .new-chat-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .new-chat-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(103, 126, 234, 0.3);
        }

        .chats-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
        }

        .chat-item {
            margin: 5px 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            group: hover;
        }

        .chat-item:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .chat-item.active {
            background: rgba(103, 126, 234, 0.1);
            border-color: rgba(103, 126, 234, 0.3);
        }

        .chat-preview {
            font-size: 14px;
            color: #333;
            font-weight: 500;
            margin-bottom: 5px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .chat-date {
            font-size: 12px;
            color: #666;
        }

        .delete-chat {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 59, 59, 0.1);
            color: #ff3b3b;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            opacity: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .chat-item:hover .delete-chat {
            opacity: 1;
        }

        .delete-chat:hover {
            background: #ff3b3b;
            color: white;
        }

        /* Toggle Button */
        .sidebar-toggle {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 20;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-toggle:hover {
            background: white;
            transform: scale(1.1);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        .main-content.sidebar-hidden {
            margin-left: 0;
        }

        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 5;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 300;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 0;
            max-height: 60vh;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
        }

        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .message {
            margin-bottom: 20px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            text-align: right;
        }

        .message.ai {
            text-align: left;
        }

        .message-content {
            display: inline-block;
            max-width: 80%;
            padding: 15px 20px;
            border-radius: 20px;
            font-size: 16px;
            line-height: 1.5;
            word-wrap: break-word;
            position: relative;
        }

        .message.user .message-content {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(103, 126, 234, 0.3);
        }

        .message.ai .message-content {
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .message-time {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 5px;
        }

        .message.ai .message-time {
            color: #999;
        }

        /* Formatage du contenu */
        .message-content h1, .message-content h2, .message-content h3 {
            margin: 10px 0;
            color: inherit;
        }

        .message-content code {
            background: rgba(0, 0, 0, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }

        .message-content pre {
            background: rgba(0, 0, 0, 0.1);
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 10px 0;
        }

        .message-content pre code {
            background: none;
            padding: 0;
        }

        .message-content ul, .message-content ol {
            margin: 10px 0;
            padding-left: 20px;
        }

        .message-content blockquote {
            border-left: 4px solid rgba(103, 126, 234, 0.5);
            padding-left: 15px;
            margin: 10px 0;
            font-style: italic;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: white;
        }

        .loading-dots {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
        }

        .loading-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
            margin: 0 4px;
            animation: bounce 1.4s ease-in-out infinite both;
        }

        .loading-dot:nth-child(1) { animation-delay: -0.32s; }
        .loading-dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
            } 40% {
                transform: scale(1);
            }
        }

        .input-container {
            padding: 20px 0;
        }

        .input-group {
            display: flex;
            gap: 15px;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 25px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .message-input {
            flex: 1;
            background: transparent;
            border: none;
            color: white;
            font-size: 16px;
            outline: none;
        }

        .message-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .send-button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(103, 126, 234, 0.3);
        }

        .send-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(103, 126, 234, 0.4);
        }

        .send-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .error-message {
            background: rgba(255, 59, 59, 0.9);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                height: 100vh;
                z-index: 15;
                transform: translateX(-100%);
            }

            .sidebar.visible {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0 !important;
            }

            .chat-container {
                padding: 10px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .message-content {
                max-width: 90%;
            }

            .sidebar-toggle {
                display: flex;
            }
        }

        @media (min-width: 769px) {
            .sidebar-toggle {
                display: none;
            }
        }

        /* Scrollbar personnalisée pour la sidebar */
        .chats-list::-webkit-scrollbar {
            width: 6px;
        }

        .chats-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .chats-list::-webkit-scrollbar-thumb {
            background: rgba(103, 126, 234, 0.3);
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <?php require_once 'nav.php'; ?>

    <div class="particles" id="particles"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">💬 Historique des chats</h2>
            <button class="new-chat-btn" onclick="createNewChat()">
                ✨ Nouveau chat
            </button>
        </div>
        <div class="chats-list" id="chatsList">
            <?php if (empty($userChats)): ?>
                <div style="padding: 20px; text-align: center; color: #666; font-style: italic;">
                    Aucun chat pour le moment
                </div>
            <?php else: ?>
                <?php foreach ($userChats as $chat): ?>
                    <div class="chat-item <?php echo $chat['id'] === $currentChannelId ? 'active' : ''; ?>" 
                         onclick="loadChat('<?php echo htmlspecialchars($chat['id']); ?>')">
                        <button class="delete-chat" onclick="event.stopPropagation(); deleteChat('<?php echo htmlspecialchars($chat['id']); ?>')" title="Supprimer ce chat">
                            ×
                        </button>
                        <div class="chat-preview">
                            <?php echo htmlspecialchars(substr($chat['first_message'], 0, 40) . (strlen($chat['first_message']) > 40 ? '...' : '')); ?>
                        </div>
                        <div class="chat-date">
                            <?php echo date('d/m/Y H:i', strtotime($chat['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        ☰
    </button>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="chat-container">
            <div class="header">
                <h1>🤖 Mistral AI Chat</h1>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php if (empty($channelHistory)): ?>
                    <!-- Message de bienvenue seulement si pas d'historique -->
                    <div class="message ai">
                        <div class="message-content">
                            Salut ! Je suis Mistral AI. Comment puis-je t'aider aujourd'hui ? 🚀
                        </div>
                        <div class="message-time" id="welcomeTime"></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="loading" id="loading">
                <div class="loading-dots">
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                </div>
                <p>Mistral réfléchit...</p>
            </div>

            <div class="input-container">
                <div class="input-group">
                    <input type="text" class="message-input" id="messageInput" placeholder="Tapez votre message..." autocomplete="off">
                    <button class="send-button" id="sendButton">
                        <span>Envoyer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const loading = document.getElementById('loading');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        // État de la sidebar
        let sidebarVisible = window.innerWidth > 768;

        // Historique des messages depuis PHP
        let messageHistory = <?php echo json_encode(array_map(function($msg) {
            return [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }, $channelHistory)); ?>;

        // Historique des messages depuis la base de données
        const channelHistoryFromDB = <?php echo json_encode($channelHistory); ?>;

        // Fonctions pour la sidebar
        function toggleSidebar() {
            sidebarVisible = !sidebarVisible;
            updateSidebarState();
        }

        function updateSidebarState() {
            if (window.innerWidth <= 768) {
                // Mobile
                if (sidebarVisible) {
                    sidebar.classList.add('visible');
                    sidebar.classList.remove('hidden');
                } else {
                    sidebar.classList.remove('visible');
                    sidebar.classList.add('hidden');
                }
            } else {
                // Desktop
                if (sidebarVisible) {
                    sidebar.classList.remove('hidden');
                    mainContent.classList.remove('sidebar-hidden');
                } else {
                    sidebar.classList.add('hidden');
                    mainContent.classList.add('sidebar-hidden');
                }
            }
        }

        // Créer un nouveau chat
        function createNewChat() {
            window.location.href = window.location.pathname;
        }

        // Charger un chat existant
        function loadChat(chatId) {
            window.location.href = `?id_channel=${chatId}`;
        }

        // Supprimer un chat
        async function deleteChat(chatId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce chat ?')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'delete_chat');
                formData.append('chat_id', chatId);

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Si on supprime le chat actuel, rediriger vers un nouveau chat
                    const urlParams = new URLSearchParams(window.location.search);
                    const currentChatId = urlParams.get('id_channel');
                    
                    if (currentChatId === chatId) {
                        window.location.href = window.location.pathname;
                    } else {
                        // Sinon, juste recharger la page pour mettre à jour la sidebar
                        window.location.reload();
                    }
                } else {
                    alert('Erreur lors de la suppression du chat');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la suppression du chat');
            }
        }

        // Fonction pour formater l'heure
        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Charger l'historique existant
        function loadHistoryMessages() {
            if (channelHistoryFromDB && channelHistoryFromDB.length > 0) {
                // Vider le conteneur (enlever le message de bienvenue)
                chatMessages.innerHTML = '';
                
                channelHistoryFromDB.forEach((message, index) => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `message ${message.role === 'user' ? 'user' : 'ai'}`;
                    
                    const timeString = formatTime(message.created_at);
                    
                    // Formater le contenu pour les messages de l'IA depuis la DB
                    const formattedContent = message.role === 'user' ? 
                        message.content : 
                        formatMessageContent(message.content);
                    
                    messageDiv.innerHTML = `
                        <div class="message-content">${formattedContent}</div>
                        <div class="message-time">${timeString}</div>
                    `;
                    
                    // Ajouter un délai pour l'animation
                    setTimeout(() => {
                        chatMessages.appendChild(messageDiv);
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }, index * 100);
                });
            } else {
                // Pas d'historique, afficher l'heure de bienvenue
                const welcomeTimeEl = document.getElementById('welcomeTime');
                if (welcomeTimeEl) {
                    welcomeTimeEl.textContent = new Date().toLocaleTimeString('fr-FR', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
        }

        // Créer les particules d'arrière-plan
        function createParticles() {
            const particles = document.getElementById('particles');
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.width = Math.random() * 4 + 2 + 'px';
                particle.style.height = particle.style.width;
                particle.style.animationDelay = Math.random() * 20 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
                particles.appendChild(particle);
            }
        }

        // Fonction pour formater le contenu d'un message
        function formatMessageContent(content) {
            // Convertir les **texte** en <strong>
            content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            
            // Convertir les *texte* en <em>
            content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');
            
            // Convertir les ### Titre en <h3>
            content = content.replace(/^### (.*$)/gm, '<h3>$1</h3>');
            
            // Convertir les ## Titre en <h2>
            content = content.replace(/^## (.*$)/gm, '<h2>$1</h2>');
            
            // Convertir les # Titre en <h1>
            content = content.replace(/^# (.*$)/gm, '<h1>$1</h1>');
            
            // Convertir les blocs de code ```code```
            content = content.replace(/```(\w+)?\n?([\s\S]*?)```/g, function(match, lang, code) {
                return `<pre><code>${code.trim()}</code></pre>`;
            });
            
            // Convertir le code inline `code`
            content = content.replace(/`([^`]+)`/g, '<code>$1</code>');
            
            // Convertir les liens [texte](url)
            content = content.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');
            
            // Convertir les listes - item
            content = content.replace(/^- (.*$)/gm, '<li>$1</li>');
            content = content.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
            
            // Convertir les listes numérotées 1. item
            content = content.replace(/^\d+\. (.*$)/gm, '<li>$1</li>');
            
            // Convertir les citations > texte
            content = content.replace(/^> (.*$)/gm, '<blockquote>$1</blockquote>');
            
            // Convertir les retours à la ligne
            content = content.replace(/\n/g, '<br>');
            
            return content;
        }

        // Afficher un message dans le chat (sans affecter l'historique)
        function displayMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'ai'}`;

            const now = new Date();
            const timeString = now.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });

            // Formater le contenu seulement pour les messages de l'IA
            const formattedContent = isUser ? content : formatMessageContent(content);

            messageDiv.innerHTML = `
                <div class="message-content">${formattedContent}</div>
                <div class="message-time">${timeString}</div>
            `;

            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Afficher une erreur
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            chatMessages.appendChild(errorDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;

            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }

        // Envoyer un message
        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            // Éviter les doubles soumissions
            if (sendButton.disabled) return;

            // Afficher le message utilisateur
            displayMessage(message, true);
            messageInput.value = '';

            // Désactiver l'interface
            sendButton.disabled = true;
            messageInput.disabled = true;
            loading.style.display = 'block';

            try {
                // Préparer les messages pour l'API
                // Inclure l'historique complet + le nouveau message utilisateur
                const currentMessages = [
                    ...messageHistory,  // Historique complet
                    {
                        role: 'user',
                        content: message
                    }
                ];

                // Garder seulement les 10 derniers messages pour l'API
                const messagesToSend = currentMessages.slice(-10);

                console.log('=== DEBUG ===');
                console.log('Historique avant envoi:', messageHistory);
                console.log('Messages envoyés à l\'API:', messagesToSend);

                // Extraire le chat_channel_id de l'URL
                const urlParams = new URLSearchParams(window.location.search);
                const chatChannelId = urlParams.get('id_channel');

                const response = await fetch('mistral_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        messages: messagesToSend,
                        chat_channel_id: chatChannelId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Formater et afficher la réponse de l'IA
                    displayMessage(data.content);
                    
                    // Mettre à jour l'historique avec BOTH messages
                    messageHistory.push(
                        {
                            role: 'user',
                            content: message
                        },
                        {
                            role: 'assistant',
                            content: data.content
                        }
                    );

                    // Garder seulement les 20 derniers messages dans l'historique
                    if (messageHistory.length > 20) {
                        messageHistory = messageHistory.slice(-20);
                    }

                    console.log('Historique après mise à jour:', messageHistory);
                    console.log('=== FIN DEBUG ===');

                } else {
                    showError(data.error || 'Erreur inconnue');
                }

            } catch (error) {
                showError('Erreur de connexion: ' + error.message);
            } finally {
                // Réactiver l'interface
                sendButton.disabled = false;
                messageInput.disabled = false;
                loading.style.display = 'none';
                messageInput.focus();
            }
        }

        // Gestionnaires d'événements
        sendButton.addEventListener('click', sendMessage);

        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Gestionnaire de redimensionnement
        window.addEventListener('resize', () => {
            updateSidebarState();
        });

        // Fermer la sidebar sur mobile quand on clique en dehors
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && sidebarVisible) {
                if (!sidebar.contains(e.target) && !document.getElementById('sidebarToggle').contains(e.target)) {
                    sidebarVisible = false;
                    updateSidebarState();
                }
            }
        });

        // Auto-focus sur l'input
        messageInput.focus();

        // Créer les particules
        createParticles();

        // Charger l'historique au démarrage
        loadHistoryMessages();

        // Initialiser l'état de la sidebar
        updateSidebarState();

        // Effet de frappe automatique pour le message de bienvenue (seulement si pas d'historique)
        if (channelHistoryFromDB.length === 0) {
            setTimeout(() => {
                const welcomeMessage = document.querySelector('.message.ai .message-content');
                if (welcomeMessage) {
                    const text = welcomeMessage.textContent;
                    welcomeMessage.textContent = '';

                    let i = 0;
                    const typeInterval = setInterval(() => {
                        welcomeMessage.textContent += text[i];
                        i++;
                        if (i >= text.length) {
                            clearInterval(typeInterval);
                        }
                    }, 50);
                }
            }, 500);
        }
    </script>
</body>

</html>

<script type="text/javascript" src="scripts/nav.js"></script>
<script type="text/javascript" src="scripts/account.js"></script>