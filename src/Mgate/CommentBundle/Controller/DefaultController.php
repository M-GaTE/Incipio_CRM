<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function maintenanceAction()
    {
        $em = $this->getDoctrine()->getManager();
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        foreach ($etudes as $entity) {
            if (!$em->getRepository('MgateCommentBundle:Thread')->findBy(array('id' => $entity))) {
                $this->container->get('Mgate_comment.thread')->creerThread('etude_', $this->container->get('router')->generate('MgatePersonne_prospect_voir', array('id' => $entity->getId())), $entity);
            }
        }

        return $this->render('MgateCommentBundle:Default:index.html.twig', array('name' => 'rien'));
    }
}
