<?php

namespace App\Entity;

use App\Repository\VersionSousEnsembleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VersionSousEnsembleRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_VERSION_SOUS_ENSEMBLE_PAR_REFERENCE', fields: ['referenceSousEnsemble', 'lettre'])]
class VersionSousEnsemble
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $lettre = null;

    #[ORM\ManyToOne(inversedBy: 'versions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ReferenceSousEnsemble $referenceSousEnsemble = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLettre(): ?string
    {
        return $this->lettre;
    }

    public function setLettre(string $lettre): static
    {
        $this->lettre = $lettre;

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
