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
        $token = bin2hex(random_bytes(32)); // 64 caractères hexadécimaux (256 bits)

        $to = $user['email'];
        $subject = "Mot de passe oublié";
        $message = "Bonjour " . htmlspecialchars($user['username']) . ",\n\nVoici un lien pour réinitialiser votre mot de passe : https://onetapai.ctts.fr/forgot_passwd.php?token=" . $token;
        $headers = "From: contact@ctts.fr";

        if (mail($to, $subject, $message, $headers)) {
            $success = "Un e-mail de réinitialisation a été envoyé à l'adresse : " . htmlspecialchars($to);
            $stmt = $pdo->prepare("UPDATE users SET forgot_password_id = ? WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
        } else {
            $error = "Une erreur est survenue lors de l'envoi de l'e-mail.";
        }

    } else {
        $error = "Aucun compte trouvé avec cette adresse ou ce nom d'utilisateur.";
    }
}

// Si l'utilisateur soumet le formulaire de réinitialisation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['token']) && isset($_POST['password'], $_POST['confirm_password'])) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } else {
        // 🔐 Hasher le mot de passe

        // 🔎 Retrouver l'utilisateur avec le token
        $token = $_GET['token'];
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE forgot_password_id = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // ✅ Mettre à jour le mot de passe
            $stmt = $pdo->prepare("UPDATE users SET password = ?, forgot_password_id = NULL WHERE id = ?");
            $stmt->execute([$password, $user['id']]);

            $success = "Mot de passe mis à jour avec succès.";
        } else {
            $error = "Lien invalide ou expiré.";
        }
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
    <?php if(!isset($_GET['token'])){
    ?>
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
    <?php }else{
    ?>
    <div class="container" id="container">

            <div class="form-container sign-in-container">
                <form method="POST">
                    <h1>Enter your new password</h1>
    
                    <input id="password" name="password" placeholder="Password" required="">
                    <input id="confirm_password" name="confirm_password" placeholder="Confirm Password" required="">

    
                    <button>Change password</button>
                </form>
            </div>
    
        </div>
    <?php }?>

    
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