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

class DocTypeSuiviType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('redige', 'checkbox', array('label' => 'Est-ce que le document est rédigé ?', 'required' => false))
            ->add('relu', 'checkbox', array('label' => 'Est-ce que le document est relu ?', 'required' => false))
            ->add('envoye', 'checkbox', array('label' => 'Est-ce que le document est envoyé ?', 'required' => false))
            ->add('receptionne', 'checkbox', array('label' => 'Est-ce que le document est réceptionné ?', 'required' => false));
    }

    public function getName()
    {
        return 'mgate_suivibundle_doctypetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'mgate\SuiviBundle\Entity\DocType',
            ));
    }
}
