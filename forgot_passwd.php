<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// On initialise les variables pour éviter les warnings
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = htmlspecialchars($_POST['email']);

    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT id, email, password, username FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $to = $user['email'];
        $subject = "Mot de passe oublié";
        $message = "Bonjour " . htmlspecialchars($user['username']) . ",\n\nVoici un lien pour réinitialiser votre mot de passe : ...";
        $headers = "From: contact@ctts.fr";

        if (mail($to, $subject, $message, $headers)) {
            $success = "Un e-mail de réinitialisation a été envoyé à l'adresse : " . htmlspecialchars($to);
        } else {
            $error = "Une erreur est survenue lors de l'envoi de l'e-mail.";
        }

    } else {
        $error = "Aucun compte trouvé avec cette adresse ou ce nom d'utilisateur.";
    }
}
?>

<link rel="stylesheet" href="css/auth.css" />

<?php if (!empty($error)): ?>
    <div class="alert alert-error" style="color: red; font-weight: bold; font-size: 16px; margin-bottom: 10px; text-align: center; padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success" style="color: green; font-weight: bold; font-size: 16px; margin-bottom: 10px; text-align: center; padding: 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<html>

<head>
    <link rel="stylesheet" href="css/auth.css">



</head>

<style>
    .sign-in-container {
        width: 100%;

    }
    h1{
        margin-bottom: 30px;
    }

    button{
        margin-top: 30px;
    }
</style>


<body>
    <div class="container" id="container">

        <div class="form-container sign-in-container">
            <form method="POST">
                <h1>Forgot Password ?</h1>

                <span>Enter your email or username</span>
                <input id="email" name="email" placeholder="Email/Username" required="">


                <button>Send code</button>
            </form>
        </div>

    </div>

    <script type="text/javascript" src="scripts/auth.js"></script>
    <script>
        function delayedRedirect(url) {
            setTimeout(function() {
                window.location.href = url;
            }, 800);
        }
    </script>
</body>

</html>

<script type="text/javascript" src="scripts/auth.js"></script>
<script>
    function delayedRedirect(url) {
        setTimeout(function() {
            window.location.href = url;
        }, 800);
    }
</script>