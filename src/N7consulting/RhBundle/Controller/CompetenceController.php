<?php

namespace N7consulting\RhBundle\Controller;

use N7consulting\RhBundle\Entity\Competence;
use N7consulting\RhBundle\Form\Type\CompetenceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

class CompetenceController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $competence = new Competence();

        $form = $this->createForm(CompetenceType::class, $competence);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($competence);
                $em->flush();

                return $this->redirect($this->generateUrl('N7consultingRh_competence_voir', array('id' => $competence->getId())));
            }
        }

        return $this->render('N7consultingRhBundle:Competence:ajouter.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction($page)
    {
        $entities = $this->getDoctrine()->getManager()->getRepository('N7consultingRhBundle:Competence')->findAll();

        return $this->render('N7consultingRhBundle:Competence:index.html.twig', array(
            'competences' => $entities,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('N7consultingRhBundle:Competence')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Cette compétence n\'existe pas !');
        }

        $devs = $em->getRepository('MgatePersonneBundle:Membre')->findByCompetence($entity);

        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findByCompetence($entity);

        return $this->render('N7consultingRhBundle:Competence:voir.html.twig', array(
            'competence' => $entity,
            'devs' => $devs,
            'etudes' => $etudes,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function modifierAction(Request $request, Competence $competence)
    {
        $em = $this->getDoctrine()->getManager();

        // On passe l'$article récupéré au formulaire
        $form = $this->createForm(CompetenceType::class, $competence);
        $deleteForm = $this->createDeleteForm($competence->getId());
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($competence);
                $em->flush();

                return $this->redirect($this->generateUrl('N7consultingRh_competence_voir', array('id' => $competence->getId())));
            }
        }

        return $this->render('N7consultingRhBundle:Competence:modifier.html.twig', array(
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Par souci de simplicité, on fait 2 requetes (une sur les competences, une sur les intervenants), alors que seule la requete sur les competences suffirait.
     */
    public function visualiserAction()
    {
        $em = $this->getDoctrine()->getManager();
        $competences = $em->getRepository('N7consulting\RhBundle\Entity\Competence')->getCompetencesTree();
        $membres = $em->getRepository('MgatePersonneBundle:Membre')->getByCompetencesNonNul();

        return $this->render('N7consultingRhBundle:Competence:visualiser.html.twig', array(
            'competences' => $competences,
            'membres' => $membres,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * @param Request $request
     * @param Competence $competence param converter on id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     */
    public function deleteAction(Request $request, Competence $competence)
    {
        $form = $this->createDeleteForm($competence->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($competence);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('N7consultingRh_competence_homepage'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->getForm()
            ;
    }
}
