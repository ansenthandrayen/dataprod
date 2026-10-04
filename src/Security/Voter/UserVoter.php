<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserVoter extends Voter
{
    // Action : créer un compte. Le "sujet" est le User en cours de création.
    public const CREATE = 'USER_CREATE';

    // Liste blanche : rôles qu'un créateur a le droit d'attribuer
    private const ASSIGNABLE_BY_ADMIN = ['ROLE_ADMIN', 'ROLE_QUALITE', 'ROLE_OPERATEUR'];
    private const ASSIGNABLE_BY_QUALITE = ['ROLE_QUALITE', 'ROLE_OPERATEUR'];

    // Injection de dépendances : sert à vérifier un rôle EN TENANT COMPTE de la hiérarchie
    public function __construct(private AccessDecisionManagerInterface $accessDecisionManager)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Ce Voter ne s'occupe que de USER_CREATE sur un objet User.
        // Pour tout le reste, il s'abstient.
        return self::CREATE === $attribute && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        // L'utilisateur connecté (celui qui veut créer le compte)
        $currentUser = $token->getUser();

        if (!$currentUser instanceof UserInterface) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }

        // On teste ADMIN en premier : grâce à la hiérarchie, un admin a aussi ROLE_QUALITE
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            $allowedRoles = self::ASSIGNABLE_BY_ADMIN;
        } elseif ($this->accessDecisionManager->decide($token, ['ROLE_QUALITE'])) {
            $allowedRoles = self::ASSIGNABLE_BY_QUALITE;
        } else {
            $vote?->addReason('Seuls les rôles Qualité et Admin peuvent créer des comptes.');

            return false;
        }

        // Rôles demandés pour le nouveau compte (ROLE_USER est ajouté automatiquement, on l'ignore)
        $requestedRoles = array_diff($subject->getRoles(), ['ROLE_USER']);

        // Tout rôle demandé qui n'est pas dans la liste blanche du créateur est refusé
        if ([] !== array_diff($requestedRoles, $allowedRoles)) {
            $vote?->addReason('Ce rôle ne peut pas être attribué par votre niveau de permission.');

            return false;
        }

        return true;
    }
}