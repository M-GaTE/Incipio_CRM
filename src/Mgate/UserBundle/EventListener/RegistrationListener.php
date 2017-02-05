<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\UserBundle\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener responsible to change the redirection at the end of the password resetting.
 */
class RegistrationListener implements EventSubscriberInterface
{
    private $mailer;
    private $templating;

    public function __construct(\Swift_Mailer $mailer, $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_CONFIRMED => 'onRegistrationConfirmed',
        );
    }

    // Prévenir lorsque quelqu'un valide compte
    public function onRegistrationConfirmed(FilterUserResponseEvent $event)
    {
        // $junior = $this->container->getParameter('junior');  //ligne faisant bugger la validation, skippé pour un remplacement. TODO : Regler ce problème.
        $message = \Swift_Message::newInstance()
            ->setSubject('Incipio : Nouvel utilisateur '.$event->getUser()->getUsername())
            ->setFrom('no-reply@erp.N7consulting.fr')
           // ->setTo($junior['email']) // cf remarque ci-dessus, remplacement en dur de la variable car bug
            ->setTo('contact@N7consulting.fr')
            ->setBody($this->templating->render('MgateUserBundle:Default:alert-email.html.twig',
                                        array('username' => $event->getUser()->getUsername(), 'email' => $event->getUser()->getEmail())), 'text/html');
        $this->mailer->send($message);
    }
}
