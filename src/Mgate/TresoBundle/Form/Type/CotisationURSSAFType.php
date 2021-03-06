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

use Genemu\Bundle\FormBundle\Form\JQuery\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CotisationURSSAFType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, array('label' => 'Libelle'))
            ->add('dateDebut', DateType::class, array('label' => 'Applicable du', 'required' => true, 'widget' => 'single_text'))
            ->add('dateFin', DateType::class, array('label' => 'Applicable au', 'required' => true, 'widget' => 'single_text'))
            ->add('tauxPartJE', PercentType::class, array('label' => 'Taux Part Junior', 'required' => false, 'scale' => 3))
            ->add('tauxPartEtu', PercentType::class, array('label' => 'Taux Part Etu', 'required' => false, 'scale' => 3))
            ->add('surBaseURSSAF', CheckboxType::class, array('label' => 'Est indexé sur la base URSSAF ?', 'required' => false))
            ->add('deductible', CheckboxType::class, array('label' => 'Est déductible ?', 'required' => false));
    }

    public function getBlockPrefix()
    {
        return 'Mgate_tresobundle_cotisationurssaftype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mgate\TresoBundle\Entity\CotisationURSSAF',
        ));
    }
}
