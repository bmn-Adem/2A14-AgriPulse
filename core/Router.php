<?php

class Router
{
    public function dispatch(): void
    {
        $page = $_GET['page'] ?? 'home';

        switch ($page) {
            case 'home':
                require __DIR__ . '/../views/front/home.php';
                break;

            case 'login':
                require_once __DIR__ . '/../controllers/AuthController.php';
                (new AuthController())->login();
                break;

            case 'register':
                require_once __DIR__ . '/../controllers/AuthController.php';
                (new AuthController())->register();
                break;

            case 'logout':
                require_once __DIR__ . '/../controllers/AuthController.php';
                (new AuthController())->logout();
                break;

            case 'dashboard':
                require_once __DIR__ . '/../controllers/UserController.php';
                (new UserController())->dashboard();
                break;

            case 'users':
                require_once __DIR__ . '/../controllers/UserController.php';
                (new UserController())->index();
                break;

            case 'user-role':
                require_once __DIR__ . '/../controllers/UserController.php';
                (new UserController())->changeRole();
                break;

            case 'user-delete':
                require_once __DIR__ . '/../controllers/UserController.php';
                (new UserController())->delete();
                break;

            default:
                require __DIR__ . '/../views/front/home.php';
                break;
        }
    }
}