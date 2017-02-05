<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\TresoBundle\Form\Type;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\DateType;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2EntityType;
use Mgate\TresoBundle\Entity\Facture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('exercice', IntegerType::class, array('label' => 'Exercice Comptable', 'required' => true))
                ->add('numero', IntegerType::class, array('label' => 'Numéro de la Facture', 'required' => true))
                ->add('type', ChoiceType::class, array('choices' => Facture::getTypeChoices(),
                    'required' => true,
                    'choice_label' => function ($name) {
                        return $name;
                    }, ))
                ->add('objet', TextareaType::class,
                    array('label' => 'Objet de la Facture',
                        'required' => true,
                        'attr' => array(
                            'cols' => '100%',
                            'rows' => 5, ),
                        )
                    )
                ->add('details', CollectionType::class, array(
                    'entry_type' => FactureDetailType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'by_reference' => false,
                ))
                ->add('beneficiaire', Select2EntityType::class, array(
                    'class' => 'Mgate\PersonneBundle\Entity\Prospect',
                    'choice_label' => 'nom',
                    'required' => true,
                    'label' => 'Facture émise pour/par',
                ))
                ->add('montantADeduire', FactureDetailType::class, array('label' => 'Montant à déduire', 'required' => true))
                ->add('dateEmission', DateType::class, array('label' => 'Date d\'émission', 'required' => true, 'widget' => 'single_text'))
                ->add('dateVersement', DateType::class, array('label' => 'Date de versement', 'required' => false, 'widget' => 'single_text'));
    }

    public function getBlockPrefix()
    {
        return 'Mgate_tresobundle_facturetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mgate\TresoBundle\Entity\Facture',
        ));
    }
}
