<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {

        if ($_POST['action'] === 'postuler') {
            $concours_id = (int)$_POST['concours_id'];
            $stmt = $pdo->prepare('SELECT id FROM candidatures WHERE user_id = ? AND concours_id = ?');
            $stmt->execute([$_SESSION['id'], $concours_id]);
            if ($stmt->fetch()) {
                $erreur = 'Vous avez deja postule a ce concours.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO candidatures (user_id, concours_id, statut) VALUES (?, ?, "en_attente")');
                $stmt->execute([$_SESSION['id'], $concours_id]);
                $message = 'Candidature soumise avec succes.';
            }
        }

        if ($_POST['action'] === 'ajouter' && $_SESSION['role'] === 'admin') {
            $titre         = trim($_POST['titre']);
            $ecole_id      = (int)$_POST['ecole_id'];
            $date_concours = trim($_POST['date_concours']);
            $date_limite   = trim($_POST['date_limite']);
            $places        = (int)$_POST['places'];
            $description   = trim($_POST['description']);

            if ($titre === '') {
                $erreur = 'Le titre est obligatoire.';
            } elseif ($ecole_id <= 0) {
                $erreur = 'Veuillez selectionner une ecole.';
            } elseif ($date_concours === '') {
                $erreur = 'La date du concours est obligatoire.';
            } elseif ($date_limite === '') {
                $erreur = 'La date limite inscription est obligatoire.';
            } elseif ($date_limite >= $date_concours) {
                $erreur = 'La date limite doit etre anterieure a la date du concours.';
            } elseif ($places <= 0) {
                $erreur = 'Le nombre de places doit etre superieur a 0.';
            } elseif ($description === '') {
                $erreur = 'La description est obligatoire.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO concours (titre, ecole_id, date_concours, date_limite_inscription, places_disponibles, description, statut) VALUES (?, ?, ?, ?, ?, ?, "ouvert")');
                $stmt->execute([$titre, $ecole_id, $date_concours, $date_limite, $places, $description]);
                $message = 'Concours ajoute avec succes.';
            }
        }

        if ($_POST['action'] === 'changer_statut' && $_SESSION['role'] === 'admin') {
            $cid    = (int)$_POST['concours_id'];
            $statut = $_POST['statut'];
            $statuts_valides = ['ouvert', 'ferme', 'termine'];
            if (in_array($statut, $statuts_valides)) {
                $stmt = $pdo->prepare('UPDATE concours SET statut = ? WHERE id = ?');
                $stmt->execute([$statut, $cid]);
                $message = 'Statut mis a jour.';
            }
        }

        if ($_POST['action'] === 'supprimer' && $_SESSION['role'] === 'admin') {
            $id = (int)$_POST['concours_id'];
            $stmt = $pdo->prepare('DELETE FROM candidatures WHERE concours_id = ?');
            $stmt->execute([$id]);
            $stmt = $pdo->prepare('DELETE FROM concours WHERE id = ?');
            $stmt->execute([$id]);
            $message = 'Concours supprime.';
        }
    }
}

$filtre = isset($_GET['statut']) ? $_GET['statut'] : 'tous';
if ($filtre === 'tous') {
    $stmt = $pdo->query('SELECT c.*, e.nom AS ecole FROM concours c JOIN ecoles e ON c.ecole_id = e.id ORDER BY c.date_concours ASC');
} else {
    $stmt = $pdo->prepare('SELECT c.*, e.nom AS ecole FROM concours c JOIN ecoles e ON c.ecole_id = e.id WHERE c.statut = ? ORDER BY c.date_concours ASC');
    $stmt->execute([$filtre]);
}
$liste_concours = $stmt->fetchAll();

$ecoles = $pdo->query('SELECT * FROM ecoles')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concours</title>
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
                <li class="nav-item"><a class="nav-link active" href="concours.php">Concours</a></li>
                <li class="nav-item"><a class="nav-link" href="profil.php">Profil</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Deconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'admin'): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Ajouter un concours</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="ajouter">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Titre</label>
                        <input type="text" name="titre" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ecole</label>
                        <select name="ecole_id" class="form-select" required>
                            <?php foreach ($ecoles as $e): ?>
                                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date du concours</label>
                        <input type="date" name="date_concours" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date limite inscription</label>
                        <input type="date" name="date_limite" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre de places</label>
                        <input type="number" name="places" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" required></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des concours</h5>
            <div>
                <a href="?statut=tous" class="btn btn-sm btn-light me-1">Tous</a>
                <a href="?statut=ouvert" class="btn btn-sm btn-light me-1">Ouverts</a>
                <a href="?statut=ferme" class="btn btn-sm btn-light me-1">Fermes</a>
                <a href="?statut=termine" class="btn btn-sm btn-light">Termines</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Ecole</th>
                        <th>Date concours</th>
                        <th>Date limite</th>
                        <th>Places</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($liste_concours) === 0): ?>
                        <tr><td colspan="7" class="text-center py-3">Aucun concours trouve.</td></tr>
                    <?php else: ?>
                        <?php foreach ($liste_concours as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['titre']) ?></td>
                            <td><?= htmlspecialchars($c['ecole']) ?></td>
                            <td><?= $c['date_concours'] ? date('d/m/Y', strtotime($c['date_concours'])) : '-' ?></td>
                            <td><?= $c['date_limite_inscription'] ? date('d/m/Y', strtotime($c['date_limite_inscription'])) : '-' ?></td>
                            <td><?= $c['places_disponibles'] ?></td>
                            <td><span class="badge-<?= $c['statut'] ?>"><?= $c['statut'] ?></span></td>
                            <td>
                                <?php if ($c['statut'] === 'ouvert' && $_SESSION['role'] === 'candidat'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="postuler">
                                        <input type="hidden" name="concours_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-primary">Postuler</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <form method="POST" style="display:inline;" class="me-1">
                                        <input type="hidden" name="action" value="changer_statut">
                                        <input type="hidden" name="concours_id" value="<?= $c['id'] ?>">
                                        <select name="statut" class="form-select form-select-sm d-inline-block"
                                            style="width:auto;" onchange="this.form.submit()">
                                            <option value="ouvert"  <?= $c['statut']==='ouvert'  ? 'selected':'' ?>>Ouvert</option>
                                            <option value="ferme"   <?= $c['statut']==='ferme'   ? 'selected':'' ?>>Ferme</option>
                                            <option value="termine" <?= $c['statut']==='termine' ? 'selected':'' ?>>Termine</option>
                                        </select>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="concours_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger btn-supprimer">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>