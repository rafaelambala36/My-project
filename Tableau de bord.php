<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard Scorpion</title>

<?php
session_start();

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header("Location: Formulaire.php");
    exit();
}

// Connexion à la base de données
require_once 'config/database.php';

// Récupérer les données pour chaque section
$users = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll();
$requests = $pdo->query("SELECT id, title, status, created_at FROM requests ORDER BY created_at DESC LIMIT 10")->fetchAll();
$services = $pdo->query("SELECT id, name, description, price FROM services WHERE active = 1")->fetchAll();
$alerts = $pdo->query("SELECT id, message, level, created_at FROM alerts WHERE resolved = 0 ORDER BY created_at DESC")->fetchAll();
?>

<!-- GA4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-7QPST09RCL"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-7QPST09RCL');
</script>

<style>
:root {
    --blue: #1e90ff;
    --violet: #6c5ce7;
    --green: #00b894;
    --red: #d63031;
    --bg: #f5f7fb;
    --dark: #2d3436;
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--dark);
}

/* HEADER */
header {
    background: linear-gradient(90deg, var(--violet), var(--blue));
    color: white;
    padding: 30px;
    text-align: center;
}

header h1 {
    margin: 0;
    font-size: 32px;
}

.user-info {
    margin-top: 10px;
    font-size: 14px;
    opacity: 0.9;
}

/* DASHBOARD */
.dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
    padding: 35px;
}

.card {
    background: white;
    border-radius: 18px;
    padding: 25px;
    text-align: center;
    cursor: pointer;
    box-shadow: 0 15px 30px rgba(0,0,0,0.08);
    transition: all 0.25s ease;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 25px 45px rgba(0,0,0,0.12);
}

.card h3 {
    margin: 0;
    font-size: 18px;
}

/* COULEURS */
.blue { border-top: 6px solid var(--blue); }
.green { border-top: 6px solid var(--green); }
.orange { border-top: 6px solid #fdcb6e; }
.red { border-top: 6px solid var(--red); }
.purple { border-top: 6px solid var(--violet); }

/* SECTIONS */
.section {
    display: none;
    background: white;
    margin: 0 35px 35px;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.08);
}

.section.active {
    display: block;
}

.section h2 {
    margin-top: 0;
    color: var(--violet);
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f1f3ff;
    padding: 12px;
    text-align: left;
}

td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

tr:hover {
    background-color: #f9f9f9;
}

/* BUTTONS */
button {
    padding: 7px 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    color: white;
    font-size: 13px;
    margin-right: 5px;
}

.btn-edit {
    background: var(--blue);
}

.btn-delete {
    background: var(--red);
}

.btn-view {
    background: var(--green);
}

button:hover {
    opacity: 0.85;
}

.logout-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255,255,255,0.2);
    padding: 8px 16px;
    border-radius: 20px;
    text-decoration: none;
    color: white;
}

.logout-btn:hover {
    background: rgba(255,255,255,0.3);
}
</style>
</head>

<body>

<header>
    <h1>TABLEAU DE BORD SCORPION</h1>
    <p>Administration • Gestion • Statistiques</p>
    <div class="user-info">
        Connecté en tant que : <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
    </div>
    <a href="logout.php" class="logout-btn">Déconnexion</a>
</header>

<!-- CARTES -->
<div class="dashboard">
    <div class="card blue" onclick="showSection('users')">
        <h3>Utilisateurs</h3>
        <p><?php echo count($users); ?> utilisateurs</p>
    </div>

    <div class="card green" onclick="showSection('requests')">
        <h3>Demandes</h3>
        <p><?php echo count($requests); ?> demandes</p>
    </div>

    <div class="card orange" onclick="showSection('services')">
        <h3>Services</h3>
        <p><?php echo count($services); ?> services</p>
    </div>

    <div class="card red" onclick="showSection('alerts')">
        <h3>Alertes</h3>
        <p><?php echo count($alerts); ?> alertes</p>
    </div>

    <div class="card purple" onclick="openStats()">
        <h3>Statistiques</h3>
        <p>Voir les stats</p>
    </div>
</div>

<!-- UTILISATEURS -->
<div id="users" class="section">
    <h2>👥 Utilisateurs (<?php echo count($users); ?>)</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Inscription</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
            <td>
                <button class="btn-view" onclick="viewUser(<?php echo $user['id']; ?>)">Voir</button>
                <button class="btn-edit" onclick="editUser(<?php echo $user['id']; ?>)">Modifier</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- DEMANDES -->
<div id="requests" class="section">
    <h2>📄 Demandes (<?php echo count($requests); ?>)</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Statut</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($requests as $request): ?>
        <tr>
            <td><?php echo $request['id']; ?></td>
            <td><?php echo htmlspecialchars($request['title']); ?></td>
            <td>
                <span class="status-badge status-<?php echo $request['status']; ?>">
                    <?php echo $request['status']; ?>
                </span>
            </td>
            <td><?php echo date('d/m/Y', strtotime($request['created_at'])); ?></td>
            <td>
                <button class="btn-view" onclick="viewRequest(<?php echo $request['id']; ?>)">Voir</button>
                <button class="btn-edit" onclick="editRequest(<?php echo $request['id']; ?>)">Modifier</button>
                <button class="btn-delete" onclick="deleteRequest(<?php echo $request['id']; ?>)">Supprimer</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- SERVICES -->
<div id="services" class="section">
    <h2>🛠️ Services (<?php echo count($services); ?>)</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Prix</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($services as $service): ?>
        <tr>
            <td><?php echo $service['id']; ?></td>
            <td><?php echo htmlspecialchars($service['name']); ?></td>
            <td><?php echo htmlspecialchars(substr($service['description'], 0, 50)) . '...'; ?></td>
            <td><?php echo $service['price']; ?>€</td>
            <td>
                <button class="btn-view" onclick="viewService(<?php echo $service['id']; ?>)">Voir</button>
                <button class="btn-edit" onclick="editService(<?php echo $service['id']; ?>)">Modifier</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- ALERTES -->
<div id="alerts" class="section">
    <h2>🔔 Alertes (<?php echo count($alerts); ?>)</h2>
    <?php if (count($alerts) > 0): ?>
        <div class="alerts-list">
            <?php foreach ($alerts as $alert): ?>
            <div class="alert-item alert-<?php echo $alert['level']; ?>">
                <p><?php echo htmlspecialchars($alert['message']); ?></p>
                <small><?php echo date('d/m/Y H:i', strtotime($alert['created_at'])); ?></small>
                <button onclick="resolveAlert(<?php echo $alert['id']; ?>)" class="btn-edit">Marquer comme résolu</button>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Aucune alerte pour le moment.</p>
    <?php endif; ?>
</div>

<script>
// Afficher une section et masquer les autres
function showSection(id) {
    gtag('event', 'open_section', { section: id });
    
    // Masquer toutes les sections
    document.querySelectorAll('.section').forEach(s => {
        s.classList.remove('active');
    });
    
    // Afficher la section sélectionnée
    document.getElementById(id).classList.add('active');
}

// Fonctions pour les actions
function viewUser(id) {
    window.location.href = 'user.php?id=' + id;
}

function editUser(id) {
    window.location.href = 'edit_user.php?id=' + id;
}

function deleteRequest(id) {
    if(confirm("Confirmer la suppression de cette demande ?")) {
        fetch('delete_request.php?id=' + id, {
            method: 'DELETE',
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la suppression');
            }
        });
    }
}

function resolveAlert(id) {
    fetch('resolve_alert.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload();
        }
    });
}

function openStats() {
    gtag('event', 'open_analytics');
    window.location.href = "stat.php";
}

// Afficher la première section par défaut
document.addEventListener('DOMContentLoaded', function() {
    showSection('users');
});

// Style pour les badges de statut
document.write(`
<style>
.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}
.status-pending { background: #fdcb6e; color: #333; }
.status-in-progress { background: #74b9ff; color: white; }
.status-completed { background: #00b894; color: white; }
.status-cancelled { background: #dfe6e9; color: #333; }

.alert-item {
    padding: 15px;
    margin: 10px 0;
    border-radius: 8px;
    border-left: 4px solid;
}
.alert-info { background: #e3f2fd; border-left-color: #2196f3; }
.alert-warning { background: #fff3e0; border-left-color: #ff9800; }
.alert-error { background: #ffebee; border-left-color: #f44336; }
</style>
`);
</script>

</body>
</html>