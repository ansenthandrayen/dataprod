<?php

namespace App\Tests\Service;

use App\Entity\Lot;
use App\Entity\Nomenclature;
use App\Entity\ReferenceSousEnsemble;
use App\Entity\Systeme;
use App\Entity\SousEnsemble;
use App\Entity\VersionProduit;
use App\Entity\VersionSousEnsemble;
use App\Repository\SousEnsembleRepository;
use App\Repository\SystemeRepository;
use App\Service\ScanValidator;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class ScanValidatorTest extends TestCase
{
    private Stub&SystemeRepository $systemes;
    private Stub&SousEnsembleRepository $sousEnsembles;
    private ScanValidator $validator;
    private Lot $lot;

    // Exécutée avant CHAQUE test : on repart d'un état neuf
    protected function setUp(): void
    {
        // Faux repositories : par défaut, "rien n'existe en base" (findOneBy renvoie null)
        $this->systemes = $this->createStub(SystemeRepository::class);
        $this->sousEnsembles = $this->createStub(SousEnsembleRepository::class);
        $this->validator = new ScanValidator($this->systemes, $this->sousEnsembles);

        // Catalogue en mémoire : une TV version A attend
        //   - 2 USB, de version A ou B
        //   - 1 dalle LCD, de version A uniquement
        $usb = (new ReferenceSousEnsemble())->setReference('ORVEX-USB')->setDescription('Carte port USB');
        $lcd = (new ReferenceSousEnsemble())->setReference('ORVEX-LCD55')->setDescription('Dalle LCD');
        $usbA = (new VersionSousEnsemble())->setLettre('A')->setReferenceSousEnsemble($usb);
        $usbB = (new VersionSousEnsemble())->setLettre('B')->setReferenceSousEnsemble($usb);
        $lcdA = (new VersionSousEnsemble())->setLettre('A')->setReferenceSousEnsemble($lcd);

        $version = (new VersionProduit())->setLettre('A');
        $version->addNomenclature(
            (new Nomenclature())->setQuantite(2)->setReferenceSousEnsemble($usb)
                ->addVersionsAcceptee($usbA)->addVersionsAcceptee($usbB)
        );
        $version->addNomenclature(
            (new Nomenclature())->setQuantite(1)->setReferenceSousEnsemble($lcd)
                ->addVersionsAcceptee($lcdA)
        );

        $this->lot = (new Lot())
            ->setNumero('ORVEX102-TV A3526')
            ->setQuantitePrevue(10)
            ->setVersionProduit($version);
    }

    // ---------- SN du système ----------

    public function testSystemeDuBonLotEstAccepte(): void
    {
        $resultat = $this->validator->validerSysteme('ORVEX102-TV A35260001', $this->lot);

        $this->assertTrue($resultat->estAccepte());
    }

    public function testSystemeDunAutreLotEstRefuse(): void
    {
        // Même produit, mais version B et autre semaine
        $resultat = $this->validator->validerSysteme('ORVEX102-TV B36260001', $this->lot);

        $this->assertFalse($resultat->estAccepte());
        $this->assertStringContainsString('ne correspond pas au lot', $resultat->erreur);
    }

    public function testSystemeDejaEnregistreEstRefuse(): void
    {
        // On simule "ce SN existe déjà en base"
        $this->systemes->method('findOneBy')->willReturn(new Systeme());

        $resultat = $this->validator->validerSysteme('ORVEX102-TV A35260001', $this->lot);

        $this->assertFalse($resultat->estAccepte());
        $this->assertStringContainsString('déjà enregistré', $resultat->erreur);
    }

    // ---------- SN d'un sous-ensemble ----------

    public function testSousEnsembleDeVersionAccepteeEstAccepte(): void
    {
        // La nomenclature accepte les USB en version A ET B
        $resultat = $this->validator->validerSousEnsemble('ORVEX-USB B35260101', $this->lot, []);

        $this->assertTrue($resultat->estAccepte());
        $this->assertSame('B', $resultat->version->getLettre());
    }

    public function testSousEnsembleDeVersionNonAccepteeEstRefuse(): void
    {
        // Seules les versions A et B de l'USB sont acceptées : la C est refusée
        $resultat = $this->validator->validerSousEnsemble('ORVEX-USB C35260101', $this->lot, []);

        $this->assertFalse($resultat->estAccepte());
        $this->assertStringContainsString('Version C', $resultat->erreur);
    }

    public function testSousEnsembleInconnuDeLaNomenclatureEstRefuse(): void
    {
        $resultat = $this->validator->validerSousEnsemble('ORVEX-CAM A35260101', $this->lot, []);

        $this->assertFalse($resultat->estAccepte());
        $this->assertStringContainsString("n'est pas attendu", $resultat->erreur);
    }

    public function testFormatInvalideEstRefuse(): void
    {
        $resultat = $this->validator->validerSousEnsemble('N IMPORTE QUOI', $this->lot, []);

        $this->assertFalse($resultat->estAccepte());
        $this->assertStringContainsString('Format', $resultat->erreur);
    }

    public function testDoublonDansLeFormulaireEstRefuse(): void
    {
        $resultat = $this->validator->validerSousEnsemble(
            'ORVEX-USB A35260101',
            $this->lot,
            ['ORVEX-USB A35260101']
        );

        $this->assertFalse($resultat->estAccepte());
        $this->assertStringContainsString('déjà été scanné', $resultat->erreur);
    }

    public function testSousEnsembleDejaEnBaseEstRefuse(): void
    {
        $this->sousEnsembles->method('findOneBy')->willReturn(new SousEnsemble());

        $resultat = $this->validator->validerSousEnsemble('ORVEX-USB A35260101', $this->lot, []);

        $this->assertFalse($resultat->estAccepte());
        $this->assertStringContainsString('déjà enregistré', $resultat->erreur);
    }

    public function testQuantiteAttendueNePeutPasEtreDepassee(): void
    {
        // 2 USB sont attendus et 2 sont déjà scannés : un troisième est refusé
        $resultat = $this->validator->validerSousEnsemble(
            'ORVEX-USB A35260103',
            $this->lot,
            ['ORVEX-USB A35260101', 'ORVEX-USB B35260102']
        );

        $this->assertFalse($resultat->estAccepte());
        $this->assertStringContainsString('déjà scannés', $resultat->erreur);
    }

    // ---------- Validation finale ----------

    public function testIlManqueDesSousEnsemblesTantQueToutNestPasScanne(): void
    {
        // 1 USB scanné sur 2, et aucune dalle LCD
        $manques = $this->validator->lignesIncompletes($this->lot, ['ORVEX-USB A35260101']);

        $this->assertCount(2, $manques);
    }

    public function testRienNeManqueQuandToutEstScanne(): void
    {
        $manques = $this->validator->lignesIncompletes($this->lot, [
            'ORVEX-USB A35260101',
            'ORVEX-USB B35260102',
            'ORVEX-LCD55 A35260201',
        ]);

        $this->assertSame([], $manques);
    }
}