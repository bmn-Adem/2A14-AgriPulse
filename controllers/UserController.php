<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AuthController.php';

/**
 * controllers/UserController.php
 * Gestion CRUD des utilisateurs, filtrage multi-profils et validation
 * administrative des dossiers Agriculteurs & Clients B2B.
 */
class UserController
{
    private PDO $pdo;
    private AuthController $auth;

    public function __construct()
    {
        $this->pdo = Database::getConnexion();
        $this->auth = new AuthController();
    }

    public function getAllRoles(): array
    {
        $stmt = $this->pdo->query('SELECT id, nom, libelle FROM roles ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    public function getAllUsers(string $search = '', string $roleFilter = '', string $statutFilter = ''): array
    {
        $sql = 'SELECT u.*, r.nom AS role_nom, r.libelle AS role_libelle
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (u.nom LIKE :s1 OR u.prenom LIKE :s2 OR u.email LIKE :s3 OR u.cin LIKE :s4 OR u.raison_sociale LIKE :s5 OR u.matricule_fiscal LIKE :s6)';
            $like = '%' . $search . '%';
            $params[':s1'] = $like;
            $params[':s2'] = $like;
            $params[':s3'] = $like;
            $params[':s4'] = $like;
            $params[':s5'] = $like;
            $params[':s6'] = $like;
        }

        if ($roleFilter !== '') {
            $sql .= ' AND r.nom = :role';
            $params[':role'] = $roleFilter;
        }

        if ($statutFilter !== '') {
            $sql .= ' AND u.statut = :statut';
            $params[':statut'] = $statutFilter;
        }

        $sql .= ' ORDER BY u.created_at DESC, u.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getPendingUsers(): array
    {
        $stmt = $this->pdo->query(
            "SELECT u.*, r.nom AS role_nom, r.libelle AS role_libelle
             FROM users u
             INNER JOIN roles r ON u.role_id = r.id
             WHERE u.statut = 'en_attente'
             ORDER BY u.created_at ASC"
        );
        return $stmt->fetchAll();
    }

    public function countAllUsers(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function countByRole(string $roleNom): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM users u INNER JOIN roles r ON u.role_id = r.id WHERE r.nom = :role'
        );
        $stmt->execute([':role' => $roleNom]);
        return (int) $stmt->fetchColumn();
    }

    public function countByStatut(string $statut): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE statut = :statut');
        $stmt->execute([':statut' => $statut]);
        return (int) $stmt->fetchColumn();
    }

    public function createUser(array $post): array
    {
        $errors = [];

        if (!$this->auth->verifyCsrf($post['csrf_token'] ?? null)) {
            $errors[] = 'Jeton de sécurité CSRF invalide.';
            return $errors;
        }

        $roleId          = (int) ($post['role_id'] ?? 3);
        $nom             = trim($post['nom'] ?? '');
        $prenom          = trim($post['prenom'] ?? '');
        $email           = trim($post['email'] ?? '');
        $telephone       = trim($post['telephone'] ?? '');
        $password        = $post['password'] ?? '';
        $gouvernorat     = trim($post['gouvernorat'] ?? '');
        $delegation      = trim($post['delegation'] ?? '');
        $adresseExacte   = trim($post['adresse_exacte'] ?? '');
        $cin             = trim($post['cin'] ?? '');
        $typeActivite    = trim($post['type_activite'] ?? '');
        $superficieRaw   = trim($post['superficie_ha'] ?? '');
        $raisonSociale   = trim($post['raison_sociale'] ?? '');
        $matriculeFiscal = strtoupper(trim($post['matricule_fiscal'] ?? ''));
        $statut          = trim($post['statut'] ?? 'verifie');

        if (!in_array($roleId, [1, 2, 3, 4], true)) {
            $errors[] = 'Rôle sélectionné invalide.';
        }
        if (!in_array($statut, ['en_attente', 'verifie', 'suspendu'], true)) {
            $errors[] = 'Statut sélectionné invalide.';
        }
        if ($nom === '' || !preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,80}$/u', $nom)) {
            $errors[] = 'Le nom est obligatoire (2 à 80 lettres uniquement).';
        }
        if ($prenom === '' || !preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,80}$/u', $prenom)) {
            $errors[] = 'Le prénom est obligatoire (2 à 80 lettres uniquement).';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        } else {
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Cette adresse email existe déjà.';
            }
        }
        if (!preg_match('/^[24579]\d{7}$/', $telephone)) {
            $errors[] = 'Le téléphone tunisien doit contenir 8 chiffres (commençant par 2, 4, 5, 7 ou 9).';
        }
        if ($gouvernorat === '' || $delegation === '') {
            $errors[] = 'Le gouvernorat et la délégation sont obligatoires.';
        }
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#_\-]).{8,}$/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.';
        }

        if ($roleId === 2) {
            if (!preg_match('/^[01]\d{7}$/', $cin)) {
                $errors[] = 'La CIN tunisienne est obligatoire pour un agriculteur (8 chiffres commençant par 0 ou 1).';
            } else {
                $stmt = $this->pdo->prepare('SELECT id FROM users WHERE cin = :cin');
                $stmt->execute([':cin' => $cin]);
                if ($stmt->fetch()) {
                    $errors[] = 'Ce numéro de CIN est déjà enregistré.';
                }
            }
        }

        if ($roleId === 4) {
            if ($raisonSociale === '') {
                $errors[] = 'La raison sociale est obligatoire pour un client professionnel (B2B).';
            }
            if (!preg_match('/^\d{7}[\/\-]?[A-Z][\/\-]?[A-Z0-9]{0,4}[\/\-]?\d{0,3}$/', $matriculeFiscal)) {
                $errors[] = 'Le Matricule Fiscal tunisien est invalide (ex: 1548920/A/M/000).';
            } else {
                $stmt = $this->pdo->prepare('SELECT id FROM users WHERE matricule_fiscal = :mf');
                $stmt->execute([':mf' => $matriculeFiscal]);
                if ($stmt->fetch()) {
                    $errors[] = 'Ce Matricule Fiscal est déjà utilisé.';
                }
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        $superficieHa = ($roleId === 2 && $superficieRaw !== '' && is_numeric($superficieRaw))
            ? round((float) $superficieRaw, 2)
            : null;

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (
                role_id, nom, prenom, email, telephone, password,
                gouvernorat, delegation, adresse_exacte,
                cin, type_activite, superficie_ha,
                raison_sociale, matricule_fiscal, statut
             ) VALUES (
                :role_id, :nom, :prenom, :email, :telephone, :password,
                :gouvernorat, :delegation, :adresse_exacte,
                :cin, :type_activite, :superficie_ha,
                :raison_sociale, :matricule_fiscal, :statut
             )'
        );

        $stmt->execute([
            ':role_id'          => $roleId,
            ':nom'              => $nom,
            ':prenom'           => $prenom,
            ':email'            => $email,
            ':telephone'        => $telephone,
            ':password'         => password_hash($password, PASSWORD_DEFAULT),
            ':gouvernorat'      => $gouvernorat,
            ':delegation'       => $delegation,
            ':adresse_exacte'   => $adresseExacte !== '' ? $adresseExacte : null,
            ':cin'              => $roleId === 2 ? $cin : null,
            ':type_activite'    => ($roleId === 2 && $typeActivite !== '') ? $typeActivite : null,
            ':superficie_ha'    => $superficieHa,
            ':raison_sociale'   => $roleId === 4 ? $raisonSociale : null,
            ':matricule_fiscal' => $roleId === 4 ? $matriculeFiscal : null,
            ':statut'           => $statut,
        ]);

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return [];
    }

    public function updateUser(array $post, int $currentAdminId): array
    {
        $errors = [];

        if (!$this->auth->verifyCsrf($post['csrf_token'] ?? null)) {
            $errors[] = 'Jeton de sécurité CSRF invalide.';
            return $errors;
        }

        $id            = (int) ($post['id'] ?? 0);
        $roleId        = (int) ($post['role_id'] ?? 3);
        $nom           = trim($post['nom'] ?? '');
        $prenom        = trim($post['prenom'] ?? '');
        $email         = trim($post['email'] ?? '');
        $telephone     = trim($post['telephone'] ?? '');
        $gouvernorat   = trim($post['gouvernorat'] ?? '');
        $delegation    = trim($post['delegation'] ?? '');
        $adresseExacte = trim($post['adresse_exacte'] ?? '');
        $statut        = trim($post['statut'] ?? 'verifie');
        $newPassword   = $post['password'] ?? '';

        if ($id <= 0) {
            $errors[] = 'Identifiant utilisateur invalide.';
            return $errors;
        }
        if ($id === $currentAdminId && $statut === 'suspendu') {
            $errors[] = 'Vous ne pouvez pas suspendre votre propre compte administrateur.';
            return $errors;
        }
        if ($id === $currentAdminId && $roleId !== 1) {
            $errors[] = 'Vous ne pouvez pas retirer vos propres droits d\'administrateur.';
            return $errors;
        }
        if ($nom === '' || !preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,80}$/u', $nom)) {
            $errors[] = 'Le nom est obligatoire (lettres uniquement).';
        }
        if ($prenom === '' || !preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,80}$/u', $prenom)) {
            $errors[] = 'Le prénom est obligatoire (lettres uniquement).';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        } else {
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id');
            $stmt->execute([':email' => $email, ':id' => $id]);
            if ($stmt->fetch()) {
                $errors[] = 'Cette adresse email est déjà utilisée par un autre compte.';
            }
        }
        if (!preg_match('/^[24579]\d{7}$/', $telephone)) {
            $errors[] = 'Numéro de téléphone tunisien invalide (8 chiffres).';
        }
        if ($newPassword !== '' && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#_\-]).{8,}$/', $newPassword)) {
            $errors[] = 'Le nouveau mot de passe doit respecter les critères de complexité (8+ car., majuscule, minuscule, chiffre, spécial).';
        }

        if (!empty($errors)) {
            return $errors;
        }

        if ($newPassword !== '') {
            $stmt = $this->pdo->prepare(
                'UPDATE users
                 SET role_id = :role_id, nom = :nom, prenom = :prenom, email = :email,
                     telephone = :telephone, gouvernorat = :gouvernorat, delegation = :delegation,
                     adresse_exacte = :adresse_exacte, statut = :statut, password = :password
                 WHERE id = :id'
            );
            $stmt->execute([
                ':role_id'        => $roleId,
                ':nom'            => $nom,
                ':prenom'         => $prenom,
                ':email'          => $email,
                ':telephone'      => $telephone,
                ':gouvernorat'    => $gouvernorat,
                ':delegation'     => $delegation,
                ':adresse_exacte' => $adresseExacte !== '' ? $adresseExacte : null,
                ':statut'         => $statut,
                ':password'       => password_hash($newPassword, PASSWORD_DEFAULT),
                ':id'             => $id,
            ]);
        } else {
            $stmt = $this->pdo->prepare(
                'UPDATE users
                 SET role_id = :role_id, nom = :nom, prenom = :prenom, email = :email,
                     telephone = :telephone, gouvernorat = :gouvernorat, delegation = :delegation,
                     adresse_exacte = :adresse_exacte, statut = :statut
                 WHERE id = :id'
            );
            $stmt->execute([
                ':role_id'        => $roleId,
                ':nom'            => $nom,
                ':prenom'         => $prenom,
                ':email'          => $email,
                ':telephone'      => $telephone,
                ':gouvernorat'    => $gouvernorat,
                ':delegation'     => $delegation,
                ':adresse_exacte' => $adresseExacte !== '' ? $adresseExacte : null,
                ':statut'         => $statut,
                ':id'             => $id,
            ]);
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return [];
    }

    public function verifyUser(int $id, ?string $csrfToken): array
    {
        if (!$this->auth->verifyCsrf($csrfToken)) {
            return ['Jeton de sécurité CSRF invalide.'];
        }
        if ($id <= 0) {
            return ['Identifiant de dossier invalide.'];
        }

        $stmt = $this->pdo->prepare("UPDATE users SET statut = 'verifie' WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return [];
    }

    public function deleteUser(int $id, ?string $csrfToken, int $currentAdminId): array
    {
        if (!$this->auth->verifyCsrf($csrfToken)) {
            return ['Jeton de sécurité CSRF invalide.'];
        }
        if ($id <= 0) {
            return ['Identifiant utilisateur invalide.'];
        }
        if ($id === $currentAdminId) {
            return ['Impossible de supprimer votre propre compte administrateur actif.'];
        }

        $user = $this->auth->findById($id);
        if ($user) {
            foreach (['doc_cin_path', 'doc_justificatif_path'] as $docCol) {
                if (!empty($user[$docCol])) {
                    $fullPath = __DIR__ . '/../' . $user[$docCol];
                    if (is_file($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }
        }

        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return [];
    }
}