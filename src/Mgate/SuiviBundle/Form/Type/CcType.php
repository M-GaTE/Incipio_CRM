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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CcType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cc', new SubCcType(), array('label' => ' ', 'prospect' => $options['prospect']))
            ->add('acompte', 'checkbox', array('label' => 'Acompte', 'required' => false))
            ->add('pourcentageAcompte', 'percent', array('label' => 'Pourcentage acompte', 'required' => false));
    }

    public function getName()
    {
        return 'Mgate_suivibundle_cctype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mgate\SuiviBundle\Entity\Etude',
            'prospect' => '',
        ));
    }
}
