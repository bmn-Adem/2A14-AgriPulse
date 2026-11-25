<?php
declare(strict_types=1);

// 1. Configuration sécurisée de la session (Workshop Partie 5.5)
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_start();

// 2. Expiration automatique par inactivité (30 minutes = 1800s)
$sessionLifetime = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionLifetime)) {
    $_SESSION = [];
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// 3. En-têtes HTTP de sécurité (Workshop Partie 7.5)
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:;");

// 4. Chargement des contrôleurs
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';

$authController = new AuthController();
$userController = new UserController();

// 5. Routage centralisé via ?action=
$action = $_GET['action'] ?? 'home';

switch ($action) {
    case 'home':
        $pageTitle = 'AgriPulse — Transition Écologique & Agriculture Intelligente';
        $currentPage = 'home';
        require_once __DIR__ . '/views/front/home.php';
        break;

    case 'register':
        if (AuthController::isAuthenticated()) {
            header('Location: index.php?action=' . (($_SESSION['role'] ?? '') === 'admin' ? 'admin' : 'home'));
            exit;
        }
        $errors = [];
        $old = [];
        $selectedProfile = $_POST['profile_type'] ?? ($_GET['type'] ?? 'client_b2c');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $authController->register($_POST, $_FILES);
            $old = $_POST;
            if (empty($errors)) {
                $pending = in_array($selectedProfile, ['agriculteur', 'client_b2b'], true) ? '&pending=1' : '&registered=1';
                header('Location: index.php?action=login' . $pending);
                exit;
            }
        }
        $csrfToken = $authController->getCsrfToken();
        $pageTitle = 'Inscription — AgriPulse';
        $currentPage = 'register';
        require_once __DIR__ . '/views/auth/register.php';
        break;

    case 'login':
        if (AuthController::isAuthenticated()) {
            header('Location: index.php?action=' . (($_SESSION['role'] ?? '') === 'admin' ? 'admin' : 'home'));
            exit;
        }
        $errors = [];
        $oldEmail = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldEmail = $_POST['email'] ?? '';
            $errors = $authController->login($_POST);
            if (empty($errors)) {
                $redirect = (($_SESSION['role'] ?? '') === 'admin') ? 'admin' : 'home';
                header('Location: index.php?action=' . $redirect);
                exit;
            }
        }
        $csrfToken = $authController->getCsrfToken();
        $pageTitle = 'Connexion — AgriPulse';
        $currentPage = 'login';
        require_once __DIR__ . '/views/auth/login';
        break;

    case 'logout':
        $authController->logout();
        header('Location: index.php?action=login&logged_out=1');
        exit;

    case 'admin':
        AuthController::requireRole('admin');
        $currentUser = $authController->findById((int) $_SESSION['user_id']);
        $totalUsers = $userController->countAllUsers();
        $totalAdmins = $userController->countByRole('admin');
        $totalAgriculteurs = $userController->countByRole('agriculteur');
        $totalB2C = $userController->countByRole('client_b2c');
        $totalB2B = $userController->countByRole('client_b2b');
        $totalEnAttente = $userController->countByStatut('en_attente');
        $pendingUsers = $userController->getPendingUsers();

        $title = 'Tableau de Bord — AgriPulse Admin';
        $currentPage = 'dashboard';
        require_once __DIR__ . '/views/back/dashboard.php';
        break;

    case 'admin_users':
        AuthController::requireRole('admin');
        $currentUser = $authController->findById((int) $_SESSION['user_id']);
        $currentAdminId = (int) $_SESSION['user_id'];
        $errors = [];
        $successMsg = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subAction = $_POST['sub_action'] ?? '';
            if ($subAction === 'create') {
                $errors = $userController->createUser($_POST);
                if (empty($errors)) {
                    $successMsg = "Compte utilisateur créé avec succès.";
                }
            } elseif ($subAction === 'update') {
                $errors = $userController->updateUser($_POST, $currentAdminId);
                if (empty($errors)) {
                    $successMsg = "Informations et statut mis à jour avec succès.";
                }
            } elseif ($subAction === 'verify') {
                $verifyId = (int) ($_POST['id'] ?? 0);
                $errors = $userController->verifyUser($verifyId, $_POST['csrf_token'] ?? null);
                if (empty($errors)) {
                    $successMsg = "Dossier vérifié et compte activé avec succès.";
                }
            } elseif ($subAction === 'delete') {
                $deleteId = (int) ($_POST['id'] ?? 0);
                $errors = $userController->deleteUser($deleteId, $_POST['csrf_token'] ?? null, $currentAdminId);
                if (empty($errors)) {
                    $successMsg = "Utilisateur supprimé avec succès.";
                }
            }
        }

        $search = trim($_GET['search'] ?? '');
        $roleFilter = trim($_GET['role'] ?? '');
        $statutFilter = trim($_GET['statut'] ?? '');
        $allUsers = $userController->getAllUsers($search, $roleFilter, $statutFilter);
        $allRoles = $userController->getAllRoles();
        $csrfToken = $authController->getCsrfToken();

        $title = 'Gestion des Utilisateurs — AgriPulse Admin';
        $currentPage = 'users';
        require_once __DIR__ . '/views/back/users.php';
        break;

    default:
        http_response_code(404);
        echo '<h1>404 — Page introuvable</h1><p><a href="index.php?action=home">Retour à l\'accueil AgriPulse</a></p>';
        break;
}