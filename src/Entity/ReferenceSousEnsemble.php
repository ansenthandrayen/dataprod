<?php

namespace App\Entity;

use App\Repository\ReferenceSousEnsembleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferenceSousEnsembleRepository::class)]
class ReferenceSousEnsemble
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
     * @var Collection<int, VersionSousEnsemble>
     */
    #[ORM\OneToMany(targetEntity: VersionSousEnsemble::class, mappedBy: 'referenceSousEnsemble')]
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
     * @return Collection<int, VersionSousEnsemble>
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    public function addVersion(VersionSousEnsemble $version): static
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setReferenceSousEnsemble($this);
        }

        return $this;
    }

    public function removeVersion(VersionSousEnsemble $version): static
    {
        if ($this->versions->removeElement($version)) {
            // set the owning side to null (unless already changed)
            if ($version->getReferenceSousEnsemble() === $this) {
                $version->setReferenceSousEnsemble(null);
            }
        }

        return $this;
    }
}
