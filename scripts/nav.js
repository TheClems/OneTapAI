// Gestion du toggle de la sidebar
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggleBtn');
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const chatMessages = document.getElementById('chatMessages');
const body = document.body;

// Éléments avec vérification d'existence (évite les erreurs si certains éléments n'existent pas)
const welcome = document.querySelector('.welcome');
const infoValues = document.querySelectorAll('.info-value');
const infoCards = document.querySelectorAll('.info-card');
const profileCard = document.querySelector('.profile-card');
const infoLabels = document.querySelectorAll('.info-label');
const h2 = document.querySelectorAll('h2');
const packages = document.querySelectorAll('.package');
const backLink = document.querySelector('.back-link a');
const featuredPackage = document.querySelector('.package.featured');
const currentCredits = document.querySelector('.current-credits');
const demoNotice = document.querySelector('.demo-notice');
const chatContainer = document.querySelector('.chat-container');
const body_buy_credits = document.querySelector('.body_buy_credits');
const body_account = document.querySelector('.body_account');
const h1 = document.querySelector('h1');
const profileHeader = document.querySelector('.profile-header');
const actionsSection = document.querySelector('.actions-section');
const actionsSectionH3 = document.querySelector('.actions-section h3');
const toggleBtn2 = document.querySelector('.toggle-btn');
const chatHistoryPanel2 = document.querySelector('.chat-history-panel');
const chatList = document.querySelectorAll('.chat-list');
const chatItem = document.querySelectorAll('.chat-item');
const chatPreview = document.querySelectorAll('.chat-preview');
const chatModel = document.querySelectorAll('.chat-model');
const chatTime = document.querySelectorAll('.chat-time');
const h3 = document.querySelectorAll('h3');
const historyHeader = document.querySelectorAll('.history-header');


const elementsToToggle = [
    ...infoValues,
    ...infoCards,
    welcome,
    profileCard,
    ...infoLabels,
    ...h2,
    backLink,
    ...packages,
    featuredPackage,
    currentCredits,
    demoNotice,
    h1,
    profileHeader,
    actionsSection,
    actionsSectionH3,
    toggleBtn2,
    chatHistoryPanel2,
    ...chatList,
    ...chatItem,
    ...chatPreview,
    ...chatModel,
    ...chatTime,
    ...h3,
    ...historyHeader,
    chatMessages
];



// Gestion du thème
let isDarkMode = true;

// Fonction pour sauvegarder le thème (avec vérification si currentUserId existe)
function saveTheme() {
        const selectedTheme = isDarkMode ? 1 : 0;

        fetch('https://onetapai.ctts.fr/theme.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ theme: selectedTheme }) // pas besoin d'envoyer l'id
        })
        
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    console.log('Thème mis à jour en base');
                } else {
                    console.error('Erreur serveur:', data.error);
                }
            })
            .catch(error => {
                console.error('Fetch failed:', error);
            });
    
}

// Toggle du thème
if (isDarkModeFromServer) {
    body.classList.remove('light-mode');



    elementsToToggle.forEach(el => {
        if (el) el.classList.remove('light-mode');
    });

    if (themeIcon) {
        themeIcon.innerHTML = '<path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>';
    }

    isDarkMode = true;
    console.log('Thème initialisé en mode sombre');
}else{
    body.classList.add('light-mode');



    elementsToToggle.forEach(el => {
        if (el) el.classList.add('light-mode');
    });

    if (themeIcon) {
        themeIcon.innerHTML = '<path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>';
    }

    isDarkMode = false;
    console.log('Thème initialisé en mode clair');
}


if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        isDarkMode = !isDarkMode;
        body.classList.toggle('light-mode');


        elementsToToggle.forEach(el => {
            if (el) el.classList.toggle('light-mode');
        });

        // Changer l'icône
        if (themeIcon) {
            if (isDarkMode) {
                themeIcon.innerHTML = '<path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>';
            } else {
                themeIcon.innerHTML = '<path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>';
            }
        }

        // Sauvegarder le thème
        saveTheme();
    });
}

// Toggle de la sidebar
if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        if (sidebar) sidebar.classList.toggle('collapsed');
        if (chatContainer) chatContainer.classList.toggle('collapsed');
        if (body_buy_credits) body_buy_credits.classList.toggle('collapsed');
        if (body_account) body_account.classList.toggle('collapsed');
        if (toggleBtn2) toggleBtn2.classList.toggle('collapsed');

        if (chatHistoryPanel2) chatHistoryPanel2.classList.toggle('collapsed');
    });
}

// Gestion des liens actifs - VERSION CORRIGÉE
document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-link');

    // Fonction pour définir le lien actif
    function setActiveLink() {
        const currentPath = window.location.pathname;
        const currentFile = currentPath.split('/').pop();

        console.log('=== DEBUG NAVIGATION ===');
        console.log('Chemin:', currentPath);
        console.log('Fichier:', currentFile);

        // Supprimer toutes les classes active
        navLinks.forEach(l => l.classList.remove('active'));

        // Trouver et activer le lien correspondant
        let linkFound = false;
        navLinks.forEach(link => {
            const href = link.getAttribute('href');

            if (href === currentFile ||
                href === currentPath ||
                currentPath.endsWith('/' + href) ||
                (currentFile === '' && href === 'test.php')) {

                link.classList.add('active');
                console.log('✅ Lien activé:', href);
                linkFound = true;
            }
        });

        if (!linkFound) {
            console.log('❌ Aucun lien actif trouvé');
        }
    }

    // Définir le lien actif au chargement
    setActiveLink();

    // Gestion des clics
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            console.log('Navigation vers:', link.getAttribute('href'));
        });
    });
});

// Création des particules flottantes
function createParticles() {
    const particlesContainer = document.getElementById('particles');
    if (!particlesContainer) return; // Éviter les erreurs si l'élément n'existe pas

    const particleCount = 15;

    for (let i = 0; i < particleCount; i++) {
        setTimeout(() => {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 6 + 's';
            particle.style.animationDuration = (Math.random() * 3 + 4) + 's';
            particlesContainer.appendChild(particle);

            // Supprimer la particule après l'animation
            setTimeout(() => {
                if (particle.parentNode) {
                    particle.parentNode.removeChild(particle);
                }
            }, 8000);
        }, i * 400);
    }
}

// Lancer les particules
createParticles();
setInterval(createParticles, 6000);

// Gestion responsive
function handleResize() {
    if (sidebar && window.innerWidth <= 768) {
        if (body_buy_credits) body_buy_credits.classList.add('mobile');

        if (sidebar) sidebar.classList.add('mobile');
        if (chatContainer) chatContainer.classList.add('mobile');
        if (body_account) body_account.classList.add('mobile');

    } else if (sidebar) {
        if (body_buy_credits) body_buy_credits.classList.remove('mobile');
        if (sidebar) sidebar.classList.remove('mobile');
        if (chatContainer) chatContainer.classList.remove('mobile');
        if (body_account) body_account.classList.remove('mobile');
    }
}

window.addEventListener('resize', handleResize);
handleResize();

// Animation au chargement
window.addEventListener('load', () => {
    document.body.style.opacity = '1';
});