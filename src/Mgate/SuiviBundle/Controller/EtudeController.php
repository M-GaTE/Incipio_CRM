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
use Mgate\SuiviBundle\Form\Type\EtudeType;
use Mgate\SuiviBundle\Form\Type\SuiviEtudeType;
use Mgate\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class EtudeController extends Controller
{
    const STATE_ID_EN_NEGOCIATION = 1;
    const STATE_ID_EN_COURS = 2;
    const STATE_ID_EN_PAUSE = 3;
    const STATE_ID_TERMINEE = 4;
    const STATE_ID_AVORTEE = 5;
    
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction($page)
    {
        $MANDAT_MAX = $this->get('Mgate.etude_manager')->getMaxMandat();
        $MANDAT_MIN = $this->get('Mgate.etude_manager')->getMinMandat();

        $em = $this->getDoctrine()->getManager();

        //Etudes En Négociation : stateID = 1
        $etudesEnNegociation = $em->getRepository('MgateSuiviBundle:Etude')->getPipeline(array('stateID' => self::STATE_ID_EN_NEGOCIATION), array('mandat' => 'DESC', 'num' => 'DESC'));

        //Etudes En Cours : stateID = 2
        $etudesEnCours = $em->getRepository('MgateSuiviBundle:Etude')->getPipeline(array('stateID' => self::STATE_ID_EN_COURS), array('mandat' => 'DESC', 'num' => 'DESC'));

        //Etudes en pause : stateID = 3
        $etudesEnPause = $em->getRepository('MgateSuiviBundle:Etude')->getPipeline(array('stateID' => self::STATE_ID_EN_PAUSE), array('mandat' => 'DESC', 'num' => 'DESC'));

        //Etudes Terminees et Avortees Chargée en Ajax dans getEtudesAsyncAction
        //On push des arrays vides pour avoir les menus déroulants
        $etudesTermineesParMandat = array();
        $etudesAvorteesParMandat = array();

        for ($i = $MANDAT_MIN; $i <= $MANDAT_MAX; ++$i) {
            array_push($etudesTermineesParMandat, array());
            array_push($etudesAvorteesParMandat, array());
        }

        $anneeCreation = $this->get('app.json_key_value_store')->get('anneeCreation');
        return $this->render('MgateSuiviBundle:Etude:index.html.twig', array(
            'etudesEnNegociation' => $etudesEnNegociation,
            'etudesEnCours' => $etudesEnCours,
            'etudesEnPause' => $etudesEnPause,
            'etudesTermineesParMandat' => $etudesTermineesParMandat,
            'etudesAvorteesParMandat' => $etudesAvorteesParMandat,
            'anneeCreation' => $anneeCreation,
            'mandatMax' => $MANDAT_MAX,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @param Request $request
     * @return Response
     */
    public function getEtudesAsyncAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'GET') {
            $mandat = $request->query->get('mandat');
            $stateID = $request->query->get('stateID');

            // CHECK VAR ATTENTION INJECTION SQL ?
            $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findBy(array('stateID' => $stateID, 'mandat' => $mandat), array('num' => 'DESC'));

            if ($stateID == self::STATE_ID_TERMINEE) {
                return $this->render('MgateSuiviBundle:Etude:Tab/EtudesTerminees.html.twig', array('etudes' => $etudes));
            } elseif ($stateID == self::STATE_ID_AVORTEE) {
                return $this->render('MgateSuiviBundle:Etude:Tab/EtudesAvortees.html.twig', array('etudes' => $etudes));
            }
        } else {
            return $this->render('MgateSuiviBundle:Etude:Tab/EtudesAvortees.html.twig', array(
                'etudes' => null,
            ));
        }
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function stateAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getManager();

            $stateDescription = !empty($request->request->get('state')) ? $request->request->get('state') : '';
            $stateID = !empty($request->request->get('id')) ? intval($request->request->get('id')) : 0;
            $etudeID = !empty($request->request->get('etude')) ? intval($request->request->get('etude')) : 0;

            if (!$etude = $em->getRepository('Mgate\SuiviBundle\Entity\Etude')->find($etudeID)) {
                throw $this->createNotFoundException('L\'étude n\'existe pas !');
            } else {
                $etude->setStateDescription($stateDescription);
                $etude->setStateID($stateID);
                $em->persist($etude);
                $em->flush();
            }

            return $this->redirect($this->generateUrl('MgateSuivi_state'));
        }

        return new Response('ok !');
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addAction(Request $request)
    {
        $etude = new Etude();

        $etude->setMandat($this->get('Mgate.etude_manager')->getMaxMandat());
        $etude->setNum($this->get('Mgate.etude_manager')->getNouveauNumero());

        $user = $this->getUser();
        if (is_object($user) && $user instanceof User) {
            $etude->setSuiveur($user->getPersonne());
        }

        $form = $this->createForm(EtudeType::class, $etude);
        $em = $this->getDoctrine()->getManager();

        $error_messages = array();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                if (!$etude->isKnownProspect()) {
                    $etude->setProspect($etude->getNewProspect());
                }

                $em->persist($etude);
                $em->flush();

                if ($request->get('ap')) {
                    return $this->redirect($this->generateUrl('MgateSuivi_ap_rediger', array('id' => $etude->getId())));
                } else {
                    return $this->redirect($this->generateUrl('MgateSuivi_etude_voir', array('nom' => $etude->getNom())));
                }
            } else {
                //constitution du tableau d'erreurs
                $errors = $this->get('validator')->validate($etude);
                foreach ($errors as $error) {
                    array_push($error_messages, $error->getPropertyPath().' : '.$error->getMessage());
                }
            }
        }

        return $this->render('MgateSuiviBundle:Etude:ajouter.html.twig', array(
            'form' => $form->createView(),
            'errors' => $error_messages,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function voirAction(Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        //get contacts clients
        $clientContacts = $em->getRepository('MgateSuiviBundle:ClientContact')->getByEtude($etude, array('date' => 'desc'));

        $chartManager = $this->get('Mgate.chart_manager');
        $ob = $chartManager->getGantt($etude, 'suivi');

        $formSuivi = $this->createForm(SuiviEtudeType::class, $etude);

        return $this->render('MgateSuiviBundle:Etude:voir.html.twig', array(
            'etude' => $etude,
            'formSuivi' => $formSuivi->createView(),
            'chart' => $ob,
            'clientContacts' => $clientContacts,
            /* 'delete_form' => $deleteForm->createView(),  */));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function modifierAction(Request $request, Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $form = $this->createForm(EtudeType::class, $etude);

        $deleteForm = $this->createDeleteForm($etude);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($etude);
                $em->flush();

                return $this->redirect($this->generateUrl('MgateSuivi_etude_voir', array('nom' => $etude->getNom())));
            }
        }

        return $this->render('MgateSuiviBundle:Etude:modifier.html.twig', array(
            'form' => $form->createView(),
            'etude' => $etude,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Etude   $etude
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Etude $etude, Request $request)
    {
        $form = $this->createDeleteForm($etude);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
                throw new AccessDeniedException('Cette étude est confidentielle');
            }

            $em->remove($etude);
            $em->flush();
            $request->getSession()->getFlashBag()->add('success', 'Etude supprimée');
        }

        return $this->redirect($this->generateUrl('MgateSuivi_etude_homepage'));
    }

    private function createDeleteForm(Etude $etude)
    {
        return $this->createFormBuilder(array('id' => $etude->getId()))
            ->add('id', HiddenType::class)
            ->getForm();
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function suiviAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $MANDAT_MAX = 10;

        $etudesParMandat = array();

        for ($i = 1; $i < $MANDAT_MAX; ++$i) {
            array_push($etudesParMandat, $em->getRepository('MgateSuiviBundle:Etude')->findBy(array('mandat' => $i), array('num' => 'DESC')));
        }

        //WARN
        /* Création d'un form personalisé sans classes (Symfony Forms without Classes)
         *
         * Le problème qui se pose est de savoir si les données reçues sont bien destinées aux bonnes études
         * Si quelqu'un ajoute une étude ou supprime une étude pendant la soumission de se formulaire, c'est la cata
         * tout se décale de 1 étude !!
         * J'ai corrigé ce bug en cas d'ajout d'une étude. Les changements sont bien sauvegardés !!
         * Mais cette page doit être rechargée et elle l'est automatiquement. (Si js est activé !)
         * bref rien de bien fracassant. Solution qui se doit d'être temporaire bien que fonctionnelle !
         * Cependant en cas de suppression d'une étude, chose qui n'arrive pas tous les jours, les données seront perdues !!
         * Perdues Perdues !!!
         */
        $etudesEnCours = array();

        $NbrEtudes = 0;
        foreach ($etudesParMandat as $etudesInMandat) {
            $NbrEtudes += count($etudesInMandat);
        }

        $form = $this->createFormBuilder();

        $id = 0;
        foreach (array_reverse($etudesParMandat) as $etudesInMandat) {
            foreach ($etudesInMandat as $etude) {
                $form = $form->add((string) (2 * $id), HiddenType::class, array('label' => 'refEtude', 'data' => $etude->getReference()))
                    ->add((string) (2 * $id + 1), 'textarea', array('label' => $etude->getReference(), 'required' => false, 'data' => $etude->getStateDescription()));
                ++$id;
                if ($etude->getStateID() == self::STATE_ID_EN_COURS) {
                    array_push($etudesEnCours, $etude);
                }
            }
        }
        $form = $form->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $data = $form->getData();

            $id = 0;
            foreach (array_reverse($etudesParMandat) as $etudesInMandat) {
                foreach ($etudesInMandat as $etude) {
                    if ($data[2 * $id] == $etude->getReference()) {
                        if ($data[2 * $id] != $etude->getStateDescription()) {
                            $etude->setStateDescription($data[2 * $id + 1]);
                            $em->persist($etude);
                            ++$id;
                        }
                    } else {
                        echo '<script>location.reload();</script>';
                    }
                }
            }
            $em->flush();
        }

        $chartManager = $this->get('Mgate.chart_manager');
        $ob = $chartManager->getGanttSuivi($etudesEnCours);

        return $this->render('MgateSuiviBundle:Etude:suiviEtudes.html.twig', array(
            'etudesParMandat' => $etudesParMandat,
            'form' => $form->createView(),
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function suiviQualiteAction()
    {
        $em = $this->getDoctrine()->getManager();

        $etudesEnCours = $em->getRepository('MgateSuiviBundle:Etude')->findBy(array('stateID' => self::STATE_ID_EN_COURS), array('mandat' => 'DESC', 'num' => 'DESC'));
        $etudesTerminees = $em->getRepository('MgateSuiviBundle:Etude')->findBy(array('stateID' => self::STATE_ID_TERMINEE), array('mandat' => 'DESC', 'num' => 'DESC'));
        $etudes = array_merge($etudesEnCours, $etudesTerminees);

        $chartManager = $this->get('Mgate.chart_manager');
        $ob = $chartManager->getGanttSuivi($etudes);

        return $this->render('MgateSuiviBundle:Etude:suiviQualite.html.twig', array(
            'etudesEnCours' => $etudesEnCours,
            'etudesTerminees' => $etudesTerminees,
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function suiviUpdateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $etude = $em->getRepository('MgateSuiviBundle:Etude')->find($id);

        if (!$etude) {
            throw $this->createNotFoundException('Unable to find Etude entity.');
        }

        if ($this->get('Mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $formSuivi = $this->createForm(SuiviEtudeType::class, $etude);
        if ($request->getMethod() == 'POST') {
            $formSuivi->handleRequest($request);

            if ($formSuivi->isValid()) {
                $em->persist($etude);
                $em->flush();

                $return = array('responseCode' => 200, 'msg' => 'ok');
            } else {
                $return = array('responseCode' => 412, 'msg' => 'Erreur:'.$formSuivi->getErrors(true, false));
            }
        }

        return new JsonResponse($return); //make sure it has the correct content type
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function vuCAAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id > 0) {
            $etude = $em->getRepository('MgateSuiviBundle:Etude')->find($id);
        } else {
            $etude = $em->getRepository('MgateSuiviBundle:Etude')->findOneBy(array('stateID' => self::STATE_ID_EN_COURS));
        }

        if (!$etude) {
            throw $this->createNotFoundException('Unable to find Etude entity.');
        }

        //Etudes En Négociation : stateID = 1
        $etudesDisplayList = $em->getRepository('MgateSuiviBundle:Etude')->getTwoStates([self::STATE_ID_EN_NEGOCIATION, self::STATE_ID_EN_COURS], array('mandat' => 'ASC', 'num' => 'ASC'));

        if (!in_array($etude, $etudesDisplayList)) {
            throw $this->createNotFoundException('Etude incorrecte');
        }

        /* pagination management */
        $currentEtudeId = array_search($etude, $etudesDisplayList);
        $nextId = min(count($etudesDisplayList), $currentEtudeId + 1);
        $previousId = max(0, $currentEtudeId - 1);

        $chartManager = $this->get('Mgate.chart_manager');
        $ob = $chartManager->getGantt($etude, 'suivi');

        return $this->render('MgateSuiviBundle:Etude:vuCA.html.twig', array(
            'etude' => $etude,
            'chart' => $ob,
            'nextID' => $etudesDisplayList[$nextId]->getId(),
            'prevID' => $etudesDisplayList[$previousId]->getId(),
            'etudesDisplayList' => $etudesDisplayList,
        ));
    }
}
