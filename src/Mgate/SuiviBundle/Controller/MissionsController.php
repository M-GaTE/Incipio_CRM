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

use Doctrine\Common\Collections\ArrayCollection;
use Mgate\SuiviBundle\Entity\Etude;
use Mgate\SuiviBundle\Entity\RepartitionJEH;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Mgate\SuiviBundle\Form\Type\MissionsType;
use Mgate\SuiviBundle\Entity\Mission;
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

        $entities = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        return $this->render('MgateSuiviBundle:Etude:index.html.twig', array(
            'etudes' => $entities,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Etude $etude
     * @return RedirectResponse|Response
     *
     */
    public function modifierAction(Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

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
                            $repartitionNew->setPrixJEH(340);
                        }
                    }
                }

                $em->persist($etude);
                $em->flush();

                return $this->redirect($this->generateUrl('MgateSuivi_missions_modifier', array('id' => $etude->getId())));
            }
        }

        return $this->render('MgateSuiviBundle:Mission:missions.html.twig', array(
            'form' => $form->createView(),
            'etude' => $etude,
        ));
    }
}
