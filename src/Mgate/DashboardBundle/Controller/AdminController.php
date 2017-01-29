<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\DashboardBundle\Controller;

use Mgate\DashboardBundle\Entity\AdminParam;
use Mgate\DashboardBundle\Form\Type\AdminParamType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    /**
     * @Security("has_role('ROLE_CA')")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(AdminParamType::class);

        $json_key_value_store =  $this->get('app.json_key_value_store');

        $keys = $json_key_value_store->keys();

        foreach ($keys as $key){
            $form->get($key)->setData($json_key_value_store->get($key));
        }

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $form_fields = $form->getData();
                foreach($form_fields as $key => $value) {
                    dump($value);
                    $json_key_value_store->set($key,$value);
                }
                $this->addFlash('success', 'valide');
            }

        }

        return $this->render('MgateDashboardBundle:Admin:index.html.twig',
            array('form' => $form->createView())
        );
    }

}
