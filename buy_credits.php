<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();

$success = '';
$error = '';

// Traiter l'achat fictif
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['package'])) {
    $package = $_POST['package'];
    $credits_to_add = 0;
    
    switch ($package) {
        case 'starter':
            $credits_to_add = 1000;
            break;
        case 'pro':
            $credits_to_add = 2500;
            break;
        case 'premium':
            $credits_to_add = 5500;
            break;
    }
    
    if ($credits_to_add > 0) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
        if ($stmt->execute([$credits_to_add, $_SESSION['user_id']])) {
            $success = "Félicitations ! Vous avez acheté " . number_format($credits_to_add) . " crédits.";
        } else {
            $error = "Erreur lors de l'achat des crédits.";
        }
    }
}

$user = getCurrentUser();

// Récupérer les packages d'abonnement
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM abonnements ORDER BY prix ASC");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://www.paypal.com/sdk/js?client-id=ATNKqjfci0KXJor6txjMz8qIWbAmbhXL1JWgKnmGl108_QSR3K_zKzUFHaNsIroR5D7tudYo4X1yZOaV&currency=EUR"></script>

    <title>Acheter des crédits - AI Credits</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .current-credits {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        .packages {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .package {
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: border-color 0.3s;
        }
        .package:hover {
            border-color: #007bff;
        }
        .package h3 {
            color: #333;
            margin-top: 0;
        }
        .price {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0;
        }
        .credits {
            font-size: 18px;
            color: #666;
            margin-bottom: 20px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #d4edda;
            border-radius: 5px;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8d7da;
            border-radius: 5px;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .demo-notice {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            color: #856404;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            overflow-x: hidden;
            transition: all 0.3s ease;
        }

        body.light-mode {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: rgb(12 12 12 / 92%);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(124, 58, 237, 0.2);
            transform: translateX(0);
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            z-index: 1000;
            box-shadow: 0 25px 50px -12px rgba(124, 58, 237, 0.25);
        }

        body.light-mode .sidebar {
            background: rgba(255, 255, 255, 0.95);
            border-right: 1px solid rgba(124, 58, 237, 0.2);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }

        .sidebar.collapsed {
            transform: translateX(-240px);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;

            pointer-events: none;
        }

        body.light-mode .sidebar::before {
            background: linear-gradient(180deg, 
                rgba(124, 58, 237, 0.05) 0%, 
                rgba(168, 85, 247, 0.05) 50%,
                rgba(147, 51, 234, 0.05) 100%);
        }

        .toggle-btn {
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(124, 58, 237, 0.3);
            transition: all 0.3s ease;
            z-index: 1001;
        }

        body.light-mode .toggle-btn {
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            box-shadow: 0 10px 25px rgba(124, 58, 237, 0.2);
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

        .sidebar.collapsed .toggle-btn::before {
            transform: rotate(180deg);
        }

        .nav-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(124, 58, 237, 0.2);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        body.light-mode .nav-header {
            border-bottom: 1px solid rgba(124, 58, 237, 0.2);
        }

        .logo {
            color: #e2e8f0;
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #7c3aed, #a855f7, #c084fc);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            flex-grow: 1;
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
            background: rgba(124, 58, 237, 0.1);
            transform: scale(1.1);
        }

        .theme-icon {
            width: 20px;
            height: 20px;
            transition: all 0.3s ease;
        }

        .nav-menu {
            padding: 2rem 0;
            list-style: none;
        }

        .nav-item {
            margin: 0.5rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(226, 232, 240, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        body.light-mode .nav-link {
            color: rgba(51, 65, 85, 0.7);
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            transform: scaleY(1);
        }

        .nav-link:hover {
            color: #e2e8f0;
            background: rgba(124, 58, 237, 0.1);
            transform: translateX(8px);
            max-width: 98%;
        }

        body.light-mode .nav-link:hover {
            color: #334155;
            background: rgba(124, 58, 237, 0.05);
        }

        .nav-link.active {
            color: #e2e8f0;
            background: rgba(124, 58, 237, 0.15);
        }

        body.light-mode .nav-link.active {
            color: #334155;
            background: rgba(124, 58, 237, 0.1);
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            margin-right: 1rem;
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .nav-link:hover .nav-icon {
            opacity: 1;
            transform: scale(1.1);
        }

        .nav-text {
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
            transition: margin-left 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            color: #e2e8f0;
        }

        body.light-mode .main-content {
            color: #334155;
        }

        .sidebar.collapsed + .main-content {
            margin-left: 40px;
        }

        .content-card {
            background: rgba(124, 58, 237, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(124, 58, 237, 0.2);
            box-shadow: 0 20px 40px rgba(124, 58, 237, 0.1);
        }

        body.light-mode .content-card {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(124, 58, 237, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        }

        .pulse-effect {
            position: relative;
            overflow: hidden;
        }

        .pulse-effect::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.1), 
                transparent);
            transition: left 0.6s ease;
        }

        .nav-link:hover.pulse-effect::after {
            left: 100%;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-240px);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 40px;
            }
            
            .sidebar.mobile-open + .main-content {
                margin-left: 280px;
            }
        }

        /* Animation d'entrée */
        .nav-item {
            opacity: 0;
            transform: translateY(20px);
            animation: slideInUp 0.6s ease forwards;
        }

        .nav-item:nth-child(1) { animation-delay: 0.1s; }
        .nav-item:nth-child(2) { animation-delay: 0.2s; }
        .nav-item:nth-child(3) { animation-delay: 0.3s; }
        .nav-item:nth-child(4) { animation-delay: 0.4s; }
        .nav-item:nth-child(5) { animation-delay: 0.5s; }
        .nav-item:nth-child(6) { animation-delay: 0.6s; }

        @keyframes slideInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Effet de particules flottantes */
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
            background: rgba(124, 58, 237, 0.3);
            border-radius: 50%;
            animation: float 6s infinite linear;
        }

        body.light-mode .particle {
            background: rgba(124, 58, 237, 0.2);
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
    </style>
</head>
<body>
<nav class="sidebar" id="sidebar">
        <div class="floating-particles" id="particles"></div>
        
        <button class="toggle-btn" id="toggleBtn"></button>
        
        <div class="nav-header">
            <div class="logo">MODERN NAV</div>
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
                <a href="#" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    <span class="nav-text">Messages</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link pulse-effect">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="nav-text">Paramètres</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="container">
        <h1>Acheter des crédits</h1>
        
        <div class="demo-notice">
            <strong>Mode démo :</strong> Les achats sont fictifs, les crédits seront ajoutés immédiatement sans paiement réel.
        </div>
        
        <div class="current-credits">
            <strong>Vos crédits actuels : <?php echo number_format($user['credits']); ?></strong>
        </div>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="packages">
            <?php foreach ($packages as $i => $package): ?>
                <div class="package">
                    <h3><?php echo htmlspecialchars($package['nom']); ?></h3>
                    <div class="price"><?php echo number_format($package['prix'], 2); ?>€</div>
                    <div class="credits"><?php echo number_format($package['credits_offerts']); ?> crédits</div>

                    <button class="btn acheter-btn" data-id="<?= $i ?>" data-nom="<?= htmlspecialchars($package['nom']) ?>" data-prix="<?= $package['prix'] ?>" data-credits="<?= $package['credits_offerts'] ?>">Acheter avec PayPal</button>
                    <div class="paypal-boutons" id="paypal-boutons-<?= $i ?>"></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="back-link">
            <a href="dashboard.php">← Retour au tableau de bord</a>
        </div>
    </div>
</body>
</html>
<?php
$pseudo = htmlspecialchars($user['username']); // Supposons que ce soit "alex_du_78"
?>
<script>
    const pseudoPHP = <?= json_encode($user['username']) ?>;

    document.querySelectorAll('.acheter-btn').forEach(function(button) {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nom = this.getAttribute('data-nom');
            const prix = this.getAttribute('data-prix');
            const credits = this.getAttribute('data-credits');

            // Empêche le double affichage
            this.disabled = true;

            paypal.Buttons({
                createOrder: function (data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            description: nom + " - " + credits + " crédits",
                            custom_id: pseudoPHP + "-" + nom,
                            invoice_id: "FACTURE-" + pseudoPHP + "-" + nom,
                            amount: {
                                value: prix,
                                currency_code: 'EUR'
                            }
                        }],
                        application_context: {
                            shipping_preference: "NO_SHIPPING"
                        }
                    });
                },
                onApprove: function (data, actions) {
                    return actions.order.capture().then(function (details) {
                        alert("✅ Paiement réussi par " + details.payer.name.given_name + " !");
                        console.log("Détails : ", details);

                        // Optionnel : envoie AJAX pour créditer automatiquement l’utilisateur
                        // fetch('crediter.php', { method: 'POST', body: JSON.stringify({ ... }) })
                    });
                }
            }).render("#paypal-boutons-" + id);
        });
    });
</script>

<script>
        // Gestion du toggle de la sidebar
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const body = document.body;
        
        // Gestion du thème
        let isDarkMode = true;
        
        themeToggle.addEventListener('click', () => {
            isDarkMode = !isDarkMode;
            body.classList.toggle('light-mode');
            
            if (isDarkMode) {
                // Icône lune pour le mode sombre
                themeIcon.innerHTML = '<path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>';
            } else {
                // Icône soleil pour le mode clair
                themeIcon.innerHTML = '<path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>';
            }
        });
        
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        // Gestion des liens actifs
        const navLinks = document.querySelectorAll('.nav-link');
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
    </script>