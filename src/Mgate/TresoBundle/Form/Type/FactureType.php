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
                ->add('type', ChoiceType::class, array('choices' => Facture::getTypeChoices(), 'required' => true))
                ->add('objet', TextareaType::class,
                    array('label' => 'Objet de la Facture',
                        'required' => true,
                        'attr' => array(
                            'cols' => '100%',
                            'rows' => 5, ),
                        )
                    )
                ->add('details', CollectionType::class, array(
                    'type' => new FactureDetailType(),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'by_reference' => false,
                ))
                ->add('beneficiaire', 'genemu_jqueryselect2_entity', array(
                    'class' => 'Mgate\PersonneBundle\Entity\Prospect',
                    'property' => 'nom',
                    'required' => true,
                    'label' => 'Facture émise pour/par',
                ))
                ->add('montantADeduire', new FactureDetailType(), array('label' => 'Montant à déduire', 'required' => true))
                ->add('dateEmission', 'genemu_jquerydate', array('label' => 'Date d\'émission', 'required' => true, 'widget' => 'single_text'))
                ->add('dateVersement', 'genemu_jquerydate', array('label' => 'Date de versement', 'required' => false, 'widget' => 'single_text'));
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
