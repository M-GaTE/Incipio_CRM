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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BVType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mandat', IntegerType::class)
            ->add('numero', IntegerType::class)
            ->add('nombreJEH', IntegerType::class)
            ->add('remunerationBruteParJEH', MoneyType::class)
            ->add('dateDeVersement', 'genemu_jquerydate', array('label' => 'Date de versement', 'required' => true, 'widget' => 'single_text'))
            ->add('dateDemission', 'genemu_jquerydate', array('label' => 'Date d\'émission', 'required' => true, 'widget' => 'single_text'))
            ->add('typeDeTravail', TextType::class)
            ->add('mission', 'genemu_jqueryselect2_entity', array(
                      'label' => 'Mission',
                       'class' => 'Mgate\\SuiviBundle\\Entity\\Mission',
                       'property' => 'reference',
                       'required' => true, ))
            ->add('numeroVirement', TextType::class, array('label' => 'Numéro de Virement', 'required' => true));
    }

    public function getBlockPrefix()
    {
        return 'Mgate_tresobundle_bvtype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mgate\TresoBundle\Entity\BV',
        ));
    }
}
