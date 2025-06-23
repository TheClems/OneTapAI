<?php
require_once 'config.php';

$input = @file_get_contents("php://input");
$event = json_decode($input, true);

if (!$event) {
    http_response_code(400);
    exit("Webhook invalide");
}

$pdo = getDBConnection();

// Pour diagnostiquer
function logErreur($message) {
    file_put_contents("stripe_errors.log", "[" . date("Y-m-d H:i:s") . "] $message\n", FILE_APPEND);
}

if ($event['type'] === 'checkout.session.completed') {
    $session = $event['data']['object'];

    if (!empty($session['client_reference_id']) && !empty($session['subscription'])) {
        $clientId = intval($session['client_reference_id']);
        $subscriptionId = $session['subscription'];
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        $subscriptionId = $session['subscription'];
        
        try {
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            $priceId = $subscription->items->data[0]->price->id;
            $price = \Stripe\Price::retrieve($priceId);
            $productId = $price->product;
            $product = \Stripe\Product::retrieve($productId);
            $productName = $product->name;
        
            // Tu peux maintenant utiliser $productName comme tu veux
            // Par exemple, le stocker en base de données
        } catch (\Exception $e) {
            logErreur("Erreur lors de la récupération du nom de l'abonnement : " . $e->getMessage());
        }

        try {
            $stmt = $pdo->prepare("UPDATE users SET stripe_subscription_id = ?, abonnement = ? WHERE id = ?");
            $stmt->execute([$subscriptionId, $productName, $clientId]);
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
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$customerEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userId = $user['id'];

            // Mettre à jour les infos
            $stmt = $pdo->prepare("UPDATE users SET stripe_user_id = ?, abonnement_date = ? WHERE id = ?");
            $stmt->execute([$customerId, $abonnementDate, $userId]);

            http_response_code(200);
        } else {
            logErreur("Aucun utilisateur avec l'email $customerEmail");
            http_response_code(404);
        }

    } catch (PDOException $e) {
        logErreur("DB error (invoice): " . $e->getMessage());
        http_response_code(500);
    }
}

