<?php

namespace App\Entity;

use App\Repository\SystemeRepository;
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
}
