<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\SuiviBundle\Form\Type;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\DateType;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2EntityType;
use Mgate\SuiviBundle\Entity\GroupePhasesRepository as GroupePhasesRepository;
use Mgate\SuiviBundle\Entity\Phase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('position', HiddenType::class, array('attr' => array('class' => 'position')))
                ->add('titre', TextType::class, array('attr' => array('placeholder' => 'Titre phase')))
                ->add('objectif', TextareaType::class, array('label' => 'Objectif', 'required' => false, 'attr' => array('placeholder' => 'Objectif')))
                ->add('methodo', TextareaType::class, array('label' => 'Méthodologie', 'required' => false, 'attr' => array('placeholder' => 'Méthodologie')))
                // Obsolète, la validation porte maintenant sur les groupes de phases
                // Une validation orale est impossible à prouver
                //->add('validation', 'choice', array('choices' => Phase::getValidationChoice(), 'required' => true))
                ->add('nbrJEH', IntegerType::class, array('label' => 'Nombre de JEH', 'required' => false, 'attr' => array('class' => 'nbrJEH')))
                ->add('prixJEH', IntegerType::class, array('label' => 'Prix du JEH HT', 'required' => false, 'attr' => array('class' => 'prixJEH')))
                ->add('dateDebut', DateType::class, array('label' => 'Date de début', 'format' => 'd/MM/y', 'required' => false, 'widget' => 'single_text'))
                ->add('delai', IntegerType::class, array('label' => 'Durée en nombre de jours', 'required' => false));
        if ($options['etude']) {
            $builder->add('groupe', Select2EntityType::class, array(
                'class' => 'Mgate\SuiviBundle\Entity\GroupePhases',
                'choice_label' => 'titre',
                'required' => false,
                'query_builder' => function (GroupePhasesRepository $er) use ($options) {
                    return $er->getGroupePhasesByEtude($options['etude']);
                },
                'label' => 'Groupe',
                ));
        }

        if ($options['isAvenant']) {
            $builder->add('etatSurAvenant', ChoiceType::class, array('choices' => Phase::getEtatSurAvenantChoice(), 'required' => false));
        }
    }

    public function getBlockPrefix()
    {
        return 'Mgate_suivibundle_phasetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mgate\SuiviBundle\Entity\Phase',
            'isAvenant' => false,
            'etude' => null,
        ));
    }
}
