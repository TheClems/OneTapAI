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
    <link
            rel="shortcut icon"
            href="./assets/logo/logo.png"
            type="image/x-icon"
        />

        <!-- Open Graph / Facebook -->
        <meta property="og:description" content="Get all your AI models and tools in one place" />
        <meta property="og:type" content="website" />
        <meta property="og:image" content="" />

        <!-- <link rel="stylesheet" href="css/tailwind-runtime.css" /> -->
        <link rel="stylesheet" href="css/tailwind-build.css">
        <link rel="stylesheet" href="css/index.css" />

        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
            integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
        />

        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-');
        </script>
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
    </style>
</head>
<body>

<header
            class="lg:tw-px-4 tw-max-w-[100vw] tw-max-w-lg:tw-mr-auto max-lg:tw-top-0 tw-fixed tw-top-4 lg:tw-left-1/2 lg:tw--translate-x-1/2 tw-z-20 tw-flex tw-h-[60px] tw-w-full 
                    tw-text-gray-700 tw-bg-white dark:tw-text-gray-200 dark:tw-bg-[#17181b] tw-px-[3%] tw-rounded-md lg:tw-max-w-5xl tw-shadow-md dark:tw-shadow-gray-700
                    lg:tw-justify-around lg:!tw-backdrop-blur-lg lg:tw-opacity-[0.99]"
        >
            <a class="tw-flex tw-p-[4px] tw-gap-2 tw-place-items-center" href="#">
               

                <div class="tw-h-[30px] tw-max-w-[100px]">
                    <img
                        src="./assets/logo/logo.png"
                        alt="logo"
                        class="tw-object-contain tw-h-full tw-w-full dark:tw-invert"
                    />
                </div>
            </a>
            <div
                class="collapsible-header animated-collapse max-lg:tw-shadow-md"
                id="collapsed-header-items"
            >
                <nav
                    class="tw-relative tw-flex tw-h-full max-lg:tw-h-max tw-w-max tw-gap-5 tw-text-base max-lg:tw-mt-[30px] max-lg:tw-flex-col 
                                max-lg:tw-gap-5 lg:tw-mx-auto tw-place-items-center"
                >
                    <a class="header-links" href="#"> API </a>
                    <a class="header-links" href="#"> Solutions </a>
                   
                    <div class="tw-relative tw-flex tw-flex-col tw-place-items-center">
                        <div id="nav-dropdown-toggle-0" class="max-lg:tw-max-w-fit tw-flex header-links tw-gap-1  tw-place-items-center">
                            <span class=""> Features </span>
                            <i class="tw-text-sm bi bi-chevron-down"></i>
                        </div>
                        <nav id="nav-dropdown-list-0" 
                            data-open="false"
                            class="tw-scale-0 tw-opacity-0  lg:tw-fixed tw-flex lg:tw-top-[80px] lg:tw-left-1/2 lg:tw--translate-x-1/2 
                                    tw-w-[90%] tw-rounded-lg max-lg:tw-h-0 max-lg:tw-w-0
                                    lg:tw-h-[450px] tw-overflow-hidden
                                     tw-bg-white dark:tw-bg-[#17181B] tw-duration-300 
                                     tw-transition-opacity tw-transition-height tw-shadow-lg tw-p-4">
                            <div class="tw-grid max-xl:tw-flex max-xl:tw-flex-col tw-justify-around tw-grid-cols-2 tw-w-full">
                                <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                                    <div class="tw-font-semibold tw-text-3xl">
                                        <i class="bi bi-list-columns-reverse"></i>
                                    </div>
                                    <div class="tw-flex tw-flex-col tw-gap-2">
                                        <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Prompt library </div>
                                        <p>Comes packed with pre-made prompt templates</p>
                                    </div> 
                                </a>

                                <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                                    <div class="tw-font-semibold tw-text-3xl">
                                        <i class="bi bi-grid-1x2-fill"></i>
                                    </div>
                                    <div class="tw-flex tw-flex-col tw-gap-2">
                                        <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Unified Interface </div>
                                        <p class="">Test multiple AI models in one interface</p>
                                    </div> 
                                </a>

                                <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                                    <div class="tw-font-semibold tw-text-3xl">
                                        <i class="bi bi-globe"></i>
                                    </div>
                                    <div class="tw-flex tw-flex-col tw-gap-2">
                                        <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">Realtime web search </div>
                                        <p class="">Search the internet in realtime</p>
                                    </div> 
                                </a>

                                <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                                    <div class="tw-font-semibold tw-text-3xl">
                                        <i class="bi bi-image-fill"></i>
                                    </div>
                                    <div class="tw-flex tw-flex-col tw-gap-2">
                                        <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">
                                            Image generation
                                        </div>
                                        <p class="">Generate images from prompts</p>
                                    </div> 
                                </a>

                                <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                                    <div class="tw-font-semibold tw-text-3xl">
                                        <i class="bi bi-calendar-range"></i>
                                    </div>
                                    <div class="tw-flex tw-flex-col tw-gap-2">
                                        <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">
                                            History
                                        </div>
                                        <p class="">Continue from where you left off</p>
                                    </div> 
                                </a>

                                <a class="header-links tw-flex tw-text-left tw-gap-4 !tw-p-4" href="#">
                                    <div class="tw-font-semibold tw-text-3xl">
                                        <i class="bi bi-translate"></i>
                                    </div>
                                    <div class="tw-flex tw-flex-col tw-gap-2">
                                        <div class="tw-text-lg tw-text-black dark:tw-text-white tw-font-medium">
                                            Multilingual
                                        </div>
                                        <p class="">Converse in multiple languages</p>
                                    </div> 
                                </a>
                            </div>           
                        </nav>
                    </div>
                    <a class="header-links" href="#pricing"> Pricing </a>
                    
                </nav>
                <div
                    class="lg:tw-mx-4 tw-flex tw-place-items-center tw-gap-[20px] tw-text-base max-md:tw-w-full 
                            max-md:tw-flex-col max-md:tw-place-content-center"
                >
                    <button type="button" onclick="toggleMode()" class="header-links tw-text-gray-600 dark:tw-text-gray-300" title="toggle-theme" 
                            id="theme-toggle"> 
                        <i class="bi bi-sun" id="toggle-mode-icon"></i>
                    </button>
                    <a
                        href="#"
                        class="btn tw-flex tw-gap-3 tw-px-3 tw-py-2 tw-transition-transform 
                                    tw-duration-[0.3s] hover:tw-translate-x-2"
                    >
                        <span>Try playground</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            <button
                class="bi bi-list tw-absolute tw-right-3 tw-top-3 tw-z-50 tw-text-3xl tw-text-gray-500 lg:tw-hidden"
                onclick="toggleHeader()"
                aria-label="menu"
                id="collapse-btn"
            ></button>
        </header>
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

<script
        src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.0/gsap.min.js"
        integrity="sha512-B1lby8cGcAUU3GR+Fd809/ZxgHbfwJMp0jLTVfHiArTuUt++VqSlJpaJvhNtRf3NERaxDNmmxkdx2o+aHd4bvw=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    ></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.0/ScrollTrigger.min.js"
        integrity="sha512-AY2+JxnBETJ0wcXnLPCcZJIJx0eimyhz3OJ55k2Jx4RtYC+XdIi2VtJQ+tP3BaTst4otlGG1TtPJ9fKrAUnRdQ=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    ></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.0.10/typed.min.js" integrity="sha512-hIlMpy2enepx9maXZF1gn0hsvPLerXoLHdb095CmRY5HG3bZfN7XPBZ14g+TUDH1aGgfLyPHmY9/zuU53smuMw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="./scripts/components.js"></script>
    <script src="./scripts/index.js"></script>