<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\TresoBundle\Controller;

use Mgate\TresoBundle\Entity\CotisationURSSAF;
use Mgate\TresoBundle\Form\Type\CotisationURSSAFType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CotisationURSSAFController extends Controller
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $cotisations = $em->getRepository('MgateTresoBundle:CotisationURSSAF')->findAll();

        return $this->render('MgateTresoBundle:CotisationURSSAF:index.html.twig', array('cotisations' => $cotisations));
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     */
    public function modifierAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$cotisation = $em->getRepository('MgateTresoBundle:CotisationURSSAF')->find($id)) {
            $cotisation = new CotisationURSSAF();
        }

        $form = $this->createForm(new CotisationURSSAFType(), $cotisation);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));
            if ($form->isValid()) {
                $em->persist($cotisation);
                $em->flush();

                return $this->redirect($this->generateUrl('MgateTreso_CotisationURSSAF_index', array()));
            }
        }

        return $this->render('MgateTresoBundle:CotisationURSSAF:modifier.html.twig', array(
                    'form' => $form->createView(),
                    'cotisation' => $cotisation,
                ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function supprimerAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$cotisation = $em->getRepository('MgateTresoBundle:CotisationURSSAF')->find($id)) {
            throw $this->createNotFoundException('La Cotisation URSSAF n\'existe pas !');
        }

        $em->remove($cotisation);
        $em->flush();

        return $this->redirect($this->generateUrl('MgateTreso_CotisationURSSAF_index', array()));
    }
}
