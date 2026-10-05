<?php

namespace App\Entity;

use App\Repository\SystemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SystemeRepository::class)]
class Systeme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $numeroSerie = null;

    #[ORM\ManyToOne(inversedBy: 'systemes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lot $lot = null;

    /**
     * @var Collection<int, SousEnsemble>
     */
    #[ORM\OneToMany(targetEntity: SousEnsemble::class, mappedBy: 'systeme')]
    private Collection $sousEnsembles;

    public function __construct()
    {
        $this->sousEnsembles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroSerie(): ?string
    {
        return $this->numeroSerie;
    }

    public function setNumeroSerie(string $numeroSerie): static
    {
        $this->numeroSerie = $numeroSerie;

        return $this;
    }

    public function getLot(): ?Lot
    {
        return $this->lot;
    }

    public function setLot(?Lot $lot): static
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * @return Collection<int, SousEnsemble>
     */
    public function getSousEnsembles(): Collection
    {
        return $this->sousEnsembles;
    }

    public function addSousEnsemble(SousEnsemble $sousEnsemble): static
    {
        if (!$this->sousEnsembles->contains($sousEnsemble)) {
            $this->sousEnsembles->add($sousEnsemble);
            $sousEnsemble->setSysteme($this);
        }

        return $this;
    }

    public function removeSousEnsemble(SousEnsemble $sousEnsemble): static
    {
        if ($this->sousEnsembles->removeElement($sousEnsemble)) {
            // set the owning side to null (unless already changed)
            if ($sousEnsemble->getSysteme() === $this) {
                $sousEnsemble->setSysteme(null);
            }
        }

        return $this;
    }
}
