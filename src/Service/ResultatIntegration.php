<?php

namespace App\Service;

use App\Entity\VersionSousEnsemble;

/**
 * Résultat du contrôle complet d'une intégration soumise.
 */
final readonly class ResultatIntegration
{
    /**
     * @param string[]                           $erreurs  messages à afficher (vide = tout est bon)
     * @param array<string, VersionSousEnsemble> $versions SN du sous-ensemble => version identifiée
     */
    public function __construct(
        public array $erreurs,
        public array $versions,
    ) {
    }

    public function estValide(): bool
    {
        return [] === $this->erreurs;
    }
}