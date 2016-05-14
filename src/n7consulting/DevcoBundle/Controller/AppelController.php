<?php

namespace n7consulting\DevcoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use n7consulting\DevcoBundle\Entity\Appel;
use n7consulting\DevcoBundle\Entity\AppelRepository;
use n7consulting\DevcoBundle\Form\AppelType;

class AppelController extends Controller
{
    /**
     * @Secure(roles="ROLE_SUIVEUR")
     */
    public function indexAction()
    {
        return $this->render('n7consultingDevcoBundle:Default:index.html.twig', array('name' => 'tocard'));
    }

    /**
     * @Secure(roles="ROLE_SUIVEUR")
     */
    public function ajouterAction(){
        $em = $this->getDoctrine()->getManager();

        $appel = new Appel();

        $form = $this->createForm(new AppelType(), $appel);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                $em->persist($appel);
                $em->flush();

                return $this->redirect($this->generateUrl('n7consultingDevco_appel_voir', array('id' => $appel->getId())));
            }
        }

        return $this->render('n7consultingDevcoBundle:Appel:ajouter.html.twig', array(
            'form' => $form->createView(),
        ));
    }


    /**
     * @Secure(roles="ROLE_SUIVEUR")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifierAction($id){
        $em = $this->getDoctrine()->getManager();

        if (!$appel = $em->getRepository('n7consulting\DevcoBundle\Entity\Appel')->find($id)) {
            throw $this->createNotFoundException('L\'appel demandé n\'existe pas !');
        }
        // On passe l'appel récupéré au formulaire
        $form = $this->createForm(new AppelType(), $appel);
        $deleteForm = $this->createDeleteForm($id);
        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                $em->persist($appel);
                $em->flush();

                return $this->redirect($this->generateUrl('n7consultingDevco_appel_voir', array('id' => $appel->getId())));
            }
        }

        return $this->render('n7consultingDevcoBundle:Appel:modifier.html.twig', array(
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Secure(roles="ROLE_SUIVEUR")
     * utilisation du paramconverter
     */
    public function voirAction(Appel $appel, $id){
        return $this->render('n7consultingDevcoBundle:Appel:voir.html.twig',array('appel' => $appel));
    }


    /**
     * @Secure(roles="ROLE_CA")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (!$entity = $em->getRepository('n7consulting\DevcoBundle\Entity\Appel')->find($id)) {
                throw $this->createNotFoundException('L\'appel demandé n\'existe pas !');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('n7consultingDevco_homepage'));
    }


    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
            ;
    }


}
