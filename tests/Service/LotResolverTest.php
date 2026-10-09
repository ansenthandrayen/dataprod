<?php

namespace App\Tests\Service;

use App\Entity\Lot;
use App\Entity\ReferenceProduit;
use App\Entity\VersionProduit;
use App\Repository\LotRepository;
use App\Repository\ReferenceProduitRepository;
use App\Service\LotResolver;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class LotResolverTest extends TestCase
{
    private Stub&LotRepository $lots;
    private Stub&ReferenceProduitRepository $references;
    private LotResolver $resolver;
    private ReferenceProduit $referenceTv;

    protected function setUp(): void
    {
        $this->lots = $this->createStub(LotRepository::class);
        $this->references = $this->createStub(ReferenceProduitRepository::class);
        $this->resolver = new LotResolver($this->lots, $this->references);

        // Catalogue en mémoire : la TV existe en versions A et B (pas de C)
        $this->referenceTv = (new ReferenceProduit())->setReference('ORVEX102-TV')->setDescription('TV');
        $this->referenceTv->addVersion((new VersionProduit())->setLettre('A'));
        $this->referenceTv->addVersion((new VersionProduit())->setLettre('B'));

        // Faux repository du catalogue : il ne connaît que ORVEX102-TV
        $this->references->method('findOneBy')->willReturnCallback(
            fn (array $criteres) => 'ORVEX102-TV' === ($criteres['reference'] ?? null) ? $this->referenceTv : null
        );
    }

    public function testLotExistantEstRetrouve(): void
    {
        $lotExistant = (new Lot())->setNumero('ORVEX102-TV A3526');
        $this->lots->method('findOneBy')->willReturn($lotExistant);

        $resultat = $this->resolver->resoudre('ORVEX102-TV A3526');

        $this->assertTrue($resultat->existe());
        $this->assertSame($lotExistant, $resultat->lot);
    }

    public function testLotInconnuMaisValideDoitEtreCree(): void
    {
        $resultat = $this->resolver->resoudre('ORVEX102-TV B3626');

        $this->assertTrue($resultat->doitEtreCree());
        $this->assertSame('B', $resultat->version->getLettre());
    }

    public function testEspacesEtRetourALaLigneSontIgnores(): void
    {
        // Un scanner termine souvent par un retour à la ligne
        $resultat = $this->resolver->resoudre("  ORVEX102-TV A3526\n");

        $this->assertTrue($resultat->doitEtreCree());
    }

    public function testFormatInvalideEstRefuse(): void
    {
        $resultat = $this->resolver->resoudre('N IMPORTE QUOI');

        $this->assertTrue($resultat->estRefuse());
        $this->assertStringContainsString('non reconnu', $resultat->erreur);
    }

    public function testSemaineInexistanteEstRefusee(): void
    {
        // La semaine 54 n'existe pas
        $resultat = $this->resolver->resoudre('ORVEX102-TV A5426');

        $this->assertTrue($resultat->estRefuse());
        $this->assertStringContainsString('non reconnu', $resultat->erreur);
    }

    public function testReferenceInconnueEstRefusee(): void
    {
        $resultat = $this->resolver->resoudre('ORVEX999-XX A3526');

        $this->assertTrue($resultat->estRefuse());
        $this->assertStringContainsString('inconnue', $resultat->erreur);
    }

    public function testVersionInexistanteEstRefusee(): void
    {
        // La TV n'a pas de version C au catalogue
        $resultat = $this->resolver->resoudre('ORVEX102-TV C3526');

        $this->assertTrue($resultat->estRefuse());
        $this->assertStringContainsString('version C', $resultat->erreur);
    }
    
    // ---------- Lot limité au produit courant ----------

    public function testLotExistantDuBonProduitEstAccepte(): void
    {
        $version = $this->referenceTv->getVersions()->first();
        $lot = (new Lot())->setNumero('ORVEX102-TV A3526')->setVersionProduit($version);
        $this->lots->method('findOneBy')->willReturn($lot);

        $resultat = $this->resolver->resoudre('ORVEX102-TV A3526', $this->referenceTv);

        $this->assertTrue($resultat->existe());
    }

    public function testLotExistantDunAutreProduitEstRefuse(): void
    {
        $telephone = (new ReferenceProduit())->setReference('ORVEX330-PHONE')->setDescription('Smartphone');
        $versionTelephone = (new VersionProduit())->setLettre('B');
        $telephone->addVersion($versionTelephone);
        $lot = (new Lot())->setNumero('ORVEX330-PHONE B3526')->setVersionProduit($versionTelephone);
        $this->lots->method('findOneBy')->willReturn($lot);

        // On est sur la page de la TV, mais le lot est celui du smartphone
        $resultat = $this->resolver->resoudre('ORVEX330-PHONE B3526', $this->referenceTv);

        $this->assertTrue($resultat->estRefuse());
        $this->assertStringContainsString('correspond au produit', $resultat->erreur);
    }

    public function testNouveauLotDuBonProduitDoitEtreCree(): void
    {
        $resultat = $this->resolver->resoudre('ORVEX102-TV B3626', $this->referenceTv);

        $this->assertTrue($resultat->doitEtreCree());
    }

    public function testNouveauLotDunAutreProduitEstRefuse(): void
    {
        $telephone = (new ReferenceProduit())->setReference('ORVEX330-PHONE')->setDescription('Smartphone');

        // Le numéro est celui de la TV, mais on est sur la page du smartphone
        $resultat = $this->resolver->resoudre('ORVEX102-TV B3626', $telephone);

        $this->assertTrue($resultat->estRefuse());
        $this->assertStringContainsString('correspond au produit', $resultat->erreur);
    }
}
