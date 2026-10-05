<?php

namespace App\Entity;

use App\Repository\ReferenceProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferenceProduitRepository::class)]
class ReferenceProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    /**
     * @var Collection<int, VersionProduit>
     */
    #[ORM\OneToMany(targetEntity: VersionProduit::class, mappedBy: 'referenceProduit')]
    private Collection $versions;

    public function __construct()
    {
        $this->versions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, VersionProduit>
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    public function addVersion(VersionProduit $version): static
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setReferenceProduit($this);
        }

        return $this;
    }

    public function removeVersion(VersionProduit $version): static
    {
        if ($this->versions->removeElement($version)) {
            // set the owning side to null (unless already changed)
            if ($version->getReferenceProduit() === $this) {
                $version->setReferenceProduit(null);
            }
        }

        return $this;
    }
}
