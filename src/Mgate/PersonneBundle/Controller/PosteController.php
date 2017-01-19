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

use Mgate\PersonneBundle\Entity\Poste;
use Mgate\PersonneBundle\Form\Type\PosteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

class PosteController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $poste = new Poste();

        $form = $this->createForm(PosteType::class, $poste);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($poste);
                $em->flush();

                return $this->redirect($this->generateUrl('MgatePersonne_poste_voir', array('id' => $poste->getId())));
            }
        }

        return $this->render('MgatePersonneBundle:Poste:ajouter.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MgatePersonneBundle:Poste')->findAll();

        return $this->render('MgatePersonneBundle:Poste:index.html.twig', array(
            'postes' => $entities,
        ));
    }

    /**
     * @Security("has_role('ROLE_ELEVE')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MgatePersonneBundle:Poste')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Le poste demandé n\'existe pas !');
        }

        return $this->render('MgatePersonneBundle:Poste:voir.html.twig', array(
            'poste' => $entity,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifierAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$poste = $em->getRepository('Mgate\PersonneBundle\Entity\Poste')->find($id)) {
            throw $this->createNotFoundException('Le poste demandé n\'existe pas !');
        }

        // On passe l'$article récupéré au formulaire
        $form = $this->createForm(PosteType::class, $poste);
        $deleteForm = $this->createDeleteForm($id);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($poste);
                $em->flush();

                return $this->redirect($this->generateUrl('MgatePersonne_poste_voir', array('id' => $poste->getId())));
            }
        }

        return $this->render('MgatePersonneBundle:Poste:modifier.html.twig', array(
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function deleteAction(Request $request, Poste $poste)
    {
        $form = $this->createDeleteForm($poste->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if($poste->getMandats()->count() == 0) { //collection contains no mandats
                foreach ($poste->getMandats() as $membre) {
                    $membre->setPoste(null);
                }
                $em->remove($poste);
                $em->flush();
                $this->addFlash('success', 'Poste supprimé avec succès');
                return $this->redirect($this->generateUrl('MgatePersonne_poste_homepage'));
            }
            else{
                $this->addFlash('danger', 'Impossible de supprimer un poste ayant des membres.');
                return $this->redirect($this->generateUrl('MgatePersonne_poste_modifier', array('id' => $poste->getId() )));
            }
        }


    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->getForm()
        ;
    }
}
