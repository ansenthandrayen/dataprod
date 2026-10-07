<?php

namespace App\Service;

use App\Entity\VersionSousEnsemble;

/**
 * Résultat du contrôle d'un scan : soit accepté, soit refusé avec un message.
 */
final readonly class ResultatScan
{
    private function __construct(
        public ?string $erreur,
        public ?VersionSousEnsemble $version,
    ) {
    }

    public static function accepte(?VersionSousEnsemble $version = null): self
    {
        return new self(null, $version);
    }

    public static function refuse(string $erreur): self
    {
        return new self($erreur, null);
    }

    public function estAccepte(): bool
    {
        return null === $this->erreur;
    }
}