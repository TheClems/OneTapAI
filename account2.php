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
    <title>Mon Compte - OneTapAI</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            min-height: 100vh;
            transition: all 0.3s ease;
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
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, rgba(0, 255, 148, 0.1), rgba(0, 212, 170, 0.1));
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
        }

        .floating-element:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: -7s;
        }

        .floating-element:nth-child(3) {
            bottom: 20%;
            left: 50%;
            animation-delay: -14s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-30px) rotate(120deg); }
            66% { transform: translateY(30px) rotate(240deg); }
        }

        .theme-toggle {
            position: fixed;
            top: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .theme-toggle:hover {
            background: rgba(0, 255, 148, 0.2);
            transform: scale(1.1);
        }

        .main-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            margin-top: 80px;
        }

        h1 {
            font-size: 3rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #00ff94, #00d4aa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        .account-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            padding: 2rem;
            background: rgba(26, 26, 26, 0.8);
            border-radius: 12px;
            margin-bottom: 2rem;
            border: 1px solid #333;
            backdrop-filter: blur(10px);
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00ff94, #00d4aa);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: #000;
            flex-shrink: 0;
        }

        .profile-picture img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-info h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #fff;
        }

        .profile-info .username {
            color: #00ff94;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .profile-info .email {
            color: #888;
            font-size: 1rem;
        }

        .profile-info .status {
            color: #718096;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: rgba(26, 26, 26, 0.8);
            border: 1px solid #333;
            border-radius: 12px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
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
            gap: 0.5rem;
            color: #888;
            font-size: 0.9rem;
        }

        .info-label i {
            width: 16px;
            height: 16px;
        }

        .info-value {
            font-weight: 600;
            color: #fff;
        }

        .info-value.credits {
            color: #00ff94;
        }

        .actions-section {
            background: rgba(26, 26, 26, 0.8);
            border: 1px solid #333;
            border-radius: 12px;
            padding: 2rem;
            backdrop-filter: blur(10px);
        }

        .actions-section h3 {
            margin-bottom: 1.5rem;
            color: #fff;
            font-size: 1.3rem;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }

        .btn:hover:before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00ff94, #00d4aa);
            color: #000;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 255, 148, 0.3);
        }

        .btn-secondary {
            background: #333;
            color: #fff;
            border: 1px solid #555;
        }

        .btn-secondary:hover {
            background: #444;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #dc2626;
            color: #fff;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        .btn i {
            width: 18px;
            height: 18px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: #1a1a1a;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            border: 1px solid #333;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal h3 {
            color: #dc2626;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .modal p {
            color: #ccc;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .modal input {
            width: 100%;
            padding: 0.8rem;
            background: #333;
            border: 1px solid #555;
            border-radius: 6px;
            color: #fff;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

        .modal input:focus {
            outline: none;
            border-color: #00ff94;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #888;
            text-decoration: none;
            margin-bottom: 2rem;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #00ff94;
        }

        .back-link::before {
            content: '←';
            margin-right: 0.5rem;
        }

        /* Dark mode */
        body.dark-mode {
            background: #1a1a1a;
        }

        body.dark-mode .profile-header,
        body.dark-mode .info-card,
        body.dark-mode .actions-section {
            background: rgba(255, 255, 255, 0.05);
            border-color: #444;
        }

        /* Particles animation */
        .particle {
            position: fixed;
            width: 4px;
            height: 4px;
            background: #00ff94;
            border-radius: 50%;
            pointer-events: none;
            animation: particleMove 2s ease-out forwards;
            z-index: 1000;
        }

        @keyframes particleMove {
            0% {
                opacity: 1;
                transform: translate(0, 0) scale(1);
            }
            100% {
                opacity: 0;
                transform: translate(var(--dx, 0), var(--dy, 0)) scale(0);
            }
        }

        .btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
                margin-top: 60px;
            }

            h1 {
                font-size: 2.5rem;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .theme-toggle {
                top: 1rem;
                right: 1rem;
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body class="body_account">
    <?php require_once 'nav.php'; ?>

    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <button class="theme-toggle" onclick="toggleTheme()">
        <i data-lucide="sun" id="theme-icon"></i>
    </button>

    <div class="main-content">
        <a href="dashboard.php" class="back-link">Retour au dashboard</a>
        
        <h1>Mon Compte</h1>
        <p class="subtitle">Gérez votre profil et vos paramètres</p>

        <div class="account-container">
            <div class="profile-header">
                <div class="profile-picture" id="profilePicture">
                    <?php echo htmlspecialchars(strtoupper($user['full_name'][0])); ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <div class="username">@<?php echo htmlspecialchars($user['username']); ?></div>
                    <div class="email"><?php echo htmlspecialchars($user['email']); ?></div>
                    <div class="status">Membre Premium</div>
                </div>
            </div>

            <div class="info-grid">
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
                        <div class="info-value credits"><?php echo number_format($user['credits']); ?></div>
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

            <div class="actions-section">
                <h3>Actions du compte</h3>
                <div class="actions-grid">
                    <button class="btn btn-primary" id="profileBtn" onclick="window.location.href = 'auth.php?mode=edit_profile';">
                        <i data-lucide="edit"></i>
                        Modifier profil
                    </button>
                    <button class="btn btn-secondary" id="logoutBtn" onclick="window.location.href = 'logout.php';">
                        <i data-lucide="log-out"></i>
                        Se déconnecter
                    </button>
                    <button class="btn btn-danger" id="deleteBtn" onclick="showDeleteModal()">
                        <i data-lucide="trash-2"></i>
                        Supprimer compte
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>⚠️ Supprimer le compte</h3>
            <p>Cette action est irréversible. Toutes vos données seront définitivement supprimées.</p>
            <p>Pour confirmer la suppression, tapez votre nom d'utilisateur : <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
            <input type="text" id="confirmUsername" placeholder="Nom d'utilisateur">
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeDeleteModal()">Annuler</button>
                <button class="btn btn-danger" onclick="confirmDelete()" disabled id="deleteConfirmBtn">
                    Supprimer définitivement
                </button>
            </div>
        </div>
    </div>

    <script>
        // Initialiser Lucide icons
        lucide.createIcons();

        // Variables PHP pour JS
        const userUsername = '<?php echo htmlspecialchars($user['username']); ?>';
        const userFullName = '<?php echo htmlspecialchars($user['full_name']); ?>';

        // Gestion du thème
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

        // Particules
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
                particle.style.setProperty('--dx', Math.cos(angle) * velocity * 100 + 'px');
                particle.style.setProperty('--dy', Math.sin(angle) * velocity * 100 + 'px');
                
                document.body.appendChild(particle);
                
                setTimeout(() => {
                    particle.remove();
                }, 2000);
            }
        }

        // Gestion des boutons avec effets
        function handleButtonClick(button, loadingText, successText, successColor) {
            button.classList.add('loading');
            const originalText = button.querySelector('span') ? button.querySelector('span').textContent : button.textContent;
            
            if (button.querySelector('span')) {
                button.querySelector('span').textContent = loadingText;
            } else {
                button.textContent = loadingText;
            }
            
            createParticles(button);
            
            setTimeout(() => {
                button.classList.remove('loading');
                if (button.querySelector('span')) {
                    button.querySelector('span').textContent = successText;
                } else {
                    button.textContent = successText;
                }
                if (successColor) {
                    button.style.background = successColor;
                }
                
                setTimeout(() => {
                    if (button.querySelector('span')) {
                        button.querySelector('span').textContent = originalText;
                    } else {
                        button.textContent = originalText;
                    }
                    button.style.background = '';
                }, 2000);
            }, 1500);
        }

        // Modal de suppression
        function showDeleteModal() {
            document.getElementById('deleteModal').style.display = 'block';
            document.getElementById('confirmUsername').value = '';
            document.getElementById('deleteConfirmBtn').disabled = true;
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        function confirmDelete() {
            window.location.href = 'delete_account.php';
        }

        // Vérifier la saisie du nom d'utilisateur
        document.getElementById('confirmUsername').addEventListener('input', function() {
            const deleteBtn = document.getElementById('deleteConfirmBtn');
            if (this.value === userUsername) {
                deleteBtn.disabled = false;
                deleteBtn.style.opacity = '1';
            } else {
                deleteBtn.disabled = true;
                deleteBtn.style.opacity = '0.5';
            }
        });

        // Fermer la modal en cliquant à l'extérieur
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        }

        // Event listeners pour les boutons
        document.getElementById('profileBtn').addEventListener('click', function(e) {
            e.preventDefault();
            handleButtonClick(this, 'Chargement...', 'Profil ouvert !', 'linear-gradient(135deg, #48bb78, #38a169)');
            setTimeout(() => {
                window.location.href = 'auth.php?mode=edit_profile';
            }, 1000);
        });

        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            handleButtonClick(this, 'Déconnexion...', 'Déconnecté !', 'linear-gradient(135deg, #48bb78, #38a169)');
            setTimeout(() => {
                window.location.href = 'logout.php';
            }, 1500);
        });
    </script>
    <script type="text/javascript" src="scripts/nav.js"></script>
</body>
</html>