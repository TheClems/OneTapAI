<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

\Stripe\Stripe::setApiKey($stripeSecretKey); // clé secrète API Stripe

$payload = @file_get_contents("php://input");
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

// Vérification de la signature
try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sigHeader, $stripeWebhookSecretCheckout
    );
} catch (\UnexpectedValueException $e) {
    // Mauvais JSON
    http_response_code(400);
    exit('Contenu invalide');
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Signature invalide
    http_response_code(400);
    exit('Signature invalide');
}

$pdo = getDBConnection();

function logErreur($message) {
    file_put_contents("stripe_errors.log", "[" . date("Y-m-d H:i:s") . "] $message\n", FILE_APPEND);
}

if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;

    if (!empty($session->client_reference_id) && !empty($session->subscription)) {
        $clientId = intval($session->client_reference_id);
        $subscriptionId = $session->subscription;

        $productName = null;

        try {
            // Récupération des infos d'abonnement
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            $priceId = $subscription->items->data[0]->price->id;
            $price = \Stripe\Price::retrieve($priceId);
            $productId = $price->product;
            $product = \Stripe\Product::retrieve($productId);
            $productName = $product->name;
            $customerId = $subscription->customer;
        } catch (\Exception $e) {
            logErreur("Erreur récupération abonnement : " . $e->getMessage());
            $productName = "Inconnu";
        }

        try {
            // Mise à jour en base de données
            $stmt = $pdo->prepare("UPDATE users SET stripe_user_id = ?, abonnement = ? WHERE id = ?");
            $stmt->execute([$customerId, $productName, $clientId]);
            http_response_code(200);
        } catch (PDOException $e) {
            logErreur("Erreur DB (checkout) : " . $e->getMessage());
            http_response_code(500);
        }
    } else {
        logErreur("Données manquantes dans checkout.session.completed");
        http_response_code(400);
    }
}
