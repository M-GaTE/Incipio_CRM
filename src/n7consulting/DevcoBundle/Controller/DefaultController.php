<?php

namespace n7consulting\DevcoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('n7consultingDevcoBundle:Default:index.html.twig', array('name' => $name));
    }
}
