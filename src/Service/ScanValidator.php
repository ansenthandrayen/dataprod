<?php

namespace App\Service;

use App\Entity\Lot;
use App\Entity\Nomenclature;
use App\Repository\SousEnsembleRepository;
use App\Repository\SystemeRepository;

/**
 * Règles de contrôle des scans de la page Intégration.
 * Aucune dépendance à HTTP : on peut donc la tester seule (PHPUnit).
 */
class ScanValidator
{
    // Format d'un SN de sous-ensemble : "ORVEX-USB A35260101"
    // = référence (sans espace) + espace + lettre de version + code semaine/année (4 chiffres) + compteur (4 chiffres)
    private const FORMAT_SN_SOUS_ENSEMBLE = '/^(\S+) ([A-Z])\d{4}\d{4}$/';

    // Injection de dépendances : Symfony fournit les deux repositories automatiquement
    public function __construct(
        private SystemeRepository $systemes,
        private SousEnsembleRepository $sousEnsembles,
    ) {
    }

    /** Contrôle le SN d'un système scanné pour un lot donné. */
    public function validerSysteme(string $sn, Lot $lot): ResultatScan
    {
        $sn = trim($sn);

        // Le SN doit être : numéro du lot + 4 chiffres
        $motif = '/^' . preg_quote((string) $lot->getNumero(), '/') . '\d{4}$/';
        if (!preg_match($motif, $sn)) {
            return ResultatScan::refuse(sprintf('Ce SN ne correspond pas au lot %s.', $lot->getNumero()));
        }

        if (null !== $this->systemes->findOneBy(['numeroSerie' => $sn])) {
            return ResultatScan::refuse('Ce système est déjà enregistré.');
        }

        return ResultatScan::accepte();
    }

    /**
     * Contrôle le SN d'un sous-ensemble scanné.
     *
     * @param string[] $dejaScannes SN déjà scannés dans le formulaire en cours
     */
    public function validerSousEnsemble(string $sn, Lot $lot, array $dejaScannes): ResultatScan
    {
        $sn = trim($sn);

        $analyse = $this->analyser($sn);
        if (null === $analyse) {
            return ResultatScan::refuse('Format de SN non reconnu.');
        }
        [$reference, $lettre] = $analyse;

        // 1) La référence doit figurer dans la nomenclature de la version du lot
        $ligne = $this->trouverLigne($lot, $reference);
        if (null === $ligne) {
            return ResultatScan::refuse(sprintf("%s n'est pas attendu pour ce produit.", $reference));
        }

        // 2) La version doit faire partie des versions acceptées sur cette ligne
        $version = null;
        foreach ($ligne->getVersionsAcceptees() as $acceptee) {
            if ($acceptee->getLettre() === $lettre) {
                $version = $acceptee;
                break;
            }
        }
        if (null === $version) {
            $acceptees = array_map(fn ($v) => $v->getLettre(), $ligne->getVersionsAcceptees()->toArray());

            return ResultatScan::refuse(sprintf(
                'Version %s refusée pour %s (acceptées : %s).',
                $lettre,
                $reference,
                implode(', ', $acceptees)
            ));
        }

        // 3) Pas de doublon dans le formulaire en cours, ni déjà en base
        if (in_array($sn, $dejaScannes, true)) {
            return ResultatScan::refuse('Ce SN a déjà été scanné.');
        }
        if (null !== $this->sousEnsembles->findOneBy(['numeroSerie' => $sn])) {
            return ResultatScan::refuse('Ce sous-ensemble est déjà enregistré.');
        }

        // 4) La quantité attendue ne doit pas être dépassée
        if ($this->compter($dejaScannes, $reference) >= $ligne->getQuantite()) {
            return ResultatScan::refuse(sprintf('Tous les %s attendus sont déjà scannés.', $reference));
        }

        return ResultatScan::accepte($version);
    }

    /**
     * Lignes de nomenclature pas encore complètes. Liste vide = on peut valider.
     *
     * @param string[] $snScannes
     *
     * @return string[]
     */
    public function lignesIncompletes(Lot $lot, array $snScannes): array
    {
        $manques = [];
        foreach ($lot->getVersionProduit()->getNomenclatures() as $ligne) {
            $reference = $ligne->getReferenceSousEnsemble()->getReference();
            $nb = $this->compter($snScannes, $reference);
            if ($nb < $ligne->getQuantite()) {
                $manques[] = sprintf('%s : %d scanné(s) sur %d', $reference, $nb, $ligne->getQuantite());
            }
        }

        return $manques;
    }

        /**
     * Contrôle complet d'une intégration soumise (validation finale côté serveur).
     *
     * @param string[] $snSousEnsembles
     */
    public function validerIntegration(Lot $lot, string $snSysteme, array $snSousEnsembles): ResultatIntegration
    {
        $erreurs = [];
        $versions = [];

        // 1) Le système
        $snSysteme = trim($snSysteme);
        if ('' === $snSysteme) {
            $erreurs[] = 'Système : le SN est obligatoire.';
        } else {
            $resultat = $this->validerSysteme($snSysteme, $lot);
            if (!$resultat->estAccepte()) {
                $erreurs[] = sprintf('Système : %s', $resultat->erreur);
            }
        }

        // 2) Les sous-ensembles, dans l'ordre : chacun connaît ceux qui le précèdent
        $acceptes = [];
        foreach ($snSousEnsembles as $sn) {
            $sn = trim($sn);
            if ('' === $sn) {
                continue; // champ non rempli : signalé par lignesIncompletes() plus bas
            }

            $resultat = $this->validerSousEnsemble($sn, $lot, $acceptes);
            if ($resultat->estAccepte()) {
                $acceptes[] = $sn;
                $versions[$sn] = $resultat->version;
            } else {
                $erreurs[] = sprintf('%s : %s', $sn, $resultat->erreur);
            }
        }

        // 3) Tout ce que la nomenclature attend est-il présent ?
        foreach ($this->lignesIncompletes($lot, $acceptes) as $manque) {
            $erreurs[] = 'Incomplet — ' . $manque;
        }

        return new ResultatIntegration($erreurs, $versions);
    }

    /** @return array{0: string, 1: string}|null [référence, lettre], ou null si le format est invalide */
    private function analyser(string $sn): ?array
    {
        if (!preg_match(self::FORMAT_SN_SOUS_ENSEMBLE, $sn, $m)) {
            return null;
        }

        return [$m[1], $m[2]];
    }

    private function trouverLigne(Lot $lot, string $reference): ?Nomenclature
    {
        foreach ($lot->getVersionProduit()->getNomenclatures() as $ligne) {
            if ($ligne->getReferenceSousEnsemble()->getReference() === $reference) {
                return $ligne;
            }
        }

        return null;
    }

    /** @param string[] $snScannes */
    private function compter(array $snScannes, string $reference): int
    {
        $nb = 0;
        foreach ($snScannes as $sn) {
            $analyse = $this->analyser($sn);
            if (null !== $analyse && $analyse[0] === $reference) {
                ++$nb;
            }
        }

        return $nb;
    }
}