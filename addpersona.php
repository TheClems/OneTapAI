<?php


require_once 'config.php';
getDBConnection();
$message = '';

// Traitement du formulaire
if ($_POST) {
    $logo = trim($_POST['logo']);
    $description = trim($_POST['description']);
    $categorie = trim($_POST['categorie']);
    $sous_categorie = trim($_POST['sous_categorie']);
    $tags = trim($_POST['tags']);
    $model = trim($_POST['model']);
    $instructions = trim($_POST['instructions']);
    
    // Validation des données
    $erreurs = [];
    
    if (empty($logo)) {
        $erreurs[] = "Le logo est requis";
    }
    
    if (empty($description)) {
        $erreurs[] = "La description est requise";
    }
    
    if (empty($categorie)) {
        $erreurs[] = "La catégorie est requise";
    }
    
    if (empty($sous_categorie)) {
        $erreurs[] = "La sous catégorie est requise";
    }
    
    if (empty($tags)) {
        $erreurs[] = "Les tags sont requis";
    }
    
    if (empty($model)) {
        $erreurs[] = "Le model est requis";
    }
    
    if (empty($instructions)) {
        $erreurs[] = "Les instructions sont requises";
    }
    
    // Si pas d'erreurs, insertion en base
    if (empty($erreurs)) {
        try {
            $sql = "INSERT INTO personas (logo, description, categorie, sous_categorie, tags, model, instructions) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$logo, $description, $categorie, $sous_categorie, $tags, $model, $instructions]);
            
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
                <label for="logo">Logo <span class="required">*</span></label>
                <input type="text" id="logo" name="logo" value="<?php echo isset($_POST['logo']) ? htmlspecialchars($_POST['logo']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description <span class="required">*</span></label>
                <input type="text" id="description" name="description" value="<?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="categorie">Catégorie <span class="required">*</span></label>
                <input type="text" id="categorie" name="categorie" value="<?php echo isset($_POST['categorie']) ? htmlspecialchars($_POST['categorie']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="sous_categorie">Sous Catégorie <span class="required">*</span></label>
                <input type="text" id="sous_categorie" name="sous_categorie" value="<?php echo isset($_POST['sous_categorie']) ? htmlspecialchars($_POST['sous_categorie']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="tags">Tags <span class="required">*</span></label>
                <input type="text" id="tags" name="tags" value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="model">Model <span class="required">*</span></label>
                <input type="text" id="model" name="model" value="<?php echo isset($_POST['model']) ? htmlspecialchars($_POST['model']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="instructions">Instructions</label>
                <textarea id="instructions" name="instructions" placeholder="Votre instructions..."><?php echo isset($_POST['instructions']) ? htmlspecialchars($_POST['instructions']) : ''; ?></textarea>
            </div>
            
            <button type="submit" class="btn">Envoyer</button>
        </form>
    </div>
</body>
</html>