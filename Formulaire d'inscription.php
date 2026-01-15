<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Administrateur</title>
    <link rel="stylesheet" href="Inscription.css">
    <style>
        /* Styles supplémentaires pour les messages */
        .alert {
            padding: 12px 20px;
            margin: 15px 0;
            border-radius: 8px;
            font-size: 14px;
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
        
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    
    // Initialisation des variables
    $error = '';
    $success = '';
    $username = '';
    $email = '';
    $country = '';
    
    // Vérifier si l'utilisateur est déjà connecté
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        exit();
    }
    
    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Nettoyage et validation des données
        $username = htmlspecialchars(trim($_POST['username'] ?? ''));
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $country = htmlspecialchars(trim($_POST['country'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        $errors = [];
        
        // Validation du nom d'utilisateur
        if (empty($username)) {
            $errors[] = "Le nom d'utilisateur est requis.";
        } elseif (strlen($username) < 3) {
            $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = "Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores.";
        }
        
        // Validation de l'email
        if (empty($email)) {
            $errors[] = "L'adresse email est requise.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide.";
        }
        
        // Validation du pays
        if (empty($country)) {
            $errors[] = "Veuillez sélectionner un pays.";
        }
        
        // Validation du mot de passe
        if (empty($password)) {
            $errors[] = "Le mot de passe est requis.";
        } elseif (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        } elseif (!preg_match('/[A-Z]/', $password) || 
                  !preg_match('/[a-z]/', $password) || 
                  !preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.";
        }
        
        // Validation de la confirmation du mot de passe
        if ($password !== $confirm_password) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        
        // Si pas d'erreurs de validation, procéder à l'inscription
        if (empty($errors)) {
            try {
                // Connexion à la base de données
                require_once 'config/database.php';
                
                // Vérifier si l'utilisateur ou l'email existe déjà
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                $existingUser = $stmt->fetch();
                
                if ($existingUser) {
                    $error = "Ce nom d'utilisateur ou cette adresse email est déjà utilisé.";
                } else {
                    // Hasher le mot de passe
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Déterminer le rôle (premier utilisateur = admin)
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                    $userCount = $stmt->fetch()['count'];
                    $role = ($userCount == 0) ? 'admin' : 'user';
                    
                    // Insertion dans la base de données
                    $stmt = $pdo->prepare("
                        INSERT INTO users (username, email, password, country, role) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([$username, $email, $hashedPassword, $country, $role]);
                    
                    // Récupérer l'ID du nouvel utilisateur
                    $userId = $pdo->lastInsertId();
                    
                    // Connecter automatiquement l'utilisateur
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $role;
                    $_SESSION['country'] = $country;
                    
                    // Message de succès
                    $success = "Compte créé avec succès ! Redirection vers le tableau de bord...";
                    
                    // Redirection après 2 secondes
                    header("Refresh: 2; url=dashboard.php");
                }
                
            } catch (PDOException $e) {
                $error = "Erreur lors de la création du compte : " . $e->getMessage();
            }
        } else {
            $error = implode("<br>", $errors);
        }
    }
    ?>
    
    <div class="form-container">
        <h2><center>Inscription</center></h2>
        
        <!-- Messages d'alerte -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form id="adminForm" method="POST" action="">
            <div class="input-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required 
                       placeholder="ex: admin_2026" 
                       value="<?php echo htmlspecialchars($username ?? ''); ?>"
                       minlength="3" pattern="[a-zA-Z0-9_]+">
                <small>3 caractères minimum, lettres, chiffres et underscores seulement</small>
            </div>

            <div class="input-group">
                <label for="email">Adresse Email</label>
                <input type="email" id="email" name="email" required 
                       placeholder="admin@entreprise.com"
                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>

            <div class="input-group">
                <label for="country">Pays</label>
                <select id="country" name="country" required>
                    <option value="" disabled <?php echo empty($country) ? 'selected' : ''; ?>>Choisir un pays...</option>
                    <option value="Afrique du Sud" <?php echo ($country == 'Afrique du Sud') ? 'selected' : ''; ?>>Afrique du Sud</option>
                    <option value="Belgique" <?php echo ($country == 'Belgique') ? 'selected' : ''; ?>>Belgique</option>
                    <option value="Benin" <?php echo ($country == 'Benin') ? 'selected' : ''; ?>>Benin</option>
                    <option value="Cameroun" <?php echo ($country == 'Cameroun') ? 'selected' : ''; ?>>Cameroun</option>
                    <option value="Canada" <?php echo ($country == 'Canada') ? 'selected' : ''; ?>>Canada</option>
                    <option value="Chine" <?php echo ($country == 'Chine') ? 'selected' : ''; ?>>Chine</option>
                    <option value="Congo" <?php echo ($country == 'Congo') ? 'selected' : ''; ?>>Congo</option>
                    <option value="Espagne" <?php echo ($country == 'Espagne') ? 'selected' : ''; ?>>Espagne</option>
                    <option value="Etats-Unis" <?php echo ($country == 'Etats-Unis') ? 'selected' : ''; ?>>Etats-Unis</option>
                    <option value="France" <?php echo ($country == 'France') ? 'selected' : ''; ?>>France</option>
                    <option value="Gabon" <?php echo ($country == 'Gabon') ? 'selected' : ''; ?>>Gabon</option>
                    <option value="Italie" <?php echo ($country == 'Italie') ? 'selected' : ''; ?>>Italie</option>
                    <option value="Mali" <?php echo ($country == 'Mali') ? 'selected' : ''; ?>>Mali</option>
                    <option value="Maroc" <?php echo ($country == 'Maroc') ? 'selected' : ''; ?>>Maroc</option>
                    <option value="Nigeria" <?php echo ($country == 'Nigeria') ? 'selected' : ''; ?>>Nigeria</option>
                    <option value="Russie" <?php echo ($country == 'Russie') ? 'selected' : ''; ?>>Russie</option>
                </select>
            </div>

            <div class="input-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required 
                       minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                       title="Doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre">
                <small>8 caractères minimum, avec majuscule, minuscule et chiffre</small>
            </div>

            <div class="input-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn-submit">Créer le compte</button>
            
            <p style="text-align: center; margin-top: 20px;">
                Déjà inscrit ? <a href="Formulaire.php">Se connecter</a>
            </p>
        </form>
    </div>

    <script>
    // Validation côté client
    document.getElementById('adminForm').addEventListener('submit', function(event) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const username = document.getElementById('username').value;
        
        // Validation du nom d'utilisateur
        const usernameRegex = /^[a-zA-Z0-9_]+$/;
        if (!usernameRegex.test(username)) {
            alert("Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores.");
            event.preventDefault();
            return;
        }
        
        // Validation de la force du mot de passe
        const passwordRegex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
        if (!passwordRegex.test(password)) {
            alert("Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.");
            event.preventDefault();
            return;
        }
        
        // Validation de la correspondance des mots de passe
        if (password !== confirmPassword) {
            alert("Les mots de passe ne correspondent pas.");
            event.preventDefault();
            return;
        }
        
        // Validation du pays
        const country = document.getElementById('country').value;
        if (!country) {
            alert("Veuillez sélectionner un pays.");
            event.preventDefault();
            return;
        }
    });
    
    // Afficher/masquer le mot de passe (optionnel)
    function togglePasswordVisibility(id) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }
    </script>
</body>
</html>