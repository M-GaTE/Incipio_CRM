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

use JMS\Serializer\Exception\LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Mgate\TresoBundle\Entity\BV;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Mgate\TresoBundle\Form\Type\BVType;

class BVController extends Controller
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $bvs = $em->getRepository('MgateTresoBundle:BV')->findAll();

        return $this->render('MgateTresoBundle:BV:index.html.twig', array('bvs' => $bvs));
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $bv = $em->getRepository('MgateTresoBundle:BV')->find($id);

        return $this->render('MgateTresoBundle:BV:voir.html.twig', array('bv' => $bv));
    }

    /**
     * @Security("has_role('ROLE_TRESO', 'ROLE_SUIVEUR')")
     */
    public function modifierAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$bv = $em->getRepository('MgateTresoBundle:BV')->find($id)) {
            $bv = new BV();
            $bv->setTypeDeTravail('Réalisateur')
                ->setDateDeVersement(new \DateTime('now'))
                ->setDateDemission(new \DateTime('now'));
        }

        $form = $this->createForm(new BVType(), $bv);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->handleRequest($this->get('request'));
            if ($form->isValid()) {
                $bv->setCotisationURSSAF();
                $charges = $em->getRepository('MgateTresoBundle:CotisationURSSAF')->findAllByDate($bv->getDateDemission());
                foreach ($charges as $charge) {
                    $bv->addCotisationURSSAF($charge);
                }

                $baseURSSAF = $em->getRepository('MgateTresoBundle:BaseURSSAF')->findByDate($bv->getDateDemission());
                if ($baseURSSAF === null) {
                    throw new LogicException('Il n\'y a aucune base Urssaf définie pour cette période.Pour ajouter une base URSSAF : '.$this->get('router')->generate('MgateTreso_BaseURSSAF_index').'.');
                }
                $bv->setBaseURSSAF($baseURSSAF);

                $em->persist($bv);
                $em->flush();

                return $this->redirect($this->generateUrl('MgateTreso_BV_index', array()));
            }
        }

        return $this->render('MgateTresoBundle:BV:modifier.html.twig', array(
            'form' => $form->createView(),
            'bv' => $bv,
        ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function supprimerAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$bv = $em->getRepository('MgateTresoBundle:BV')->find($id)) {
            throw $this->createNotFoundException('Le BV n\'existe pas !');
        }

        $em->remove($bv);
        $em->flush();

        return $this->redirect($this->generateUrl('MgateTreso_BV_index', array()));
    }
}
