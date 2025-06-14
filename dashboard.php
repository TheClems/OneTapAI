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
    <title>Dashboard - AI Credits</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://www.paypal.com/sdk/js?client-id=ATNKqjfci0KXJor6txjMz8qIWbAmbhXL1JWgKnmGl108_QSR3K_zKzUFHaNsIroR5D7tudYo4X1yZOaV"></script>

</head>
<body>
    <div class="container">
        <div class="header">
            <div class="user-info">
                <h1 class="welcome">Bienvenue, <?php echo htmlspecialchars($user['username']); ?></h1>
                <div class="actions-header">
                    <a href="auth.php?mode=edit_profile" class="btn btn-success">Modifier le profil</a>
                    <a href="logout.php" class="logout">Déconnexion</a>
                </div>
            </div>
        </div>
        
        <div class="credits-box">
            <h2>Vos crédits</h2>
            <div class="credits-number"><?php echo number_format($user['credits']); ?></div>
            <span class="credits-text">crédits disponibles</span>
        </div>
        
        <div class="actions">
            <a href="buy_credits.php" class="btn btn-success">Acheter des crédits</a>
            <div id="paypal-boutons"></div>

            <a href="new_chat.php" class="btn btn-primary">Créer un nouveau chat</a>
        </div>
        
        <div class="section account-info">
            <h3>Informations du compte</h3>
            <div class="info-group">
                <div class="info-item">
                    <span class="label">Nom d'utilisateur :</span>
                    <span class="value"><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Email :</span>
                    <span class="value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Date d'inscription :</span>
                    <span class="value"><?php echo date('d/m/Y à H:i', strtotime($user['date_inscription'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Crédits :</span>
                    <span class="value"><?php echo number_format($user['credits']); ?></span>
                </div>
            </div>
        </div>
        
        <div class="section features">
            <h3>Fonctionnalités disponibles</h3>
            <ul>
                <li>Chat avec des IA avancées</li>
                <li>Historique de vos conversations</li>
                <li>Gestion flexible de vos crédits</li>
                <li>Support technique</li>
            </ul>
        </div>
    </div>
</body>
</html>

<script>
	// 2. Afficher le bouton PayPal
	paypal.Buttons().render("#paypal-boutons");
    paypal.Buttons({

// Configurer la transaction
createOrder : function (data, actions) {

    // Les produits à payer avec leurs details
    var produits = [
        {
            name : "Produit 1",
            description : "Description du produit 1",
            quantity : 1,
            unit_amount : { value : 9.9, currency_code : "USD" }
        },
        {
            name : "Produit 2",
            description : "Description du produit 2",
            quantity : 1,
            unit_amount : { value : 8.0, currency_code : "USD" }
        }
    ];

    // Le total des produits
    var total_amount = produits.reduce(function (total, product) {
        return total + product.unit_amount.value * product.quantity;
    }, 0);

    // La transaction
    return actions.order.create({
        purchase_units : [{
            items : produits,
            amount : {
                value : total_amount,
                currency_code : "USD",
                breakdown : {
                    item_total : { value : total_amount, currency_code : "USD" }
                }
            }
        }]
    });
}

}).render("#paypal-boutons");
</script>