// Gestion du toggle de la sidebar
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggleBtn');
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const body = document.body;
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
// Gestion du thème
// Gestion du thème
let isDarkMode = true;
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
    demoNotice
];
themeToggle.addEventListener('click', () => {
    isDarkMode = !isDarkMode;
    body.classList.toggle('light-mode');

    elementsToToggle.forEach(el => {
        if (el) el.classList.toggle('light-mode');
    });

    if (isDarkMode) {
        themeIcon.innerHTML = '<path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>';
    } else {
        themeIcon.innerHTML = '<path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>';
    }
});

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
});
const selectedTheme = isDarkMode ? 0 : 1;  // 0 = dark, 1 = light, ou selon ta convention


fetch('https://onetapai.ctts.fr/theme.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ theme: selectedTheme, user_id: currentUserId })
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


// Gestion des liens actifs - VERSION CORRIGÉE
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        // NE PAS empêcher la navigation
        // e.preventDefault(); // ← Ligne supprimée
        
        // Mettre à jour les classes actives
        navLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');
        
        // Sauvegarder l'état actif dans localStorage (optionnel)
        localStorage.setItem('activeNavLink', link.getAttribute('href'));
        
        // Laisser la navigation se faire normalement
        console.log('Navigation vers:', link.getAttribute('href'));
    });
});

// Restaurer le lien actif au chargement de la page (optionnel)

window.addEventListener('DOMContentLoaded', () => {
    const currentPath = window.location.pathname;
    console.log('Page chargée :', currentPath);

    navLinks.forEach(link => {
        const linkPath = new URL(link.href, window.location.origin).pathname;
        console.log('Lien:', link.href, '→', linkPath);

        if (linkPath === currentPath) {
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            console.log('Lien actif restauré:', link.href);
        }
    });
});


// Création des particules flottantes
function createParticles() {
    const particlesContainer = document.getElementById('particles');
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
    if (window.innerWidth <= 768) {
        sidebar.classList.add('mobile');
    } else {
        sidebar.classList.remove('mobile');
    }
}

window.addEventListener('resize', handleResize);
handleResize();

// Animation au chargement
window.addEventListener('load', () => {
    document.body.style.opacity = '1';
});
