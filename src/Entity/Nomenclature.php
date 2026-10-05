<?php

namespace App\Entity;

use App\Repository\NomenclatureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NomenclatureRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_NOMENCLATURE_VERSION_SOUS_ENSEMBLE', fields: ['versionProduit', 'referenceSousEnsemble'])]
class Nomenclature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\ManyToOne(inversedBy: 'nomenclatures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?VersionProduit $versionProduit = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ReferenceSousEnsemble $referenceSousEnsemble = null;

    /**
     * @var Collection<int, VersionSousEnsemble>
     */
    #[ORM\ManyToMany(targetEntity: VersionSousEnsemble::class)]
    private Collection $versionsAcceptees;

    public function __construct()
    {
        $this->versionsAcceptees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

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

    public function getReferenceSousEnsemble(): ?ReferenceSousEnsemble
    {
        return $this->referenceSousEnsemble;
    }

    public function setReferenceSousEnsemble(?ReferenceSousEnsemble $referenceSousEnsemble): static
    {
        $this->referenceSousEnsemble = $referenceSousEnsemble;

        return $this;
    }

    /**
     * @return Collection<int, VersionSousEnsemble>
     */
    public function getVersionsAcceptees(): Collection
    {
        return $this->versionsAcceptees;
    }

    public function addVersionsAcceptee(VersionSousEnsemble $versionsAcceptee): static
    {
        if (!$this->versionsAcceptees->contains($versionsAcceptee)) {
            $this->versionsAcceptees->add($versionsAcceptee);
        }

        return $this;
    }

    public function removeVersionsAcceptee(VersionSousEnsemble $versionsAcceptee): static
    {
        $this->versionsAcceptees->removeElement($versionsAcceptee);

        return $this;
    }
}
