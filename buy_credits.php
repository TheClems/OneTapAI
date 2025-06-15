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
    <link rel="stylesheet" href="css/buy_credits.css" />

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Acheter des crédits - OneTapAI</title>

</head>

<body>
<?php require_once 'nav.php'; ?>

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
                    <div class="credits"><?php echo number_format($package['credits_offerts']); ?> crédits</div>
                    <div class="price"><?php echo number_format($package['prix'], 2); ?>€</div>
                    <button class="btn acheter-btn" data-id="<?= $i ?>" data-nom="<?= htmlspecialchars($package['nom']) ?>" data-prix="<?= $package['prix'] ?>" data-credits="<?= $package['credits_offerts'] ?>">
                        Acheter
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
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nom = this.getAttribute('data-nom');
                const prix = this.getAttribute('data-prix');
                const credits = this.getAttribute('data-credits');

                this.disabled = true;

                paypal.Buttons({
                    createOrder: function(data, actions) {
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
                    onApprove: function(data, actions) {
                        return actions.order.capture().then(function(details) {
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
</body>

<script type="text/javascript" src="scripts/nav.js"></script>