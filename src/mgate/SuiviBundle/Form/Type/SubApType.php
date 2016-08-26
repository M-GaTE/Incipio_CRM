<?php


namespace mgate\SuiviBundle\Form\Type;


use mgate\PersonneBundle\Entity\PersonneRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubApType extends DocTypeType
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('contactMgate', 'genemu_jqueryselect2_entity', array('label' => "'En cas d’absence ou de problème, il est également possible de joindre ...' ex: Vice-Président",
            'class' => 'mgate\\PersonneBundle\\Entity\\Personne',
            'property' => 'prenomNom',
            'attr' => array('title' => "Dans l'AP: 'En cas d’absence ou de problème, il est également possible de joindre le ...'"),
            'query_builder' => function (PersonneRepository $pr) {
                return $pr->getMembresByPoste('%vice-president%');
            },
            'required' => true,));
        DocTypeType::buildForm($builder, $options);
        $builder->add('nbrDev', 'integer', array('label' => 'Nombre d\'intervenants estimé', 'required' => false, 'attr' => array('title' => 'Mettre 0 pour ne pas afficher la phrase indiquant le nombre d\'intervenant')));
    }

    public function getName()
    {
        return 'mgate_suivibundle_subaptype';
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'mgate\SuiviBundle\Entity\Ap',
            'prospect' => '',
        ));
    }

}