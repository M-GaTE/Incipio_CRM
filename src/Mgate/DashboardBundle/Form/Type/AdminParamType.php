<?php
/**
 * Created by PhpStorm.
 * User: Antoine
 * Date: 29/01/2017
 * Time: 10:33
 */

namespace Mgate\DashboardBundle\Form\Type;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminParamType extends AbstractType
{

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $fields = $this->em->getRepository('MgateDashboardBundle:AdminParam')->findAll(array(), array('priority'=>'desc'));

        foreach ($fields as $field){
            $builder->add($field->getName(),$this->chooseType($field->getParamType()), array('required' => $field->getRequired(), 'label' => $field->getParamLabel()));
        }

    }

    public function getBlockPrefix()
    {
        return 'Mgate_dashboardbundle_adminparam';
    }

//    public function configureOptions(OptionsResolver $resolver)
//    {
//        $resolver->setDefaults();
//    }

    private function chooseType($formType){

        if($formType === 'string'){
            return TextType::class;
        }
        elseif($formType === 'integer'){
            return IntegerType::class;
        }
        elseif ($formType === 'number'){
            return NumberType::class;
        }
        elseif ($formType === 'url'){
            return UrlType::class;
        }
        else{
            throw new \LogicException('Type '.$formType.' is invalid.');
        }
    }

}
