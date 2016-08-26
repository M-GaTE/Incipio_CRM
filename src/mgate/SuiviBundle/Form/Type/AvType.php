<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\SuiviBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use mgate\SuiviBundle\Entity\Av;

class AvType extends DocTypeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('differentielDelai', 'integer', array('label' => 'Modification du Délai (+/- x jours)', 'required' => false))
        ->add('objet', 'textarea',
        array('label' => 'Exposer les causes de l’Avenant. Ne pas hésiter à détailler l\'historique des relations avec le client et du travail sur l\'étude qui ont conduit à l\'Avenant.',
        'required' => false, ))
        ->add('clauses', 'choice', array('label' => 'Type d\'avenant', 'multiple' => true, 'choices' => Av::getClausesChoices()))
        ->add('phases', 'collection', array(
                'type' => new PhaseType(),
                'options' => array('isAvenant' => true),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                ));
                /*->add('avenantsMissions', 'collection', array(
            'type' => new AvMissionType,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'by_reference' => false,
        ))*/;

        DocTypeType::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'mgate_suivibundle_avtype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'mgate\SuiviBundle\Entity\Av',
            'prospect' => '',
        ));
    }
}
