<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Remplace cette clé par ta clé Mistral API personnelle
$apiKey = 'OX4fzStQrzPd2PfyCAl7PR6ip3bcsvey';

$ch = curl_init();

$data = [
    "model" => "mistral-tiny", // Ou mistral-small, mistral-medium selon ton accès
    "prompt" => "Dis-moi une blague !",
    "temperature" => 0.7,
    "max_tokens" => 200
];

curl_setopt($ch, CURLOPT_URL, 'https://api.mistral.ai/v1/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Erreur CURL : ' . curl_error($ch);
} else {
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $result = json_decode($response, true);

    if ($httpCode !== 200) {
        echo "Erreur API (HTTP $httpCode) :<br>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    } elseif (isset($result['choices'][0]['text'])) {
        echo nl2br(htmlspecialchars($result['choices'][0]['text']));
    } else {
        echo "Réponse inattendue de l'API :<br>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
}

curl_close($ch);
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Ultra Moderne</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .chat-container {
            width: 90%;
            max-width: 800px;
            height: 85vh;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-header {
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
            padding: 20px;
            text-align: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .chat-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message {
            max-width: 80%;
            padding: 15px 20px;
            border-radius: 20px;
            font-size: 16px;
            line-height: 1.5;
            animation: messageSlide 0.4s ease-out;
            position: relative;
            word-wrap: break-word;
        }

        @keyframes messageSlide {
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
            align-self: flex-end;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-bottom-right-radius: 5px;
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
        }

        .message.ai {
            align-self: flex-start;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            border-bottom-left-radius: 5px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .message::before {
            content: '';
            position: absolute;
            bottom: 0;
            width: 0;
            height: 0;
        }

        .message.user::before {
            right: -10px;
            border-left: 10px solid transparent;
            border-top: 10px solid #764ba2;
        }

        .message.ai::before {
            left: -10px;
            border-right: 10px solid transparent;
            border-top: 10px solid rgba(255, 255, 255, 0.9);
        }

        .chat-input-container {
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .chat-input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .chat-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .send-button {
            padding: 15px 25px;
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .send-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
        }

        .send-button:active {
            transform: translateY(-1px);
        }

        .loading {
            display: none;
            align-self: flex-start;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            border-bottom-left-radius: 5px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .loading-dots {
            display: flex;
            gap: 4px;
        }

        .loading-dot {
            width: 8px;
            height: 8px;
            background: #667eea;
            border-radius: 50%;
            animation: loadingDots 1.4s infinite ease-in-out;
        }

        .loading-dot:nth-child(1) { animation-delay: -0.32s; }
        .loading-dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes loadingDots {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        .error-message {
            background: linear-gradient(135deg, #ff6b6b, #ff5252) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Scrollbar personnalisée */
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .chat-container {
                width: 95%;
                height: 90vh;
                border-radius: 15px;
            }
            
            .message {
                max-width: 90%;
                font-size: 14px;
            }
            
            .chat-header {
                font-size: 20px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div>🤖 Chat Ultra Moderne</div>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="message ai">
                👋 Salut ! Je suis ton assistant IA. Comment puis-je t'aider aujourd'hui ?
            </div>
        </div>
        
        <div class="loading" id="loadingIndicator">
            <div class="loading-dots">
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
            </div>
        </div>
        
        <div class="chat-input-container">
            <input type="text" class="chat-input" id="messageInput" placeholder="Tapez votre message ici..." maxlength="500">
            <button class="send-button" id="sendButton">Envoyer</button>
        </div>
    </div>

    <script>
        const messagesContainer = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const loadingIndicator = document.getElementById('loadingIndicator');

        // Configuration - Remplacez par votre vraie clé API OpenAI
        const API_KEY = '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';

        let conversationHistory = [];

        function addMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'ai'}`;
            messageDiv.textContent = content;
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            // Ajouter à l'historique
            conversationHistory.push({
                role: isUser ? 'user' : 'assistant',
                content: content
            });
        }

        function showLoading(show = true) {
            loadingIndicator.style.display = show ? 'block' : 'none';
            if (show) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }

        function addErrorMessage(error) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message ai error-message';
            messageDiv.textContent = `❌ Erreur: ${error}`;
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            // Ajouter le message utilisateur
            addMessage(message, true);
            messageInput.value = '';
            
            // Afficher l'indicateur de chargement
            showLoading(true);
            
            // Désactiver le bouton d'envoi
            sendButton.disabled = true;
            sendButton.textContent = 'Envoi...';

            try {
                // Préparer les messages pour l'API
                const messages = [
                    { role: 'system', content: 'Tu es un assistant IA sympathique et utile. Réponds de manière concise et engageante.' },
                    ...conversationHistory.slice(-10), // Garder les 10 derniers messages
                    { role: 'user', content: message }
                ];

                const response = await fetch('https://api.openai.com/v1/chat/completions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${API_KEY}`
                    },
                    body: JSON.stringify({
                        model: 'gpt-3.5-turbo',
                        messages: messages,
                        max_tokens: 500,
                        temperature: 0.7
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error?.message || `Erreur HTTP ${response.status}`);
                }

                if (data.choices && data.choices[0] && data.choices[0].message) {
                    const aiResponse = data.choices[0].message.content;
                    showLoading(false);
                    addMessage(aiResponse, false);
                } else {
                    throw new Error('Réponse inattendue de l\'API');
                }

            } catch (error) {
                console.error('Erreur:', error);
                showLoading(false);
                addErrorMessage(error.message || 'Erreur de connexion');
            } finally {
                // Réactiver le bouton d'envoi
                sendButton.disabled = false;
                sendButton.textContent = 'Envoyer';
                messageInput.focus();
            }
        }

        // Event listeners
        sendButton.addEventListener('click', sendMessage);
        
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Focus sur l'input au chargement
        messageInput.focus();

        // Empêcher l'envoi de messages vides
        messageInput.addEventListener('input', () => {
            sendButton.disabled = !messageInput.value.trim();
        });

        // Animation d'entrée
        setTimeout(() => {
            document.querySelector('.chat-container').style.opacity = '1';
        }, 100);
    </script>
</body>
</html>