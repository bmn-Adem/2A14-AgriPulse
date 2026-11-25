<?php

require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller
{
    public function login(): void
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['mot_de_passe'] ?? '';

            $pdo = Database::getConnection();
            $user = User::findByEmail($pdo, $email);

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'nom' => $user['nom'],
                    'email' => $user['email'],
                    'role_id' => $user['role_id'],
                    'role_nom' => $user['role_nom']
                ];

                if ($user['role_nom'] === 'admin') {
                    $this->redirect('dashboard');
                } else {
                    $this->redirect('home');
                }
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        }

        $this->render('auth/login', ['error' => $error]);
    }

    public function register(): void
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['mot_de_passe'] ?? '';
            $roleId = (int)($_POST['role_id'] ?? 3);

            // Sécurité : empêcher de s'auto-inscrire en admin
            if (!in_array($roleId, [2, 3])) {
                $roleId = 3;
            }

            $pdo = Database::getConnection();

            if (empty($nom) || empty($email) || strlen($password) < 6) {
                $error = "Veuillez remplir tous les champs (mot de passe de 6 caractères minimum).";
            } elseif (User::findByEmail($pdo, $email)) {
                $error = "Cette adresse email est déjà utilisée.";
            } else {
                $user = new User(null, $nom, $email, $password, $roleId);
                if ($user->register($pdo)) {
                    header("Location: index.php?page=login&registered=1");
                    exit();
                } else {
                    $error = "Erreur lors de l'inscription.";
                }
            }
        }

        $this->render('auth/register', ['error' => $error]);
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('home');
    }
}