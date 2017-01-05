<?php

namespace N7consulting\DevcoBundle\Form\Type;

use Mgate\PersonneBundle\Entity\MembreRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('aRappeller', CheckboxType::class, array('required' => false, 'attr' => array('checked' => true)))
            ->add('dateRappel', 'genemu_jquerydate', array('label' => 'Date de Rappel', 'required' => false, 'format' => 'dd/MM/yyyy', 'widget' => 'single_text', 'attr' => array('cols' => 10, 'rows' => 6)))
            ->add('noteAppel', TextareaType::class, array('label' => 'Note sur l\'appel', 'required' => false))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'N7consulting\DevcoBundle\Entity\Appel',
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'N7consulting_devcobundle_appel';
    }
}
