<?php
declare(strict_types=1);

/**
 * models/RoleModel.php
 * Entité représentant un rôle utilisateur dans AgriPulse.
 */
class RoleModel
{
    private ?int $id = null;
    private string $nom = '';
    private string $libelle = '';

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): self { $this->id = $id; return $this; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getLibelle(): string { return $this->libelle; }
    public function setLibelle(string $libelle): self { $this->libelle = $libelle; return $this; }

    public static function fromArray(array $data): self
    {
        $role = new self();
        return $role
            ->setId(isset($data['id']) ? (int) $data['id'] : null)
            ->setNom((string) ($data['nom'] ?? ''))
            ->setLibelle((string) ($data['libelle'] ?? ''));
    }
}