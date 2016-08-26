<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use mgate\UserBundle\Form\UserAdminType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DefaultController extends Controller
{
    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listerAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('mgateUserBundle:User')->findAll();

        return $this->render('mgateUserBundle:Default:lister.html.twig', array('users' => $entities));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('mgateUserBundle:User')->find($id);
        if (!$user) {
            throw $this->createNotFoundException('L\'utilisateur n\'existe pas !');
        }

        return $this->render('mgateUserBundle:Default:voir.html.twig', array('user' => $user));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function modifierAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('mgateUserBundle:User')->find($id);
        if (!$user) {
            throw $this->createNotFoundException('L\'utilisateur n\'existe pas !');
        }

        if ($user->getId() == 1) {
            throw new AccessDeniedException('Impossible de modifier le Super Administrateur. Contactez support@incipio.fr pour toute modification.');
        }

        $form = $this->createForm(new UserAdminType('mgate\UserBundle\Entity\User', $this->getParameter('security.role_hierarchy.roles')), $user);
        $deleteForm = $this->createDeleteForm($id);
        if ($this->get('request')->getMethod() == 'POST') {
            $form->handleRequest($this->get('request'));

            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();

                $userManager = $this->container->get('fos_user.user_manager');
                $userManager->refreshUser($user);

                return $this->redirect($this->generateUrl('mgate_user_voir', array('id' => $user->getId())));
            }
        }

        return $this->render('mgateUserBundle:Default:modifier.html.twig', array(
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id, Request $request)
    {
        $form = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (!$entity = $em->getRepository('mgate\UserBundle\Entity\User')->find($id)) {
                throw $this->createNotFoundException('L\'utilisateur n\'existe pas !');
            }

            if ($entity->getId() == 1) {
                throw new AccessDeniedException('Impossible de supprimer le Super Administrateur. Contactez support@incipio.fr pour toute modification.');
            }

            if ($entity->getPersonne()) {
                $entity->getPersonne()->setUser(null);
            }
            $entity->setPersonne(null);
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('mgate_user_lister'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addUserFromPersonneAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $personne = $em->getRepository('mgatePersonneBundle:Personne')->find($id);

        if (!$personne) {
            throw $this->createNotFoundException('La personne n\'existe pas !');
        }

        if ($personne->getUser()) {
            throw new \Exception('Un utilisateur est déjà liée à cette personne !');
        }
        if (!$personne->getEmail()) {
            throw new \Exception("l'utilisateur n'a pas d'email valide !");
        }

        $temporaryPassword = md5(mt_rand());
        $token = sha1(uniqid(mt_rand(), true));

        /* Génération de l'user */
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->createUser();
        $user->setPersonne($personne);
        $user->setEmail($personne->getEmail());
        $user->setPlainPassword($temporaryPassword);
        // Utilisateur à confirmer
        $user->setEnabled(false);
        $user->setConfirmationToken($token);
        // \\
        $user->setUsername($this->enMinusculeSansAccent($personne->getPrenom().'.'.$personne->getNom()));
        /**/

        $userManager->updateUser($user); // Pas besoin de faire un flush (ça le fait tout seul)

        /* Envoie d'un email de confirmation */
        $mailer = $this->container->get('fos_user.mailer');
        $mailer->sendConfirmationEmailMessage($user);
        $mailer->sendResettingEmailMessage($user);

        return $this->redirect($this->generateUrl('mgate_user_lister'));
    }

    private function enMinusculeSansAccent($texte)
    {
        $texte = mb_strtolower($texte, 'UTF-8');
        $texte = str_replace(
            array(
                'à', 'â', 'ä', 'á', 'ã', 'å',
                'î', 'ï', 'ì', 'í',
                'ô', 'ö', 'ò', 'ó', 'õ', 'ø',
                'ù', 'û', 'ü', 'ú',
                'é', 'è', 'ê', 'ë',
                'ç', 'ÿ', 'ñ',
            ),
            array(
                'a', 'a', 'a', 'a', 'a', 'a',
                'i', 'i', 'i', 'i',
                'o', 'o', 'o', 'o', 'o', 'o',
                'u', 'u', 'u', 'u',
                'e', 'e', 'e', 'e',
                'c', 'y', 'n',
            ),
            $texte
        );

        return $texte;
    }
}
