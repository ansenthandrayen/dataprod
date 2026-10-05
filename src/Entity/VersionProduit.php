<?php

namespace App\Entity;

use App\Repository\VersionProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, Nomenclature>
     */
    #[ORM\OneToMany(targetEntity: Nomenclature::class, mappedBy: 'versionProduit')]
    private Collection $nomenclatures;

    /**
     * @var Collection<int, Lot>
     */
    #[ORM\OneToMany(targetEntity: Lot::class, mappedBy: 'versionProduit')]
    private Collection $lots;

    public function __construct()
    {
        $this->nomenclatures = new ArrayCollection();
        $this->lots = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Nomenclature>
     */
    public function getNomenclatures(): Collection
    {
        return $this->nomenclatures;
    }

    public function addNomenclature(Nomenclature $nomenclature): static
    {
        if (!$this->nomenclatures->contains($nomenclature)) {
            $this->nomenclatures->add($nomenclature);
            $nomenclature->setVersionProduit($this);
        }

        return $this;
    }

    public function removeNomenclature(Nomenclature $nomenclature): static
    {
        if ($this->nomenclatures->removeElement($nomenclature)) {
            // set the owning side to null (unless already changed)
            if ($nomenclature->getVersionProduit() === $this) {
                $nomenclature->setVersionProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Lot>
     */
    public function getLots(): Collection
    {
        return $this->lots;
    }

    public function addLot(Lot $lot): static
    {
        if (!$this->lots->contains($lot)) {
            $this->lots->add($lot);
            $lot->setVersionProduit($this);
        }

        return $this;
    }

    public function removeLot(Lot $lot): static
    {
        if ($this->lots->removeElement($lot)) {
            // set the owning side to null (unless already changed)
            if ($lot->getVersionProduit() === $this) {
                $lot->setVersionProduit(null);
            }
        }

        return $this;
    }
}
