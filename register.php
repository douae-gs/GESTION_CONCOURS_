<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isset($_SESSION['id'])) {
    header('Location: dashboard.php');
    exit;
}

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom']);
    $email    = trim($_POST['email']);
    $password = $_POST['password']; 
    // Validation nom
    if ($nom === '') {
        $erreur = 'Le nom complet est obligatoire.';
    }
    // Validation email : format valide + domaine obligatoire (doit avoir un . après le @)
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@.+\..+$/', $email)) {
        $erreur = 'Adresse email invalide';
    }
    
    elseif (strlen($password) < 8) {
        $erreur = 'Le mot de passe doit contenir au moins 8 caracteres.';
    }
    elseif (trim($password) === '') {
        $erreur = 'Le mot de passe ne peut pas etre compose uniquement d\'espaces.';
    }
    elseif (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $erreur = 'Le mot de passe doit contenir au moins une lettre et un chiffre.';
    }
    else {
        $resultat = register($pdo, $nom, $email, $password);
        if ($resultat === 'ok') {
            $succes = 'Compte cree avec succes. Vous pouvez vous connecter.';
        } else {
            $erreur = $resultat;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Inscription</h4>
                </div>
                <div class="card-body">
                    <?php if ($erreur): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                    <?php endif; ?>
                    <?php if ($succes): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                    <?php endif; ?>
                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="nom" class="form-control"
                                value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <small class="text-muted"></small></label>
                            <input type="email" name="email" class="form-control"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                placeholder="Entrez votre email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe <small class="text-muted">(min. 8 car., 1 lettre + 1 chiffre)</small></label>
                            <input type="password" name="password" class="form-control"
                                minlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Creer mon compte</button>
                    </form>
                    <hr>
                    <p class="text-center mb-0">Deja un compte ? <a href="login.php">Se connecter</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
