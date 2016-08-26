<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use mgate\UserBundle\Entity\User as User;
use mgate\UserBundle\Form\Type\EventListener\AddMembreFieldSubscriber;


class UserAdminType extends BaseType
{
    protected $roles;

    public function __construct($class, $roles)
    {
        parent::__construct($class);
        $this->roles = $roles;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $subscriber = new AddMembreFieldSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);

        $builder->add('enabled', 'checkbox', array(
            'label' => 'Adresse email validé ?',
            'required' => false,
        ));
        $builder->add('roles', 'choice', array(
            'choices' => $this->refactorRoles($this->roles),
            'required' => false, 'label' => 'Roles', 'multiple' => true,
        ));

        parent::buildForm($builder, $options);
    }

    public function getName()
    {
        return 'mgate_user_useradmin';
    }

    private function refactorRoles($originRoles)
    {
        $roles = array();

        $rolesParent = array_keys($originRoles);
        foreach ($rolesParent as $roleParent) {
            if ($roleParent != 'ROLE_SUPER_ADMIN') {
                $roles[$roleParent] = $roleParent;
            }
        }

        return $roles;
    }
}
