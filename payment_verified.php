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

    // Traitement des abonnements (mode subscription)
    if ($session->mode === 'subscription' && !empty($session->client_reference_id) && !empty($session->subscription)) {

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
    // Traitement des paiements ponctuels (mode payment)
    elseif ($session->mode === 'payment' && !empty($session->client_reference_id)) {
        $clientId = intval($session->client_reference_id);
        $productName = null;

        try {
            // 1. Récupère les line items de la session
            $line_items = \Stripe\Checkout\Session::allLineItems($session->id, ['limit' => 1]);

            if (!empty($line_items->data)) {
                $priceId = $line_items->data[0]->price->id;
                
                // 2. Récupère l'objet Price
                $price = \Stripe\Price::retrieve($priceId);

                // 3. Récupère le produit lié au Price
                $product = \Stripe\Product::retrieve($price->product);
                $productName = $product->name;
            } else {
                $productName = "Aucun produit";
            }

        } catch (\Exception $e) {
            logErreur("Erreur récupération du produit : " . $e->getMessage());
            $productName = "Inconnu";
        }

        try {
            // Utilisation de l'id utilisateur (client_reference_id) et non stripe_user_id
            $stmt = $pdo->prepare("SELECT id, abonnement, credits FROM users WHERE id = ?");
            $stmt->execute([$clientId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($user) {
                // Vérifier que l'abonnement existe
                $stmt = $pdo->prepare("SELECT * FROM paiement WHERE nom = ?");
                $stmt->execute([$productName]);
                $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$paiement) {
                    logErreur("Aucun plan de paiement trouvé pour l'abonnement: " . $productName);
                    http_response_code(404);
                    exit();
                }
            
                $userId = $user['id'];
                
                // Log pour débugger
                $totalCredits = $user['credits'] + $paiement['nb_credits'];
                $stmt = $pdo->prepare("UPDATE users SET credits = ? WHERE id = ?");
                $success = $stmt->execute([$totalCredits, $userId]);
                
                if ($success) {
                    http_response_code(200);
                    echo "OK";
                } else {
                    http_response_code(500);
                }
            } else {
                logErreur("Utilisateur non trouvé avec l'ID: " . $clientId);
                http_response_code(404);
            }
        } catch (PDOException $e) {
            logErreur("Erreur DB (payment) : " . $e->getMessage());
            http_response_code(500);
        }

    } else {
        logErreur("Mode de session non pris en charge ou données manquantes. Mode: " . $session->mode);
        http_response_code(400);
    }
}