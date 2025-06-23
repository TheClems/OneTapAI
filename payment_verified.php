<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

$input = @file_get_contents("php://input");
$event = json_decode($input, true);

if (!$event) {
    http_response_code(400);
    exit("Webhook invalide");
}

$pdo = getDBConnection();

function logErreur($message) {
    file_put_contents("stripe_errors.log", "[" . date("Y-m-d H:i:s") . "] $message\n", FILE_APPEND);
}

if ($event['type'] === 'checkout.session.completed') {
    $session = $event['data']['object'];

    if (!empty($session['client_reference_id']) && !empty($session['subscription'])) {
        $clientId = intval($session['client_reference_id']);
        $subscriptionId = $session['subscription'];
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        $productName = null; // <- initialisation

        try {
            // Étape 1 : récupérer le nom de l'abonnement
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            $priceId = $subscription->items->data[0]->price->id;
            $price = \Stripe\Price::retrieve($priceId);
            $productId = $price->product;
            $product = \Stripe\Product::retrieve($productId);
            $productName = $product->name;
            $customerId = $subscription->customer;
        } catch (\Exception $e) {
            logErreur("Erreur lors de la récupération du nom de l'abonnement : " . $e->getMessage());
            $productName = "Inconnu"; // valeur de secours
        }

        try {
            // Étape 2 : mise à jour en base de données
            $stmt = $pdo->prepare("UPDATE users SET stripe_user_id = ?, abonnement = ? WHERE id = ?");
            $stmt->execute([$customerId, $productName, $clientId]);
            http_response_code(200);
        } catch (PDOException $e) {
            logErreur("DB error (checkout): " . $e->getMessage());
            http_response_code(500);
        }
    } else {
        logErreur("Données manquantes dans checkout.session.completed");
        http_response_code(400);
    }
} elseif ($event['type'] === 'invoice.payment_succeeded') {
    $invoice = $event['data']['object'];

    $customerId = $invoice['customer'] ?? null;
    $customerEmail = $invoice['customer_email'] ?? null;
    $timestamp = $invoice['created'] ?? null;

    // Nouvelle récupération du subscription_id
    $subscriptionId = $invoice['lines']['data'][0]['parent']['subscription_item_details']['subscription'] ?? null;

    if (!$customerId || !$timestamp || !$customerEmail || !$subscriptionId) {
        logErreur("invoice.payment_succeeded incomplet: " . print_r($invoice, true));
        http_response_code(400);
        exit();
    }

    $abonnementDate = date('Y-m-d H:i:s', $timestamp);

    try {
        // Chercher l'utilisateur par email
        $stmt = $pdo->prepare("SELECT id, abonnement FROM users WHERE stripe_user_id = ?");
        $stmt->execute([$customerId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($user) {
            // seulement ici on peut accéder à $user['abonnement']
            $stmt = $pdo->prepare("SELECT * FROM paiement WHERE nom = ?");
            $stmt->execute([$user['abonnement']]);
            $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
        
            $userId = $user['id'];
        
            $stmt = $pdo->prepare("UPDATE users SET abonnement_date = ?, credits = credits + ? WHERE id = ?");
            $stmt->execute([$abonnementDate, $paiement['credits'], $userId]);
        
            http_response_code(200);
        } else {
            logErreur("Aucun utilisateur avec le stripe_user_id $customerId");
            http_response_code(404);
        }

    } catch (PDOException $e) {
        logErreur("DB error (invoice): " . $e->getMessage());
        http_response_code(500);
    }
}

