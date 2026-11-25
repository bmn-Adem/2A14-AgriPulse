DROP DATABASE IF EXISTS agripulse;
CREATE DATABASE agripulse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agripulse;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    libelle VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

INSERT INTO roles (id, nom, libelle) VALUES
(1, 'admin', 'Administrateur'),
(2, 'agriculteur', 'Agriculteur / Producteur'),
(3, 'client_b2c', 'Client Particulier (B2C)'),
(4, 'client_b2b', 'Client Professionnel (B2B)');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL DEFAULT 3,
    nom VARCHAR(80) NOT NULL,
    prenom VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telephone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    gouvernorat VARCHAR(60) NOT NULL,
    delegation VARCHAR(80) NOT NULL,
    adresse_exacte VARCHAR(255) DEFAULT NULL,
    cin VARCHAR(8) DEFAULT NULL UNIQUE,
    type_activite VARCHAR(100) DEFAULT NULL,
    superficie_ha DECIMAL(8,2) DEFAULT NULL,
    doc_cin_path VARCHAR(255) DEFAULT NULL,
    raison_sociale VARCHAR(150) DEFAULT NULL,
    matricule_fiscal VARCHAR(50) DEFAULT NULL UNIQUE,
    doc_justificatif_path VARCHAR(255) DEFAULT NULL,
    statut ENUM('en_attente', 'verifie', 'suspendu') NOT NULL DEFAULT 'verifie',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Comptes de démonstration (Mot de passe : password)
INSERT INTO users (id, role_id, nom, prenom, email, telephone, password, gouvernorat, delegation, adresse_exacte, cin, type_activite, superficie_ha, raison_sociale, matricule_fiscal, statut) VALUES
(1, 1, 'Ben Mimoun', 'Adem', 'admin@agripulse.tn', '22000111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tunis', 'Ariana Ville', 'ESPRIT El Ghazala', NULL, NULL, NULL, NULL, NULL, 'verifie'),
(2, 2, 'Mansouri', 'Salah', 'agriculteur@agripulse.tn', '98111222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Médenine', 'Houmt Souk', 'Route de Midoun Km 4', '08451236', 'Arboriculture & Maraîchage', 12.50, NULL, NULL, 'verifie'),
(3, 3, 'Trabelsi', 'Sarra', 'client@agripulse.tn', '55333444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tunis', 'La Marsa', '14 Rue des Oliviers', NULL, NULL, NULL, NULL, NULL, 'verifie'),
(4, 4, 'Gharbi', 'Karim', 'pro@agripulse.tn', '71444555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sousse', 'Sousse Jawhara', 'Zone Industrielle Sidi Abdelhamid', NULL, NULL, NULL, 'BioFresh Distribution SARL', '1548920/A/M/000', 'en_attente');