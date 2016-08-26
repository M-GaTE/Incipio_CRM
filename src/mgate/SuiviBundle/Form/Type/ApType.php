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

use mgate\PersonneBundle\Entity\PersonneRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use mgate\SuiviBundle\Entity\Ap;
use mgate\SuiviBundle\Entity\Etude;

class ApType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('suiveur', 'genemu_jqueryselect2_entity', array('label' => 'Suiveur de projet',
                    'class' => 'mgate\\PersonneBundle\\Entity\\Personne',
                    'property' => 'prenomNom',
                    'query_builder' => function (PersonneRepository $pr) {
                        return $pr->getByMandatNonNulQueryBuilder();
                    },
                    'required' => false, ))
                ->add('ap', new SubApType(), array('label' => ' ', 'prospect' => $options['prospect']))
                ->add('fraisDossier', 'integer', array('label' => 'Frais de dossier', 'required' => false))
                ->add('presentationProjet', 'textarea', array('label' => 'Présentation du projet', 'required' => false, 'attr' => array('cols' => '100%', 'rows' => 5)))
                ->add('descriptionPrestation', 'textarea', array('label' => 'Description de la prestation proposée', 'required' => false, 'attr' => array('title' => "La phrase commence par 'N7 Consulting réalisera, pour le compte du Client, une étude consistant en'. Il faut la continuer en décrivant la prestation proposée. Le début de la phrase est déjà généré.", 'cols' => '100%', 'rows' => 5)))
                ->add('typePrestation', 'choice', array('choices' => Etude::getTypePrestationChoice(), 'label' => 'Type de prestation', 'required' => false))
                ->add('competences' /**,'textarea', array('label' => 'Capacité des intervenants:', 'required' => false, 'attr' => array('cols' => '100%', 'rows' => 5))**/);
    }

    public function getName()
    {
        return 'mgate_suivibundle_aptype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'mgate\SuiviBundle\Entity\Etude',
            'prospect' => '',
        ));
    }
}
