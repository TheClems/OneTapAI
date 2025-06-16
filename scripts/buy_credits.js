// PayPal integration
const pseudoPHP = <?= json_encode($user['username']) ?>;

document.querySelectorAll('.acheter-btn').forEach(function (button) {
    button.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        const nom = this.getAttribute('data-nom');
        const prix = this.getAttribute('data-prix');
        const credits = this.getAttribute('data-credits');

        this.disabled = true;

        paypal.Buttons({
            createOrder: function (data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        description: nom + " - " + credits + " crédits",
                        custom_id: pseudoPHP + "-" + nom,
                        invoice_id: "FACTURE-" + pseudoPHP + "-" + nom,
                        amount: {
                            value: prix,
                            currency_code: 'EUR'
                        }
                    }],
                    application_context: {
                        shipping_preference: "NO_SHIPPING"
                    }
                });
            },
            onApprove: function (data, actions) {
                return actions.order.capture().then(function (details) {
                    alert("✅ Paiement réussi par " + details.payer.name.given_name + " !");
                    console.log("Détails : ", details);
                });
            }
        }).render("#paypal-boutons-" + id);
    });
});