<?php

namespace App\Controller;

use App\Entity\Lot;
use App\Entity\ReferenceProduit;
use App\Entity\SousEnsemble;
use App\Entity\Systeme;
use App\Form\IntegrationType;
use App\Form\NumeroLotType;
use App\Form\QuantiteLotType;
use App\Service\LotResolver;
use App\Service\ResultatScan;
use App\Service\ScanValidator;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Tous les rôles passent (Qualité et Admin héritent de ROLE_OPERATEUR)
#[IsGranted('ROLE_OPERATEUR')]
// Préfixe commun à toutes les routes de cette classe : {id} est celui du PRODUIT
#[Route('/produits/{id}/integration', requirements: ['id' => '\d+'])]
final class IntegrationController extends AbstractController
{
    // Étape 1 : saisir ou scanner le numéro de lot (le lot doit appartenir au produit courant)
    #[Route('', name: 'app_integration')]
    public function lot(ReferenceProduit $produit, Request $request, LotResolver $resolver): Response
    {
        $form = $this->createForm(NumeroLotType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $numero = trim((string) $form->get('numero')->getData());
            $resultat = $resolver->resoudre($numero, $produit);

            if ($resultat->existe()) {
                return $this->redirectToRoute('app_integration_scan', [
                    'id' => $produit->getId(),
                    'lotId' => $resultat->lot->getId(),
                ]);
            }
            if ($resultat->doitEtreCree()) {
                return $this->redirectToRoute('app_integration_nouveau_lot', [
                    'id' => $produit->getId(),
                    'numero' => $numero,
                ]);
            }

            // Numéro refusé : l'erreur s'affiche sous le champ
            $form->get('numero')->addError(new FormError($resultat->erreur));
        }

        return $this->render('integration/lot.html.twig', ['produit' => $produit, 'form' => $form]);
    }

    // Étape 2 : le lot n'existe pas encore, on le crée en demandant la quantité prévue
    #[Route('/nouveau-lot', name: 'app_integration_nouveau_lot')]
    public function nouveauLot(
        ReferenceProduit $produit,
        Request $request,
        LotResolver $resolver,
        EntityManagerInterface $em,
    ): Response {
        // Le numéro vient de l'URL : on ne lui fait pas confiance, on le re-contrôle (produit compris)
        $numero = trim((string) $request->query->get('numero'));
        $resultat = $resolver->resoudre($numero, $produit);

        if ($resultat->existe()) {
            return $this->redirectToRoute('app_integration_scan', [
                'id' => $produit->getId(),
                'lotId' => $resultat->lot->getId(),
            ]);
        }
        if (!$resultat->doitEtreCree()) {
            $this->addFlash('error', $resultat->erreur ?? 'Numéro de lot invalide.');

            return $this->redirectToRoute('app_integration', ['id' => $produit->getId()]);
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

            return $this->redirectToRoute('app_integration_scan', [
                'id' => $produit->getId(),
                'lotId' => $lot->getId(),
            ]);
        }

        return $this->render('integration/nouveau_lot.html.twig', [
            'produit' => $produit,
            'form' => $form,
            'numero' => $numero,
            'version' => $resultat->version,
        ]);
    }

    // Étape 3 : scan d'un système et de ses sous-ensembles
    #[Route('/lot/{lotId}', name: 'app_integration_scan', requirements: ['lotId' => '\d+'])]
    public function scan(
        #[MapEntity(id: 'id')] ReferenceProduit $produit,
        #[MapEntity(id: 'lotId')] Lot $lot,
        Request $request,
        ScanValidator $validator,
        EntityManagerInterface $em,
    ): Response {
        $this->verifierAppartenance($lot, $produit);

        $form = $this->createForm(IntegrationType::class, null, ['lot' => $lot]);
        $form->handleRequest($request);
        $erreurs = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $donnees = $form->getData();
            $snSysteme = (string) ($donnees['snSysteme'] ?? '');

            $snSousEnsembles = [];
            foreach ($donnees as $nom => $valeur) {
                if (str_starts_with($nom, IntegrationType::PREFIXE_SOUS_ENSEMBLE)) {
                    $snSousEnsembles[] = (string) $valeur;
                }
            }

            // Contrôle complet côté serveur : la vraie protection
            $resultat = $validator->validerIntegration($lot, $snSysteme, $snSousEnsembles);

            if ($resultat->estValide()) {
                $systeme = (new Systeme())
                    ->setNumeroSerie(trim($snSysteme))
                    ->setLot($lot)
                    ->setIntegreLe(new \DateTimeImmutable())
                    ->setIntegrePar($this->getUser()); // l'opérateur connecté
                $em->persist($systeme);

                foreach ($resultat->versions as $sn => $version) {
                    $em->persist(
                        (new SousEnsemble())
                            ->setNumeroSerie($sn)
                            ->setSysteme($systeme)
                            ->setVersionSousEnsemble($version)
                    );
                }

                try {
                    $em->flush(); // une seule transaction : tout ou rien
                } catch (UniqueConstraintViolationException) {
                    // Un autre poste vient d'enregistrer l'un de ces SN. L'EntityManager est fermé : on redirige.
                    $this->addFlash('error', "Un de ces numéros de série vient d'être enregistré par un autre poste. Rescannez.");

                    return $this->redirectToRoute('app_integration_scan', [
                        'id' => $produit->getId(),
                        'lotId' => $lot->getId(),
                    ]);
                }

                $this->addFlash('success', sprintf('Système %s intégré.', $systeme->getNumeroSerie()));

                // Redirection : un F5 ne renvoie pas le formulaire, et le suivant arrive vide
                return $this->redirectToRoute('app_integration_scan', [
                    'id' => $produit->getId(),
                    'lotId' => $lot->getId(),
                ]);
            }

            $erreurs = $resultat->erreurs;
        }

        // Turbo n'affiche la réponse à un POST que si c'est une redirection (succès)
        // ou un statut 422 (refus). Un 200 est ignoré : on le force quand il y a des erreurs.
        $reponse = new Response(null, [] === $erreurs ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);

        return $this->render('integration/scan.html.twig', [
            'produit' => $produit,
            'lot' => $lot,
            'form' => $form,
            'erreurs' => $erreurs,
        ], $reponse);
    }

    // Contrôle d'UN seul scan, appelé par le JavaScript à chaque saisie. Lecture seule : rien n'est enregistré.
    #[Route('/lot/{lotId}/controle', name: 'app_integration_controle', requirements: ['lotId' => '\d+'], methods: ['GET'])]
    public function controle(
        #[MapEntity(id: 'id')] ReferenceProduit $produit,
        #[MapEntity(id: 'lotId')] Lot $lot,
        Request $request,
        ScanValidator $validator,
    ): JsonResponse {
        $this->verifierAppartenance($lot, $produit);

        $type = $request->query->getString('type');
        $sn = trim($request->query->getString('sn'));
        // SN déjà acceptés dans les autres champs du formulaire (?deja[]=...&deja[]=...)
        $deja = array_values(array_filter($request->query->all('deja'), 'is_string'));

        if ('' === $sn) {
            return $this->json(['ok' => false, 'erreur' => 'SN vide.']);
        }

        $resultat = match ($type) {
            'systeme' => $validator->validerSysteme($sn, $lot),
            'sous_ensemble' => $validator->validerSousEnsemble($sn, $lot, $deja),
            default => ResultatScan::refuse('Type de contrôle inconnu.'),
        };

        return $this->json(['ok' => $resultat->estAccepte(), 'erreur' => $resultat->erreur]);
    }

    // Un lot ne s'ouvre que depuis la page de son propre produit (adresse tapée à la main : 404).
    // Ici les entités viennent de la base : elles ont un id, on peut les comparer.
    private function verifierAppartenance(Lot $lot, ReferenceProduit $produit): void
    {
        if ($lot->getVersionProduit()->getReferenceProduit()->getId() !== $produit->getId()) {
            throw $this->createNotFoundException();
        }
    }
}