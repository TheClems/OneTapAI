<?php
// Évite toute redirection ou output
ini_set('display_errors', 0);
error_reporting(0);

// Force la méthode POST seulement
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit();
}

require 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51RcoRWRpHQWEgzdpOCZacqLoI6cSuDptFH8kNlj7z9MdjtGeyvOqASjZWGrO2yO0tUFRNmlhgrbffAwiV4Qcosid00SpgNlasL');

$endpoint_secret = 'whsec_o6D7bLYjdCP1cOh1vAF0CEqUu9tFgNFP';

// Récupère la charge utile brute du webhook
$payload = @file_get_contents("php://input");
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Vérifie que les données nécessaires sont présentes
if (empty($payload) || empty($sig_header)) {
    http_response_code(400);
    echo "Bad Request";
    exit();
}

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch (\UnexpectedValueException $e) {
    // Mauvaise charge utile
    http_response_code(400);
    echo "Invalid payload";
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Signature invalide
    http_response_code(400);
    echo "Invalid signature";
    exit();
} catch (Exception $e) {
    // Autres erreurs
    http_response_code(500);
    echo "Webhook error";
    exit();
}

// Traite l'événement
switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;
        
        // Extrait les infos importantes
        $client_reference_id = $session->client_reference_id ?? null;
        $customer_email = $session->customer_details->email ?? null;
        $stripe_customer_id = $session->customer ?? null;
        $subscription_id = $session->subscription ?? null;
        $montant_total = $session->amount_total ?? 0;
        
        // Ici tu peux traiter les données (base de données, emails, etc.)
        // Exemple avec les données reçues :
        // Customer ID : cus_SXxuE1i9QtiV6t
        // Subscription ID : sub_1RcryXRpHQWEgzdpotkO4NbI  
        // Email : gateaublabla.gateaublabla@gmail.com
        // Client ref : 12
        
        // Log pour debug (supprime en production)
        file_put_contents('webhook_success.txt', 
            "SUCCESS - " . date('Y-m-d H:i:s') . 
            " - Customer: " . $stripe_customer_id . 
            " - Email: " . $customer_email . "\n", 
            FILE_APPEND
        );
        
        break;
        
    case 'invoice.payment_succeeded':
        // Traite les paiements récurrents
        break;
        
    case 'customer.subscription.deleted':
        // Traite les annulations d'abonnement
        break;
        
    default:
        // Événement non géré
        break;
}

// Répond toujours avec un 200
http_response_code(200);
echo "Webhook received";
exit();
?>