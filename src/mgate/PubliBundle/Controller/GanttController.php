<?php

namespace mgate\PubliBundle\Controller;

use mgate\SuiviBundle\Entity\Etude;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class GetGanttController
 * @package mgate\PubliBundle\Controller
 * Controller dédié à la génération de gantt Chart.
 */
class GanttController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * Génère le Gantt Chart de l'étude passée en paramètre.
     * @param Etude $etude project whom gantt chart should be exported.
     * @param int $width width of exported gantt
     * @param bool $debug
     * @return Response a png of project gantt chart
     */
    public function getGanttAction(Etude $etude,$width=960, $debug = false)
    {
        if ($this->get('mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
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
