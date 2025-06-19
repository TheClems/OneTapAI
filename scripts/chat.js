// Fonction pour afficher une image dans le chat
function displayImageMessage(imagePath, isUser = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isUser ? 'user' : 'ai'}`;

    const now = new Date();
    const timeString = now.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });

    // Créer l'élément image avec gestion d'erreur
    const imageHtml = `
        <div class="message-content">
            <img src="${imagePath}" 
                 alt="Image générée" 
                 style="max-width: 100%; height: auto; border-radius: 8px; cursor: pointer;"
                 onclick="window.open('${imagePath}', '_blank')"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <div style="display: none; padding: 10px; background: #f0f0f0; border-radius: 8px; color: #666;">
                ❌ Erreur de chargement de l'image<br>
                <small>Chemin: ${imagePath}</small>
            </div>
        </div>
        <div class="message-time">${timeString}</div>
    `;

    messageDiv.innerHTML = imageHtml;
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Fonction pour déterminer si c'est une réponse d'image
function isImageResponse(content) {
    return content && (
        content.startsWith('image_api/') ||
        content.includes('.jpg') ||
        content.includes('.jpeg') ||
        content.includes('.png') ||
        content.includes('.gif') ||
        content.includes('.webp')
    );
}

//Envoyer message - Version mise à jour
async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const chatMessages = document.getElementById('chatMessages');
    const loading = document.getElementById('loading');

    const message = messageInput.value.trim();
    if (!message) return;

    let selectedModel = getSelectedModel();

    // Si des instructions personnalisées existent, forcer un modèle spécifique
    if (typeof personaInstructions !== 'undefined' &&
        typeof personaInstructions === 'string' &&
        personaInstructions.trim() !== '' &&
        typeof selectedModelPersona !== 'undefined') {
        selectedModel = selectedModelPersona;
    }

    // Déterminer l'endpoint API à utiliser
    let apiEndpoint;
    switch (selectedModel) {
        case 'gemini': apiEndpoint = 'gemini_api.php'; break;
        case 'openrouter': apiEndpoint = 'openrouter_api.php'; break;
        case 'mistral-medium': apiEndpoint = 'mistral_api.php'; break;
        case 'deepseek': apiEndpoint = 'deepseek_api.php'; break;
        case 'gpt': apiEndpoint = 'gpt_api.php'; break;
        case 'image': apiEndpoint = 'image_api.php'; break; // Utiliser votre nouveau fichier
        default: apiEndpoint = 'default_api.php'; break;
    }

    // Désactiver l'interface pendant l'envoi
    messageInput.disabled = true;
    sendButton.disabled = true;
    loading.style.display = 'block';

    displayMessage(message, true);
    messageInput.value = '';

    messageHistory.push({
        role: 'user',
        content: message
    });

    try {
        const urlParams = new URLSearchParams(window.location.search);
        const chatChannelId = urlParams.get('id_channel');

        let messagesToSend = [...messageHistory];

        // Ajouter instructions si elles existent
        if (typeof personaInstructions !== 'undefined' &&
            typeof personaInstructions === 'string' &&
            personaInstructions.trim() !== '' &&
            typeof personaNom !== 'undefined' &&
            typeof personaTags !== 'undefined') {

            messagesToSend = [{
                role: 'system',
                content: `Tu es ${personaNom}, Utilise tes compétences en ${personaTags}. ${personaInstructions.trim()}`
            }, ...messagesToSend];
        }

        const response = await fetch(apiEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                messages: messagesToSend,
                chat_channel_id: chatChannelId
            })
        });

        // Vérifier si la réponse est OK
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status} - ${response.statusText}`);
        }

        // Lire le contenu de la réponse
        const responseText = await response.text();
        
        // Vérifier si le contenu est vide
        if (!responseText || responseText.trim() === '') {
            throw new Error('Réponse vide du serveur');
        }

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('Erreur JSON:', jsonError);
            console.error('Contenu de la réponse:', responseText);
            throw new Error('Réponse invalide du serveur (JSON malformé)');
        }

        if (data.success) {
            // Vérifier si c'est une réponse d'image
            if (data.type === 'image' || isImageResponse(data.content)) {
                displayImageMessage(data.content, false);
                messageHistory.push({
                    role: 'assistant',
                    content: `[IMAGE: ${data.content}]`
                });
            } else {
                // Réponse texte normale
                displayMessage(data.content, false);
                messageHistory.push({
                    role: 'assistant',
                    content: data.content.replace(/<br\s*\/?>/gi, '\n')
                });
            }
        } else {
            throw new Error(data.error || 'Erreur inconnue');
        }

    } catch (error) {
        console.error('Erreur complète:', error);
        
        // Afficher un message d'erreur plus détaillé
        let errorMessage = 'Désolé, une erreur s\'est produite';
        
        if (error.message.includes('JSON')) {
            errorMessage += ' (erreur de format de réponse)';
        } else if (error.message.includes('HTTP')) {
            errorMessage += ' (erreur de connexion)';
        } else if (error.message.includes('vide')) {
            errorMessage += ' (réponse vide du serveur)';
        }
        
        errorMessage += ` : ${error.message}`;
        
        displayMessage(errorMessage, false);
    } finally {
        messageInput.disabled = false;
        sendButton.disabled = false;
        loading.style.display = 'none';
        messageInput.focus();
    }
}

// Fonction pour formater le contenu d'un message (mise à jour pour les images)
function formatMessageContent(content) {
    // Vérifier si c'est un chemin d'image
    if (isImageResponse(content)) {
        return `<img src="${content}" alt="Image générée" style="max-width: 100%; height: auto; border-radius: 8px; cursor: pointer;" onclick="window.open('${content}', '_blank')">`;
    }
    
    // Vérifier si c'est un marqueur d'image dans l'historique
    if (content.startsWith('[IMAGE:') && content.endsWith(']')) {
        const imagePath = content.slice(8, -1); // Extraire le chemin entre [IMAGE: et ]
        return `<img src="${imagePath}" alt="Image générée" style="max-width: 100%; height: auto; border-radius: 8px; cursor: pointer;" onclick="window.open('${imagePath}', '_blank')">`;
    }
    
    // Formatage normal pour le texte
    content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');
    content = content.replace(/^###### (.*$)/gm, '<h6>$1</h6>');
    content = content.replace(/^##### (.*$)/gm, '<h5>$1</h5>');
    content = content.replace(/^#### (.*$)/gm, '<h4>$1</h4>');
    content = content.replace(/^### (.*$)/gm, '<h3>$1</h3>');
    content = content.replace(/^## (.*$)/gm, '<h2>$1</h2>');
    content = content.replace(/^# (.*$)/gm, '<h1>$1</h1>');
    content = content.replace(/```(\w+)?\n?([\s\S]*?)```/g, function(match, lang, code) {
        return `<pre><code>${code.trim()}</code></pre>`;
    });
    content = content.replace(/`([^`]+)`/g, '<code>$1</code>');
    content = content.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');
    content = content.replace(/^- (.*$)/gm, '<li>$1</li>');
    content = content.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
    content = content.replace(/^\d+\. (.*$)/gm, '<li>$1</li>');
    content = content.replace(/^> (.*$)/gm, '<blockquote>$1</blockquote>');
    content = content.replace(/\n/g, '<br>');
    
    return content;
}