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

use Mgate\SuiviBundle\Entity\Etude;
use Mgate\SuiviBundle\Entity\ProcesVerbal;
use Mgate\SuiviBundle\Form\Type\ProcesVerbalSubType;
use Mgate\SuiviBundle\Form\Type\ProcesVerbalType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProcesVerbalController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        return $this->render('MgateSuiviBundle:Etude:index.html.twig', array(
            'etudes' => $entities,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MgateSuiviBundle:ProcesVerbal')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProcesVerbal entity.');
        }

        $etude = $entity->getEtude();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        return $this->render('MgateSuiviBundle:ProcesVerbal:voir.html.twig', array(
            'procesverbal' => $entity,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function addAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$etude = $em->getRepository('Mgate\SuiviBundle\Entity\Etude')->find($id)) {
            throw $this->createNotFoundException('L\'étude n\'existe pas !');
        }

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $proces = new ProcesVerbal();
        $etude->addPvi($proces);

        $form = $this->createForm(ProcesVerbalSubType::class, $proces, array('type' => 'pvi', 'prospect' => $etude->getProspect(), 'phases' => count($etude->getPhases()->getValues())));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($proces);
                $em->flush();

                return $this->redirect($this->generateUrl('MgateSuivi_procesverbal_voir', array('id' => $proces->getId())));
            }
        }

        return $this->render('MgateSuiviBundle:ProcesVerbal:ajouter.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function modifierAction(Request $request, $id_pv)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$procesverbal = $em->getRepository('Mgate\SuiviBundle\Entity\ProcesVerbal')->find($id_pv)) {
            throw $this->createNotFoundException('Le Procès Verbal n\'existe pas !');
        }

        $etude = $procesverbal->getEtude();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $form = $this->createForm(ProcesVerbalSubType::class, $procesverbal, array('type' => $procesverbal->getType(), 'prospect' => $procesverbal->getEtude()->getProspect(), 'phases' => count($procesverbal->getEtude()->getPhases()->getValues())));
        $deleteForm = $this->createDeleteForm($id_pv);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($procesverbal);
                $em->flush();

                return $this->redirect($this->generateUrl('MgateSuivi_procesverbal_voir', array('id' => $procesverbal->getId())));
            }
        }

        return $this->render('MgateSuiviBundle:ProcesVerbal:modifier.html.twig', array(
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'etude' => $procesverbal->getEtude(),
            'type' => $procesverbal->getType(),
            'procesverbal' => $procesverbal,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @param Request $request
     * @param Etude $etude
     * @param $type string PVR or PVRI
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function redigerAction(Request $request, Etude $etude, $type)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        if (!$procesverbal = $etude->getDoc($type)) {
            $procesverbal = new ProcesVerbal();
            if (strtoupper($type) == 'PVR') {
                $etude->setPvr($procesverbal);
            }

            $procesverbal->setType($type);
        }

        $form = $this->createForm(ProcesVerbalType::class, $etude, array('type' => $type, 'prospect' => $etude->getProspect(), 'phases' => count($etude->getPhases()->getValues())));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($etude);
                $em->flush();

                return $this->redirect($this->generateUrl('MgateSuivi_procesverbal_voir', array('id' => $procesverbal->getId())));
            }
        }

        return $this->render('MgateSuiviBundle:ProcesVerbal:rediger.html.twig',
            array('form' => $form->createView(),'etude' => $etude,'type' => $type,)
        );
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function deleteAction(Request $request, $id_pv)
    {
        $form = $this->createDeleteForm($id_pv);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (!$entity = $em->getRepository('Mgate\SuiviBundle\Entity\ProcesVerbal')->find($id_pv)) {
                throw $this->createNotFoundException('Le Procès Verbal n\'existe pas !');
            }

            $etude = $entity->getEtude();

            if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
                throw new AccessDeniedException('Cette étude est confidentielle');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('MgateSuivi_etude_voir', array('nom' => $etude->getNom())));
    }

    private function createDeleteForm($id_pv)
    {
        return $this->createFormBuilder(array('id' => $id_pv))
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
