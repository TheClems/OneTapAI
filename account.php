<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #2d3748;
            transition: all 0.3s ease;
        }

        .dark-mode {
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            color: #e2e8f0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            opacity: 0;
            animation: fadeInUp 1s ease forwards;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .dark-mode .header h1 {
            background: linear-gradient(135deg, #81e6d9, #48bb78);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.8;
            max-width: 600px;
            margin: 0 auto;
        }

        .profile-section {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        @media (max-width: 768px) {
            .profile-section {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeInLeft 1s ease 0.2s forwards;
        }

        .dark-mode .profile-card {
            background: rgba(45, 55, 72, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .profile-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            font-weight: bold;
            position: relative;
            overflow: hidden;
        }

        .avatar::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 3s infinite;
        }

        .info-cards {
            opacity: 0;
            animation: fadeInRight 1s ease 0.4s forwards;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dark-mode .info-card {
            background: rgba(45, 55, 72, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .info-card:hover {
            transform: translateX(10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.95rem;
        }

        .dark-mode .info-label {
            color: #a0aec0;
        }

        .info-value {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2d3748;
        }

        .dark-mode .info-value {
            color: #e2e8f0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeInUp 1s ease forwards;
        }

        .dark-mode .stat-card {
            background: rgba(45, 55, 72, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-card:nth-child(1) { animation-delay: 0.6s; }
        .stat-card:nth-child(2) { animation-delay: 0.8s; }
        .stat-card:nth-child(3) { animation-delay: 1s; }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .dark-mode .stat-value {
            color: #e2e8f0;
        }

        .stat-label {
            font-size: 1rem;
            color: #718096;
            font-weight: 500;
        }

        .dark-mode .stat-label {
            color: #a0aec0;
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            opacity: 0;
            animation: fadeInUp 1s ease 1.2s forwards;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 15px;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: #4a5568;
            border: 2px solid rgba(102, 126, 234, 0.2);
        }

        .dark-mode .btn-secondary {
            background: rgba(45, 55, 72, 0.9);
            color: #e2e8f0;
            border: 2px solid rgba(129, 230, 217, 0.2);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .theme-toggle {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .dark-mode .theme-toggle {
            background: rgba(45, 55, 72, 0.9);
            color: #e2e8f0;
        }

        .theme-toggle:hover {
            transform: scale(1.1) rotate(180deg);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .floating-element {
            position: absolute;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <button class="theme-toggle" onclick="toggleTheme()">
        <i data-lucide="sun" id="theme-icon"></i>
    </button>

    <div class="container">
        <div class="header">
            <h1>Mon Compte</h1>
            <p>Gérez vos informations personnelles et suivez votre activité</p>
        </div>

        <div class="profile-section">
            <div class="profile-card">
                <div class="avatar">
                    JD
                </div>
                <h2>Jean Dupont</h2>
                <p style="color: #718096; margin-top: 0.5rem;">Membre Premium</p>
                
                <div style="margin-top: 2rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <span style="color: #718096;">Niveau</span>
                        <span style="font-weight: 600;">Expert</span>
                    </div>
                    <div style="background: #e2e8f0; border-radius: 10px; height: 8px;">
                        <div style="background: linear-gradient(90deg, #667eea, #764ba2); height: 100%; width: 85%; border-radius: 10px;"></div>
                    </div>
                </div>
            </div>

            <div class="info-cards">
                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="user"></i>
                            Nom d'utilisateur
                        </div>
                        <div class="info-value">jean.dupont</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="mail"></i>
                            Email
                        </div>
                        <div class="info-value">jean.dupont@email.com</div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="coins"></i>
                            Crédits actuels
                        </div>
                        <div class="info-value">2,450</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="trending-up"></i>
                            Points XP
                        </div>
                        <div class="info-value">15,820</div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="calendar"></i>
                            Date d'inscription
                        </div>
                        <div class="info-value">15/03/2023</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="clock"></i>
                            Dernière connexion
                        </div>
                        <div class="info-value">14/06/2025 14:32</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i data-lucide="activity"></i>
                </div>
                <div class="stat-value">127</div>
                <div class="stat-label">Sessions actives</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i data-lucide="award"></i>
                </div>
                <div class="stat-value">23</div>
                <div class="stat-label">Réalisations</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i data-lucide="users"></i>
                </div>
                <div class="stat-value">89</div>
                <div class="stat-label">Connexions</div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary">
                <i data-lucide="edit"></i>
                Modifier le profil
            </button>
            <button class="btn btn-secondary">
                <i data-lucide="settings"></i>
                Paramètres
            </button>
            <button class="btn btn-secondary">
                <i data-lucide="shield"></i>
                Sécurité
            </button>
        </div>
    </div>

    <script>
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
    </script>
</body>
</html>