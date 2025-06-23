<?php
require_once 'config.php'; // Ton fichier avec getDBConnection()

// Lire le corps brut envoyé par Stripe
$input = @file_get_contents("php://input");
$event = json_decode($input, true);

if (!$event) {
    http_response_code(400);
    exit("Webhook invalide");
}

$pdo = getDBConnection();

if ($event['type'] === 'checkout.session.completed') {
    $session = $event['data']['object'];

    if (!empty($session['client_reference_id']) && !empty($session['subscription'])) {
        $clientId = intval($session['client_reference_id']);
        $subscriptionId = $session['subscription'];

        try {
            $stmt = $pdo->prepare("UPDATE users SET stripe_subscription_id = ? WHERE id = ?");
            $stmt->execute([$subscriptionId, $clientId]);
            http_response_code(200);
        } catch (PDOException $e) {
            error_log("Erreur DB (checkout) : " . $e->getMessage());
            http_response_code(500);
        }
    } else {
        error_log("Données manquantes dans checkout.session.completed");
        http_response_code(400);
    }

} elseif ($event['type'] === 'invoice.payment_succeeded') {
    $invoice = $event['data']['object'];

    $invoiceId = $invoice['id'];
    $subscriptionId = $invoice['subscription'];
    $customerId = $invoice['customer'];
    $amountPaid = $invoice['amount_paid'];
    $currency = $invoice['currency'];
    $status = $invoice['status'];
    $invoiceUrl = $invoice['hosted_invoice_url'];
    $invoicePdf = $invoice['invoice_pdf'];
    $timestamp = $invoice['created'];
    $abonnementDate = date('Y-m-d H:i:s', $timestamp);
    $abonnement = 1;

    try {
        // Trouver l'utilisateur via l'abonnement Stripe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE stripe_subscription_id = ?");
        $stmt->execute([$subscriptionId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userId = $user['id'];

            // Mettre à jour les infos dans la base
            $stmt = $pdo->prepare("UPDATE users SET stripe_user_id = ?, abonnement = ?, abonnement_date = ? WHERE id = ?");
            $stmt->execute([$customerId, $abonnement, $abonnementDate, $userId]);

            http_response_code(200);
        } else {
            error_log("Aucun utilisateur trouvé avec subscription_id: $subscriptionId");
            http_response_code(404);
        }

    } catch (PDOException $e) {
        error_log("Erreur DB (invoice) : " . $e->getMessage());
        http_response_code(500);
    }

} else {
    // Événement non géré
    http_response_code(400);
    echo "Événement non géré : " . $event['type'];
}
