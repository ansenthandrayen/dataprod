<?php

namespace App\Controller;

use App\Entity\Lot;
use App\Form\NumeroLotType;
use App\Form\QuantiteLotType;
use App\Service\LotResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Tous les rôles passent (Qualité et Admin héritent de ROLE_OPERATEUR)
#[IsGranted('ROLE_OPERATEUR')]
final class IntegrationController extends AbstractController
{
    // Étape 1 : saisir ou scanner le numéro de lot
    #[Route('/integration', name: 'app_integration')]
    public function lot(Request $request, LotResolver $resolver): Response
    {
        $form = $this->createForm(NumeroLotType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $numero = trim((string) $form->get('numero')->getData());
            $resultat = $resolver->resoudre($numero);

            if ($resultat->existe()) {
                return $this->redirectToRoute('app_integration_scan', ['id' => $resultat->lot->getId()]);
            }
            if ($resultat->doitEtreCree()) {
                return $this->redirectToRoute('app_integration_nouveau_lot', ['numero' => $numero]);
            }

            // Numéro refusé : l'erreur s'affiche sous le champ
            $form->get('numero')->addError(new FormError($resultat->erreur));
        }

        return $this->render('integration/lot.html.twig', ['form' => $form]);
    }

    // Étape 2 : le lot n'existe pas encore, on le crée en demandant la quantité prévue
    #[Route('/integration/nouveau-lot', name: 'app_integration_nouveau_lot')]
    public function nouveauLot(Request $request, LotResolver $resolver, EntityManagerInterface $em): Response
    {
        // Le numéro vient de l'URL : on ne lui fait pas confiance, on le re-contrôle
        $numero = trim((string) $request->query->get('numero'));
        $resultat = $resolver->resoudre($numero);

        if ($resultat->existe()) {
            return $this->redirectToRoute('app_integration_scan', ['id' => $resultat->lot->getId()]);
        }
        if (!$resultat->doitEtreCree()) {
            $this->addFlash('error', $resultat->erreur ?? 'Numéro de lot invalide.');

            return $this->redirectToRoute('app_integration');
        }

        $form = $this->createForm(QuantiteLotType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lot = (new Lot())
                ->setNumero($numero)
                ->setQuantitePrevue($form->get('quantitePrevue')->getData())
                ->setVersionProduit($resultat->version);
            $em->persist($lot);
            $em->flush();

            $this->addFlash('success', sprintf('Lot %s créé.', $numero));

            return $this->redirectToRoute('app_integration_scan', ['id' => $lot->getId()]);
        }

        return $this->render('integration/nouveau_lot.html.twig', [
            'form' => $form,
            'numero' => $numero,
            'version' => $resultat->version,
        ]);
    }

    // Étape 3 (squelette, à compléter) : Symfony charge le Lot à partir de {id}, 404 s'il n'existe pas
    #[Route('/integration/lot/{id}', name: 'app_integration_scan', requirements: ['id' => '\d+'])]
    public function scan(Lot $lot): Response
    {
        return $this->render('integration/scan.html.twig', ['lot' => $lot]);
    }
}