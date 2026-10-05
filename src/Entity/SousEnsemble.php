<?php

namespace App\Entity;

use App\Repository\SousEnsembleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SousEnsembleRepository::class)]
class SousEnsemble
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $numeroSerie = null;

    #[ORM\ManyToOne(inversedBy: 'sousEnsembles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Systeme $systeme = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ReferenceSousEnsemble $referenceSousEnsemble = null;

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

    public function getSysteme(): ?Systeme
    {
        return $this->systeme;
    }

    public function setSysteme(?Systeme $systeme): static
    {
        $this->systeme = $systeme;

        return $this;
    }

    public function getReferenceSousEnsemble(): ?ReferenceSousEnsemble
    {
        return $this->referenceSousEnsemble;
    }

    public function setReferenceSousEnsemble(?ReferenceSousEnsemble $referenceSousEnsemble): static
    {
        $this->referenceSousEnsemble = $referenceSousEnsemble;

        return $this;
    }
}
