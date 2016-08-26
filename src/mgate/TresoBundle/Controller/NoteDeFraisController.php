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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use mgate\TresoBundle\Entity\NoteDeFrais as NoteDeFrais;
use mgate\TresoBundle\Form\NoteDeFraisType as NoteDeFraisType;

class NoteDeFraisController extends Controller
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $nfs = $em->getRepository('mgateTresoBundle:NoteDeFrais')->findAll();

        return $this->render('mgateTresoBundle:NoteDeFrais:index.html.twig', array('nfs' => $nfs));
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$nf = $em->getRepository('mgateTresoBundle:NoteDeFrais')->find($id)) {
            throw $this->createNotFoundException('La Note de Frais n\'existe pas !');
        }

        return $this->render('mgateTresoBundle:NoteDeFrais:voir.html.twig', array('nf' => $nf));
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     */
    public function modifierAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$nf = $em->getRepository('mgateTresoBundle:NoteDeFrais')->find($id)) {
            $nf = new NoteDeFrais();
            $now = new \DateTime('now');
            $nf->setDate($now);
        }

        $form = $this->createForm(new NoteDeFraisType(), $nf);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                foreach ($nf->getDetails() as $nfd) {
                    $nfd->setNoteDeFrais($nf);
                }
                $em->persist($nf);
                $em->flush();

                return $this->redirect($this->generateUrl('mgateTreso_NoteDeFrais_voir', array('id' => $nf->getId())));
            }
        }

        return $this->render('mgateTresoBundle:NoteDeFrais:modifier.html.twig', array(
                    'form' => $form->createView(),
                ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function supprimerAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$nf = $em->getRepository('mgateTresoBundle:NoteDeFrais')->find($id)) {
            throw $this->createNotFoundException('La Note de Frais n\'existe pas !');
        }

        $em->remove($nf);
        $em->flush();

        return $this->redirect($this->generateUrl('mgateTreso_NoteDeFrais_index', array()));
    }
}
