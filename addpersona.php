<?php


require_once 'config.php';
getConnection();

$message = '';

// Traitement du formulaire
if ($_POST) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $message_user = trim($_POST['message']);
    
    // Validation des données
    $erreurs = [];
    
    if (empty($nom)) {
        $erreurs[] = "Le nom est requis";
    }
    
    if (empty($email)) {
        $erreurs[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email n'est pas valide";
    }
    
    if (empty($telephone)) {
        $erreurs[] = "Le téléphone est requis";
    }
    
    // Si pas d'erreurs, insertion en base
    if (empty($erreurs)) {
        try {
            $sql = "INSERT INTO personas (nom, email, telephone, message, date_creation) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $email, $telephone, $message_user]);
            
            $message = "<div class='alert alert-success'>Données enregistrées avec succès !</div>";
            
            // Réinitialiser le formulaire
            $_POST = [];
            
        } catch(PDOException $e) {
            $message = "<div class='alert alert-error'>Erreur lors de l'enregistrement : " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-error'>" . implode('<br>', $erreurs) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de contact</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        textarea:focus {
            outline: none;
            border-color: #4CAF50;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #45a049;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .required {
            color: red;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Formulaire de Contact</h1>
        
        <?php echo $message; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nom">Nom complet <span class="required">*</span></label>
                <input type="text" id="nom" name="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="telephone">Téléphone <span class="required">*</span></label>
                <input type="tel" id="telephone" name="telephone" value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="Votre message..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
            </div>
            
            <button type="submit" class="btn">Envoyer</button>
        </form>
    </div>
</body>
</html>