<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom'])) {
    $nouveau_nom = trim($_POST['nom']);
    if ($nouveau_nom !== '') {
        $stmt = $pdo->prepare('UPDATE users SET nom = ? WHERE id = ?');
        $stmt->execute([$nouveau_nom, $_SESSION['id']]);
        $_SESSION['nom'] = $nouveau_nom;
        $message = 'Nom mis a jour avec succes.';
    } else {
        $erreur = 'Le nom ne peut pas etre vide.';
    }
}

$stmt = $pdo->prepare('SELECT nom, email, role, created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['id']]);
$user = $stmt->fetch();

$stmt2 = $pdo->prepare('SELECT COUNT(*) FROM candidatures WHERE user_id = ?');
$stmt2->execute([$_SESSION['id']]);
$nb_candidatures = $stmt2->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Gestion des Concours</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="concours.php">Concours</a></li>
                <li class="nav-item"><a class="nav-link active" href="profil.php">Profil</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Deconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Mon Profil</h4>
                </div>
                <div class="card-body">

                    <?php if ($message): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <?php if ($erreur): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <p><strong>Role :</strong> <?= htmlspecialchars($user['role']) ?></p>
                        <p><strong>Membre depuis :</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
                        <p><strong>Candidatures soumises :</strong> <?= $nb_candidatures ?></p>
                    </div>

                    <hr>

                    <h5>Modifier mon nom</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </form>

                    <hr>

                    <a href="logout.php" class="btn btn-danger mt-2">Se deconnecter</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>