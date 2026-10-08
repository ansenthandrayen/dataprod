<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

// Pas de data_class : le formulaire renvoie un simple tableau ['numero' => ...]
class NumeroLotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('numero', TextType::class, [
            'label' => 'Numéro de lot',
            'attr' => ['autofocus' => true, 'autocomplete' => 'off'], // prêt pour le scanner
            'constraints' => [new NotBlank(message: 'Scannez ou saisissez un numéro de lot.')],
        ]);
    }
}