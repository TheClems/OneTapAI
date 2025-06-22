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
        $timestamp = date('Y-m-d H:i:s');
        
        // LOG de débogage
        file_put_contents('webhook_debug.log', 
            "=== DÉBUT TRAITEMENT ===\n" .
            "Timestamp: $timestamp\n" .
            "Client Reference ID: $client_reference_id\n" .
            "Customer Email: $customer_email\n" .
            "Stripe Customer ID: $stripe_customer_id\n" .
            "Subscription ID: $subscription_id\n" .
            "Montant Total: $montant_total\n\n", 
            FILE_APPEND
        );
        
        // RÉCUPÉRATION DES PRODUITS ACHETÉS
        try {
            $line_items = \Stripe\Checkout\Session::allLineItems($session->id, [
                'limit' => 100
            ]);
            
            $produits_achetes = [];
            $product_names = [];
            
            foreach ($line_items->data as $item) {
                $price_id = $item->price->id;
                $product_id = $item->price->product;
                $quantity = $item->quantity;
                $amount_total = $item->amount_total;
                
                $product = \Stripe\Product::retrieve($product_id);
                $product_names[] = $product->name;
                
                $produits_achetes[] = [
                    'price_id' => $price_id,
                    'product_id' => $product_id,
                    'product_name' => $product->name,
                    'product_description' => $product->description,
                    'quantity' => $quantity,
                    'amount_total' => $amount_total,
                    'currency' => $item->currency,
                    'metadata' => $product->metadata->toArray()
                ];
            }
            
            // Si c'est un abonnement, récupère via la subscription
            if ($subscription_id) {
                $subscription = \Stripe\Subscription::retrieve($subscription_id);
                
                foreach ($subscription->items->data as $sub_item) {
                    $price_id = $sub_item->price->id;
                    $product_id = $sub_item->price->product;
                    
                    $product = \Stripe\Product::retrieve($product_id);
                    
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
                        $product_names[] = $product->name;
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
                'timestamp' => $timestamp,
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
                if (isset($produit['metadata']['type'])) {
                    switch ($produit['metadata']['type']) {
                        case 'premium':
                            activerPremium($client_reference_id, $subscription_id);
                            break;
                        case 'formation':
                            donnerAccesFormation($client_reference_id, $produit['metadata']['formation_id']);
                            break;
                    }
                } else {
                    if (strpos(strtolower($produit['product_name']), 'premium') !== false) {
                        activerPremium($client_reference_id, $subscription_id);
                    }
                }
            }
            
            // MISE À JOUR BASE DE DONNÉES - VERSION CORRIGÉE
            if ($client_reference_id) {
                $pdo = getDBConnection();
                try {
                    // Créer une chaîne avec tous les noms de produits
                    $product_name = implode(', ', $product_names);
                    
                    file_put_contents('webhook_debug.log', 
                        "Recherche produit: '$product_name'\n", 
                        FILE_APPEND
                    );
                    
                    // RECHERCHE DES CRÉDITS - AVEC GESTION D'ERREUR
                    $stmt = $pdo->prepare("SELECT nb_credits FROM paiement WHERE nom = ?");
                    $stmt->execute([$product_name]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $nb_credits = 0; // Valeur par défaut
                    
                    if ($user && isset($user['nb_credits'])) {
                        $nb_credits = $user['nb_credits'];
                        file_put_contents('webhook_debug.log', 
                            "Crédits trouvés: $nb_credits\n", 
                            FILE_APPEND
                        );
                    } else {
                        // LOG si aucun produit trouvé
                        file_put_contents('webhook_debug.log', 
                            "ATTENTION: Aucun produit trouvé dans 'paiement' pour: '$product_name'\n", 
                            FILE_APPEND
                        );
                        
                        // Essaie avec des variantes du nom
                        $variantes = [
                            'Professional',
                            'Professionnal', 
                            'professional',
                            'professionnal'
                        ];
                        
                        foreach ($variantes as $variante) {
                            $stmt = $pdo->prepare("SELECT nb_credits FROM paiement WHERE nom = ?");
                            $stmt->execute([$variante]);
                            $user = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($user && isset($user['nb_credits'])) {
                                $nb_credits = $user['nb_credits'];
                                file_put_contents('webhook_debug.log', 
                                    "Crédits trouvés avec variante '$variante': $nb_credits\n", 
                                    FILE_APPEND
                                );
                                break;
                            }
                        }
                        
                        // Si toujours rien, liste tous les produits disponibles
                        if ($nb_credits == 0) {
                            $stmt = $pdo->prepare("SELECT nom, nb_credits FROM paiement");
                            $stmt->execute();
                            $tous_produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            file_put_contents('webhook_debug.log', 
                                "Produits disponibles dans la table 'paiement':\n" . 
                                json_encode($tous_produits, JSON_PRETTY_PRINT) . "\n", 
                                FILE_APPEND
                            );
                        }
                    }
                    
                    // VÉRIFICATION QUE L'UTILISATEUR EXISTE
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
                    $stmt->execute([$client_reference_id]);
                    $user_exists = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$user_exists) {
                        file_put_contents('webhook_debug.log', 
                            "ERREUR: Utilisateur avec ID $client_reference_id n'existe pas!\n", 
                            FILE_APPEND
                        );
                        throw new Exception("Utilisateur introuvable avec l'ID: $client_reference_id");
                    }
                    
                    file_put_contents('webhook_debug.log', 
                        "Utilisateur trouvé, mise à jour avec $nb_credits crédits\n", 
                        FILE_APPEND
                    );
                    
                    // MISE À JOUR - REQUÊTE PRÉPARÉE SÉCURISÉE
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET stripe_user_id = ?, 
                            stripe_subscription_id = ?, 
                            abonnement = ?, 
                            abonnement_date = ?,
                            credits = credits + ?
                        WHERE id = ?
                    ");
                    
                    $result = $stmt->execute([
                        $stripe_customer_id,
                        $subscription_id,
                        $product_name,
                        $timestamp,
                        $nb_credits,
                        $client_reference_id
                    ]);
                    
                    if ($result && $stmt->rowCount() > 0) {
                        file_put_contents('webhook_debug.log', 
                            "✅ SUCCÈS: Utilisateur $client_reference_id mis à jour avec $nb_credits crédits\n", 
                            FILE_APPEND
                        );
                        
                        file_put_contents('db_updates.log', 
                            "Utilisateur mis à jour: $client_reference_id - $timestamp - Crédits: $nb_credits\n", 
                            FILE_APPEND
                        );
                    } else {
                        file_put_contents('webhook_debug.log', 
                            "⚠️ ATTENTION: Requête exécutée mais aucune ligne affectée pour l'utilisateur $client_reference_id\n", 
                            FILE_APPEND
                        );
                    }
                    
                } catch (PDOException $e) {
                    $error_msg = "Erreur PDO: " . $e->getMessage();
                    error_log($error_msg);
                    
                    file_put_contents('webhook_debug.log', 
                        "❌ ERREUR DB: $error_msg - $timestamp\n", 
                        FILE_APPEND
                    );
                    
                    file_put_contents('db_errors.log', 
                        "Erreur DB: " . $e->getMessage() . " - $timestamp\n", 
                        FILE_APPEND
                    );
                } catch (Exception $e) {
                    $error_msg = "Erreur générale: " . $e->getMessage();
                    error_log($error_msg);
                    
                    file_put_contents('webhook_debug.log', 
                        "❌ ERREUR: $error_msg - $timestamp\n", 
                        FILE_APPEND
                    );
                }
            } else {
                file_put_contents('webhook_debug.log', 
                    "❌ ERREUR: client_reference_id manquant - impossible de mettre à jour l'utilisateur\n", 
                    FILE_APPEND
                );
            }
            
        } catch (Exception $e) {
            file_put_contents('webhook_errors.txt', 
                "ERROR récupération produits - " . date('Y-m-d H:i:s') . 
                " - " . $e->getMessage() . "\n", 
                FILE_APPEND
            );
            
            file_put_contents('webhook_debug.log', 
                "❌ ERREUR récupération produits: " . $e->getMessage() . "\n", 
                FILE_APPEND
            );
        }
        
        break;
        
    case 'invoice.payment_succeeded':
        $invoice = $event->data->object;
        $subscription_id = $invoice->subscription;
        
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
    file_put_contents('premium_activations.txt', 
        "Premium activé - " . date('Y-m-d H:i:s') . 
        " - User: " . $user_id . 
        " - Subscription: " . $subscription_id . "\n", 
        FILE_APPEND
    );
}

function donnerAccesFormation($user_id, $formation_id) {
    file_put_contents('formations_acces.txt', 
        "Accès formation - " . date('Y-m-d H:i:s') . 
        " - User: " . $user_id . 
        " - Formation: " . $formation_id . "\n", 
        FILE_APPEND
    );
}

http_response_code(200);
echo "Webhook received";
exit();
?>