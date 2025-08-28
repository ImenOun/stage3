<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\DemandeRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
#[ORM\Table(name: 'demande')]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idDem', type: 'integer')]
    private ?int $idDem = null;

    public function getIdDem(): ?int
    {
        return $this->idDem;
    }

    public function setIdDem(int $idDem): self
    {
        $this->idDem = $idDem;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Le type est obligatoire')]
    #[Assert\Choice(
        choices: ['Attestation de Travail', 'Attestation de Salaire'],
        message: 'Veuillez sélectionner un type valide.'
    )]
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $statut = null;

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date_soumission = null;

    public function getDateSoumission(): ?\DateTime
    {
        return $this->date_soumission;
    }

    public function setDateSoumission(\DateTime $date_soumission): static
    {
        $this->date_soumission = $date_soumission;

        return $this;
    }

    

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'demandes')]
    #[ORM\JoinColumn(name: 'employe_id', referencedColumnName: 'id',nullable: false)]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: HistoriqueDemande::class, mappedBy: 'demande')]
    private Collection $historiqueDemandes;

    public function __construct()
    {
        $this->historiqueDemandes = new ArrayCollection();
         $this->date_soumission = new \DateTime(); // date et heure actuelles
        $this->statut = 'en attente'; // valeur par défaut

    
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

    

   

}
 