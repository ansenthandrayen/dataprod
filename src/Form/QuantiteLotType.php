<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class QuantiteLotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('quantitePrevue', IntegerType::class, [
            'label' => 'Quantité prévue',
            'attr' => ['autofocus' => true, 'min' => 1],
            'constraints' => [
                new NotBlank(message: 'La quantité prévue est obligatoire.'),
                new Positive(message: 'La quantité doit être un nombre entier supérieur à 0.'),
            ],
        ]);
    }
}