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

use Mgate\SuiviBundle\Entity\Suivi;
use Mgate\SuiviBundle\Form\Type\SuiviType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SuiviController extends Controller
{
    /**
     * @Security("has_role('ROLE_CA')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MgateSuiviBundle:Suivi')
            ->createQueryBuilder('s')
            ->innerJoin('s.etude', 'e')
            ->where('e.stateID < 5')
            //->groupBy('s.date')
            ->orderBy('e.mandat', 'DESC')
            ->addOrderBy('e.num', 'DESC')
            ->addOrderBy('s.date', 'DESC')
            ->getQuery()->getResult();

        return $this->render('MgateSuiviBundle:Suivi:index.html.twig', array(
            'suivis' => $entities,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    public function addAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$etude = $em->getRepository('Mgate\SuiviBundle\Entity\Etude')->find($id)) {
            throw $this->createNotFoundException('L\'étude n\'existe pas !');
        }

        $suivi = new Suivi();
        $suivi->setEtude($etude);
        $suivi->setDate(new \DateTime('now'));
        $form = $this->createForm(SuiviType::class, $suivi);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($suivi);
                $em->flush();

                return $this->redirect($this->generateUrl('MgateSuivi_suivi_voir', array('id' => $suivi->getId())));
            }
        }

        return $this->render('MgateSuiviBundle:Suivi:ajouter.html.twig', array(
            'form' => $form->createView(),
            'etude' => $etude,
        ));
    }

    private function compareDate(Suivi $a, Suivi $b)
    {
        if ($a->getDate() == $b->getDate()) {
            return 0;
        } else {
            return ($a->getDate() < $b->getDate()) ? -1 : 1;
        }
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $suivi = $em->getRepository('MgateSuiviBundle:Suivi')->find($id);

        if (!$suivi) {
            throw $this->createNotFoundException('Ce suivi n\'existe pas !');
        }

        $etude = $suivi->getEtude();
        $suivis = $etude->getSuivis()->toArray();
        usort($suivis, array($this, 'compareDate'));

        return $this->render('MgateSuiviBundle:Suivi:voir.html.twig', array(
            'suivis' => $suivis,
            'selectedSuivi' => $suivi,
            'etude' => $etude,
            ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    public function modifierAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$suivi = $em->getRepository('Mgate\SuiviBundle\Entity\Suivi')->find($id)) {
            throw $this->createNotFoundException('Ce suivi n\'existe pas !');
        }

        $form = $this->createForm(SuiviType::class, $suivi);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->flush();

                return $this->redirect($this->generateUrl('MgateSuivi_suivi_voir', array('id' => $suivi->getId())));
            }
        }

        return $this->render('MgateSuiviBundle:Suivi:modifier.html.twig', array(
            'form' => $form->createView(),
            'clientcontact' => $suivi,
            'etude' => $suivi->getEtude(),
        ));
    }
}
