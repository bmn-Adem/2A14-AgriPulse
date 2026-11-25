<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Role.php';

class UserController extends Controller
{
    public function dashboard(): void
    {
        $this->requireAdmin();
        $pdo = Database::getConnection();

        $this->render('back/dashboard', [
            'users' => User::getAllWithRole($pdo),
            'roles' => Role::getAll($pdo)
        ]);
    }

    public function index(): void
    {
        $this->requireAdmin();
        $pdo = Database::getConnection();

        $this->render('back/users', [
            'users' => User::getAllWithRole($pdo),
            'roles' => Role::getAll($pdo)
        ]);
    }

    public function changeRole(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $roleId = (int)($_POST['role_id'] ?? 3);

            if ($id > 0 && $roleId > 0) {
                $pdo = Database::getConnection();
                User::updateRole($pdo, $id, $roleId);
            }
        }
        $this->redirect('users');
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0 && $id !== (int)$_SESSION['user']['id']) {
            $pdo = Database::getConnection();
            User::delete($pdo, $id);
        }
        $this->redirect('users');
    }
}
