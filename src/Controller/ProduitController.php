<?php

namespace App\Controller;

use App\Entity\ReferenceProduit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_OPERATEUR')]
final class ProduitController extends AbstractController
{
    // Symfony charge le produit à partir de {id}, et renvoie une 404 s'il n'existe pas
    #[Route('/produits/{id}', name: 'app_produit', requirements: ['id' => '\d+'])]
    public function show(ReferenceProduit $produit): Response
    {
        return $this->render('produit/show.html.twig', ['produit' => $produit]);
    }
}