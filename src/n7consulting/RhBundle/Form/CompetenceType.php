<?php


namespace n7consulting\RhBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompetenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('nom', 'text', array('required' => true))
                ->add('description', 'text', array('required' => false))
        ;
    }

    public function getName()
    {
        return 'n7consulting_rhbundle_competencetype';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'n7consulting\RhBundle\Entity\Competence',
        ));
    }
}
