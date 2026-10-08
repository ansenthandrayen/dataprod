<?php

namespace App\Controller;

use App\Repository\ReferenceProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    // Page d'accueil après connexion : choisir ou rechercher un produit
    #[Route('/home', name: 'app_home')]
    public function index(Request $request, ReferenceProduitRepository $produits): Response
    {
        $q = trim($request->query->getString('q'));

        return $this->render('home/index.html.twig', [
            'q' => $q,
            'produits' => $produits->rechercher($q),
        ]);
    }
}