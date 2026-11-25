# AgriPulse — Transition Écologique & Agriculture Intelligente

Plateforme web collaborative développée en **PHP 8 (Architecture MVC)** et **MySQL** dans le cadre du module **Projet Technologies Web 2A** à **ESPRIT** (Année universitaire 2026–2027 — Classe **2A14**, Groupe **04**).

## Description

**AgriPulse** centralise le pilotage agronomique et la transition écologique autour de 5 modules complémentaires :
1. **Gestion des Récoltes & Rendements** (`Recolte` / `SaisonAgricole`) — Suivi des quantités récoltées, qualité et rendements saisonniers.
2. **Gestion des Cultures & Plantations** (`Plantation` / `VarieteCulture`) — Suivi du cycle de vie depuis le semis jusqu'à la maturation.
3. **Partage d'Équipements Agricoles** (`Reservation` / `Materiel`) — Économie circulaire et location d'outils agricoles.
4. **Sensibilisation & Ateliers Écologiques** (`SessionAtelier` / `AtelierEco`) — Organisation de formations et ateliers pratiques.
5. **Conseil & Diagnostic des Sols** (`Diagnostic` / `Recommandation`) — Analyses chimiques (pH, humidité, azote NPK) et préconisations agronomiques.

### Fonctionnalités du Socle Utilisateurs & Sécurité
* **Inscription multi-profils avec règles métiers tunisiennes** :
  * **Agriculteur / Producteur** : CIN (8 chiffres), localisation de l'exploitation (Gouvernorat/Délégation), type d'activité, superficie, et upload sécurisé de justificatifs (CIN Recto/Verso + Attestation UTAP/CRDA ou Patente agricole) — compte créé en statut *En attente de vérification* jusqu'à validation par l'administrateur.
  * **Client Particulier (B2C)** : Activation immédiate après inscription et validation des coordonnées de livraison.
  * **Client Professionnel (B2B)** : Raison sociale, Matricule Fiscal tunisien, siège social, et upload de Patente / extrait RNE — validation administrative requise.
* **CRUD complet des utilisateurs & workflow d'approbation** (Ajout, Consultation, Modification, Suppression, Vérification des dossiers, Suspension) dans le Back-Office.
* **Sécurité avancée** : hachage `password_hash()` (BCRYPT), requêtes préparées PDO (`ATTR_EMULATE_PREPARES => false`), protection XSS (`htmlspecialchars()`), protection CSRF (`csrf_token` + `hash_equals()`), upload sécurisé (vérification MIME + taille max), sessions durcies (`HttpOnly`, `SameSite=Lax`, `use_strict_mode`, expiration 30 min, `session_regenerate_id()`), et en-têtes HTTP de sécurité.
* **Contrôle de saisie double** : validation JavaScript temps réel sous chaque champ + validation PHP stricte côté serveur.

## Table des Matières
* [Installation](#installation)
* [Configuration](#configuration)
* [Utilisation](#utilisation)
* [Architecture MVC](#architecture-mvc)
* [Contributions](#contributions)
* [Licence](#licence)

## Installation

1. **Prérequis** : Installer **XAMPP** (PHP >= 8.0, Apache, MySQL / phpMyAdmin).
2. **Cloner le dépôt** dans le répertoire `htdocs` de XAMPP :
   ```bash
   cd C:\xampp\htdocs
   git clone [https://github.com/bmn-Adem/2A14-AgriPulse.git](https://github.com/bmn-Adem/2A14-AgriPulse.git)
   cd 2A14-AgriPulse