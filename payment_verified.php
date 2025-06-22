<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once 'config.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit();
}

require 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51RcoRWRpHQWEgzdpOCZacqLoI6cSuDptFH8kNlj7z9MdjtGeyvOqASjZWGrO2yO0tUFRNmlhgrbffAwiV4Qcosid00SpgNlasL');

$endpoint_secret = 'whsec_o6D7bLYjdCP1cOh1vAF0CEqUu9tFgNFP';

$payload = @file_get_contents("php://input");
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

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
    http_response_code(400);
    echo "Invalid payload";
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    echo "Invalid signature";
    exit();
} catch (Exception $e) {
    http_response_code(500);
    echo "Webhook error";
    exit();
}

switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;
        
        // Données de base de la session
        $client_reference_id = $session->client_reference_id ?? null;
        $customer_email = $session->customer_details->email ?? null;
        $stripe_customer_id = $session->customer ?? null;
        $subscription_id = $session->subscription ?? null;
        $montant_total = $session->amount_total ?? 0;
        
        // RÉCUPÉRATION DES PRODUITS ACHETÉS
        try {
            // Méthode 1: Via les line_items de la session
            $line_items = \Stripe\Checkout\Session::allLineItems($session->id, [
                'limit' => 100
            ]);
            
            $produits_achetes = [];
            
            foreach ($line_items->data as $item) {
                $price_id = $item->price->id;
                $product_id = $item->price->product;
                $quantity = $item->quantity;
                $amount_total = $item->amount_total;
                
                // Récupère les détails du produit
                $product = \Stripe\Product::retrieve($product_id);
                
                $produits_achetes[] = [
                    'price_id' => $price_id,
                    'product_id' => $product_id,
                    'product_name' => $product->name,
                    'product_description' => $product->description,
                    'quantity' => $quantity,
                    'amount_total' => $amount_total,
                    'currency' => $item->currency,
                    // Métadonnées du produit (utiles pour identifier ton produit)
                    'metadata' => $product->metadata->toArray()
                ];
            }
            
            // Méthode 2: Si c'est un abonnement, récupère via la subscription
            if ($subscription_id) {
                $subscription = \Stripe\Subscription::retrieve($subscription_id);
                
                foreach ($subscription->items->data as $sub_item) {
                    $price_id = $sub_item->price->id;
                    $product_id = $sub_item->price->product;
                    
                    // Récupère les détails du produit d'abonnement
                    $product = \Stripe\Product::retrieve($product_id);
                    
                    // Ajoute les infos d'abonnement si pas déjà présentes
                    $found = false;
                    foreach ($produits_achetes as &$produit) {
                        if ($produit['product_id'] === $product_id) {
                            $produit['subscription_item_id'] = $sub_item->id;
                            $produit['subscription_interval'] = $sub_item->price->recurring->interval ?? null;
                            $found = true;
                            break;
                        }
                    }
                    
                    if (!$found) {
                        $produits_achetes[] = [
                            'price_id' => $price_id,
                            'product_id' => $product_id,
                            'product_name' => $product->name,
                            'product_description' => $product->description,
                            'subscription_item_id' => $sub_item->id,
                            'subscription_interval' => $sub_item->price->recurring->interval ?? null,
                            'metadata' => $product->metadata->toArray()
                        ];
                    }
                }
            }
            
            // LOG COMPLET pour debug
            $log_data = [
                'timestamp' => date('Y-m-d H:i:s'),
                'event_type' => 'checkout.session.completed',
                'client_reference_id' => $client_reference_id,
                'customer_email' => $customer_email,
                'customer_id' => $stripe_customer_id,
                'subscription_id' => $subscription_id,
                'montant_total' => $montant_total,
                'produits_achetes' => $produits_achetes
            ];
            
            file_put_contents('webhook_produits.json', json_encode($log_data, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
            
            // TRAITEMENT DE TES DONNÉES
            foreach ($produits_achetes as $produit) {
                // Exemple : identifier le type de produit via metadata ou nom
                if (isset($produit['metadata']['type'])) {
                    switch ($produit['metadata']['type']) {
                        case 'premium':
                            // Activer premium pour l'utilisateur
                            activerPremium($client_reference_id, $subscription_id);
                            break;
                        case 'formation':
                            // Donner accès à une formation
                            donnerAccesFormation($client_reference_id, $produit['metadata']['formation_id']);
                            break;
                    }
                } else {
                    // Ou identifier par nom de produit
                    if (strpos(strtolower($produit['product_name']), 'premium') !== false) {
                        activerPremium($client_reference_id, $subscription_id);
                    }
                }
            }
            
        } catch (Exception $e) {
            file_put_contents('webhook_errors.txt', 
                "ERROR récupération produits - " . date('Y-m-d H:i:s') . 
                " - " . $e->getMessage() . "\n", 
                FILE_APPEND
            );
        }
        
        break;
        
    case 'invoice.payment_succeeded':
        // Paiements récurrents d'abonnement
        $invoice = $event->data->object;
        $subscription_id = $invoice->subscription;
        
        // Log du paiement récurrent
        file_put_contents('webhook_paiements_recurrents.txt', 
            "Paiement récurrent - " . date('Y-m-d H:i:s') . 
            " - Subscription: " . $subscription_id . 
            " - Montant: " . $invoice->amount_paid . "\n", 
            FILE_APPEND
        );
        break;
        
    default:
        break;
}

// Fonctions d'exemple pour traiter les produits
function activerPremium($user_id, $subscription_id) {
    // Exemple : mise à jour base de données
    // UPDATE users SET is_premium = 1, stripe_subscription_id = '$subscription_id' WHERE id = $user_id
    file_put_contents('premium_activations.txt', 
        "Premium activé - " . date('Y-m-d H:i:s') . 
        " - User: " . $user_id . 
        " - Subscription: " . $subscription_id . "\n", 
        FILE_APPEND
    );
}

function donnerAccesFormation($user_id, $formation_id) {
    // Exemple : donner accès à une formation spécifique
    file_put_contents('formations_acces.txt', 
        "Accès formation - " . date('Y-m-d H:i:s') . 
        " - User: " . $user_id . 
        " - Formation: " . $formation_id . "\n", 
        FILE_APPEND
    );
}

$pdo = getDBConnection();
try {
    $stmt = $pdo->prepare("UPDATE users SET stripe_user_id='$customer_id', stripe_subscription_id = '$subscription_id', abonnement ='$product_name', abonnement_date = '$timestamp' WHERE id = $user_id");
    $stmt->execute();
} catch (PDOException $e) {
    error_log("Erreur mise à jour utilisateur: " . $e->getMessage());
}


http_response_code(200);
echo "Webhook received";
exit();
?>

