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

// Récupérer les packages d'abonnement
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM abonnements ORDER BY prix ASC");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ajouter des packages par défaut si la table est vide
if (empty($packages)) {
    $default_packages = [
        ['nom' => 'Starter', 'prix' => 9.99, 'credits_offerts' => 1000],
        ['nom' => 'Pro', 'prix' => 19.99, 'credits_offerts' => 2500],
        ['nom' => 'Premium', 'prix' => 49.99, 'credits_offerts' => 5500]
    ];
    $packages = $default_packages;
}
?>
<!doctype html>
<html lang="en" class="tw-dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Acheter des crédits - AI Credits</title>
    <link rel="stylesheet" href="css/tailwind-build.css">
    <link rel="stylesheet" href="css/index.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <script src="https://www.paypal.com/sdk/js?client-id=ATNKqjfci0KXJor6txjMz8qIWbAmbhXL1JWgKnmGl108_QSR3K_zKzUFHaNsIroR5D7tudYo4X1yZOaV&currency=EUR"></script>
</head>
<body class="tw-flex tw-min-h-[100vh] tw-flex-col tw-bg-[#fcfcfc] tw-text-black dark:tw-bg-black dark:tw-text-white">
    <!-- Header -->
    <header class="lg:tw-px-4 tw-max-w-[100vw] tw-max-w-lg:tw-mr-auto max-lg:tw-top-0 tw-fixed tw-top-4 lg:tw-left-1/2 lg:tw--translate-x-1/2 tw-z-20 tw-flex tw-h-[60px] tw-w-full tw-text-gray-700 tw-bg-white dark:tw-text-gray-200 dark:tw-bg-[#17181b] tw-px-[3%] tw-rounded-md lg:tw-max-w-5xl tw-shadow-md dark:tw-shadow-gray-700 lg:tw-justify-around lg:!tw-backdrop-blur-lg lg:tw-opacity-[0.99]">
        <a class="tw-flex tw-p-[4px] tw-gap-2 tw-place-items-center" href="#">
            <div class="tw-h-[30px] tw-max-w-[100px]">
                <img src="./assets/logo/logo.png" alt="logo" class="tw-object-contain tw-h-full tw-w-full dark:tw-invert" />
            </div>
            <span class="tw-text-base tw-font-medium">AI Credits</span>
        </a>
        <div class="collapsible-header animated-collapse">
            <nav class="tw-relative tw-flex tw-h-full max-lg:tw-h-max tw-w-max tw-gap-5 tw-text-base max-lg:tw-mt-[30px] max-lg:tw-flex-col max-lg:tw-gap-5 lg:tw-mx-auto tw-place-items-center">
                <a class="header-links" href="#">API</a>
                <a class="header-links" href="#">Solutions</a>
                <div class="tw-relative tw-flex tw-flex-col tw-place-items-center">
                    <div id="nav-dropdown-toggle-0" class="max-lg:tw-max-w-fit tw-flex header-links tw-gap-1 tw-place-items-center">
                        <span>Features</span>
                        <i class="tw-text-sm bi bi-chevron-down"></i>
                    </div>
                    <nav id="nav-dropdown-list-0" data-open="false" class="tw-scale-0 tw-opacity-0 lg:tw-fixed tw-flex lg:tw-top-[80px] lg:tw-left-1/2 lg:tw--translate-x-1/2 tw-w-[90%] tw-rounded-lg max-lg:tw-h-0 max-lg:tw-w-0 lg:tw-h-[450px] tw-overflow-hidden tw-bg-white dark:tw-bg-[#17181B] tw-duration-300 tw-transition-opacity tw-transition-height tw-shadow-lg tw-p-4">
                        <div class="tw-grid max-xl:tw-flex max-xl:tw-flex-col tw-justify-around tw-grid-cols-2 tw-w-full">
                            <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                                <div class="tw-font-semibold tw-text-3xl">
                                    <i class="bi bi-list-columns-reverse"></i>
                                </div>
                                <div class="tw-flex tw-flex-col tw-gap-2">
                                    <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Prompt library</div>
                                    <p>Comes packed with pre-made prompt templates</p>
                                </div> 
                            </a>
                            <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                                <div class="tw-font-semibold tw-text-3xl">
                                    <i class="bi bi-grid-1x2-fill"></i>
                                </div>
                                <div class="tw-flex tw-flex-col tw-gap-2">
                                    <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Unified Interface</div>
                                    <p class="">Test multiple AI models in one interface</p>
                                </div> 
                            </a>
                            <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                                <div class="tw-font-semibold tw-text-3xl">
                                    <i class="bi bi-globe"></i>
                                </div>
                                <div class="tw-flex tw-flex-col tw-gap-2">
                                    <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Realtime web search</div>
                                    <p class="">Search the internet in realtime</p>
                                </div> 
                            </a>
                            <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                                <div class="tw-font-semibold tw-text-3xl">
                                    <i class="bi bi-image-fill"></i>
                                </div>
                                <div class="tw-flex tw-flex-col tw-gap-2">
                                    <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Image generation</div>
                                    <p class="">Generate images from prompts</p>
                                </div> 
                            </a>
                            <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                                <div class="tw-font-semibold tw-text-3xl">
                                    <i class="bi bi-calendar-range"></i>
                                </div>
                                <div class="tw-flex tw-flex-col tw-gap-2">
                                    <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">History</div>
                                    <p class="">Continue from where you left off</p>
                                </div> 
                            </a>
                            <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                                <div class="tw-font-semibold tw-text-3xl">
                                    <i class="bi bi-translate"></i>
                                </div>
                                <div class="tw-flex tw-flex-col tw-gap-2">
                                    <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Multilingual</div>
                                    <p class="">Converse in multiple languages</p>
                                </div> 
                            </a>
                        </div>           
                    </nav>
                </div>
                <a class="header-links" href="#pricing">Pricing</a>
            </nav>
            <div class="lg:tw-mx-4 tw-flex tw-place-items-center tw-gap-[20px] tw-text-base max-md:tw-w-full max-md:tw-flex-col max-md:tw-place-content-center">
                <button type="button" onclick="toggleMode()" class="header-links tw-text-gray-600 dark:tw-text-gray-300" title="toggle-theme" id="theme-toggle">
                    <i class="bi bi-sun" id="toggle-mode-icon"></i>
                </button>
                <a href="#" aria-label="Dashboard" class="btn tw-flex tw-gap-3 tw-px-3 tw-py-2 tw-transition-transform tw-duration-[0.3s] hover:tw-translate-x-2">
                    <span>Dashboard</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <button class="bi bi-list tw-absolute tw-right-3 tw-top-3 tw-z-50 tw-text-3xl tw-text-gray-500 lg:tw-hidden" onclick="toggleHeader()" aria-label="menu" id="collapse-btn"></button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="tw-flex tw-flex-col tw-flex-1 tw-p-4 tw-max-w-7xl tw-mx-auto tw-mt-20">
        <div class="tw-flex tw-flex-col tw-gap-8">
            <h1 class="tw-text-4xl tw-font-bold tw-text-center">Acheter des crédits</h1>

            <div class="tw-flex tw-flex-col tw-gap-3">
                <div class="tw-flex tw-justify-between tw-items-center tw-bg-white dark:tw-bg-[#17181B] tw-rounded-xl tw-p-4 tw-shadow-md dark:tw-shadow-gray-700">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <i class="bi bi-credit-card-2-front tw-text-2xl tw-text-gray-600 dark:tw-text-gray-300"></i>
                        <div class="tw-flex tw-flex-col">
                            <span class="tw-text-lg tw-font-medium">Crédits actuels</span>
                            <div class="tw-text-2xl tw-font-bold"><?php echo number_format($user['credits']); ?></div>
                        </div>
                    </div>
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <span class="tw-text-xs tw-bg-blue-100 dark:tw-bg-blue-900/20 tw-text-blue-800 dark:tw-text-blue-200 tw-rounded-full tw-px-3 tw-py-1">Crédits disponibles</span>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="tw-p-4 tw-rounded-lg tw-bg-green-50 tw-text-green-700 dark:tw-bg-green-900 dark:tw-text-green-300">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="tw-p-4 tw-rounded-lg tw-bg-red-50 tw-text-red-700 dark:tw-bg-red-900 dark:tw-text-red-300">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="packages tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-8">
                <?php foreach ($packages as $i => $package): ?>
                    <div class="package tw-bg-white dark:tw-bg-[#17181B] tw-rounded-xl tw-p-6 tw-shadow-md dark:tw-shadow-gray-700 tw-transition-all tw-duration-300 hover:tw-shadow-lg">
                        <div class="tw-flex tw-justify-between tw-items-start tw-mb-4">
                            <h3 class="tw-text-2xl tw-font-bold"><?php echo htmlspecialchars($package['nom']); ?></h3>
                            <div class="tw-flex tw-items-center tw-gap-2 tw-bg-blue-100 dark:tw-bg-blue-900/20 tw-rounded-full tw-px-3 tw-py-1">
                                <span class="tw-text-xs tw-text-blue-800 dark:tw-text-blue-200"><?php echo $package['credits_offerts']; ?> crédits</span>
                            </div>
                        </div>
                        <div class="price tw-text-4xl tw-font-bold tw-text-center tw-text-blue-600 dark:tw-text-blue-400"><?php echo number_format($package['prix'], 2); ?>€</div>
                        <div class="tw-mt-6 tw-flex tw-flex-col tw-gap-4">
                            <button class="btn acheter-btn tw-w-full tw-mt-6 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-px-4 tw-py-3 tw-transition-colors tw-duration-300 hover:tw-bg-blue-700 dark:tw-bg-blue-400 dark:hover:tw-bg-blue-500" 
                                    data-id="<?= $i ?>" data-nom="<?= htmlspecialchars($package['nom']) ?>" data-prix="<?= $package['prix'] ?>" data-credits="<?= $package['credits_offerts'] ?>">
                                <span class="tw-flex tw-items-center tw-gap-2">
                                    <i class="bi bi-credit-card-2-front"></i>
                                    Acheter avec PayPal
                                </span>
                            </button>
                            <div class="paypal-boutons tw-mt-4" id="paypal-boutons-<?= $i ?>"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="tw-mt-8 tw-text-center">
                <a href="dashboard.php" class="tw-text-blue-600 dark:tw-text-blue-400 hover:tw-underline">
                    <span class="tw-flex tw-items-center tw-gap-2">
                        <i class="bi bi-arrow-left"></i>
                        Retour au dashboard
                    </span>
                </a>
            </div>
        </div>
    </main>

    <!-- Theme Toggle Script -->
    <script>
        function toggleMode() {
            document.documentElement.classList.toggle('tw-dark');
            const icon = document.getElementById('toggle-mode-icon');
            if (icon.classList.contains('bi-sun')) {
                icon.classList.remove('bi-sun');
                icon.classList.add('bi-moon');
            } else {
                icon.classList.remove('bi-moon');
                icon.classList.add('bi-sun');
            }
        }

        // Initialize theme based on user preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (prefersDark) {
            document.documentElement.classList.add('tw-dark');
            document.getElementById('toggle-mode-icon').classList.remove('bi-sun');
            document.getElementById('toggle-mode-icon').classList.add('bi-moon');
        }

        // Theme toggle for PayPal buttons
        const pseudoPHP = <?= json_encode($user['username']) ?>;
        document.querySelectorAll('.acheter-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nom = this.getAttribute('data-nom');
                const prix = this.getAttribute('data-prix');
                const credits = this.getAttribute('data-credits');

                // Empêche le double affichage
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
    </script>
</body>
</html>
<?php
$pseudo = htmlspecialchars($user['username']); // Supposons que ce soit "alex_du_78"
?>