<?php

namespace App\Form;

use App\Entity\Lot;
use App\Entity\Nomenclature;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire d'intégration d'un système : ses champs dépendent de la nomenclature du lot.
 * Pas de data_class : il renvoie un simple tableau.
 */
class IntegrationType extends AbstractType
{
    // Les champs de sous-ensembles s'appellent se_<id de la ligne>_<numéro>
    public const PREFIXE_SOUS_ENSEMBLE = 'se_';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Lot $lot */
        $lot = $options['lot'];

        $builder->add('snSysteme', TextType::class, [
            'label' => 'SN du système',
            'required' => false,
            'attr' => ['autofocus' => true, 'autocomplete' => 'off'],
        ]);

        // Ordre stable : par référence de sous-ensemble
        $lignes = $lot->getVersionProduit()->getNomenclatures()->toArray();
        usort(
            $lignes,
            fn (Nomenclature $a, Nomenclature $b) => $a->getReferenceSousEnsemble()->getReference() <=> $b->getReferenceSousEnsemble()->getReference()
        );

        // Un champ par exemplaire attendu : "2 USB" donne deux champs
        foreach ($lignes as $ligne) {
            $reference = $ligne->getReferenceSousEnsemble()->getReference();
            for ($i = 1; $i <= $ligne->getQuantite(); ++$i) {
                $builder->add(self::PREFIXE_SOUS_ENSEMBLE . $ligne->getId() . '_' . $i, TextType::class, [
                    'label' => sprintf('%s — n° %d/%d', $reference, $i, $ligne->getQuantite()),
                    'required' => false,
                    'attr' => ['autocomplete' => 'off'],
                ]);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('lot');
        $resolver->setAllowedTypes('lot', Lot::class);
    }
}