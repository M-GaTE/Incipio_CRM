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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuiviType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('date', DateType::class, array('label' => 'Date du suivi'))
                ->add('etat', TextareaType::class, array('label' => 'Etat de l\'étude', 'attr' => array('cols' => '100%', 'rows' => 5)))
                ->add('todo', TextareaType::class, array('label' => 'Taches à faire', 'attr' => array('cols' => '100%', 'rows' => 5)));
    }

    public function getBlockPrefix()
    {
        return 'Mgate_suivibundle_clientcontacttype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Mgate\SuiviBundle\Entity\Suivi',
            ));
    }
}
