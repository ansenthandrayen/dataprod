<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            // Mot de passe EN CLAIR : mapped=false => non copié dans l'entité.
            // Le Controller le hashera puis appellera setPassword() avec le hash.
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(message: 'Le mot de passe est obligatoire.'),
                    new Length(min: 8, minMessage: '8 caractères minimum.'),
                ],
            ])
            // L'entité stocke un TABLEAU de rôles, le formulaire propose UN SEUL choix
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => $options['role_choices'],
                'placeholder' => false, // pas de choix vide : le 1er (Opérateur) est présélectionné
                'constraints' => [new NotBlank(message: 'Choisissez un rôle.')],
            ])
        ;

        // Convertisseur tableau <-> valeur unique pour le champ "roles"
        $builder->get('roles')->addModelTransformer(new CallbackTransformer(
            // entité -> formulaire : ['ROLE_QUALITE', 'ROLE_USER'] devient 'ROLE_QUALITE'
            fn (array $roles): ?string => array_values(array_diff($roles, ['ROLE_USER']))[0] ?? null,
            // formulaire -> entité : 'ROLE_QUALITE' devient ['ROLE_QUALITE']
            fn (?string $role): array => $role ? [$role] : [],
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            // Rôles proposés dans la liste : le Controller pourra la restreindre
            'role_choices' => [
                'Opérateur' => 'ROLE_OPERATEUR',
                'Qualité' => 'ROLE_QUALITE',
                'Administrateur' => 'ROLE_ADMIN',
            ],
        ]);
        $resolver->setAllowedTypes('role_choices', 'array');
    }
}