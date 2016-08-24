<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\SuiviBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use mgate\SuiviBundle\Entity\Etude;
use mgate\SuiviBundle\Entity\Mission;
use mgate\SuiviBundle\Form\MissionType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MissionController extends Controller
{
    /**
     * @Secure(roles="ROLE_SUIVEUR")
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('mgateSuiviBundle:Etude')->findAll();

        return $this->render('mgateSuiviBundle:Etude:index.html.twig', array(
                    'etudes' => $entities,
                ));
    }

    /**
     * @Secure(roles="ROLE_SUIVEUR")
     * @param Request $request
     * @return Response
     */
    public function avancementAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        // TODO : use a symfony form instead a simili php.
        $avancement = !empty($request->request->get('avancement')) ? intval($request->request->get('avancement')) : 0;
        $id = !empty($request->request->get('id')) ? $request->request->get('id') : 0;
        $intervenant = !empty($request->request->get('intervenant')) ? intval($request->request->get('intervenant')) : 0;

        $etude = $em->getRepository('mgate\SuiviBundle\Entity\Etude')->find($id);
        if (!$etude) {
            throw $this->createNotFoundException('L\'étude n\'existe pas !');
        } else {
            $etude->getMissions()->get($intervenant)->setAvancement($avancement);
            $em->persist($etude->getMissions()->get($intervenant));
            $em->flush();
        }

        return new Response('ok !');
    }

}
