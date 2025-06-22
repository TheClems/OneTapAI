<?php
require 'vendor/autoload.php'; // Stripe PHP SDK
\Stripe\Stripe::setApiKey('sk_test_51RcoRWRpHQWEgzdpOCZacqLoI6cSuDptFH8kNlj7z9MdjtGeyvOqASjZWGrO2yO0tUFRNmlhgrbffAwiV4Qcosid00SpgNlasL'); // ta clé secrète

// Récupère la charge utile brute du webhook
$payload = @file_get_contents("php://input");
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$endpoint_secret = 'whsec_o6D7bLYjdCP1cOh1vAF0CEqUu9tFgNFP'; // ton webhook secret

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch (\UnexpectedValueException $e) {
    // Mauvaise charge utile
    http_response_code(400);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Signature invalide
    http_response_code(400);
    exit();
}

// Vérifie que l'événement est bien une session de paiement complétée
if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;

    // Extrait les infos importantes
    $client_reference_id = $session->client_reference_id; // ex: 12
    $customer_email = $session->customer_details->email;
    $stripe_customer_id = $session->customer;
    $subscription_id = $session->subscription;
    $montant_total = $session->amount_total;

}

http_response_code(200); // OK
?>
