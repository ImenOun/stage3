<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Permission;
use App\Repository\PermissionRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
     }

            public function getUserIdentifier(): string
{
    return (string) $this->email; // ou autre champ unique utilisé comme identifiant
}

/**
 * @deprecated 
 */
public function getUsername(): string
{
    return $this->getUserIdentifier();
}

public function getRoles(): array
{
    return ['ROLE_USER'];
}

public function eraseCredentials(): void
{
    // Ici tu peux nettoyer des infos sensibles si besoin 
}


#[ORM\ManyToMany(targetEntity: Permission::class)]
#[ORM\JoinTable(
    name: 'user_permission',  // <- nom de la table de jointure 
    joinColumns: [new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')],
    inverseJoinColumns: [new ORM\JoinColumn(name: 'permission_id', referencedColumnName: 'id')]
)]
private Collection $permissions;
public function getPermissions(): Collection
{
    return $this->permissions;
}

public function addPermission(Permission $permission): static
{
    if (!$this->permissions->contains($permission)) {
        $this->permissions->add($permission);
    }
    return $this;
}

public function removePermission(Permission $permission): static
{
    $this->permissions->removeElement($permission);
    return $this;
}

    

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $password = null;

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }
    
#[ORM\Column(name:'salaireNet', type: 'decimal', precision: 10, scale: 2, nullable: true)]
private ?string $salaireNet = null;

#[ORM\Column(name:'salaireBrute',type: 'decimal', precision: 10, scale: 2, nullable: true)]
private ?string $salaireBrute = null;

#[ORM\Column(name:'salaireNetInitial', type: 'decimal', precision: 10, scale: 2, nullable: true)]
private ?string $salaireNetInitial = null;

public function getSalaireNetInitial(): ?string
{
    return $this->salaireNetInitial;
}

public function setSalaireNetInitial(?string $salaireNetInitial): self
{
    $this->salaireNetInitial = $salaireNetInitial;
    return $this;
}


public function getSalaireNet(): ?string
{
    return $this->salaireNet;
}

public function setSalaireNet(?string $salaireNet): self
{
    $this->salaireNet = $salaireNet;
    return $this;
}

public function getSalaireBrute(): ?string
{
    return $this->salaireBrute;
}

public function setSalaireBrute(?string $salaireBrute): self
{
    $this->salaireBrute = $salaireBrute;
    return $this;
}


    #[ORM\OneToMany(targetEntity: Demande::class, mappedBy: 'user')]
    private Collection $demandes;

    /**
     * @return Collection<int, Demande>
     */
    public function getDemandes(): Collection
    {
        if (!$this->demandes instanceof Collection) {
            $this->demandes = new ArrayCollection();
        }
        return $this->demandes;
    }

    public function addDemande(Demande $demande): self
    {
        if (!$this->getDemandes()->contains($demande)) {
            $this->getDemandes()->add($demande);
        }
        return $this;
    }

    public function removeDemande(Demande $demande): self
    {
        $this->getDemandes()->removeElement($demande);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: HistoriqueDemande::class, mappedBy: 'user')]
    private Collection $historiqueDemandes;

    public function __construct()
{
    $this->demandes = new ArrayCollection();
    $this->historiqueDemandes = new ArrayCollection();
    $this->permissions = new ArrayCollection(); // si tu as ajouté ça aussi
}
    /**
     * @return Collection<int, HistoriqueDemande>
     */
    public function getHistoriqueDemandes(): Collection
    {
        if (!$this->historiqueDemandes instanceof Collection) {
            $this->historiqueDemandes = new ArrayCollection();
        }
        return $this->historiqueDemandes;
    }

    public function addHistoriqueDemande(HistoriqueDemande $historiqueDemande): self
    {
        if (!$this->getHistoriqueDemandes()->contains($historiqueDemande)) {
            $this->getHistoriqueDemandes()->add($historiqueDemande);
        }
        return $this;
    }

    public function removeHistoriqueDemande(HistoriqueDemande $historiqueDemande): self
    {
        $this->getHistoriqueDemandes()->removeElement($historiqueDemande);
        return $this;
    }
    public function hasPermission(string $permissionName): bool
    {
        foreach ($this->permissions as $permission) {
            if ($permission->getName() === $permissionName) {
                return true;
            }
        }
        return false;
    }


}