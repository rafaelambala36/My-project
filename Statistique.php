<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Statistiques du Site</title>

<style>
body {
    font-family: "Segoe UI", Arial;
    background: #eef2f7;
    padding: 30px;
}

h1 {
    text-align: center;
    color: #0a3d62;
}

.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px,1fr));
    gap: 20px;
    margin-top: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.stat-card h2 {
    margin: 0;
    font-size: 32px;
    color: #1e90ff;
}

.stat-card p {
    margin-top: 10px;
    color: #555;
}

.button {
    display: block;
    margin: 40px auto 0;
    width: fit-content;
    padding: 14px 22px;
    background: linear-gradient(90deg, #1e90ff, #0a3d62);
    color: white;
    border-radius: 10px;
    text-decoration: none;
}
.button:hover {
    opacity: 0.9;
}
</style>

<?php
session_start();

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header("Location: Formulaire.php");
    exit();
}

// Connexion à la base de données
require_once 'config/database.php';

// Récupérer les statistiques depuis la base
try {
    // Visiteurs uniques (exemple)
    $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) as visitors FROM site_visits WHERE DATE(visit_date) = CURDATE()");
    $visitors = $stmt->fetch()['visitors'];
    
    // Pages vues
    $stmt = $pdo->query("SELECT COUNT(*) as pages FROM page_views WHERE DATE(view_date) = CURDATE()");
    $pages = $stmt->fetch()['pages'];
    
    // Sessions
    $stmt = $pdo->query("SELECT COUNT(DISTINCT session_id) as sessions FROM user_sessions WHERE DATE(start_time) = CURDATE()");
    $sessions = $stmt->fetch()['sessions'];
    
    // Taux d'engagement (exemple calculé)
    $engagement = ($sessions > 0) ? round(($pages / $sessions) * 10, 1) : 0;
    
} catch(PDOException $e) {
    // Valeurs par défaut en cas d'erreur
    $visitors = 0;
    $pages = 0;
    $sessions = 0;
    $engagement = 0;
}
?>
</head>

<body>

<h1>📊 Statistiques du site</h1>

<div class="stats">
    <div class="stat-card">
        <h2 id="visitors"><?php echo $visitors; ?></h2>
        <p>Visiteurs (aujourd'hui)</p>
    </div>
    
    <div class="stat-card">
        <h2 id="pages"><?php echo $pages; ?></h2>
        <p>Pages vues (aujourd'hui)</p>
    </div>

    <div class="stat-card">
        <h2 id="sessions"><?php echo $sessions; ?></h2>
        <p>Sessions (aujourd'hui)</p>
    </div>

    <div class="stat-card">
        <h2 id="engagement"><?php echo $engagement; ?>%</h2>
        <p>Taux d'engagement</p>
    </div>
</div>

<a class="button" href="https://analytics.google.com/analytics/web/" target="_blank">
    Voir les statistiques complètes (GA4)
</a>

<script>
// Animation des chiffres (optionnel)
document.addEventListener('DOMContentLoaded', function() {
    animateValue('visitors', 0, <?php echo $visitors; ?>, 1000);
    animateValue('pages', 0, <?php echo $pages; ?>, 1000);
    animateValue('sessions', 0, <?php echo $sessions; ?>, 1000);
    animateValue('engagement', 0, <?php echo $engagement; ?>, 1000);
});

function animateValue(id, start, end, duration) {
    let obj = document.getElementById(id);
    let startTimestamp = null;
    
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        obj.innerHTML = id === 'engagement' ? value + "%" : value;
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}
</script>

</body>
</html>