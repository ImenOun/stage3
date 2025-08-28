<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\HistoriqueDemandeRepository;

#[ORM\Entity(repositoryClass: HistoriqueDemandeRepository::class)]
#[ORM\Table(name: 'historique_demande')]
class HistoriqueDemande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idHisto', type: 'integer')]
    private ?int $idHisto = null;

    public function getIdHisto(): ?int
    {
        return $this->idHisto;
    }

    public function setIdHisto(int $idHisto): self
    {
        $this->idHisto = $idHisto;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $action = null;

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_action = null;

    public function getDate_action(): ?\DateTimeInterface
    {
        return $this->date_action;
    }

    public function setDate_action(\DateTimeInterface $date_action): self
    {
        $this->date_action = $date_action;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Demande::class, inversedBy: 'historiqueDemandes')]
    #[ORM\JoinColumn(name: 'demande_id', referencedColumnName: 'idDem')]
    private ?Demande $demande = null;

    public function getDemande(): ?Demande
    {
        return $this->demande;
    }

    public function setDemande(?Demande $demande): self
    {
        $this->demande = $demande;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'historiqueDemandes')]
    #[ORM\JoinColumn(name: 'acteur_id', referencedColumnName: 'id')]
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

    public function getDateAction(): ?\DateTime
    {
        return $this->date_action;
    }

    public function setDateAction(\DateTime $date_action): static
    {
        $this->date_action = $date_action;

        return $this;
    }

}