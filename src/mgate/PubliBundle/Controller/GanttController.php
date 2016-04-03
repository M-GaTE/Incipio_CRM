<?php

namespace mgate\PubliBundle\Controller;

use mgate\SuiviBundle\Entity\Etude;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GetGanttController
 * @package mgate\PubliBundle\Controller
 * Controller dédié à la génération de gantt Chart.
 */
class GanttController extends Controller
{
    /**
     * @Secure(roles="ROLE_SUIVEUR")
     * Génère le Gantt Chart de l'étude passée en paramètre.
     */
    public function getGanttAction(Etude $etude, $id,$width=960, $debug = false)
    {
        if ($this->get('mgate.etude_manager')->confidentielRefus($etude, $this->container->get('security.context'))) {
            $errorEtudeConfidentielle = new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Cette étude est confidentielle');
            throw $errorEtudeConfidentielle;
        }

        //Gantt
        $chartManager = $this->get('mgate.chart_manager');
        $ob = $chartManager->getGantt($etude, 'gantt');
        if ($chartManager->exportGantt($ob, 'gantt' . $etude->getReference(),$width)) {
            $repertoire = 'tmp';
            $image = array();
            $image['fileLocation'] = $repertoire.'/'.$etude->getReference().'.png';
            $info = getimagesize($repertoire.'/'.$etude->getReference().'.png');
            $image['width'] = $info[0];
            $image['height'] = $info[1];
            $images['imageVARganttAP'] = $image;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'image/png');
        $response->headers->set('Content-disposition', 'attachment; filename="gantt' . $etude->getReference() . '.png"');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Expires', 0);

        $response->setContent(file_get_contents('tmp/gantt' . $etude->getReference() . '.png'));
        return $response;

    }

}
