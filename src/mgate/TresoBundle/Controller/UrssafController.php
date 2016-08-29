<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\TresoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UrssafController extends Controller
{
    public function indexAction(Request $request, $year = null, $month = null)
    {
        $em = $this->getDoctrine()->getManager();

        $defaultData = array('message' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            ->add('date', 'genemu_jquerydate', array('label' => 'Nombre de développeur au :', 'required' => true, 'widget' => 'single_text', 'data' => date_create(), 'format' => 'dd/MM/yyyy'))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                return $this->redirect($this->generateUrl('mgate_treso_urssaf', array('year' => $data['date']->format('Y'),
                    'month' => $data['date']->format('m'),
                )));
            }
        }

        if ($year == null || $month === null) {
            $date = new \DateTime('now');
        } else {
            $date = new \DateTime();
            $date->setDate($year, $month, 01);
        }

        $RMs = $em->getRepository('mgateSuiviBundle:Mission')->getMissionsBeginBeforeDate($date);



        return $this->render('mgateTresoBundle:Urssaf:index.html.twig', array('form' => $form->createView(), 'RMs' => $RMs));
    }
}
