<?php

class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("Vue introuvable : " . htmlspecialchars($view));
        }
    }

    protected function redirect(string $page): void
    {
        header("Location: index.php?page=" . $page);
        exit();
    }

    protected function requireAdmin(): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role_nom'] !== 'admin') {
            $this->redirect('login');
        }
    }
}