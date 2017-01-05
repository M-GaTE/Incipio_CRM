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

use Mgate\SuiviBundle\Entity\Av;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvType extends DocTypeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('differentielDelai', 'integer', array('label' => 'Modification du Délai (+/- x jours)', 'required' => true))
        ->add('objet', 'textarea',
        array('label' => 'Exposer les causes de l’Avenant. Ne pas hésiter à détailler l\'historique des relations avec le client et du travail sur l\'étude qui ont conduit à l\'Avenant.',
        'required' => true, ))
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
        ))*/

        DocTypeType::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'Mgate_suivibundle_avtype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mgate\SuiviBundle\Entity\Av',
            'prospect' => '',
        ));
    }
}
