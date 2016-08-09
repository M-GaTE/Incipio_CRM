<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\PersonneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('personne', new PersonneType(), array('label' => ' ', 'user' => true))
                ->add('identifiant', 'text', array('label' => 'Identifiant', 'required' => false, 'read_only' => true))
                ->add('emailEMSE', 'text', array('label' => 'Email Ecole', 'required' => false))
                ->add('promotion', 'integer', array('label' => 'Promotion', 'required' => false))
                ->add('dateDeNaissance', 'date', array('label' => 'Date de naissance (jj/mm/aaaa)', 'widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'required' => false))
                ->add('lieuDeNaissance', 'text', array('label' => 'Lieu de naissance', 'required' => false))
                ->add('nationalite', 'genemu_jqueryselect2_country', array('label' => 'Nationalité', 'required' => true, 'preferred_choices' => array('FR')))
                ->add('mandats', 'collection', array(
                    'type' => new MandatType(),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'by_reference' => false, //indispensable cf doc
                ))
                ->add('dateConventionEleve', 'genemu_jquerydate', array('label' => 'Date de Signature de la Convention Elève', 'format' => 'dd/MM/yyyy', 'required' => false, 'widget' => 'single_text'))
                ->add('photo', 'file', array(
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Modifier la photo de profil du membre',
                ))
				->add('formatPaiement', 'choice', array( 'choices' => array('aucun'=>'aucun', 'cheque' => 'Chèque', 'especes' => 'Espèces') ))
				->add('estSocieteGenerale', 'checkbox', array('label' => 'Compte Sogé ?', 'required' => false))
				->add('filiere', 'choice', array( 'choices' => array('EN' => 'EN', 'HMF' => 'HMF', 'IMA'=>'IMA', 'TR'=>'TR', 'GEA'=>'GEA') ));
    }

    public function getName()
    {
        return 'mgate_personnebundle_membretype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'mgate\PersonneBundle\Entity\Membre',
        ));
    }
}
