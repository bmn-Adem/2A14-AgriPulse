<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * controllers/AuthController.php
 * Gestion de l'authentification, des sessions, du CSRF, des règles métiers
 * multi-profils (Agriculteur, Client B2C, Client B2B) et de l'upload sécurisé.
 */
class AuthController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnexion();
    }

    public function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function verifyCsrf(?string $token): bool
    {
        return !empty($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id'], $_SESSION['role']);
    }

    public static function requireRole(string $requiredRole): void
    {
        if (!self::isAuthenticated()) {
            header('Location: index.php?action=login');
            exit;
        }
        if (($_SESSION['role'] ?? '') !== $requiredRole) {
            http_response_code(403);
            die('<h1>403 — Accès refusé</h1><p>Vous n\'avez pas les droits requis pour accéder à cet espace.</p><a href="index.php?action=home">Retour à l\'accueil</a>');
        }
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, r.nom AS role_nom, r.libelle AS role_libelle
             FROM users u
             INNER JOIN roles r ON u.role_id = r.id
             WHERE u.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function register(array $post, array $files = []): array
    {
        $errors = [];

        if (!$this->verifyCsrf($post['csrf_token'] ?? null)) {
            $errors[] = 'Jeton de sécurité CSRF invalide. Veuillez recharger la page.';
            return $errors;
        }

        $profileType   = trim($post['profile_type'] ?? 'client_b2c');
        $nom           = trim($post['nom'] ?? '');
        $prenom        = trim($post['prenom'] ?? '');
        $email         = trim($post['email'] ?? '');
        $telephone     = trim($post['telephone'] ?? '');
        $password      = $post['password'] ?? '';
        $confirmPwd    = $post['confirm_password'] ?? '';
        $gouvernorat   = trim($post['gouvernorat'] ?? '');
        $delegation    = trim($post['delegation'] ?? '');
        $adresseExacte = trim($post['adresse_exacte'] ?? '');

        $cin           = trim($post['cin'] ?? '');
        $typeActivite  = trim($post['type_activite'] ?? '');
        $superficieRaw = trim($post['superficie_ha'] ?? '');

        $raisonSociale   = trim($post['raison_sociale'] ?? '');
        $matriculeFiscal = strtoupper(trim($post['matricule_fiscal'] ?? ''));

        $allowedProfiles = ['agriculteur' => 2, 'client_b2c' => 3, 'client_b2b' => 4];
        if (!array_key_exists($profileType, $allowedProfiles)) {
            $errors[] = 'Type de profil sélectionné invalide.';
            return $errors;
        }
        $roleId = $allowedProfiles[$profileType];

        if ($nom === '' || !preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,80}$/u', $nom)) {
            $errors[] = 'Le nom est obligatoire (2 à 80 lettres uniquement, sans chiffres).';
        }
        if ($prenom === '' || !preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,80}$/u', $prenom)) {
            $errors[] = 'Le prénom est obligatoire (2 à 80 lettres uniquement, sans chiffres).';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Veuillez saisir une adresse email valide.';
        } else {
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Cette adresse email est déjà utilisée par un autre compte.';
            }
        }

        if (!preg_match('/^[24579]\d{7}$/', $telephone)) {
            $errors[] = 'Le numéro de téléphone tunisien doit contenir exactement 8 chiffres (commençant par 2, 4, 5, 7 ou 9).';
        }

        if ($gouvernorat === '' || mb_strlen($gouvernorat) < 2) {
            $errors[] = 'Veuillez sélectionner un gouvernorat.';
        }
        if ($delegation === '' || mb_strlen($delegation) < 2) {
            $errors[] = 'Veuillez renseigner la délégation.';
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#_\-]).{8,}$/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères, dont 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial (@$!%*?&#_-).';
        } elseif ($password !== $confirmPwd) {
            $errors[] = 'La confirmation du mot de passe ne correspond pas.';
        }

        $docCinPath = null;
        $docJustificatifPath = null;
        $superficieHa = null;
        $statut = 'verifie';

        if ($profileType === 'agriculteur') {
            $statut = 'en_attente';

            if (!preg_match('/^[01]\d{7}$/', $cin)) {
                $errors[] = 'Le numéro de CIN tunisienne doit comporter exactement 8 chiffres (commençant par 0 ou 1).';
            } else {
                $stmt = $this->pdo->prepare('SELECT id FROM users WHERE cin = :cin');
                $stmt->execute([':cin' => $cin]);
                if ($stmt->fetch()) {
                    $errors[] = 'Ce numéro de CIN est déjà enregistré dans le système.';
                }
            }

            if ($superficieRaw !== '') {
                if (!is_numeric($superficieRaw) || (float) $superficieRaw < 0 || (float) $superficieRaw > 100000) {
                    $errors[] = 'La superficie exploitée doit être un nombre positif valide (en hectares).';
                } else {
                    $superficieHa = round((float) $superficieRaw, 2);
                }
            }

            if (empty($errors)) {
                $cinUpload = $this->handleSecureUpload($files['doc_cin'] ?? null, 'cin', true);
                if ($cinUpload['error'] !== null) {
                    $errors[] = 'Copie CIN : ' . $cinUpload['error'];
                } else {
                    $docCinPath = $cinUpload['path'];
                }

                $justifUpload = $this->handleSecureUpload($files['doc_justificatif_agri'] ?? null, 'utap_crda', true);
                if ($justifUpload['error'] !== null) {
                    $errors[] = 'Justificatif agricole (UTAP/CRDA/Patente) : ' . $justifUpload['error'];
                } else {
                    $docJustificatifPath = $justifUpload['path'];
                }
            }
        } elseif ($profileType === 'client_b2c') {
            $statut = 'verifie';
            if ($adresseExacte === '' || mb_strlen($adresseExacte) < 5) {
                $errors[] = 'L\'adresse exacte de livraison est obligatoire pour un client particulier (minimum 5 caractères).';
            }
        } elseif ($profileType === 'client_b2b') {
            $statut = 'en_attente';
            if ($raisonSociale === '' || mb_strlen($raisonSociale) < 2) {
                $errors[] = 'La raison sociale de l\'entreprise est obligatoire.';
            }
            if ($adresseExacte === '' || mb_strlen($adresseExacte) < 5) {
                $errors[] = 'L\'adresse du siège / local professionnel est obligatoire.';
            }
            if (!preg_match('/^\d{7}[\/\-]?[A-Z][\/\-]?[A-Z0-9]{0,4}[\/\-]?\d{0,3}$/', $matriculeFiscal)) {
                $errors[] = 'Le Matricule Fiscal tunisien est obligatoire et doit respecter le format officiel (ex: 1548920/A/M/000).';
            } else {
                $stmt = $this->pdo->prepare('SELECT id FROM users WHERE matricule_fiscal = :mf');
                $stmt->execute([':mf' => $matriculeFiscal]);
                if ($stmt->fetch()) {
                    $errors[] = 'Ce Matricule Fiscal est déjà associé à un compte professionnel.';
                }
            }

            if (empty($errors)) {
                $b2bUpload = $this->handleSecureUpload($files['doc_justificatif_b2b'] ?? null, 'rne_patente', true);
                if ($b2bUpload['error'] !== null) {
                    $errors[] = 'Justificatif entreprise (Patente / RNE) : ' . $b2bUpload['error'];
                } else {
                    $docJustificatifPath = $b2bUpload['path'];
                }
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO users (
                    role_id, nom, prenom, email, telephone, password,
                    gouvernorat, delegation, adresse_exacte,
                    cin, type_activite, superficie_ha, doc_cin_path,
                    raison_sociale, matricule_fiscal, doc_justificatif_path, statut
                ) VALUES (
                    :role_id, :nom, :prenom, :email, :telephone, :password,
                    :gouvernorat, :delegation, :adresse_exacte,
                    :cin, :type_activite, :superficie_ha, :doc_cin_path,
                    :raison_sociale, :matricule_fiscal, :doc_justificatif_path, :statut
                )';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':role_id'               => $roleId,
            ':nom'                   => $nom,
            ':prenom'                => $prenom,
            ':email'                 => $email,
            ':telephone'             => $telephone,
            ':password'              => $hashedPassword,
            ':gouvernorat'           => $gouvernorat,
            ':delegation'            => $delegation,
            ':adresse_exacte'        => $adresseExacte !== '' ? $adresseExacte : null,
            ':cin'                   => $profileType === 'agriculteur' ? $cin : null,
            ':type_activite'         => ($profileType === 'agriculteur' && $typeActivite !== '') ? $typeActivite : null,
            ':superficie_ha'         => $profileType === 'agriculteur' ? $superficieHa : null,
            ':doc_cin_path'          => $docCinPath,
            ':raison_sociale'        => $profileType === 'client_b2b' ? $raisonSociale : null,
            ':matricule_fiscal'      => $profileType === 'client_b2b' ? $matriculeFiscal : null,
            ':doc_justificatif_path' => $docJustificatifPath,
            ':statut'                => $statut,
        ]);

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        return [];
    }

    public function login(array $post): array
    {
        $errors = [];

        if (!$this->verifyCsrf($post['csrf_token'] ?? null)) {
            $errors[] = 'Jeton de sécurité CSRF invalide. Veuillez réessayer.';
            return $errors;
        }

        $email    = trim($post['email'] ?? '');
        $password = $post['password'] ?? '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Veuillez saisir une adresse email valide.';
            return $errors;
        }
        if ($password === '') {
            $errors[] = 'Veuillez saisir votre mot de passe.';
            return $errors;
        }

        $stmt = $this->pdo->prepare(
            'SELECT u.*, r.nom AS role_nom, r.libelle AS role_libelle
             FROM users u
             INNER JOIN roles r ON u.role_id = r.id
             WHERE u.email = :email'
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Identifiants incorrects (email ou mot de passe invalide).';
            return $errors;
        }

        if ($user['statut'] === 'en_attente') {
            $errors[] = 'Votre dossier professionnel est en cours de vérification par l\'administrateur AgriPulse. Vous recevrez l\'accès dès validation.';
            return $errors;
        }

        if ($user['statut'] === 'suspendu') {
            $errors[] = 'Ce compte a été suspendu par l\'administration. Veuillez contacter le support AgriPulse.';
            return $errors;
        }

        session_regenerate_id(true);

        $_SESSION['user_id']       = (int) $user['id'];
        $_SESSION['nom']           = $user['nom'];
        $_SESSION['prenom']        = $user['prenom'];
        $_SESSION['email']         = $user['email'];
        $_SESSION['role']          = $user['role_nom'];
        $_SESSION['role_libelle']  = $user['role_libelle'];
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token']    = bin2hex(random_bytes(32));

        return [];
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    private function handleSecureUpload(?array $file, string $prefix, bool $required): array
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $required
                ? ['path' => null, 'error' => 'Ce document justificatif est obligatoire.']
                : ['path' => null, 'error' => null];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['path' => null, 'error' => 'Erreur lors du transfert du fichier.'];
        }

        $maxSize = 5 * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxSize) {
            return ['path' => null, 'error' => 'Le fichier dépasse la taille maximale autorisée (5 Mo).'];
        }

        $allowedMimes = [
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'application/pdf' => 'pdf',
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($file['tmp_name']);

        if (!array_key_exists($detectedMime, $allowedMimes)) {
            return ['path' => null, 'error' => 'Format non autorisé. Seuls les fichiers JPG, PNG et PDF sont acceptés.'];
        }

        $extension = $allowedMimes[$detectedMime];
        $safeFilename = $prefix . '_' . bin2hex(random_bytes(12)) . '.' . $extension;
        $uploadDir = __DIR__ . '/../public/uploads/justificatifs';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . '/' . $safeFilename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['path' => null, 'error' => 'Impossible d\'enregistrer le fichier sur le serveur.'];
        }

        return ['path' => 'public/uploads/justificatifs/' . $safeFilename, 'error' => null];
    }
}