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

use Mgate\TresoBundle\Entity\NoteDeFrais as NoteDeFrais;
use Mgate\TresoBundle\Form\Type\NoteDeFraisType as NoteDeFraisType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NoteDeFraisController extends Controller
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $nfs = $em->getRepository('MgateTresoBundle:NoteDeFrais')->findAll();

        return $this->render('MgateTresoBundle:NoteDeFrais:index.html.twig', array('nfs' => $nfs));
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$nf = $em->getRepository('MgateTresoBundle:NoteDeFrais')->find($id)) {
            throw $this->createNotFoundException('La Note de Frais n\'existe pas !');
        }

        return $this->render('MgateTresoBundle:NoteDeFrais:voir.html.twig', array('nf' => $nf));
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     */
    public function modifierAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$nf = $em->getRepository('MgateTresoBundle:NoteDeFrais')->find($id)) {
            $nf = new NoteDeFrais();
            $now = new \DateTime('now');
            $nf->setDate($now);
        }

        $form = $this->createForm(new NoteDeFraisType(), $nf);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                foreach ($nf->getDetails() as $nfd) {
                    $nfd->setNoteDeFrais($nf);
                }
                $em->persist($nf);
                $em->flush();

                return $this->redirect($this->generateUrl('MgateTreso_NoteDeFrais_voir', array('id' => $nf->getId())));
            }
        }

        return $this->render('MgateTresoBundle:NoteDeFrais:modifier.html.twig', array(
                    'form' => $form->createView(),
                ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function supprimerAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$nf = $em->getRepository('MgateTresoBundle:NoteDeFrais')->find($id)) {
            throw $this->createNotFoundException('La Note de Frais n\'existe pas !');
        }

        $em->remove($nf);
        $em->flush();

        return $this->redirect($this->generateUrl('MgateTreso_NoteDeFrais_index', array()));
    }
}
