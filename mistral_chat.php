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

// Fonction pour récupérer la liste des channels
function getUserChannels($userId) {
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("
            SELECT 
                cc.id, 
                cc.created_at,
                COALESCE(
                    (SELECT content FROM chat_messages WHERE chat_channel_id = cc.id AND role = 'user' ORDER BY created_at ASC LIMIT 1),
                    'Nouveau chat'
                ) as first_message
            FROM chat_channels cc 
            WHERE cc.id_user = ? 
            ORDER BY cc.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur récupération channels: " . $e->getMessage());
        return [];
    }
}

$channelHistory = [];
$currentChannelId = null;
$userChannels = getUserChannels($userId);

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
            color: #e0e0e0;
            height: 100vh;
            overflow: hidden;
        }

        .main-container {
            display: flex;
            height: 100vh;
            margin: 0 auto;
            background: rgba(15, 15, 25, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);

            margin-left: 17rem;
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }

        .main-container.nav-collapsed {
            margin-left: 2rem;
        }

        .main-container.mobile {
            margin-left: 0rem;
        }

        /* Panneau historique des chats */
        .chat-history-panel {
            width: 320px;
            background: rgba(10, 10, 20, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            position: relative;
            overflow: hidden;
            margin-left: 0rem;
        }

        .chat-history-panel.collapsed {

            border-right: none;
            margin-left: -15rem;
        }

        .history-header {
            padding: 20px;
            background: linear-gradient(135deg, #1e1b4b, #312e81);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .history-header h3 {
            font-size: 1.1em;
            font-weight: 600;
            color: #e0e0e0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .new-chat-btn {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 20px;
            color: white;
            padding: 8px 16px;
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .new-chat-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }

        .toggle-history-btn {
            position: absolute;
            top: 20px;
            right: -40px;
            background: rgba(15, 15, 25, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0 8px 8px 0;
            color: #e0e0e0;
            padding: 10px 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .toggle-history-btn:hover {
            background: rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.5);
        }

        .chat-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            scrollbar-width: thin;
            scrollbar-color: #6366f1 transparent;
        }

        .chat-list::-webkit-scrollbar {
            width: 6px;
        }

        .chat-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-list::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #6366f1, #8b5cf6);
            border-radius: 10px;
        }

        .chat-item {
            background: rgba(30, 30, 45, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .chat-item:hover {
            background: rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.5);
            transform: translateX(4px);
        }

        .chat-item.active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(139, 92, 246, 0.3));
            border-color: rgba(99, 102, 241, 0.7);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.2);
        }

        .chat-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(to bottom, #6366f1, #8b5cf6);
        }

        .chat-preview {
            color: #e0e0e0;
            font-size: 0.9em;
            line-height: 1.4;
            margin-bottom: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        .chat-time {
            color: #9ca3af;
            font-size: 0.75em;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            flex: 1;
            background: rgba(15, 15, 25, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }

        .header {
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            padding: 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                left: -100%;
            }

            100% {
                left: 100%;
            }
        }

        .header h1 {
            font-size: 2em;
            font-weight: 600;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: black;
            scrollbar-width: thin;
            scrollbar-color: #6366f1 transparent;
        }

        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #6366f1, #8b5cf6);
            border-radius: 10px;
        }

        .message {
            margin-bottom: 20px;
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards;
        }

        @keyframes fadeInUp {
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

        .message-content {
            display: inline-block;
            max-width: 70%;
            padding: 15px 20px;
            border-radius: 20px;
            position: relative;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            word-wrap: break-word;
            line-height: 1.5;
        }

        /* Styles pour le formatage du texte */
        .message-content h1, .message-content h2, .message-content h3, 
        .message-content h4, .message-content h5, .message-content h6 {
            color: #8b5cf6;
            margin: 15px 0 10px 0;
            font-weight: 600;
        }

        .message-content h1 { font-size: 1.5em; }
        .message-content h2 { font-size: 1.3em; }
        .message-content h3 { font-size: 1.2em; }

        .message-content strong, .message-content b {
            color: #a78bfa;
            font-weight: 700;
        }

        .message-content em, .message-content i {
            color: #c4b5fd;
            font-style: italic;
        }

        .message-content code {
            background: rgba(15, 15, 25, 0.8);
            color: #10b981;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .message-content pre {
            background: rgba(15, 15, 25, 0.9);
            border: 1px solid rgba(16, 185, 129, 0.4);
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            overflow-x: auto;
            position: relative;
        }

        .message-content pre code {
            background: none;
            border: none;
            padding: 0;
            color: #10b981;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }

        .message-content pre::before {
            content: '💻 Code';
            position: absolute;
            top: -10px;
            left: 10px;
            background: rgba(15, 15, 25, 0.9);
            color: #10b981;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .message-content ul, .message-content ol {
            margin: 10px 0;
            padding-left: 20px;
        }

        .message-content li {
            margin: 5px 0;
            color: #e0e0e0;
        }

        .message-content blockquote {
            border-left: 3px solid #8b5cf6;
            padding-left: 15px;
            margin: 15px 0;
            color: #c4b5fd;
            font-style: italic;
        }

        .message-content a {
            color: #6366f1;
            text-decoration: underline;
        }

        .message-content a:hover {
            color: #8b5cf6;
        }

        /* Style pour les emojis */
        .message-content .emoji {
            font-size: 1.2em;
        }

        .message.user .message-content {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border-bottom-right-radius: 5px;
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.3);
        }

        .message.ai .message-content {
            background: rgba(30, 30, 45, 0.8);
            color: #e0e0e0;
            border-bottom-left-radius: 5px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .message-time {
            font-size: 0.8em;
            opacity: 0.6;
            margin-top: 5px;
        }

        .input-container {
            padding: 20px;
            background: rgba(15, 15, 25, 0.9);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .input-group {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .message-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            background: rgba(30, 30, 45, 0.8);
            color: #e0e0e0;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .message-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
        }

        .send-button {
            padding: 15px 25px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .send-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        .send-button:active {
            transform: translateY(0);
        }

        .send-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .loading-dots {
            display: inline-flex;
            gap: 8px;
        }

        .loading-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: linear-gradient(45deg, #6366f1, #8b5cf6);
            animation: bounce 1.4s infinite both;
        }

        .loading-dot:nth-child(1) {
            animation-delay: -0.32s;
        }

        .loading-dot:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {

            0%,
            80%,
            100% {
                transform: scale(0.8);
                opacity: 0.5;
            }

            40% {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        .error-message {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        /* Particules d'arrière-plan */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 50%;
            animation: float 20s infinite linear;
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

        /* Responsive */
        @media (max-width: 1024px) {
            .main-container {
                margin-left: 2rem;
            }
            
            .chat-history-panel {
                width: 280px;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                margin-left: 0;
                flex-direction: column;
            }

            .chat-history-panel {
                width: 100%;
                height: 40vh;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .chat-history-panel.collapsed {
                height: 0;
                border-bottom: none;
            }

            .toggle-history-btn {
                top: auto;
                bottom: -40px;
                right: 20px;
                left: auto;
                border-radius: 8px 8px 0 0;
            }

            .message-content {
                max-width: 85%;
            }

            .header h1 {
                font-size: 1.5em;
            }

            .input-group {
                gap: 10px;
            }

            .chat-container {
                flex: 1;
                min-height: 0;
            }
        }
    </style>
</head>

<body>
<?php require_once 'nav.php'; ?>

    <div class="particles" id="particles"></div>

    <div class="main-container" id="mainContainer">
        <!-- Panneau historique des chats -->
        <div class="chat-history-panel" id="chatHistoryPanel">
            <button class="toggle-history-btn" id="toggleHistoryBtn" title="Basculer l'historique">
                📊
            </button>
            
            <div class="history-header">
                <h3>
                    💬 Historique
                </h3>
                <button class="new-chat-btn" id="newChatBtn">
                    ➕ Nouveau
                </button>
            </div>
            
            <div class="chat-list" id="chatList">
                <?php foreach ($userChannels as $channel): ?>
                    <div class="chat-item <?php echo ($channel['id'] === $currentChannelId) ? 'active' : ''; ?>" 
                         data-channel-id="<?php echo htmlspecialchars($channel['id']); ?>">
                        <div class="chat-preview">
                            <?php echo htmlspecialchars(substr($channel['first_message'], 0, 50)) . (strlen($channel['first_message']) > 50 ? '...' : ''); ?>
                        </div>
                        <div class="chat-time">
                            🕒 <?php echo date('d/m H:i', strtotime($channel['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Zone de chat principale -->
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
const chatHistoryPanel = document.getElementById('chatHistoryPanel');
const toggleHistoryBtn = document.getElementById('toggleHistoryBtn');
const newChatBtn = document.getElementById('newChatBtn');
const mainContainer = document.getElementById('mainContainer');

// Historique des messages depuis PHP
let messageHistory = <?php echo json_encode(array_map(function($msg) {
    return [
        'role' => $msg['role'],
        'content' => $msg['content']
    ];
}, $channelHistory)); ?>;

// Historique des messages depuis la base de données
const channelHistoryFromDB = <?php echo json_encode($channelHistory); ?>;

// Fonction pour formater l'heure
function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Gérer le responsive basé sur la navbar
function updateLayoutForNavbar() {
    const nav = document.querySelector('nav') || document.querySelector('.nav') || document.querySelector('#nav');
    if (nav) {
        const navWidth = nav.offsetWidth;
        const isCollapsed = navWidth < 100; // Supposer que la navbar est collapsée si < 100px
        
        mainContainer.classList.toggle('nav-collapsed', isCollapsed);
    }
}

// Gérer le responsive mobile
function handleMobileLayout() {
    const isMobile = window.innerWidth <= 768;
    mainContainer.classList.toggle('mobile', isMobile);
    
    if (isMobile) {
        toggleHistoryBtn.textContent = chatHistoryPanel.classList.contains('collapsed') ? '📊' : '✖️';
    } else {
        toggleHistoryBtn.textContent = chatHistoryPanel.classList.contains('collapsed') ? '📊' : '📈';
    }
}

// Basculer l'affichage de l'historique
toggleHistoryBtn.addEventListener('click', () => {
    chatHistoryPanel.classList.toggle('collapsed');
    const isCollapsed = chatHistoryPanel.classList.contains('collapsed');
    
    if (window.innerWidth <= 768) {
        toggleHistoryBtn.textContent = isCollapsed ? '📊' : '✖️';
    } else {
        toggleHistoryBtn.textContent = isCollapsed ? '📊' : '📈';
    }
});

// Créer un nouveau chat
newChatBtn.addEventListener('click', () => {
    window.location.href = window.location.pathname; // Retour à la page sans paramètres
});

// Gérer les clics sur les éléments de chat
document.addEventListener('click', (e) => {
    const chatItem = e.target.closest('.chat-item');
    if (chatItem) {
        const channelId = chatItem.dataset.channelId;
        if (channelId) {
            window.location.href = `?id_channel=${channelId}`;
        }
    }
});

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

            // Rafraîchir la liste des chats si c'est le premier message
            if (messageHistory.length === 2) {
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }

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

// Gestion du redimensionnement
window.addEventListener('resize', () => {
    handleMobileLayout();
    updateLayoutForNavbar();
});

// Auto-focus sur l'input
messageInput.focus();

// Créer les particules
createParticles();

// Charger l'historique au démarrage
loadHistoryMessages();

// Initialiser le layout
handleMobileLayout();
updateLayoutForNavbar();

// Observer les changements de la navbar (si elle peut être toggle)
const navObserver = new MutationObserver(() => {
    updateLayoutForNavbar();
});

// Observer la navbar si elle existe
const nav = document.querySelector('nav') || document.querySelector('.nav') || document.querySelector('#nav');
if (nav) {
    navObserver.observe(nav, { 
        attributes: true, 
        attributeFilter: ['class', 'style'] 
    });
}

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

// Gestion du swipe sur mobile pour l'historique
let startY = 0;
let startX = 0;

chatHistoryPanel.addEventListener('touchstart', (e) => {
    startY = e.touches[0].clientY;
    startX = e.touches[0].clientX;
});

chatHistoryPanel.addEventListener('touchend', (e) => {
    if (!startY || !startX) return;
    
    const endY = e.changedTouches[0].clientY;
    const endX = e.changedTouches[0].clientX;
    const diffY = startY - endY;
    const diffX = startX - endX;
    
    // Swipe vertical sur mobile
    if (window.innerWidth <= 768 && Math.abs(diffY) > Math.abs(diffX) && Math.abs(diffY) > 50) {
        if (diffY > 0) {
            // Swipe up - fermer l'historique
            chatHistoryPanel.classList.add('collapsed');
            toggleHistoryBtn.textContent = '📊';
        } else {
            // Swipe down - ouvrir l'historique
            chatHistoryPanel.classList.remove('collapsed');
            toggleHistoryBtn.textContent = '✖️';
        }
    }
    
    startY = 0;
    startX = 0;
});
    </script>
</body>

</html>

<script type="text/javascript" src="scripts/nav.js"></script>
<script type="text/javascript" src="scripts/account.js"></script>