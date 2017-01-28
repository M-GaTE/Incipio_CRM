<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\UserBundle\Controller;

use Mgate\PersonneBundle\Entity\Personne;
use Mgate\UserBundle\Entity\User;
use Mgate\UserBundle\Form\Type\UserAdminType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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

        $entities = $em->getRepository('MgateUserBundle:User')->findAll();

        return $this->render('MgateUserBundle:Default:lister.html.twig', array('users' => $entities));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modifierAction(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();

        if ($user->getId() == 1) {
            throw new AccessDeniedException('Impossible de modifier le Super Administrateur. Contactez dsi@n7consulting.fr pour toute modification.');
        }

        $form = $this->createForm(UserAdminType::class, $user, array(
            'user_class' => 'Mgate\UserBundle\Entity\User','roles' => $this->getParameter('security.role_hierarchy.roles')
        ));
        $deleteForm = $this->createDeleteForm($user->getId());
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();

                $userManager = $this->container->get('fos_user.user_manager');
                $userManager->reloadUser($user);
                $this->addFlash('success', 'Utilisateur modifié');

                return $this->redirect($this->generateUrl('Mgate_user_lister'));
            }
        }

        return $this->render('MgateUserBundle:Default:modifier.html.twig', array(
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param User $user the user to be deleted
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @internal param $id
     */
    public function deleteAction(User $user, Request $request)
    {
        $form = $this->createDeleteForm($user->getId());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($user->getId() == 1) {
                throw new AccessDeniedException('Impossible de supprimer le Super Administrateur. Contactez support@incipio.fr pour toute modification.');
            }

            if ($user->getPersonne()) {
                $user->getPersonne()->setUser(null);
            }
            $user->setPersonne(null);
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé');
        }

        return $this->redirect($this->generateUrl('Mgate_user_lister'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->getForm();
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @param Personne $personne the personne whom a user should be added
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function addUserFromPersonneAction(Personne $personne)
    {

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
        $user->setUsername($this->enMinusculeSansAccent($personne->getPrenom() . '.' . $personne->getNom()));

        $userManager->updateUser($user); // Pas besoin de faire un flush (ça le fait tout seul)

        /* Envoie d'un email de confirmation */
        $mailer = $this->container->get('fos_user.mailer');
        $mailer->sendConfirmationEmailMessage($user);
        $mailer->sendResettingEmailMessage($user);

        return $this->redirect($this->generateUrl('Mgate_user_lister'));
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
