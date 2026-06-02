<?php
function register($pdo, $nom, $email, $password) {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return 'Email deja utilise.';
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (nom, email, password, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nom, $email, $hash, 'candidat']);
    return 'ok';
}

function login($pdo, $email, $password) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user) {
        return 'Email introuvable.';
    }
    if (!password_verify($password, $user['password'])) {
        return 'Mot de passe incorrect.';
    }
    $_SESSION['id'] = $user['id'];
    $_SESSION['nom'] = $user['nom'];
    $_SESSION['role'] = $user['role'];
    return 'ok';
}
?>
