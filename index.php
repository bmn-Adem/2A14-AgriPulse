<?php
declare(strict_types=1);

// Affichage des erreurs en développement local
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// 1. Configuration sécurisée de la session
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_start();

// 2. Expiration automatique par inactivité (30 minutes)
$sessionLifetime = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $sessionLifetime) {
    $_SESSION = [];
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// 3. En-têtes HTTP de sécurité
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';

$authController = new AuthController();
$userController = new UserController();
$csrfToken = $authController->getCsrfToken();

$action = $_GET['action'] ?? 'home';
$errors = [];

switch ($action) {
    case 'home':
        require __DIR__ . '/views/home.php';
        break;

    case 'register':
        if (AuthController::isAuthenticated()) {
            header('Location: index.php?action=home');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $authController->register($_POST, $_FILES);
            if (empty($errors)) {
                $profile = $_POST['profile_type'] ?? 'client_b2c';
                $_SESSION['flash_success'] = ($profile === 'client_b2c')
                    ? 'Votre compte Client B2C a été créé et activé avec succès. Vous pouvez vous connecter.'
                    : 'Votre dossier professionnel a été enregistré. Un administrateur AgriPulse validera vos justificatifs sous peu.';
                header('Location: index.php?action=login');
                exit;
            }
        }
        require __DIR__ . '/views/auth/register.php';
        break;

    case 'login':
        if (AuthController::isAuthenticated()) {
            header('Location: index.php?action=home');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $authController->login($_POST);
            if (empty($errors)) {
                $_SESSION['flash_success'] = 'Connexion réussie. Bienvenue sur AgriPulse !';
                if (($_SESSION['role'] ?? '') === 'admin') {
                    header('Location: index.php?action=admin_users');
                } else {
                    header('Location: index.php?action=home');
                }
                exit;
            }
        }
        require __DIR__ . '/views/auth/login.php';
        break;

    case 'logout':
        $authController->logout();
        header('Location: index.php?action=login');
        exit;

    case 'admin_users':
        AuthController::requireRole('admin');
        $search = trim($_GET['search'] ?? '');
        $roleFilter = trim($_GET['role'] ?? '');
        $statutFilter = trim($_GET['statut'] ?? '');

        $users = $userController->getAllUsers($search, $roleFilter, $statutFilter);
        $pendingUsers = $userController->getPendingUsers();
        $roles = $userController->getAllRoles();
        $stats = [
            'total'        => $userController->countAllUsers(),
            'pending'      => $userController->countByStatut('en_attente'),
            'agriculteurs' => $userController->countByRole('agriculteur'),
            'b2b'          => $userController->countByRole('client_b2b'),
            'b2c'          => $userController->countByRole('client_b2c'),
        ];
        require __DIR__ . '/views/admin/users.php';
        break;

    case 'admin_create_user':
        AuthController::requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $userController->createUser($_POST);
            if (empty($errors)) {
                $_SESSION['flash_success'] = 'Nouvel utilisateur ajouté avec succès.';
                header('Location: index.php?action=admin_users');
                exit;
            }
        }
        $users = $userController->getAllUsers();
        $pendingUsers = $userController->getPendingUsers();
        $roles = $userController->getAllRoles();
        $stats = [
            'total'        => $userController->countAllUsers(),
            'pending'      => $userController->countByStatut('en_attente'),
            'agriculteurs' => $userController->countByRole('agriculteur'),
            'b2b'          => $userController->countByRole('client_b2b'),
            'b2c'          => $userController->countByRole('client_b2c'),
        ];
        require __DIR__ . '/views/admin/users.php';
        break;

    case 'admin_update_user':
        AuthController::requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $userController->updateUser($_POST, (int) $_SESSION['user_id']);
            if (empty($errors)) {
                $_SESSION['flash_success'] = 'Informations utilisateur mises à jour avec succès.';
                header('Location: index.php?action=admin_users');
                exit;
            }
        }
        $users = $userController->getAllUsers();
        $pendingUsers = $userController->getPendingUsers();
        $roles = $userController->getAllRoles();
        $stats = [
            'total'        => $userController->countAllUsers(),
            'pending'      => $userController->countByStatut('en_attente'),
            'agriculteurs' => $userController->countByRole('agriculteur'),
            'b2b'          => $userController->countByRole('client_b2b'),
            'b2c'          => $userController->countByRole('client_b2c'),
        ];
        require __DIR__ . '/views/admin/users.php';
        break;

    case 'admin_verify_user':
        AuthController::requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            $errors = $userController->verifyUser($id, $_POST['csrf_token'] ?? null);
            if (empty($errors)) {
                $_SESSION['flash_success'] = 'Le dossier professionnel a été approuvé et activé.';
            }
        }
        header('Location: index.php?action=admin_users');
        exit;

    case 'admin_delete_user':
        AuthController::requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            $errors = $userController->deleteUser($id, $_POST['csrf_token'] ?? null, (int) $_SESSION['user_id']);
            if (empty($errors)) {
                $_SESSION['flash_success'] = 'Utilisateur supprimé définitivement.';
            }
        }
        header('Location: index.php?action=admin_users');
        exit;

    default:
        http_response_code(404);
        echo '<h1>404 — Page introuvable</h1><a href="index.php?action=home">Retour à l\'accueil</a>';
        break;
}