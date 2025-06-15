<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>
    <link rel="stylesheet" href="css/nav.css" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            background: rgba(45, 55, 72, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            display: flex;
            padding: 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeInLeft 1s ease 0.2s forwards;
            flex-direction: column;
            justify-content: center;
        }

        .profile-card.light-mode {
            background: rgba(255, 255, 255, 0.95);

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
            background: rgba(45, 55, 72, 0.95);
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

        
        .info-card.light-mode {
            background: rgba(255, 255, 255, 0.95);
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
            color: white;
            font-size: 0.95rem;
        }
        .info-label.light-mode {
            color: #4a5568;
        }



        .info-value {
            font-weight: 700;
            font-size: 1.1rem;
            color: white;
        }
        .info-value.light-mode {
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


        h2 {
            color: white;
        }

        h2.light-mode {
            color: #4a5568;
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

        .button {
            position: relative;
            padding: 16px 32px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            overflow: hidden;
        }

        .button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .button:hover::before {
            left: 100%;
        }

        .button:active {
            transform: translateY(2px);
        }

        /* Bouton Modifier */
        .modify-btn {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }

        .modify-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(76, 175, 80, 0.4);
        }

        /* Bouton Déconnexion */
        .logout-btn {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            box-shadow: 0 8px 20px rgba(33, 150, 243, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(33, 150, 243, 0.4);
        }

        /* Bouton Supprimer */
        .delete-btn {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
            box-shadow: 0 8px 20px rgba(244, 67, 54, 0.3);
        }

        .delete-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(244, 67, 54, 0.4);
            animation: shake 0.3s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0) translateY(-3px); }
            25% { transform: translateX(-2px) translateY(-3px); }
            75% { transform: translateX(2px) translateY(-3px); }
        }

        .icon {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 24px;
            font-weight: 700;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
                min-width: auto;
                width: 100%;
                max-width: 400px;
            }
            
            .button {
                padding: 14px 24px;
                font-size: 15px;
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
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                </svg>
            </button>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#" class="nav-link active pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span class="nav-text">Accueil</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="nav-text">Projets</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                    </svg>
                    <span class="nav-text">Équipe</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="buy_credits.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    <span class="nav-text">Acheter des crédits</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="account.php" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="nav-text">Mon Compte</span>
                </a>
            </li>

        </ul>
    </nav>
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
        </div>

        <div class="profile-section">
            <div class="profile-card">
                <div class="avatar">
                    <?php echo htmlspecialchars($user['full_name'][0]); ?>
                </div>
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p style="color: #718096; margin-top: 0.5rem;">Membre Premium</p>
                
            </div>

            <div class="info-cards">
                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="user"></i>
                            Nom d'utilisateur
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="mail"></i>
                            Email
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="coins"></i>
                            Crédits actuels
                        </div>
                        <div class="info-value"><?php echo number_format($user['credits']); ?></div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            <i data-lucide="calendar"></i>
                            Date d'inscription
                        </div>
                        <div class="info-value"><?php echo date('d/m/Y à H:i', strtotime($user['date_inscription'])); ?></div>
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
            <div class="action-buttons mt-6">
                <div class="flex flex-col gap-4">
                    <!-- Modifier le profil -->
                    <a href="auth2.php?mode=edit_profile" class="button w-full flex items-center justify-center gap-2">
                        <i data-lucide="edit-2" class="w-5 h-5"></i>
                        Modifier le profil
                    </a>
                    
                    <!-- Déconnexion -->
                    <form action="auth2.php" method="POST" class="w-full">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="button w-full flex items-center justify-center gap-2">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                            Déconnexion
                        </button>
                    </form>
                    
                    <!-- Supprimer le compte -->
                    <form action="auth2.php" method="POST" class="w-full">
                        <input type="hidden" name="action" value="delete_account">
                        <button type="submit" class="button w-full flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 text-white">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                            Supprimer le compte
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <button class="button modify-btn" onclick="modifyAccount()">
            <svg class="icon" viewBox="0 0 24 24">
                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
            </svg>
            Modifier le compte
        </button>
        
        <button class="button logout-btn" onclick="logout()">
            <svg class="icon" viewBox="0 0 24 24">
                <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.59L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
            </svg>
            Se déconnecter
        </button>
        
        <button class="button delete-btn" onclick="deleteAccount()">
            <svg class="icon" viewBox="0 0 24 24">
                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
            </svg>
            Supprimer le compte
        </button>
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

<script type="text/javascript" src="assets/js/nav.js"></script>
<script>
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
</script>

<script>
        function modifyAccount() {
            // Animation de feedback
            const btn = event.target.closest('.button');
            btn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                btn.style.transform = '';
            }, 150);
            
            alert('Redirection vers la page de modification du compte...');
            // Ici vous pouvez ajouter votre logique de redirection
            // window.location.href = '/modify-account';
        }

        function logout() {
            const btn = event.target.closest('.button');
            btn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                btn.style.transform = '';
            }, 150);
            
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                alert('Déconnexion en cours...');
                // Ici vous pouvez ajouter votre logique de déconnexion
                // window.location.href = '/logout';
            }
        }

        function deleteAccount() {
            const btn = event.target.closest('.button');
            btn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                btn.style.transform = '';
            }, 150);
            
            if (confirm('⚠️ ATTENTION : Cette action est irréversible !\n\nÊtes-vous vraiment sûr de vouloir supprimer définitivement votre compte ?')) {
                if (confirm('Dernière confirmation : Voulez-vous vraiment supprimer votre compte ? Toutes vos données seront perdues.')) {
                    alert('Suppression du compte en cours...');
                    // Ici vous pouvez ajouter votre logique de suppression
                    // window.location.href = '/delete-account';
                }
            }
        }

        // Ajout d'effets sonores visuels au survol
        document.querySelectorAll('.button').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.filter = 'brightness(1.1)';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.filter = 'brightness(1)';
            });
        });
    </script>