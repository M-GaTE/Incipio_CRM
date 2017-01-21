<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PersonneBundle\Form\Type;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\DateType as GenemuDateType;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2CountryType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('personne', PersonneType::class, array('label' => ' ', 'user' => true))
                ->add('identifiant', TextType::class,
                    array('label' => 'Identifiant',
                        'required' => false,
                        'attr' => array('readonly' => true)
                    )
                )
                ->add('emailEMSE', TextType::class, array('label' => 'Email Ecole', 'required' => false))
                ->add('promotion', IntegerType::class, array('label' => 'Promotion', 'required' => false))
                ->add('dateDeNaissance', GenemuDateType::class, array('label' => 'Date de naissance (jj/mm/aaaa)', 'widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'required' => false))
                ->add('lieuDeNaissance', TextType::class, array('label' => 'Lieu de naissance', 'required' => false))
                ->add('nationalite', Select2CountryType::class, array('label' => 'Nationalité', 'required' => true, 'preferred_choices' => array('FR')))
                ->add('mandats', CollectionType::class, array(
                    'entry_type' => MandatType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'by_reference' => false, //indispensable cf doc
                ))
                ->add('dateConventionEleve', GenemuDateType::class, array('label' => 'Date de Signature de la Convention Elève', 'format' => 'dd/MM/yyyy', 'required' => false, 'widget' => 'single_text'))
                ->add('photo', FileType::class, array(
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Modifier la photo de profil du membre',
                ))
                ->add('formatPaiement', ChoiceType::class, array('choices' => array('aucun' => 'aucun', 'cheque' => 'Chèque', 'especes' => 'Espèces'), 'required' => true))
                ->add('filiere', EntityType::class,
                    array('label' => 'Filiere',
                        'class' => 'Mgate\\PersonneBundle\\Entity\\Filiere',
                        'required' => true, ));
    }

    public function getBlockPrefix()
    {
        return 'Mgate_personnebundle_membretype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mgate\PersonneBundle\Entity\Membre',
        ));
    }
}
