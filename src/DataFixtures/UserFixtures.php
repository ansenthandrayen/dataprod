<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    // Injection de dépendances : Symfony fournit automatiquement le service de hashage
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // --- Admin ---
        $admin = new User();
        $admin->setEmail('admin@suivi-production.local');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'password')
        );
        $manager->persist($admin);
        $this->addReference('user-admin', $admin);

        // --- Qualité ---
        $qualite = new User();
        $qualite->setEmail('qualite@suivi-production.local');
        $qualite->setRoles(['ROLE_QUALITE']);
        $qualite->setPassword(
            $this->passwordHasher->hashPassword($qualite, 'password')
        );
        $manager->persist($qualite);
        $this->addReference('user-qualite', $qualite);

        // --- Opérateur 1 ---
        $operateur1 = new User();
        $operateur1->setEmail('operateur1@suivi-production.local');
        $operateur1->setRoles(['ROLE_OPERATEUR']);
        $operateur1->setPassword(
            $this->passwordHasher->hashPassword($operateur1, 'password')
        );
        $manager->persist($operateur1);
        $this->addReference('user-operateur1', $operateur1);

        // --- Opérateur 2 ---
        $operateur2 = new User();
        $operateur2->setEmail('operateur2@suivi-production.local');
        $operateur2->setRoles(['ROLE_OPERATEUR']);
        $operateur2->setPassword(
            $this->passwordHasher->hashPassword($operateur2, 'password')
        );
        $manager->persist($operateur2);
        $this->addReference('user-operateur2', $operateur2);

        $manager->flush();
    }
}