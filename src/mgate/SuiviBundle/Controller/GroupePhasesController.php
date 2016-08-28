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
use mgate\SuiviBundle\Form\Type\GroupesPhasesType;
use mgate\SuiviBundle\Entity\GroupePhases;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GroupePhasesController extends Controller
{
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

        $originalGroupes = array();
        // Create an array of the current groupe objects in the database
        foreach ($etude->getGroupes() as $groupe) {
            $originalGroupes[] = $groupe;
        }

        $form = $this->createForm(new GroupesPhasesType(), $etude);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->handleRequest($this->get('request'));

            if ($form->isValid()) {
                if ($this->get('request')->get('add')) {
                    $groupeNew = new GroupePhases();
                    $groupeNew->setNumero(count($etude->getGroupes()));
                    $groupeNew->setTitre('Titre')->setDescription('Description');
                    $groupeNew->setEtude($etude);
                    $etude->addGroupe($groupeNew);
                }

                // filter $originalGroupes to contain Groupes no longer present
                foreach ($etude->getGroupes() as $groupe) {
                    foreach ($originalGroupes as $key => $toDel) {
                        if ($toDel->getId() === $groupe->getId()) {
                            unset($originalGroupes[$key]);
                        }
                    }
                }

                // remove the relationship between the groupe and the etude
                foreach ($originalGroupes as $groupe) {
                    $em->remove($groupe); // on peut faire un persist sinon, cf doc collection form
                }

                $em->persist($etude); // persist $etude / $form->getData()
                $em->flush();
            }
            return $this->redirect($this->generateUrl('mgateSuivi_groupes_modifier',array('id' => $etude->getId())));
        }

        return $this->render('mgateSuiviBundle:GroupePhases:modifier.html.twig', array(
            'form' => $form->createView(),
            'etude' => $etude,
        ));
    }
}
