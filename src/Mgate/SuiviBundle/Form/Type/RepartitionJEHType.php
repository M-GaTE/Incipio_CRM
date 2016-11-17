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

class RepartitionJEHType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nbrJEH', 'integer', array('required' => true))
                ->add('prixJEH', 'integer', array('required' => true, 'attr' => array('min' => 80, 'max' => 320)));
    }

    public function getName()
    {
        return 'Mgate_suivibundle_RepartitionJEHType';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mgate\SuiviBundle\Entity\RepartitionJEH',
            'type' => '',
            'prospect' => '',
            'phases' => '',
        ));
    }
}
