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
    <link rel="stylesheet" href="css/nav.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Acheter des crédits - AI Credits</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            color: #ffffff;
            overflow-x: hidden;
        }

        /* Animated background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.1;
        }

        .bg-particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: linear-gradient(45deg, #64ffda, #1de9b6);
            border-radius: 50%;
            animation: float-particle 8s infinite linear;
        }

        @keyframes float-particle {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 700;
            background: linear-gradient(135deg, #64ffda 0%, #1de9b6 50%, #00bcd4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            animation: glow-text 2s ease-in-out infinite alternate;
        }

        @keyframes glow-text {
            from {
                text-shadow: 0 0 20px rgba(100, 255, 218, 0.3);
            }
            to {
                text-shadow: 0 0 30px rgba(100, 255, 218, 0.6);
            }
        }

        .subtitle {
            font-size: 1.2rem;
            color: #94a3b8;
            font-weight: 300;
        }

        .demo-notice {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 152, 0, 0.1));
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
            backdrop-filter: blur(10px);
            animation: pulse-border 3s infinite;
            color: #ffffff;
        }

        .demo-notice.light-mode {
            color: #0f0f23;

        }

        @keyframes pulse-border {
            0%, 100% {
                border-color: rgba(255, 193, 7, 0.3);
                box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
            }
            50% {
                border-color: rgba(255, 193, 7, 0.6);
                box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
            }
        }

        .current-credits {
            background: linear-gradient(135deg, rgba(100, 255, 218, 0.1), rgba(29, 233, 182, 0.1));
            border: 1px solid rgba(100, 255, 218, 0.3);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 3rem;
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
        }

        .current-credits.light-mode {
            background: rgba(45, 55, 72, 0.95);

        }

        .current-credits::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(100, 255, 218, 0.1), transparent);
            animation: rotate 4s linear infinite;
        }

        .current-credits-content {
            position: relative;
            z-index: 1;
        }

        .current-credits h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #64ffda;
        }

        .credits-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #64ffda, #1de9b6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .packages {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .package {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            backdrop-filter: blur(20px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .package.light-mode {
            background: rgba(45, 55, 72, 0.95);

        }

        .package::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(100, 255, 218, 0.8), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .package:hover::before {
            transform: translateX(100%);
        }

        .package:hover {
            transform: translateY(-8px);
            border-color: rgba(100, 255, 218, 0.4);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(100, 255, 218, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .package.featured {
            border-color: rgba(100, 255, 218, 0.6);
            background: linear-gradient(135deg, rgba(100, 255, 218, 0.1), rgba(29, 233, 182, 0.05));
            transform: scale(1.05);
        }

        .package.featured.light-mode {
            border-color: rgba(45, 55, 72, 0.95);

        }

        .package.featured::after {
            content: 'POPULAIRE';
            position: absolute;
            top: -1px;
            right: 20px;
            background: linear-gradient(135deg, #64ffda, #1de9b6);
            color: #0f0f23;
            padding: 0.5rem 1rem;
            border-radius: 0 0 12px 12px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .package h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .price {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #64ffda, #1de9b6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .credits {
            font-size: 1.1rem;
            color: #94a3b8;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .btn {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #64ffda 0%, #1de9b6 100%);
            color: #0f0f23;
            border: none;
            border-radius: 16px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.025em;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(100, 255, 218, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .paypal-boutons {
            margin-top: 1rem;
        }

        .success, .error {
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        .success {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(22, 163, 74, 0.1));
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #4ade80;
        }

        .error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        .back-link {
            text-align: center;
            margin-top: 3rem;
        }

        .back-link a {
            color: #64ffda;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-link.light-mode {
            color:rgba(45, 55, 72, 0.95);
            text-decoration: none;
            font-weight: 500;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-link a:hover {
            color: #1de9b6;
            transform: translateX(-4px);
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .packages {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .package {
                padding: 2rem 1.5rem;
            }

            .package.featured {
                transform: none;
            }

            .price {
                font-size: 2.5rem;
            }

            .current-credits {
                padding: 1.5rem;
            }

            .credits-number {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .package {
                padding: 1.5rem 1rem;
            }

            .price {
                font-size: 2rem;
            }

            .btn {
                padding: 0.875rem 1.5rem;
            }
        }

        /* Floating elements modernisés */
        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }

        .floating-element {
            position: absolute;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(100, 255, 218, 0.03) 0%, transparent 70%);
            border-radius: 50%;
            animation: float-modern 12s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 4s;
        }

        .floating-element:nth-child(3) {
            bottom: 20%;
            left: 15%;
            animation-delay: 8s;
        }

        @keyframes float-modern {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg) scale(1);
                opacity: 0.3;
            }
            33% { 
                transform: translateY(-30px) rotate(120deg) scale(1.1);
                opacity: 0.5;
            }
            66% { 
                transform: translateY(15px) rotate(240deg) scale(0.9);
                opacity: 0.4;
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

    <!-- Animated background -->
    <div class="animated-bg" id="animatedBg"></div>

    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <div class="container">
        <div class="header">
            <h1>Acheter des crédits</h1>
            <p class="subtitle">Boostez votre créativité avec nos packs de crédits IA</p>
        </div>
        
        <div class="demo-notice">
            <strong>⚡ Mode démo</strong> : Les achats sont fictifs, les crédits seront ajoutés immédiatement sans paiement réel.
        </div>
        
        <div class="current-credits">
            <div class="current-credits-content">
                <p>Vos crédits actuels</p>
                <div class="credits-number"><?php echo number_format($user['credits']); ?></div>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="packages">
            <?php foreach ($packages as $i => $package): ?>
                <div class="package <?php echo $i === 1 ? 'featured' : ''; ?>">
                    <h3><?php echo htmlspecialchars($package['nom']); ?></h3>
                    <div class="price"><?php echo number_format($package['prix'], 2); ?>€</div>
                    <div class="credits"><?php echo number_format($package['credits_offerts']); ?> crédits</div>
                    <button class="btn acheter-btn" data-id="<?= $i ?>" data-nom="<?= htmlspecialchars($package['nom']) ?>" data-prix="<?= $package['prix'] ?>" data-credits="<?= $package['credits_offerts'] ?>">
                        Acheter avec PayPal
                    </button>
                    <div class="paypal-boutons" id="paypal-boutons-<?= $i ?>"></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="back-link">
            <a href="dashboard.php">← Retour au tableau de bord</a>
        </div>
    </div>

    <?php $pseudo = htmlspecialchars($user['username']); ?>
    <script>
        // Animated background particles
        function createBackgroundParticles() {
            const bg = document.getElementById('animatedBg');
            const particleCount = 20;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'bg-particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 8 + 's';
                particle.style.animationDuration = (8 + Math.random() * 4) + 's';
                bg.appendChild(particle);
            }
        }

        // PayPal integration
        const pseudoPHP = <?= json_encode($user['username']) ?>;

        document.querySelectorAll('.acheter-btn').forEach(function(button) {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nom = this.getAttribute('data-nom');
                const prix = this.getAttribute('data-prix');
                const credits = this.getAttribute('data-credits');

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
                        });
                    }
                }).render("#paypal-boutons-" + id);
            });
        });

        // Enhanced mouse parallax effect
        document.addEventListener('mousemove', function(e) {
            const floatingElements = document.querySelectorAll('.floating-element');
            const x = (e.clientX / window.innerWidth) - 0.5;
            const y = (e.clientY / window.innerHeight) - 0.5;
            
            floatingElements.forEach((element, index) => {
                const speed = (index + 1) * 0.3;
                const xPos = x * speed * 30;
                const yPos = y * speed * 30;
                
                element.style.transform = `translate(${xPos}px, ${yPos}px) rotate(${x * speed * 10}deg)`;
            });
        });

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            createBackgroundParticles();
            
            // Stagger animation for packages
            const packages = document.querySelectorAll('.package');
            packages.forEach((pkg, index) => {
                pkg.style.animationDelay = (index * 0.1) + 's';
                pkg.style.animation = 'fadeInUp 0.6s ease forwards';
            });
        });

        // Add fadeInUp animation
        const style = document.createElement('style');
        style.textContent = `
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
            
            .package {
                opacity: 0;
            }
        `;
        document.head.appendChild(style);
    </script>
    <script type="text/javascript" src="assets/js/nav.js"></script>
</body>
</html>