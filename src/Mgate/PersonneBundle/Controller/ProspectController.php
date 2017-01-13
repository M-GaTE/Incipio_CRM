<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PersonneBundle\Controller;

use Mgate\PersonneBundle\Entity\Prospect;
use Mgate\PersonneBundle\Form\Type\ProspectType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProspectController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function ajouterAction(Request $request, $format)
    {
        $em = $this->getDoctrine()->getManager();
        $prospect = new Prospect();

        $form = $this->createForm(ProspectType::class, $prospect);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($prospect);
                $em->flush();

                return $this->redirect($this->generateUrl('MgatePersonne_prospect_voir', array('id' => $prospect->getId())));
            }
        }

        return $this->render('MgatePersonneBundle:Prospect:ajouter.html.twig', array(
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

        $entities = $em->getRepository('MgatePersonneBundle:Prospect')->getAllProspect();

        return $this->render('MgatePersonneBundle:Prospect:index.html.twig', array(
            'prospects' => $entities,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MgatePersonneBundle:Prospect')->find($id);

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
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findByProspect($entity);

        return $this->render('MgatePersonneBundle:Prospect:voir.html.twig', array(
            'prospect' => $entity,
            'mailing' => $mailing,
            'etudes' => $etudes,
            ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function modifierAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$prospect = $em->getRepository('Mgate\PersonneBundle\Entity\Prospect')->find($id)) {
            throw $this->createNotFoundException('Le prospect demandé n\'existe pas!');
        }

        // On passe l'$article récupéré au formulaire
        $form = $this->createForm(ProspectType::class, $prospect);
        $deleteForm = $this->createDeleteForm($id);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($prospect);
                $em->flush();

                return $this->redirect($this->generateUrl('MgatePersonne_prospect_voir', array('id' => $prospect->getId())));
            }
        }

        return $this->render('MgatePersonneBundle:Prospect:modifier.html.twig', array(
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'prospect' => $prospect,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @param Prospect $prospect the prospect to delete
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Prospect $prospect, Request $request)
    {
        $session = $request->getSession();

        $form = $this->createDeleteForm($prospect->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $related_projects = $em->getRepository('MgateSuiviBundle:Etude')->findByProspect($prospect);

            if(count($related_projects) > 0){//can't delete a prospect with related projects
                $session->getFlashBag()->add('warning', 'Impossible de supprimer un prospect ayant une étude liée.');
                return $this->redirect($this->generateUrl('MgatePersonne_prospect_voir', array('id' => $prospect->getId())));
            }
            else {
                //remove employes
                foreach($prospect->getEmployes() as $employe){
                    $em->remove($employe);
                }
                $em->remove($prospect);
                $em->flush();
                $session->getFlashBag()->add('success', 'Prospect supprimé');
            }
        }

        return $this->redirect($this->generateUrl('MgatePersonne_prospect_homepage'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->getForm()
        ;
    }

    /**
     * Point d'entré ajax retournant un json des prospect dont le nom contient une partie de $_GET['term'].
     *
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function ajaxProspectAction(Request $request)
    {
        $value = $request->get('term');

        $em = $this->getDoctrine()->getManager();
        $members = $em->getRepository('MgatePersonneBundle:Prospect')->ajaxSearch($value);

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
     *
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function ajaxEmployesAction(Prospect $prospect, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $employes = $em->getRepository('MgatePersonneBundle:Employe')->findByProspect($prospect);
        $json = array();
        foreach ($employes as $employe) {
            array_push($json, array('label' => $employe->__toString(), 'value' => $employe->getId()));
        }
        $response = new JsonResponse();
        $response->setData($json);

        return $response;
    }
}
