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

use Mgate\SuiviBundle\Entity\AvMission;
use Mgate\SuiviBundle\Form\Type\AvMissionHandler;
use Mgate\SuiviBundle\Form\Type\AvMissionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AvMissionController extends Controller
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
    public function addAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // On vérifie que l'article d'id $id existe bien, sinon, erreur 404.
        if (!$etude = $em->getRepository('Mgate\SuiviBundle\Entity\Etude')->find($id)) {
            throw $this->createNotFoundException('Article[id=' . $id . '] inexistant');
        }

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $avmission = new AvMission();
        $avmission->setEtude($etude);
        $form = $this->createForm(AvMissionType::class, $avmission);
        $formHandler = new AvMissionHandler($form, $this->get('request'), $em);

        if ($formHandler->process()) {
            return $this->redirect($this->generateUrl('MgateSuivi_avmission_voir', array('id' => $avmission->getId())));
        }

        return $this->render('MgateSuiviBundle:AvMission:ajouter.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MgateSuiviBundle:AvMission')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AvMission entity.');
        }

        $etude = $entity->getEtude();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        return $this->render('MgateSuiviBundle:AvMission:voir.html.twig', array(
            'avmission' => $entity,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function modifierAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$avmission = $em->getRepository('Mgate\SuiviBundle\Entity\AvMission')->find($id)) {
            throw $this->createNotFoundException('AvMission[id=' . $id . '] inexistant');
        }

        $etude = $avmission->getEtude();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $form = $this->createForm(AvMissionType::class, $avmission);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->flush();

                return $this->redirect($this->generateUrl('MgateSuivi_avmission_voir', array('id' => $avmission->getId())));
            }
        }

        return $this->render('MgateSuiviBundle:AvMission:modifier.html.twig', array(
            'form' => $form->createView(),
            'avmission' => $avmission,
        ));
    }
}
