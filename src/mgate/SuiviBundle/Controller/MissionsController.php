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

use Doctrine\Common\Collections\ArrayCollection;
use mgate\SuiviBundle\Entity\RepartitionJEH;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use mgate\SuiviBundle\Entity\Etude;
use mgate\SuiviBundle\Form\MissionsType;
use mgate\SuiviBundle\Entity\Mission;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MissionsController extends Controller
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
     * @param $id int id of project.
     * @return RedirectResponse|Response
     */
    public function modifierAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$etude = $em->getRepository('mgate\SuiviBundle\Entity\Etude')->find($id)) {
            throw $this->createNotFoundException('L\'étude demandée n\'existe pas!');
        }

        if ($this->get('mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        //save missions and repartition before form handling
        $missionList = new ArrayCollection();
        foreach($etude->getMissions() as $mission){
            $missionList->add($mission);
        }

        $repartitionList = new ArrayCollection();
        foreach ($missionList as $mission) {
            $repartitionList->add($mission->getRepartitionsJEH());
        }

        /** Form handling */
        $form = $this->createForm(new MissionsType($etude), $etude);
        if ($this->get('request')->getMethod() == 'POST') {
            $form->handleRequest($this->get('request'));

            if ($form->isValid()) {
                //if a new missions set is created
                if ($this->get('request')->get('add')) {
                    $missionNew = new Mission(); // add a new empty mission to mission set.
                    $missionNew->setEtude($etude);
                    $etude->addMission($missionNew);
                }

                //if a repartition is added to a mission
                if ($this->get('request')->get('addRepartition')) {
                    $repartitionNew = new RepartitionJEH();

                    if ($this->get('request')->get('idMission') !== null) {
                        $idMission = intval($this->get('request')->get('idMission'));
                        if ($etude->getMissions()->get($idMission)) {
                            $mission = $etude->getMissions()->get($this->get('request')->get('idMission'));
                            $mission->addRepartitionsJEH($repartitionNew);
                            $repartitionNew->setMission($mission);

                            $repartitionNew->setNbrJEH(0);
                            $repartitionNew->setPrixJEH(320);
                        }
                    }
                }
                //removing existing missions from missionList to get missions to delete
                foreach ($etude->getMissions() as $mission) {
                    //compare current missions to initial mission list
                    if ($missionList->contains($mission)) { // mission still exists, let's remove it
                        $missionList->removeElement($mission);

                        //compare current repartition to initial repartition list
                        foreach ($mission->getRepartitionsJEH() as $repartition) {
                            if ($repartitionList->contains($repartition)) { // repartition still exist, let's remove it
                                $repartitionList->removeElement($repartition);
                            }
                        }
                    }
                }
                //at that point we have missionList and repartitionlist are containing only objects to delete. let's iterate on them
                foreach ($missionList as $mission) {
                    $em->remove($mission);
                }
                foreach ($repartitionList as $repartitions) {
                    foreach ($repartitions as $repartition) {
                        $em->remove($repartition);
                    }
                }

                $em->persist($etude);
                $em->flush();

                return $this->redirect($this->generateUrl('mgateSuivi_missions_modifier', array('id' => $etude->getId())));
            }
        }

        return $this->render('mgateSuiviBundle:Mission:missions.html.twig', array(
            'form' => $form->createView(),
            'etude' => $etude,
        ));
    }
}
