<?php
declare(strict_types=1);

/**
 * models/UserModel.php
 * Entité PHP pure représentant un utilisateur multi-profils (Admin, Agriculteur, Client B2C, Client B2B).
 */
class UserModel
{
    private ?int $id = null;
    private int $roleId = 3;
    private string $nom = '';
    private string $prenom = '';
    private string $email = '';
    private string $telephone = '';
    private string $password = '';
    private string $gouvernorat = '';
    private string $delegation = '';
    private ?string $adresseExacte = null;
    private ?string $cin = null;
    private ?string $typeActivite = null;
    private ?float $superficieHa = null;
    private ?string $docCinPath = null;
    private ?string $raisonSociale = null;
    private ?string $matriculeFiscal = null;
    private ?string $docJustificatifPath = null;
    private string $statut = 'verifie';
    private ?string $createdAt = null;
    private ?string $roleNom = null;
    private ?string $roleLibelle = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): self { $this->id = $id; return $this; }

    public function getRoleId(): int { return $this->roleId; }
    public function setRoleId(int $roleId): self { $this->roleId = $roleId; return $this; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getPrenom(): string { return $this->prenom; }
    public function setPrenom(string $prenom): self { $this->prenom = $prenom; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getTelephone(): string { return $this->telephone; }
    public function setTelephone(string $telephone): self { $this->telephone = $telephone; return $this; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }

    public function getGouvernorat(): string { return $this->gouvernorat; }
    public function setGouvernorat(string $gouvernorat): self { $this->gouvernorat = $gouvernorat; return $this; }

    public function getDelegation(): string { return $this->delegation; }
    public function setDelegation(string $delegation): self { $this->delegation = $delegation; return $this; }

    public function getAdresseExacte(): ?string { return $this->adresseExacte; }
    public function setAdresseExacte(?string $adresseExacte): self { $this->adresseExacte = $adresseExacte; return $this; }

    public function getCin(): ?string { return $this->cin; }
    public function setCin(?string $cin): self { $this->cin = $cin; return $this; }

    public function getTypeActivite(): ?string { return $this->typeActivite; }
    public function setTypeActivite(?string $typeActivite): self { $this->typeActivite = $typeActivite; return $this; }

    public function getSuperficieHa(): ?float { return $this->superficieHa; }
    public function setSuperficieHa(?float $superficieHa): self { $this->superficieHa = $superficieHa; return $this; }

    public function getDocCinPath(): ?string { return $this->docCinPath; }
    public function setDocCinPath(?string $docCinPath): self { $this->docCinPath = $docCinPath; return $this; }

    public function getRaisonSociale(): ?string { return $this->raisonSociale; }
    public function setRaisonSociale(?string $raisonSociale): self { $this->raisonSociale = $raisonSociale; return $this; }

    public function getMatriculeFiscal(): ?string { return $this->matriculeFiscal; }
    public function setMatriculeFiscal(?string $matriculeFiscal): self { $this->matriculeFiscal = $matriculeFiscal; return $this; }

    public function getDocJustificatifPath(): ?string { return $this->docJustificatifPath; }
    public function setDocJustificatifPath(?string $docJustificatifPath): self { $this->docJustificatifPath = $docJustificatifPath; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): self { $this->statut = $statut; return $this; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getRoleNom(): ?string { return $this->roleNom; }
    public function setRoleNom(?string $roleNom): self { $this->roleNom = $roleNom; return $this; }

    public function getRoleLibelle(): ?string { return $this->roleLibelle; }
    public function setRoleLibelle(?string $roleLibelle): self { $this->roleLibelle = $roleLibelle; return $this; }

    public function getFullName(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public static function fromArray(array $data): self
    {
        $user = new self();
        return $user
            ->setId(isset($data['id']) ? (int) $data['id'] : null)
            ->setRoleId((int) ($data['role_id'] ?? 3))
            ->setNom((string) ($data['nom'] ?? ''))
            ->setPrenom((string) ($data['prenom'] ?? ''))
            ->setEmail((string) ($data['email'] ?? ''))
            ->setTelephone((string) ($data['telephone'] ?? ''))
            ->setPassword((string) ($data['password'] ?? ''))
            ->setGouvernorat((string) ($data['gouvernorat'] ?? ''))
            ->setDelegation((string) ($data['delegation'] ?? ''))
            ->setAdresseExacte($data['adresse_exacte'] ?? null)
            ->setCin($data['cin'] ?? null)
            ->setTypeActivite($data['type_activite'] ?? null)
            ->setSuperficieHa(isset($data['superficie_ha']) ? (float) $data['superficie_ha'] : null)
            ->setDocCinPath($data['doc_cin_path'] ?? null)
            ->setRaisonSociale($data['raison_sociale'] ?? null)
            ->setMatriculeFiscal($data['matricule_fiscal'] ?? null)
            ->setDocJustificatifPath($data['doc_justificatif_path'] ?? null)
            ->setStatut((string) ($data['statut'] ?? 'verifie'))
            ->setCreatedAt($data['created_at'] ?? null)
            ->setRoleNom($data['role_nom'] ?? null)
            ->setRoleLibelle($data['role_libelle'] ?? null);
    }
}