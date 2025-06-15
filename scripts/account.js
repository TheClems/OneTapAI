// Initialiser Lucide icons
lucide.createIcons();

// Gestion du thème sombre
function toggleTheme() {
    document.body.classList.toggle('dark-mode');
    const icon = document.getElementById('theme-icon');
    
    if (document.body.classList.contains('dark-mode')) {
        icon.setAttribute('data-lucide', 'moon');
        localStorage.setItem('theme', 'dark');
    } else {
        icon.setAttribute('data-lucide', 'sun');
        localStorage.setItem('theme', 'light');
    }
    
    lucide.createIcons();
}

// Charger le thème sauvegardé
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        document.getElementById('theme-icon').setAttribute('data-lucide', 'moon');
        lucide.createIcons();
    }
});

// Animation des statistiques au scroll
function animateStats() {
    const statValues = document.querySelectorAll('.stat-value');
    
    statValues.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        let currentValue = 0;
        const increment = finalValue / 50;
        
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                stat.textContent = finalValue;
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(currentValue);
            }
        }, 30);
    });
}

// Observer pour déclencher les animations
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
            entry.target.classList.add('animated');
            if (entry.target.classList.contains('stats-grid')) {
                setTimeout(() => animateStats(), 500);
            }
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const statsGrid = document.querySelector('.stats-grid');
    if (statsGrid) {
        observer.observe(statsGrid);
    }
});

// Effet de parallaxe sur les éléments flottants
document.addEventListener('mousemove', function(e) {
    const floatingElements = document.querySelectorAll('.floating-element');
    const x = e.clientX / window.innerWidth;
    const y = e.clientY / window.innerHeight;
    
    floatingElements.forEach((element, index) => {
        const speed = (index + 1) * 0.5;
        const xPos = x * speed * 20;
        const yPos = y * speed * 20;
        
        element.style.transform = `translate(${xPos}px, ${yPos}px)`;
    });
});


document.addEventListener('DOMContentLoaded', function() {
    // Test des liens de navigation
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            console.log('Lien cliqué:', this.href);
            // Décommentez la ligne suivante pour empêcher la navigation pendant les tests
            // e.preventDefault();
        });
    });
});




//button deconexion
function createParticles(button) {
    const rect = button.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;

    for (let i = 0; i < 8; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = centerX + 'px';
        particle.style.top = centerY + 'px';
        
        const angle = (Math.PI * 2 * i) / 8;
        const velocity = 2;
        particle.style.setProperty('--dx', Math.cos(angle) * velocity + 'px');
        particle.style.setProperty('--dy', Math.sin(angle) * velocity + 'px');
        
        document.body.appendChild(particle);
        
        setTimeout(() => {
            particle.remove();
        }, 2000);
    }
}

function handleLogout(button) {
    // Ajout de la classe loading
    button.classList.add('loading');
    button.querySelector('.logout-text').textContent = 'Déconnexion...';
    
    // Création des particules
    createParticles(button);
    
    // Simulation de la déconnexion
    setTimeout(() => {
        button.classList.remove('loading');
        button.querySelector('.logout-text').textContent = 'Déconnecté !';
        button.style.background = 'linear-gradient(135deg, #48bb78, #38a169)';
        
        // Reset après 2 secondes
        setTimeout(() => {
            button.querySelector('.logout-text').textContent = 'Se déconnecter';
            button.style.background = '';
        }, 2000);
    }, 1500);
}

// Event listeners
document.getElementById('logoutBtn').addEventListener('click', function() {
    handleLogout(this);
});

document.getElementById('logoutBtnAlt').addEventListener('click', function() {
    handleLogout(this);
});

document.getElementById('profileBtn').addEventListener('click', function() {
    handleProfileEdit(this);
});

document.getElementById('deleteBtn').addEventListener('click', function() {
    handleAccountDelete(this);
});

function handleProfileEdit(button) {
    button.classList.add('loading');
    button.querySelector('.logout-text').textContent = 'Chargement...';
    
    createParticles(button);
    
    setTimeout(() => {
        button.classList.remove('loading');
        button.querySelector('.logout-text').textContent = 'Profil ouvert !';
        button.style.background = 'linear-gradient(135deg, #48bb78, #38a169)';
        
        setTimeout(() => {
            button.querySelector('.logout-text').textContent = 'Modifier profil';
            button.style.background = '';
        }, 2000);
    }, 1000);
}

function handleAccountDelete(button) {
    // Confirmation avant suppression
    if (!confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
        return;
    }
    
    button.classList.add('loading');
    button.querySelector('.logout-text').textContent = 'Suppression...';
    
    createParticles(button);
    
    setTimeout(() => {
        button.classList.remove('loading');
        button.querySelector('.logout-text').textContent = 'Compte supprimé';
        button.style.background = 'linear-gradient(135deg, #e53e3e, #c53030)';
        button.style.pointerEvents = 'none';
        
        setTimeout(() => {
            button.querySelector('.logout-text').textContent = 'Supprimer compte';
            button.style.background = '';
            button.style.pointerEvents = '';
        }, 3000);
    }, 2000);
}

// Effet de survol avec son (optionnel)
document.querySelectorAll('.logout-btn').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
        // Vous pouvez ajouter un son ici si nécessaire
        // new Audio('hover-sound.mp3').play();
    });
});