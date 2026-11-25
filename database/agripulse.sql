CREATE DATABASE IF NOT EXISTS agripulse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agripulse;

CREATE TABLE IF NOT EXISTS role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO role (nom) VALUES ('admin'), ('agriculteur'), ('client');

CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Compte admin par défaut (Email: admin@agripulse.tn | Mot de passe: password)
INSERT INTO user (nom, email, mot_de_passe, role_id)
VALUES ('Administrateur AgriPulse', 'admin@agripulse.tn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);