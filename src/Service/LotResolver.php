<?php

namespace App\Service;

use App\Entity\ReferenceProduit;
use App\Repository\LotRepository;
use App\Repository\ReferenceProduitRepository;

/**
 * Détermine ce qu'on peut faire d'un numéro de lot saisi ou scanné.
 */
class LotResolver
{
    // "ORVEX102-TV A3526" = référence + espace + lettre de version + semaine (01 à 53) + année (2 chiffres)
    private const FORMAT_LOT = '/^(\S+) ([A-Z])(0[1-9]|[1-4]\d|5[0-3])(\d{2})$/';

    public function __construct(
        private LotRepository $lots,
        private ReferenceProduitRepository $references,
    ) {
    }

    /**
     * @param ReferenceProduit|null $produit si renseigné, le lot doit appartenir à ce produit
     */
    public function resoudre(string $numero, ?ReferenceProduit $produit = null): ResultatLot
    {
        // Un scanner ajoute souvent un retour à la ligne : on nettoie
        $numero = trim($numero);

        // 1) Le lot existe déjà
        $lot = $this->lots->findOneBy(['numero' => $numero]);
        if (null !== $lot) {
            if (null !== $produit) {
                $refus = $this->controlerProduit($lot->getVersionProduit()->getReferenceProduit(), $produit);
                if (null !== $refus) {
                    return $refus;
                }
            }

            return ResultatLot::existant($lot);
        }

        // 2) Sinon, le numéro doit avoir le bon format pour pouvoir créer le lot
        if (!preg_match(self::FORMAT_LOT, $numero, $m)) {
            return ResultatLot::refuse(
                'Numéro de lot non reconnu (attendu : référence, espace, lettre de version, semaine et année — ex : ORVEX102-TV A3526).'
            );
        }
        [, $reference, $lettre] = $m;

        // 3) La référence et la version doivent exister au catalogue
        $referenceProduit = $this->references->findOneBy(['reference' => $reference]);
        if (null === $referenceProduit) {
            return ResultatLot::refuse(sprintf('Référence produit inconnue : %s.', $reference));
        }

        // 4) Et correspondre au produit courant, s'il y en a un
        $refus = $this->controlerProduit($referenceProduit, $produit);
        if (null !== $refus) {
            return $refus;
        }

        foreach ($referenceProduit->getVersions() as $version) {
            if ($version->getLettre() === $lettre) {
                return ResultatLot::aCreer($version);
            }
        }

        return ResultatLot::refuse(sprintf("La version %s n'existe pas pour %s.", $lettre, $reference));
    }

    /** Renvoie un refus si le produit trouvé n'est pas celui attendu, null sinon. */
    private function controlerProduit(ReferenceProduit $trouve, ?ReferenceProduit $attendu): ?ResultatLot
    {
        if (null === $attendu || $trouve->getReference() === $attendu->getReference()) {
            return null;
        }

        return ResultatLot::refuse(sprintf(
            'Ce numéro de lot correspond au produit %s, pas à %s.',
            $trouve->getReference(),
            $attendu->getReference()
        ));
    }
}