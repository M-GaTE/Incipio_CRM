<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PersonneBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['user']) {
            $helpEmail = array('title' => "Pas d'adresse etu. Cette adresse est reprise dans les AP des études suivies.");
            $helpMobile = array('title' => 'Sous la forme: 06 78 39 .. Ce téléphone est repris dans les AP des études suivies.');
        } else {
            $helpEmail = array();
            $helpMobile = array();
        }

        $builder
                ->add('prenom')
                ->add('nom')
                ->add('sexe', SexeType::class, array('required' => true))
                ->add('mobile', TextType::class, array('required' => false, 'attr' => $helpMobile))
                ->add('email', EmailType::class, array('required' => false, 'attr' => $helpEmail))
                ->add('estAbonneNewsletter', CheckboxType::class, array('label' => 'Abonné Newsletter ?', 'required' => false))
                ->add('emailEstValide', CheckboxType::class, array('label' => 'Email Valide ?', 'required' => false));

        if (!$options['mini'] && !$options['user']) {
            $builder->add('fix', TextType::class, array('required' => false));
        }
        if (!$options['mini']) {
            $builder->add('adresse', TextareaType::class, array('label' => 'Adresse', 'required' => false))
                ->add('codepostal', TextType::class, array('label' => 'Code Postal', 'required' => false))
                ->add('ville', TextType::class, array('label' => 'Ville', 'required' => false))
                ->add('pays', TextType::class, array('label' => 'Pays', 'required' => false));
        }
    }

    public function getBlockPrefix()
    {
        return 'Mgate_personnebundle_personnetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mgate\PersonneBundle\Entity\Personne',
            'mini' => false,
            'user' => false,
            'signataire' => false,
        ));
    }
}
