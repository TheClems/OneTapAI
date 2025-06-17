<html lang="fr"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OneTapAI - Navigation Bootstrap</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #3ef3c7;
            --primary-rgb: 62, 243, 199;
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #000;
            min-height: 100vh;
            overflow-x: hidden;
            transition: all 0.3s ease;
        }

        body.light-mode {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        /* Sidebar personnalisée */
        .custom-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: rgba(12, 12, 12, 0.92);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(var(--primary-rgb), 0.2);
            transform: translateX(0);
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            z-index: 1000;
            box-shadow: 0 25px 50px -12px rgba(var(--primary-rgb), 0.25);
        }

        body.light-mode .custom-sidebar {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }

        .custom-sidebar.collapsed {
            transform: translateX(-280px);
        }

        /* Toggle button */
        .toggle-btn {
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), #1de9b6);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(var(--primary-rgb), 0.3);
            transition: all 0.3s ease;
            z-index: 1001;
        }

        .toggle-btn.collapsed {
            right: -42px;
        }

        .toggle-btn:hover {
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        .toggle-btn::before {
            content: '';
            width: 16px;
            height: 2px;
            background: white;
            border-radius: 1px;
            box-shadow: 0 -5px 0 white, 0 5px 0 white;
            transition: all 0.3s ease;
        }

        .custom-sidebar.collapsed .toggle-btn::before {
            transform: rotate(180deg);
        }

        /* Header */
        .nav-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(var(--primary-rgb), 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: #e2e8f0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        body.light-mode .logo {
            color: #334155;
        }

        .theme-toggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: #e2e8f0;
        }

        body.light-mode .theme-toggle {
            color: #334155;
        }

        .theme-toggle:hover {
            background: rgba(var(--primary-rgb), 0.1);
            transform: scale(1.1);
        }

        /* Navigation */
        .nav-menu {
            padding: 2rem 0;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(226, 232, 240, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin: 0.5rem 0;
        }

        body.light-mode .nav-link-custom {
            color: rgba(51, 65, 85, 0.7);
        }

        .nav-link-custom::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--primary-color);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-link-custom:hover::before,
        .nav-link-custom.active::before {
            transform: scaleY(1);
        }

        .nav-link-custom:hover {
            color: #e2e8f0;
            background: rgba(var(--primary-rgb), 0.1);
            transform: translateX(8px);
        }

        body.light-mode .nav-link-custom:hover {
            color: #334155;
            background: rgba(var(--primary-rgb), 0.05);
        }

        .nav-link-custom.active {
            color: #e2e8f0;
            background: rgba(var(--primary-rgb), 0.15);
        }

        body.light-mode .nav-link-custom.active {
            color: #334155;
            background: rgba(var(--primary-rgb), 0.1);
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            margin-right: 1rem;
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .nav-link-custom:hover .nav-icon {
            opacity: 1;
            transform: scale(1.1);
        }

        .nav-text {
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: margin-left 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            color: #e2e8f0;
            min-height: 100vh;
        }

        body.light-mode .main-content {
            color: #334155;
        }

        .main-content.collapsed {
            margin-left: 0;
        }

        .content-card {
            background: rgba(var(--primary-rgb), 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(var(--primary-rgb), 0.2);
            box-shadow: 0 20px 40px rgba(var(--primary-rgb), 0.1);
        }

        body.light-mode .content-card {
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        }

        /* Particules flottantes */
        .floating-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(var(--primary-rgb), 0.3);
            border-radius: 50%;
            animation: float 6s infinite linear;
        }

        body.light-mode .particle {
            background: rgba(var(--primary-rgb), 0.2);
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) scale(1);
                opacity: 0;
            }
        }

        /* Animations d'entrée */
        .nav-item {
            opacity: 0;
            transform: translateY(20px);
            animation: slideInUp 0.6s ease forwards;
        }

        .nav-item:nth-child(1) { animation-delay: 0.05s; }
        .nav-item:nth-child(2) { animation-delay: 0.1s; }
        .nav-item:nth-child(3) { animation-delay: 0.15s; }
        .nav-item:nth-child(4) { animation-delay: 0.2s; }
        .nav-item:nth-child(5) { animation-delay: 0.25s; }
        .nav-item:nth-child(6) { animation-delay: 0.3s; }

        @keyframes slideInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .custom-sidebar {
                transform: translateX(-280px);
            }

            .custom-sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }

            .mobile-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body style="opacity: 1;" class="">
    <!-- Mobile overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Sidebar -->
    <nav class="custom-sidebar" id="sidebar">
        <div class="floating-particles" id="particles"><div class="particle" style="left: 75.5721%; animation-delay: 1.48516s; animation-duration: 6.6939s;"></div><div class="particle" style="left: 55.4085%; animation-delay: 5.66206s; animation-duration: 4.22237s;"></div><div class="particle" style="left: 65.1327%; animation-delay: 4.29028s; animation-duration: 6.89319s;"></div><div class="particle" style="left: 62.6426%; animation-delay: 4.10263s; animation-duration: 5.0728s;"></div><div class="particle" style="left: 20.0034%; animation-delay: 5.51666s; animation-duration: 6.71599s;"></div><div class="particle" style="left: 8.22361%; animation-delay: 3.18846s; animation-duration: 6.84553s;"></div><div class="particle" style="left: 72.9416%; animation-delay: 5.66247s; animation-duration: 5.92555s;"></div><div class="particle" style="left: 45.0734%; animation-delay: 4.24518s; animation-duration: 5.98917s;"></div><div class="particle" style="left: 46.4026%; animation-delay: 5.28092s; animation-duration: 6.39751s;"></div><div class="particle" style="left: 15.3951%; animation-delay: 2.03072s; animation-duration: 5.59476s;"></div><div class="particle" style="left: 94.0191%; animation-delay: 2.51104s; animation-duration: 6.71406s;"></div><div class="particle" style="left: 57.6945%; animation-delay: 3.28049s; animation-duration: 6.95067s;"></div><div class="particle" style="left: 69.3706%; animation-delay: 2.03498s; animation-duration: 5.75292s;"></div><div class="particle" style="left: 69.1463%; animation-delay: 4.22628s; animation-duration: 6.51942s;"></div><div class="particle" style="left: 15.4825%; animation-delay: 4.31005s; animation-duration: 5.21828s;"></div><div class="particle" style="left: 7.67477%; animation-delay: 5.64657s; animation-duration: 4.44043s;"></div><div class="particle" style="left: 23.5732%; animation-delay: 1.1055s; animation-duration: 4.98487s;"></div><div class="particle" style="left: 47.6458%; animation-delay: 4.16662s; animation-duration: 4.91975s;"></div><div class="particle" style="left: 23.4239%; animation-delay: 0.732651s; animation-duration: 6.60658s;"></div><div class="particle" style="left: 3.87215%; animation-delay: 3.08753s; animation-duration: 5.6337s;"></div></div>
        
        <button class="toggle-btn collapsed" id="toggleBtn"></button>

        <div class="nav-header">
            <div class="logo">OneTapAI</div>
            <button class="theme-toggle" id="themeToggle">
                <i class="bi bi-moon-fill" id="themeIcon"></i>
            </button>
        </div>

        <div class="nav-menu">
            <div class="nav-item">
                <a href="index.php" class="nav-link-custom" data-page="home">
                    <i class="bi bi-house-fill nav-icon"></i>
                    <span class="nav-text">Home</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link-custom active" data-page="projects">
                    <i class="bi bi-check-circle-fill nav-icon"></i>
                    <span class="nav-text">Projects</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="chat.php" class="nav-link-custom" data-page="chat">
                    <i class="bi bi-chat-dots-fill nav-icon"></i>
                    <span class="nav-text">Chat</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="dashboard.php" class="nav-link-custom" data-page="dashboard">
                    <i class="bi bi-grid-3x3-gap-fill nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="buy_credits.php" class="nav-link-custom" data-page="credits">
                    <i class="bi bi-credit-card-fill nav-icon"></i>
                    <span class="nav-text">Buy credits</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="account.php" class="nav-link-custom" data-page="account">
                    <i class="bi bi-gear-fill nav-icon"></i>
                    <span class="nav-text">Account</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Variables globales
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const mainContent = document.getElementById('mainContent');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const body = document.body;

        // Gestion du thème
        let isDarkMode = true;

        // Fonction pour sauvegarder le thème (simulation)
        function saveTheme() {
            const selectedTheme = isDarkMode ? 1 : 0;
            console.log('Thème sauvegardé:', selectedTheme ? 'Sombre' : 'Clair');
            // Ici vous pouvez ajouter votre appel AJAX vers theme.php
            /*
            fetch('https://onetapai.ctts.fr/theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ theme: selectedTheme })
            })
            .then(response => response.json())
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
            */
        }

        // Initialisation du thème
        function initTheme() {
            if (isDarkMode) {
                body.classList.remove('light-mode');
                themeIcon.className = 'bi bi-moon-fill';
            } else {
                body.classList.add('light-mode');
                themeIcon.className = 'bi bi-sun-fill';
            }
        }

        // Toggle du thème
        themeToggle.addEventListener('click', () => {
            isDarkMode = !isDarkMode;
            body.classList.toggle('light-mode');

            // Changer l'icône
            if (isDarkMode) {
                themeIcon.className = 'bi bi-moon-fill';
            } else {
                themeIcon.className = 'bi bi-sun-fill';
            }

            saveTheme();
        });

        // Toggle de la sidebar
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('collapsed');
            toggleBtn.classList.toggle('collapsed');
        });

        // Gestion des liens actifs
        const navLinks = document.querySelectorAll('.nav-link-custom');
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Supprimer la classe active de tous les liens
                navLinks.forEach(l => l.classList.remove('active'));
                
                // Ajouter la classe active au lien cliqué
                link.classList.add('active');
                
                const page = link.getAttribute('data-page');
                console.log('Navigation vers:', page);
                
                // Fermer la sidebar sur mobile
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('mobile-open');
                    mobileOverlay.classList.remove('show');
                }
            });
        });

        // Création des particules flottantes
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            if (!particlesContainer) return;

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

        // Gestion responsive
        function handleResize() {
            if (window.innerWidth <= 768) {
                // Mode mobile
                if (!sidebar.classList.contains('collapsed')) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('collapsed');
                }
            } else {
                // Mode desktop
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('show');
            }
        }

        // Gestion du clic sur l'overlay mobile
        mobileOverlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            mobileOverlay.classList.remove('show');
        });

        // Toggle mobile (clic sur le bouton toggle en mode mobile)
        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('mobile-open');
                mobileOverlay.classList.toggle('show');
            }
        });

        // Event listeners
        window.addEventListener('resize', handleResize);
        window.addEventListener('load', () => {
            document.body.style.opacity = '1';
            createParticles();
            setInterval(createParticles, 6000);
        });

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            initTheme();
            handleResize();
        });
    </script>

</body></html>