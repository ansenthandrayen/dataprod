<?php

namespace App\DataFixtures;

use App\Entity\Lot;
use App\Entity\Nomenclature;
use App\Entity\ReferenceProduit;
use App\Entity\ReferenceSousEnsemble;
use App\Entity\SousEnsemble;
use App\Entity\Systeme;
use App\Entity\VersionProduit;
use App\Entity\VersionSousEnsemble;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductionFixtures extends Fixture implements DependentFixtureInterface
{
    // Catalogue des sous-ensembles : référence => description + versions existantes
    private const SOUS_ENSEMBLES = [
        'ORVEX-USB'     => ['description' => 'Carte port USB',           'versions' => ['A', 'B', 'C']],
        'ORVEX-ALIM'    => ['description' => "Bloc d'alimentation",      'versions' => ['A', 'B']],
        'ORVEX-LCD55'   => ['description' => 'Dalle LCD 55 pouces',      'versions' => ['A', 'B']],
        'ORVEX-CARTEPC' => ['description' => 'Carte mère PC de bureau', 'versions' => ['A', 'B']],
        'ORVEX-RAM'     => ['description' => 'Barrette mémoire',         'versions' => ['A', 'B']],
        'ORVEX-SSD'     => ['description' => 'Disque SSD',               'versions' => ['A', 'B']],
        'ORVEX-OLED'    => ['description' => 'Écran OLED 6,5 pouces',    'versions' => ['A', 'B']],
        'ORVEX-BATT'    => ['description' => 'Batterie lithium',         'versions' => ['A', 'B']],
        'ORVEX-CAM'     => ['description' => 'Module caméra',            'versions' => ['A', 'B']],
        'ORVEX-TETE'    => ['description' => "Tête d'impression",        'versions' => ['A', 'B']],
        'ORVEX-BAC'     => ['description' => 'Bac papier',               'versions' => ['A', 'B']],
    ];

    // Produits : référence => description + versions.
    // Nomenclature de chaque version : sous-ensemble => [quantité attendue, versions acceptées]
    private const PRODUITS = [
        'ORVEX102-TV' => [
            'description' => 'Orvex TV LED curvé 55 pouces 2026',
            'versions' => [
                'A' => ['ORVEX-LCD55' => [1, ['A']], 'ORVEX-USB' => [2, ['A', 'B']], 'ORVEX-ALIM' => [1, ['A', 'B']]],
                'B' => ['ORVEX-LCD55' => [1, ['A', 'B']], 'ORVEX-USB' => [3, ['B']], 'ORVEX-ALIM' => [1, ['B']]],
                'C' => ['ORVEX-LCD55' => [1, ['B']], 'ORVEX-USB' => [4, ['B', 'C']], 'ORVEX-ALIM' => [1, ['B']]],
            ],
        ],
        'ORVEX210-PC' => [
            'description' => 'Orvex PC de bureau compact 2026',
            'versions' => [
                'A' => ['ORVEX-CARTEPC' => [1, ['A']], 'ORVEX-RAM' => [2, ['A', 'B']], 'ORVEX-SSD' => [1, ['A']], 'ORVEX-ALIM' => [1, ['A', 'B']]],
                'B' => ['ORVEX-CARTEPC' => [1, ['A', 'B']], 'ORVEX-RAM' => [4, ['B']], 'ORVEX-SSD' => [1, ['A', 'B']], 'ORVEX-ALIM' => [1, ['B']]],
                'C' => ['ORVEX-CARTEPC' => [1, ['B']], 'ORVEX-RAM' => [4, ['B']], 'ORVEX-SSD' => [2, ['B']], 'ORVEX-ALIM' => [1, ['B']]],
            ],
        ],
        'ORVEX330-PHONE' => [
            'description' => 'Orvex smartphone 5G 2026',
            'versions' => [
                'A' => ['ORVEX-OLED' => [1, ['A']], 'ORVEX-BATT' => [1, ['A']], 'ORVEX-CAM' => [2, ['A']]],
                'B' => ['ORVEX-OLED' => [1, ['A', 'B']], 'ORVEX-BATT' => [1, ['A', 'B']], 'ORVEX-CAM' => [3, ['B']]],
                'C' => ['ORVEX-OLED' => [1, ['B']], 'ORVEX-BATT' => [1, ['B']], 'ORVEX-CAM' => [3, ['B']], 'ORVEX-USB' => [1, ['C']]],
            ],
        ],
        'ORVEX440-PRINT' => [
            'description' => 'Orvex imprimante laser multifonction 2026',
            'versions' => [
                'A' => ['ORVEX-TETE' => [1, ['A']], 'ORVEX-BAC' => [1, ['A']], 'ORVEX-ALIM' => [1, ['A']]],
                'B' => ['ORVEX-TETE' => [1, ['A', 'B']], 'ORVEX-BAC' => [2, ['B']], 'ORVEX-ALIM' => [1, ['A', 'B']]],
                'C' => ['ORVEX-TETE' => [2, ['B']], 'ORVEX-BAC' => [2, ['B']], 'ORVEX-ALIM' => [1, ['B']], 'ORVEX-USB' => [1, ['C']]],
            ],
        ],
    ];

    // Lots : 'code' = semaine + année (3526 = semaine 35 de 2026)
    // 'integres' = nombre de systèmes déjà scannés dans ce lot
    private const LOTS = [
        ['reference' => 'ORVEX102-TV',    'version' => 'A', 'code' => '3526', 'prevus' => 10, 'integres' => 3],
        ['reference' => 'ORVEX102-TV',    'version' => 'B', 'code' => '3626', 'prevus' => 10, 'integres' => 0],
        ['reference' => 'ORVEX210-PC',    'version' => 'A', 'code' => '3526', 'prevus' => 10, 'integres' => 2],
        ['reference' => 'ORVEX330-PHONE', 'version' => 'B', 'code' => '3526', 'prevus' => 20, 'integres' => 2],
        ['reference' => 'ORVEX440-PRINT', 'version' => 'C', 'code' => '3626', 'prevus' => 8,  'integres' => 1],
    ];

    public function getDependencies(): array
    {
        // UserFixtures doit être chargée avant celle-ci
        return [UserFixtures::class];
    }
       
    public function load(ObjectManager $manager): void
    {
        
        $operateurs = [
            $this->getReference('user-operateur1', User::class),
            $this->getReference('user-operateur2', User::class),
        ];

        // 1) Catalogue des sous-ensembles et de leurs versions
        $refsSE = [];      // "REFERENCE" => objet ReferenceSousEnsemble
        $versionsSE = [];  // "REFERENCE|LETTRE" => objet VersionSousEnsemble
        foreach (self::SOUS_ENSEMBLES as $reference => $donnees) {
            $refSE = (new ReferenceSousEnsemble())
                ->setReference($reference)
                ->setDescription($donnees['description']);
            $manager->persist($refSE);
            $refsSE[$reference] = $refSE;

            foreach ($donnees['versions'] as $lettreSE) {
                $versionSE = (new VersionSousEnsemble())
                    ->setLettre($lettreSE)
                    ->setReferenceSousEnsemble($refSE);
                $manager->persist($versionSE);
                $versionsSE[$reference . '|' . $lettreSE] = $versionSE;
            }
        }

        // 2) Produits, versions de produit et nomenclatures
        $versionsProduit = []; // "REFERENCE|LETTRE" => objet VersionProduit
        foreach (self::PRODUITS as $reference => $produit) {
            $refProduit = (new ReferenceProduit())
                ->setReference($reference)
                ->setDescription($produit['description']);
            $manager->persist($refProduit);

            foreach ($produit['versions'] as $lettre => $composition) {
                $version = (new VersionProduit())
                    ->setLettre($lettre)
                    ->setReferenceProduit($refProduit);
                $manager->persist($version);
                $versionsProduit[$reference . '|' . $lettre] = $version;

                foreach ($composition as $refSousEnsemble => [$quantite, $acceptees]) {
                    $nomenclature = (new Nomenclature())
                        ->setQuantite($quantite)
                        ->setVersionProduit($version)
                        ->setReferenceSousEnsemble($refsSE[$refSousEnsemble]);

                    // Versions de sous-ensemble acceptées sur cette ligne
                    foreach ($acceptees as $lettreAcceptee) {
                        $nomenclature->addVersionsAcceptee($versionsSE[$refSousEnsemble . '|' . $lettreAcceptee]);
                    }
                    $manager->persist($nomenclature);
                }
            }
        }

        // 3) Lots, systèmes et sous-ensembles scannés
        $compteurs = []; // un compteur par (référence, version, code) pour garder des SN uniques
        foreach (self::LOTS as $donnees) {
            $reference = $donnees['reference'];
            $lettre = $donnees['version'];
            $numeroLot = sprintf('%s %s%s', $reference, $lettre, $donnees['code']); // ex : ORVEX102-TV A3526

            $lot = (new Lot())
                ->setNumero($numeroLot)
                ->setQuantitePrevue($donnees['prevus'])
                ->setVersionProduit($versionsProduit[$reference . '|' . $lettre]);
            $manager->persist($lot);

            // La nomenclature de la version dit quoi scanner pour chaque système
            $composition = self::PRODUITS[$reference]['versions'][$lettre];
            
            // 3526 => semaine 35 de 2026, le lundi à 8 h
            $semaine = (int) substr($donnees['code'], 0, 2);
            $annee = 2000 + (int) substr($donnees['code'], 2, 2);
            $dateDebut = (new \DateTimeImmutable())->setISODate($annee, $semaine, 1)->setTime(8, 0);
            
            for ($i = 1; $i <= $donnees['integres']; $i++) {
                $systeme = (new Systeme())
                    ->setNumeroSerie(sprintf('%s%04d', $numeroLot, $i)) // ex : ORVEX102-TV A35260001
                    ->setLot($lot)
                    ->setIntegreLe($dateDebut->modify(sprintf('+%d minutes', $i * 7)))
                    ->setIntegrePar($operateurs[($i - 1) % 2]); // alterne opérateur 1 et 2
                $manager->persist($systeme);

                foreach ($composition as $refSousEnsemble => [$quantite, $acceptees]) {
                    for ($q = 0; $q < $quantite; $q++) {
                        // On alterne parmi les versions acceptées pour obtenir des mélanges
                        $lettreSE = $acceptees[($i + $q) % count($acceptees)];

                        $cle = $refSousEnsemble . '|' . $lettreSE . '|' . $donnees['code'];
                        $compteurs[$cle] = ($compteurs[$cle] ?? 100) + 1;

                        $sousEnsemble = (new SousEnsemble())
                            // ex : ORVEX-USB A35260101
                            ->setNumeroSerie(sprintf('%s %s%s%04d', $refSousEnsemble, $lettreSE, $donnees['code'], $compteurs[$cle]))
                            ->setSysteme($systeme)
                            ->setVersionSousEnsemble($versionsSE[$refSousEnsemble . '|' . $lettreSE]);
                        $manager->persist($sousEnsemble);
                    }
                }
            }
        }

        // Un seul flush : tout est enregistré dans une seule transaction
        $manager->flush();
    }
}