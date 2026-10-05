<?php

namespace App\Entity;

use App\Repository\VersionProduitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VersionProduitRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_VERSION_PAR_REFERENCE', fields: ['referenceProduit', 'lettre'])]
class VersionProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $lettre = null;

    #[ORM\ManyToOne(inversedBy: 'versions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ReferenceProduit $referenceProduit = null;

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

    public function getReferenceProduit(): ?ReferenceProduit
    {
        return $this->referenceProduit;
    }

    public function setReferenceProduit(?ReferenceProduit $referenceProduit): static
    {
        $this->referenceProduit = $referenceProduit;

        return $this;
    }
}
