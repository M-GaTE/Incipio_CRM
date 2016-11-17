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

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvMissionType extends DocTypeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // utiliser DocTypeType::buildForm($builder,$options) ?
    }

    public function getName()
    {
        return 'Mgate_suivibundle_avmssiontype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mgate\SuiviBundle\Entity\AvMission',
        ));
    }
}
