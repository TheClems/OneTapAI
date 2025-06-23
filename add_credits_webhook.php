<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

\Stripe\Stripe::setApiKey($stripeSecretKey); // Clé API Stripe

$payload = @file_get_contents("php://input");
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

// Vérifie l'authenticité du webhook via sa signature
try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sigHeader, $stripeWebhookSecretInvoice
    );
} catch (\UnexpectedValueException $e) {
    http_response_code(400);
    exit("Contenu JSON invalide");
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    exit("Signature Stripe invalide");
}

$pdo = getDBConnection();

function logErreur($message) {
    file_put_contents("stripe_errors.log", "[" . date("Y-m-d H:i:s") . "] $message\n", FILE_APPEND);
}

if ($event->type === 'invoice.payment_succeeded') {

    sleep(3);
    $invoice = $event->data->object;

    $customerId = $invoice->customer ?? null;
    $timestamp = $invoice->created ?? null;
    $subscriptionId = $invoice->subscription ?? null;

    if (!$customerId || !$timestamp || !$subscriptionId) {
        logErreur("invoice.payment_succeeded incomplet - customerId: $customerId, timestamp: $timestamp, subscriptionId: $subscriptionId");
        logErreur("Contenu complet de l'invoice: " . print_r($invoice, true));
        http_response_code(400);
        exit();
    }

    $abonnementDate = date('Y-m-d H:i:s', $timestamp);

    try {
        $stmt = $pdo->prepare("SELECT id, abonnement, credits FROM users WHERE stripe_user_id = ?");
        $stmt->execute([$customerId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $stmt = $pdo->prepare("SELECT * FROM paiement WHERE nom = ?");
            $stmt->execute([$user['abonnement']]);
            $paiement = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$paiement) {
                logErreur("Aucun plan de paiement trouvé pour l'abonnement: " . $user['abonnement']);
                http_response_code(404);
                exit();
            }

            $userId = $user['id'];
            logErreur("Mise à jour utilisateur $userId - Date: $abonnementDate, Crédits: " . $paiement['nb_credits']);

            $totalCredits = $user['credits'] + $paiement['nb_credits'];

            $stmt = $pdo->prepare("UPDATE users SET abonnement_date = ?, credits = ?, stripe_subscription_id = ? WHERE id = ?");
            $success = $stmt->execute([$abonnementDate, $totalCredits, $subscriptionId, $userId]);

            if ($success) {
                logErreur("Mise à jour réussie pour l'utilisateur $userId");
                http_response_code(200);
            } else {
                logErreur("Échec de la mise à jour pour l'utilisateur $userId");
                http_response_code(500);
            }
        } else {
            logErreur("Aucun utilisateur avec le stripe_user_id $customerId");
            http_response_code(404);
        }

    } catch (PDOException $e) {
        logErreur("DB error (invoice): " . $e->getMessage());
        http_response_code(500);
    }
}
