<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Security\Voter\UserVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{
    #[Route('/utilisateurs/nouveau', name: 'app_user_new')]
    #[IsGranted('ROLE_QUALITE')] // Barrière d'entrée : un opérateur reçoit une 403. L'admin passe (hiérarchie).
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $newUser = new User();

        // Rôles proposés dans la liste : un Qualité ne voit pas "Administrateur"
        $roleChoices = [
            'Opérateur' => 'ROLE_OPERATEUR',
            'Qualité' => 'ROLE_QUALITE',
        ];
        if ($this->isGranted('ROLE_ADMIN')) {
            $roleChoices['Administrateur'] = 'ROLE_ADMIN';
        }

        $form = $this->createForm(UserFormType::class, $newUser, [
            'role_choices' => $roleChoices,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Règle métier : après la soumission (on a l'objet rempli), avant l'enregistrement
            $this->denyAccessUnlessGranted(UserVoter::CREATE, $newUser);

            // Le mot de passe en clair n'est pas dans l'entité (mapped: false) : on le récupère ici
            $plainPassword = $form->get('plainPassword')->getData();
            $newUser->setPassword($passwordHasher->hashPassword($newUser, $plainPassword));

            $em->persist($newUser);
            $em->flush();

            $this->addFlash('success', sprintf('Le compte %s a été créé.', $newUser->getEmail()));

            // Post/Redirect/Get : évite un doublon si l'utilisateur recharge la page
            return $this->redirectToRoute('app_user_new');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form,
        ]);
    }
}