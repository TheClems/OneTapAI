// Gestion du thème
function toggleTheme() {
    const body = document.body;
    const currentTheme = body.getAttribute('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';

    body.setAttribute('data-theme', newTheme);

    // Mise à jour du texte du bouton
    const themeButtons = document.querySelectorAll('.theme-toggle');
    themeButtons.forEach(button => {
        button.textContent = newTheme === 'light' ? '🌙' : '☀️';
    });

    // Sauvegarde dans le localStorage (si disponible)
    try {
        localStorage.setItem('theme', newTheme);
    } catch (e) {
        // localStorage non disponible, on ignore
    }
}

// Chargement du thème sauvegardé
function loadTheme() {
    try {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.body.setAttribute('data-theme', savedTheme);
            const themeButtons = document.querySelectorAll('.theme-toggle');
            themeButtons.forEach(button => {
                button.textContent = savedTheme === 'light' ? '🌙' : '☀️';
            });
        }
    } catch (e) {
        // localStorage non disponible, on utilise le thème par défaut
    }
}

// Gestion de la navigation
function showContent(pageId, linkElement) {
    // Masquer toutes les pages
    const pages = document.querySelectorAll('.content-page');
    pages.forEach(page => page.style.display = 'none');

    // Afficher la page sélectionnée
    document.getElementById(pageId).style.display = 'block';

    // Mettre à jour les liens actifs
    const links = document.querySelectorAll('.nav-link');
    links.forEach(link => link.classList.remove('active'));
    linkElement.classList.add('active');

    // Fermer le menu mobile si ouvert
    closeMobileMenu();
}

// Gestion du menu mobile
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.overlay');

    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
}

function closeMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.overlay');

    sidebar.classList.remove('open');
    overlay.classList.remove('active');
}

// Initialisation
document.addEventListener('DOMContentLoaded', function () {
    loadTheme();

    // Gestion des liens avec ancres
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const linkElement = document.querySelector(`[href="#${hash}"]`);
        if (linkElement) {
            showContent(hash, linkElement);
        }
    }
});

// Fermer le menu mobile lors du redimensionnement
window.addEventListener('resize', function () {
    if (window.innerWidth > 768) {
        closeMobileMenu();
    }
});