<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'] ?? 'candidat';
$id   = $_SESSION['id'];
$nom  = htmlspecialchars($_SESSION['nom']);

$stmt = $pdo->query('SELECT COUNT(*) FROM concours WHERE statut = "ouvert"');
$ouverts = $stmt->fetchColumn();

if ($role === 'admin') {
    $stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "candidat"');
    $candidats = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(*) FROM concours WHERE statut = "ferme"');
    $concours_fermes = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT c.titre, e.nom AS ecole, c.date_concours, c.date_limite_inscription, c.places_disponibles,
        (SELECT COUNT(*) FROM candidatures ca WHERE ca.concours_id = c.id) AS nb
        FROM concours c JOIN ecoles e ON c.ecole_id = e.id
        ORDER BY c.date_concours ASC LIMIT 5');
    $liste = $stmt->fetchAll();

} else {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM candidatures WHERE user_id = ?');
    $stmt->execute([$id]);
    $mes_dossiers = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM candidatures ca JOIN concours c ON ca.concours_id = c.id 
        WHERE ca.user_id = ? AND c.statut = "ferme"');
    $stmt->execute([$id]);
    $concours_fermes = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query('SELECT c.titre, e.nom AS ecole, c.date_concours, c.date_limite_inscription, c.places_disponibles
        FROM concours c JOIN ecoles e ON c.ecole_id = e.id
        WHERE c.statut = "ouvert" ORDER BY c.date_concours ASC LIMIT 5');
    $liste = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php"><b>Gestion des Concours</b></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="concours.php">Concours</a></li>
                <li class="nav-item"><a class="nav-link" href="profil.php">Profil</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Deconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">

    <?php if ($role === 'admin'): ?>
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card bleu">
                <h2><?= $ouverts ?></h2>
                <p>Concours ouverts</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card gris">
                <h2><?= $candidats ?></h2>
                <p>Candidats inscrits</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card vert">
                <h2><?= $concours_fermes ?></h2>
                <p>Concours fermés</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Apercu des concours</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Ecole</th>
                        <th>Date concours</th>
                        <th>Date limite</th>
                        <th>Places</th>
                        <th>Postulants</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($liste)): ?>
                        <tr><td colspan="6" class="text-center py-3">Aucun concours.</td></tr>
                    <?php else: ?>
                        <?php foreach ($liste as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['titre']) ?></td>
                            <td><?= htmlspecialchars($c['ecole']) ?></td>
                            <td><?= date('d/m/Y', strtotime($c['date_concours'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($c['date_limite_inscription'])) ?></td>
                            <td><?= $c['places_disponibles'] ?></td>
                            <td><span class="badge bg-secondary"><?= $c['nb'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <?php else: ?>
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card bleu">
                <h2><?= $ouverts ?></h2>
                <p>Concours ouverts</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card gris">
                <h2><?= $mes_dossiers ?></h2>
                <p>Mes candidatures</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card vert">
                <h2><?= $concours_fermes ?></h2>
                <p>Concours fermés</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Concours ouverts</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Ecole</th>
                        <th>Date concours</th>
                        <th>Date limite</th>
                        <th>Places</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($liste)): ?>
                        <tr><td colspan="5" class="text-center py-3">Aucun concours disponible.</td></tr>
                    <?php else: ?>
                        <?php foreach ($liste as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['titre']) ?></td>
                            <td><?= htmlspecialchars($c['ecole']) ?></td>
                            <td><?= date('d/m/Y', strtotime($c['date_concours'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($c['date_limite_inscription'])) ?></td>
                            <td><?= $c['places_disponibles'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>