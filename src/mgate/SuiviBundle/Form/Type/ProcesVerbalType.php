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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcesVerbalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(strtolower($options['type']), new ProcesVerbalSubType(), array('label' => ' ', 'type' => $options['type'], 'prospect' => $options['prospect'], 'phases' => $options['phases']));
    }

    public function getName()
    {
        return 'mgate_suivibundle_ProcesVerbaltype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'mgate\SuiviBundle\Entity\Etude',
            'type' => '',
            'prospect' => '',
            'phases' => '',
        ));
    }
}
