<?php

namespace App\Entity;

use App\Repository\LotRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LotRepository::class)]
class Lot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $numero = null;

    #[ORM\ManyToOne(inversedBy: 'lots')]
    #[ORM\JoinColumn(nullable: false)]
    private ?VersionProduit $versionProduit = null;

    #[ORM\Column]
    private ?int $quantitePrevue = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getVersionProduit(): ?VersionProduit
    {
        return $this->versionProduit;
    }

    public function setVersionProduit(?VersionProduit $versionProduit): static
    {
        $this->versionProduit = $versionProduit;

        return $this;
    }

    public function getQuantitePrevue(): ?int
    {
        return $this->quantitePrevue;
    }

    public function setQuantitePrevue(int $quantitePrevue): static
    {
        $this->quantitePrevue = $quantitePrevue;

        return $this;
    }
}
