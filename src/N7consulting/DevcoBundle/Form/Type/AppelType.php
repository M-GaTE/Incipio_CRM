<?php

namespace N7consulting\DevcoBundle\Form\Type;

use Mgate\PersonneBundle\Entity\MembreRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AppelType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('suiveur', 'genemu_jqueryselect2_entity',
                array('label' => 'Appellant',
                'class' => 'Mgate\\PersonneBundle\\Entity\\Membre',
                'query_builder' => function (MembreRepository $mr) {
                    return $mr->getByMandatNonNulQueryBuilder();
                },
                'required' => false, ))
            ->add('prospect')
            ->add('employe')
            ->add('dateAppel', 'genemu_jquerydate', array('label' => 'Date appel (jj/mm/aaaa)', 'widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'required' => false))
            ->add('aRappeller', 'checkbox', array('required' => false, 'attr' => array('checked' => true)))
            ->add('dateRappel', 'genemu_jquerydate', array('label' => 'Date de Rappel', 'required' => false, 'format' => 'dd/MM/yyyy', 'widget' => 'single_text', 'attr' => array('cols' => 10, 'rows' => 6)))
            ->add('noteAppel', 'textarea', array('label' => 'Note sur l\'appel', 'required' => false))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'N7consulting\DevcoBundle\Entity\Appel',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'N7consulting_devcobundle_appel';
    }
}
