<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\SuiviBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use mgate\SuiviBundle\Entity\Etude;
use mgate\SuiviBundle\Form\Type\PhasesType;
use mgate\SuiviBundle\Entity\Phase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PhasesController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('mgateSuiviBundle:Etude')->findAll();

        return $this->render('mgateSuiviBundle:Etude:index.html.twig', array(
            'etudes' => $entities,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function modifierAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$etude = $em->getRepository('mgate\SuiviBundle\Entity\Etude')->find($id)) {
            throw $this->createNotFoundException('L\'étude n\'existe pas !');
        }

        if ($this->get('mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $originalPhases = array();
        // Create an array of the current Phase objects in the database
        foreach ($etude->getPhases() as $phase) {
            $originalPhases[] = $phase;
        }

        $form = $this->createForm(new PhasesType(), $etude, array('etude' => $etude));

        if ($this->get('request')->getMethod() == 'POST') {
            $form->handleRequest($this->get('request'));

            if ($form->isValid()) {
                if ($this->get('request')->get('add')) {
                    $phaseNew = new Phase();
                    $phaseNew->setPosition(count($etude->getPhases()));
                    $phaseNew->setEtude($etude);
                    $etude->addPhase($phaseNew);
                }

                // filter $originalPhases to contain phases no longer present
                foreach ($etude->getPhases() as $phase) {
                    foreach ($originalPhases as $key => $toDel) {
                        if ($toDel->getId() === $phase->getId()) {
                            unset($originalPhases[$key]);
                        }
                    }
                }

                // remove the relationship between the phase and the etude
                foreach ($originalPhases as $phase) {
                    $em->remove($phase); // on peut faire un persist sinon, cf doc collection form
                }

                $em->persist($etude); // persist $etude / $form->getData()
                $em->flush();

            }
            return $this->redirect($this->generateUrl('mgateSuivi_phases_modifier', array('id' => $etude->getId())));
        }

        return $this->render('mgateSuiviBundle:Phase:phases.html.twig', array(
            'form' => $form->createView(),
            'etude' => $etude,
        ));
    }
}
