<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\PersonneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use mgate\PersonneBundle\Entity\Prospect;
use mgate\PersonneBundle\Form\ProspectType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ProspectController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function ajouterAction($format)
    {
        $em = $this->getDoctrine()->getManager();
        $prospect = new Prospect();

        $form = $this->createForm(new ProspectType(), $prospect);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                $em->persist($prospect);
                $em->flush();

                return $this->redirect($this->generateUrl('mgatePersonne_prospect_voir', array('id' => $prospect->getId())));
            }
        }

        return $this->render('mgatePersonneBundle:Prospect:ajouter.html.twig', array(
            'form' => $form->createView(),
            'format' => $format,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('mgatePersonneBundle:Prospect')->getAllProspect();

        return $this->render('mgatePersonneBundle:Prospect:index.html.twig', array(
            'prospects' => $entities,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('mgatePersonneBundle:Prospect')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Le prospect demandé n\'existe pas !');
        }

        //récupération des employés
        $mailing = '';
        $employes = array();
        foreach ($entity->getEmployes() as $employe) {
            if ($employe->getPersonne()->getEmailEstValide() && $employe->getPersonne()->getEstAbonneNewsletter()) {
                $nom = $employe->getPersonne()->getNom();
                $mail = $employe->getPersonne()->getEmail();
                $employes[$nom] = $mail;
            }
        }
        ksort($employes);
        foreach ($employes as $nom => $mail) {
            $mailing .= "$nom <$mail>; ";
        }

        //récupération des études faites avec ce prospect
        $etudes = $em->getRepository('mgateSuiviBundle:Etude')->findByProspect($entity);

        return $this->render('mgatePersonneBundle:Prospect:voir.html.twig', array(
            'prospect' => $entity,
            'mailing' => $mailing,
            'etudes' => $etudes,
            ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function modifierAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$prospect = $em->getRepository('mgate\PersonneBundle\Entity\Prospect')->find($id)) {
            throw $this->createNotFoundException('Le prospect demandé n\'existe pas!');
        }

        // On passe l'$article récupéré au formulaire
        $form = $this->createForm(new ProspectType(), $prospect);
        $deleteForm = $this->createDeleteForm($id);
        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                $em->persist($prospect);
                $em->flush();

                return $this->redirect($this->generateUrl('mgatePersonne_prospect_voir', array('id' => $prospect->getId())));
            }
        }

        return $this->render('mgatePersonneBundle:Prospect:modifier.html.twig', array(
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'prospect' => $prospect,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (!$entity = $em->getRepository('mgate\PersonneBundle\Entity\Prospect')->find($id)) {
                throw $this->createNotFoundException('Le prospect demandé n\'existe pas!');
            }

            /*if($entity->getPersonne())
            {
                $entity->getPersonne()->setMembre(null);
            }
            $entity->setPersonne(null);*/

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('mgatePersonne_prospect_homepage'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }


    /**
     * Point d'entré ajax retournant un json des prospect dont le nom contient une partie de $_GET['term'].
     * @Route("/ajax_prospect", name="ajax_prospect")
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function ajaxProspectAction(Request $request)
    {
        $value = $request->get('term');

        $em = $this->getDoctrine()->getManager();
        $members = $em->getRepository('mgatePersonneBundle:Prospect')->ajaxSearch($value);

        $json = array();
        foreach ($members as $member) {
            $json[] = array(
                'label' => $member->getNom(),
                'value' => $member->getId(),
            );
        }

        $response = new Response();
        $response->setContent(json_encode($json));

        return $response;
    }

    /**
     * Point d'entrée ajax retournant un Json avec la liste des employés d'un prospect donné.
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function ajaxEmployesAction(Prospect $prospect, $id){

        $em = $this->getDoctrine()->getManager();
        $employes = $em->getRepository('mgatePersonneBundle:Employe')->findByProspect($prospect);
        $json = array();
        foreach($employes as $employe){
            array_push( $json,array('label' => $employe->__toString(), 'value'=>$employe->getId()) );
        }
        $response = new JsonResponse();
        $response->setData($json);

        return $response;
    }


}
