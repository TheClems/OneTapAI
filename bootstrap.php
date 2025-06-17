<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Bootstrap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3ef3c7;
            --primary-rgb: 62, 243, 199;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
            overflow-x: hidden;
        }

        .dark-theme {
            background: black !important;
            color: #e2e8f0 !important;
        }

        .light-theme {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            color: #334155 !important;
        }

        /* Sidebar personnalisé */
        .custom-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(var(--primary-rgb), 0.2);
            box-shadow: 0 25px 50px -12px rgba(var(--primary-rgb), 0.25);
        }

        .dark-theme .custom-sidebar {
            background: rgba(12, 12, 12, 0.92);
        }

        .light-theme .custom-sidebar {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }

        .sidebar-collapsed {
            transform: translateX(-280px);
        }

        .custom-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
        }

        .light-theme .custom-sidebar::before {
            background: linear-gradient(180deg,
                rgba(124, 58, 237, 0.05) 0%,
                rgba(168, 85, 247, 0.05) 50%,
                rgba(147, 51, 234, 0.05) 100%);
        }

        /* Bouton toggle */
        .toggle-btn {
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #64ffda, #1de9b6);
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

        .sidebar-collapsed .toggle-btn::before {
            transform: rotate(180deg);
        }

        /* Header de navigation */
        .nav-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(var(--primary-rgb), 0.2);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: #fff;
            background-clip: text;
            -webkit-background-clip: text;
        }

        .dark-theme .logo {
            color: #e2e8f0;
        }

        .light-theme .logo {
            color: #334155;
        }

        .theme-toggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .dark-theme .theme-toggle {
            color: #e2e8f0;
        }

        .light-theme .theme-toggle {
            color: #334155;
        }

        .theme-toggle:hover {
            background: rgba(var(--primary-rgb), 0.1);
            transform: scale(1.1);
        }

        /* Menu de navigation */
        .nav-link-custom {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border-radius: 0;
            margin: 0.5rem 0;
        }

        .dark-theme .nav-link-custom {
            color: rgba(226, 232, 240, 0.7);
        }

        .light-theme .nav-link-custom {
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
            background: rgba(var(--primary-rgb), 0.1) !important;
            transform: translateX(8px);
            max-width: 97%;
        }

        .dark-theme .nav-link-custom:hover {
            color: #e2e8f0;
        }

        .light-theme .nav-link-custom:hover {
            color: #334155;
            background: rgba(var(--primary-rgb), 0.05) !important;
        }

        .nav-link-custom.active {
            background: rgba(var(--primary-rgb), 0.15) !important;
        }

        .dark-theme .nav-link-custom.active {
            color: #e2e8f0;
        }

        .light-theme .nav-link-custom.active {
            color: #334155;
            background: rgba(var(--primary-rgb), 0.1) !important;
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

        /* Contenu principal */
        .main-content {
            transition: margin-left 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            margin-left: 280px;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        .content-card {
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(var(--primary-rgb), 0.2);
        }

        .dark-theme .content-card {
            background: rgba(var(--primary-rgb), 0.1);
            box-shadow: 0 20px 40px rgba(var(--primary-rgb), 0.1);
        }

        .light-theme .content-card {
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        }

        /* Animations */
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
            border-radius: 50%;
            animation: float 6s infinite linear;
        }

        .dark-theme .particle {
            background: rgba(var(--primary-rgb), 0.3);
        }

        .light-theme .particle {
            background: rgba(var(--primary-rgb), 0.2);
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% {
                transform: translateY(-100px) scale(1);
                opacity: 0;
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
        }
    </style>
</head>
<body class="dark-theme">
    <!-- Sidebar -->
    <nav class="custom-sidebar" id="sidebar">
        <button class="toggle-btn" id="toggleBtn"></button>
        
        <!-- Header avec logo et toggle theme -->
        <div class="nav-header d-flex justify-content-between align-items-center">
            <div class="logo">MonApp</div>
            <button class="theme-toggle" id="themeToggle">
                <i class="fas fa-sun theme-icon"></i>
            </button>
        </div>

        <!-- Menu de navigation -->
        <ul class="nav flex-column p-0 mt-4">
            <li class="nav-item px-3">
                <a class="nav-link nav-link-custom d-flex align-items-center py-3 active" href="#">
                    <i class="fas fa-home nav-icon"></i>
                    <span class="nav-text">Accueil</span>
                </a>
            </li>
            <li class="nav-item px-3">
                <a class="nav-link nav-link-custom d-flex align-items-center py-3" href="#">
                    <i class="fas fa-chart-bar nav-icon"></i>
                    <span class="nav-text">Tableau de bord</span>
                </a>
            </li>
            <li class="nav-item px-3">
                <a class="nav-link nav-link-custom d-flex align-items-center py-3" href="#">
                    <i class="fas fa-users nav-icon"></i>
                    <span class="nav-text">Utilisateurs</span>
                </a>
            </li>
            <li class="nav-item px-3">
                <a class="nav-link nav-link-custom d-flex align-items-center py-3" href="#">
                    <i class="fas fa-cog nav-icon"></i>
                    <span class="nav-text">Paramètres</span>
                </a>
            </li>
            <li class="nav-item px-3">
                <a class="nav-link nav-link-custom d-flex align-items-center py-3" href="#">
                    <i class="fas fa-envelope nav-icon"></i>
                    <span class="nav-text">Messages</span>
                </a>
            </li>
            <li class="nav-item px-3">
                <a class="nav-link nav-link-custom d-flex align-items-center py-3" href="#">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    <span class="nav-text">Déconnexion</span>
                </a>
            </li>
        </ul>

        <!-- Particules flottantes -->
        <div class="floating-particles" id="particles"></div>
    </nav>

    <!-- Contenu principal -->
    <main class="main-content p-4" id="mainContent">
        <div class="container-fluid">
            <div class="content-card p-4">
                <h1 class="h2 mb-4">Bienvenue sur votre tableau de bord</h1>
                <p class="lead">Cette sidebar Bootstrap reproduit fidèlement le design original avec :</p>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Mode sombre/clair</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Animations fluides</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Effets glassmorphism</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Responsive design</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Particules animées</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Sidebar rétractable</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestion du toggle sidebar
        const toggleBtn = document.getElementById('toggleBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-collapsed');
            toggleBtn.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Gestion du thème
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const themeIcon = themeToggle.querySelector('.theme-icon');

        themeToggle.addEventListener('click', () => {
            if (body.classList.contains('dark-theme')) {
                body.classList.remove('dark-theme');
                body.classList.add('light-theme');
                themeIcon.className = 'fas fa-moon theme-icon';
            } else {
                body.classList.remove('light-theme');
                body.classList.add('dark-theme');
                themeIcon.className = 'fas fa-sun theme-icon';
            }
        });

        // Gestion des liens de navigation
        const navLinks = document.querySelectorAll('.nav-link-custom');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            });
        });

        // Création des particules flottantes
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            
            setInterval(() => {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
                particle.style.animationDelay = Math.random() * 2 + 's';
                
                particlesContainer.appendChild(particle);
                
                setTimeout(() => {
                    particle.remove();
                }, 8000);
            }, 300);
        }

        // Gestion responsive
        function handleResize() {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('sidebar-collapsed');
                mainContent.classList.add('expanded');
            } else {
                sidebar.classList.remove('mobile-open');
            }
        }

        // Event listeners
        window.addEventListener('resize', handleResize);
        
        // Mobile menu toggle
        if (window.innerWidth <= 768) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('mobile-open');
            });
        }

        // Initialisation
        handleResize();
        createParticles();
    </script>
</body>
</html>