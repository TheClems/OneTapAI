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
    </style>
</head>
<body>
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
            <?php foreach ($packages as $package): ?>
                <div class="package">
                    <h3><?php echo htmlspecialchars($package['nom']); ?></h3>
                    <div class="price"><?php echo number_format($package['prix'], 2); ?>€</div>
                    <div class="credits"><?php echo number_format($package['credits_offerts']); ?> crédits</div>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="package" value="<?php echo strtolower($package['nom']); ?>">
                        <button type="submit" class="btn" id="acheter-btn">>Acheter maintenant</button>
                        <div id="paypal-boutons"></div>

                    </form>
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

document.getElementById("acheter-btn").addEventListener("click", function() {
    // Empêche plusieurs rendus du bouton
    document.getElementById("acheter-btn").disabled = true;

    var pseudoPHP = <?php echo json_encode($pseudo); ?>;
paypal.Buttons({
    createOrder: function (data, actions) {
        return actions.order.create({
            purchase_units: [{
                description: "Paiement pour l'utilisateur " + pseudoPHP,
                custom_id: pseudoPHP,
                invoice_id: "FACTURE-" + pseudoPHP,
                amount: {
                    value: '10.00',
                    currency_code: 'EUR'
                }
            }],
            application_context: {
                shipping_preference: "NO_SHIPPING" // ✅ Aucune adresse demandée
            }
        });
    },
    onApprove: function (data, actions) {
        return actions.order.capture().then(function (details) {
            alert("Paiement effectué par " + details.payer.name.given_name + " !");
            console.log("Détails complets :", details);
        });
    }
}).render("#paypal-boutons");
</script>