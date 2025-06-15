<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

session_start();  // Toujours démarrer la session en début de script

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    // Tu peux maintenant utiliser $userId
} else {
    // Pas d'utilisateur connecté ou ID non stocké en session
    $userId = null;
}


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
}
?>



<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mistral AI Chat</title>
    <link rel="stylesheet" href="css/nav.css" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            color: #e0e0e0;
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(15, 15, 25, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
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
            background: rgba(10, 10, 20, 0.5);
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

        /* Responsive */
        @media (max-width: 768px) {
            .message-content {
                max-width: 85%;
            }

            .header h1 {
                font-size: 1.5em;
            }

            .input-group {
                gap: 10px;
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
    </style>
</head>

<body>
<nav class="sidebar" id="sidebar">
        <div class="floating-particles" id="particles"></div>

        <button class="toggle-btn" id="toggleBtn"></button>

        <div class="nav-header">
            <div class="logo">OneTapAI</div>
            <button class="theme-toggle" id="themeToggle">
                <svg class="theme-icon" id="themeIcon" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                </svg>
            </button>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="test.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                    <span class="nav-text">Accueil</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="test2.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="nav-text">Projets</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="test3.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                    </svg>
                    <span class="nav-text">Équipe</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                    </svg>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="buy_credits.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                    <span class="nav-text">Acheter des crédits</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="account.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                    <span class="nav-text">Mon Compte</span>
                </a>
            </li>

        </ul>
    </nav>
    <div class="particles" id="particles"></div>

    <div class="chat-container">
        <div class="header">
            <h1>🤖 Mistral AI Chat</h1>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="message ai">
                <div class="message-content">
                    Salut ! Je suis Mistral AI. Comment puis-je t'aider aujourd'hui ? 🚀
                </div>
                <div class="message-time" id="welcomeTime"></div>
            </div>
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

    <script>
        // Configuration
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const loading = document.getElementById('loading');

        // Historique des messages
        let messageHistory = [];

        // Afficher l'heure de bienvenue
        document.getElementById('welcomeTime').textContent = new Date().toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });

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

        // Ajouter un message au chat
        function addMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'ai'}`;

            const now = new Date();
            const timeString = now.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });

            messageDiv.innerHTML = `
                <div class="message-content">${content}</div>
                <div class="message-time">${timeString}</div>
            `;

            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Mise à jour de l'historique
            messageHistory.push({
                role: isUser ? 'user' : 'assistant',
                content: content
            });
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

            // Ajouter le message utilisateur
            addMessage(message, true);
            messageInput.value = '';

            // Désactiver l'interface
            sendButton.disabled = true;
            messageInput.disabled = true;
            loading.style.display = 'block';

            try {
                // Préparer les messages pour l'API (garder les 10 derniers)
                const messages = messageHistory.slice(-10);
                messages.push({
                    role: 'user',
                    content: message
                });

                const response = await fetch('mistral_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        messages: messages
                    })
                });

                const data = await response.json();

                if (data.success) {
                    addMessage(data.content);
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

        // Auto-focus sur l'input
        messageInput.focus();

        // Créer les particules
        createParticles();

        // Effet de frappe automatique pour le message de bienvenue
        setTimeout(() => {
            const welcomeMessage = document.querySelector('.message.ai .message-content');
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
        }, 500);
    </script>
</body>

</html>

<script type="text/javascript" src="scripts/nav.js"></script>
<script type="text/javascript" src="scripts/account.js"></script>