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

use Mgate\SuiviBundle\Entity\Phase;
use Mgate\SuiviBundle\Entity\PhaseChange;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Mgate\SuiviBundle\Entity\Av;
use Mgate\SuiviBundle\Form\Type\AvType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AvController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        return $this->render('MgateSuiviBundle:Av:index.html.twig', array(
            'etudes' => $entities,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function addAction($id)
    {
        return $this->modifierAction(null, $id);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MgateSuiviBundle:Av')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('La Convention Cliente n\'existe pas !');
        }

        $etude = $entity->getEtude();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }


        return $this->render('MgateSuiviBundle:Av:voir.html.twig', array(
            'av' => $entity,
        ));
    }

    private function getPhaseByPosition($position, $array)
    {
        foreach ($array as $phase) {
            if ($phase->getPosition() == $position) {
                return $phase;
            }
        }

        return;
    }

    public static $phaseMethodes = array('NbrJEH', 'PrixJEH', 'Titre', 'Objectif', 'Methodo', 'DateDebut', 'Validation', 'Delai', 'Position');

    private function mergePhaseIfNotNull($phaseReceptor, $phaseToMerge, $changes)
    {
        foreach (self::$phaseMethodes as $methode) {
            $getMethode = 'get' . $methode;
            $setMethode = 'set' . $methode;
            if ($phaseToMerge->$getMethode() !== null) {
                $changes->$setMethode(true);
                $phaseReceptor->$setMethode($phaseToMerge->$getMethode());
            }
        }
    }

    private function copyPhase($source, $destination)
    {
        foreach (self::$phaseMethodes as $methode) {
            $getMethode = 'get' . $methode;
            $setMethode = 'set' . $methode;
            $destination->$setMethode($source->$getMethode());
        }
    }

    private function phaseChange($phase)
    {
        $isNotNull = false;
        foreach (self::$phaseMethodes as $methode) {
            $getMethode = 'get' . $methode;
            $isNotNull = $isNotNull || ($phase->$getMethode() !== null && $methode != 'Position');
        }

        return $isNotNull;
    }

    private function nullFielIfEqual($phaseReceptor, $phaseToCompare)
    {
        $isNotNull = false;
        foreach (self::$phaseMethodes as $methode) {
            $getMethode = 'get' . $methode;
            $setMethode = 'set' . $methode;
            if ($phaseReceptor->$getMethode() == $phaseToCompare->$getMethode() && $methode != 'Position') {
                $phaseReceptor->$setMethode(null);
            } else {
                $isNotNull = true;
            }
        }

        return $isNotNull;
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function modifierAction($id, $idEtude = null)
    {
        $em = $this->getDoctrine()->getManager();

        if ($idEtude) {
            if (!$etude = $em->getRepository('Mgate\SuiviBundle\Entity\Etude')->find($idEtude)) {
                throw $this->createNotFoundException('L\'étude n\'existe pas !');
            }
            $av = new Av();
            $av->setEtude($etude);
            $etude->addAv($av);
        } elseif (!$av = $em->getRepository('Mgate\SuiviBundle\Entity\Av')->find($id)) {
            throw $this->createNotFoundException('L\'avenant n\'existe pas !');
        }

        $etude = $av->getEtude();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $phasesAv = array();
        if ($av->getPhases()) {
            $phasesAv = $av->getPhases()->toArray();

            foreach ($av->getPhases() as $phase) {
                $av->removePhase($phase);
                $em->remove($phase);
            }
        }

        $phasesChanges = array();

        $phasesEtude = $av->getEtude()->getPhases()->toArray();
        foreach ($phasesEtude as $phase) {
            $changes = new PhaseChange();
            $phaseAV = new Phase();

            $this->copyPhase($phase, $phaseAV);

            if ($phaseOriginAV = $this->getPhaseByPosition($phaseAV->getPosition(), $phasesAv)) {
                $this->mergePhaseIfNotNull($phaseAV, $phaseOriginAV, $changes);
            }

            $phaseAV->setEtude()->setAvenant($av);
            $av->addPhase($phaseAV);
            $phasesChanges[] = $changes;
        }

        $form = $this->createForm(new AvType(), $av, array('prospect' => $av->getEtude()->getProspect()));

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                $phasesEtude = $av->getEtude()->getPhases()->getValues();
                foreach ($av->getPhases() as $phase) {
                    $toKeep = false;
                    $av->removePhase($phase);

                    if (!$phaseEtude = $this->getPhaseByPosition($phase->getPosition(), $phasesEtude)) {
                        $toKeep = true;
                    }

                    if (isset($phaseEtude)) {
                        $toKeep = $this->nullFielIfEqual($phase, $phaseEtude);
                    }

                    if ($toKeep) {
                        $av->addPhase($phase);
                    }

                    unset($phaseEtude);
                }

                foreach ($av->getPhases() as $phase) {
                    $phase->setEtatSurAvenant(0);
                    if ($this->phaseChange($phase)) { // S'il n'y a plus de modification sur la phase
                        $em->persist($phase);
                    } else {
                        $av->removePhase($phase);
                    }
                }

                if ($idEtude) { // Si on ajoute un avenant
                    $em->persist($etude);
                } else { // Si on modifie un avenant
                    $em->persist($av);
                }
                $em->flush();

                return $this->redirect($this->generateUrl('MgateSuivi_av_voir', array('id' => $av->getId())));
            }
        }

        return $this->render('MgateSuiviBundle:Av:modifier.html.twig', array(
            'form' => $form->createView(),
            'av' => $av,
            'changes' => $phasesChanges,
        ));
    }
}
