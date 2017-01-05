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

use Mgate\PersonneBundle\Entity\PersonneRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteDeFraisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('mandat', IntegerType::class, array('label' => 'Mandat', 'required' => true))
                ->add('numero', IntegerType::class, array('label' => 'Numéro de la Note de Frais', 'required' => true))
                ->add('objet', TextareaType::class,
                    array('label' => 'Objet de la Note de Frais',
                        'required' => false,
                        'attr' => array(
                            'cols' => '100%',
                            'rows' => 5, ),
                        )
                    )
                ->add('details', CollectionType::class, array(
                    'type' => new NoteDeFraisDetailType(),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'by_reference' => false,
                ))
                ->add('demandeur', 'genemu_jqueryselect2_entity', array(
                      'label' => 'Demandeur',
                       'class' => 'Mgate\\PersonneBundle\\Entity\\Personne',
                       'property' => 'prenomNom',
                       'query_builder' => function (PersonneRepository $pr) {
                           return $pr->getMembreOnly();
                       },
                       'required' => true, ))
                ->add('date', 'genemu_jquerydate', array('label' => 'Date', 'required' => true, 'widget' => 'single_text'));
    }

    public function getBlockPrefix()
    {
        return 'Mgate_tresobundle_notedefraistype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mgate\TresoBundle\Entity\NoteDeFrais',
        ));
    }
}
