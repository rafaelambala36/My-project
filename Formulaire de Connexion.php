<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="formulaire.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <?php
    session_start();
    
    $error = '';
    $success = '';
    
    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = htmlspecialchars($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation basique
        if (empty($username) || empty($password)) {
            $error = "Veuillez remplir tous les champs.";
        } else {
            // Connexion à la base de données (exemple)
            $servername = "localhost";
            $db_username = "root";
            $db_password = "";
            $dbname = "scorpion_db";
            
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $db_username, $db_password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Recherche de l'utilisateur
                $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
                $stmt->execute(['username' => $username, 'email' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    $success = "Connexion réussie ! Redirection...";
                    header("Refresh: 2; url=dashboard.php");
                } else {
                    $error = "Identifiants incorrects.";
                }
            } catch(PDOException $e) {
                $error = "Erreur de connexion à la base de données.";
            }
        }
    }
    ?>
    
    <div class="container">
        <form method="POST" action="">
            <h3>Connexion</h3>
            
            <?php if (!empty($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <label for="username">Nom d'utilisateur</label>
            <input type="text" placeholder="Email ou Pseudo" id="username" name="username" required>
            
            <div class="input-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
                <div class="forgot-link">
                    <a href="reset-password.php">Mot de passe oublié ?</a>
                </div>
            </div>
            
            <button type="submit">Se connecter</button>
            
            <div class="social">
                <a href="google-login.php" class="go">Google</a>
                <a href="facebook-login.php" class="fb">Facebook</a>
            </div>
            
            <p class="message">Pas encore inscrit ?<a href="Inscription.php">Créer un compte</a></p>
        </form>
    </div>
    
    <script src="Formulaire.js"></script>
</body>
</html>