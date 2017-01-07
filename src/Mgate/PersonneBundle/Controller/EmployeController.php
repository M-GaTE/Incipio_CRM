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

use Mgate\PersonneBundle\Entity\Employe;
use Mgate\PersonneBundle\Form\Type\EmployeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EmployeController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function ajouterAction(Request $request, $prospect_id, $format)
    {
        $em = $this->getDoctrine()->getManager();

        // On vérifie que le prospect existe bien
        if (!$prospect = $em->getRepository('Mgate\PersonneBundle\Entity\Prospect')->find($prospect_id)) {
            throw $this->createNotFoundException('Ce prospect n\'existe pas');
        }

        $employe = new Employe();
        $employe->setProspect($prospect);

        $form = $this->createForm(EmployeType::class, $employe);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($employe->getPersonne());
                $em->persist($employe);
                $employe->getPersonne()->setEmploye($employe);
                $em->flush();

                return $this->redirect($this->generateUrl('MgatePersonne_employe_voir', array('id' => $employe->getId())));
            }
        }

        return $this->render('MgatePersonneBundle:Employe:ajouter.html.twig', array(
            'form' => $form->createView(),
            'prospect' => $prospect,
            'format' => $format,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MgatePersonneBundle:Employe')->findAll();

        return $this->render('MgatePersonneBundle:Employe:index.html.twig', array(
            'users' => $entities,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MgatePersonneBundle:Employe')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('L\'employé demandé n\'existe pas');
        }

        return $this->render('MgatePersonneBundle:Employe:voir.html.twig', array(
            'employe' => $entity,
            ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function modifierAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$employe = $em->getRepository('Mgate\PersonneBundle\Entity\Employe')->find($id)) {
            throw $this->createNotFoundException('L\'employé demandé n\'existe pas');
        }

        // On passe l'$article récupéré au formulaire
        $form = $this->createForm(EmployeType::class, $employe);
        $deleteForm = $this->createDeleteForm($id);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($employe);
                $em->flush();

                return $this->redirect($this->generateUrl('MgatePersonne_employe_voir', array('id' => $employe->getId())));
            }
        }

        return $this->render('MgatePersonneBundle:Employe:modifier.html.twig', array(
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'employe' => $employe,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @param Employe $employe the employee to delete
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Employe $employe, Request $request)
    {
        $session = $request->getSession();

        $form = $this->createDeleteForm($employe->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

                //remove employes
                $em->remove($employe);
                $em->flush();
                $session->getFlashBag()->add('success', 'Employé supprimé');
            return $this->redirect($this->generateUrl('MgatePersonne_prospect_voir',array('id'=>$employe->getProspect()->getId())));

        }
        return $this->redirect($this->generateUrl('MgatePersonne_prospect_homepage'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
            ;
    }
}
