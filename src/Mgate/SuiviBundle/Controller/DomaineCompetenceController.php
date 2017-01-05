<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\SuiviBundle\Controller;

use Mgate\SuiviBundle\Entity\DomaineCompetence;
use Mgate\SuiviBundle\Form\Type\DomaineCompetenceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DomaineCompetenceController extends Controller
{
    /**
     * @Security("has_role('ROLE_CA')")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('MgateSuiviBundle:DomaineCompetence')->findAll();

        $domaine = new DomaineCompetence();

        $form = $this->createForm(DomaineCompetenceType::class, $domaine);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($domaine);
                $em->flush();

                return $this->redirect($this->generateUrl('MgateSuivi_domaine_index'));
            }
        }

        return $this->render('MgateSuiviBundle:DomaineCompetence:index.html.twig', array(
            'domaines' => $entities,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$domaine = $em->getRepository('Mgate\SuiviBundle\Entity\DomaineCompetence')->find($id)) {
            throw $this->createNotFoundException('Ce domaine de competence n\'existe pas !');
        }

        $em->remove($domaine);
        $em->flush();

        return $this->redirect($this->generateUrl('MgateSuivi_domaine_index'));
    }
}
