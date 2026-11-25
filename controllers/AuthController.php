<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

/**
 * controllers/AuthController.php
 * Gestion complète de l'authentification, inscription multi-profils (Agriculteur, B2C, B2B),
 * sécurité CSRF, hachage Argon2id/Bcrypt, contrôle d'accès RBAC et upload sécurisé.
 */
class AuthController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnexion();
    }

    /**
     * Génère et retourne un jeton CSRF unique stocké en session.
     */
    public function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie la validité du jeton CSRF soumis dans un formulaire POST.
     */
    public function verifyCsrfToken(?string $token): bool
    {
        return self::verifyCsrf($token);
    }

    /**
     * Méthode statique de vérification CSRF (utilisée notamment par UserController).
     */
    public static function verifyCsrf(?string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Vérifie si un utilisateur est actuellement connecté.
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id'], $_SESSION['role']);
    }

    /**
     * Protège une route selon les rôles autorisés (RBAC).
     */
    public static function requireRole(string ...$allowedRoles): void
    {
        if (!self::isAuthenticated()) {
            $_SESSION['flash_error'] = 'Veuillez vous connecter pour accéder à cette page.';
            header('Location: index.php?action=login');
            exit;
        }
        if (!empty($allowedRoles) && !in_array($_SESSION['role'], $allowedRoles, true)) {
            http_response_code(403);
            die('<h1>403 — Accès refusé</h1><p>Vous n\'avez pas les permissions requises.</p><a href="index.php?action=home">Retour à l\'accueil</a>');
        }
    }

    /**
     * Inscription multi-profils avec validation stricte côté serveur et upload de justificatifs.
     *
     * @return array<int, string> Liste des erreurs éventuelles (vide si succès)
     */
    public function register(array $post, array $files): array
    {
        $errors = [];

        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            $errors[] = 'Jeton de sécurité CSRF invalide. Veuillez recharger la page.';
            return $errors;
        }

        $profileType    = trim((string) ($post['profile_type'] ?? 'client_b2c'));
        $nom            = trim((string) ($post['nom'] ?? ''));
        $prenom         = trim((string) ($post['prenom'] ?? ''));
        $email          = trim((string) ($post['email'] ?? ''));
        $telephone      = trim((string) ($post['telephone'] ?? ''));
        $gouvernorat    = trim((string) ($post['gouvernorat'] ?? ''));
        $delegation     = trim((string) ($post['delegation'] ?? ''));
        $adresseExacte  = trim((string) ($post['adresse_exacte'] ?? ''));
        $password       = (string) ($post['password'] ?? '');
        $confirmPwd     = (string) ($post['confirm_password'] ?? '');

        // Champs spécifiques Agriculteur
        $cin            = trim((string) ($post['cin'] ?? ''));
        $typeActivite   = trim((string) ($post['type_activite'] ?? ''));
        $superficieRaw  = trim((string) ($post['superficie_ha'] ?? ''));
        $superficieHa   = ($superficieRaw !== '') ? (float) $superficieRaw : null;

        // Champs spécifiques Client B2B
        $raisonSociale   = trim((string) ($post['raison_sociale'] ?? ''));
        $matriculeFiscal = strtoupper(trim((string) ($post['matricule_fiscal'] ?? '')));

        // 1. Validation du profil choisi (interdiction de s'inscrire directement en admin)
        $allowedProfiles = ['agriculteur', 'client_b2c', 'client_b2b'];
        if (!in_array($profileType, $allowedProfiles, true)) {
            $errors[] = 'Type de profil sélectionné invalide.';
        }

        // 2. Validation des champs communs
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,80}$/u', $nom)) {
            $errors[] = 'Le nom doit contenir uniquement des lettres (2 caractères minimum).';
        }
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,80}$/u', $prenom)) {
            $errors[] = 'Le prénom doit contenir uniquement des lettres (2 caractères minimum).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email est invalide.';
        }
        if (!preg_match('/^[24579]\d{7}$/', $telephone)) {
            $errors[] = 'Le numéro de téléphone tunisien doit contenir 8 chiffres (commençant par 2, 4, 5, 7 ou 9).';
        }
        if ($gouvernorat === '') {
            $errors[] = 'Veuillez sélectionner un gouvernorat.';
        }
        if (mb_strlen($delegation) < 2) {
            $errors[] = 'Veuillez renseigner votre délégation.';
        }
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#_\-]).{8,}$/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
        }
        if ($password !== $confirmPwd) {
            $errors[] = 'La confirmation du mot de passe ne correspond pas.';
        }

        // Vérification unicité email
        if (empty($errors)) {
            $stmtCheck = $this->pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmtCheck->execute([':email' => $email]);
            if ($stmtCheck->fetch()) {
                $errors[] = 'Cette adresse email est déjà utilisée par un autre compte.';
            }
        }

        // 3. Validation conditionnelle selon le profil
        $statut = 'verifie';
        $docCinPath = null;
        $docJustificatifPath = null;

        if ($profileType === 'agriculteur') {
            $statut = 'en_attente';
            if (!preg_match('/^[01]\d{7}$/', $cin)) {
                $errors[] = 'La CIN tunisienne est obligatoire pour un agriculteur (8 chiffres commençant par 0 ou 1).';
            } else {
                $stmtCin = $this->pdo->prepare('SELECT id FROM users WHERE cin = :cin LIMIT 1');
                $stmtCin->execute([':cin' => $cin]);
                if ($stmtCin->fetch()) {
                    $errors[] = 'Ce numéro de CIN est déjà enregistré sur la plateforme.';
                }
            }

            if (empty($files['doc_cin']) || ($files['doc_cin']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                $errors[] = 'La copie de votre CIN est obligatoire pour le profil Agriculteur.';
            }
            if (empty($files['doc_justificatif_agri']) || ($files['doc_justificatif_agri']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                $errors[] = 'L\'attestation UTAP/CRDA ou patente agricole est obligatoire.';
            }
        } elseif ($profileType === 'client_b2c') {
            // Activation immédiate, adresse exacte optionnelle
            $statut = 'verifie';
        } elseif ($profileType === 'client_b2b') {
            $statut = 'en_attente';
            if (mb_strlen($raisonSociale) < 2) {
                $errors[] = 'La raison sociale est obligatoire pour un client professionnel B2B.';
            }
            if (!preg_match('/^\d{7}[\/\-]?[A-Z][\/\-]?[A-Z0-9]{0,4}[\/\-]?\d{0,3}$/', $matriculeFiscal)) {
                $errors[] = 'Le matricule fiscal tunisien est invalide (ex: 1548920/A/M/000).';
            }
            if (mb_strlen($adresseExacte) < 5) {
                $errors[] = 'L\'adresse exacte du siège est obligatoire pour un client B2B.';
            }
            if (empty($files['doc_justificatif_b2b']) || ($files['doc_justificatif_b2b']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                $errors[] = 'La copie de la patente ou de l\'extrait RNE est obligatoire pour un compte B2B.';
            }
        }

        // 4. Upload sécurisé des documents si aucune erreur
        if (empty($errors)) {
            if ($profileType === 'agriculteur') {
                $resCin = $this->handleFileUpload($files['doc_cin'], 'cin');
                if ($resCin['error'] !== null) {
                    $errors[] = $resCin['error'];
                } else {
                    $docCinPath = $resCin['path'];
                }

                $resJustif = $this->handleFileUpload($files['doc_justificatif_agri'], 'agri');
                if ($resJustif['error'] !== null) {
                    $errors[] = $resJustif['error'];
                } else {
                    $docJustificatifPath = $resJustif['path'];
                }
            } elseif ($profileType === 'client_b2b') {
                $resB2b = $this->handleFileUpload($files['doc_justificatif_b2b'], 'b2b');
                if ($resB2b['error'] !== null) {
                    $errors[] = $resB2b['error'];
                } else {
                    $docJustificatifPath = $resB2b['path'];
                }
            }
        }

        // 5. Insertion en base de données
        if (empty($errors)) {
            $stmtRole = $this->pdo->prepare('SELECT id FROM roles WHERE nom = :nom LIMIT 1');
            $stmtRole->execute([':nom' => $profileType]);
            $roleRow = $stmtRole->fetch();
            if (!$roleRow) {
                $errors[] = 'Rôle introuvable dans la base de données.';
                return $errors;
            }

            $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
            $hashedPassword = password_hash($password, $algo);

            $sql = 'INSERT INTO users (
                        role_id, nom, prenom, email, telephone, password,
                        gouvernorat, delegation, adresse_exacte,
                        cin, type_activite, superficie_ha, doc_cin_path,
                        raison_sociale, matricule_fiscal, doc_justificatif_path,
                        statut
                    ) VALUES (
                        :role_id, :nom, :prenom, :email, :telephone, :password,
                        :gouvernorat, :delegation, :adresse_exacte,
                        :cin, :type_activite, :superficie_ha, :doc_cin_path,
                        :raison_sociale, :matricule_fiscal, :doc_justificatif_path,
                        :statut
                    )';

            $stmtInsert = $this->pdo->prepare($sql);
            $stmtInsert->execute([
                ':role_id'               => (int) $roleRow['id'],
                ':nom'                   => $nom,
                ':prenom'                => $prenom,
                ':email'                 => $email,
                ':telephone'             => $telephone,
                ':password'              => $hashedPassword,
                ':gouvernorat'           => $gouvernorat,
                ':delegation'            => $delegation,
                ':adresse_exacte'        => ($adresseExacte !== '') ? $adresseExacte : null,
                ':cin'                   => ($profileType === 'agriculteur') ? $cin : null,
                ':type_activite'         => ($profileType === 'agriculteur' && $typeActivite !== '') ? $typeActivite : null,
                ':superficie_ha'         => ($profileType === 'agriculteur') ? $superficieHa : null,
                ':doc_cin_path'          => $docCinPath,
                ':raison_sociale'        => ($profileType === 'client_b2b') ? $raisonSociale : null,
                ':matricule_fiscal'      => ($profileType === 'client_b2b') ? $matriculeFiscal : null,
                ':doc_justificatif_path' => $docJustificatifPath,
                ':statut'                => $statut,
            ]);
        }

        return $errors;
    }

    /**
     * Connexion sécurisée avec vérification du statut (verifie / en_attente / suspendu).
     *
     * @return array<int, string>
     */
    public function login(array $post): array
    {
        $errors = [];

        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            $errors[] = 'Jeton de sécurité CSRF invalide. Veuillez réessayer.';
            return $errors;
        }

        $email    = trim((string) ($post['email'] ?? ''));
        $password = (string) ($post['password'] ?? '');

        if ($email === '' || $password === '') {
            $errors[] = 'Veuillez saisir votre adresse email et votre mot de passe.';
            return $errors;
        }

        $sql = 'SELECT u.*, r.nom AS role_nom, r.libelle AS role_libelle
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE u.email = :email
                LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Identifiants incorrects (email ou mot de passe invalide).';
            return $errors;
        }

        if ($user['statut'] === 'en_attente') {
            $errors[] = 'Votre dossier professionnel est en cours de vérification par un administrateur AgriPulse.';
            return $errors;
        }

        if ($user['statut'] === 'suspendu') {
            $errors[] = 'Votre compte a été suspendu. Veuillez contacter l\'administration AgriPulse.';
            return $errors;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']      = (int) $user['id'];
        $_SESSION['nom']          = $user['nom'];
        $_SESSION['prenom']       = $user['prenom'];
        $_SESSION['email']        = $user['email'];
        $_SESSION['role']         = $user['role_nom'];
        $_SESSION['role_libelle'] = $user['role_libelle'];
        $_SESSION['last_activity'] = time();

        return [];
    }

    /**
     * Déconnexion propre et destruction de session.
     */
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

    /**
     * Gère la validation MIME réelle et le déplacement sécurisé d'un document justificatif.
     *
     * @return array{path: ?string, error: ?string}
     */
    private function handleFileUpload(array $file, string $prefix): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['path' => null, 'error' => 'Erreur lors du téléversement du document justificatif.'];
        }

        $maxSize = 5 * 1024 * 1024; // 5 Mo
        if (($file['size'] ?? 0) > $maxSize) {
            return ['path' => null, 'error' => 'Le fichier justificatif dépasse la taille maximale autorisée (5 Mo).'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedMimes = [
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'application/pdf' => 'pdf',
        ];

        if (!isset($allowedMimes[$mimeType])) {
            return ['path' => null, 'error' => 'Format de fichier non autorisé (seuls JPG, PNG et PDF sont acceptés).'];
        }

        $uploadDir = __DIR__ . '/../public/uploads/justificatifs';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = $prefix . '_' . bin2hex(random_bytes(16)) . '.' . $allowedMimes[$mimeType];
        $destination = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['path' => null, 'error' => 'Impossible d\'enregistrer le fichier sur le serveur.'];
        }

        return ['path' => 'public/uploads/justificatifs/' . $fileName, 'error' => null];
    }
}