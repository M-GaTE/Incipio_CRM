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

use Mgate\PersonneBundle\Entity\PersonneRepository;
use Mgate\PersonneBundle\Form\Type\ProspectType;
use Mgate\SuiviBundle\Entity\Etude;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtudeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('knownProspect', CheckboxType::class, array(
                'required' => false,
                'label' => 'Le signataire client existe-t-il déjà dans la base de donnée ?',
                ))
             ->add('prospect', 'genemu_jqueryselect2_entity', array(
                'class' => 'Mgate\PersonneBundle\Entity\Prospect',
                'property' => 'nom',
                'required' => true,
                'label' => 'Prospect existant',
                ))
            ->add('newProspect', new ProspectType(), array('label' => 'Nouveau prospect:', 'required' => false))
            ->add('nom', TextType::class, array('label' => 'Nom interne de l\'étude'))
            ->add('description', TextareaType::class, array('label' => 'Présentation interne de l\'étude', 'required' => false, 'attr' => array('cols' => '100%', 'rows' => 5)))
            ->add('mandat', IntegerType::class)
            ->add('num', IntegerType::class, array('label' => 'Numéro de l\'étude', 'required' => false))
            ->add('confidentiel', CheckboxType::class, array('label' => 'Confidentialité :', 'required' => false, 'attr' => array('title' => "Si l'étude est confidentielle, elle ne sera visible que par vous et les membres du CA.")))
            ->add('suiveur', 'genemu_jqueryselect2_entity',
                array('label' => 'Suiveur de projet',
                       'class' => 'Mgate\\PersonneBundle\\Entity\\Personne',
                       'property' => 'prenomNom',
                       'query_builder' => function (PersonneRepository $pr) {
                           return $pr->getMembreOnly();
                       },
                       'required' => false, ))
            ->add('domaineCompetence', 'genemu_jqueryselect2_entity', array(
                'class' => 'Mgate\SuiviBundle\Entity\DomaineCompetence',
                'property' => 'nom',
                'required' => false,
                'label' => 'Domaine de compétence',
                ))
            ->add('sourceDeProspection', ChoiceType::class, array('choices' => Etude::getSourceDeProspectionChoice(), 'required' => false));
    }

    public function getBlockPrefix()
    {
        return 'Mgate_suivibundle_etudetype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mgate\SuiviBundle\Entity\Etude',
        ));
    }
}
