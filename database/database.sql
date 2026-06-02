CREATE DATABASE gestion_concours_db CHARACTER SET utf8 COLLATE utf8_general_ci;
USE gestion_concours_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'candidat') DEFAULT 'candidat',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ecoles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    type VARCHAR(100),
    ville VARCHAR(100)
);

CREATE TABLE concours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    ecole_id INT NOT NULL,
    date_concours DATE,
    date_limite_inscription DATE,
    places_disponibles INT DEFAULT 0,
    description TEXT,
    statut ENUM('ouvert', 'ferme', 'termine') DEFAULT 'ouvert',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ecole_id) REFERENCES ecoles(id) ON DELETE CASCADE
);

CREATE TABLE candidatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    concours_id INT NOT NULL,
    statut ENUM('en attente', 'accepte', 'rejete') DEFAULT 'en attente',
    date_candidature TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (concours_id) REFERENCES concours(id) ON DELETE CASCADE
);

INSERT INTO ecoles (nom, type, ville) VALUES
('ENSA Oujda', 'Ecole Nationale des Sciences Appliquees', 'Oujda'),
('ENCG Oujda', 'Ecole Nationale de Commerce et de Gestion', 'Oujda'),
('Faculte de Medecine Oujda', 'Faculte', 'Oujda'),
('FST Oujda', 'Faculte des Sciences et Techniques', 'Oujda'),
('ENSAM Meknes', 'Ecole Nationale Superieure des Arts et Metiers', 'Meknes'),
('UM6P Benguerir', 'Universite Mohammed VI Polytechnique', 'Benguerir');

INSERT INTO users (nom, email, password, role) VALUES
('Administrateur', 'admin@concours.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Candidat Test', 'candidat@concours.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'candidat');

INSERT INTO concours (titre, ecole_id, date_concours, date_limite_inscription, places_disponibles, description, statut) VALUES
('Concours ENSA 2025', 1, '2025-07-15', '2025-06-30', 120, 'Concours d entree a l ENSA Oujda filiere ingenierie', 'ouvert'),
('Concours ENCG 2025', 2, '2025-08-01', '2025-07-15', 80, 'Concours d entree a l ENCG Oujda', 'ouvert'),
('Concours Medecine 2025', 3, '2025-09-10', '2025-08-20', 200, 'Concours d entree a la Faculte de Medecine Oujda', 'ouvert');