<?php

class User
{
    private ?int $id;
    private string $nom;
    private string $email;
    private string $motDePasse;
    private int $roleId;

    public function __construct(?int $id = null, string $nom = '', string $email = '', string $motDePasse = '', int $roleId = 3)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->email = $email;
        $this->motDePasse = $motDePasse;
        $this->roleId = $roleId;
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getEmail(): string { return $this->email; }
    public function getRoleId(): int { return $this->roleId; }

    public function register(PDO $pdo): bool
    {
        $sql = "INSERT INTO user (nom, email, mot_de_passe, role_id) VALUES (:nom, :email, :mot_de_passe, :role_id)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':nom' => $this->nom,
            ':email' => $this->email,
            ':mot_de_passe' => password_hash($this->motDePasse, PASSWORD_BCRYPT),
            ':role_id' => $this->roleId
        ]);
    }

    public static function findByEmail(PDO $pdo, string $email): ?array
    {
        $sql = "SELECT u.*, r.nom AS role_nom FROM user u INNER JOIN role r ON u.role_id = r.id WHERE u.email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getAllWithRole(PDO $pdo): array
    {
        $sql = "SELECT u.id, u.nom, u.email, u.role_id, u.date_inscription, r.nom AS role_nom
                FROM user u INNER JOIN role r ON u.role_id = r.id ORDER BY u.id DESC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function updateRole(PDO $pdo, int $id, int $roleId): bool
    {
        $stmt = $pdo->prepare("UPDATE user SET role_id = :role_id WHERE id = :id");
        return $stmt->execute([':role_id' => $roleId, ':id' => $id]);
    }

    public static function delete(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare("DELETE FROM user WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}