<?php

namespace App\Service;

use App\Entity\Lot;
use App\Entity\VersionProduit;

/**
 * Résultat de la recherche d'un numéro de lot : trois issues possibles.
 */
final readonly class ResultatLot
{
    private function __construct(
        public ?Lot $lot,                // renseigné si le lot existe déjà
        public ?VersionProduit $version, // renseignée si le lot doit être créé
        public ?string $erreur,          // renseignée si le numéro est refusé
    ) {
    }

    public static function existant(Lot $lot): self
    {
        return new self($lot, null, null);
    }

    public static function aCreer(VersionProduit $version): self
    {
        return new self(null, $version, null);
    }

    public static function refuse(string $erreur): self
    {
        return new self(null, null, $erreur);
    }

    public function existe(): bool
    {
        return null !== $this->lot;
    }

    public function doitEtreCree(): bool
    {
        return null !== $this->version;
    }

    public function estRefuse(): bool
    {
        return null !== $this->erreur;
    }
}