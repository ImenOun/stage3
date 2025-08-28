<?php
namespace App\Entity;

use App\Repository\AvanceSalaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: AvanceSalaireRepository::class)]

class AvanceSalaire
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

    #[ORM\Column(type: 'decimal', nullable: false)]
     #[Assert\NotBlank(message: 'Veuillez saisir un montant.')]
    #[Assert\Positive(message: 'Le montant doit être supérieur à 0.')]
    private ?float $montant = null;

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;
        return $this;
    }
     
    // ---- CONTRÔLE DE SAISIE ----
    
    #[Assert\Callback]
    public function validateMontant(ExecutionContextInterface $context): void
    {
        if ($this->user && $this->montant !== null) {
            $salaireNet = $this->user->getSalaireNet();

            if ($this->montant >= $salaireNet) {
                $context->buildViolation("Le montant de l'avance doit être strictement inférieur au salaire net de l'employé ({$salaireNet}).")
                    ->atPath('montant')
                    ->addViolation();
            }
        }
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $raison = null;

    public function getRaison(): ?string
    {
        return $this->raison;
    }

    public function setRaison(?string $raison): self
    {
        $this->raison = $raison;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_demande = null;

    public function getDate_demande(): ?\DateTimeInterface
    {
        return $this->date_demande;
    }

    public function setDate_demande(\DateTimeInterface $date_demande): self
    {
        $this->date_demande = $date_demande;
        return $this;
    }

#[ORM\Column(type: 'datetime', nullable: true)]
private ?\DateTimeInterface $moisPrelevement = null;

public function getMoisPrelevement(): ?\DateTimeInterface
{
    return $this->moisPrelevement;
}

public function setMoisPrelevement(?\DateTimeInterface $moisPrelevement): self
{
    $this->moisPrelevement = $moisPrelevement;
    return $this;
}

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statut = null;

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'avance_salaires')]
    #[ORM\JoinColumn(name: 'employe_id', referencedColumnName: 'id')]
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

    public function getDateDemande(): ?\DateTime
    {
        return $this->date_demande;
    }

    public function setDateDemande(\DateTime $date_demande): static
    {
        $this->date_demande = $date_demande;

        return $this;
    }
}

