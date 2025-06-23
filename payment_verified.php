<?php
require_once 'config.php'; // contient $conn = new mysqli(...);

// Lire le corps brut de la requête Stripe
$input = @file_get_contents("php://input");
$event = json_decode($input, true);

if (!$event) {
    http_response_code(400);
    exit("Webhook invalide");
}

// Cas 1 : Checkout terminé => on récupère le client_reference_id
if ($event['type'] === 'checkout.session.completed') {
    $session = $event['data']['object'];

    if (!empty($session['client_reference_id']) && !empty($session['subscription'])) {
        $client_id = intval($session['client_reference_id']);
        $subscription_id = $session['subscription'];

        // Enregistrer le lien dans la base (ex. : pour s'en servir plus tard)
        $stmt = $conn->prepare("UPDATE users SET stripe_subscription_id = ? WHERE id = ?");
        $stmt->bind_param("si", $subscription_id, $client_id);

        if ($stmt->execute()) {
            http_response_code(200);
        } else {
            error_log("Erreur UPDATE subscription_id: " . $stmt->error);
            http_response_code(500);
        }

        $stmt->close();
    } else {
        error_log("client_reference_id ou subscription manquant");
        http_response_code(400);
    }
}

// Cas 2 : Paiement réussi de la facture
elseif ($event['type'] === 'invoice.payment_succeeded') {
    $invoice = $event['data']['object'];

    $invoice_id = $invoice['id'];
    $subscription_id = $invoice['subscription'];
    $customer_id = $invoice['customer'];
    $amount_paid = $invoice['amount_paid'];
    $currency = $invoice['currency'];
    $status = $invoice['status'];
    $invoice_url = $invoice['hosted_invoice_url'];
    $invoice_pdf = $invoice['invoice_pdf'];
    $timestamp = $invoice['created'];
    $abonnement_date = date('Y-m-d H:i:s', $timestamp);
    $abonnement = 1; // Exemple : 1 = abonnement actif

    // Trouver l'utilisateur via subscription_id
    $stmt = $conn->prepare("SELECT id FROM users WHERE stripe_subscription_id = ?");
    $stmt->bind_param("s", $subscription_id);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    if (!empty($user_id)) {
        // Mise à jour de l'utilisateur
        $stmt = $conn->prepare("UPDATE users SET stripe_user_id = ?, abonnement = ?, abonnement_date = ? WHERE id = ?");
        $stmt->bind_param("sisi", $customer_id, $abonnement, $abonnement_date, $user_id);

        if ($stmt->execute()) {
            http_response_code(200);
        } else {
            error_log("Erreur UPDATE abonnement: " . $stmt->error);
            http_response_code(500);
        }

        $stmt->close();
    } else {
        error_log("Aucun utilisateur avec subscription_id $subscription_id");
        http_response_code(404);
    }
}

else {
    // Autres types d'événements non gérés
    http_response_code(400);
    echo "Événement non géré : " . $event['type'];
}
?>
